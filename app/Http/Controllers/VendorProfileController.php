<?php

namespace App\Http\Controllers;

use App\Models\VendorProfile;
use App\Models\BusinessType;
use App\Models\Address;
use App\Http\Requests\StoreVendorProfileRequest;
use App\Http\Requests\UpdateVendorProfileRequest;
use Illuminate\Http\Request;

class VendorProfileController extends Controller
{
    public function index(Request $request)
    {
        $vendors = VendorProfile::query()
            ->with(['user', 'businessType'])
            ->when($request->filled('verified_status'), fn ($q) => $q->where('verified_status', $request->verified_status))
            ->when($request->filled('business_type_id'), fn ($q) => $q->where('business_type_id', $request->business_type_id))
            ->latest()
            ->paginate(15);

        return view('vendor-profiles.index', compact('vendors'));
    }

    public function create()
    {
        $businessTypes = BusinessType::orderBy('display_order')->get();

        return view('vendor-profiles.create', compact('businessTypes'));
    }

    public function store(StoreVendorProfileRequest $request)
    {
        VendorProfile::create($request->validated());

        return redirect()->route('vendor-profiles.index')->with('success', 'Vendor profile created successfully.');
    }

    public function show(VendorProfile $vendorProfile)
    {
        $vendorProfile->load(['user', 'businessType', 'defaultLocation', 'images']);
        $addresses = Address::where('user_id', $vendorProfile->user_id)->latest()->get();

        return view('vendor-profiles.show', compact('vendorProfile', 'addresses'));
    }

    public function edit(VendorProfile $vendorProfile)
    {
        $businessTypes = BusinessType::orderBy('display_order')->get();
        $addresses = Address::where('user_id', $vendorProfile->user_id)->latest()->get();
        $vendorProfile->load('images');

        return view('vendor-profiles.edit', compact('vendorProfile', 'businessTypes', 'addresses'));
    }

    public function update(UpdateVendorProfileRequest $request, VendorProfile $vendorProfile)
    {
        $vendorProfile->update($request->validated());

        return redirect()->route('vendor-profiles.show', $vendorProfile)->with('success', 'Vendor profile updated successfully.');
    }

    public function destroy(VendorProfile $vendorProfile)
    {
        $vendorProfile->delete();

        return redirect()->route('vendor-profiles.index')->with('success', 'Vendor profile deleted successfully.');
    }
}
