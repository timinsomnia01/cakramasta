<?php

namespace App\Actions\Catalog;

use Illuminate\Database\Eloquent\Builder;

class FilterProducts
{
    /**
     * Pilihan urutan produk.
     *
     * @var list<string>
     */
    public const SORTS = ['terbaru', 'terlaris', 'populer', 'termurah', 'termahal'];

    /**
     * Terapkan pencarian nama/deskripsi, rentang harga, dan urutan pada query produk.
     *
     * @param  Builder<\App\Models\Product>  $query
     * @param  array{q?: string|null, min_price?: int|null, max_price?: int|null, sort?: string|null}  $filters
     * @return Builder<\App\Models\Product>
     */
    public function handle(Builder $query, array $filters): Builder
    {
        $keyword = trim((string) ($filters['q'] ?? ''));

        $query
            ->when($keyword !== '', function (Builder $query) use ($keyword) {
                $query->where(fn (Builder $query) => $query
                    ->where('products.name', 'like', "%{$keyword}%")
                    ->orWhere('products.description', 'like', "%{$keyword}%"));
            })
            ->when($filters['min_price'] ?? null, fn (Builder $query, $price) => $query->where('products.price', '>=', $price))
            ->when($filters['max_price'] ?? null, fn (Builder $query, $price) => $query->where('products.price', '<=', $price));

        match ($filters['sort'] ?? 'terbaru') {
            'terlaris' => $query->orderByDesc('products.sold_count'),
            'populer' => $query->orderByDesc('products.view_count'),
            'termurah' => $query->orderBy('products.price'),
            'termahal' => $query->orderByDesc('products.price'),
            default => null,
        };
        if (!empty($filters['rating'])) {
            // Karena ProductPresenter::cardQuery() biasanya sudah menggunakan withAvg('reviews', 'rating')
            // kita bisa menggunakan having() pada query.
            $rating = (int) $filters['rating'];

            // Memfilter produk yang rata-rata rating-nya sama dengan atau lebih besar dari rating yang dipilih
            // (Atau jika Anda mau tepat sama, gunakan '=')
            $query->having('reviews_avg_rating', '>=', $rating);
        }
        return $query->orderByDesc('products.id');
    }
}
