<?php

namespace App\Http\Controllers;

use App\Models\CustomerProfile;
use App\Models\Address;
use App\Http\Requests\StoreCustomerProfileRequest;
use App\Http\Requests\UpdateCustomerProfileRequest;
use Illuminate\Http\Request;

class CustomerProfileController extends Controller
{
    public function index(Request $request)
    {
        $customers = CustomerProfile::query()
            ->with('user')
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate(15);

        return view('customer-profiles.index', compact('customers'));
    }

    public function create()
    {
        return view('customer-profiles.create');
    }

    public function store(StoreCustomerProfileRequest $request)
    {
        CustomerProfile::create($request->validated());

        return redirect()->route('customer-profiles.index')->with('success', 'Customer profile created successfully.');
    }

    public function show(CustomerProfile $customerProfile)
    {
        $customerProfile->load(['user', 'defaultAddress', 'images']);
        $addresses = Address::where('user_id', $customerProfile->user_id)->latest()->get();

        return view('customer-profiles.show', compact('customerProfile', 'addresses'));
    }

    public function edit(CustomerProfile $customerProfile)
    {
        $addresses = Address::where('user_id', $customerProfile->user_id)->latest()->get();
        $customerProfile->load('images');

        return view('customer-profiles.edit', compact('customerProfile', 'addresses'));
    }

    public function update(UpdateCustomerProfileRequest $request, CustomerProfile $customerProfile)
    {
        $customerProfile->update($request->validated());

        return redirect()->route('customer-profiles.show', $customerProfile)->with('success', 'Customer profile updated successfully.');
    }

    public function destroy(CustomerProfile $customerProfile)
    {
        $customerProfile->delete();

        return redirect()->route('customer-profiles.index')->with('success', 'Customer profile deleted successfully.');
    }
}
