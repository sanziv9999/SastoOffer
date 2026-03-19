<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\VendorProfileController;
use App\Http\Controllers\VendorAnalyticsController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Admin\PrimaryCategoryCrudController;
use App\Http\Controllers\Admin\OfferTypeCrudController;
use App\Http\Controllers\Admin\DisplayTypeCrudController;
use App\Http\Controllers\CheckoutController;

// ——— Public (no auth) ———
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/search', [PageController::class, 'search'])->name('search');
Route::get('/forgot-password', [PageController::class, 'forgotPassword'])->name('password.request');
Route::get('/checkout', fn () => redirect()->route('cart.index'))->name('checkout');
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/toggle/{offerPivotId}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

// ——— Cart ———
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'store'])->name('cart.store');
Route::get('/cart/summary', [CartController::class, 'getSummary'])->name('cart.summary');
Route::middleware(['auth'])->group(function () {
    Route::put('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::post('/checkout', [CheckoutController::class, 'placeOrder'])->name('checkout.place');
    Route::get('/order/{order}/confirmation', [CheckoutController::class, 'confirmation'])->name('order.confirmation');
});

// Public deal detail routes.
// - Canonical slug URL: /deals/{deal-slug}
// - Offer-specific URL (kept for compatibility): /deals/offer/{pivot-id}
Route::get('/deals/offer/{dealOfferType}', [DealController::class, 'showDeal'])->name('deals.show');
Route::get('/deals/{deal}', [DealController::class, 'showDealByDealId'])->name('deals.show.by-deal');

// Legacy: /deals/deal/{deal} redirects/handles old id URLs.
Route::get('/deals/deal/{deal}', [DealController::class, 'showDealByDealId']);
Route::get('/deal/{id}', function ($id) {
    return redirect()->route('deals.show.by-deal', ['deal' => $id]);
})->name('deal.show.redirect');

// Vendor profile (public – canonical slug URL)
Route::get('/vendor-profile/{vendorProfile}', [VendorProfileController::class, 'show'])->name('vendor-profile.show');

// ——— Auth (guest) ———
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ——— Customer dashboard (auth) ———
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/favorites', [DashboardController::class, 'favorites'])->name('dashboard.favorites');
    Route::get('/dashboard/purchases', [CheckoutController::class, 'myOrders'])->name('dashboard.purchases');
    Route::get('/dashboard/purchases/{id}', [DashboardController::class, 'voucherDetail'])->name('dashboard.purchases.voucher');
    Route::get('/dashboard/reviews', [DashboardController::class, 'reviews'])->name('dashboard.reviews');
    Route::get('/dashboard/reviews/edit/{id}', [DashboardController::class, 'editReview'])->name('dashboard.reviews.edit');
    Route::get('/dashboard/settings', [DashboardController::class, 'settings'])->name('dashboard.settings');
    Route::post('/dashboard/settings/address', [DashboardController::class, 'saveAddress'])->name('dashboard.settings.address');
    Route::post('/dashboard/profile', [CustomerProfileController::class, 'update'])->name('dashboard.profile.update');
    Route::post('/dashboard/profile/avatar', [CustomerProfileController::class, 'updateAvatar'])->name('dashboard.profile.avatar');
});

