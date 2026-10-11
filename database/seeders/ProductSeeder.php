<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Database\Seeders\Support\CatalogData;
use Database\Seeders\Support\PlaceholderImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Produk, gambar carousel, dan varian. Sebagian produk sengaja berstatus
 * pending / rejected / draft / inactive supaya halaman verifikasi tidak kosong.
 */
class ProductSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private const IMAGE_LABELS = ['Tampak Depan', 'Detail Bahan', 'Tampak Samping', 'Kemasan', 'Ukuran'];

    public function run(): void
    {
        $verifiers = User::role(UserRole::Verifikator)->get()->all();
        $rejectionReasons = CatalogData::productRejectionReasons();

        // slug kategori utama => daftar slug toko yang menjualnya
        $storesByCategory = collect(CatalogData::sellers())
            ->filter(fn (array $seller) => $seller['category'] !== null)
            ->groupBy('category')
            ->map(fn ($group) => $group->map(fn (array $seller) => Str::slug($seller['store']))->all());

        $counter = 0;

        foreach (CatalogData::categories() as $category) {
            $sellers = Seller::approved()
                ->whereIn('slug', $storesByCategory[$category['slug']])
                ->orderBy('id')
                ->get()
                ->values();

            foreach ($category['subs'] as $sub) {
                $subCategory = Category::where('slug', Str::slug($sub['name']))->firstOrFail();

                foreach ($sub['products'] as [$name, $price, $weight, $description, $variants]) {
                    /** @var Seller $seller */
                    $seller = $sellers[$counter % $sellers->count()];
                    $status = $this->statusFor($counter);
                    $slug = Str::slug($name);
                    $createdAt = now()->subDays(fake()->numberBetween(20, 120))->subMinutes(fake()->numberBetween(0, 1000));
                    $reviewed = in_array($status, [ProductStatus::Approved, ProductStatus::Rejected, ProductStatus::Inactive], true);

                    $variantStocks = $variants === null
                        ? []
                        : array_map(fn () => fake()->numberBetween(5, 60), $variants);

                    $product = Product::factory()->create([
                        'seller_id' => $seller->id,
                        'category_id' => $subCategory->id,
                        'name' => $name,
                        'slug' => $slug,
                        'sku' => sprintf('UMM-%04d', $counter + 1),
                        'description' => $this->description($description, $weight, $seller),
                        'price' => $variants === null ? $price : min($variants),
                        'stock' => $variants === null ? fake()->numberBetween(8, 150) : array_sum($variantStocks),
                        'weight' => $weight,
                        'condition' => 'new',
                        'status' => $status,
                        'rejection_reason' => $status === ProductStatus::Rejected
                            ? $rejectionReasons[$counter % count($rejectionReasons)]
                            : null,
                        'verified_by' => $reviewed ? fake()->randomElement($verifiers)->id : null,
                        'verified_at' => $reviewed ? $createdAt->copy()->addHours(fake()->numberBetween(4, 60)) : null,
                        'view_count' => $status === ProductStatus::Approved ? fake()->numberBetween(60, 4200) : 0,
                        'sold_count' => 0,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);

                    $this->createImages($product, $seller);
                    $this->createVariants($product, $variants, $variantStocks);

                    $counter++;
                }
            }
        }
    }

    /**
     * Sebaran status: sebagian besar disetujui, sisanya menunggu / ditolak / draft / nonaktif.
     */
    private function statusFor(int $position): ProductStatus
    {
        return match (true) {
            $position % 19 === 7 => ProductStatus::Rejected,
            $position % 11 === 5 => ProductStatus::Pending,
            $position % 29 === 11 => ProductStatus::Draft,
            $position % 23 === 3 => ProductStatus::Inactive,
            default => ProductStatus::Approved,
        };
    }

    private function description(string $text, int $weight, Seller $seller): string
    {
        return $text
            ."\n\nSpesifikasi:\n- Kondisi: Baru\n- Berat: ".number_format($weight, 0, ',', '.')." gram\n- Dikirim dari: Kota Malang"
            ."\n\nInformasi pengiriman: pesanan dikirim maksimal 2x24 jam setelah pembayaran terverifikasi. "
            ."Bisa COD atau ambil di tempat untuk area Kota Malang, silakan hubungi {$seller->store_name}."
            ."\n\nCatatan: warna produk dapat sedikit berbeda dengan foto karena pencahayaan.";
    }

    /**
     * 3 sampai 5 gambar: gambar pertama menjadi gambar utama, sisanya gambar pendukung carousel.
     */
    private function createImages(Product $product, Seller $seller): void
    {
        $total = fake()->numberBetween(3, 5);

        for ($i = 0; $i < $total; $i++) {
            $product->images()->create([
                'path' => PlaceholderImage::public(
                    "products/{$product->slug}-".($i + 1).'.svg',
                    $product->name,
                    $i,
                    $i === 0 ? $seller->store_name : self::IMAGE_LABELS[$i],
                ),
                'is_primary' => $i === 0,
                'sort_order' => $i,
            ]);
        }
    }

    /**
     * @param  array<string, int>|null  $variants
     * @param  array<string, int>  $variantStocks
     */
    private function createVariants(Product $product, ?array $variants, array $variantStocks): void
    {
        if ($variants === null) {
            return;
        }

        $number = 1;

        foreach ($variants as $variantName => $variantPrice) {
            $product->variants()->create([
                'name' => $variantName,
                'sku' => sprintf('%s-V%02d', $product->sku, $number++),
                'price' => $variantPrice,
                'stock' => $variantStocks[$variantName],
                'is_active' => true,
            ]);
        }
    }
}
