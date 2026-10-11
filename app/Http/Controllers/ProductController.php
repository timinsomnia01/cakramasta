<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Support\Presenters\ProductPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Halaman /kategori/{kategori}/produk/{produk}: galeri, varian, harga, statistik
     * (like, view, ulasan, disimpan), deskripsi, dan informasi penjual.
     *
     * Produk yang belum disetujui hanya bisa dilihat pemiliknya, verifikator, dan admin (pratinjau).
     */
    public function show(Request $request, Category $category, Product $product): Response|RedirectResponse
    {
        $product->load(['category.parent', 'seller', 'images', 'variants' => fn ($query) => $query->where('is_active', true)]);

        $viewer = $request->user();
        $canPreview = $viewer && (
            $viewer->hasRole(UserRole::Admin, UserRole::Verifikator)
            || $product->seller->user_id === $viewer->id
        );

        abort_unless(($product->isApproved() && $product->seller->isApproved()) || $canPreview, 404);

        $root = ProductPresenter::rootCategory($product);

        if ($category->slug !== $root->slug) {
            return redirect()->route('products.show', [
                'category' => $root->slug,
                'product' => $product->slug,
            ], 301);
        }

        $this->recordView($request, $product, $canPreview);

        $product->loadCount(['likers as likes_count', 'savers as saves_count', 'reviews'])
            ->loadAvg('reviews', 'rating');

        $prices = $product->variants->pluck('price');

        return Inertia::render('products/show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'description' => $product->description,
                'condition' => $product->condition,
                'weight' => $product->weight,
                'price' => $product->price,
                'priceMin' => $prices->min() ?? $product->price,
                'priceMax' => $prices->max() ?? $product->price,
                'stock' => $product->stock,
                'status' => $product->status->value,
                'isPreview' => ! $product->isApproved(),
                'images' => $product->images->map(fn ($image) => [
                    'id' => $image->id,
                    'url' => ProductPresenter::imageUrl($image->path),
                    'isPrimary' => $image->is_primary,
                ])->values(),
                'variants' => $product->variants->map(fn ($variant) => [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'price' => $variant->price,
                    'stock' => $variant->stock,
                    'image' => ProductPresenter::imageUrl($variant->image),
                ])->values(),
                'category' => [
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                    'parent' => $product->category->parent ? [
                        'name' => $product->category->parent->name,
                        'slug' => $product->category->parent->slug,
                    ] : null,
                ],
            ],
            'stats' => [
                'likes' => $product->likes_count,
                'saves' => $product->saves_count,
                'reviews' => $product->reviews_count,
                'rating' => $product->reviews_avg_rating !== null ? round((float) $product->reviews_avg_rating, 1) : null,
                'sold' => $product->sold_count,
                'views' => $product->view_count,
            ],
            'ratingBreakdown' => $this->ratingBreakdown($product),
            'seller' => [
                'storeName' => $product->seller->store_name,
                'slug' => $product->seller->slug,
                'description' => $product->seller->description,
                'logo' => ProductPresenter::imageUrl($product->seller->logo),
                'location' => $this->publicLocation($product->seller->address),
                'productCount' => $product->seller->products()->approved()->count(),
                'joinedAt' => $product->seller->verified_at?->toISOString() ?? $product->seller->created_at->toISOString(),
            ],
            'reviews' => $product->reviews()
                ->with('user:id,name,avatar')
                ->latest()
                ->paginate(5, pageName: 'ulasan')
                ->withQueryString()
                ->through(fn (Review $review) => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'images' => collect($review->images)->map(fn (string $path) => ProductPresenter::imageUrl($path))->all(),
                    'userName' => $review->user->name,
                    'userAvatar' => ProductPresenter::imageUrl($review->user->avatar),
                    'createdAt' => $review->created_at->toISOString(),
                ]),
            'related' => ProductPresenter::cardQuery()
                ->where('products.category_id', $product->category_id)
                ->whereKeyNot($product->id)
                ->latest('products.id')
                ->limit(6)
                ->get()
                ->map(fn (Product $related) => ProductPresenter::card($related))
                ->values(),
            // Null untuk tamu. Tombol like / simpan / beli hanya aktif bila user sudah login.
            'viewer' => $viewer ? [
                'liked' => $product->likers()->whereKey($viewer->id)->exists(),
                'saved' => $product->savers()->whereKey($viewer->id)->exists(),
                'canBuy' => $product->isApproved() && $product->seller->user_id !== $viewer->id,
                'reviewable' => $this->reviewableItems($product, $viewer->id),
            ] : null,
        ]);
    }

    /**
     * Tambah jumlah dilihat satu kali per sesi (bukan setiap refresh), dan tidak untuk pratinjau.
     */
    private function recordView(Request $request, Product $product, bool $isPreview): void
    {
        $key = "viewed-products.{$product->id}";

        if ($isPreview || ! $request->hasSession() || $request->session()->has($key)) {
            return;
        }

        $product->recordView();
        $request->session()->put($key, now()->timestamp);
    }

    /**
     * Jumlah ulasan per bintang, 5 sampai 1.
     *
     * @return array<int, int>
     */
    private function ratingBreakdown(Product $product): array
    {
        $counts = $product->reviews()
            ->selectRaw('rating, count(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        return collect(range(5, 1))->mapWithKeys(fn (int $star) => [$star => (int) ($counts[$star] ?? 0)])->all();
    }

    /**
     * Item pesanan selesai milik user untuk produk ini yang belum diulas.
     *
     * @return list<array<string, mixed>>
     */
    private function reviewableItems(Product $product, int $userId): array
    {
        return OrderItem::query()
            ->where('product_id', $product->id)
            ->whereDoesntHave('review')
            ->whereHas('order', fn ($query) => $query
                ->where('user_id', $userId)
                ->where('status', OrderStatus::Completed))
            ->with('order:id,code')
            ->get()
            ->map(fn (OrderItem $item) => [
                'id' => $item->id,
                'orderCode' => $item->order->code,
                'variantName' => $item->variant_name,
                'url' => route('account.orders.reviews.store', ['order' => $item->order, 'item' => $item], absolute: false),
            ])
            ->all();
    }

    /**
     * Lokasi publik penjual (kecamatan dan kota) tanpa membuka alamat lengkap.
     */
    private function publicLocation(string $address): ?string
    {
        return preg_match('/Kec\.\s*([^,]+),\s*([^,]+)/', $address, $match)
            ? trim($match[1]).', '.trim($match[2])
            : null;
    }
}
