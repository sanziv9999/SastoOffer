<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Deal;
use App\Models\Category;
use App\Models\DealOfferType;
use App\Models\Address;
use Illuminate\Support\Str;

class PageController extends Controller
{
    protected function applyActiveNonEndedOfferFilter($query)
    {
        return $query
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    public function home()
    {
        $featuredPivots = $this->applyActiveNonEndedOfferFilter(DealOfferType::query())
            ->whereHas('displayTypes', fn($q) => $q->where('name', 'featured'))
            ->with(['deal.category.parent', 'deal.images', 'deal.vendor' => fn($q) => $q->withAvg('reviews', 'rating')->withCount('reviews'), 'deal.vendor.defaultAddress', 'offerType', 'displayTypes'])
            ->take(8)
            ->get();

        $recentPivots = $this->applyActiveNonEndedOfferFilter(DealOfferType::query())
            ->with(['deal.category.parent', 'deal.images', 'deal.vendor' => fn($q) => $q->withAvg('reviews', 'rating')->withCount('reviews'), 'deal.vendor.defaultAddress', 'offerType', 'displayTypes'])
            ->latest()
            ->take(10)
            ->get();

        // Calculate sales for homepage deals
        $homeDealIds = $featuredPivots->pluck('deal_id')->merge($recentPivots->pluck('deal_id'))->unique()->filter();
        $soldByDealId = \App\Models\OrderItem::query()
            ->selectRaw('deal_id, SUM(quantity) as quantitySold')
            ->whereIn('deal_id', $homeDealIds)
            ->whereHas('order', fn ($q) => $q->whereNotIn('status', ['cancelled', 'refunded']))
            ->groupBy('deal_id')
            ->pluck('quantitySold', 'deal_id')
            ->all();

        $featuredDeals = $featuredPivots->map(function($offer) use ($soldByDealId) {
            $data = $offer->toCardData();
            $data['quantitySold'] = (int) ($soldByDealId[$offer->deal_id] ?? 0);
            return $data;
        });

        $recentOffers = $recentPivots->map(function($offer) use ($soldByDealId) {
            $data = $offer->toCardData();
            $data['quantitySold'] = (int) ($soldByDealId[$offer->deal_id] ?? 0);
            return $data;
        });

        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('display_order')
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

        // Tokenize query for better matching
        $wordTokens = [];
        $tagTokens = [];
        
        if (is_string($query) && trim($query) !== '') {
            $q = mb_strtolower(trim($query));
            
            // Split into tokens
            $rawTokens = preg_split('/[^\p{L}\p{N}]+/u', $q, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            
            // Filter stopwords and short tokens
            $stopwords = [
                'and', 'or', 'the', 'a', 'an', 'near', 'around', 'nearby',
                'for', 'with', 'without', 'of', 'to', 'in', 'at', 'from',
            ];
            
            $wordTokens = array_values(array_filter($rawTokens, function ($t) use ($stopwords) {
                $t = trim((string) $t);
                if ($t === '' || in_array($t, $stopwords, true)) return false;
                return mb_strlen($t) >= 3;
            }));
            
            // Create kebab-case tokens for highlight matching
            $kebabQuery = Str::slug($q);
            $tagWordTokens = array_values(array_unique(array_filter(array_map(fn ($t) => Str::slug($t), $wordTokens), fn ($t) => $t !== '')));
            $tagTokens = $tagWordTokens;
            if ($kebabQuery !== '') {
                $tagTokens[] = $kebabQuery;
            }
        }

        $suggestions = $this->applyActiveNonEndedOfferFilter(DealOfferType::query())
            ->whereHas('deal', function($q) use ($query, $wordTokens, $tagTokens) {
                $q->where(function($w) use ($query, $wordTokens, $tagTokens) {
                    // Match title, descriptions
                    $w->where('title', 'like', '%' . $query . '%')
                        ->orWhere('short_description', 'like', '%' . $query . '%')
                        ->orWhere('long_description', 'like', '%' . $query . '%');
                    
                    // Match individual tokens in title and descriptions
                    foreach ($wordTokens as $token) {
                        $token = trim((string) $token);
                        if ($token === '') continue;
                        $like = '%' . $token . '%';
                        $w->orWhere('title', 'like', $like)
                            ->orWhere('short_description', 'like', $like)
                            ->orWhere('long_description', 'like', $like);
                    }
                    
                    // Match against highlight tags using LIKE for partial matching
                    foreach ($tagTokens as $tagToken) {
                        $tagToken = trim((string) $tagToken);
                        if ($tagToken === '') continue;
                        $w->orWhere('highlights', 'like', '%' . $tagToken . '%');
                    }
                });
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
                    'url'   => $offer->deal->slug
                        ? route('deals.show.by-deal', ['deal' => $offer->deal->slug]) . '?offer=' . ($offer->offerType?->slug ?? $offer->id)
                        : '#',
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
        $viewMode = $request->query('view', 'grid');
        if (!in_array($viewMode, ['grid', 'list'], true)) {
            $viewMode = 'grid';
        }

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
        $nearbyEnabled = filter_var($request->query('nearby', false), FILTER_VALIDATE_BOOL);
        $nearbyLat = $request->query('lat');
        $nearbyLng = $request->query('lng');
        $nearbyRadiusKm = (float) $request->query('radiusKm', 5);
        if ($nearbyRadiusKm <= 0) {
            $nearbyRadiusKm = 5;
        }
        $nearbyRadiusKm = max(5, min(10, $nearbyRadiusKm));
        $hasNearbyCoords = is_numeric($nearbyLat) && is_numeric($nearbyLng);
        $nearbyLat = $hasNearbyCoords ? (float) $nearbyLat : null;
        $nearbyLng = $hasNearbyCoords ? (float) $nearbyLng : null;
        // Quick-and-dirty bounding box: 1 degree ~= 111km.
        $nearbyLatDelta = $hasNearbyCoords ? ($nearbyRadiusKm / 111) : null;
        $cosLat = $hasNearbyCoords ? cos(deg2rad($nearbyLat)) : null;
        $nearbyLngDelta = $hasNearbyCoords
            ? ($nearbyRadiusKm / max(111 * abs((float) $cosLat), 1e-6))
            : null;
        
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
        $minRating   = $request->query('minRating');

        // Tokenize keyword for matching highlights saved in `deals.highlights` (JSON array of kebab-case strings).
        // Example: "event ticket near Kathmandu" should match:
        // - highlight tokens: "event", "ticket", "kathmandu", ...
        // - district/location inference: "kathmandu"
        $wordTokens = []; // tokens for title/description matching
        $tagTokens = []; // potential highlight values (kebab-case)
        $locationTokens = []; // candidate district tokens for location inference

        if (is_string($query) && trim($query) !== '') {
            $q = mb_strtolower(trim($query));

            // Keep only letters/numbers; split on anything else.
            $rawTokens = preg_split('/[^\p{L}\p{N}]+/u', $q, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            // Lightweight stopwords to avoid generating noisy location/tag tokens.
            $stopwords = [
                'and', 'or', 'the', 'a', 'an', 'near', 'around', 'nearby',
                'for', 'with', 'without', 'of', 'to', 'in', 'at', 'from',
            ];

            $wordTokens = array_values(array_filter($rawTokens, function ($t) use ($stopwords) {
                $t = trim((string) $t);
                if ($t === '' || in_array($t, $stopwords, true)) return false;
                // Too short tokens create lots of false matches.
                return mb_strlen($t) >= 3;
            }));

            // Convert whole query into kebab-case (helps when highlights are multiword kebab strings).
            $kebabQuery = Str::slug($q);

            // Add each token and common n-gram kebab strings as highlight candidates.
            $tagWordTokens = array_values(array_unique(array_filter(array_map(fn ($t) => Str::slug($t), $wordTokens), fn ($t) => $t !== '')));
            $tagTokens = $tagWordTokens;
            if ($kebabQuery !== '') {
                $tagTokens[] = $kebabQuery;
            }

            $maxNgrams = 3; // "kathmandu metropolitan city" -> "kathmandu-metropolitan-city"
            $maxTagCandidates = 10;
            for ($n = 2; $n <= $maxNgrams; $n++) {
                for ($i = 0; $i + $n <= count($tagWordTokens); $i++) {
                    $candidate = implode('-', array_slice($tagWordTokens, $i, $n));
                    if ($candidate !== '') $tagTokens[] = $candidate;
                    if (count($tagTokens) >= $maxTagCandidates) break 2;
                }
            }

            $tagTokens = array_values(array_unique(array_filter($tagTokens, fn ($t) => $t !== '')));

            // For location inference (district/city), use only "long enough" tokens.
            $locationTokens = array_values(array_unique(array_filter($tagWordTokens, function ($t) {
                return mb_strlen($t) >= 4; // avoid too generic location tokens
            })));
        }

        // If user typed location words inside the search query (e.g. "near Kathmandu"),
        // infer matching districts automatically when no explicit location filter is chosen.
        if (count($locationSlugs) === 0 && count($locationTokens) > 0) {
            $lowerLocationTokens = array_map('mb_strtolower', $locationTokens);
            $placeholders = implode(',', array_fill(0, count($lowerLocationTokens), '?'));

            $inferredDistricts = Address::query()
                ->select('district')
                ->whereRaw("LOWER(district) IN ({$placeholders})", $lowerLocationTokens)
                ->distinct()
                ->pluck('district')
                ->all();

            if (!empty($inferredDistricts)) {
                $locationSlugs = array_map('trim', array_values(array_unique($inferredDistricts)));
            } else {
                // Fallback: partial match when user provides only "Kathmandu" but district is stored as
                // "Kathmandu Metropolitan City" (exact `IN` would fail).
                $likeTokens = array_slice($lowerLocationTokens, 0, 3); // keep it cheap
                $inferredDistricts = Address::query()
                    ->select('district')
                    ->where(function ($aq) use ($likeTokens) {
                        foreach ($likeTokens as $lt) {
                            if ($lt === '') continue;
                            $aq->orWhereRaw('LOWER(district) LIKE ?', ['%' . $lt . '%']);
                        }
                    })
                    ->distinct()
                    ->pluck('district')
                    ->all();

                if (!empty($inferredDistricts)) {
                    $locationSlugs = array_map('trim', array_values(array_unique($inferredDistricts)));
                }
            }
        }

        // If the query contained a location word, also add inferred districts as highlight candidates.
        if (count($locationSlugs) > 0) {
            foreach ($locationSlugs as $dl) {
                $dl = mb_strtolower(trim((string) $dl));
                $dl = preg_replace('/[^a-z0-9]+/u', '-', $dl);
                $dl = trim((string) $dl, '-');
                if ($dl !== '') $tagTokens[] = $dl;
            }
            $tagTokens = array_values(array_unique(array_filter($tagTokens, fn ($t) => $t !== '')));
        }

        $offerQuery = DealOfferType::query()
            ->with([
                'deal.category.parent',
                'deal.images',
                'deal.vendor' => fn($q) => $q->withAvg('reviews', 'rating')->withCount('reviews'),
                'deal.vendor.defaultAddress',
                'offerType',
                'displayTypes',
            ])
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->whereHas('deal', function ($q) use ($query, $wordTokens, $tagTokens, $subSlug, $categorySlugs, $locationSlugs, $nearbyEnabled, $hasNearbyCoords, $nearbyLat, $nearbyLng, $nearbyLatDelta, $nearbyLngDelta) {
                $q->when(is_string($query) && trim($query) !== '' && (count($wordTokens) > 0 || count($tagTokens) > 0), function ($qq) use ($query, $wordTokens, $tagTokens) {
                    $qq->where(function ($w) use ($query, $wordTokens, $tagTokens) {
                        // Keep full-phrase matching as a broad recall boost.
                        $w->where('title', 'like', '%' . $query . '%')
                            ->orWhere('short_description', 'like', '%' . $query . '%')
                            ->orWhere('long_description', 'like', '%' . $query . '%');

                        // Then match per-token to reduce "no results" for sentence searches.
                        foreach ($wordTokens as $token) {
                            $token = trim((string) $token);
                            if ($token === '') continue;

                            $w->orWhere(function ($tw) use ($token) {
                                $like = '%' . $token . '%';
                                $tw->where('title', 'like', $like)
                                    ->orWhere('short_description', 'like', $like)
                                    ->orWhere('long_description', 'like', $like);
                            });
                        }

                        // And match against highlight tags using LIKE for partial matching.
                        foreach ($tagTokens as $tagToken) {
                            $tagToken = trim((string) $tagToken);
                            if ($tagToken === '') continue;
                            $w->orWhere('highlights', 'like', '%' . $tagToken . '%');
                        }
                    });
                })
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
                    })
                    ->when($nearbyEnabled && $hasNearbyCoords, function ($qq) use ($nearbyLat, $nearbyLng, $nearbyLatDelta, $nearbyLngDelta) {
                        $qq->whereHas('vendor.defaultAddress', function ($addressQ) use ($nearbyLat, $nearbyLng, $nearbyLatDelta, $nearbyLngDelta) {
                            $addressQ
                                ->whereNotNull('latitude')
                                ->whereNotNull('longitude')
                                ->whereBetween('latitude', [$nearbyLat - $nearbyLatDelta, $nearbyLat + $nearbyLatDelta])
                                ->whereBetween('longitude', [$nearbyLng - $nearbyLngDelta, $nearbyLng + $nearbyLngDelta]);
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

        // Pull a reasonable working set (pivot-driven), then group in memory by parent deal.
        $rawOffers = $offerQuery->take(500)->get();

        // Sorting (each row = one offer card)
        $rawOffers = match ($sort) {
            'priceAsc'     => $rawOffers->sortBy(fn ($p) => (float) ($p->final_price ?? PHP_FLOAT_MAX))->values(),
            'priceDesc'    => $rawOffers->sortByDesc(fn ($p) => (float) ($p->final_price ?? 0))->values(),
            'discountDesc' => $rawOffers->sortByDesc(fn ($p) => (float) ($p->savings_percent ?? $p->discount_percent ?? 0))->values(),
            default        => $rawOffers->sortByDesc(fn ($p) => optional($p->deal)->created_at)->values(),
        };

        $mappedOffers = $rawOffers->take(120)->map(function (DealOfferType $pivot) {
            $deal = $pivot->deal;
            $discountPct = (float) ($pivot->savings_percent ?? $pivot->discount_percent ?? 0);
            $address = $deal?->vendor?->defaultAddress;
            $isOfferFeatured = $pivot->displayTypes
                ->contains(fn ($displayType) => mb_strtolower((string) $displayType->name) === 'featured');
            $locationLabel = collect([
                $address?->district,
                $address?->tole,
            ])->filter()->implode(', ');

            $vendorRating = $deal?->vendor?->reviews_avg_rating ?? null;
            $vendorReviewCount = $deal?->vendor?->reviews_count ?? null;

            $vendorRating = $deal?->vendor?->reviews_avg_rating ?? null;
            $vendorReviewCount = $deal?->vendor?->reviews_count ?? null;

            return [
                'dealId'            => $deal?->id,
                'basePrice'        => $deal?->base_price !== null ? (float) $deal->base_price : 0,
                'offerPivotId'      => $pivot->id,
                'offerSlug'         => $pivot->offerType?->slug,
                'dealSlug'          => $deal?->slug,
                'title'             => $deal?->title,
                'vendorName'        => $deal?->vendor?->business_name ?? 'Sasto Offer Vendor',
                'categorySlug'      => optional($deal?->category?->parent)->slug ?? ($deal?->category?->slug ?? 'uncategorized'),
                'categoryName'      => optional($deal?->category?->parent)->name ?? ($deal?->category?->name ?? 'Uncategorized'),
                'subcategoryName'   => optional($deal?->category?->parent)->name ? $deal?->category?->name : null,
                'originalPrice'     => $pivot->original_price !== null ? (float) $pivot->original_price : 0,
                'discountedPrice'   => $pivot->final_price !== null ? (float) $pivot->final_price : 0,
                'discountPercentage'=> $discountPct > 0 ? $discountPct : null,
                'image'             => $deal?->featuredImageUrl('https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&fit=crop'),
                'featured'          => (bool) $isOfferFeatured,
                'type'              => $pivot->offerType?->name ?? $pivot->offerType?->slug ?? 'offer',
                'offerTypeTitle'    => $pivot->offerType?->display_name
                    ?? $pivot->offerType?->name
                    ?? $pivot->offerType?->slug
                    ?? null,
                'locationLabel'     => $locationLabel ?: 'Location',
                'location'          => [
                    'district' => $address?->district,
                    'tole' => $address?->tole,
                    'city' => $address?->district ?? 'Location',
                ],
                'cityName'          => $locationLabel ?: 'Location',
                'vendorRating'      => $vendorRating !== null ? (float) $vendorRating : null,
                'vendorReviewCount' => $vendorReviewCount !== null ? (int) $vendorReviewCount : null,
                'timeLeft'          => optional($pivot->ends_at)?->diffForHumans() ?? 'soon',
                'url'               => $deal?->slug
                    ? route('deals.show.by-deal', ['deal' => $deal->slug]) . '?offer=' . $pivot->offerType->slug
                    : '#',
            ];
        });

        $availableMinPrice = 0;
        $availableMaxPrice = (int) ceil($mappedOffers->max('discountedPrice') ?? 100000);
        
        if ($availableMaxPrice <= $availableMinPrice) {
            $availableMaxPrice = $availableMinPrice + 100;
        }

        $minPrice = $reqMinPrice !== null ? (int) $reqMinPrice : $availableMinPrice;
        $maxPrice = $reqMaxPrice !== null ? (int) $reqMaxPrice : $availableMaxPrice;

        $groupedByDeal = [];
        $dealOrder = [];
        
        $mappedOffers->filter(function ($offer) use ($reqMinPrice, $reqMaxPrice, $availableMinPrice, $availableMaxPrice, $minRating) {
            $minPrice = $reqMinPrice !== null ? (int) $reqMinPrice : $availableMinPrice;
            $maxPrice = $reqMaxPrice !== null ? (int) $reqMaxPrice : $availableMaxPrice;
            $price = $offer['discountedPrice'] ?? 0;
            $rating = $offer['vendorRating'] ?? 0;

            $passPrice = ($price >= $minPrice && $price <= $maxPrice);
            $passRating = ($minRating === null || $minRating === '' || $rating >= (float)$minRating);

            return $passPrice && $passRating;
        })->each(function ($offer) use (&$groupedByDeal, &$dealOrder) {
            $dealId = $offer['dealId'] ?? null;
            if (!$dealId) {
                return;
            }
            if (!isset($groupedByDeal[$dealId])) {
                $dealOrder[] = $dealId;
                $groupedByDeal[$dealId] = [
                    'id' => $dealId,
                    'dealSlug' => $offer['dealSlug'] ?? null,
                    'title' => $offer['title'] ?? null,
                    'vendorName' => $offer['vendorName'] ?? 'Sasto Offer Vendor',
                    'categorySlug' => $offer['categorySlug'] ?? null,
                    'categoryName' => $offer['categoryName'] ?? null,
                    'subcategoryName' => $offer['subcategoryName'] ?? null,
                    'image' => $offer['image'] ?? null,
                    'featured' => false,
                    'displayOffer' => $offer,
                    'offers' => [],
                ];
            }

            if (!empty($offer['featured'])) {
                $groupedByDeal[$dealId]['featured'] = true;
            }
            $groupedByDeal[$dealId]['offers'][] = $offer;
        });

        // Bulk sales aggregation for "Sold" stats on search cards.
        $dealIdsForSales = array_values(array_filter($dealOrder));
        $soldByDealId = [];
        if (!empty($dealIdsForSales)) {
            $soldByDealId = \App\Models\OrderItem::query()
                ->selectRaw('deal_id, SUM(quantity) as quantitySold')
                ->whereIn('deal_id', $dealIdsForSales)
                ->whereHas('order', fn ($q) => $q->whereNotIn('status', ['cancelled', 'refunded']))
                ->groupBy('deal_id')
                ->pluck('quantitySold', 'deal_id')
                ->all();
        }

        $deals = collect($dealOrder)->map(function ($dealId) use ($groupedByDeal, $soldByDealId) {
            $group = $groupedByDeal[$dealId];
            $group['offers'] = collect($group['offers'])->take(8)->values()->all();
            $display = $group['displayOffer'];

            // Map to the shape expected by <x-deal-card> (parent deal card),
            // while still using a single offer pivot for wishlist/cart actions.
            $group['title'] = $display['title'] ?? $group['title'];
            $group['vendorName'] = $display['vendorName'] ?? $group['vendorName'] ?? 'Sasto Offer Vendor';
            $group['offerPivotId'] = $display['offerPivotId'] ?? null;
            $group['offerSlug'] = $display['offerSlug'] ?? null;
            // Search must show parent deal with base price only (no offer discounts/time).
            $basePrice = $display['basePrice'] ?? 0;
            $group['discountedPrice'] = $basePrice;
            $group['originalPrice'] = 0;
            $group['discountPercentage'] = 0;
            $group['offerTypeTitle'] = $display['offerTypeTitle'] ?? null;
            $group['timeLeft'] = $display['timeLeft'] ?? null;
            $group['categoryName'] = $display['categoryName'] ?? 'Uncategorized';
            $group['subcategoryName'] = $display['subcategoryName'] ?? null;
            $group['status'] = 'active';
            $dealSlug = $display['dealSlug'] ?? $group['dealSlug'] ?? null;
            $group['url'] = $dealSlug ? route('deals.show.by-deal', ['deal' => $dealSlug]) : '#';
            // Important: reviews on the deal page are tied to a specific offer pivot (?offer=...).
            // On search listing, we always prefer the first display offer pivot so the
            // "reviews" section is available on the details page.
            if (! empty($group['offerSlug'])) {
                $group['url'] .= '?offer=' . $group['offerSlug'];
            }
            $group['offersCount'] = count($group['offers']);

            // Ensure parent card shows location + rating + sales stats.
            $group['locationLabel'] = $display['locationLabel'] ?? null;
            $group['location'] = $display['location'] ?? null;
            $group['cityName'] = $display['cityName'] ?? null;
            $group['vendorRating'] = $display['vendorRating'] ?? null;
            $group['vendorReviewCount'] = $display['vendorReviewCount'] ?? null;
            $group['quantitySold'] = (int) ($soldByDealId[$dealId] ?? 0);

            return $group;
        })->values()->all();

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
            'minRating'         => $minRating,
            'availableMinPrice' => $availableMinPrice,
            'availableMaxPrice' => $availableMaxPrice,
            'sortByOptions'     => $sortByOptions,
            'viewMode'          => $viewMode,
            'nearbyEnabled'     => $nearbyEnabled && $hasNearbyCoords,
            'nearbyLat'         => $nearbyLat,
            'nearbyLng'         => $nearbyLng,
            'nearbyRadiusKm'    => $nearbyRadiusKm,
        ];

        if ($request->has('partial')) {
            return view('partials.deals-grid', $viewData);
        }

        return view('search', $viewData);
    }

    public function nearBy(Request $request){
       $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'radiusKm' => ['nullable', 'numeric', 'min:5', 'max:10'],
        ]);

        $request->merge([
            'nearby' => 'true',
            'lat' => $data['lat'],
            'lng' => $data['lng'],
            'radiusKm' => $data['radiusKm'] ?? 5,
        ]);

        return $this->search($request);
    }

    public function forgotPassword()
    {
        return Inertia::render('ForgotPasswordPage');
    }

    public function terms()
    {
        return view('terms');
    }

    public function privacy()
    {
        return view('privacy');
    }

    public function contact()
    {
        return view('contact');
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
