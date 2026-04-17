<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealOfferType;
use App\Support\DealUrl;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Display the wishlist page. 
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login')->with('info', 'Please login to view your wishlist.');
        }

        // Fetch real saved parent deals for the authenticated user
        $deals = \App\Models\Wishlist::where('user_id', $user->id)
            ->with([
                'deal.category.parent',
                'deal.images',
                'deal.vendor.defaultAddress',
                'deal.activeOfferPivots',
            ])
            ->latest()
            ->get()
            ->pluck('deal')
            ->filter();

        $mappedDeals = $deals->map(fn (Deal $deal) => $this->mapDealToCardData($deal));

        $featuredDeals = DealOfferType::where('status', 'active')
            ->whereHas('displayTypes', fn($q) => $q->where('name', 'featured'))
            ->with(['deal.category.parent', 'deal.images', 'deal.vendor.defaultAddress', 'offerType', 'displayTypes'])
            ->take(8)
            ->get()
            ->map(fn($offer) => $offer->toCardData());

        return view('wishlist', [
            'deals' => $mappedDeals,
            'featuredDeals' => $featuredDeals,
        ]);
    }

    /**
     * Toggle a parent deal in the user's wishlist.
     */
    public function toggle(Request $request, int $dealId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        $deal = Deal::find($dealId);
        if (! $deal) {
            return response()->json(['error' => 'deal_not_found'], 404);
        }

        $wishlist = \App\Models\Wishlist::where('user_id', $user->id)
            ->where('deal_id', $dealId)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            return response()->json(['status' => 'removed', 'message' => 'Removed from wishlist']);
        }

        \App\Models\Wishlist::create([
            'user_id' => $user->id,
            'deal_id' => $dealId,
        ]);

        return response()->json(['status' => 'added', 'message' => 'Added to wishlist']);
    }

    protected function mapDealToCardData(Deal $deal): array
    {
        $basePrice = (float) ($deal->base_price ?? 0);

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
            // Wishlist should show parent deal values only (no offer-based discounts).
            'originalPrice' => 0,
            'discountedPrice' => $basePrice,
            'discountPercentage' => 0,
            'image' => $deal->featuredImageUrl('https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&fit=crop'),
            'featured' => (bool) ($deal->is_featured ?? false),
            'offerTypeTitle' => null,
            'timeLeft' => null,
            'status' => $deal->status,
            'locationLabel' => $locationLabel,
            'url' => DealUrl::forDealFirstOffer($deal),
        ];
    }
}
