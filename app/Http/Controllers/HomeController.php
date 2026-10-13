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

            // 1. TAMBAHKAN BARIS INI UNTUK MENGIRIM DATA topProducts
            'topProducts' => $this->topProducts(),
        ]);
    }

    /**
     * Mengambil 3 produk teratas untuk section Top Products.
     *
     * @return list<array<string, mixed>>
     */
    private function topProducts(): array
    {
        // Anda bisa mengubah logika sorting-nya (misal: orderBy('sold_count', 'desc')
        // atau orderBy('views', 'desc')) sesuai dengan definisi "Top" di database Anda.
        return Product::query()
            ->approved()
            ->where('stock', '>', 0)
            ->inRandomOrder() // Contoh: ambil acak untuk saat ini. Silakan ubah sesuai kebutuhan bisnis.
            ->limit(3)
            ->get()
            ->map(function (Product $product) {
                // Pastikan format ini persis dengan tipe TopProductData di React
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->price,
                    // Pastikan field description ditarik agar bisa muncul di desain baru
                    'description' => $product->description,
                    // Jika Anda menggunakan Presenter/Accessor untuk URL gambar, sesuaikan kodenya (misal: $product->image_url)
                    'image' => $product->image ?? '/images/placeholder.webp',
                ];
            })
            ->values()
            ->all();
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
