<?php

namespace App\Http\Controllers;

use App\Models\VendorProfile;
use App\Models\Category;
use App\Models\Address;
use App\Http\Requests\StoreVendorProfileRequest;
use App\Http\Requests\UpdateVendorProfileRequest;
use App\Services\ActivityMailer;
use App\Support\DealUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class VendorProfileController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $vendors = VendorProfile::query()
            ->with(['user', 'primaryCategory.parent', 'images'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('business_name', 'like', "%{$search}%")
                        ->orWhere('public_email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('verified_status'), fn ($q) => $q->where('verified_status', $request->verified_status))
            ->when($request->filled('primary_category_id'), fn ($q) => $q->where('category_id', $request->primary_category_id))
            ->latest()
            ->paginate(15)
            ->through(function (VendorProfile $v) {
                $logo = $v->images?->firstWhere('attribute_name', 'logo')?->image_url;
                $category = $v->primaryCategory;
                return [
                    'id' => $v->id,
                    'business_name' => $v->business_name,
                    'description' => $v->description,
                    'public_email' => $v->public_email,
                    'averageRating' => round((float) ($v->reviews_avg_rating ?? 0), 1),
                    'reviewCount' => (int) ($v->reviews_count ?? 0),
                    'verified_status' => $v->verified_status,
                    'created_at' => $v->created_at?->toIso8601String(),
                    'logo' => $logo,
                    'category' => $category ? [
                        'id' => $category->id,
                        'name' => $category->name,
                        'parent' => $category->parent ? [
                            'id' => $category->parent->id,
                            'name' => $category->parent->name,
                        ] : null,
                    ] : null,
                ];
            })
            ->withQueryString();

        return Inertia::render('admin/AdminVendors', [
            'vendors' => $vendors,
            'filters' => [
                'search' => $search,
                'verified_status' => $request->query('verified_status'),
            ],
        ]);
    }

    public function updateVerifiedStatus(Request $request, VendorProfile $vendorProfile, ActivityMailer $activityMailer)
    {
        $user = auth()->user();
        if (! $user || ! $user->hasRole('admin')) {
            abort(403);
        }

        $data = $request->validate([
            'verified_status' => ['required', 'in:pending,verified,rejected,suspended'],
        ]);

        $oldStatus = $vendorProfile->verified_status;
        $newStatus = $data['verified_status'];
        $vendorProfile->verified_status = $newStatus;

        if ($data['verified_status'] === 'verified') {
            $vendorProfile->verified_at = now();
            $vendorProfile->verified_by_user_id = $user->id;
        } else {
            $vendorProfile->verified_at = null;
            $vendorProfile->verified_by_user_id = null;
        }

        $vendorProfile->save();

        if ($oldStatus !== $newStatus) {
            try {
                $vendorProfile->loadMissing('user');
                $activityMailer->sendVendorStatusChanged($vendorProfile, $newStatus);
            } catch (\Throwable $e) {
                Log::warning('Vendor status mail failed', [
                    'vendor_profile_id' => $vendorProfile->id,
                    'status' => $newStatus,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return back()->with('success', 'Vendor status updated.');
    }

    public function adminShow(VendorProfile $vendorProfile)
    {
        $user = auth()->user();
        if (! $user || ! $user->hasRole('admin')) {
            abort(403);
        }

        $vendorProfile->load(['user', 'primaryCategory.parent', 'defaultAddress', 'images', 'verifiedBy']);

        $logo = $vendorProfile->images?->firstWhere('attribute_name', 'logo')?->image_url;
        $cover = $vendorProfile->images?->firstWhere('attribute_name', 'cover')?->image_url;
        $profileComplete = $vendorProfile->hasCompletedBusinessDetails();

        return Inertia::render('admin/AdminVendorView', [
            'vendor' => [
                'id' => $vendorProfile->id,
                'user' => [
                    'id' => $vendorProfile->user?->id,
                    'name' => $vendorProfile->user?->name,
                    'email' => $vendorProfile->user?->email,
                ],
                'business_name' => $vendorProfile->business_name,
                'slug' => $vendorProfile->slug,
                'business_type' => $vendorProfile->business_type,
                'description' => $vendorProfile->description,
                'public_email' => $vendorProfile->public_email,
                'public_phone' => $vendorProfile->public_phone,
                'website_url' => $vendorProfile->website_url,
                'verified_status' => $vendorProfile->verified_status,
                'verified_at' => $vendorProfile->verified_at?->toIso8601String(),
                'verified_by' => $vendorProfile->verifiedBy ? [
                    'id' => $vendorProfile->verifiedBy->id,
                    'name' => $vendorProfile->verifiedBy->name,
                    'email' => $vendorProfile->verifiedBy->email,
                ] : null,
                'created_at' => $vendorProfile->created_at?->toIso8601String(),
                'updated_at' => $vendorProfile->updated_at?->toIso8601String(),
                'logo' => $logo,
                'cover' => $cover,
                'business_hours' => $vendorProfile->business_hours,
                'social_media' => $vendorProfile->social_media,
                'is_profile_complete' => $profileComplete,
                'category' => $vendorProfile->primaryCategory ? [
                    'id' => $vendorProfile->primaryCategory->id,
                    'name' => $vendorProfile->primaryCategory->name,
                    'parent' => $vendorProfile->primaryCategory->parent ? [
                        'id' => $vendorProfile->primaryCategory->parent->id,
                        'name' => $vendorProfile->primaryCategory->parent->name,
                    ] : null,
                ] : null,
                'default_address' => $vendorProfile->defaultAddress ? [
                    'id' => $vendorProfile->defaultAddress->id,
                    'label' => $vendorProfile->defaultAddress->label,
                    'province' => $vendorProfile->defaultAddress->province,
                    'district' => $vendorProfile->defaultAddress->district,
                    'municipality' => $vendorProfile->defaultAddress->municipality,
                    'ward_no' => $vendorProfile->defaultAddress->ward_no,
                    'tole' => $vendorProfile->defaultAddress->tole,
                    'latitude' => $vendorProfile->defaultAddress->latitude,
                    'longitude' => $vendorProfile->defaultAddress->longitude,
                ] : null,
            ],
        ]);
    }

    public function create()
    {
        $primaryCategories = Category::whereNull('parent_id')
            ->orderBy('display_order')
            ->get();

        return Inertia::render('vendor-profiles/Create', compact('primaryCategories'));
    }

    public function store(StoreVendorProfileRequest $request)
    {
        $data = $request->validated();

        // Normalize business hours: keep only rows with a day, cast flags/fields
        if (isset($data['business_hours']) && is_array($data['business_hours'])) {
            $data['business_hours'] = collect($data['business_hours'])
                ->filter(fn ($row) => !empty($row['day']))
                ->values()
                ->map(function ($row) {
                    $isClosed = in_array($row['is_closed'] ?? false, [1, '1', true, 'true'], true);
                    return [
                        'day'       => $row['day'],
                        'open'      => $isClosed ? null : ($row['open'] ?? null),
                        'close'     => $isClosed ? null : ($row['close'] ?? null),
                        'is_closed' => $isClosed,
                    ];
                })
                ->all();
        }

        // Handle social media array
        if (isset($data['social_media'])) {
            $data['social_media'] = array_filter($data['social_media'], fn($item) => !empty($item['platform']) && !empty($item['url']));
        }

        // Handle Address creation
        $addressFields = ['province', 'district', 'municipality', 'ward_no', 'tole', 'latitude', 'longitude'];
        $hasAddressData = collect($data)->only($addressFields)->filter()->isNotEmpty();

        if ($hasAddressData) {
            $addressData = collect($data)->only($addressFields)->toArray();
            $addressData['user_id'] = $data['user_id'];
            $addressData['label'] = 'Pickup Point';

            $address = Address::create($addressData);
            $data['default_address_id'] = $address->id;
        }

        VendorProfile::create($data);

        return redirect()->route('vendor-profiles.index')->with('success', 'Vendor profile created successfully.');
    }

    public function show(VendorProfile $vendorProfile)
    {
        $rawParam = request()->segment(2);
        if ($vendorProfile->slug && $rawParam !== $vendorProfile->slug) {
            return redirect()->route('vendor-profile.show', ['vendorProfile' => $vendorProfile->slug], 301);
        }

        $vendorProfile->load(['user', 'primaryCategory.parent', 'defaultAddress', 'images'])
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');
        
        $logo = $vendorProfile->images?->firstWhere('attribute_name', 'logo')?->image_url;
        $cover = $vendorProfile->images?->firstWhere('attribute_name', 'cover')?->image_url;

        $statusTimezone = config('app.timezone', 'Asia/Kathmandu');
        $now = \Carbon\Carbon::now($statusTimezone);
        $today = $now->toDateString();

        // Fetch active offers for this vendor (match vendor_id + validate date window)
        $deals = \App\Models\DealOfferType::whereHas('deal', function ($q) use ($vendorProfile) {
                $q->where('vendor_id', $vendorProfile->id);
            })
            ->where('status', 'active')
            // Offer should have started (date-based: ignore time component)
            ->where(function ($q) use ($today) {
                $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', $today);
            })
            // Offer should not be expired yet (date-based: ignore time component)
            ->where(function ($q) use ($today) {
                $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today);
            })
            ->with(['deal.category.parent', 'deal.images', 'deal.vendor.defaultAddress', 'offerType'])
            ->latest()
            ->take(8)
            ->get();

        // Map deals for the deal-card component
        $mappedDeals = $deals->map(function ($pivot) use ($now) {
            $deal = $pivot->deal;
            $discountPct = (float) ($pivot->savings_percent ?? $pivot->discount_percent ?? 0);
            $address = $deal?->vendor?->defaultAddress;
            $locationLabel = collect([
                $address?->district,
                $address?->tole,
            ])->filter()->implode(', ');

            $endsAt = $pivot->ends_at;
            // If ends_at is stored as date-only (midnight), treat it as end-of-day.
            // Otherwise, compare with full datetime.
            $effectiveEndsAt = null;
            if ($endsAt) {
                $effectiveEndsAt = $endsAt->format('H:i:s') === '00:00:00'
                    ? $endsAt->copy()->endOfDay()
                    : $endsAt;
            }
            $isExpired = $effectiveEndsAt ? $effectiveEndsAt->lt($now) : false;

            return [
                'id'                => $deal?->id,
                'offerPivotId'      => $pivot->id,
                'dealSlug'          => $deal?->slug,
                'title'             => $deal?->title,
                'categoryName'      => optional($deal?->category?->parent)->name ?? ($deal?->category?->name ?? 'Uncategorized'),
                'originalPrice'     => $pivot->original_price !== null ? (float) $pivot->original_price : 0,
                'discountedPrice'   => $pivot->final_price !== null ? (float) $pivot->final_price : 0,
                'discountPercentage'=> $discountPct > 0 ? (int) $discountPct : null,
                'image'             => $deal?->featuredImageUrl('https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&fit=crop'),
                'featured'          => (bool) ($deal?->is_featured ?? false),
                // Prefer display_name, but fall back to name/slug so the UI always shows offer type.
                'offerTypeTitle'    => $pivot->offerType?->display_name
                    ?? $pivot->offerType?->name
                    ?? $pivot->offerType?->slug
                    ?? null,
                'locationLabel'     => $locationLabel ?: 'Location',
                'cityName'          => $locationLabel ?: 'Location',
                'status'            => $isExpired ? 'expired' : 'active',
                'timeLeft'          => $isExpired ? null : (optional($pivot->ends_at)?->diffForHumans() ?? 'soon'),
                'url'               => DealUrl::fromPivot($pivot),
            ];
        });

        $vendorReviews = $vendorProfile->reviews()
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
            ]);

        $userVendorReview = auth()->check()
            ? $vendorProfile->reviews()->where('user_id', auth()->id())->first()?->only(['id', 'rating', 'comment'])
            : null;

        return view('vendor-profile.show', [
            'vendor' => $vendorProfile,
            'logo' => $logo,
            'cover' => $cover,
            'deals' => $mappedDeals,
            'reviews' => $vendorReviews,
            'userReview' => $userVendorReview,
        ]);
    }

    public function edit(Request $request)
    {
        $vendorProfile = VendorProfile::firstOrCreate(
            ['user_id' => auth()->id()],
            [
                'business_name' => auth()->user()->name . ' Business',
                'slug' => \Illuminate\Support\Str::slug(auth()->user()->name . uniqid()),
                'business_type' => 'service',
            ]
        );
        
        $primaryCategories = Category::whereNull('parent_id')
            ->orderBy('display_order')
            ->get();
        $addresses = Address::where('user_id', auth()->id())->latest()->get();
        $vendorProfile->load(['images', 'defaultAddress']);

        return Inertia::render('vendor/Settings', compact('vendorProfile', 'primaryCategories', 'addresses'));
    }

    public function update(UpdateVendorProfileRequest $request, VendorProfile $vendorProfile)
    {
        $data = $request->validated();

        // Normalize business hours on update as well
        if (isset($data['business_hours']) && is_array($data['business_hours'])) {
            $data['business_hours'] = collect($data['business_hours'])
                ->filter(fn ($row) => !empty($row['day']))
                ->values()
                ->map(function ($row) {
                    $isClosed = in_array($row['is_closed'] ?? false, [1, '1', true, 'true'], true);
                    return [
                        'day'       => $row['day'],
                        'open'      => $isClosed ? null : ($row['open'] ?? null),
                        'close'     => $isClosed ? null : ($row['close'] ?? null),
                        'is_closed' => $isClosed,
                    ];
                })
                ->all();
        }

        // Handle social media array
        if (isset($data['social_media'])) {
            $data['social_media'] = array_filter($data['social_media'], fn($item) => !empty($item['platform']) && !empty($item['url']));
        }

        // Handle Address update/create
        $addressFields = ['province', 'district', 'municipality', 'ward_no', 'tole', 'latitude', 'longitude'];
        $hasAddressData = collect($data)->only($addressFields)->filter()->isNotEmpty();

        if ($hasAddressData) {
            $addressData = collect($data)->only($addressFields)->toArray();
            $addressData['user_id'] = $vendorProfile->user_id;
            $addressData['label'] = 'Pickup Point'; // Default label for vendor profile address

            if ($vendorProfile->default_address_id) {
                Address::where('id', $vendorProfile->default_address_id)->update($addressData);
            } else {
                $address = Address::create($addressData);
                $data['default_address_id'] = $address->id;
            }
        }

        $vendorProfile->update($data);

        // Handle Image Uploads (Polymorphic)
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('vendor_profiles/logos', 'public');
            $vendorProfile->images()->where('attribute_name', 'logo')->delete();
            $vendorProfile->images()->create([
                'attribute_name' => 'logo',
                'image_url' => '/storage/' . $path,
            ]);
        }

        if ($request->hasFile('cover')) {
            $path = $request->file('cover')->store('vendor_profiles/covers', 'public');
            $vendorProfile->images()->where('attribute_name', 'cover')->delete();
            $vendorProfile->images()->create([
                'attribute_name' => 'cover',
                'image_url' => '/storage/' . $path,
            ]);
        }

        // If profile is complete and not yet verified, keep it in pending queue for admin review.
        $vendorProfile->loadMissing('defaultAddress');
        if (
            $vendorProfile->hasCompletedBusinessDetails()
            && in_array($vendorProfile->verified_status, ['pending', 'rejected'], true)
        ) {
            $vendorProfile->verified_status = 'pending';
            $vendorProfile->verified_at = null;
            $vendorProfile->verified_by_user_id = null;
            $vendorProfile->save();
        }

        return redirect()->back()->with('success', 'Vendor profile updated successfully.');
    }

    public function updateSettings(UpdateVendorProfileRequest $request)
    {
        $vendorProfile = VendorProfile::where('user_id', auth()->id())->firstOrFail();
        return $this->update($request, $vendorProfile);
    }

    public function destroy(VendorProfile $vendorProfile)
    {
        $vendorProfile->delete();

        return redirect()->route('vendor-profiles.index')->with('success', 'Vendor profile deleted successfully.');
    }
}
