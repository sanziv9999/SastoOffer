<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddressRequest;
use App\Models\Address;
use App\Models\CustomerProfile;
use App\Models\Deal;
use App\Models\OfferType;
use App\Models\Order;
use App\Models\Review;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected function getUserWishlistDeals($user): Collection
    {
        if (! $user) {
            return collect();
        }

        return Wishlist::where('user_id', $user->id)
            ->with([
                'deal.category.parent',
                'deal.images',
                'deal.vendor.defaultAddress',
                'deal.activeOfferPivots',
            ])
            ->latest()
            ->get()
            ->pluck('deal')
            ->filter()
            ->map(fn (Deal $deal) => $this->mapWishlistedDeal($deal))
            ->values();
    }

    protected function mapWishlistedDeal(Deal $deal): array
    {
        $base = (float) ($deal->base_price ?? 0);

        $address = $deal->vendor?->defaultAddress;
        $locationLabel = collect([
            $address?->district,
            $address?->tole,
        ])->filter()->implode(', ');
        if ($locationLabel === '') {
            $locationLabel = 'Location';
        }

        return [
            'id' => $deal->id,
            'title' => $deal->title,
            'dealSlug' => $deal->slug,
            'categorySlug' => optional($deal->category?->parent)->slug ?? ($deal->category?->slug ?? 'uncategorized'),
            'categoryName' => optional($deal->category?->parent)->name ?? ($deal->category?->name ?? 'Uncategorized'),
            'originalPrice' => 0,
            'discountedPrice' => $base,
            'discountPercentage' => 0,
            'image' => $deal->featuredImageUrl('https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&fit=crop'),
            'featured' => (bool) ($deal->is_featured ?? false),
            'offerTypeTitle' => null,
            'timeLeft' => null,
            'status' => $deal->status,
            'locationLabel' => $locationLabel,
            'url' => route('deals.show.by-deal', ['deal' => $deal->slug ?? $deal->id]),
        ];
    }

    protected function buildCustomerDashboardPayload($user): array
    {
        if (! $user) {
            return [
                'stats' => [
                    'totalPurchases' => 0,
                    'activeCoupons' => 0,
                    'totalSavings' => 0,
                    'favoriteDealsCount' => 0,
                    'reviewsCount' => 0,
                ],
                'deals' => [],
                'recommendations' => [],
                'recentActivity' => [],
                'purchases' => [],
            ];
        }

        $orders = Order::where('user_id', $user->id)
            ->with(['items'])
            ->latest()
            ->get();

        /** @var Collection<string, Collection<int, Order>> $groupedOrders */
        $groupedOrders = $orders->groupBy(fn (Order $o) => $o->order_number ?: ('ORD-'.$o->id));

        $purchaseGroups = $groupedOrders->map(function (Collection $ordersInGroup, string $orderNumber) {
            /** @var Order $firstOrder */
            $firstOrder = $ordersInGroup->first();
            $allItems = $ordersInGroup->flatMap(fn (Order $o) => $o->items);

            $statusPriority = ['pending', 'paid', 'fulfilled', 'cancelled', 'refunded'];
            $statuses = $ordersInGroup->pluck('status')->filter()->values();
            $aggregatedStatus = collect($statusPriority)->first(fn ($s) => $statuses->contains($s)) ?? 'pending';

            $redeemed = in_array($aggregatedStatus, ['fulfilled', 'cancelled', 'refunded'], true);

            $firstItem = $allItems->first();
            $paidAt = $ordersInGroup->pluck('paid_at')->filter()->max();

            return [
                'id' => (string) ($firstOrder?->id ?? ''),
                'dealId' => $firstItem?->deal_id,
                'dealSlug' => $firstItem?->meta['deal_slug'] ?? null,
                'couponCode' => $orderNumber,
                'redeemed' => $redeemed,
                'redeemedAt' => $paidAt?->toIso8601String(),
                'quantity' => (int) $allItems->sum('quantity'),
                'totalPrice' => (float) $ordersInGroup->sum('grand_total'),
                'createdAt' => $ordersInGroup->max('created_at')?->toIso8601String(),
            ];
        })->values()->sortByDesc(fn ($p) => $p['createdAt'] ?? '');

        $allDealIds = collect($purchaseGroups)
            ->pluck('dealId')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $dealModels = collect();
        if (! empty($allDealIds)) {
            $dealModels = Deal::whereIn('id', $allDealIds)
                ->with(['offerTypes', 'images'])
                ->get();
        }

        $mapDeal = function (Deal $deal) {
            $activePivot = $deal->offerTypes
                ->first(fn ($ot) => ($ot->pivot?->status ?? null) === 'active')
                ?->pivot;

            $base = (float) ($deal->base_price ?? 0);

            $originalPrice = $activePivot ? (float) ($activePivot->original_price ?? $base) : $base;
            $discountedPrice = $activePivot ? (float) ($activePivot->final_price ?? $base) : $base;
            $endDate = $activePivot?->ends_at?->toIso8601String();

            return [
                'id' => $deal->id,
                'title' => $deal->title,
                'slug' => $deal->slug,
                'vendorId' => $deal->vendor_id,
                'image' => $deal->featuredImageUrl('https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&fit=crop'),
                'originalPrice' => $originalPrice,
                'discountedPrice' => $discountedPrice,
                'endDate' => $endDate,
            ];
        };

        $dealsFromPurchases = $dealModels->map(fn (Deal $d) => $mapDeal($d))->values()->all();

        $recommendedDeals = Deal::with(['offerTypes', 'images'])
            ->latest()
            ->take(20)
            ->get()
            ->map(function (Deal $deal) use ($mapDeal) {
                $card = $mapDeal($deal);
                $base = (float) ($card['originalPrice'] ?? 0);
                $disc = (float) ($card['discountedPrice'] ?? 0);
                $discountPct = $base > 0 ? (int) round((($base - $disc) / $base) * 100) : 0;
                $card['discountPct'] = $discountPct;

                return $card;
            })
            ->filter(fn ($d) => ($d['discountPct'] ?? 0) > 0)
            ->sortByDesc(fn ($d) => $d['discountPct'] ?? 0)
            ->take(6)
            ->values()
            ->all();

        $deals = collect($dealsFromPurchases)
            ->concat($recommendedDeals)
            ->unique('id')
            ->values()
            ->all();

        $recentActivity = $purchaseGroups
            ->take(5)
            ->values()
            ->all();

        $totalSavings = (float) $orders->sum('discount_total');

        $favoriteDealsCount = $this->getUserWishlistDeals($user)->count();

        $reviewsCount = Review::where('user_id', $user->id)->count();
        $activeCouponsCount = collect($purchaseGroups)->filter(fn ($p) => ! ($p['redeemed'] ?? false))->count();

        $stats = [
            'totalPurchases' => $purchaseGroups->count(),
            'activeCoupons' => $activeCouponsCount,
            'totalSavings' => $totalSavings,
            'favoriteDealsCount' => $favoriteDealsCount,
            'reviewsCount' => $reviewsCount,
        ];

        return [
            'stats' => $stats,
            'deals' => $deals,
            'recommendations' => $recommendedDeals,
            'recentActivity' => $recentActivity,
            'purchases' => $purchaseGroups->values()->all(),
        ];
    }

    public function index(Request $request)
    {
        $payload = $this->buildCustomerDashboardPayload($request->user());

        return Inertia::render('CustomerDashboard', [
            'stats' => $payload['stats'],
            'recommendations' => $payload['recommendations'],
            'recentActivity' => $payload['recentActivity'],
            'deals' => $payload['deals'],
        ]);
    }

    public function customerDashboardData()
    {
        $payload = $this->buildCustomerDashboardPayload(auth()->user());

        return response()->json($payload);
    }

    public function favorites()
    {
        $favoriteDeals = $this->getUserWishlistDeals(auth()->user())->all();

        return Inertia::render('dashboard/SavedDeals', [
            'favoriteDeals' => $favoriteDeals,
        ]);
    }

    public function purchases()
    {
        return Inertia::render('dashboard/MyPurchases', [
            'purchases' => [],
            'deals' => [],
        ]);
    }

    public function cart()
    {
        $payload = $this->buildCustomerCartPayload(auth()->user());

        return Inertia::render('dashboard/Cart', [
            'items' => $payload['items'],
            'total' => $payload['total'],
            'count' => $payload['count'],
        ]);
    }

    public function cartData()
    {
        return response()->json($this->buildCustomerCartPayload(auth()->user()));
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

    public function settings(Request $request)
    {
        $user = $request->user();

        /** @var CustomerProfile|null $profile */
        $profile = $user?->customerProfile;
        if ($profile) {
            $profile->load('images', 'defaultAddress');
        }

        return Inertia::render('dashboard/Settings', [
            'defaultAddress' => $user?->defaultAddress,
            'profile' => $profile,
        ]);
    }

    public function saveAddress(StoreAddressRequest $request)
    {
        $user = $request->user();

        $data = $request->validated();

        $addressFields = ['province', 'district', 'municipality', 'ward_no', 'tole', 'latitude', 'longitude'];
        $addressData = collect($data)->only($addressFields)->filter()->toArray();

        if (empty($addressData)) {
            return back()->with('error', 'Please provide at least one address field.');
        }

        // Ensure address belongs to the authenticated user
        $addressData['user_id'] = $user->id;
        $addressData['label'] = $data['label'] ?? 'Home';

        // Make this the default address for the user
        Address::where('user_id', $user->id)->update(['is_default' => false]);
        $addressData['is_default'] = true;

        if ($user->defaultAddress) {
            $user->defaultAddress->update($addressData);
            $address = $user->defaultAddress;
        } else {
            $address = Address::create($addressData);
        }

        // If customer profile exists, sync default_address_id
        $user->customerProfile?->update([
            'default_address_id' => $address->id,
        ]);

        return back()->with('success', 'Address saved successfully.');
    }

    protected function buildCustomerCartPayload($user): array
    {
        if (! $user) {
            return [
                'items' => [],
                'total' => 0,
                'count' => 0,
            ];
        }

        // Remove ended/inactive offers from cart so only purchasable items remain.
        $user->cartItems()
            ->whereHas('offerType', fn ($q) => $q->where('status', '!=', 'active'))
            ->delete();

        $items = $user->cartItems()
            ->with(['offerType.deal.images', 'offerType.offerType'])
            ->whereHas('offerType', fn ($q) => $q->where('status', 'active'))
            ->get()
            ->map(fn ($item) => $this->mapCustomerCartItem($item))
            ->values();

        return [
            'items' => $items,
            'total' => $items->sum(fn ($i) => ((float) $i['discountedPrice']) * ((int) $i['quantity'])),
            'count' => $items->sum(fn ($i) => (int) $i['quantity']),
        ];
    }

    protected function mapCustomerCartItem($item): array
    {
        $pivot = $item->offerType;
        $deal = $pivot?->deal;

        return [
            'id' => $item->id,
            'offerPivotId' => $pivot?->id,
            'title' => $deal?->title ?? 'Deal',
            'dealId' => $deal?->id,
            'dealSlug' => $deal?->slug,
            'quantity' => (int) $item->quantity,
            'discountedPrice' => (float) ($pivot?->final_price ?? 0),
            'originalPrice' => (float) ($pivot?->original_price ?? 0),
            'image' => $deal?->featuredImageUrl('https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=200'),
            'typeLabel' => $pivot?->offerType?->display_name ?? 'Standard Offer',
            'url' => $deal
                ? route('deals.show.by-deal', ['deal' => $deal->slug ?: $deal->id]).'?offer='.$pivot->id
                : route('search'),
            'isFirstXOffer' => $this->isFirstXCustomersOffer($pivot),
        ];
    }

    protected function isFirstXCustomersOffer($pivot): bool
    {
        if (! $pivot) {
            return false;
        }

        $offerType = $pivot->offerType;
        if (! $offerType instanceof OfferType) {
            return false;
        }

        $rule = $offerType->calculation_rule;
        if (is_string($rule)) {
            $rule = json_decode($rule, true) ?: [];
        }

        if (! is_array($rule)) {
            return false;
        }

        $availability = $rule['availability'] ?? null;

        return is_array($availability) && (($availability['mode'] ?? null) === 'first_x_customers');
    }
}
