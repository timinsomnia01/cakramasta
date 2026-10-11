<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory()->approved(),
            'product_variant_id' => null,
            'product_name' => Str::title(fake()->words(3, true)),
            'variant_name' => null,
            'product_image' => null,
            'price' => fake()->numberBetween(5, 300) * 1000,
            'quantity' => fake()->numberBetween(1, 3),
            'subtotal' => fn (array $attributes) => $attributes['price'] * $attributes['quantity'],
        ];
    }

    /**
     * Copy the snapshot fields from the given product.
     */
    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
        ]);
    }
}
