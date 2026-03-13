<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddressRequest;
use App\Models\Address;
use App\Models\CustomerProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('CustomerDashboard', [
            'stats' => [],
            'recommendations' => [],
            'recentActivity' => [],
            'deals' => [],
        ]);
    }

    public function favorites()
    {
        return Inertia::render('dashboard/SavedDeals', [
            'favoriteDeals' => [],
        ]);
    }

    public function purchases()
    {
        return Inertia::render('dashboard/MyPurchases', [
            'purchases' => [],
            'deals' => [],
        ]);
    }

    public function voucherDetail($id)
    {
        return Inertia::render('dashboard/VoucherDetail', [
            'purchases' => [],
            'deals' => [],
            'vendors' => [],
        ]);
    }

    public function reviews()
    {
        return Inertia::render('dashboard/Reviews', [
            'reviews' => [],
            'deals' => [],
        ]);
    }

    public function editReview($id)
    {
        return Inertia::render('dashboard/EditReview', [
            'reviews' => [],
            'deals' => [],
        ]);
    }

    public function settings(Request $request)
    {
        $user = $request->user();

        /** @var CustomerProfile|null $profile */
        $profile = $user?->customerProfile;
        if ($profile) {
            $profile->load('images', 'defaultAddress');
        }

        return Inertia::render('dashboard/Settings', [
            'defaultAddress' => $user?->defaultAddress,
            'profile'        => $profile,
        ]);
    }

    public function saveAddress(StoreAddressRequest $request)
    {
        $user = $request->user();

        $data = $request->validated();

        $addressFields = ['province', 'district', 'municipality', 'ward_no', 'tole', 'latitude', 'longitude'];
        $addressData = collect($data)->only($addressFields)->filter()->toArray();

        if (empty($addressData)) {
            return back()->with('error', 'Please provide at least one address field.');
        }

        // Ensure address belongs to the authenticated user
        $addressData['user_id'] = $user->id;
        $addressData['label'] = $data['label'] ?? 'Home';

        // Make this the default address for the user
        Address::where('user_id', $user->id)->update(['is_default' => false]);
        $addressData['is_default'] = true;

        if ($user->defaultAddress) {
            $user->defaultAddress->update($addressData);
            $address = $user->defaultAddress;
        } else {
            $address = Address::create($addressData);
        }

        // If customer profile exists, sync default_address_id
        $user->customerProfile?->update([
            'default_address_id' => $address->id,
        ]);

        return back()->with('success', 'Address saved successfully.');
    }
}
