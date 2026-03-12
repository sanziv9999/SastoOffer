<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

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
            ->with(['vendor', 'offerTypes', 'images', 'feature'])
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
                    'is_featured' => (bool) $deal->is_featured,
                    'is_deal_of_day' => (bool) $deal->is_deal_of_day,
                    'is_best_seller' => (bool) $deal->is_best_seller,
                    'is_new_arrival' => (bool) $deal->is_new_arrival,
                    'rank' => (int) $deal->rank,
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
            ->with(['vendor', 'offerTypes', 'images', 'feature'])
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
                    'is_featured' => (bool) $deal->is_featured,
                    'is_deal_of_day' => (bool) $deal->is_deal_of_day,
                    'is_best_seller' => (bool) $deal->is_best_seller,
                    'is_new_arrival' => (bool) $deal->is_new_arrival,
                    'rank' => (int) $deal->rank,
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

    public function toggleDealFeatured(Deal $deal): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->hasRole('admin')) {
            abort(403);
        }

        $feature = $deal->feature()->firstOrCreate(['deal_id' => $deal->id], ['is_featured' => false]);
        $feature->is_featured = ! $feature->is_featured;
        $feature->save();

        return back()->with('success', 'Featured status updated.');
    }

    public function updateDealFlags(Request $request, Deal $deal): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->hasRole('admin')) {
            abort(403);
        }

        $data = $request->validate([
            'is_featured' => ['nullable', 'boolean'],
            'is_deal_of_day' => ['nullable', 'boolean'],
            'is_best_seller' => ['nullable', 'boolean'],
            'is_new_arrival' => ['nullable', 'boolean'],
            'rank' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ]);

        $feature = $deal->feature()->firstOrCreate(['deal_id' => $deal->id], [
            'is_featured' => false,
            'is_deal_of_day' => false,
            'is_best_seller' => false,
            'is_new_arrival' => false,
            'rank' => 0,
        ]);

        foreach (['is_featured', 'is_deal_of_day', 'is_best_seller', 'is_new_arrival', 'rank'] as $key) {
            if (array_key_exists($key, $data)) {
                $feature->{$key} = $key === 'rank' ? (int) $data[$key] : (bool) $data[$key];
            }
        }

        $feature->save();

        return back()->with('success', 'Deal flags updated.');
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
