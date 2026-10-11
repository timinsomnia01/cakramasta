<?php

namespace App\Support\Presenters;

use App\Models\CartItem;
use Illuminate\Support\Collection;

/**
 * Mengubah item keranjang menjadi array siap kirim ke komponen Inertia,
 * dikelompokkan per penjual (satu order = satu penjual).
 */
class CartPresenter
{
    /**
     * Stok yang tersedia untuk item (stok varian bila ada, jika tidak stok produk).
     */
    public static function stock(CartItem $item): int
    {
        return (int) ($item->variant?->stock ?? $item->product->stock);
    }

    /**
     * Apakah item masih bisa dibeli (produk aktif, toko aktif, varian aktif, stok ada).
     */
    public static function isAvailable(CartItem $item): bool
    {
        $product = $item->product;

        if (! $product->isApproved() || ! $product->seller?->isApproved()) {
            return false;
        }

        if ($item->product_variant_id && (! $item->variant || ! $item->variant->is_active)) {
            return false;
        }

        return self::stock($item) >= 1;
    }

    /**
     * Apakah item bisa dicheckout dengan jumlah saat ini.
     */
    public static function isPurchasable(CartItem $item): bool
    {
        return self::isAvailable($item) && self::stock($item) >= $item->quantity;
    }

    /**
     * Relasi yang perlu dimuat sebelum memanggil groups().
     *
     * @return list<string>
     */
    public static function relations(): array
    {
        return ['product.seller', 'product.primaryImage', 'product.category.parent', 'variant'];
    }

    /**
     * @param  Collection<int, CartItem>  $items
     * @return list<array<string, mixed>>
     */
    public static function groups(Collection $items): array
    {
        return $items
            ->groupBy(fn (CartItem $item) => $item->product->seller_id)
            ->map(fn (Collection $group) => [
                'seller' => [
                    'id' => $group->first()->product->seller->id,
                    'storeName' => $group->first()->product->seller->store_name,
                    'slug' => $group->first()->product->seller->slug,
                ],
                'items' => $group->map(fn (CartItem $item) => [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'unitPrice' => $item->unitPrice(),
                    'subtotal' => $item->subtotal(),
                    'stock' => self::stock($item),
                    'available' => self::isAvailable($item),
                    'purchasable' => self::isPurchasable($item),
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'image' => ProductPresenter::imageUrl($item->product->primaryImage?->path),
                        'href' => ProductPresenter::href($item->product),
                    ],
                    'variant' => $item->variant ? ['id' => $item->variant->id, 'name' => $item->variant->name] : null,
                ])->values(),
                'subtotal' => (int) $group->filter(fn (CartItem $item) => self::isPurchasable($item))->sum(fn (CartItem $item) => $item->subtotal()),
            ])
            ->values()
            ->all();
    }
}
