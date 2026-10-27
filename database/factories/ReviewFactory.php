<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id'  => Product::factory(),
            'customer_id' => User::factory(),
            'order_id'    => Order::factory(),
            'rating'      => $this->faker->numberBetween(1, 5),
            'comment'     => $this->faker->paragraph(),
            'store_reply' => null,
            'replied_at'  => null,
            'is_hidden'   => false,
        ];
    }

    /** Ulasan yang disembunyikan oleh admin. */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_hidden' => true,
        ]);
    }

    /** Ulasan yang sudah dibalas oleh toko. */
    public function withReply(): static
    {
        return $this->state(fn (array $attributes) => [
            'store_reply' => $this->faker->sentence(),
            'replied_at'  => now(),
        ]);
    }
}
