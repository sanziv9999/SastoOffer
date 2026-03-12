<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('CustomerDashboard', [
            'stats' => [],
            'recommendations' => [],
            'recentActivity' => [],
            'deals' => [],
        ]);
    }

    public function favorites()
    {
        return Inertia::render('dashboard/SavedDeals', [
            'favoriteDeals' => [],
        ]);
    }

    public function purchases()
    {
        return Inertia::render('dashboard/MyPurchases', [
            'purchases' => [],
            'deals' => [],
        ]);
    }

    public function voucherDetail($id)
    {
        return Inertia::render('dashboard/VoucherDetail', [
            'purchases' => [],
            'deals' => [],
            'vendors' => [],
        ]);
    }

    public function reviews()
    {
        return Inertia::render('dashboard/Reviews', [
            'reviews' => [],
            'deals' => [],
        ]);
    }

    public function editReview($id)
    {
        return Inertia::render('dashboard/EditReview', [
            'reviews' => [],
            'deals' => [],
        ]);
    }

    public function settings()
    {
        return Inertia::render('dashboard/Settings');
    }
}
