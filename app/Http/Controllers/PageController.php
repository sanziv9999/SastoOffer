<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Deal;
use App\Models\Category;
use App\Models\DealOfferType;

class PageController extends Controller
{
    public function home()
    {
        $featuredDeals = DealOfferType::where('status', 'active')
            ->whereHas('displayTypes', fn($q) => $q->where('name', 'featured'))
            ->with(['deal.category.parent', 'deal.images', 'deal.vendor.defaultAddress', 'offerType', 'displayTypes'])
            ->take(8)
            ->get()
            ->map(fn($offer) => $offer->toCardData());

        $recentOffers = DealOfferType::where('status', 'active')
            ->with(['deal.category.parent', 'deal.images', 'deal.vendor.defaultAddress', 'offerType', 'displayTypes'])
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($offer) => $offer->toCardData());

        return view('home', compact('featuredDeals', 'recentOffers'));
    }

    public function search(Request $request)
    {
        $query      = $request->query('q', '');
        $category   = $request->query('category', 'all');      // primary category slug or 'all'
        $subSlug    = $request->query('subcategory');          // optional business subcategory slug
        $district   = trim((string) $request->query('city', $request->query('district', '')));
        $sort       = $request->query('sort', 'relevance');
        $featured   = $request->query('featured') === 'true';
        $type       = $request->query('type', 'all');
        $reqMinPrice = $request->query('minPrice');
        $reqMaxPrice = $request->query('maxPrice');

        // Fetch deals via deal_offer_type (offers) instead of deals table.
        // This means only deals with at least one attached offer will appear here.
        $offerQuery = DealOfferType::query()
            ->with([
                'deal.category.parent',
                'deal.images',
                'deal.vendor.defaultAddress',
                'offerType',
                'displayTypes',
            ])
            ->where('status', 'active')
            ->whereHas('deal', function ($q) use ($query, $featured, $subSlug, $category, $district) {
                $q->when($query !== '', fn ($qq) => $qq->where('title', 'like', '%' . $query . '%'))
                    ->when($subSlug, fn ($qq) => $qq->whereHas('category', fn ($c) => $c->where('slug', $subSlug)))
                    ->when(!$subSlug && $category !== 'all', function ($qq) use ($category) {
                        // Top-level category filter should match:
                        // - leaf category's parent slug, OR
                        // - category slug itself (if deal uses a top-level category directly)
                        $qq->where(function ($w) use ($category) {
                            $w->whereHas('category.parent', fn ($c) => $c->where('slug', $category))
                                ->orWhereHas('category', fn ($c) => $c->where('slug', $category)->whereNull('parent_id'));
                        });
                    })
                    ->when($district !== '' && !in_array($district, ['All Cities', 'All Districts'], true), function ($qq) use ($district) {
                        $needle = mb_strtolower($district);
                        $qq->whereHas('vendor.defaultAddress', function ($addressQ) use ($needle) {
                            $addressQ->whereRaw('LOWER(district) = ?', [$needle]);
                        });
                    });
            });

        if ($featured) {
            $offerQuery->whereHas('displayTypes', fn ($q) => $q->where('name', 'featured'));
        }

        // Deal type filter should filter offer rows directly (one card per offer).
        if ($type !== 'all') {
            $offerQuery->whereHas('offerType', fn ($q) => $q->where('slug', $type)->orWhere('name', $type));
        }

        // Pull a reasonable working set and group in memory (pivot-driven).
        $rawOffers = $offerQuery->take(500)->get();

        // Sorting (each row = one offer card)
        $rawOffers = match ($sort) {
            'priceAsc'     => $rawOffers->sortBy(fn ($p) => (float) ($p->final_price ?? PHP_FLOAT_MAX))->values(),
            'priceDesc'    => $rawOffers->sortByDesc(fn ($p) => (float) ($p->final_price ?? 0))->values(),
            'discountDesc' => $rawOffers->sortByDesc(fn ($p) => (float) ($p->savings_percent ?? $p->discount_percent ?? 0))->values(),
            default        => $rawOffers->sortByDesc(fn ($p) => optional($p->deal)->created_at)->values(),
        };

        $mappedDeals = $rawOffers->take(60)->map(fn(DealOfferType $pivot) => $pivot->toCardData());

        $availableMinPrice = 0;
        $availableMaxPrice = (int) ceil($mappedDeals->max('discountedPrice') ?? 100000);
        
        if ($availableMaxPrice <= $availableMinPrice) {
            $availableMaxPrice = $availableMinPrice + 100;
        }

        $minPrice = $reqMinPrice !== null ? (int) $reqMinPrice : $availableMinPrice;
        $maxPrice = $reqMaxPrice !== null ? (int) $reqMaxPrice : $availableMaxPrice;

        $deals = $mappedDeals
            ->filter(function ($deal) use ($minPrice, $maxPrice) {
                $price = $deal['discountedPrice'] ?? 0;
                return $price >= $minPrice && $price <= $maxPrice;
            })
            ->values()
            ->all();

        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('display_order')
            ->get(['id', 'name', 'slug'])
            ->map(fn ($cat) => [
                'id'   => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
            ])
            ->all();

        return view('search', [
            'deals'           => $deals,
            'categories'      => $categories,
            'query'           => $query,
            'currentDistrict' => $district,
            'currentCategory' => $category,
            'sortBy'          => $sort,
            'isFeatured'      => $featured,
            'dealType'        => $type,
            'minPrice'          => $minPrice,
            'maxPrice'          => $maxPrice,
            'availableMinPrice' => $availableMinPrice,
            'availableMaxPrice' => $availableMaxPrice,
        ]);
    }

    public function forgotPassword()
    {
        return Inertia::render('ForgotPasswordPage');
    }

    public function checkout()
    {
        return Inertia::render('CheckoutPage');
    }

    public function notFound()
    {
        return Inertia::render('NotFound');
    }
}
