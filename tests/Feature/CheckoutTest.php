<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Enums\VerificationStatus;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_one_order_per_store_with_a_single_payment(): void
    {
        $customer = User::factory()->create();
        $address = $this->createAddress($customer);
        $productA = $this->createProduct(1_000_000, 5);
        $productB = $this->createProduct(500_000, 4);

        CartItem::create(['user_id' => $customer->id, 'product_id' => $productA->id, 'quantity' => 2]);
        CartItem::create(['user_id' => $customer->id, 'product_id' => $productB->id, 'quantity' => 1]);

        $response = $this->actingAs($customer)->post(route('customer.checkout.store'), [
            'address_id' => $address->id,
            'shipping_method' => 'jne_reguler',
            'note' => 'Tolong dibungkus rapi.',
        ]);

        $payment = Payment::query()->firstOrFail();

        $response->assertRedirect(route('customer.payments.show', $payment));

        $this->assertSame(PaymentStatus::Unpaid, $payment->status);
        $this->assertSame(2, $payment->orders()->count());
        $this->assertSame(2, $payment->orders()->distinct()->count('store_id'));

        // 2 × Rp1.000.000 + Rp500.000 + ongkir Rp20.000 per toko.
        $this->assertSame(2_540_000, $payment->amount);

        $orderA = $payment->orders()->where('store_id', $productA->store_id)->firstOrFail();
        $orderB = $payment->orders()->where('store_id', $productB->store_id)->firstOrFail();

        $this->assertSame(OrderStatus::WaitingConfirmation, $orderA->status);
        $this->assertSame(2_000_000, $orderA->subtotal);
        $this->assertSame(100_000, $orderA->commission_amount);
        $this->assertSame(2_020_000, $orderA->total);
        $this->assertSame('Tolong dibungkus rapi.', $orderA->note);

        $this->assertSame(500_000, $orderB->subtotal);
        $this->assertSame(25_000, $orderB->commission_amount);
        $this->assertSame(520_000, $orderB->total);

        $this->assertSame(3, $productA->fresh()->stock);
        $this->assertSame(3, $productB->fresh()->stock);
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $orderA->id,
            'status' => OrderStatus::WaitingConfirmation->value,
        ]);
    }

    public function test_checkout_rejects_an_empty_cart(): void
    {
        $customer = User::factory()->create();
        $address = $this->createAddress($customer);

        $response = $this->actingAs($customer)
            ->from(route('customer.checkout.index'))
            ->post(route('customer.checkout.store'), [
                'address_id' => $address->id,
                'shipping_method' => 'jne_reguler',
            ]);

        $response->assertRedirect(route('customer.checkout.index'));
        $response->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_rejects_a_product_that_is_not_publicly_visible(): void
    {
        $customer = User::factory()->create();
        $address = $this->createAddress($customer);
        $product = $this->createProduct(1_000_000, 5, [
            'verification_status' => VerificationStatus::Pending,
        ]);

        CartItem::create(['user_id' => $customer->id, 'product_id' => $product->id, 'quantity' => 1]);

        $response = $this->actingAs($customer)->post(route('customer.checkout.store'), [
            'address_id' => $address->id,
            'shipping_method' => 'jne_reguler',
        ]);

        $response->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_only_processes_selected_cart_items(): void
    {
        $customer = User::factory()->create();
        $address = $this->createAddress($customer);
        $productA = $this->createProduct(1_000_000, 5);
        $productB = $this->createProduct(500_000, 4);

        $itemA = CartItem::create(['user_id' => $customer->id, 'product_id' => $productA->id, 'quantity' => 2]);
        $itemB = CartItem::create(['user_id' => $customer->id, 'product_id' => $productB->id, 'quantity' => 1]);

        $this->actingAs($customer)
            ->post(route('customer.cart.checkout'), ['items' => [$itemA->id]])
            ->assertRedirect(route('customer.checkout.index'))
            ->assertSessionHas('checkout.cart_item_ids', [$itemA->id]);

        $this->actingAs($customer)
            ->get(route('customer.checkout.index'))
            ->assertOk()
            ->assertSee($productA->name)
            ->assertDontSee($productB->name);

        $this->actingAs($customer)->post(route('customer.checkout.store'), [
            'address_id' => $address->id,
            'shipping_method' => 'jne_reguler',
        ]);

        $payment = Payment::query()->firstOrFail();

        // Hanya produk A yang dibeli: Rp2.000.000 + ongkir Rp20.000.
        $this->assertSame(1, $payment->orders()->count());
        $this->assertSame(2_020_000, $payment->amount);
        $this->assertDatabaseHas('order_items', ['product_id' => $productA->id]);
        $this->assertDatabaseMissing('order_items', ['product_id' => $productB->id]);

        $this->assertSame(3, $productA->fresh()->stock);
        $this->assertSame(4, $productB->fresh()->stock);

        $this->assertDatabaseMissing('cart_items', ['id' => $itemA->id]);
        $this->assertDatabaseHas('cart_items', ['id' => $itemB->id]);
        $this->assertNull(session('checkout.cart_item_ids'));
    }

    public function test_cart_checkout_requires_a_valid_selection(): void
    {
        $customer = User::factory()->create();
        $product = $this->createProduct(1_000_000, 5);
        $item = CartItem::create(['user_id' => $customer->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($customer)
            ->from(route('customer.cart.index'))
            ->post(route('customer.cart.checkout'), ['items' => []])
            ->assertRedirect(route('customer.cart.index'))
            ->assertSessionHasErrors('items');

        $otherCustomer = User::factory()->create();
        $foreignItem = CartItem::create(['user_id' => $otherCustomer->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($customer)
            ->from(route('customer.cart.index'))
            ->post(route('customer.cart.checkout'), ['items' => [$foreignItem->id]])
            ->assertRedirect(route('customer.cart.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('cart_items', ['id' => $item->id]);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_customer_can_simulate_payment_and_both_parties_are_notified(): void
    {
        Notification::fake();

        $customer = User::factory()->create();
        $address = $this->createAddress($customer);
        $product = $this->createProduct(1_000_000, 5);

        CartItem::create(['user_id' => $customer->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($customer)->post(route('customer.checkout.store'), [
            'address_id' => $address->id,
            'shipping_method' => 'jne_reguler',
        ]);

        $payment = Payment::query()->firstOrFail();

        $this->actingAs($customer)
            ->post(route('customer.payments.method', $payment), ['method' => 'transfer_bank'])
            ->assertSessionHasNoErrors();

        $payment->refresh();
        $this->assertSame('transfer_bank', $payment->method);
        $this->assertNotNull($payment->gateway_reference);

        $this->actingAs($customer)
            ->post(route('customer.payments.pay', $payment))
            ->assertSessionHasNoErrors();

        $payment->refresh();
        $this->assertSame(PaymentStatus::Paid, $payment->status);
        $this->assertNotNull($payment->paid_at);

        $order = Order::query()->firstOrFail();
        $this->assertSame(OrderStatus::WaitingConfirmation, $order->status);

        Notification::assertSentTo($customer, \App\Notifications\PaymentPaid::class);
        Notification::assertSentTo($product->store->user, \App\Notifications\PaymentPaid::class);
    }

    private function createAddress(User $customer): Address
    {
        return Address::create([
            'user_id' => $customer->id,
            'label' => 'Rumah',
            'recipient_name' => $customer->name,
            'phone' => '081234567890',
            'address_line' => 'Jl. Contoh No. 1',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12140',
            'is_default' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createProduct(int $price, int $stock, array $overrides = []): Product
    {
        $store = Store::factory()->create();

        return Product::factory()->create(array_merge([
            'store_id' => $store->id,
            'price' => $price,
            'stock' => $stock,
            'status' => ProductStatus::Active,
            'verification_status' => VerificationStatus::Approved,
        ], $overrides));
    }
}
