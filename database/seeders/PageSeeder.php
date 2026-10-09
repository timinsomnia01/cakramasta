<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            'Guide' => 'Panduan penggunaan UM-Mart untuk pengunjung, pembeli, dan penjual.',
            'Cara Jual Produk' => "1. Login dan daftar sebagai penjual (KTP, foto, QRIS).\n2. Tunggu verifikasi dari verifikator.\n3. Input detail produk dan tunggu produk diverifikasi.\n4. Produk tampil di katalog dan siap dibeli.\n5. Ajukan pencairan hasil jual melalui halaman manajemen penjualan.",
            'Cara Beli Produk' => "1. Cari atau pilih produk dari kategori.\n2. Login lalu pilih varian dan jumlah.\n3. Lakukan pembayaran.\n4. Pantau status di histori pembelian.\n5. Beri ulasan setelah pesanan selesai.",
            'Peraturan' => 'Daftar peraturan jual beli di UM-Mart. Produk terlarang akan ditolak oleh verifikator.',
            'Kesepakatan Pajak' => 'Ketentuan potongan layanan dan pajak atas transaksi yang berlaku di UM-Mart.',
        ];

        $i = 1;
        foreach ($pages as $title => $content) {
            Page::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'content' => $content,
                'sort_order' => $i++,
            ]);
        }
    }
}
