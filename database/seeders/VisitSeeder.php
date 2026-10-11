<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;

/**
 * Data kunjungan 30 hari terakhir untuk statistik dashboard admin.
 * Hari kerja dibuat lebih ramai daripada akhir pekan.
 */
class VisitSeeder extends Seeder
{
    public function run(): void
    {
        $buyerIds = User::role(UserRole::User)->pluck('id')->all();
        $products = Product::approved()->with('category.parent')->get();
        $categories = $products->map(fn (Product $product) => $product->category->parent?->slug ?? $product->category->slug)->unique()->values()->all();
        $productList = $products->all();
        $base = rtrim((string) config('app.url'), '/');

        $rows = [];

        for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
            $day = now()->subDays($daysAgo);
            $count = fake()->numberBetween(25, 90);

            if ($day->isWeekend()) {
                $count = (int) round($count * 0.6);
            }

            for ($i = 0; $i < $count; $i++) {
                $at = $day->copy()->setTime(fake()->numberBetween(7, 23), fake()->numberBetween(0, 59), fake()->numberBetween(0, 59));
                $product = fake()->boolean(55) ? fake()->randomElement($productList) : null;

                $url = match (true) {
                    $product !== null => $base.'/kategori/'.($product->category->parent?->slug ?? $product->category->slug).'/produk/'.$product->slug,
                    fake()->boolean(50) => $base.'/kategori/'.fake()->randomElement($categories),
                    default => $base,
                };

                $rows[] = [
                    'user_id' => fake()->boolean(25) ? fake()->randomElement($buyerIds) : null,
                    'product_id' => $product?->id,
                    'session_id' => fake()->uuid(),
                    'ip_address' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                    'url' => $url,
                    'visited_date' => $at->toDateString(),
                    'created_at' => $at,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            Visit::insert($chunk);
        }
    }
}
