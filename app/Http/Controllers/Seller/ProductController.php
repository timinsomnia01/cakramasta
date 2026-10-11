<?php

namespace App\Http\Controllers\Seller;

use App\Actions\Products\SaveProduct;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\SaveProductRequest;
use App\Models\Product;
use App\Support\Presenters\CategoryPresenter;
use App\Support\Presenters\ProductPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Daftar produk milik toko, dengan pencarian nama / SKU dan filter status.
     */
    public function index(Request $request): Response
    {
        $seller = $request->user()->seller;
        $status = ProductStatus::tryFrom((string) $request->query('status'));
        $search = trim((string) $request->query('q', ''));

        return Inertia::render('seller/products/index', [
            'products' => $seller->products()
                ->with(['primaryImage', 'category'])
                ->when($status, fn ($query) => $query->where('status', $status))
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")))
                ->latest()
                ->paginate(10)
                ->withQueryString()
                ->through(fn (Product $product) => ProductPresenter::row($product) + ['id' => $product->id]),
            'filters' => ['status' => $status?->value, 'q' => $search],
            'statuses' => collect(ProductStatus::cases())->map(fn (ProductStatus $s) => ['value' => $s->value, 'label' => $s->label()]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('seller/products/form', [
            'product' => null,
            'categories' => CategoryPresenter::subcategoryOptions(),
            'limits' => $this->limits(),
        ]);
    }

    public function store(SaveProductRequest $request, SaveProduct $saveProduct): RedirectResponse
    {
        $product = $saveProduct->handle(
            $request->user()->seller,
            null,
            $request->safe()->except('images'),
            $request->file('images', []),
            $request->user(),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $product->status === ProductStatus::Draft
                ? __('Draft produk disimpan.')
                : __('Produk dikirim dan menunggu verifikasi.'),
        ]);

        return to_route('seller.products.index');
    }

    public function edit(Product $product): Response
    {
        Gate::authorize('manage', $product);

        $product->load(['images', 'variants', 'category']);

        return Inertia::render('seller/products/form', [
            'product' => ProductPresenter::form($product),
            'categories' => CategoryPresenter::subcategoryOptions(),
            'limits' => $this->limits(),
        ]);
    }

    public function update(SaveProductRequest $request, Product $product, SaveProduct $saveProduct): RedirectResponse
    {
        Gate::authorize('manage', $product);

        $saveProduct->handle(
            $request->user()->seller,
            $product,
            $request->safe()->except('images'),
            $request->file('images', []),
            $request->user(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Produk diperbarui.')]);

        return to_route('seller.products.index');
    }

    /**
     * Ajukan produk draft / ditolak ke verifikator tanpa mengubah isinya.
     */
    public function submit(Product $product): RedirectResponse
    {
        Gate::authorize('manage', $product);

        if (! in_array($product->status, [ProductStatus::Draft, ProductStatus::Rejected], true)) {
            throw ValidationException::withMessages(['product' => 'Produk ini tidak dapat diajukan.']);
        }

        if (! $product->images()->exists()) {
            throw ValidationException::withMessages(['product' => 'Tambahkan minimal satu gambar sebelum mengajukan produk.']);
        }

        $product->forceFill(['status' => ProductStatus::Pending, 'rejection_reason' => null])->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Produk diajukan untuk verifikasi.')]);

        return back();
    }

    /**
     * Aktifkan / nonaktifkan produk yang sudah disetujui tanpa perlu verifikasi ulang.
     */
    public function toggle(Product $product): RedirectResponse
    {
        Gate::authorize('manage', $product);

        $next = match ($product->status) {
            ProductStatus::Approved => ProductStatus::Inactive,
            ProductStatus::Inactive => ProductStatus::Approved,
            default => throw ValidationException::withMessages(['product' => 'Hanya produk yang sudah disetujui yang dapat diaktifkan / dinonaktifkan.']),
        };

        $product->forceFill(['status' => $next])->save();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $next === ProductStatus::Approved ? __('Produk diaktifkan.') : __('Produk dinonaktifkan.'),
        ]);

        return back();
    }

    /**
     * Hapus produk (soft delete). Riwayat pesanan tetap utuh karena menyimpan snapshot produk.
     */
    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('manage', $product);

        $product->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Produk dihapus.')]);

        return to_route('seller.products.index');
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
