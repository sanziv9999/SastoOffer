<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Deal;
use App\Models\PrimaryCategory;
use App\Models\DealOfferType;

class PageController extends Controller
{
    public function home()
    {
        return view('home');
    }

    public function search(Request $request)
    {
        $query      = $request->query('q', '');
        $category   = $request->query('category', 'all');      // primary category slug or 'all'
        $subSlug    = $request->query('subcategory');          // optional business subcategory slug
        $sort       = $request->query('sort', 'relevance');
        $featured   = $request->query('featured') === 'true';
        $type       = $request->query('type', 'all');
        $minPrice   = (int) $request->query('minPrice', 0);

        // Default max based on current deals in DB (fallback 100000)
        $dbMaxPrice = (int) (DealOfferType::max('final_price') ?? 100000);
        $maxPrice   = (int) $request->query('maxPrice', $dbMaxPrice);

        $dealsQuery = Deal::query()
            ->with(['subCategory.primaryCategory', 'offerTypes', 'images', 'vendor'])
            ->when($query !== '', function ($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%');
            })
            // Filter by subcategory slug if provided
            ->when($subSlug, function ($q) use ($subSlug) {
                $q->whereHas('subCategory', fn ($qq) => $qq->where('slug', $subSlug));
            })
            // Otherwise filter by primary category slug (if not 'all')
            ->when(!$subSlug && $category !== 'all', function ($q) use ($category) {
                $q->whereHas('subCategory.primaryCategory', fn ($qq) => $qq->where('slug', $category));
            })
            ->when($featured, function ($q) {
                $q->where('is_featured', true);
            });

        // Sorting
        $dealsQuery->when($sort === 'priceAsc', function ($q) {
                $q->orderBy('base_price', 'asc');
            })
            ->when($sort === 'priceDesc', function ($q) {
                $q->orderBy('base_price', 'desc');
            })
            ->when($sort === 'discountDesc', function ($q) {
                $q->orderBy('discount_percent', 'desc');
            }, function ($q) use ($sort) {
                if ($sort === 'relevance') {
                    $q->latest();
                }
            });

        $rawDeals = $dealsQuery->take(60)->get();

        $deals = $rawDeals
            ->map(function ($deal) {
                $offer = $deal->offerTypes->first()?->pivot;

                return [
                    'id'                => $deal->id,
                    'title'             => $deal->title,
                    'categorySlug'      => optional($deal->subCategory->primaryCategory)->slug ?? 'uncategorized',
                    'categoryName'      => optional($deal->subCategory->primaryCategory)->name ?? 'Uncategorized',
                    'originalPrice'     => $offer ? (float) $offer->original_price : 0,
                    'discountedPrice'   => $offer ? (float) $offer->final_price : 0,
                    'discountPercentage'=> null,
                    'image'             => $deal->images->first()?->image_url
                                           ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&fit=crop',
                    'featured'          => (bool) ($deal->is_featured ?? false),
                    'type'              => $deal->offerTypes->first()->code ?? 'percentage',
                    'location'          => [
                        'city' => $deal->vendor?->defaultAddress?->municipality ?? 'City',
                    ],
                    'timeLeft'          => optional($deal->ends_at)?->diffForHumans() ?? 'soon',
                ];
            })
            ->filter(function ($deal) use ($minPrice, $maxPrice) {
                $price = $deal['discountedPrice'] ?? 0;
                return $price >= $minPrice && $price <= $maxPrice;
            })
            ->values()
            ->all();

        $categories = PrimaryCategory::where('is_active', true)
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
            'currentCategory' => $category,
            'sortBy'          => $sort,
            'isFeatured'      => $featured,
            'dealType'        => $type,
            'minPrice'        => $minPrice,
            'maxPrice'        => $maxPrice,
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
