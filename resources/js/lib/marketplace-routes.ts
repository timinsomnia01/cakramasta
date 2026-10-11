/**
 * URL area marketplace. Sengaja berupa string biasa (bukan hasil Wayfinder) karena rute
 * routes/marketplace.php tidak punya parameter tim. Bila nanti ingin dibuat otomatis,
 * jalankan `php artisan wayfinder:generate` dan ganti isi file ini.
 */
export const urls = {
    account: {
        dashboard: '/akun',
        orders: '/akun/pesanan',
        saved: '/akun/produk-tersimpan',
        personalData: '/akun/data-diri',
        sellerRegistration: '/akun/daftar-penjual',
    },
    seller: {
        dashboard: '/toko',
        products: '/toko/produk',
        newProduct: '/toko/produk/buat',
        orders: '/toko/pesanan',
        withdrawals: '/toko/pencairan',
        settings: '/toko/pengaturan',
    },
    verification: {
        dashboard: '/verifikasi',
        sellers: '/verifikasi/penjual',
        products: '/verifikasi/produk',
        payments: '/verifikasi/pembayaran',
        withdrawals: '/verifikasi/pencairan',
    },
    admin: {
        dashboard: '/admin',
        verifikators: '/admin/verifikator',
        products: '/admin/produk',
        sellers: '/admin/penjual',
        orders: '/admin/pembelian',
    },
} as const;
