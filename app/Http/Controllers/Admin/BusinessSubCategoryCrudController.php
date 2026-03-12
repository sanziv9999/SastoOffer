<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBusinessSubCategoryRequest;
use App\Http\Requests\UpdateBusinessSubCategoryRequest;
use App\Models\BusinessSubCategory;
use App\Models\PrimaryCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BusinessSubCategoryCrudController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $subCategories = BusinessSubCategory::query()
            ->with('primaryCategory:id,name')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderByRaw('COALESCE(display_order, 999999) asc')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/SubCategories/Index', [
            'subCategories' => $subCategories,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        $primaryCategories = PrimaryCategory::query()
            ->orderByRaw('COALESCE(display_order, 999999) asc')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('admin/SubCategories/Create', [
            'primaryCategories' => $primaryCategories,
        ]);
    }

    public function store(StoreBusinessSubCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        if (! isset($data['display_order']) || $data['display_order'] === null || $data['display_order'] === '') {
            $data['display_order'] = 0;
        }

        if (empty($data['slug']) && ! empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        BusinessSubCategory::create($data);

        return redirect()->route('admin.sub-categories.index')->with('success', 'Sub category created.');
    }

    public function edit(BusinessSubCategory $businessSubCategory): Response
    {
        $primaryCategories = PrimaryCategory::query()
            ->orderByRaw('COALESCE(display_order, 999999) asc')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('admin/SubCategories/Edit', [
            'subCategory' => $businessSubCategory->load('primaryCategory:id,name'),
            'primaryCategories' => $primaryCategories,
        ]);
    }

    public function update(UpdateBusinessSubCategoryRequest $request, BusinessSubCategory $businessSubCategory): RedirectResponse
    {
        $data = $request->validated();
        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }
        if (array_key_exists('display_order', $data) && ($data['display_order'] === null || $data['display_order'] === '')) {
            $data['display_order'] = 0;
        }
        if (array_key_exists('slug', $data) && empty($data['slug']) && ! empty($data['name'] ?? $businessSubCategory->name)) {
            $data['slug'] = Str::slug($data['name'] ?? $businessSubCategory->name);
        }

        $businessSubCategory->update($data);

        return redirect()->route('admin.sub-categories.index')->with('success', 'Sub category updated.');
    }

    public function destroy(BusinessSubCategory $businessSubCategory): RedirectResponse
    {
        $businessSubCategory->delete();

        return redirect()->route('admin.sub-categories.index')->with('success', 'Sub category deleted.');
    }
}

