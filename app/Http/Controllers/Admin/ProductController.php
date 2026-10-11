<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Products\SaveProduct;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Support\Presenters\CategoryPresenter;
use App\Support\Presenters\ProductPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Semua produk, dengan pencarian (nama / SKU / toko) dan filter status, kategori, toko, serta rentang harga.
     */
    public function index(Request $request): Response
    {
        $status = ProductStatus::tryFrom((string) $request->query('status'));
        $search = trim((string) $request->query('q', ''));
        $category = $request->query('category') ? Category::query()->with('children:id,parent_id')->find($request->query('category')) : null;
        $sellerId = $request->integer('seller') ?: null;
        $min = $request->query('min') !== null && $request->query('min') !== '' ? max(0, $request->integer('min')) : null;
        $max = $request->query('max') !== null && $request->query('max') !== '' ? max(0, $request->integer('max')) : null;

        return Inertia::render('admin/products/index', [
            'products' => Product::query()
                ->with(['primaryImage', 'category', 'seller:id,store_name'])
                ->when($status, fn ($query) => $query->where('status', $status))
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhereHas('seller', fn ($query) => $query->where('store_name', 'like', "%{$search}%"))))
                ->when($category, fn ($query) => $query->whereIn('category_id', $category->children->pluck('id')->push($category->id)))
                ->when($sellerId, fn ($query) => $query->where('seller_id', $sellerId))
                ->when($min !== null, fn ($query) => $query->where('price', '>=', $min))
                ->when($max !== null, fn ($query) => $query->where('price', '<=', $max))
                ->latest('products.id')
                ->paginate(15)
                ->withQueryString()
                ->through(fn (Product $product) => ProductPresenter::row($product)),
            'filters' => [
                'q' => $search,
                'status' => $status?->value,
                'category' => $category?->id,
                'seller' => $sellerId,
                'min' => $min,
                'max' => $max,
            ],
            'statuses' => collect(ProductStatus::cases())->map(fn (ProductStatus $s) => ['value' => $s->value, 'label' => $s->label()]),
            'categories' => $this->categoryFilterOptions(),
            'sellers' => $this->sellerOptions(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/products/form', [
            'product' => null,
            'categories' => CategoryPresenter::subcategoryOptions(),
            'sellers' => $this->sellerOptions(),
            'statuses' => $this->statusOptions(),
            'limits' => $this->limits(),
        ]);
    }

    public function store(SaveProductRequest $request, SaveProduct $saveProduct): RedirectResponse
    {
        $saveProduct->handle(
            Seller::query()->findOrFail($request->validated('seller_id')),
            null,
            $request->safe()->except('images'),
            $request->file('images', []),
            $request->user(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Produk ditambahkan.')]);

        return to_route('admin.products.index');
    }

    public function edit(Product $product): Response
    {
        $product->load(['images', 'variants']);

        return Inertia::render('admin/products/form', [
            'product' => ProductPresenter::form($product),
            'categories' => CategoryPresenter::subcategoryOptions(),
            'sellers' => $this->sellerOptions($product->seller_id),
            'statuses' => $this->statusOptions(),
            'limits' => $this->limits(),
        ]);
    }

    public function update(SaveProductRequest $request, Product $product, SaveProduct $saveProduct): RedirectResponse
    {
        $saveProduct->handle(
            Seller::query()->findOrFail($request->validated('seller_id')),
            $product,
            $request->safe()->except('images'),
            $request->file('images', []),
            $request->user(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Produk diperbarui.')]);

        return to_route('admin.products.index');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Produk dihapus.')]);

        return to_route('admin.products.index');
    }

    /**
     * @return list<array{id: int, name: string, depth: int}>
     */
    private function categoryFilterOptions(): array
    {
        return Category::query()
            ->root()
            ->with('children:id,parent_id,name,sort_order')
            ->orderBy('sort_order')
            ->get()
            ->flatMap(fn (Category $root) => $root->children->sortBy('sort_order')
                ->map(fn (Category $child) => ['id' => $child->id, 'name' => $child->name, 'depth' => 1])
                ->prepend(['id' => $root->id, 'name' => $root->name, 'depth' => 0]))
            ->values()
            ->all();
    }

    /**
     * Toko yang bisa dipilih: yang disetujui, ditambah toko pemilik produk saat ini (bila sedang dinonaktifkan).
     *
     * @return list<array{id: int, name: string}>
     */
    private function sellerOptions(?int $include = null): array
    {
        return Seller::query()
            ->where(fn ($query) => $query->approved()->when($include, fn ($query) => $query->orWhere('id', $include)))
            ->orderBy('store_name')
            ->get(['id', 'store_name'])
            ->map(fn (Seller $seller) => ['id' => $seller->id, 'name' => $seller->store_name])
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return collect(ProductStatus::cases())->map(fn (ProductStatus $s) => ['value' => $s->value, 'label' => $s->label()])->all();
    }

    /**
     * @return array<string, int>
     */
    private function limits(): array
    {
        return [
            'maxImages' => (int) config('marketplace.max_product_images'),
            'maxImageKb' => (int) config('marketplace.max_image_kb'),
        ];
    }
}