// ——— Vendor (auth) ———
Route::middleware(['auth'])->group(function () {
    Route::middleware(['vendor.approved'])->group(function () {
        Route::get('/vendor/dashboard', [DealController::class, 'dashboard'])->name('vendor.dashboard');
        Route::get('/vendor', [DealController::class, 'dashboard']);
        Route::get('/vendor/create-deal', [DealController::class, 'create'])->name('vendor.create-deal');
        Route::get('/vendor/deals', [DealController::class, 'manageDeals'])->name('vendor.deals.index');
        Route::get('/vendor/deals/create', [DealController::class, 'create'])->name('vendor.deals.create');
        Route::post('/vendor/deals', [DealController::class, 'store'])->name('vendor.deals.store');
        Route::get('/vendor/deals/{deal}', [DealController::class, 'viewDeal'])->name('vendor.deals.view');
        Route::get('/vendor/deals/{deal}/edit', [DealController::class, 'editDeal'])->name('vendor.deals.edit');
        Route::put('/vendor/deals/{deal}', [DealController::class, 'updateDeal'])->name('vendor.deals.update');
        Route::get('/vendor/deals/{deal}/offers', [DealController::class, 'offers'])->name('vendor.deals.offers');
        Route::post('/vendor/deals/{deal}/offers', [DealController::class, 'attachOffer'])->name('vendor.deals.offers.attach');
        Route::put('/vendor/deals/{deal}/offers/{offerType}', [DealController::class, 'updateOffer'])->name('vendor.deals.offers.update');
        Route::delete('/vendor/deals/{deal}/offers/{offerType}', [DealController::class, 'removeOffer'])->name('vendor.deals.offers.remove');

        Route::get('/vendor/analytics', [VendorAnalyticsController::class, 'index'])->name('vendor.analytics');
        Route::get('/vendor/orders', [VendorAnalyticsController::class, 'orders'])->name('vendor.orders');
        Route::patch('/vendor/orders/{order}/status', [VendorAnalyticsController::class, 'updateOrderStatus'])->name('vendor.orders.status');
        Route::get('/vendor/customers', [VendorAnalyticsController::class, 'customers'])->name('vendor.customers');
        Route::get('/vendor/customers/history', [VendorAnalyticsController::class, 'customerHistory'])->name('vendor.customers.history');
        Route::get('/vendor/sales-history', [VendorAnalyticsController::class, 'salesHistory'])->name('vendor.sales-history');
        Route::get('/vendor/reviews', [VendorAnalyticsController::class, 'reviews'])->name('vendor.reviews');
    });

    Route::get('/vendor/settings', [VendorProfileController::class, 'edit'])->name('vendor.settings');
    Route::put('/vendor/settings', [VendorProfileController::class, 'updateSettings'])->name('vendor.settings.update');
    Route::put('/vendor-profiles/{vendorProfile}', [VendorProfileController::class, 'update'])->name('vendor-profiles.update');
});

// ——— Admin (auth) ———
Route::middleware(['auth'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::resource('/admin/primary-categories', PrimaryCategoryCrudController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
        ->names('admin.primary-categories');
    Route::resource('/admin/offer-types', OfferTypeCrudController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
        ->names('admin.offer-types');
    Route::resource('/admin/display-types', DisplayTypeCrudController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
        ->names('admin.display-types');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/admin/vendors', [VendorProfileController::class, 'index'])->name('admin.vendors.index');
    Route::get('/admin/vendors/{vendorProfile}', [VendorProfileController::class, 'adminShow'])->name('admin.vendors.show');
    Route::patch('/admin/vendors/{vendorProfile}/verified-status', [VendorProfileController::class, 'updateVerifiedStatus'])->name('admin.vendors.verified-status');
    Route::get('/admin/deals', [AdminController::class, 'deals'])->name('admin.deals');
    Route::get('/admin/deals/pending', [AdminController::class, 'pendingDeals'])->name('admin.deals.pending');
    Route::get('/admin/deals/{deal}/view', [AdminController::class, 'viewDeal'])->name('admin.deals.view');
    Route::patch('/admin/deals/{deal}/status', [AdminController::class, 'updateDealStatus'])->name('admin.deals.status');
    Route::patch('/admin/deals/offers/{dealOfferType}/status', [AdminController::class, 'updateOfferStatus'])->name('admin.deals.offers.status');
    Route::patch('/admin/deals/offers/{dealOfferType}/display-types', [AdminController::class, 'updateOfferDisplayTypes'])->name('admin.deals.offers.display-types');
    Route::post('/admin/deals/{deal}/toggle-featured', [AdminController::class, 'toggleDealFeatured'])->name('admin.deals.toggle-featured');
    Route::patch('/admin/deals/{deal}/flags', [AdminController::class, 'updateDealFlags'])->name('admin.deals.flags');
    Route::get('/admin/featured-ranking', [AdminController::class, 'featuredRanking'])->name('admin.featured-ranking');
    Route::post('/admin/featured-ranking/{deal}/move', [AdminController::class, 'moveFeaturedRank'])->name('admin.featured-ranking.move');
    Route::get('/admin/reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::get('/admin/reports/revenue', [AdminController::class, 'revenueReports'])->name('admin.reports.revenue');
    Route::get('/admin/reports/users', [AdminController::class, 'userAnalytics'])->name('admin.reports.users');
});

// ——— 404 fallback (Inertia) ———
Route::fallback([PageController::class, 'notFound']);
