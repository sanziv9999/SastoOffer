<?php

namespace App\Http\Controllers;

use App\Models\BusinessSubCategory;
use App\Models\BusinessType;
use App\Http\Requests\StoreBusinessSubCategoryRequest;
use App\Http\Requests\UpdateBusinessSubCategoryRequest;
use Illuminate\Http\Request;

class BusinessSubCategoryController extends Controller
{
    public function index(Request $request)
    {
        $subCategories = BusinessSubCategory::query()
            ->with('businessType')
            ->when($request->filled('business_type_id'), fn ($q) => $q->where('business_type_id', $request->business_type_id))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('display_order')
            ->paginate(15);

        return view('business-sub-categories.index', compact('subCategories'));
    }

    public function create()
    {
        $businessTypes = BusinessType::orderBy('display_order')->get();

        return view('business-sub-categories.create', compact('businessTypes'));
    }

    public function store(StoreBusinessSubCategoryRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        BusinessSubCategory::create($data);

        return redirect()->route('business-sub-categories.index')->with('success', 'Business sub-category created successfully.');
    }

    public function show(BusinessSubCategory $businessSubCategory)
    {
        $businessSubCategory->load(['businessType', 'images']);

        return view('business-sub-categories.show', compact('businessSubCategory'));
    }

    public function edit(BusinessSubCategory $businessSubCategory)
    {
        $businessTypes = BusinessType::orderBy('display_order')->get();
        $businessSubCategory->load('images');

        return view('business-sub-categories.edit', compact('businessSubCategory', 'businessTypes'));
    }

    public function update(UpdateBusinessSubCategoryRequest $request, BusinessSubCategory $businessSubCategory)
    {
        $data = $request->validated();
        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        $businessSubCategory->update($data);

        return redirect()->route('business-sub-categories.show', $businessSubCategory)->with('success', 'Business sub-category updated successfully.');
    }

    public function destroy(BusinessSubCategory $businessSubCategory)
    {
        $businessSubCategory->delete();

        return redirect()->route('business-sub-categories.index')->with('success', 'Business sub-category deleted successfully.');
    }
}
