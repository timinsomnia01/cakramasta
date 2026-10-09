<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $verifikator = User::where('email', 'verifikator@umart.test')->first();
        $sellers = SellerProfile::approved()->get();
        $buyers = User::where('role', 'user')->whereDoesntHave('sellerProfile')->get();

        // [nama, kategori, harga dasar, status, varian [nama => [harga, stok]]]
        $items = [
            ['Lampu LED Neon Flex 220V per Meter', 'Lampu & Pencahayaan', 5800, 'approved', []],
            ['Lampu Tumblr Jumbo Bohlam 30 Watt', 'Lampu & Pencahayaan', 9331, 'approved', []],
            ['Laptop Bekas Lenovo ThinkPad T480', 'Laptop', 3850000, 'approved', [
                'RAM 8GB / SSD 256GB' => [3850000, 3],
                'RAM 16GB / SSD 512GB' => [4600000, 2],
            ]],
            ['Keyboard Mechanical Hot-swap 75%', 'Keyboard & Mouse', 450000, 'approved', [
                'Switch Blue' => [450000, 10],
                'Switch Red' => [450000, 8],
                'Switch Brown' => [465000, 5],
            ]],
            ['Casing HP Silikon Anti Crack', 'Casing & Pelindung', 25000, 'approved', [
                'Hitam' => [25000, 40],
                'Biru' => [25000, 25],
                'Transparan' => [27000, 30],
            ]],
            ['Kaos Polos Cotton Combed 30s', 'Kaos Pria', 55000, 'approved', [
                'Hitam - M' => [55000, 20], 'Hitam - L' => [55000, 20], 'Hitam - XL' => [58000, 15],
                'Putih - M' => [55000, 20], 'Putih - L' => [55000, 20], 'Putih - XL' => [58000, 15],
            ]],
            ['Kemeja Flanel Kotak Lengan Panjang', 'Kemeja Pria', 120000, 'approved', [
                'M' => [120000, 12], 'L' => [120000, 12], 'XL' => [125000, 8],
            ]],
            ['Kain Batik Tulis Malang 2 Meter', 'Kain Batik', 175000, 'approved', []],
            ['Kain Satin Velvet Premium per Meter', 'Kain Satin', 65000, 'approved', [
                'Maroon' => [65000, 50], 'Navy' => [65000, 50],
            ]],
            ['Powerbank 10000mAh Fast Charging', 'Powerbank', 135000, 'pending', []],
            ['Dress Midi Floral', 'Dress', 145000, 'pending', []],
            ['Mesin Jahit Portable Mini', 'Mesin Jahit & Aksesoris', 275000, 'rejected', []],
        ];

        foreach ($items as $i => [$name, $categoryName, $price, $status, $variants]) {
            $category = Category::where('name', $categoryName)->firstOrFail();
            $seller = $sellers[$i % $sellers->count()];
            $status = ProductStatus::from($status);

            $product = Product::create([
                'seller_profile_id' => $seller->id,
                'category_id' => $category->id,
                'name' => $name,
                'description' => "{$name}\n\nKondisi baik, siap kirim/COD area kampus. Silakan chat penjual untuk info lebih lanjut.",
                'price' => $price,
                'stock' => $variants ? collect($variants)->sum(fn ($v) => $v[1]) : rand(5, 50),
                'weight_gram' => rand(100, 2000),
                'status' => $status,
                'rejection_note' => $status === ProductStatus::Rejected
                    ? 'Foto produk kurang jelas dan deskripsi belum lengkap.' : null,
                'verified_by' => in_array($status, [ProductStatus::Approved, ProductStatus::Rejected]) ? $verifikator->id : null,
                'verified_at' => in_array($status, [ProductStatus::Approved, ProductStatus::Rejected]) ? now()->subDays(rand(1, 7)) : null,
                'view_count' => $status === ProductStatus::Approved ? rand(20, 500) : 0,
            ]);

            // Gambar utama + gambar pendukung (carousel)
            foreach (range(1, 4) as $n) {
                $product->images()->create([
                    'path' => "seed/products/{$product->slug}-{$n}.jpg",
                    'is_primary' => $n === 1,
                    'sort_order' => $n,
                ]);
            }

            // Varian
            foreach ($variants as $variantName => [$vPrice, $vStock]) {
                $product->variants()->create([
                    'name' => $variantName,
                    'sku' => strtoupper(substr(md5($product->slug.$variantName), 0, 8)),
                    'price' => $vPrice,
                    'stock' => $vStock,
                ]);
            }

            // Like & simpan (hanya produk yang tayang)
            if ($status === ProductStatus::Approved) {
                $product->likedBy()->attach($buyers->random(rand(1, $buyers->count()))->pluck('id'));
                $product->savedBy()->attach($buyers->random(rand(1, $buyers->count()))->pluck('id'));
                $product->syncInteractionCounts();
            }
        }
    }
}
