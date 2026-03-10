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
    Route::post('/vendor/deals', [\App\Http\Controllers\DealController::class, 'store'])->name('vendor.deals.store');
});
