<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Biaya Platform dan Pajak
    |--------------------------------------------------------------------------
    |
    | Dipotong dari subtotal produk setiap order. Nilai ini harus sama dengan isi halaman
    | "Kesepakatan Pajak". seller_income = subtotal - platform_fee - tax_amount.
    |
    */

    'platform_fee_rate' => 0.05,

    'tax_rate' => 0.005,

    /*
    |--------------------------------------------------------------------------
    | Pencairan Hasil Jual
    |--------------------------------------------------------------------------
    */

    'min_withdrawal' => 50000,

    /*
    |--------------------------------------------------------------------------
    | Pembayaran
    |--------------------------------------------------------------------------
    |
    | Pembeli membayar ke rekening / QRIS UM-Mart, lalu verifikator memeriksa bukti
    | pembayarannya. Order yang tidak dibayar dalam batas waktu dibatalkan otomatis
    | oleh command "orders:expire".
    |
    */

    'payment_expiry_hours' => 24,

    'payment_methods' => [
        'qris' => 'QRIS',
        'bank_transfer' => 'Transfer Bank',
    ],

    // Path gambar QRIS UM-Mart pada disk "public".
    'qris_image' => env('MARKETPLACE_QRIS_IMAGE', 'payments/qris-um-mart.svg'),

    'bank_account' => [
        'bank' => env('MARKETPLACE_BANK_NAME', 'BCA'),
        'number' => env('MARKETPLACE_BANK_NUMBER', '0000000000'),
        'holder' => env('MARKETPLACE_BANK_HOLDER', 'UM-Mart'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Kurir
    |--------------------------------------------------------------------------
    |
    | Ongkir flat per order. Nama kurir disimpan di orders.courier.
    | Kurir dengan "tracking" => false (COD / ambil di tempat) tidak butuh nomor resi.
    |
    */

    'couriers' => [
        'jne' => ['name' => 'JNE Reguler', 'cost' => 15000, 'tracking' => true],
        'jnt' => ['name' => 'J&T Express', 'cost' => 14000, 'tracking' => true],
        'sicepat' => ['name' => 'SiCepat REG', 'cost' => 13000, 'tracking' => true],
        'cod' => ['name' => 'COD / Ambil di Tempat (Kota Malang)', 'cost' => 0, 'tracking' => false],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pesanan Otomatis Selesai
    |--------------------------------------------------------------------------
    |
    | Pesanan berstatus "shipped" yang tidak dikonfirmasi pembeli selesai otomatis
    | setelah sekian hari oleh command "orders:complete-shipped".
    |
    */

    'auto_complete_days' => 7,

    /*
    |--------------------------------------------------------------------------
    | Batas Unggahan
    |--------------------------------------------------------------------------
    */

    'max_product_images' => 5,

    'max_image_kb' => 2048,

    'max_document_kb' => 4096,

];
