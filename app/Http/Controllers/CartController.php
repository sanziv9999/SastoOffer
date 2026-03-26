<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\DealOfferType;
use App\Models\OfferType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('info', 'Please login to view your cart.');
        }

        $cartItems = auth()->user()->cartItems()->with(['offerType.deal.images', 'offerType.offerType'])->get();
        
        $mappedItems = $cartItems->map(fn($item) => $this->mapCartItem($item));
        $total = $mappedItems->sum(fn($i) => $i['discountedPrice'] * $i['quantity']);

        $featuredDeals = DealOfferType::where('status', 'active')
            ->whereHas('displayTypes', fn($q) => $q->where('name', 'featured'))
            ->with(['deal.category.parent', 'deal.images', 'deal.vendor.defaultAddress', 'offerType', 'displayTypes'])
            ->take(8)
            ->get()
            ->map(fn($offer) => $offer->toCardData());

        return view('cart.index', [
            'items' => $mappedItems,
            'total' => $total,
            'featuredDeals' => $featuredDeals
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'offerPivotId' => 'required|exists:deal_offer_type,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $offerPivot = DealOfferType::with('offerType')->findOrFail((int) $request->offerPivotId);
        $isFirstXOffer = $this->isFirstXCustomersOffer($offerPivot);
        $requestedQty = (int) $request->quantity;

        if ($isFirstXOffer && $requestedQty > 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'This is a limited first-X offer. You can order only 1 quantity for this offer.'
            ], 422);
        }

        $cartItem = CartItem::where('user_id', auth()->id())
            ->where('deal_offer_type_id', $request->offerPivotId)
            ->first();

        if ($cartItem) {
            if ($isFirstXOffer) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This is a limited first-X offer. You can order only 1 quantity for this offer.'
                ], 422);
            }
            $cartItem->increment('quantity', $requestedQty);
        } else {
            $cartItem = CartItem::create([
                'user_id' => auth()->id(),
                'deal_offer_type_id' => $request->offerPivotId,
                'quantity' => $requestedQty
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Item added to cart',
            'cartCount' => auth()->user()->cartItems()->sum('quantity')
        ]);
    }

    public function update(Request $request, CartItem $cartItem)
    {
        if ($cartItem->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate(['quantity' => 'required|integer|min:1']);

        $cartItem->loadMissing('offerType.offerType');
        $isFirstXOffer = $this->isFirstXCustomersOffer($cartItem->offerType);
        $requestedQty = (int) $request->quantity;

        if ($isFirstXOffer && $requestedQty > 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'This is a limited first-X offer. You can order only 1 quantity for this offer.'
            ], 422);
        }

        $cartItem->update(['quantity' => $requestedQty]);

        return response()->json([
            'status' => 'success',
            'itemTotal' => $cartItem->quantity * $cartItem->offerType->final_price,
            'cartTotal' => auth()->user()->cartItems->sum(fn($i) => $i->quantity * $i->offerType->final_price),
            'cartCount' => auth()->user()->cartItems()->sum('quantity')
        ]);
    }

    public function destroy(CartItem $cartItem)
    {
        if ($cartItem->user_id !== auth()->id()) {
            abort(403);
        }

        $cartItem->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'cartTotal' => auth()->user()->cartItems->sum(fn($i) => $i->quantity * $i->offerType->final_price),
                'cartCount' => auth()->user()->cartItems()->sum('quantity')
            ]);
        }

        return back()->with('success', 'Item removed from cart.');
    }

    public function getSummary()
    {
        if (!auth()->check()) {
            return response()->json(['items' => [], 'total' => 0, 'count' => 0]);
        }

        $cartItems = auth()->user()->cartItems()->with(['offerType.deal.images'])->get();
        $mappedItems = $cartItems->map(fn($item) => $this->mapCartItem($item));
        $total = $mappedItems->sum(fn($i) => $i['discountedPrice'] * $i['quantity']);

        return response()->json([
            'items' => $mappedItems,
            'total' => $total,
            'count' => $cartItems->sum('quantity')
        ]);
    }

    private function mapCartItem($item)
    {
        $pivot = $item->offerType;
        $deal = $pivot->deal;
        return [
            'id' => $item->id,
            'offerPivotId' => $pivot->id,
            'title' => $deal->title,
            'dealId' => $deal->id,
            'dealSlug' => $deal->slug,
            'quantity' => $item->quantity,
            'discountedPrice' => (float)$pivot->final_price,
            'originalPrice' => (float)$pivot->original_price,
            'image' => $deal->featuredImageUrl('https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=200'),
            'typeLabel' => $pivot->offerType?->display_name ?? 'Standard Offer',
            'url' => route('deals.show.by-deal', ['deal' => $deal->slug ?: $deal->id]) . '?offer=' . $pivot->id,
        ];
    }

    private function isFirstXCustomersOffer(?DealOfferType $pivot): bool
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
