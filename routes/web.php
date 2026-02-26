<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BusinessSubCategoryController;
use App\Http\Controllers\BusinessTypeController;
use App\Http\Controllers\CustomerProfileController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\OfferTypeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VendorProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Guest: login & register
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('images', [ImageController::class, 'store'])->name('images.store');
    Route::delete('images/{image}', [ImageController::class, 'destroy'])->name('images.destroy');
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::resource('deals', DealController::class);
    Route::resource('vendor-profiles', VendorProfileController::class)->parameters(['vendor-profiles' => 'vendorProfile']);
    Route::resource('customer-profiles', CustomerProfileController::class)->parameters(['customer-profiles' => 'customerProfile']);
    Route::resource('addresses', AddressController::class);
    Route::resource('business-types', BusinessTypeController::class)->parameters(['business-types' => 'businessType']);
    Route::resource('business-sub-categories', BusinessSubCategoryController::class)->parameters(['business-sub-categories' => 'businessSubCategory']);
    Route::resource('offer-types', OfferTypeController::class)->parameters(['offer-types' => 'offerType']);
});
