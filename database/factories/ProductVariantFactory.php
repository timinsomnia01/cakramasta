<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => fake()->randomElement(['Hitam', 'Putih', 'Navy', 'Merah Maroon', 'Abu-abu', 'S', 'M', 'L', 'XL']),
            'sku' => strtoupper(fake()->bothify('VAR-????-###')),
            'price' => fake()->numberBetween(5, 500) * 1000,
            'stock' => fake()->numberBetween(0, 60),
            'image' => null,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the variant is disabled.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
