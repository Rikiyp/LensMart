<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Happy path
    // -------------------------------------------------------------------------

    public function test_customer_can_review_a_completed_order_product(): void
    {
        [$customer, $product, $order] = $this->makeCompletedOrder();

        $this->actingAs($customer)
            ->post(route('customer.reviews.store', [$order, $product]), [
                'rating'  => 5,
                'comment' => 'Kamera sangat bagus!',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('reviews', [
            'order_id'    => $order->id,
            'product_id'  => $product->id,
            'customer_id' => $customer->id,
            'rating'      => 5,
        ]);
    }

    public function test_customer_can_edit_their_own_review(): void
    {
        [$customer, $product, $order] = $this->makeCompletedOrder();

        $review = Review::create([
            'product_id'  => $product->id,
            'customer_id' => $customer->id,
            'order_id'    => $order->id,
            'rating'      => 3,
            'comment'     => 'Lumayan.',
        ]);

        $this->actingAs($customer)
            ->put(route('customer.reviews.update', $review), [
                'rating'  => 4,
                'comment' => 'Ternyata bagus juga.',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'rating' => 4]);
    }

    // -------------------------------------------------------------------------
    // Authorization & business rules
    // -------------------------------------------------------------------------

    public function test_guest_cannot_submit_a_review(): void
    {
        [$customer, $product, $order] = $this->makeCompletedOrder();

        $this->post(route('customer.reviews.store', [$order, $product]), ['rating' => 5])
            ->assertRedirect(route('login'));
    }

    public function test_customer_cannot_review_someone_elses_order(): void
    {
        [$customer, $product, $order] = $this->makeCompletedOrder();

        $this->actingAs(User::factory()->create())
            ->post(route('customer.reviews.store', [$order, $product]), ['rating' => 5])
            ->assertForbidden();
    }

    public function test_customer_cannot_review_a_non_completed_order(): void
    {
        [$customer, $product, $order] = $this->makeOrderWithStatus(OrderStatus::Shipped);

        $this->actingAs($customer)
            ->post(route('customer.reviews.store', [$order, $product]), ['rating' => 5])
            ->assertForbidden();
    }

    public function test_customer_cannot_submit_a_duplicate_review(): void
    {
        [$customer, $product, $order] = $this->makeCompletedOrder();

        Review::create([
            'product_id'  => $product->id,
            'customer_id' => $customer->id,
            'order_id'    => $order->id,
            'rating'      => 4,
            'comment'     => 'Sudah diulas.',
        ]);

        // Percobaan ulasan kedua harus ditolak (403).
        $this->actingAs($customer)
            ->post(route('customer.reviews.store', [$order, $product]), ['rating' => 5])
            ->assertForbidden();
    }

    public function test_review_rating_must_be_between_1_and_5(): void
    {
        [$customer, $product, $order] = $this->makeCompletedOrder();

        $this->actingAs($customer)
            ->post(route('customer.reviews.store', [$order, $product]), ['rating' => 6])
            ->assertSessionHasErrors('rating');

        $this->actingAs($customer)
            ->post(route('customer.reviews.store', [$order, $product]), ['rating' => 0])
            ->assertSessionHasErrors('rating');
    }

    public function test_another_customer_cannot_edit_a_review(): void
    {
        [$customer, $product, $order] = $this->makeCompletedOrder();

        $review = Review::create([
            'product_id'  => $product->id,
            'customer_id' => $customer->id,
            'order_id'    => $order->id,
            'rating'      => 3,
            'comment'     => 'Ulasan asli.',
        ]);

        $this->actingAs(User::factory()->create())
            ->put(route('customer.reviews.update', $review), ['rating' => 1, 'comment' => 'Diubah'])
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** @return array{0: User, 1: Product, 2: Order} */
    private function makeCompletedOrder(): array
    {
        return $this->makeOrderWithStatus(OrderStatus::Completed, true);
    }

    /** @return array{0: User, 1: Product, 2: Order} */
    private function makeOrderWithStatus(OrderStatus $status, bool $isCompleted = false): array
    {
        $customer = User::factory()->create();
        $product  = Product::factory()->create();
        $payment  = Payment::factory()->create(['customer_id' => $customer->id]);
        $store    = Store::find($product->store_id);

        $order = Order::factory()->create([
            'customer_id'  => $customer->id,
            'store_id'     => $store->id,
            'payment_id'   => $payment->id,
            'status'       => $status,
            'completed_at' => $isCompleted ? now() : null,
        ]);

        $order->items()->create([
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'price'        => $product->price,
            'quantity'     => 1,
            'subtotal'     => $product->price,
        ]);

        return [$customer, $product, $order];
    }
}
