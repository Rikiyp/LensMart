<?php

use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Partner\AnalyticsController as PartnerAnalyticsController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PartnerController as AdminPartnerController;
use App\Http\Controllers\Admin\PayoutController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SpecAttributeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\PartnerRegisterController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\Customer\AddressController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\ReviewController as CustomerReviewController;
use App\Http\Controllers\Customer\WishlistController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Partner\DashboardController as PartnerDashboardController;
use App\Http\Controllers\Partner\FinanceController;
use App\Http\Controllers\Partner\OrderController as PartnerOrderController;
use App\Http\Controllers\Partner\ProductController as PartnerProductController;
use App\Http\Controllers\Partner\ReviewController as PartnerReviewController;
use App\Http\Controllers\Partner\StoreController as PartnerStoreController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Publik
|--------------------------------------------------------------------------
*/
Route::get('/', HomeController::class)->name('home');
Route::get('/produk', [CatalogController::class, 'index'])->name('products.index');
Route::get('/produk/{product:slug}', [CatalogController::class, 'show'])->name('products.show');

Route::get('/bandingkan', [CompareController::class, 'index'])->name('compare.index');
Route::post('/bandingkan/{product}', [CompareController::class, 'store'])->name('compare.store');
Route::delete('/bandingkan/{product}', [CompareController::class, 'destroy'])->name('compare.destroy');
Route::delete('/bandingkan', [CompareController::class, 'clear'])->name('compare.clear');

Route::middleware('guest')->group(function () {
    Route::get('/daftar-partner', [PartnerRegisterController::class, 'create'])->name('partner.register');
    Route::post('/daftar-partner', [PartnerRegisterController::class, 'store'])->name('partner.register.store');
});

