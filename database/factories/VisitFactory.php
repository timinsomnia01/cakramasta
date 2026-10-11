<?php

namespace Database\Factories;

use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visit>
 */
class VisitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = now()->subDays(fake()->numberBetween(0, 29))->setTime(fake()->numberBetween(7, 22), fake()->numberBetween(0, 59));

        return [
            'user_id' => null,
            'product_id' => null,
            'session_id' => fake()->uuid(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'url' => url('/'),
            'visited_date' => $date->toDateString(),
            'created_at' => $date,
        ];
    }
}
