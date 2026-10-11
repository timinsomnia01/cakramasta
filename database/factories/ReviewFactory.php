<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\Support\CatalogData;
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
        $rating = fake()->randomElement([5, 5, 5, 5, 4, 4, 3]);

        return [
            'product_id' => Product::factory()->approved(),
            'user_id' => User::factory(),
            'order_item_id' => null,
            'rating' => $rating,
            'comment' => str_replace('{produk}', 'Barangnya', fake()->randomElement(CatalogData::reviewComments()[$rating])),
            'images' => null,
        ];
    }

    /**
     * Set the rating (and a matching comment).
     */
    public function rating(int $rating): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => $rating,
            'comment' => str_replace('{produk}', 'Barangnya', fake()->randomElement(CatalogData::reviewComments()[$rating])),
        ]);
    }
}
