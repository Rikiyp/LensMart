<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_number'     => 'LM-'.now()->format('ymd').'-'.strtoupper(bin2hex(random_bytes(3))),
            'payment_id'       => Payment::factory(),
            'customer_id'      => User::factory(),
            'store_id'         => Store::factory(),
            'status'           => OrderStatus::WaitingConfirmation,
            'recipient_name'   => $this->faker->name(),
            'recipient_phone'  => '08'.str_pad((string) $this->faker->numberBetween(100_000_000, 999_999_999), 10, '0', STR_PAD_LEFT),
            'shipping_address' => $this->faker->address(),
            'shipping_method'  => 'jne_reguler',
            'shipping_cost'    => 20_000,
            'subtotal'         => 1_000_000,
            'commission_amount'=> 50_000,
            'total'            => 1_020_000,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => OrderStatus::Completed,
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Cancelled,
        ]);
    }
}
