<?php

use App\Http\Controllers\Account;
use App\Http\Controllers\Admin;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PrivateFileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductLikeController;
use App\Http\Controllers\ProductSaveController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Seller;
use App\Http\Controllers\Verification;
use App\Http\Middleware\EnsureApprovedSeller;
use App\Http\Middleware\EnsureUserRole;
use App\Http\Middleware\RecordVisit;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute UM-Mart
|--------------------------------------------------------------------------
|
| Dimuat dari routes/web.php dengan: require __DIR__.'/marketplace.php';
| Awalan "akun", "toko", "verifikasi", "admin", "kategori", "cari", "halaman", "keranjang",
| "checkout", dan "berkas" adalah slug tim yang sebaiknya dicadangkan agar tidak bentrok
| dengan rute berawalan {current_team}.
|
*/

// ---------------------------------------------------------------------------
// Publik (dihitung sebagai kunjungan untuk statistik admin)
// ---------------------------------------------------------------------------
Route::middleware(RecordVisit::class)->group(function () {
    Route::get('/', HomeController::class)->name('home');
    Route::get('/cari', SearchController::class)->name('search');
    Route::get('/kategori', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/kategori/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('/kategori/{category}/produk/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/halaman/{page}', [PageController::class, 'show'])->name('pages.show');
});

// ---------------------------------------------------------------------------
// Pembeli (login + email terverifikasi)
// ---------------------------------------------------------------------------
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/produk/{product}/suka', [ProductLikeController::class, 'store'])->name('products.like.store');
    Route::delete('/produk/{product}/suka', [ProductLikeController::class, 'destroy'])->name('products.like.destroy');
    Route::post('/produk/{product}/simpan', [ProductSaveController::class, 'store'])->name('products.save.store');
    Route::delete('/produk/{product}/simpan', [ProductSaveController::class, 'destroy'])->name('products.save.destroy');

    Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
    Route::post('/keranjang', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/keranjang/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/keranjang/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');

    Route::prefix('akun')->name('account.')->group(function () {
        Route::get('/', [Account\DashboardController::class, 'index'])->name('dashboard');

        Route::get('/data-diri', [Account\PersonalDataController::class, 'edit'])->name('personal-data.edit');
        Route::post('/data-diri', [Account\PersonalDataController::class, 'update'])->name('personal-data.update');

        Route::get('/produk-tersimpan', [Account\SavedProductController::class, 'index'])->name('saved-products');

        Route::get('/pesanan', [Account\OrderController::class, 'index'])->name('orders.index');
        Route::get('/pesanan/{order}', [Account\OrderController::class, 'show'])->name('orders.show');
        Route::post('/pesanan/{order}/batal', [Account\OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('/pesanan/{order}/selesai', [Account\OrderController::class, 'complete'])->name('orders.complete');
        Route::get('/pesanan/{order}/pembayaran', [Account\PaymentController::class, 'show'])->name('orders.payment.show');
        Route::post('/pesanan/{order}/pembayaran', [Account\PaymentController::class, 'store'])->middleware('throttle:10,1')->name('orders.payment.store');
        Route::post('/pesanan/{order}/ulasan/{item}', [Account\ReviewController::class, 'store'])->name('orders.reviews.store');

        Route::get('/daftar-penjual', [Account\SellerRegistrationController::class, 'show'])->name('seller.show');
        Route::post('/daftar-penjual', [Account\SellerRegistrationController::class, 'store'])->middleware('throttle:5,1')->name('seller.store');
    });

    // Berkas privat (otorisasi per berkas ada di controller).
    Route::prefix('berkas')->name('files.')->group(function () {
        Route::get('/penjual/{seller:id}/{type}', [PrivateFileController::class, 'sellerDocument'])->whereIn('type', ['ktp', 'selfie', 'qris'])->name('seller-document');
        Route::get('/pembayaran/{payment}', [PrivateFileController::class, 'paymentProof'])->name('payment-proof');
        Route::get('/pencairan/{withdrawal}', [PrivateFileController::class, 'withdrawalProof'])->name('withdrawal-proof');
    });
});

// ---------------------------------------------------------------------------
// Penjual (toko yang sudah disetujui)
// ---------------------------------------------------------------------------
Route::middleware(['auth', 'verified', EnsureApprovedSeller::class])->prefix('toko')->name('seller.')->group(function () {
    Route::get('/', [Seller\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/pengaturan', [Seller\StoreController::class, 'edit'])->name('store.edit');
    Route::post('/pengaturan', [Seller\StoreController::class, 'update'])->name('store.update');

    Route::get('/produk', [Seller\ProductController::class, 'index'])->name('products.index');
    Route::get('/produk/buat', [Seller\ProductController::class, 'create'])->name('products.create');
    Route::post('/produk', [Seller\ProductController::class, 'store'])->name('products.store');
    Route::get('/produk/{product:id}/ubah', [Seller\ProductController::class, 'edit'])->name('products.edit');
    // POST (bukan PUT) karena formulir membawa berkas gambar; Inertia memakai _method bila perlu.
    Route::match(['put', 'post'], '/produk/{product:id}', [Seller\ProductController::class, 'update'])->name('products.update');
    Route::post('/produk/{product:id}/ajukan', [Seller\ProductController::class, 'submit'])->name('products.submit');
    Route::post('/produk/{product:id}/aktif', [Seller\ProductController::class, 'toggle'])->name('products.toggle');
    Route::delete('/produk/{product:id}', [Seller\ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/pesanan', [Seller\OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [Seller\OrderController::class, 'show'])->name('orders.show');
    Route::post('/pesanan/{order}/proses', [Seller\OrderController::class, 'process'])->name('orders.process');
    Route::post('/pesanan/{order}/kirim', [Seller\OrderController::class, 'ship'])->name('orders.ship');

    Route::get('/pencairan', [Seller\WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('/pencairan', [Seller\WithdrawalController::class, 'store'])->middleware('throttle:5,1')->name('withdrawals.store');
});

// ---------------------------------------------------------------------------
// Verifikator (dan admin)
// ---------------------------------------------------------------------------
Route::middleware(['auth', 'verified', EnsureUserRole::class.':verifikator,admin'])->prefix('verifikasi')->name('verification.')->group(function () {
    Route::get('/', [Verification\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/penjual', [Verification\SellerController::class, 'index'])->name('sellers.index');
    Route::get('/penjual/{seller:id}', [Verification\SellerController::class, 'show'])->name('sellers.show');
    Route::post('/penjual/{seller:id}/setujui', [Verification\SellerController::class, 'approve'])->name('sellers.approve');
    Route::post('/penjual/{seller:id}/tolak', [Verification\SellerController::class, 'reject'])->name('sellers.reject');

    Route::get('/produk', [Verification\ProductController::class, 'index'])->name('products.index');
    Route::get('/produk/{product:id}', [Verification\ProductController::class, 'show'])->name('products.show');
    Route::post('/produk/{product:id}/setujui', [Verification\ProductController::class, 'approve'])->name('products.approve');
    Route::post('/produk/{product:id}/tolak', [Verification\ProductController::class, 'reject'])->name('products.reject');

    Route::get('/pembayaran', [Verification\PaymentController::class, 'index'])->name('payments.index');
    Route::get('/pembayaran/{payment}', [Verification\PaymentController::class, 'show'])->name('payments.show');
    Route::post('/pembayaran/{payment}/setujui', [Verification\PaymentController::class, 'approve'])->name('payments.approve');
    Route::post('/pembayaran/{payment}/tolak', [Verification\PaymentController::class, 'reject'])->name('payments.reject');

    Route::get('/pencairan', [Verification\WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::get('/pencairan/{withdrawal}', [Verification\WithdrawalController::class, 'show'])->name('withdrawals.show');
    Route::post('/pencairan/{withdrawal}/setujui', [Verification\WithdrawalController::class, 'approve'])->name('withdrawals.approve');
    Route::post('/pencairan/{withdrawal}/tolak', [Verification\WithdrawalController::class, 'reject'])->name('withdrawals.reject');
    Route::post('/pencairan/{withdrawal}/dibayar', [Verification\WithdrawalController::class, 'markPaid'])->name('withdrawals.paid');
});

// ---------------------------------------------------------------------------
// Admin
// ---------------------------------------------------------------------------
Route::middleware(['auth', 'verified', EnsureUserRole::class.':admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('verifikator', Admin\VerifikatorController::class)
        ->except('show')
        ->parameters(['verifikator' => 'verifikator'])
        ->names('verifikators');

    Route::get('/produk', [Admin\ProductController::class, 'index'])->name('products.index');
    Route::get('/produk/buat', [Admin\ProductController::class, 'create'])->name('products.create');
    Route::post('/produk', [Admin\ProductController::class, 'store'])->name('products.store');
    Route::get('/produk/{product:id}/ubah', [Admin\ProductController::class, 'edit'])->name('products.edit');
    Route::match(['put', 'post'], '/produk/{product:id}', [Admin\ProductController::class, 'update'])->name('products.update');
    Route::delete('/produk/{product:id}', [Admin\ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/penjual', [Admin\SellerController::class, 'index'])->name('sellers.index');
    Route::get('/penjual/{seller:id}', [Admin\SellerController::class, 'show'])->name('sellers.show');
    Route::post('/penjual/{seller:id}/nonaktifkan', [Admin\SellerController::class, 'suspend'])->name('sellers.suspend');
    Route::post('/penjual/{seller:id}/aktifkan', [Admin\SellerController::class, 'reactivate'])->name('sellers.reactivate');

    // "export" harus didaftarkan sebelum "{order}" agar tidak dianggap kode pesanan.
    Route::get('/pembelian/export', [Admin\OrderController::class, 'export'])->name('orders.export');
    Route::get('/pembelian', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/pembelian/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::delete('/pembelian/{order}', [Admin\OrderController::class, 'destroy'])->name('orders.destroy');
});