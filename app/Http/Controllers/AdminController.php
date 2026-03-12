<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;

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

    public function users(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $users = User::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->through(function (User $u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'role' => $u->getRoleNames()->first() ?? 'customer',
                    'created_at' => $u->created_at?->toIso8601String(),
                ];
            })
            ->withQueryString();

        return Inertia::render('admin/AdminUsers', [
            'users' => $users,
            'filters' => ['search' => $search],
        ]);
    }

    public function vendors()
    {
        return Inertia::render('admin/AdminVendors', [
            'vendors' => [],
        ]);
    }

    public function deals(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');

        $deals = Deal::query()
            ->with(['vendor', 'offerTypes', 'images'])
            ->when($status && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->through(function (Deal $deal) {
                $offer = $deal->offerTypes->first()?->pivot;
                return [
                    'id' => $deal->id,
                    'title' => $deal->title,
                    'status' => $deal->status,
                    'vendorName' => $deal->vendor?->business_name,
                    'discountedPrice' => $offer ? (float) $offer->final_price : null,
                    'originalPrice' => $offer ? (float) $offer->original_price : null,
                    'endDate' => $deal->ends_at?->toIso8601String(),
                    'image' => $deal->images->first()?->image_url,
                ];
            })
            ->withQueryString();

        return Inertia::render('admin/AdminDeals', [
            'deals' => $deals,
            'filters' => ['search' => $search, 'status' => $status ?? 'all'],
        ]);
    }

    public function pendingDeals(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $pendingDeals = Deal::query()
            ->with(['vendor', 'offerTypes', 'images'])
            ->where('status', 'pending')
            ->when($search !== '', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->through(function (Deal $deal) {
                $offer = $deal->offerTypes->first()?->pivot;
                return [
                    'id' => $deal->id,
                    'title' => $deal->title,
                    'status' => $deal->status,
                    'vendorName' => $deal->vendor?->business_name,
                    'discountedPrice' => $offer ? (float) $offer->final_price : null,
                    'originalPrice' => $offer ? (float) $offer->original_price : null,
                    'createdAt' => $deal->created_at?->toIso8601String(),
                    'type' => $deal->offerTypes->first()?->slug,
                    'image' => $deal->images->first()?->image_url,
                ];
            })
            ->withQueryString();

        return Inertia::render('admin/AdminPendingDeals', [
            'pendingDeals' => $pendingDeals,
            'filters' => ['search' => $search],
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
