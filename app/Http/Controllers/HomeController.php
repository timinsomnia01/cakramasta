<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Presenters\CategoryPresenter;
use App\Support\Presenters\ProductPresenter;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Beranda: carousel produk pilihan hari ini, seluruh kategori, dan produk terbaru.
     * Kolom pencarian mengarah ke route "search".
     */
    public function __invoke(): Response
    {
        return Inertia::render('home', [
            'dailyProducts' => $this->dailyProducts(),
            'categories' => CategoryPresenter::roots(),
            'newProducts' => ProductPresenter::cardQuery()
                ->latest('products.id')
                ->limit(8)
                ->get()
                ->map(fn (Product $product) => ProductPresenter::card($product))
                ->values(),
        ]);
    }

    /**
     * Produk pilihan yang berganti setiap hari. Urutannya ditentukan oleh tanggal sehingga
     * sama untuk semua pengunjung sepanjang hari, lalu di-cache sampai tengah malam.
     *
     * @return list<array<string, mixed>>
     */
    private function dailyProducts(): array
    {
        $date = today()->toDateString();

        $ids = Cache::remember("home.daily-products.{$date}", now()->endOfDay(), fn () => Product::query()
            ->approved()
            ->where('stock', '>', 0)
            ->pluck('id')
            ->sortBy(fn (int $id) => crc32("{$date}|{$id}"))
            ->take(10)
            ->values()
            ->all());

        return ProductPresenter::cardQuery()
            ->whereIn('products.id', $ids)
            ->get()
            ->sortBy(fn (Product $product) => array_search($product->id, $ids, true))
            ->map(fn (Product $product) => ProductPresenter::card($product))
            ->values()
            ->all();
    }
}
