<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(3, true));

        return [
            'seller_id' => Seller::factory()->approved(),
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'sku' => strtoupper(fake()->bothify('SKU-????-####')),
            'description' => implode("\n\n", fake()->paragraphs(2)),
            'price' => fake()->numberBetween(5, 500) * 1000,
            'stock' => fake()->numberBetween(0, 150),
            'weight' => fake()->numberBetween(50, 2000),
            'condition' => 'new',
            'status' => ProductStatus::Pending,
            'rejection_reason' => null,
            'verified_by' => null,
            'verified_at' => null,
            'view_count' => 0,
            'sold_count' => 0,
        ];
    }

    /**
     * Indicate that the product has been approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductStatus::Approved,
            'verified_by' => User::factory()->verifikator(),
            'verified_at' => now()->subDays(fake()->numberBetween(1, 30)),
            'view_count' => fake()->numberBetween(20, 3000),
        ]);
    }

    /**
     * Indicate that the product has been rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductStatus::Rejected,
            'rejection_reason' => 'Foto produk tidak jelas, mohon unggah ulang.',
            'verified_by' => User::factory()->verifikator(),
            'verified_at' => now(),
        ]);
    }

    /**
     * Indicate that the product is still a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductStatus::Draft,
        ]);
    }

    /**
     * Indicate that the product has been deactivated by the seller.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductStatus::Inactive,
        ]);
    }

    /**
     * Indicate that the product is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }

    /**
     * Indicate that the product has been deleted.
     */
    public function trashed(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}
