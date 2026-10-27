<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code'        => 'PAY-'.now()->format('ymd').'-'.strtoupper(bin2hex(random_bytes(3))),
            'customer_id' => User::factory(),
            'amount'      => $this->faker->numberBetween(100_000, 10_000_000),
            'method'      => null,
            'status'      => PaymentStatus::Unpaid,
            'gateway'     => 'simulated',
            'expires_at'  => now()->addHours(24),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'  => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'     => PaymentStatus::Expired,
            'expires_at' => now()->subHours(1),
        ]);
    }
}
