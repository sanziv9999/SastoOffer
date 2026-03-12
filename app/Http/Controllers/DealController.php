<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealOfferType;
use App\Models\OfferType;
use App\Models\VendorProfile;
use App\Models\BusinessSubCategory;
use App\Services\DealOfferService;
use App\Http\Requests\StoreDealRequest;
use App\Http\Requests\UpdateDealRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DealController extends Controller
{
    public function index(Request $request)
    {
        $deals = Deal::query()
            ->with(['vendor', 'subCategory'])
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
            ->with(['subCategory', 'offerTypes', 'images'])
            ->latest()
            ->get()
            ->map(function ($deal) {
                $offer = $deal->offerTypes->first()?->pivot;

                // Placeholder: when you implement real purchases, replace quantitySold
                $quantitySold = 0;

                return [
                    'id'             => $deal->id,
                    'title'          => $deal->title,
                    'status'         => $deal->status,
                    'discountedPrice'=> $offer ? (float) $offer->final_price : 0,
                    'originalPrice'  => $offer ? (float) $offer->original_price : 0,
                    'quantitySold'   => $quantitySold,
                    'maxQuantity'    => $deal->total_inventory,
                    'endDate'        => $deal->ends_at?->toIso8601String(),
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
            ->with(['subCategory', 'offerTypes', 'images'])
            ->latest()
            ->get()
            ->map(function ($deal) {
                $offer = $deal->offerTypes->first()?->pivot;
                return [
                    'id' => $deal->id,
                    'title' => $deal->title,
                    'status' => $deal->status,
                    'discountedPrice' => $offer ? (float) $offer->final_price : 0,
                    'originalPrice' => $offer ? (float) $offer->original_price : 0,
                    'quantitySold' => 0,
                    'maxQuantity' => $deal->total_inventory,
                    'endDate' => $deal->ends_at?->toIso8601String(),
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
        $subCategories = BusinessSubCategory::active()->orderBy('display_order')->get();
        $offerTypes = OfferType::where('is_active', true)->orderBy('display_name')->get();

        return \Inertia\Inertia::render('vendor/CreateDeal', [
            'vendors' => $vendors,
            'categories' => $subCategories,
            'offerTypes' => $offerTypes,
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

        // Map React fields to DB fields
        $dealData = [
            'vendor_id' => $vendor->id,
            'business_sub_category_id' => (int) ($data['categoryId'] ?? 0),
            'title' => $data['title'] ?? 'Untitled Deal',
            'slug' => Str::slug($data['title'] ?? 'untitled') . '-' . rand(1000, 9999),
            'short_description' => $data['shortDesc'] ?? null,
            'long_description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active',
            'total_inventory' => !empty($data['maxQuantity']) ? (int) $data['maxQuantity'] : null,
            'starts_at' => !empty($data['startDate']) ? $data['startDate'] : now(),
            'ends_at' => !empty($data['endDate']) ? $data['endDate'] : now()->addDays(30),
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

        $offerTypeId = $data['offerTypeId'] ?? null;
        $offerType = $offerTypeId ? OfferType::find($offerTypeId) : null;

        if ($offerType) {
            $params = [];
            $uiType = $data['uiType'] ?? 'percentage';

            if ($uiType === 'percentage') {
                $params['discount_percent'] = $data['discountPercentage'];
            } elseif (in_array($uiType, ['fixed', 'flash', 'bundle'])) {
                $params['discount_amount'] = (float)$data['originalPrice'] - (float)$data['discountedPrice'];
            } elseif ($uiType === 'bogo') {
                $params['buy_quantity'] = 1;
                $params['get_quantity'] = 1;
                $params['get_discount_percent'] = 100;
            }

            $service = app(DealOfferService::class);
            $service->attachOfferToDeal($deal, $offerType, [
                'original_price' => (float)$data['originalPrice'],
                'discounted_price' => (float)$data['discountedPrice'], // Note: service uses original_price and params to calculate final_price
                'params' => $params,
                'status' => 'active',
                'currency_code' => 'NPR',
            ]);
        }

        return redirect()->route('vendor.deals.index')->with('success', 'Deal created and published successfully!');
    }

    /**
     * Public deal view (Inertia)
     */
    public function showDeal(Deal $deal)
    {
        $deal->load(['vendor', 'subCategory', 'offerTypes', 'images']);

        $offer = $deal->offerTypes->first()?->pivot;

        return \Inertia\Inertia::render('DealDetails', [
            'deal' => [
                'id'               => $deal->id,
                'title'            => $deal->title,
                'short_description' => $deal->short_description,
                'long_description' => $deal->long_description,
                'status'           => $deal->status,
                'highlights'       => $deal->highlights,
                'ends_at'          => $deal->ends_at?->toIso8601String(),
                'is_featured'      => (bool) $deal->is_featured,
                'is_deal_of_day'   => (bool) $deal->is_deal_of_day,
                'is_best_seller'   => (bool) $deal->is_best_seller,
                'is_new_arrival'   => (bool) $deal->is_new_arrival,
                'discountedPrice'  => $offer ? (float) $offer->final_price : null,
                'originalPrice'    => $offer ? (float) $offer->original_price : null,
                'discountPercent'  => $offer ? (float) ($offer->discount_percent ?? 0) : null,
                'images'           => $deal->images->map(fn($img) => [
                    'id'             => $img->id,
                    'image_url'      => $img->image_url,
                    'attribute_name' => $img->attribute_name,
                ]),
                'vendor'           => $deal->vendor ? [
                    'id'            => $deal->vendor->id,
                    'business_name' => $deal->vendor->business_name,
                ] : null,
                'subCategory'      => $deal->subCategory ? [
                    'id'   => $deal->subCategory->id,
                    'name' => $deal->subCategory->name,
                ] : null,
            ],
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

        $subCategories = BusinessSubCategory::active()->orderBy('display_order')->get();
        $offerTypes    = OfferType::where('is_active', true)->orderBy('display_name')->get();
        $deal->load(['offerTypes', 'images']);

        $offer = $deal->offerTypes->first()?->pivot;

        return \Inertia\Inertia::render('vendor/EditDeal', [
            'deal'       => [
                'id'               => $deal->id,
                'title'            => $deal->title,
                'shortDesc'        => $deal->short_description,
                'description'      => $deal->long_description,
                'categoryId'       => $deal->business_sub_category_id,
                'offerTypeId'      => $deal->offerTypes->first()?->id,
                'tags'             => $deal->highlights ?? [],
                'originalPrice'    => $offer ? (string) $offer->original_price : '',
                'discountedPrice'  => $offer ? (string) $offer->final_price : '',
                'discountPercent'  => $offer ? (float) ($offer->discount_percent ?? 0) : null,
                'maxQuantity'      => $deal->total_inventory ? (string) $deal->total_inventory : '',
                'startDate'        => $deal->starts_at?->format('Y-m-d'),
                'endDate'          => $deal->ends_at?->format('Y-m-d'),
                'requestFeatured'  => (bool) $deal->is_featured,
                'status'           => $deal->status,
                'images'           => $deal->images->map(fn($img) => [
                    'id'             => $img->id,
                    'image_url'      => $img->image_url,
                    'attribute_name' => $img->attribute_name,
                ]),
            ],
            'categories' => $subCategories,
            'offerTypes' => $offerTypes,
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

        $deal->update([
            'business_sub_category_id' => (int) ($data['categoryId'] ?? $deal->business_sub_category_id),
            'title'                    => $data['title'] ?? $deal->title,
            'slug'                     => Str::slug($data['title'] ?? $deal->title) . '-' . $deal->id,
            'short_description'        => $data['shortDesc'] ?? $deal->short_description,
            'long_description'         => $data['description'] ?? $deal->long_description,
            'total_inventory'          => !empty($data['maxQuantity']) ? (int) $data['maxQuantity'] : null,
            'starts_at'                => !empty($data['startDate']) ? $data['startDate'] : $deal->starts_at,
            'ends_at'                  => !empty($data['endDate']) ? $data['endDate'] : $deal->ends_at,
            'highlights'               => is_array($data['tags'] ?? []) ? ($data['tags'] ?? []) : [],
            'status'                   => $data['status'] ?? $deal->status,
        ]);

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

        // ─── Sync Offer Type & Pricing (pivot: deal_offer_type) ─────────────

        $offerTypeId = $data['offerTypeId'] ?? null;
        $offerType   = $offerTypeId ? OfferType::find($offerTypeId) : null;

        if ($offerType) {
            $params = [];
            $uiType = $data['uiType'] ?? 'percentage';

            if ($uiType === 'percentage') {
                $params['discount_percent'] = (float) ($data['discountPercentage'] ?? 0);
            } elseif (in_array($uiType, ['fixed', 'flash', 'bundle'], true)) {
                $original   = (float) ($data['originalPrice'] ?? 0);
                $discounted = (float) ($data['discountedPrice'] ?? 0);
                $params['discount_amount'] = max(0, $original - $discounted);
            } elseif ($uiType === 'bogo') {
                $params['buy_quantity']         = 1;
                $params['get_quantity']         = 1;
                $params['get_discount_percent'] = 100;
            }

            $service = app(DealOfferService::class);

            $payload = [
                'original_price' => (float) ($data['originalPrice'] ?? 0),
                'currency_code'  => 'NPR',
                'params'         => $params,
                'status'         => 'active',
            ];

            // If pivot exists for this offer type, update; otherwise attach new
            $existingPivot = DealOfferType::where('deal_id', $deal->id)
                ->where('offer_type_id', $offerType->id)
                ->first();

            if ($existingPivot) {
                $service->updateOfferOnDeal($deal, $offerType, $payload);
            } else {
                $service->attachOfferToDeal($deal, $offerType, $payload);
            }

            // Ensure only the selected offer type remains attached to this deal
            $deal->load('offerTypes');
            foreach ($deal->offerTypes as $attached) {
                if ($attached->id !== $offerType->id) {
                    $service->removeOfferFromDeal($deal, $attached);
                }
            }
        }

        return redirect()->route('vendor.deals.index')->with('success', 'Deal updated successfully!');
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
