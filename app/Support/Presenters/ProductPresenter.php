<?php

namespace App\Support\Presenters;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

/**
 * Mengubah model produk menjadi array siap kirim ke komponen Inertia.
 */
class ProductPresenter
{
    /**
     * Query dasar produk yang boleh tampil di katalog publik: produk disetujui milik toko aktif,
     * lengkap dengan relasi yang dibutuhkan kartu produk.
     *
     * @return Builder<Product>
     */
    public static function cardQuery(): Builder
    {
        return Product::query()
            ->approved()
            ->whereHas('seller', fn (Builder $query) => $query->approved())
            ->with(['primaryImage', 'category.parent', 'seller:id,store_name,slug'])
            ->withCount(['reviews', 'variants'])
            ->withAvg('reviews', 'rating');
    }

    /**
     * Data kartu produk (beranda, katalog, hasil pencarian).
     *
     * @return array<string, mixed>
     */
    public static function card(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
            'hasVariants' => ($product->variants_count ?? 0) > 0,
            'image' => self::imageUrl($product->primaryImage?->path),
            'rating' => isset($product->reviews_avg_rating) ? round((float) $product->reviews_avg_rating, 1) : null,
            'reviewsCount' => $product->reviews_count ?? 0,
            'soldCount' => $product->sold_count,
            'category' => [
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ],
            'storeName' => $product->seller?->store_name,
            'href' => self::href($product),
        ];
    }

    /**
     * URL halaman detail: /kategori/{kategori utama}/produk/{produk}.
     */
    public static function href(Product $product): string
    {
        return route('products.show', [
            'category' => self::rootCategory($product)->slug,
            'product' => $product->slug,
        ], absolute: false);
    }

    /**
     * Kategori utama dari sebuah produk (produk disimpan pada sub kategori).
     */
    public static function rootCategory(Product $product): Category
    {
        return $product->category->parent ?? $product->category;
    }

    /**
     * URL publik gambar pada disk "public".
     */
    public static function imageUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }

    /**
     * Baris produk untuk tabel manajemen (penjual / admin).
     *
     * @return array<string, mixed>
     */
    public static function row(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'price' => $product->price,
            'stock' => $product->stock,
            'status' => $product->status->value,
            'statusLabel' => $product->status->label(),
            'rejectionReason' => $product->rejection_reason,
            'image' => self::imageUrl($product->primaryImage?->path),
            'category' => $product->category?->name,
            'storeName' => $product->relationLoaded('seller') ? $product->seller?->store_name : null,
            'viewCount' => $product->view_count,
            'soldCount' => $product->sold_count,
            'createdAt' => $product->created_at?->toISOString(),
        ];
    }

    /**
     * Data formulir ubah produk (penjual / admin). Relasi images dan variants harus sudah dimuat.
     *
     * @return array<string, mixed>
     */
    public static function form(Product $product): array
    {
        return [
            'id' => $product->id,
            'sellerId' => $product->seller_id,
            'name' => $product->name,
            'sku' => $product->sku,
            'category_id' => $product->category_id,
            'description' => $product->description,
            'price' => $product->price,
            'stock' => $product->stock,
            'weight' => $product->weight,
            'condition' => $product->condition,
            'status' => $product->status->value,
            'statusLabel' => $product->status->label(),
            'rejectionReason' => $product->rejection_reason,
            'images' => $product->images->map(fn ($image) => [
                'id' => $image->id,
                'url' => self::imageUrl($image->path),
                'isPrimary' => $image->is_primary,
            ])->values(),
            'variants' => $product->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'name' => $variant->name,
                'sku' => $variant->sku,
                'price' => $variant->price,
                'stock' => $variant->stock,
                'is_active' => $variant->is_active,
            ])->values(),
        ];
    }
}
