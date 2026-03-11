<?php

namespace App\Http\Controllers;

use App\Models\PrimaryCategory;
use App\Http\Requests\StorePrimaryCategoryRequest;
use App\Http\Requests\UpdatePrimaryCategoryRequest;
use Illuminate\Http\Request;

class PrimaryCategoryController extends Controller
{
    public function index(Request $request)
    {
        $primaryCategories = PrimaryCategory::query()
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('display_order')
            ->paginate(15);

        return view('primary-categories.index', compact('primaryCategories'));
    }

    public function create()
    {
        return view('primary-categories.create');
    }

    public function store(StorePrimaryCategoryRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        PrimaryCategory::create($data);

        return redirect()->route('primary-categories.index')->with('success', 'Primary category created successfully.');
    }

    public function show(PrimaryCategory $primaryCategory)
    {
        $primaryCategory->load(['subCategories', 'images']);

        return view('primary-categories.show', compact('primaryCategory'));
    }

    public function edit(PrimaryCategory $primaryCategory)
    {
        $primaryCategory->load('images');

        return view('primary-categories.edit', compact('primaryCategory'));
    }

    public function update(UpdatePrimaryCategoryRequest $request, PrimaryCategory $primaryCategory)
    {
        $data = $request->validated();
        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        $primaryCategory->update($data);

        return redirect()->route('primary-categories.show', $primaryCategory)->with('success', 'Primary category updated successfully.');
    }

    public function destroy(PrimaryCategory $primaryCategory)
    {
        $primaryCategory->delete();

        return redirect()->route('primary-categories.index')->with('success', 'Primary category deleted successfully.');
    }
}
