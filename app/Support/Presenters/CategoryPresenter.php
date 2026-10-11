<?php

namespace App\Support\Presenters;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * Mengubah model kategori menjadi array siap kirim ke komponen Inertia.
 */
class CategoryPresenter
{
    /**
     * Kartu seluruh kategori utama beserta jumlah produk aktif (termasuk sub kategorinya).
     *
     * @return list<array<string, mixed>>
     */
    public static function roots(): array
    {
        $counts = Product::query()
            ->approved()
            ->whereHas('seller', fn ($query) => $query->approved())
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->groupBy('categories.parent_id')
            ->select('categories.parent_id', DB::raw('count(*) as total'))
            ->pluck('total', 'parent_id');

        return Category::query()
            ->root()
            ->active()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image' => ProductPresenter::imageUrl($category->image),
                'productCount' => (int) ($counts[$category->id] ?? 0),
                'href' => route('categories.show', $category, absolute: false),
            ])
            ->all();
    }

    /**
     * Pilihan sub kategori untuk formulir produk, dikelompokkan per kategori utama.
     * Produk hanya boleh disimpan pada sub kategori.
     *
     * @return list<array{id: int, name: string, group: string}>
     */
    public static function subcategoryOptions(): array
    {
        return Category::query()
            ->active()
            ->whereNotNull('parent_id')
            ->whereHas('parent', fn ($query) => $query->active())
            ->with('parent:id,name,sort_order')
            ->get()
            ->sortBy(fn (Category $category) => [$category->parent->sort_order, $category->sort_order, $category->name])
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'group' => $category->parent->name,
            ])
            ->values()
            ->all();
    }
}
