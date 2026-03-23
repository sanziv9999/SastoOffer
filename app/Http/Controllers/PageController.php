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

        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->take(6)
            ->get();

        $topRatedVendors = \App\Models\VendorProfile::query()
            ->where('verified_status', 'verified')
            ->withAvg('reviews', 'rating')
            ->with(['images' => fn($q) => $q->where('attribute_name', 'logo')])
            ->orderByDesc('reviews_avg_rating')
            ->take(12)
            ->get();

        return view('home', compact('featuredDeals', 'recentOffers', 'categories', 'topRatedVendors'));
    }

    public function suggestions(Request $request)
    {
        $query = $request->query('q', '');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = DealOfferType::where('status', 'active')
            ->whereHas('deal', function($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%');
            })
            ->with(['deal.images', 'offerType'])
            ->take(6)
            ->get()
            ->map(function($offer) {
                return [
                    'id'    => $offer->id,
                    'title' => $offer->deal->title,
                    'price' => (float) $offer->final_price,
                    'image' => $offer->deal->featuredImageUrl('https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=100&fit=crop'),
                    'url'   => route('deals.show.by-deal', ['deal' => $offer->deal->slug ?? $offer->deal->id]) . '?offer=' . $offer->id,
                ];
            });

        return response()->json($suggestions);
    }

    public function search(Request $request)
    {
        $sortByOptions = [
            'relevance' => 'Relevance',
            'newest' => 'Newest',
            'priceAsc' => 'Price: Low to High',
            'priceDesc' => 'Price: High to Low',
            'discountDesc' => 'Biggest Discount',
            'endingSoon' => 'Ending Soon',
        ];

        $query      = $request->query('q', '');
        $categoryParam = $request->query('category', 'all');
        $subSlug    = $request->query('subcategory');
        
        // If a subcategory is provided via navbar, merge it into category slugs so the filter checks it
        if ($subSlug && $categoryParam !== 'all') {
            if ($categoryParam === '') {
                $categoryParam = $subSlug;
            } else {
                $existing = array_filter(array_map('trim', explode(',', $categoryParam)));
                if (!in_array($subSlug, $existing)) {
                    $existing[] = $subSlug;
                    $categoryParam = implode(',', $existing);
                }
            }
        }

        $categorySlugs = ($categoryParam !== 'all' && $categoryParam !== '')
            ? array_filter(array_map('trim', explode(',', $categoryParam)))
            : [];
        // Support comma-separated districts for multi-select
        $locationParam  = trim((string) $request->query('location', $request->query('city', $request->query('district', ''))));
        
        // Treat "All Districts" or "All Cities" as no location selected
        if (in_array($locationParam, ['All Districts', 'All Cities'], true)) {
            $locationParam = '';
        }

        $locationSlugs  = ($locationParam !== '')
            ? array_filter(array_map('trim', explode(',', $locationParam)), function($slug) {
                return !in_array($slug, ['All Districts', 'All Cities'], true);
            })
            : [];
        $sort       = $request->query('sort', 'relevance');
        $featured   = $request->query('featured') === 'true';
        // Support comma-separated deal types for multi-select
        $typeParam  = $request->query('type', 'all');
        $typeSlugs  = ($typeParam !== 'all' && $typeParam !== '')
            ? array_filter(array_map('trim', explode(',', $typeParam)))
            : [];
        $reqMinPrice = $request->query('minPrice');
        $reqMaxPrice = $request->query('maxPrice');

        $offerQuery = DealOfferType::query()
            ->with([
                'deal.category.parent',
                'deal.images',
                'deal.vendor.defaultAddress',
                'offerType',
                'displayTypes',
            ])
            ->where('status', 'active')
            ->whereHas('deal', function ($q) use ($query, $subSlug, $categorySlugs, $locationSlugs) {
                $q->when($query !== '', fn ($qq) => $qq->where('title', 'like', '%' . $query . '%'))
                    ->when($subSlug, fn ($qq) => $qq->whereHas('category', fn ($c) => $c->where('slug', $subSlug)))
                    ->when(!$subSlug && count($categorySlugs) > 0, function ($qq) use ($categorySlugs) {
                        $qq->where(function ($w) use ($categorySlugs) {
                            // Match if the deal's own category slug is in selected list (subcat)
                            $w->whereHas('category', fn ($c) => $c->whereIn('slug', $categorySlugs))
                              // OR the deal's category's parent slug is in the list (parent cat)
                              ->orWhereHas('category.parent', fn ($c) => $c->whereIn('slug', $categorySlugs));
                        });
                    })
                    ->when(count($locationSlugs) > 0, function ($qq) use ($locationSlugs) {
                        $needles = array_map('mb_strtolower', $locationSlugs);
                        $qq->whereHas('vendor.defaultAddress', function ($addressQ) use ($needles) {
                            $addressQ->whereRaw('LOWER(district) IN (' . implode(',', array_fill(0, count($needles), '?')) . ')', $needles);
                        });
                    });
            });

        if ($featured) {
            $offerQuery->whereHas('displayTypes', fn ($q) => $q->where('name', 'featured'));
        }

        // Deal type filter (multi-select via comma-separated slugs)
        if (count($typeSlugs) > 0) {
            $offerQuery->whereHas('offerType', fn ($q) => $q->whereIn('slug', $typeSlugs)->orWhereIn('name', $typeSlugs));
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

        $mappedDeals = $rawOffers->take(60)->map(function (DealOfferType $pivot) {
            $deal = $pivot->deal;
            $discountPct = (float) ($pivot->savings_percent ?? $pivot->discount_percent ?? 0);
            $address = $deal?->vendor?->defaultAddress;
            $isOfferFeatured = $pivot->displayTypes
                ->contains(fn ($displayType) => mb_strtolower((string) $displayType->name) === 'featured');
            $locationLabel = collect([
                $address?->district,
                $address?->tole,
            ])->filter()->implode(', ');

            return [
                'id'                => $deal?->id,
                'offerPivotId'      => $pivot->id,
                'dealSlug'          => $deal?->slug,
                'title'             => $deal?->title,
                'categorySlug'      => optional($deal?->category?->parent)->slug ?? ($deal?->category?->slug ?? 'uncategorized'),
                'categoryName'      => optional($deal?->category?->parent)->name ?? ($deal?->category?->name ?? 'Uncategorized'),
                'originalPrice'     => $pivot->original_price !== null ? (float) $pivot->original_price : 0,
                'discountedPrice'   => $pivot->final_price !== null ? (float) $pivot->final_price : 0,
                'discountPercentage'=> $discountPct > 0 ? $discountPct : null,
                'image'             => $deal?->featuredImageUrl('https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&fit=crop'),
                'featured'          => (bool) $isOfferFeatured,
                'type'              => $pivot->offerType?->name ?? $pivot->offerType?->slug ?? 'offer',
                'offerTypeTitle'    => $pivot->offerType?->display_name ?? null,
                'locationLabel'     => $locationLabel ?: 'Location',
                'location'          => [
                    'district' => $address?->district,
                    'tole' => $address?->tole,
                    'city' => $address?->district ?? 'Location',
                ],
                'cityName'          => $locationLabel ?: 'Location',
                'timeLeft'          => optional($pivot->ends_at)?->diffForHumans() ?? 'soon',
                'url'               => route('deals.show.by-deal', ['deal' => $deal?->slug ?? $deal?->id]) . '?offer=' . $pivot->id,
            ];
        });

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
            ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('display_order')])
            ->orderBy('display_order')
            ->get()
            ->map(fn ($cat) => [
                'id'       => $cat->id,
                'name'     => $cat->name,
                'slug'     => $cat->slug,
                'children' => $cat->children->map(fn ($sub) => [
                    'id'   => $sub->id,
                    'name' => $sub->name,
                    'slug' => $sub->slug,
                ])->all(),
            ])
            ->all();

        // Use full list of districts for the location filter (as in the navbar)
        $locations = [
            "Achham", "Arghakhanchi", "Baglung", "Baitadi", "Bajhang", "Bajura", "Banke", "Bara", 
            "Bardiya", "Bhaktapur", "Bhojpur", "Chitwan", "Dadeldhura", "Dailekh", "Dang", "Darchula", 
            "Dhading", "Dhankuta", "Dhanusha", "Dolakha", "Dolpa", "Doti", "Eastern Rukum", "Gorkha", 
            "Gulmi", "Humla", "Ilam", "Jajarkot", "Jhapa", "Jumla", "Kailali", "Kalikot", "Kanchanpur", 
            "Kapilvastu", "Kaski", "Kathmandu", "Kavrepalanchok", "Khotang", "Lalitpur", "Lamjung", 
            "Mahottari", "Makwanpur", "Manang", "Morang", "Mugu", "Mustang", "Myagdi", "Nawalpur", 
            "Nuwakot", "Okhaldhunga", "Palpa", "Panchthar", "Parasi", "Parbat", "Parsa", "Pyuthan", 
            "Ramechhap", "Rasuwa", "Rautahat", "Rolpa", "Rupandehi", "Salyan", "Sankhuwasabha", 
            "Saptari", "Sarlahi", "Sindhuli", "Sindhupalchok", "Siraha", "Solukhumbu", "Sunsari", 
            "Surkhet", "Syangja", "Tanahun", "Taplejung", "Tehrathum", "Udayapur", "Western Rukum"
        ];

        $viewData = [
            'deals'           => $deals,
            'categories'      => $categories,
            'locations'       => $locations,
            'query'           => $query,
            'currentLocation' => $locationParam,
            'currentCategory' => $categoryParam,
            'sortBy'          => $sort,
            'isFeatured'      => $featured,
            'dealType'        => $typeParam,
            'minPrice'          => $minPrice,
            'maxPrice'          => $maxPrice,
            'availableMinPrice' => $availableMinPrice,
            'availableMaxPrice' => $availableMaxPrice,
            'sortByOptions'     => $sortByOptions,
        ];

        if ($request->has('partial')) {
            return view('partials.deals-grid', $viewData);
        }

        return view('search', $viewData);
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
