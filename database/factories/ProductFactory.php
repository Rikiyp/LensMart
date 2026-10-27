<?php

namespace Database\Factories;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Enums\VerificationStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'store_id' => Store::factory(),
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'name' => $name,
            'slug' => Product::generateSlug($name).'-'.fake()->unique()->numerify('###'),
            'model_name' => strtoupper(fake()->bothify('MDL-####')),
            'description' => fake()->paragraph(),
            'price' => fake()->numberBetween(500_000, 30_000_000),
            'stock' => fake()->numberBetween(1, 20),
            'condition' => ProductCondition::New,
            'status' => ProductStatus::Active,
            'verification_status' => VerificationStatus::Approved,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => VerificationStatus::Pending,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
            'status' => ProductStatus::OutOfStock,
        ]);
    }
}
