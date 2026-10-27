<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PayoutStatus;
use App\Enums\ProductStatus;
use App\Enums\VerificationStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_must_advance_order_status_one_step_at_a_time(): void
    {
        [$customer, $store, $product] = $this->makeActors();
        $order = $this->createOrder($customer, $store, $product, OrderStatus::WaitingConfirmation);

        $this->actingAs($store->user)
            ->post(route('partner.orders.status', $order), ['action' => 'advance'])
            ->assertRedirect();

        $this->assertSame(OrderStatus::Confirmed, $order->fresh()->status);

        $this->actingAs($store->user)
            ->post(route('partner.orders.status', $order), ['action' => 'advance'])
            ->assertRedirect();

        $this->assertSame(OrderStatus::Packed, $order->fresh()->status);

        // Status dikirim wajib menyertakan nomor resi.
        $this->actingAs($store->user)
            ->from(route('partner.orders.show', $order))
            ->post(route('partner.orders.status', $order), ['action' => 'advance'])
            ->assertSessionHasErrors('tracking_number');

        $this->assertSame(OrderStatus::Packed, $order->fresh()->status);

        $this->actingAs($store->user)
            ->post(route('partner.orders.status', $order), [
                'action' => 'advance',
                'tracking_number' => 'JNE-1234567890',
            ])
            ->assertRedirect();

        $order->refresh();
        $this->assertSame(OrderStatus::Shipped, $order->status);
        $this->assertSame('JNE-1234567890', $order->tracking_number);

        // Partner tidak boleh menandai selesai; hanya pelanggan yang dapat.
        $this->actingAs($store->user)
            ->from(route('partner.orders.show', $order))
            ->post(route('partner.orders.status', $order), ['action' => 'advance'])
            ->assertSessionHasErrors('status');

        $this->assertSame(OrderStatus::Shipped, $order->fresh()->status);
        $this->assertSame(4, $order->histories()->count());
    }

    public function test_partner_cannot_update_another_stores_order(): void
    {
        [$customer, $store, $product] = $this->makeActors();
        $otherStore = Store::factory()->create();
        $order = $this->createOrder($customer, $store, $product, OrderStatus::Confirmed);

        $this->actingAs($otherStore->user)
            ->post(route('partner.orders.status', $order), ['action' => 'advance'])
            ->assertForbidden();

        $this->assertSame(OrderStatus::Confirmed, $order->fresh()->status);
    }

    public function test_customer_confirmation_completes_order_and_creates_payout(): void
    {
        Notification::fake();

        [$customer, $store, $product] = $this->makeActors();
        $order = $this->createOrder($customer, $store, $product, OrderStatus::Shipped);

        $this->actingAs($customer)
            ->post(route('customer.orders.confirm', $order))
            ->assertRedirect();

        $order->refresh();
        $this->assertSame(OrderStatus::Completed, $order->status);
        $this->assertNotNull($order->completed_at);

        $payout = $order->payout()->firstOrFail();
        $this->assertSame($store->id, $payout->store_id);
        $this->assertSame(950_000, $payout->amount);
        $this->assertSame(PayoutStatus::Pending, $payout->status);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => OrderStatus::Completed->value,
            'changed_by' => $customer->id,
        ]);

        Notification::assertSentTo($store->user, \App\Notifications\OrderStatusUpdated::class);
    }

    public function test_customer_cannot_confirm_receipt_before_order_is_shipped(): void
    {
        [$customer, $store, $product] = $this->makeActors();
        $order = $this->createOrder($customer, $store, $product, OrderStatus::Confirmed);

        $this->actingAs($customer)
            ->post(route('customer.orders.confirm', $order))
            ->assertForbidden();

        $this->assertSame(OrderStatus::Confirmed, $order->fresh()->status);
        $this->assertDatabaseCount('payouts', 0);
    }

    public function test_cancelling_order_restores_stock_and_is_only_allowed_once(): void
    {
        [$customer, $store, $product] = $this->makeActors();
        $product->update(['stock' => 3]);
        $order = $this->createOrder($customer, $store, $product, OrderStatus::WaitingConfirmation, quantity: 2);

        $this->actingAs($customer)
            ->post(route('customer.orders.cancel', $order), ['note' => 'Salah pilih lensa.'])
            ->assertRedirect();

        $order->refresh();
        $this->assertSame(OrderStatus::Cancelled, $order->status);
        $this->assertSame(5, $product->fresh()->stock);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => OrderStatus::Cancelled->value,
            'note' => 'Salah pilih lensa.',
        ]);

        // Pembatalan kedua tidak diizinkan karena status sudah final.
        $this->actingAs($customer)
            ->post(route('customer.orders.cancel', $order))
            ->assertForbidden();

        $this->assertSame(5, $product->fresh()->stock);
    }

    /**
     * @return array{0: User, 1: Store, 2: Product}
     */
    private function makeActors(): array
    {
        $customer = User::factory()->create();
        $store = Store::factory()->create();
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'price' => 1_000_000,
            'stock' => 10,
            'status' => ProductStatus::Active,
            'verification_status' => VerificationStatus::Approved,
        ]);

        return [$customer, $store, $product];
    }

    private function createOrder(
        User $customer,
        Store $store,
        Product $product,
        OrderStatus $status,
        int $quantity = 1,
        PaymentStatus $paymentStatus = PaymentStatus::Paid,
    ): Order {
        $subtotal = $product->price * $quantity;
        $commission = (int) round($subtotal * 0.05);
        $shippingCost = 20_000;

        $payment = Payment::create([
            'code' => Payment::generateCode(),
            'customer_id' => $customer->id,
            'amount' => $subtotal + $shippingCost,
            'method' => 'transfer_bank',
            'status' => $paymentStatus,
            'gateway' => 'simulated',
            'gateway_reference' => 'VA-TEST-1234',
            'paid_at' => $paymentStatus === PaymentStatus::Paid ? now() : null,
            'expires_at' => now()->addDay(),
        ]);

        $order = Order::create([
            'order_number' => Order::generateNumber(),
            'payment_id' => $payment->id,
            'customer_id' => $customer->id,
            'store_id' => $store->id,
            'status' => $status,
            'recipient_name' => $customer->name,
            'recipient_phone' => '081234567890',
            'shipping_address' => 'Jl. Contoh No. 1, Jakarta Selatan, DKI Jakarta 12140',
            'shipping_method' => 'jne_reguler',
            'shipping_cost' => $shippingCost,
            'subtotal' => $subtotal,
            'commission_amount' => $commission,
            'total' => $subtotal + $shippingCost,
            'tracking_number' => $status === OrderStatus::Shipped ? 'JNE-TEST-0001' : null,
            'completed_at' => $status === OrderStatus::Completed ? now() : null,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
        ]);

        $order->histories()->create([
            'status' => OrderStatus::WaitingConfirmation,
            'note' => 'Pesanan dibuat.',
            'changed_by' => $customer->id,
        ]);

        return $order;
    }
}