/*
|--------------------------------------------------------------------------
| Autentikasi umum
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profil', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifikasi/unread', [NotificationController::class, 'unreadCount'])->name('notifications.unread');
    Route::post('/notifikasi/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifikasi/{id}', [NotificationController::class, 'read'])->name('notifications.read');

    // Analytics API
    Route::get('/api/analytics/dashboard', [\App\Http\Controllers\Api\AnalyticsController::class, 'dashboard'])->name('api.analytics.dashboard');
    Route::get('/api/analytics/revenue', [\App\Http\Controllers\Api\AnalyticsController::class, 'revenue'])->name('api.analytics.revenue');
    Route::get('/api/analytics/top-products', [\App\Http\Controllers\Api\AnalyticsController::class, 'topProducts'])->name('api.analytics.top-products');
    Route::get('/api/analytics/order-status', [\App\Http\Controllers\Api\AnalyticsController::class, 'orderStatus'])->name('api.analytics.order-status');
    Route::get('/api/analytics/categories', [\App\Http\Controllers\Api\AnalyticsController::class, 'categories'])->name('api.analytics.categories');
    Route::get('/api/analytics/hours', [\App\Http\Controllers\Api\AnalyticsController::class, 'hours'])->name('api.analytics.hours');
});

/*
|--------------------------------------------------------------------------
| Pelanggan
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/akun/dashboard', CustomerDashboardController::class)->name('customer.dashboard');

    Route::get('/keranjang', [CartController::class, 'index'])->name('customer.cart.index');
    Route::post('/keranjang/checkout', [CartController::class, 'prepareCheckout'])->name('customer.cart.checkout');
    Route::post('/keranjang/{product}', [CartController::class, 'store'])->name('customer.cart.store');
    Route::patch('/keranjang/{cartItem}', [CartController::class, 'update'])->name('customer.cart.update');
    Route::delete('/keranjang/{cartItem}', [CartController::class, 'destroy'])->name('customer.cart.destroy');

    Route::get('/akun/wishlist', [WishlistController::class, 'index'])->name('customer.wishlist.index');
    Route::post('/wishlist/{product}', [WishlistController::class, 'store'])->name('customer.wishlist.store');
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy'])->name('customer.wishlist.destroy');

    Route::resource('/akun/alamat', AddressController::class)
        ->parameters(['alamat' => 'address'])
        ->except('show')
        ->names('customer.addresses');
    Route::post('/akun/alamat/{address}/utama', [AddressController::class, 'setDefault'])->name('customer.addresses.default');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('customer.checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('customer.checkout.store');

    Route::get('/pembayaran/{payment}', [PaymentController::class, 'show'])->name('customer.payments.show');
    Route::post('/pembayaran/{payment}/metode', [PaymentController::class, 'setMethod'])->name('customer.payments.method');
    Route::post('/pembayaran/{payment}/bayar', [PaymentController::class, 'pay'])->name('customer.payments.pay');
    Route::get('/pembayaran/{payment}/status', [PaymentController::class, 'status'])->name('customer.payments.status');

    Route::get('/pesanan', [CustomerOrderController::class, 'index'])->name('customer.orders.index');
    Route::get('/pesanan/{order}', [CustomerOrderController::class, 'show'])->name('customer.orders.show');
    Route::post('/pesanan/{order}/batal', [CustomerOrderController::class, 'cancel'])->name('customer.orders.cancel');
    Route::post('/pesanan/{order}/terima', [CustomerOrderController::class, 'confirm'])->name('customer.orders.confirm');

    Route::get('/pesanan/{order}/ulasan/{product}/buat', [CustomerReviewController::class, 'create'])->name('customer.reviews.create');
    Route::post('/pesanan/{order}/ulasan/{product}', [CustomerReviewController::class, 'store'])->name('customer.reviews.store');
    Route::get('/ulasan/{review}/edit', [CustomerReviewController::class, 'edit'])->name('customer.reviews.edit');
    Route::put('/ulasan/{review}', [CustomerReviewController::class, 'update'])->name('customer.reviews.update');
});

/*
|--------------------------------------------------------------------------
| Partner
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:partner'])->prefix('partner')->name('partner.')->group(function () {
    Route::get('/dashboard', PartnerDashboardController::class)->name('dashboard');
    Route::get('/analitik', [PartnerAnalyticsController::class, 'index'])->name('analytics');

    Route::get('/toko', [PartnerStoreController::class, 'edit'])->name('store.edit');
    Route::put('/toko', [PartnerStoreController::class, 'update'])->name('store.update');

    Route::middleware('store.verified')->group(function () {
        Route::resource('/produk', PartnerProductController::class)
            ->parameters(['produk' => 'product'])
            ->except('show')
            ->names('products');
    });

    Route::get('/pesanan', [PartnerOrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [PartnerOrderController::class, 'show'])->name('orders.show');
    Route::post('/pesanan/{order}/status', [PartnerOrderController::class, 'updateStatus'])->name('orders.status');

    Route::get('/keuangan', FinanceController::class)->name('finance.index');

    Route::get('/ulasan', [PartnerReviewController::class, 'index'])->name('reviews.index');
    Route::post('/ulasan/{review}/balas', [PartnerReviewController::class, 'reply'])->name('reviews.reply');
    Route::post('/ulasan/{review}/lapor', [PartnerReviewController::class, 'report'])->name('reviews.report');
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::get('/analitik', [AdminAnalyticsController::class, 'index'])->name('analytics');

    Route::get('/akun', [UserController::class, 'index'])->name('users.index');
    Route::patch('/akun/{user}/status', [UserController::class, 'toggle'])->name('users.toggle');

    Route::get('/partner', [AdminPartnerController::class, 'index'])->name('partners.index');
    Route::get('/partner/{store}', [AdminPartnerController::class, 'show'])->name('partners.show');
    Route::post('/partner/{store}/verifikasi', [AdminPartnerController::class, 'verify'])->name('partners.verify');

    Route::get('/produk', [AdminProductController::class, 'index'])->name('products.index');
    Route::post('/produk/{product}/verifikasi', [AdminProductController::class, 'verify'])->name('products.verify');
    Route::delete('/produk/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/pesanan', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [AdminOrderController::class, 'show'])->name('orders.show');

    Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/laporan/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
    Route::get('/laporan/excel', [ReportController::class, 'exportExcel'])->name('reports.excel');

    Route::get('/ulasan', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::patch('/ulasan/{review}/sembunyikan', [AdminReviewController::class, 'toggle'])->name('reviews.toggle');
    Route::delete('/ulasan/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('/laporan-ulasan/{report}/tanggapi', [AdminReviewController::class, 'respond'])->name('reviews.respond');

    Route::get('/kategori', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/kategori', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/kategori/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/kategori/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/merek', [BrandController::class, 'index'])->name('brands.index');
    Route::post('/merek', [BrandController::class, 'store'])->name('brands.store');
    Route::put('/merek/{brand}', [BrandController::class, 'update'])->name('brands.update');
    Route::delete('/merek/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');

    Route::get('/atribut', [SpecAttributeController::class, 'index'])->name('spec-attributes.index');
    Route::post('/atribut', [SpecAttributeController::class, 'store'])->name('spec-attributes.store');
    Route::put('/atribut/{specAttribute}', [SpecAttributeController::class, 'update'])->name('spec-attributes.update');
    Route::delete('/atribut/{specAttribute}', [SpecAttributeController::class, 'destroy'])->name('spec-attributes.destroy');

    Route::get('/pencairan', [PayoutController::class, 'index'])->name('payouts.index');
    Route::patch('/pencairan/{payout}/bayar', [PayoutController::class, 'markPaid'])->name('payouts.paid');
});

require __DIR__.'/auth.php';
