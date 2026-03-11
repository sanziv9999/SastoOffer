<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Auth\AuthController;

// The one and only route you'll ever need for the frontend flow!
Route::get('/', function () {
    return Inertia::render('AppShell');
})->where('any', '.*');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');

Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Vendor Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/vendor/dashboard', [\App\Http\Controllers\DealController::class, 'dashboard'])->name('vendor.dashboard');
    Route::get('/vendor/deals', [\App\Http\Controllers\DealController::class, 'manageDeals'])->name('vendor.deals.index');
    Route::get('/vendor/deals/create', [\App\Http\Controllers\DealController::class, 'create'])->name('vendor.deals.create');
    Route::post('/vendor/deals', [\App\Http\Controllers\DealController::class, 'store'])->name('vendor.deals.store');
    Route::get('/vendor/deals/{deal}/edit', [\App\Http\Controllers\DealController::class, 'editDeal'])->name('vendor.deals.edit');
    Route::put('/vendor/deals/{deal}', [\App\Http\Controllers\DealController::class, 'updateDeal'])->name('vendor.deals.update');
    
    // Vendor Profile & Settings
    Route::get('/vendor/settings', [\App\Http\Controllers\VendorProfileController::class, 'edit'])->name('vendor.settings');
    Route::put('/vendor/settings', [\App\Http\Controllers\VendorProfileController::class, 'updateSettings'])->name('vendor.settings.update');
    Route::get('/vendor-profile/{vendorProfile:id}', [\App\Http\Controllers\VendorProfileController::class, 'show'])->name('vendor-profile.show');
    Route::put('/vendor-profiles/{vendorProfile}', [\App\Http\Controllers\VendorProfileController::class, 'update'])->name('vendor-profiles.update');
});

// Public Deal routes
Route::get('/deals/{deal}', [\App\Http\Controllers\DealController::class, 'showDeal'])->name('deals.show');

// Admin Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/vendors', [\App\Http\Controllers\VendorProfileController::class, 'index'])->name('admin.vendors.index');
});
