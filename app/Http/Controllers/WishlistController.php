<?php

namespace App\Http\Controllers;

use App\Models\DealOfferType;
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

        // Fetch real saved deals for the authenticated user
        $pivots = \App\Models\Wishlist::where('user_id', $user->id)
            ->with(['dealOfferType.deal.category.parent', 'dealOfferType.deal.images', 'dealOfferType.deal.vendor.defaultAddress', 'dealOfferType.offerType', 'dealOfferType.displayTypes'])
            ->latest()
            ->get()
            ->pluck('dealOfferType')
            ->filter();

        $mappedDeals = $pivots->map(fn($pivot) => $pivot->toCardData());

        $featuredDeals = DealOfferType::where('status', 'active')
            ->whereHas('displayTypes', fn($q) => $q->where('name', 'featured'))
            ->with(['deal.category.parent', 'deal.images', 'deal.vendor.defaultAddress', 'offerType', 'displayTypes'])
            ->take(8)
            ->get()
            ->map(fn($offer) => $offer->toCardData());

        return view('wishlist', [
            'deals' => $mappedDeals,
            'featuredDeals' => $featuredDeals
        ]);
    }

    /**
     * Toggle a deal in the user's wishlist.
     */
    public function toggle(Request $request, $offerPivotId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        $wishlist = \App\Models\Wishlist::where('user_id', $user->id)
            ->where('deal_offer_type_id', $offerPivotId)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            return response()->json(['status' => 'removed', 'message' => 'Removed from wishlist']);
        }

        \App\Models\Wishlist::create([
            'user_id' => $user->id,
            'deal_offer_type_id' => $offerPivotId,
        ]);

        return response()->json(['status' => 'added', 'message' => 'Added to wishlist']);
    }
}
