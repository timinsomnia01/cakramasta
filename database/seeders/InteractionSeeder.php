<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Like, simpan (wishlist), dan isi keranjang pembeli.
 */
class InteractionSeeder extends Seeder
{
    public function run(): void
    {
        $buyerIds = User::role(UserRole::User)
            ->whereDoesntHave('seller')
            ->pluck('id')
            ->all();

        $products = Product::approved()->with('variants')->get();

        foreach ($products as $product) {
            $likes = fake()->numberBetween(0, 12);
            $saves = fake()->numberBetween(0, 8);

            if ($likes > 0) {
                $product->likers()->attach(fake()->randomElements($buyerIds, $likes));
            }

            if ($saves > 0) {
                $product->savers()->attach(fake()->randomElements($buyerIds, $saves));
            }
        }

        // Beberapa pembeli punya barang di keranjang.
        $inStock = $products->where('stock', '>', 0)->values();

        foreach (fake()->randomElements($buyerIds, 7) as $buyerId) {
            foreach (fake()->randomElements($inStock->all(), fake()->numberBetween(1, 3)) as $product) {
                $variant = $product->variants->isNotEmpty()
                    ? fake()->randomElement($product->variants->all())
                    : null;

                CartItem::factory()->create([
                    'user_id' => $buyerId,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'quantity' => fake()->numberBetween(1, 3),
                ]);
            }
        }
    }
}
