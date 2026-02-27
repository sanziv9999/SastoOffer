<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return Inertia::render('CustomerDashboard', [
        'auth' => [
            'user' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'role' => 'user'
            ]
        ]
    ]);
});