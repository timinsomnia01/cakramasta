<?php

namespace App\Http\Controllers;

use App\Actions\Catalog\FilterProducts;
use App\Http\Requests\Catalog\CatalogFilterRequest;
use App\Models\Category;
use App\Models\Product;
use App\Support\Presenters\CategoryPresenter;
use App\Support\Presenters\ProductPresenter;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Seluruh kategori utama dalam bentuk kartu.
     */
    public function index(): Response
    {
        return Inertia::render('categories/index', [
            'categories' => CategoryPresenter::roots(),
        ]);
    }

    /**
     * Halaman /kategori/{kategori}: sub kategori di sisi kiri, daftar produk di sisi kanan.
     * Sub kategori dipilih lewat ?sub={slug}. Membuka slug sub kategori langsung dialihkan
     * ke kategori utamanya.
     */
    public function show(CatalogFilterRequest $request, Category $category, FilterProducts $filterProducts): Response|RedirectResponse
    {
        abort_unless($category->is_active, 404);

        if (! $category->isRoot()) {
            return to_route('categories.show', [
                'category' => $category->parent->slug,
                ...$request->query(),
                'sub' => $category->slug,
            ]);
        }

        $filters = $request->validated();

        $subCategories = $category->children()
            ->where('is_active', true)
            ->withCount(['products as products_count' => fn ($query) => $query
                ->approved()
                ->whereHas('seller', fn ($seller) => $seller->approved())])
            ->get();

        $activeSub = $subCategories->firstWhere('slug', $filters['sub'] ?? null);

        $query = ProductPresenter::cardQuery()->whereIn(
            'products.category_id',
            $activeSub ? [$activeSub->id] : $subCategories->pluck('id')->push($category->id),
        );

        $filterProducts->handle($query, $filters);

        return Inertia::render('categories/show', [
            'category' => [
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image' => ProductPresenter::imageUrl($category->image),
            ],
            'subCategories' => $subCategories->map(fn (Category $sub) => [
                'id' => $sub->id,
                'name' => $sub->name,
                'slug' => $sub->slug,
                'productCount' => $sub->products_count,
            ])->values(),
            'activeSub' => $activeSub?->slug,
            'filters' => [
                'q' => $filters['q'] ?? '',
                'min_price' => $filters['min_price'] ?? null,
                'max_price' => $filters['max_price'] ?? null,
                'sort' => $filters['sort'] ?? 'terbaru',
            ],
            'sorts' => FilterProducts::SORTS,
            'products' => $query->paginate(12)
                ->withQueryString()
                ->through(fn (Product $product) => ProductPresenter::card($product)),
        ]);
    }
}
