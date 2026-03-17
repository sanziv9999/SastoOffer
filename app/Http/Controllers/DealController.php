<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealOfferType;
use App\Models\OfferType;
use App\Models\VendorProfile;
use App\Models\Category;
use App\Services\DealOfferService;
use App\Http\Requests\StoreDealRequest;
use App\Http\Requests\UpdateDealRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DealController extends Controller
{
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

        if (!$vendor) {
            return \Inertia\Inertia::render('VendorDashboard', [
                'vendor' => null,
                'stats' => null,
                'deals' => [],
            ]);
        }

        $deals = Deal::where('vendor_id', $vendor->id)
            ->with(['category', 'offerTypes', 'images'])
            ->latest()
            ->get()
            ->map(function ($deal) {
                $offer = $deal->offerTypes->first()?->pivot;
                $base = (float) ($deal->base_price ?? 0);

                // Placeholder: when you implement real purchases, replace quantitySold
                $quantitySold = 0;

                return [
                    'id'             => $deal->id,
                    'title'          => $deal->title,
                    'status'         => $deal->status,
                    'discountedPrice'=> $offer ? (float) $offer->final_price : $base,
                    'originalPrice'  => $offer ? (float) $offer->original_price : $base,
                    'quantitySold'   => $quantitySold,
                    'maxQuantity'    => $deal->total_inventory,
                    'endDate'        => $offer?->ends_at?->toIso8601String(),
                    'image'          => $deal->images->first()?->image_url ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=200&h=200&fit=crop',
                ];
            });

        // Basic dynamic stats derived from current deal data
        $totalSales   = $deals->sum('quantitySold');
        $totalRevenue = $deals->sum(function ($d) {
            return ($d['quantitySold'] ?? 0) * ($d['discountedPrice'] ?? 0);
        });

        $stats = [
            'totalRevenue' => $totalRevenue,
            'totalSales'   => $totalSales,
            'activeDeals'  => $deals->where('status', 'active')->count(),
            'totalDeals'   => $deals->count(),
        ];

        return \Inertia\Inertia::render('VendorDashboard', [
            'vendor' => $vendor,
            'stats' => $stats,
            'deals' => $deals,
        ]);
    }

    public function manageDeals(Request $request)
    {
        $user = auth()->user();
        $vendor = $user->vendorProfile;

        if (!$vendor) {
            return redirect()->route('vendor.dashboard')->with('error', 'Vendor profile not found.');
        }

        $deals = Deal::where('vendor_id', $vendor->id)
            ->with(['category', 'offerTypes', 'images'])
            ->latest()
            ->get()
            ->map(function ($deal) {
                $base = (float) ($deal->base_price ?? 0);
                return [
                    'id' => $deal->id,
                    'title' => $deal->title,
                    'status' => $deal->status,
                    // Manage Deals list should show only deal table data (base price),
                    // offers are managed separately in the Offers screen.
                    'price' => $base,
                    'quantitySold' => 0,
                    'maxQuantity' => $deal->total_inventory,
                    'endDate' => null,
                    'image' => $deal->images->first()?->image_url ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=200&h=200&fit=crop',
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
        if (!$user || !$user->hasRole('vendor')) {
            return back()->withErrors(['error' => 'Unauthorized. Only vendors can create deals.']);
        }

        $vendor = $user->vendorProfile;
        if (!$vendor) {
             // Fallback for demo/early stage
             $vendor = VendorProfile::first(); 
        }

        $data = $request->all();
        \Illuminate\Support\Facades\Log::info('Deal Creation Request:', [
            'data' => array_keys($data),
            'files' => array_keys($request->allFiles()),
            'has_featurePhoto' => $request->hasFile('featurePhoto'),
            'has_gallery' => $request->hasFile('gallery'),
        ]);

        // Map React fields to DB fields (core deal only; offers added separately)
        $dealData = [
            'vendor_id' => $vendor->id,
            'category_id' => (int) ($data['categoryId'] ?? 0),
            'title' => $data['title'] ?? 'Untitled Deal',
            'slug' => Str::slug($data['title'] ?? 'untitled') . '-' . rand(1000, 9999),
            'base_price' => isset($data['basePrice']) && $data['basePrice'] !== '' ? (float) $data['basePrice'] : null,
            'short_description' => $data['shortDesc'] ?? null,
            'long_description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active',
            'total_inventory' => !empty($data['maxQuantity']) ? (int) $data['maxQuantity'] : null,
            'highlights' => is_array($data['tags'] ?? []) ? $data['tags'] ?? [] : [$data['tags'] ?? ''],
        ];


        $deal = Deal::create($dealData);

        // Handle Feature Photo
        $featurePhoto = $request->file('featurePhoto');
        if ($featurePhoto instanceof \Illuminate\Http\UploadedFile && $featurePhoto->isValid()) {
            $path = $featurePhoto->store('deals/covers', 'public');
            \Illuminate\Support\Facades\Log::info('Feature photo stored:', ['path' => $path]);
            $deal->images()->create([
                'attribute_name' => 'feature_photo',
                'image_url' => '/storage/' . $path,
                'sort_order' => 0,
            ]);
        }

        // Handle Gallery Photos
        $galleryFiles = $request->file('gallery');
        if (is_array($galleryFiles) && count($galleryFiles) > 0) {
            $validGallery = array_values(array_filter(
                $galleryFiles,
                fn ($f) => $f instanceof \Illuminate\Http\UploadedFile && $f->isValid()
            ));
            \Illuminate\Support\Facades\Log::info('Processing gallery photos:', ['count' => count($validGallery)]);
            foreach ($validGallery as $index => $file) {
                $path = $file->store('deals/gallery', 'public');
                \Illuminate\Support\Facades\Log::info('Gallery photo stored:', ['path' => $path]);
                $deal->images()->create([
                    'attribute_name' => 'gallery',
                    'image_url' => '/storage/' . $path,
                    'sort_order' => $index + 1,
                ]);
            }
        }

        return redirect()
            ->route('vendor.deals.offers', $deal)
            ->with('success', 'Deal created. Now add one or more offers.');
    }

    /**
     * Public deal view (Inertia)
     */
    public function showDeal($id)
    {
        try {

            $deal = Deal::with(['vendor', 'category', 'images', 'offerTypes'])->find($id);
            if (!$deal) {
                return view('deals.show', ['deal' => null]);
            }

            $offerType = $deal->offerTypes->first();
            $offer = $offerType?->pivot;
            $base = $deal->base_price !== null ? (float) $deal->base_price : null;
            return view('deals.show', [
                'deal' => [
                    'id'               => $deal->id,
                    'title'            => $deal->title,
                    'short_description' => $deal->short_description,
                    'long_description' => $deal->long_description,
                    'status'           => $deal->status,
                    'highlights'       => is_array($deal->highlights) ? $deal->highlights : [],
                    'ends_at'          => $offer?->ends_at?->toIso8601String(),
                    'is_featured'      => (bool) $deal->is_featured,
                    'discountedPrice'  => $offer ? (float) $offer->final_price : $base,
                    'originalPrice'    => $offer ? (float) $offer->original_price : $base,
                    'discountPercent'  => $offer ? (float) ($offer->discount_percent ?? 0) : null,
                    'offers'           => $deal->offerTypes->map(function ($ot) {
                        return [
                            'id' => $ot->id,
                            'name' => $ot->name,
                            'display_name' => $ot->display_name,
                            'pivot' => [
                                'original_price' => $ot->pivot?->original_price,
                                'final_price' => $ot->pivot?->final_price,
                                'currency_code' => $ot->pivot?->currency_code,
                                'status' => $ot->pivot?->status,
                                'params' => $ot->pivot?->params,
                                'starts_at' => $ot->pivot?->starts_at?->toIso8601String(),
                                'ends_at' => $ot->pivot?->ends_at?->toIso8601String(),
                            ],
                        ];
                    })->values()->toArray(),
                    'images'           => $deal->images->map(fn($img) => [
                        'id'             => $img->id,
                        'image_url'      => $img->image_url,
                        'attribute_name' => $img->attribute_name,
                    ])->toArray(),
                    'vendor'           => $deal->vendor ? [
                        'id'            => $deal->vendor->id,
                        'business_name' => $deal->vendor->business_name,
                        'rating'        => 4.8, 
                        'reviewCount'   => 42
                    ] : null,
                    'category'      => $deal->category ? [
                        'id'   => $deal->category->id,
                        'name' => $deal->category->name,
                    ] : null,
                ],
            ]);
        } catch (\Exception $e) {
            // Log the exception for debugging
            \Illuminate\Support\Facades\Log::error("Error in showDeal: " . $e->getMessage());
            // Redirect to a safe page or show an error
            return redirect()->route('home')->with('error', 'Could not load deal details.');
        }
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
            'deal'       => [
                'id'               => $deal->id,
                'title'            => $deal->title,
                'shortDesc'        => $deal->short_description,
                'description'      => $deal->long_description,
                'categoryId'       => $deal->category_id,
                'basePrice'        => $deal->base_price ? (string) $deal->base_price : '',
                'tags'             => $deal->highlights ?? [],
                'maxQuantity'      => $deal->total_inventory ? (string) $deal->total_inventory : '',
                'requestFeatured'  => (bool) $deal->is_featured,
                'status'           => $deal->status,
                'images'           => $deal->images->map(fn($img) => [
                    'id'             => $img->id,
                    'image_url'      => $img->image_url,
                    'attribute_name' => $img->attribute_name,
                ]),
            ],
            'categories' => $categories,
        ]);
    }

    /**
     * Vendor: update deal
     */
    public function updateDeal(Request $request, Deal $deal)
    {
        $user   = auth()->user();
        $vendor = $user->vendorProfile;

        if ($vendor && $deal->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->all();

        $oldBasePrice = $deal->base_price !== null ? (float) $deal->base_price : null;

        $deal->update([
            'category_id'              => (int) ($data['categoryId'] ?? $deal->category_id),
            'title'                    => $data['title'] ?? $deal->title,
            'slug'                     => Str::slug($data['title'] ?? $deal->title) . '-' . $deal->id,
            'base_price'               => isset($data['basePrice']) && $data['basePrice'] !== '' ? (float) $data['basePrice'] : $deal->base_price,
            'short_description'        => $data['shortDesc'] ?? $deal->short_description,
            'long_description'         => $data['description'] ?? $deal->long_description,
            'total_inventory'          => !empty($data['maxQuantity']) ? (int) $data['maxQuantity'] : null,
            'highlights'               => is_array($data['tags'] ?? []) ? ($data['tags'] ?? []) : [],
            'status'                   => $data['status'] ?? $deal->status,
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

        // Handle Feature Photo replacement
        $featurePhoto = $request->file('featurePhoto');
        if ($featurePhoto instanceof \Illuminate\Http\UploadedFile && $featurePhoto->isValid()) {
            $deal->images()->where('attribute_name', 'feature_photo')->delete();
            $path = $featurePhoto->store('deals/covers', 'public');
            $deal->images()->create([
                'attribute_name' => 'feature_photo',
                'image_url'      => '/storage/' . $path,
                'sort_order'     => 0,
            ]);
        }

        // Handle Gallery: delete removed images
        $keptIds = $request->input('keptGalleryIds', []);
        if (is_string($keptIds)) {
            // Support both JSON-encoded and comma-separated formats
            $decoded = json_decode($keptIds, true);
            if (is_array($decoded)) {
                $keptIds = $decoded;
            } else {
                $keptIds = array_filter(array_map('intval', explode(',', $keptIds)));
            }
        }
        $keptIds = array_map('intval', (array) $keptIds);

        $deal->images()
            ->where('attribute_name', 'gallery')
            ->whereNotIn('id', $keptIds)
            ->delete();

        // Append new gallery photos
        $galleryFiles = $request->file('gallery');
        if (is_array($galleryFiles) && count($galleryFiles) > 0) {
            $validGallery = array_values(array_filter(
                $galleryFiles,
                fn ($f) => $f instanceof \Illuminate\Http\UploadedFile && $f->isValid()
            ));
            foreach ($validGallery as $index => $file) {
                $path = $file->store('deals/gallery', 'public');
                $deal->images()->create([
                    'attribute_name' => 'gallery',
                    'image_url'      => '/storage/' . $path,
                    'sort_order'     => $index + 1,
                ]);
            }
        }

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

        $deal->load(['offerTypes']);
        $offerTypes = OfferType::where('is_active', true)->orderBy('display_name')->get(['id', 'name', 'display_name']);

        return \Inertia\Inertia::render('vendor/DealOffers', [
            'deal' => [
                'id' => $deal->id,
                'title' => $deal->title,
                'basePrice' => $deal->base_price,
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

        if (!isset($validated['original_price']) || $validated['original_price'] === null || $validated['original_price'] === '') {
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

        if (!isset($validated['original_price']) || $validated['original_price'] === null || $validated['original_price'] === '') {
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

        return redirect()->route('deals.show', $deal)->with('success', 'Deal updated successfully.');
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
                'currency_code'  => $payload['currency_code'] ?? 'NPR',
                'params'         => $params,
                'status'         => $payload['status'] ?? 'active',
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
}
