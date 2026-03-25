<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDealRequest;
use App\Models\Category;
use App\Models\Deal;
use App\Models\DealOfferType;
use App\Models\DealOfferType as DealOfferTypePivot;
use App\Models\OfferType;
use App\Models\VendorProfile;
use App\Services\DealOfferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DealController extends Controller
{
    protected function generateUniqueDealSlug(string $title, ?int $ignoreDealId = null): string
    {
        $base = Str::slug($title);
        if ($base === '') {
            $base = 'deal';
        }

        $slug = $base;
        $suffix = 1;
        while (
            Deal::query()
                ->when($ignoreDealId, fn ($q) => $q->where('id', '!=', $ignoreDealId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $suffix++;
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }

    public function index(Request $request)
    {
        $deals = Deal::query()
            ->with(['vendor', 'category'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('vendor_id'), fn ($q) => $q->where('vendor_id', $request->vendor_id))
            ->latest()
            ->paginate(15);

        return view('deals.index', compact('deals'));
    }

    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $vendor = $user->vendorProfile;

        if (! $vendor) {
            return \Inertia\Inertia::render('VendorDashboard', [
                'vendor' => null,
                'stats' => null,
                'deals' => [],
                'recentOrders' => [],
                'monthlySales' => [],
            ]);
        }

        $orders = \App\Models\Order::where('vendor_id', $vendor->id)
            ->with(['user', 'items'])
            ->get();

        $totalRevenue = (float) $orders->sum('grand_total');
        $totalSales = $orders->sum(fn ($o) => $o->items->sum('quantity'));
        $totalOrders = $orders->count();
        $uniqueCustomers = $orders->pluck('user_id')->unique()->count();

        $vendorReviewCount = $vendor->reviews()->count();
        $avgVendorRating = round($vendor->reviews()->avg('rating') ?? 0, 1);

        $dealOfferIds = \App\Models\DealOfferType::whereHas('deal', fn ($q) => $q->where('vendor_id', $vendor->id))->pluck('id');
        $dealReviewCount = \App\Models\Review::where('reviewable_type', \App\Models\DealOfferType::class)
            ->whereIn('reviewable_id', $dealOfferIds)->count();
        $totalReviews = $vendorReviewCount + $dealReviewCount;

        $deals = Deal::where('vendor_id', $vendor->id)
            ->with(['category', 'offerTypes', 'images'])
            ->latest()
            ->get()
            ->map(function ($deal) use ($orders) {
                $offer = $deal->offerTypes->first()?->pivot;
                $base = (float) ($deal->base_price ?? 0);

                $quantitySold = $orders->flatMap(fn ($o) => $o->items)
                    ->where('deal_id', $deal->id)
                    ->sum('quantity');

                return [
                    'id' => $deal->id,
                    'title' => $deal->title,
                    'status' => $deal->status,
                    'discountedPrice' => $offer ? (float) $offer->final_price : $base,
                    'originalPrice' => $offer ? (float) $offer->original_price : $base,
                    'quantitySold' => $quantitySold,
                    'maxQuantity' => $deal->total_inventory,
                    'endDate' => $offer?->ends_at?->toIso8601String(),
                    'image' => $deal->featuredImageUrl('https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=200&h=200&fit=crop'),
                ];
            });

        $stats = [
            'totalRevenue' => $totalRevenue,
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders,
            'uniqueCustomers' => $uniqueCustomers,
            'activeDeals' => $deals->where('status', 'active')->count(),
            'totalDeals' => $deals->count(),
            'totalReviews' => $totalReviews,
            'avgRating' => $avgVendorRating,
        ];

        $recentOrders = $orders->sortByDesc('created_at')->take(5)->values()->map(fn (\App\Models\Order $o) => [
            'id' => $o->order_number,
            'customer' => $o->user?->name ?? 'Customer',
            'total' => (float) $o->grand_total,
            'status' => $o->status,
            'itemCount' => $o->items->sum('quantity'),
            'date' => $o->created_at->toIso8601String(),
        ]);

        $monthlySales = $orders->groupBy(fn ($o) => $o->created_at->format('Y-m'))
            ->sortKeys()
            ->map(fn ($group, $key) => [
                'month' => \Carbon\Carbon::parse($key.'-01')->format('M'),
                'amount' => round((float) $group->sum('grand_total'), 2),
                'orders' => $group->count(),
            ])
            ->values()
            ->slice(-6)
            ->values();

        return \Inertia\Inertia::render('VendorDashboard', [
            'vendor' => $vendor,
            'stats' => $stats,
            'deals' => $deals,
            'recentOrders' => $recentOrders,
            'monthlySales' => $monthlySales,
        ]);
    }

    public function manageDeals(Request $request)
    {
        $user = auth()->user();
        $vendor = $user->vendorProfile;

        if (! $vendor) {
            return redirect()->route('vendor.dashboard')->with('error', 'Vendor profile not found.');
        }

        $dealModels = Deal::where('vendor_id', $vendor->id)
            ->with(['category', 'offerTypes', 'images'])
            ->latest()
            ->get();

        $salesByDeal = \App\Models\OrderItem::query()
            ->selectRaw('deal_id, SUM(quantity) as sold_qty')
            ->whereIn('deal_id', $dealModels->pluck('id'))
            ->whereHas('order', fn ($q) => $q
                ->where('vendor_id', $vendor->id)
                ->whereNotIn('status', ['cancelled', 'refunded']))
            ->groupBy('deal_id')
            ->pluck('sold_qty', 'deal_id');

        $deals = $dealModels->map(function ($deal) use ($salesByDeal) {
            $base = (float) ($deal->base_price ?? 0);

            return [
                'id' => $deal->id,
                'title' => $deal->title,
                'status' => $deal->status,
                // Manage Deals list should show only deal table data (base price),
                // offers are managed separately in the Offers screen.
                'price' => $base,
                // Parent-deal sales = sum of all sold quantities across its offer purchases.
                'quantitySold' => (int) ($salesByDeal[$deal->id] ?? 0),
                'maxQuantity' => $deal->total_inventory,
                'endDate' => null,
                'image' => $deal->featuredImageUrl('https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=200&h=200&fit=crop'),
            ];
        });

        return \Inertia\Inertia::render('vendor/ManageDeals', [
            'deals' => $deals,
        ]);
    }

    public function create()
    {
        $vendors = VendorProfile::orderBy('business_name')->get();
        $categories = Category::where('is_active', true)->orderBy('display_order')->orderBy('name')->get();

        return \Inertia\Inertia::render('vendor/CreateDeal', [
            'vendors' => $vendors,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! $user->hasRole('vendor')) {
            return back()->withErrors(['error' => 'Unauthorized. Only vendors can create deals.']);
        }

        $vendor = $user->vendorProfile;
        if (! $vendor) {
            // Fallback for demo/early stage
            $vendor = VendorProfile::first();
        }

        $data = $request->all();
        \Illuminate\Support\Facades\Log::info('Deal Creation Request:', [
            'data' => array_keys($data),
            'files' => array_keys($request->allFiles()),
            'has_images' => $request->hasFile('images'),
        ]);

        // Map React fields to DB fields (core deal only; offers added separately)
        $dealData = [
            'vendor_id' => $vendor->id,
            'category_id' => (int) ($data['categoryId'] ?? 0),
            'title' => $data['title'] ?? 'Untitled Deal',
            'slug' => $this->generateUniqueDealSlug((string) ($data['title'] ?? 'untitled')),
            'base_price' => isset($data['basePrice']) && $data['basePrice'] !== '' ? (float) $data['basePrice'] : null,
            'short_description' => $data['shortDesc'] ?? null,
            'long_description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active',
            'total_inventory' => ! empty($data['maxQuantity']) ? (int) $data['maxQuantity'] : null,
            'highlights' => is_array($data['tags'] ?? []) ? $data['tags'] ?? [] : [$data['tags'] ?? ''],
        ];

        $deal = Deal::create($dealData);
        $this->syncDealImagesFromRequest($request, $deal, false);

        return redirect()
            ->route('vendor.deals.offers', $deal)
            ->with('success', 'Deal created. Now add one or more offers.');
    }

    /**
     * Public deal view (Inertia)
     */
    public function showDeal(DealOfferTypePivot $dealOfferType)
    {
        try {
            $dealOfferType->load([
                'deal.vendor',
                'deal.category',
                'deal.images',
                'deal.offerTypes',
                'offerType',
            ]);

            $deal = $dealOfferType->deal;
            if (! $deal) {
                return view('deals.show', ['deal' => null]);
            }

            $selectedPivot = $dealOfferType;
            $base = $deal->base_price !== null ? (float) $deal->base_price : null;

            // Fetch Similar Deals (same category)
            $similarDeals = DealOfferType::where('status', 'active')
                ->where('id', '!=', $selectedPivot->id)
                ->whereHas('deal', function ($q) use ($deal) {
                    $q->where('category_id', $deal->category_id);
                })
                ->with(['deal.category.parent', 'deal.images', 'deal.vendor.defaultAddress', 'offerType', 'displayTypes'])
                ->take(8)
                ->get();

            // If not enough similar deals, pad with featured deals
            if ($similarDeals->count() < 4) {
                $featuredPadding = DealOfferType::where('status', 'active')
                    ->where('id', '!=', $selectedPivot->id)
                    ->whereNotIn('id', $similarDeals->pluck('id'))
                    ->whereHas('displayTypes', fn ($q) => $q->where('name', 'featured'))
                    ->with(['deal.category.parent', 'deal.images', 'deal.vendor.defaultAddress', 'offerType', 'displayTypes'])
                    ->take(8 - $similarDeals->count())
                    ->get();
                $similarDeals = $similarDeals->concat($featuredPadding);
            }

            $mappedSimilarDeals = $similarDeals->map(fn ($offer) => $offer->toCardData());

            return view('deals.show', [
                'deal' => [
                    'id' => $deal->id,
                    'offerPivotId' => $selectedPivot->id,
                    'title' => $deal->title,
                    'short_description' => $deal->short_description,
                    'long_description' => $deal->long_description,
                    'status' => $deal->status,
                    'highlights' => is_array($deal->highlights) ? $deal->highlights : [],
                    'ends_at' => $selectedPivot->ends_at?->toIso8601String(),
                    'is_featured' => (bool) $deal->is_featured,
                    'discountedPrice' => $selectedPivot->final_price !== null ? (float) $selectedPivot->final_price : $base,
                    'originalPrice' => $selectedPivot->original_price !== null ? (float) $selectedPivot->original_price : $base,
                    'discountPercent' => $selectedPivot->discount_percent !== null ? (float) $selectedPivot->discount_percent : null,
                    'offerTypeTitle' => $selectedPivot->offerType?->display_name,
                    'offers' => $deal->activeOfferTypes->map(function ($ot) {
                        $pivot = $ot->pivot;
                        return [
                            'id' => $ot->id,
                            'name' => $ot->name,
                            'display_name' => $ot->display_name,
                            // Expose pivot id at top-level so the blade can build links reliably.
                            'offerPivotId' => $pivot?->id,
                            'pivot' => [
                                'pivot_id' => $pivot?->id,
                                'original_price' => $pivot?->original_price,
                                'final_price' => $pivot?->final_price,
                                'currency_code' => $pivot?->currency_code,
                                'status' => $pivot?->status,
                                'params' => $pivot?->params,
                                'starts_at' => $pivot?->starts_at?->toIso8601String(),
                                'ends_at' => $pivot?->ends_at?->toIso8601String(),
                            ],
                        ];
                    })->values()->toArray(),
                    'images' => $deal->images->map(fn ($img) => [
                        'id' => $img->id,
                        'image_url' => $img->image_url,
                        'attribute_name' => $img->attribute_name,
                        'sort_order' => $img->sort_order,
                    ])->toArray(),
                    'vendor' => $deal->vendor ? [
                        'id' => $deal->vendor->id,
                        'slug' => $deal->vendor->slug,
                        'business_name' => $deal->vendor->business_name,
                        'rating' => round($deal->vendor->reviews()->avg('rating') ?? 0, 1),
                        'reviewCount' => $deal->vendor->reviews()->count(),
                    ] : null,
                    'category' => $deal->category ? [
                        'id' => $deal->category->id,
                        'name' => $deal->category->name,
                    ] : null,
                ],
                'reviews' => $selectedPivot->reviews()
                    ->where('is_hidden', false)
                    ->with('user')
                    ->latest()
                    ->get()
                    ->map(fn (\App\Models\Review $r) => [
                        'id' => $r->id,
                        'userName' => $r->user?->name ?? 'Anonymous',
                        'rating' => $r->rating,
                        'comment' => $r->comment,
                        'vendorReply' => $r->vendor_reply,
                        'vendorRepliedAt' => $r->vendor_replied_at?->toIso8601String(),
                        'createdAt' => $r->created_at->toIso8601String(),
                        'isOwn' => auth()->check() && (int) $r->user_id === (int) auth()->id(),
                    ]),
                'userReview' => auth()->check()
                    ? $selectedPivot->reviews()->where('user_id', auth()->id())->first()?->only(['id', 'rating', 'comment'])
                    : null,
                'similarDeals' => $mappedSimilarDeals,
            ]);
        } catch (\Exception $e) {
            // Log the exception for debugging
            \Illuminate\Support\Facades\Log::error('Error in showDeal: '.$e->getMessage());

            // Redirect to a safe page or show an error
            return redirect()->route('home')->with('error', 'Could not load deal details.');
        }
    }

    /**
     * Public deal view by deal id (legacy): redirects to first active offer pivot.
     */
    public function showDealByDealId($deal, Request $request)
    {
        $dealModel = Deal::query()
            ->where('slug', $deal)
            ->orWhere('id', is_numeric($deal) ? (int) $deal : 0)
            ->first();

        if (! $dealModel) {
            return view('deals.show', ['deal' => null]);
        }

        if ((string) $deal !== (string) $dealModel->slug) {
            $canonicalUrl = route('deals.show.by-deal', ['deal' => $dealModel->slug]);
            if ($request->filled('offer')) {
                $canonicalUrl .= '?offer='.$request->query('offer');
            }

            return redirect()->to($canonicalUrl);
        }

        $requestedOfferPivotId = $request->query('offer');
        $pivot = null;
        if ($request->filled('offer') && is_numeric($requestedOfferPivotId)) {
            $requested = (int) $requestedOfferPivotId;

            // 1) Most common: ?offer points to deal_offer_type.id (pivot id)
            $pivot = DealOfferTypePivot::where('deal_id', $dealModel->id)
                ->where('status', 'active')
                ->where('id', $requested)
                ->first();

            // 2) Fallback: some UI paths might pass offer_type_id instead of pivot id
            if (! $pivot) {
                $pivot = DealOfferTypePivot::where('deal_id', $dealModel->id)
                    ->where('status', 'active')
                    ->where('offer_type_id', $requested)
                    ->first();
            }
        }

        // If an offer is explicitly selected, render the selected offer context.
        if ($pivot) {
            return $this->showDeal($pivot);
        }

        // No offer selected: render primary deal (base price) + list of offers (nothing selected).
        $dealModel->load([
            'vendor',
            'category.parent',
            'images',
            'activeOfferTypes',
        ]);

        $activeOffers = $dealModel->activeOfferTypes->map(function ($ot) {
            $pivotRow = $ot->pivot;
            return [
                'id' => $ot->id,
                'name' => $ot->name,
                'display_name' => $ot->display_name,
                'offerPivotId' => $pivotRow?->id,
                'pivot' => [
                    'pivot_id' => $pivotRow?->id,
                    'original_price' => $pivotRow?->original_price,
                    'final_price' => $pivotRow?->final_price,
                    'currency_code' => $pivotRow?->currency_code,
                    'status' => $pivotRow?->status,
                    'params' => $pivotRow?->params,
                    'starts_at' => $pivotRow?->starts_at?->toIso8601String(),
                    'ends_at' => $pivotRow?->ends_at?->toIso8601String(),
                ],
            ];
        })->values()->toArray();

        $vendor = $dealModel->vendor ? [
            'id' => $dealModel->vendor->id,
            'slug' => $dealModel->vendor->slug,
            'business_name' => $dealModel->vendor->business_name,
            'rating' => round((float) ($dealModel->vendor->reviews()->avg('rating') ?? 0), 1),
            'reviewCount' => $dealModel->vendor->reviews()->count(),
        ] : null;

        return view('deals.show', [
            'deal' => [
                'id' => $dealModel->id,
                'offerPivotId' => null,
                'title' => $dealModel->title,
                'short_description' => $dealModel->short_description,
                'long_description' => $dealModel->long_description,
                'status' => $dealModel->status,
                'highlights' => is_array($dealModel->highlights) ? $dealModel->highlights : [],
                'ends_at' => null,
                'is_featured' => (bool) $dealModel->is_featured,
                'discountedPrice' => $dealModel->base_price !== null ? (float) $dealModel->base_price : 0,
                'originalPrice' => $dealModel->base_price !== null ? (float) $dealModel->base_price : 0,
                'discountPercent' => null,
                'offerTypeTitle' => null,
                'offers' => $activeOffers,
                'images' => $dealModel->images->map(fn ($img) => [
                    'id' => $img->id,
                    'image_url' => $img->image_url,
                    'attribute_name' => $img->attribute_name,
                    'sort_order' => $img->sort_order,
                ])->toArray(),
                'vendor' => $vendor,
                'category' => $dealModel->category ? [
                    'id' => $dealModel->category->id,
                    'name' => $dealModel->category->name,
                ] : null,
            ],
            'reviews' => [],
            'userReview' => null,
            'similarDeals' => [],
        ]);
    }

    /**
     * Vendor: edit deal form (Inertia)
     */
    public function editDeal(Deal $deal)
    {
        $user = auth()->user();
        $vendor = $user->vendorProfile;

        // Security: ensure the deal belongs to this vendor
        if ($vendor && $deal->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized');
        }

        $categories = Category::where('is_active', true)->orderBy('display_order')->orderBy('name')->get();
        $deal->load(['offerTypes', 'images']);

        $offer = $deal->offerTypes->first()?->pivot;

        return \Inertia\Inertia::render('vendor/EditDeal', [
            'deal' => [
                'id' => $deal->id,
                'title' => $deal->title,
                'shortDesc' => $deal->short_description,
                'description' => $deal->long_description,
                'categoryId' => $deal->category_id,
                'basePrice' => $deal->base_price ? (string) $deal->base_price : '',
                'tags' => $deal->highlights ?? [],
                'maxQuantity' => $deal->total_inventory ? (string) $deal->total_inventory : '',
                'requestFeatured' => (bool) $deal->is_featured,
                'status' => $deal->status,
                'images' => $deal->images->map(fn ($img) => [
                    'id' => $img->id,
                    'image_url' => $img->image_url,
                    'attribute_name' => $img->attribute_name,
                    'sort_order' => $img->sort_order,
                ]),
            ],
            'categories' => $categories,
        ]);
    }

    /**
     * Vendor: view deal (Inertia) - vendor-only preview page.
     */
    public function viewDeal(Deal $deal)
    {
        $user = auth()->user();
        $vendor = $user->vendorProfile;

        if ($vendor && $deal->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized');
        }

        $deal->load(['category.parent', 'images', 'offerTypes', 'vendor.defaultAddress']);

        return \Inertia\Inertia::render('vendor/DealView', [
            'deal' => [
                'id' => $deal->id,
                'title' => $deal->title,
                'status' => $deal->status,
                'basePrice' => $deal->base_price,
                'shortDesc' => $deal->short_description,
                'description' => $deal->long_description,
                'category' => $deal->category ? [
                    'id' => $deal->category->id,
                    'name' => $deal->category->name,
                    'parent' => $deal->category->parent ? [
                        'id' => $deal->category->parent->id,
                        'name' => $deal->category->parent->name,
                    ] : null,
                ] : null,
                'images' => $deal->images->map(fn ($img) => [
                    'id' => $img->id,
                    'image_url' => $img->image_url,
                    'attribute_name' => $img->attribute_name,
                    'sort_order' => $img->sort_order,
                ])->values()->toArray(),
                'offers' => $deal->offerTypes->map(function ($ot) {
                    $pct = (float) ($ot->pivot?->savings_percent ?? $ot->pivot?->discount_percent ?? 0);

                    return [
                        'id' => $ot->id,
                        'name' => $ot->name,
                        'display_name' => $ot->display_name,
                        'pivot' => [
                            'pivot_id' => $ot->pivot?->id,
                            'original_price' => $ot->pivot?->original_price,
                            'final_price' => $ot->pivot?->final_price,
                            'discount_percent' => $ot->pivot?->discount_percent,
                            'savings_percent' => $ot->pivot?->savings_percent,
                            'discountPercentage' => $pct > 0 ? $pct : null,
                            'status' => $ot->pivot?->status,
                            'starts_at' => $ot->pivot?->starts_at?->toDateString(),
                            'ends_at' => $ot->pivot?->ends_at?->toDateString(),
                        ],
                    ];
                })->values()->toArray(),
            ],
        ]);
    }

    /**
     * Vendor: update deal
     */
    public function updateDeal(Request $request, Deal $deal)
    {
        $user = auth()->user();
        $vendor = $user->vendorProfile;

        if ($vendor && $deal->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->all();

        $oldBasePrice = $deal->base_price !== null ? (float) $deal->base_price : null;

        $deal->update([
            'category_id' => (int) ($data['categoryId'] ?? $deal->category_id),
            'title' => $data['title'] ?? $deal->title,
            'slug' => $this->generateUniqueDealSlug((string) ($data['title'] ?? $deal->title), $deal->id),
            'base_price' => isset($data['basePrice']) && $data['basePrice'] !== '' ? (float) $data['basePrice'] : $deal->base_price,
            'short_description' => $data['shortDesc'] ?? $deal->short_description,
            'long_description' => $data['description'] ?? $deal->long_description,
            'total_inventory' => ! empty($data['maxQuantity']) ? (int) $data['maxQuantity'] : null,
            'highlights' => is_array($data['tags'] ?? []) ? ($data['tags'] ?? []) : [],
            'status' => $data['status'] ?? $deal->status,
        ]);

        // If base price changes, keep all offers in sync and recalculate their final prices.
        $newBasePrice = $deal->base_price !== null ? (float) $deal->base_price : null;
        if ($newBasePrice !== null && $oldBasePrice !== $newBasePrice) {
            $deal->load('offerTypes');
            foreach ($deal->offerTypes as $offerType) {
                $pivot = $offerType->pivot;
                if ($pivot instanceof \App\Models\DealOfferType) {
                    $pivot->original_price = $newBasePrice;
                    $pivot->saveQuietly();
                    $pivot->calculatePrices();
                }
            }
        }

        $this->syncDealImagesFromRequest($request, $deal, true);

        return redirect()->route('vendor.deals.index')->with('success', 'Deal updated successfully!');
    }

    /**
     * Vendor: manage offers for a deal (add multiple offer types)
     */
    public function offers(Deal $deal)
    {
        $user = auth()->user();
        $vendor = $user->vendorProfile;

        if ($vendor && $deal->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized');
        }

        $deal->load(['offerTypes', 'images']);
        $offerTypes = OfferType::where('is_active', true)->orderBy('display_name')->get(['id', 'name', 'display_name']);

        $shortDescription = $deal->short_description
            ? Str::limit(trim(strip_tags($deal->short_description)), 200)
            : null;

        return \Inertia\Inertia::render('vendor/DealOffers', [
            'deal' => [
                'id' => $deal->id,
                'title' => $deal->title,
                'slug' => $deal->slug,
                'basePrice' => $deal->base_price !== null ? (float) $deal->base_price : null,
                'status' => $deal->status,
                'shortDescription' => $shortDescription,
                'featuredImage' => $deal->featuredImageUrl(),
                'images' => $deal->images->map(fn ($img) => [
                    'id' => $img->id,
                    'url' => $img->image_url,
                    'label' => $img->attribute_name,
                ])->values()->all(),
            ],
            'offerTypes' => $offerTypes,
            'attachedOffers' => $deal->offerTypes->map(function ($ot) {
                return [
                    'id' => $ot->id,
                    'name' => $ot->name,
                    'display_name' => $ot->display_name,
                    'pivot' => [
                        'original_price' => $ot->pivot?->original_price,
                        'discount_percent' => $ot->pivot?->discount_percent,
                        'discount_amount' => $ot->pivot?->discount_amount,
                        'final_price' => $ot->pivot?->final_price,
                        'currency_code' => $ot->pivot?->currency_code,
                        'status' => $ot->pivot?->status,
                        'params' => $ot->pivot?->params,
                        'starts_at' => $ot->pivot?->starts_at?->toDateString(),
                        'ends_at' => $ot->pivot?->ends_at?->toDateString(),
                    ],
                ];
            }),
        ]);
    }

    public function attachOffer(Request $request, Deal $deal)
    {
        $user = auth()->user();
        $vendor = $user->vendorProfile;
        if ($vendor && $deal->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'offer_type_id' => ['required', 'exists:offer_types,id'],
            // original_price defaults to deal.base_price (no manual entry needed)
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'status' => ['nullable', 'string'],
            'params' => ['nullable', 'array'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            // UI helpers (converted into params on the server)
            'discount_percent' => ['nullable', 'numeric', 'min:0'],
            'offer_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (! isset($validated['original_price']) || $validated['original_price'] === null || $validated['original_price'] === '') {
            if ($deal->base_price === null) {
                return back()->withErrors(['original_price' => 'Set a base price on the deal before adding offers.']);
            }
            $validated['original_price'] = (float) $deal->base_price;
        }

        $offerType = OfferType::findOrFail((int) $validated['offer_type_id']);
        $service = app(DealOfferService::class);

        // Normalize UI helper fields into params so pricing always recalculates.
        $params = is_array($validated['params'] ?? null) ? ($validated['params'] ?? []) : [];
        if (isset($validated['discount_percent']) && $validated['discount_percent'] !== null && $validated['discount_percent'] !== '') {
            $params['discount_percent'] = (float) $validated['discount_percent'];
        }
        if (isset($validated['offer_price']) && $validated['offer_price'] !== null && $validated['offer_price'] !== '') {
            $params['discount_amount'] = max(0, (float) $validated['original_price'] - (float) $validated['offer_price']);
        }
        $validated['params'] = $params;

        $service->attachOfferToDeal($deal, $offerType, $validated);

        return redirect()->route('vendor.deals.offers', $deal)->with('success', 'Offer added.');
    }

    public function updateOffer(Request $request, Deal $deal, OfferType $offerType)
    {
        $user = auth()->user();
        $vendor = $user->vendorProfile;
        if ($vendor && $deal->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            // allow changing offer type (replace flow)
            'offer_type_id' => ['nullable', 'exists:offer_types,id'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'status' => ['nullable', 'string'],
            'params' => ['nullable', 'array'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            // UI helpers (converted into params on the server)
            'discount_percent' => ['nullable', 'numeric', 'min:0'],
            'offer_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (! isset($validated['original_price']) || $validated['original_price'] === null || $validated['original_price'] === '') {
            if ($deal->base_price === null) {
                return back()->withErrors(['original_price' => 'Set a base price on the deal before updating offers.']);
            }
            $validated['original_price'] = (float) $deal->base_price;
        }

        $service = app(DealOfferService::class);

        // Normalize UI helper fields into params so pricing always recalculates.
        $params = is_array($validated['params'] ?? null) ? ($validated['params'] ?? []) : [];
        if (isset($validated['discount_percent']) && $validated['discount_percent'] !== null && $validated['discount_percent'] !== '') {
            $params['discount_percent'] = (float) $validated['discount_percent'];
        }
        if (isset($validated['offer_price']) && $validated['offer_price'] !== null && $validated['offer_price'] !== '') {
            $params['discount_amount'] = max(0, (float) $validated['original_price'] - (float) $validated['offer_price']);
        }
        $validated['params'] = $params;

        $newOfferTypeId = isset($validated['offer_type_id']) && $validated['offer_type_id'] !== null && $validated['offer_type_id'] !== ''
            ? (int) $validated['offer_type_id']
            : null;

        if ($newOfferTypeId !== null && $newOfferTypeId !== (int) $offerType->id) {
            // Prevent duplicates
            $alreadyAttached = \App\Models\DealOfferType::where('deal_id', $deal->id)
                ->where('offer_type_id', $newOfferTypeId)
                ->exists();

            if ($alreadyAttached) {
                return back()->withErrors(['offer_type_id' => 'This offer type is already attached to the deal.']);
            }

            $newOfferType = OfferType::findOrFail($newOfferTypeId);

            DB::transaction(function () use ($service, $deal, $offerType, $newOfferType, $validated) {
                // Replace: remove old then attach new using the same payload
                $service->removeOfferFromDeal($deal, $offerType);
                $service->attachOfferToDeal($deal, $newOfferType, $validated);
            });
        } else {
            $service->updateOfferOnDeal($deal, $offerType, $validated);
        }

        return redirect()->route('vendor.deals.offers', $deal)->with('success', 'Offer updated.');
    }

    public function removeOffer(Deal $deal, OfferType $offerType)
    {
        $user = auth()->user();
        $vendor = $user->vendorProfile;
        if ($vendor && $deal->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized');
        }

        $service = app(DealOfferService::class);
        $service->removeOfferFromDeal($deal, $offerType);

        return redirect()->route('vendor.deals.offers', $deal)->with('success', 'Offer removed.');
    }

    public function show(Deal $deal)
    {
        $deal->load(['vendor', 'subCategory', 'offerTypes', 'images']);

        return view('deals.show', compact('deal'));
    }

    public function edit(Deal $deal)
    {
        $vendors = VendorProfile::orderBy('business_name')->get();
        $subCategories = BusinessSubCategory::active()->orderBy('display_order')->get();
        $offerTypes = OfferType::where('is_active', true)->orderBy('display_name')->get();
        $deal->load(['offerTypes', 'images']);

        return view('deals.edit', compact('deal', 'vendors', 'subCategories', 'offerTypes'));
    }

    public function update(UpdateDealRequest $request, Deal $deal)
    {
        $data = $request->validated();
        if (array_key_exists('title', $data) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $offerTypesInput = $request->input('offer_types', []);
        unset($data['offer_types']);

        $deal->update($data);

        $this->syncDealOffers($deal, $offerTypesInput);

        return redirect()->route('deals.show.by-deal', ['deal' => $deal->slug])->with('success', 'Deal updated successfully.');
    }

    protected function syncDealOffers(Deal $deal, array $offerTypesInput): void
    {
        $service = app(DealOfferService::class);
        $submittedIds = [];
        foreach ($offerTypesInput as $offerTypeId => $payload) {
            $offerTypeId = (int) $offerTypeId;
            $originalPrice = isset($payload['original_price']) ? (float) $payload['original_price'] : 0;
            if ($originalPrice <= 0) {
                continue;
            }
            $offerType = OfferType::find($offerTypeId);
            if (! $offerType) {
                continue;
            }
            $params = $payload['params'] ?? [];
            $params = is_array($params) ? $params : [];
            $params = array_filter($params, fn ($v) => $v !== '' && $v !== null);
            $data = [
                'original_price' => $originalPrice,
                'currency_code' => $payload['currency_code'] ?? 'NPR',
                'params' => $params,
                'status' => $payload['status'] ?? 'active',
            ];
            $pivot = DealOfferType::where('deal_id', $deal->id)->where('offer_type_id', $offerTypeId)->first();
            if ($pivot) {
                $service->updateOfferOnDeal($deal, $offerType, $data);
            } else {
                $service->attachOfferToDeal($deal, $offerType, $data);
            }
            $submittedIds[] = $offerTypeId;
        }
        $deal->load('offerTypes');
        foreach ($deal->offerTypes as $attached) {
            if (! in_array($attached->id, $submittedIds, true)) {
                $service->removeOfferFromDeal($deal, $attached);
            }
        }
    }

    public function destroy(Deal $deal)
    {
        $deal->delete();

        return redirect()->route('deals.index')->with('success', 'Deal deleted successfully.');
    }

    protected function syncDealImagesFromRequest(Request $request, Deal $deal, bool $isUpdate): void
    {
        $maxImages = 8;

        $uploadedFiles = array_values(array_filter(
            (array) $request->file('images', []),
            fn ($f) => $f instanceof \Illuminate\Http\UploadedFile && $f->isValid()
        ));

        $existingImages = $deal->images()->orderBy('sort_order')->orderBy('id')->get();
        $existingKeys = $existingImages->map(fn ($img) => 'existing:'.$img->id)->values()->all();
        $uploadedKeys = array_map(fn ($idx) => 'new:'.$idx, array_keys($uploadedFiles));

        $order = $this->normalizeImageOrder(
            $request->input('image_order'),
            $existingKeys,
            $uploadedKeys,
            $maxImages
        );

        if (empty($order)) {
            $order = array_slice(array_merge($existingKeys, $uploadedKeys), 0, $maxImages);
        }

        $featuredKey = (string) ($request->input('featured_image_key') ?? '');
        if ($featuredKey === '' || ! in_array($featuredKey, $order, true)) {
            $featuredKey = $order[0] ?? '';
        }

        $existingById = $existingImages->keyBy('id');
        $keepExistingIds = [];
        $uploadedUrlByKey = [];

        foreach ($uploadedFiles as $idx => $file) {
            $path = $file->store('deals/gallery', 'public');
            $uploadedUrlByKey['new:'.$idx] = '/storage/'.$path;
        }

        foreach ($order as $position => $key) {
            $isFeatured = $featuredKey !== '' && $key === $featuredKey;
            $attributeName = $isFeatured ? 'feature_photo' : 'gallery';

            if (str_starts_with($key, 'existing:')) {
                $id = (int) str_replace('existing:', '', $key);
                $img = $existingById->get($id);
                if (! $img) {
                    continue;
                }

                $keepExistingIds[] = $id;
                $img->attribute_name = $attributeName;
                $img->sort_order = $position;
                $img->save();

                continue;
            }

            if (isset($uploadedUrlByKey[$key])) {
                $deal->images()->create([
                    'attribute_name' => $attributeName,
                    'image_url' => $uploadedUrlByKey[$key],
                    'sort_order' => $position,
                ]);
            }
        }

        if ($isUpdate) {
            $toDelete = $existingImages->filter(fn ($img) => ! in_array($img->id, $keepExistingIds, true));
            foreach ($toDelete as $img) {
                if (! empty($img->image_url) && str_starts_with($img->image_url, '/storage/')) {
                    $storagePath = substr($img->image_url, strlen('/storage/'));
                    Storage::disk('public')->delete($storagePath);
                }
                $img->delete();
            }
        }
    }

    protected function normalizeImageOrder(
        mixed $rawOrder,
        array $existingKeys,
        array $uploadedKeys,
        int $maxImages
    ): array {
        $allowed = array_fill_keys(array_merge($existingKeys, $uploadedKeys), true);
        $order = [];

        if (is_string($rawOrder) && $rawOrder !== '') {
            $decoded = json_decode($rawOrder, true);
            if (is_array($decoded)) {
                $rawOrder = $decoded;
            }
        }

        if (is_array($rawOrder)) {
            foreach ($rawOrder as $entry) {
                $key = is_string($entry) ? $entry : '';
                if ($key === '' || ! isset($allowed[$key]) || in_array($key, $order, true)) {
                    continue;
                }
                $order[] = $key;
                if (count($order) >= $maxImages) {
                    break;
                }
            }
        }

        foreach (array_merge($existingKeys, $uploadedKeys) as $fallbackKey) {
            if (! in_array($fallbackKey, $order, true)) {
                $order[] = $fallbackKey;
            }
            if (count($order) >= $maxImages) {
                break;
            }
        }

        return array_slice($order, 0, $maxImages);
    }
}
