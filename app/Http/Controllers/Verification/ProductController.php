<?php

namespace App\Http\Controllers\Verification;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Verification\RejectionRequest;
use App\Models\Product;
use App\Support\Presenters\ProductPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $status = ProductStatus::tryFrom((string) $request->query('status', 'pending'));
        $search = trim((string) $request->query('q', ''));

        return Inertia::render('verification/products/index', [
            'products' => Product::query()
                ->with(['primaryImage', 'category', 'seller:id,store_name'])
                ->when($status, fn ($query) => $query->where('status', $status))
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhereHas('seller', fn ($query) => $query->where('store_name', 'like', "%{$search}%"))))
                ->oldest('products.updated_at')
                ->paginate(15)
                ->withQueryString()
                ->through(fn (Product $product) => ProductPresenter::row($product)),
            'filters' => ['status' => $status?->value, 'q' => $search],
            'statuses' => collect(ProductStatus::cases())->map(fn (ProductStatus $s) => ['value' => $s->value, 'label' => $s->label()]),
        ]);
    }

    public function show(Product $product): Response
    {
        $product->load(['images', 'variants', 'category.parent', 'seller:id,store_name,slug', 'verifier:id,name']);

        return Inertia::render('verification/products/show', [
            'product' => ProductPresenter::row($product) + [
                'description' => $product->description,
                'weight' => $product->weight,
                'condition' => $product->condition,
                'categoryPath' => collect([$product->category->parent?->name, $product->category->name])->filter()->implode(' › '),
                'images' => $product->images->map(fn ($image) => ProductPresenter::imageUrl($image->path))->values(),
                'variants' => $product->variants->map(fn ($variant) => [
                    'name' => $variant->name,
                    'price' => $variant->price,
                    'stock' => $variant->stock,
                    'isActive' => $variant->is_active,
                ])->values(),
                'verifiedBy' => $product->verifier?->name,
            ],
            'canReview' => $product->status === ProductStatus::Pending,
        ]);
    }

    public function approve(Request $request, Product $product): RedirectResponse
    {
        $this->ensurePending($product);

        $product->approve($request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Produk disetujui dan tampil di katalog.')]);

        return to_route('verification.products.index');
    }

    public function reject(RejectionRequest $request, Product $product): RedirectResponse
    {
        $this->ensurePending($product);

        $product->reject($request->user(), $request->validated('reason'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Produk ditolak.')]);

        return to_route('verification.products.index');
    }

    private function ensurePending(Product $product): void
    {
        if ($product->status !== ProductStatus::Pending) {
            throw ValidationException::withMessages(['product' => 'Produk ini sudah diproses.']);
        }
    }
}
