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
            ->with(['dealOfferType.deal.category.parent', 'dealOfferType.deal.images', 'dealOfferType.deal.vendor.defaultAddress', 'dealOfferType.offerType'])
            ->latest()
            ->get()
            ->pluck('dealOfferType')
            ->filter();

        $mappedDeals = $pivots->map(function ($pivot) {
            $deal = $pivot->deal;
            $discountPct = (float) ($pivot->savings_percent ?? $pivot->discount_percent ?? 0);

            return [
                'id'                => $deal?->id,
                'offerPivotId'      => $pivot->id,
                'title'             => $deal?->title,
                'categoryName'      => optional($deal?->category?->parent)->name ?? ($deal?->category?->name ?? 'Uncategorized'),
                'originalPrice'     => $pivot->original_price !== null ? (float) $pivot->original_price : 0,
                'discountedPrice'   => $pivot->final_price !== null ? (float) $pivot->final_price : 0,
                'discountPercentage'=> $discountPct > 0 ? (int) $discountPct : null,
                'image'             => $deal?->images?->first()?->image_url ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&fit=crop',
                'featured'          => (bool) ($deal?->is_featured ?? false),
                'offerTypeTitle'    => $pivot->offerType?->display_name ?? null,
                'cityName'          => $deal?->vendor?->defaultAddress?->municipality ?? 'City',
                'timeLeft'          => optional($pivot->ends_at)?->diffForHumans() ?? 'soon',
            ];
        });

        return view('wishlist', [
            'deals' => $mappedDeals
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
