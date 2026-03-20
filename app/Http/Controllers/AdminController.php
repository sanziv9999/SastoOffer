<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealOfferType;
use App\Models\DisplayType;
use App\Models\FeaturedDealRank;
use App\Models\Order;
use App\Models\User;
use App\Models\VendorProfile;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $totalRevenue = (float) Order::whereNotIn('status', ['cancelled', 'refunded'])->sum('grand_total');
        $totalSales = (int) Order::whereNotIn('status', ['cancelled', 'refunded'])->sum('subtotal');
        $redeemedSalesCount = (int) Order::where('status', 'fulfilled')->count();

        $totalUsers = (int) User::count();
        $totalVendors = (int) VendorProfile::count();
        $activeDealsCount = (int) Deal::where('status', 'active')->count();

        $pendingDeals = Deal::query()
            ->with(['vendor', 'offerTypes', 'images'])
            ->where('status', 'pending')
            ->latest()
            ->take(8)
            ->get()
            ->map(function (Deal $deal) {
                $offerType = $deal->offerTypes->first();
                $offer = $offerType?->pivot;
                $base = $deal->base_price !== null ? (float) $deal->base_price : null;

                return [
                    'id' => $deal->id,
                    'title' => $deal->title,
                    'vendorName' => $deal->vendor?->business_name,
                    'discountedPrice' => $offer ? (float) $offer->final_price : $base,
                    'originalPrice' => $offer ? (float) $offer->original_price : $base,
                    'type' => $offerType?->name ?? 'offer',
                    'createdAt' => $deal->created_at?->toIso8601String(),
                    'image' => $deal->featuredImageUrl(),
                    'slug' => $deal->slug,
                ];
            })
            ->values()
            ->all();

        $recentUsers = User::query()
            ->latest()
            ->take(8)
            ->get()
            ->map(function (User $u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'role' => $u->getRoleNames()->first() ?? 'customer',
                    'avatar' => null,
                    'createdAt' => $u->created_at?->toIso8601String(),
                ];
            })
            ->values()
            ->all();

        $vendorsList = VendorProfile::query()
            ->with(['user', 'images'])
            ->latest()
            ->take(8)
            ->get()
            ->map(function (VendorProfile $vendor) {
                return [
                    'id' => $vendor->id,
                    'businessName' => $vendor->business_name,
                    'contactEmail' => $vendor->public_email ?: $vendor->user?->email,
                    'averageRating' => round((float) ($vendor->reviews()->avg('rating') ?? 0), 1),
                    'createdAt' => $vendor->created_at?->toIso8601String(),
                    'logo' => $vendor->images?->firstWhere('attribute_name', 'logo')?->image_url,
                ];
            })
            ->values()
            ->all();

        $monthlyRevenue = Order::query()
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->whereDate('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get()
            ->groupBy(fn (Order $order) => $order->created_at->format('Y-m'))
            ->sortKeys()
            ->map(fn ($group, $key) => [
                'month' => \Carbon\Carbon::parse($key . '-01')->format('M'),
                'amount' => round((float) $group->sum('grand_total'), 2),
            ])
            ->values()
            ->all();

        $userGrowth = User::query()
            ->whereDate('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get()
            ->groupBy(fn (User $user) => $user->created_at->format('Y-m'))
            ->sortKeys()
            ->map(fn ($group, $key) => [
                'month' => \Carbon\Carbon::parse($key . '-01')->format('M'),
                'users' => $group->count(),
            ])
            ->values()
            ->all();

        $systemAlerts = collect();
        if (count($pendingDeals) > 0) {
            $systemAlerts->push([
                'type' => 'warning',
                'title' => 'Deals pending approval',
                'description' => count($pendingDeals) . ' deal(s) are waiting for admin review.',
                'actionLabel' => 'Review pending deals',
            ]);
        }
        if ($totalVendors === 0) {
            $systemAlerts->push([
                'type' => 'info',
                'title' => 'No vendors found',
                'description' => 'No vendor profiles are registered yet.',
                'actionLabel' => null,
            ]);
        }

        return Inertia::render('AdminDashboard', [
            'stats' => [
                'totalRevenue' => $totalRevenue,
                'totalUsers' => $totalUsers,
                'totalVendors' => $totalVendors,
                'activeDealsCount' => $activeDealsCount,
                'totalSales' => $totalSales,
                'redeemedSalesCount' => $redeemedSalesCount,
            ],
            'pendingDeals' => $pendingDeals,
            'recentUsers' => $recentUsers,
            'vendorsList' => $vendorsList,
            'systemAlerts' => $systemAlerts->values()->all(),
            'monthlyRevenue' => $monthlyRevenue,
            'userGrowth' => $userGrowth,
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
                    'status' => $u->status,
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

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $admin = auth()->user();
        if (! $admin || ! $admin->hasRole('admin')) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:customer,vendor,admin'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);
        $user->syncRoles([$data['role']]);

        return back()->with('success', 'User updated successfully.');
    }

    public function suspendUser(User $user): RedirectResponse
    {
        $admin = auth()->user();
        if (! $admin || ! $admin->hasRole('admin')) {
            abort(403);
        }

        if ((int) $admin->id === (int) $user->id) {
            return back()->with('error', 'You cannot suspend your own account.');
        }

        $user->status = 'suspended';
        $user->save();

        return back()->with('success', 'User suspended successfully.');
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
            ->with(['vendor', 'images', 'offerPivots.offerType', 'offerPivots.displayTypes'])
            ->when($status && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->through(function (Deal $deal) {
                $offers = $deal->offerPivots
                    ->sortByDesc(fn ($p) => $p->status === 'active')
                    ->values()
                    ->map(function ($pivot) {
                        return [
                            'id' => $pivot->id,
                            'offerTypeTitle' => $pivot->offerType?->display_name ?? $pivot->offerType?->name ?? 'Offer',
                            'offerTypeName' => $pivot->offerType?->name ?? $pivot->offerType?->slug ?? null,
                            'status' => $pivot->status,
                            'discountedPrice' => $pivot->final_price !== null ? (float) $pivot->final_price : null,
                            'originalPrice' => $pivot->original_price !== null ? (float) $pivot->original_price : null,
                            'currencyCode' => $pivot->currency_code,
                            'endDate' => $pivot->ends_at?->toIso8601String(),
                            'displayTypeIds' => $pivot->displayTypes->pluck('id')->values()->all(),
                            'displayTypeNames' => $pivot->displayTypes->pluck('name')->values()->all(),
                        ];
                    })
                    ->all();

                $primaryOffer = $offers[0] ?? null;
                $base = $deal->base_price !== null ? (float) $deal->base_price : null;
                return [
                    'id' => $deal->id,
                    'title' => $deal->title,
                    'status' => $deal->status,
                    'vendorName' => $deal->vendor?->business_name,
                    'basePrice' => $base,
                    'is_featured' => (bool) $deal->is_featured,
                    'is_deal_of_day' => (bool) $deal->is_deal_of_day,
                    'is_best_seller' => (bool) $deal->is_best_seller,
                    'is_new_arrival' => (bool) $deal->is_new_arrival,
                    'offerPivotId' => $primaryOffer['id'] ?? null,
                    'offerTypeTitle' => $primaryOffer['offerTypeTitle'] ?? null,
                    'discountedPrice' => $primaryOffer['discountedPrice'] ?? $base,
                    'originalPrice' => $primaryOffer['originalPrice'] ?? $base,
                    'endDate' => $primaryOffer['endDate'] ?? null,
                    'offers' => $offers,
                    'image' => $deal->featuredImageUrl(),
                ];
            })
            ->withQueryString();

        return Inertia::render('admin/AdminDeals', [
            'deals' => $deals,
            'displayTypes' => DisplayType::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (DisplayType $dt) => ['id' => $dt->id, 'name' => $dt->name])
                ->all(),
            'filters' => [
                'search' => $search,
                'status' => $status ?? 'all',
            ],
        ]);
    }

    public function pendingDeals(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $pendingDeals = Deal::query()
            ->with(['vendor', 'offerTypes', 'images', 'activeOfferPivots.displayTypes'])
            ->where('status', 'pending')
            ->when($search !== '', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->through(function (Deal $deal) {
                $offerType = $deal->offerTypes->first();
                $offer = $offerType?->pivot;
                $base = $deal->base_price !== null ? (float) $deal->base_price : null;
                return [
                    'id' => $deal->id,
                    'title' => $deal->title,
                    'status' => $deal->status,
                    'vendorName' => $deal->vendor?->business_name,
                    'is_featured' => (bool) $deal->is_featured,
                    'is_deal_of_day' => (bool) $deal->is_deal_of_day,
                    'is_best_seller' => (bool) $deal->is_best_seller,
                    'is_new_arrival' => (bool) $deal->is_new_arrival,
                    'offerPivotId' => $offer?->id,
                    'offerTypeTitle' => $offerType?->display_name,
                    'discountedPrice' => $offer ? (float) $offer->final_price : $base,
                    'originalPrice' => $offer ? (float) $offer->original_price : $base,
                    'createdAt' => $deal->created_at?->toIso8601String(),
                    'type' => $offerType?->name ?? $offerType?->slug,
                    'image' => $deal->featuredImageUrl(),
                ];
            })
            ->withQueryString();

        return Inertia::render('admin/AdminPendingDeals', [
            'pendingDeals' => $pendingDeals,
            'filters' => ['search' => $search],
        ]);
    }

    public function viewDeal(Deal $deal)
    {
        $deal->load([
            'vendor',
            'category.parent',
            'images',
            'offerPivots.offerType',
            'offerPivots.displayTypes',
        ]);

        $payload = [
            'id' => $deal->id,
            'title' => $deal->title,
            'status' => $deal->status,
            'basePrice' => $deal->base_price !== null ? (float) $deal->base_price : null,
            'vendorName' => $deal->vendor?->business_name,
            'category' => [
                'name' => $deal->category?->name,
                'parentName' => $deal->category?->parent?->name,
            ],
            'shortDesc' => $deal->short_description,
            'description' => $deal->long_description,
            'image' => $deal->featuredImageUrl(),
            'offers' => $deal->offerPivots->map(function ($pivot) {
                return [
                    'id' => $pivot->id,
                    'offerTypeTitle' => $pivot->offerType?->display_name ?? $pivot->offerType?->name ?? 'Offer',
                    'offerTypeName' => $pivot->offerType?->name ?? null,
                    'status' => $pivot->status,
                    'originalPrice' => $pivot->original_price !== null ? (float) $pivot->original_price : null,
                    'finalPrice' => $pivot->final_price !== null ? (float) $pivot->final_price : null,
                    'currencyCode' => $pivot->currency_code,
                    'startsAt' => $pivot->starts_at?->toIso8601String(),
                    'endsAt' => $pivot->ends_at?->toIso8601String(),
                    'displayTypes' => $pivot->displayTypes->pluck('name')->values()->all(),
                ];
            })->values()->all(),
        ];

        return Inertia::render('admin/AdminDealView', [
            'deal' => $payload,
        ]);
    }

    public function featuredRanking(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $featured = Deal::query()
            ->with(['vendor', 'offerTypes', 'images', 'activeOfferPivots.displayTypes'])
            ->whereHas('activeOfferPivots.displayTypes', fn ($q) => $q->where('name', 'featured'))
            ->when($search !== '', fn ($q) => $q->where('title', 'like', "%{$search}%"))
            ->get()
            ->map(function (Deal $deal) {
                $offerType = $deal->offerTypes->first();
                $offer = $offerType?->pivot;
                $base = $deal->base_price !== null ? (float) $deal->base_price : null;
                $rank = FeaturedDealRank::where('deal_id', $deal->id)->value('rank');
                return [
                    'id' => $deal->id,
                    'title' => $deal->title,
                    'vendorName' => $deal->vendor?->business_name,
                    'rank' => $rank,
                    'offerPivotId' => $offer?->id,
                    'offerTypeTitle' => $offerType?->display_name,
                    'discountedPrice' => $offer ? (float) $offer->final_price : $base,
                    'originalPrice' => $offer ? (float) $offer->original_price : $base,
                    'image' => $deal->featuredImageUrl(),
                ];
            })
            ->sortBy(fn ($d) => $d['rank'] ?? 999999)
            ->values();

        $maxRank = (int) (FeaturedDealRank::max('rank') ?? 0);

        return Inertia::render('admin/FeaturedRanking', [
            'featuredDeals' => $featured,
            'filters' => ['search' => $search, 'maxRank' => $maxRank],
        ]);
    }

    public function moveFeaturedRank(Request $request, Deal $deal): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->hasRole('admin')) {
            abort(403);
        }

        $direction = $request->validate([
            'direction' => ['required', 'in:up,down'],
        ])['direction'];

        DB::transaction(function () use ($deal, $direction) {
            // Ensure the deal is featured
            if (! $deal->fresh()->is_featured) {
                abort(422, 'Deal is not featured.');
            }

            $current = FeaturedDealRank::lockForUpdate()->firstOrCreate(
                ['deal_id' => $deal->id],
                ['rank' => ((int) (FeaturedDealRank::lockForUpdate()->max('rank') ?? 0)) + 1]
            );

            $currentRank = (int) $current->rank;

            $swapWith = FeaturedDealRank::query()
                ->lockForUpdate()
                ->when($direction === 'up',
                    fn ($q) => $q->where('rank', '<', $currentRank)->orderByDesc('rank'),
                    fn ($q) => $q->where('rank', '>', $currentRank)->orderBy('rank')
                )
                ->first();

            if (! $swapWith) {
                return;
            }

            $otherRank = (int) $swapWith->rank;

            // Swap safely under UNIQUE(rank) using temp value outside range
            $temp = (int) (FeaturedDealRank::lockForUpdate()->max('rank') ?? 0) + 1000;
            $swapWith->rank = $temp;
            $swapWith->save();

            $current->rank = $otherRank;
            $current->save();

            $swapWith->rank = $currentRank;
            $swapWith->save();
        });

        return back()->with('success', 'Featured rank updated.');
    }

    public function toggleDealFeatured(Deal $deal): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->hasRole('admin')) {
            abort(403);
        }

        $makeFeatured = ! $deal->fresh()->is_featured;
        $this->setDealDisplayType($deal, 'featured', $makeFeatured);

        // If a deal is un-featured, remove it from the ranking table
        if (! $makeFeatured) {
            FeaturedDealRank::where('deal_id', $deal->id)->delete();
        } else {
            FeaturedDealRank::firstOrCreate(
                ['deal_id' => $deal->id],
                ['rank' => ((int) (FeaturedDealRank::max('rank') ?? 0)) + 1]
            );
        }

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
        ]);

        $map = [
            'is_featured' => 'featured',
            'is_deal_of_day' => 'deals_of_the_day',
            'is_best_seller' => 'hot_sell',
            'is_new_arrival' => 'new_arrival',
        ];

        foreach ($map as $key => $displayAs) {
            if (array_key_exists($key, $data)) {
                $this->setDealDisplayType($deal, $displayAs, (bool) $data[$key]);
            }
        }

        return back()->with('success', 'Deal flags updated.');
    }

    public function updateOfferDisplayTypes(Request $request, DealOfferType $dealOfferType): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->hasRole('admin')) {
            abort(403);
        }

        $data = $request->validate([
            'display_type_ids' => ['array'],
            'display_type_ids.*' => ['integer', 'exists:display_types,id'],
        ]);

        $ids = collect($data['display_type_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values()->all();

        // Strict per-offer sync (never touches sibling offer rows of same deal).
        DB::table('deal_offer_display')
            ->where('deal_offer_type_id', $dealOfferType->id)
            ->whereNotIn('display_as', $ids ?: [0])
            ->delete();

        $now = now();
        foreach ($ids as $displayTypeId) {
            DB::table('deal_offer_display')->updateOrInsert(
                [
                    'deal_offer_type_id' => $dealOfferType->id,
                    'display_as' => $displayTypeId,
                ],
                [
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        // Keep featured rank in sync using normalized bridge table.
        $featuredId = (int) (DisplayType::where('name', 'featured')->value('id') ?? 0);
        if ($featuredId > 0) {
            $hasFeatured = DB::table('deal_offer_display as dod')
                ->join('deal_offer_type as dot', 'dot.id', '=', 'dod.deal_offer_type_id')
                ->where('dot.deal_id', $dealOfferType->deal_id)
                ->where('dod.display_as', $featuredId)
                ->exists();

            if ($hasFeatured) {
                FeaturedDealRank::firstOrCreate(
                    ['deal_id' => $dealOfferType->deal_id],
                    ['rank' => ((int) (FeaturedDealRank::max('rank') ?? 0)) + 1]
                );
            } else {
                FeaturedDealRank::where('deal_id', $dealOfferType->deal_id)->delete();
            }
        }

        return back()->with('success', 'Offer display tags updated.');
    }

    public function updateDealStatus(Request $request, Deal $deal): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->hasRole('admin')) {
            abort(403);
        }

        $data = $request->validate([
            'status' => ['required', 'in:draft,active,inactive,expired,pending'],
        ]);

        $deal->status = $data['status'];
        $deal->save();

        return back()->with('success', 'Deal status updated.');
    }

    public function updateOfferStatus(Request $request, DealOfferType $dealOfferType): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->hasRole('admin')) {
            abort(403);
        }

        $data = $request->validate([
            'status' => ['required', 'in:draft,active,inactive,expired,pending'],
        ]);

        $dealOfferType->status = $data['status'];
        $dealOfferType->save();

        return back()->with('success', 'Offer status updated.');
    }

    protected function setDealDisplayType(Deal $deal, string $displayTypeName, bool $enabled): void
    {
        $displayTypeId = (int) DisplayType::firstOrCreate(['name' => $displayTypeName])->id;
        $now = now();

        $targetPivotId = DB::table('deal_offer_type')
            ->where('deal_id', $deal->id)
            ->where('status', 'active')
            ->orderBy('id')
            ->value('id');

        if (! $targetPivotId) {
            $targetPivotId = DB::table('deal_offer_type')
                ->where('deal_id', $deal->id)
                ->orderBy('id')
                ->value('id');
        }

        if (! $targetPivotId) {
            return;
        }

        if ($enabled) {
            DB::table('deal_offer_display')->updateOrInsert(
                [
                    'deal_offer_type_id' => $targetPivotId,
                    'display_as' => $displayTypeId,
                ],
                [
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        } else {
            DB::table('deal_offer_display')
                ->where('deal_offer_type_id', $targetPivotId)
                ->where('display_as', $displayTypeId)
                ->delete();
        }

        // Keep ranking table in sync with featured state only.
        if ($displayTypeName === 'featured' && ! $enabled) {
            FeaturedDealRank::where('deal_id', $deal->id)->delete();
        } elseif ($displayTypeName === 'featured' && $enabled) {
            FeaturedDealRank::firstOrCreate(
                ['deal_id' => $deal->id],
                ['rank' => ((int) (FeaturedDealRank::max('rank') ?? 0)) + 1]
            );
        }
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
