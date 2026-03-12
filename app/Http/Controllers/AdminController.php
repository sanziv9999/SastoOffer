<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class AdminController extends Controller
{
    public function index()
    {
        return Inertia::render('AdminDashboard', [
            'stats' => [],
            'pendingDeals' => [],
            'recentUsers' => [],
            'vendorsList' => [],
            'systemAlerts' => [],
        ]);
    }

    public function users()
    {
        return Inertia::render('admin/AdminUsers', [
            'users' => [],
        ]);
    }

    public function vendors()
    {
        return Inertia::render('admin/AdminVendors', [
            'vendors' => [],
        ]);
    }

    public function deals()
    {
        return Inertia::render('admin/AdminDeals', [
            'deals' => [],
        ]);
    }

    public function pendingDeals()
    {
        return Inertia::render('admin/AdminPendingDeals', [
            'pendingDeals' => [],
        ]);
    }

    public function reports()
    {
        return Inertia::render('admin/AdminReports', [
            'statsData' => [],
        ]);
    }

    public function revenueReports()
    {
        return Inertia::render('admin/AdminRevenueReports');
    }

    public function userAnalytics()
    {
        return Inertia::render('admin/AdminUserAnalytics');
    }
}
