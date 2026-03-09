<?php

namespace App\Http\Controllers;

use App\Models\BusinessType;
use App\Http\Requests\StoreBusinessTypeRequest;
use App\Http\Requests\UpdateBusinessTypeRequest;
use Illuminate\Http\Request;

class BusinessTypeController extends Controller
{
    public function index(Request $request)
    {
        $businessTypes = BusinessType::query()
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('display_order')
            ->paginate(15);

        return view('business-types.index', compact('businessTypes'));
    }

    public function create()
    {
        return view('business-types.create');
    }

    public function store(StoreBusinessTypeRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        BusinessType::create($data);

        return redirect()->route('business-types.index')->with('success', 'Business type created successfully.');
    }

    public function show(BusinessType $businessType)
    {
        $businessType->load(['subCategories', 'images']);

        return view('business-types.show', compact('businessType'));
    }

    public function edit(BusinessType $businessType)
    {
        $businessType->load('images');

        return view('business-types.edit', compact('businessType'));
    }

    public function update(UpdateBusinessTypeRequest $request, BusinessType $businessType)
    {
        $data = $request->validated();
        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        $businessType->update($data);

        return redirect()->route('business-types.show', $businessType)->with('success', 'Business type updated successfully.');
    }

    public function destroy(BusinessType $businessType)
    {
        $businessType->delete();

        return redirect()->route('business-types.index')->with('success', 'Business type deleted successfully.');
    }
}
