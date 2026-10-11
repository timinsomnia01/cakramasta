<?php

namespace App\Http\Controllers;

use App\Actions\Catalog\FilterProducts;
use App\Http\Requests\Catalog\CatalogFilterRequest;
use App\Models\Category;
use App\Models\Product;
use App\Support\Presenters\ProductPresenter;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    /**
     * Cari produk berdasarkan nama / deskripsi, dengan filter kategori utama, harga, dan urutan.
     */
    public function __invoke(CatalogFilterRequest $request, FilterProducts $filterProducts): Response
    {
        $filters = $request->validated();

        $query = ProductPresenter::cardQuery()
            ->when($filters['kategori'] ?? null, fn (Builder $query, string $slug) => $query->whereHas(
                'category',
                fn (Builder $category) => $category
                    ->where('slug', $slug)
                    ->orWhereHas('parent', fn (Builder $parent) => $parent->where('slug', $slug)),
            ));

        $filterProducts->handle($query, $filters);

        return Inertia::render('search/index', [
            'filters' => [
                'q' => $filters['q'] ?? '',
                'kategori' => $filters['kategori'] ?? null,
                'min_price' => $filters['min_price'] ?? null,
                'max_price' => $filters['max_price'] ?? null,
                'sort' => $filters['sort'] ?? 'terbaru',
            ],
            'sorts' => FilterProducts::SORTS,
            'categories' => Category::root()->active()->orderBy('sort_order')->get(['id', 'name', 'slug']),
            'products' => $query->paginate(12)
                ->withQueryString()
                ->through(fn (Product $product) => ProductPresenter::card($product)),
        ]);
    }
}
