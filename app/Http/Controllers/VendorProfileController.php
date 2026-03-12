<?php

namespace App\Http\Controllers;

use App\Models\VendorProfile;
use App\Models\PrimaryCategory;
use App\Models\Address;
use App\Http\Requests\StoreVendorProfileRequest;
use App\Http\Requests\UpdateVendorProfileRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VendorProfileController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $vendors = VendorProfile::query()
            ->with(['user', 'primaryCategory'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('business_name', 'like', "%{$search}%")
                        ->orWhere('public_email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('verified_status'), fn ($q) => $q->where('verified_status', $request->verified_status))
            ->when($request->filled('primary_category_id'), fn ($q) => $q->where('primary_category_id', $request->primary_category_id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/AdminVendors', [
            'vendors' => $vendors,
            'filters' => ['search' => $search],
        ]);
    }

    public function create()
    {
        $primaryCategories = PrimaryCategory::orderBy('display_order')->get();

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
        $vendorProfile->load(['user', 'primaryCategory', 'defaultAddress', 'images']);
        $addresses = Address::where('user_id', $vendorProfile->user_id)->latest()->get();

        return Inertia::render('VendorProfile', compact('vendorProfile', 'addresses'));
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
        
        $primaryCategories = PrimaryCategory::orderBy('display_order')->get();
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
