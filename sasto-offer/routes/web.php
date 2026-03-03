<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// The one and only route you'll ever need for the frontend flow!
Route::get('{any?}', function () {
    return Inertia::render('AppShell');
})->where('any', '.*');