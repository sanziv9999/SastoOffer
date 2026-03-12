<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePrimaryCategoryRequest;
use App\Http\Requests\UpdatePrimaryCategoryRequest;
use App\Models\PrimaryCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PrimaryCategoryCrudController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $primaryCategories = PrimaryCategory::query()
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

        return Inertia::render('admin/PrimaryCategories/Index', [
            'primaryCategories' => $primaryCategories,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/PrimaryCategories/Create');
    }

    public function store(StorePrimaryCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        if (! isset($data['display_order']) || $data['display_order'] === null || $data['display_order'] === '') {
            $data['display_order'] = 0;
        }

        if (empty($data['slug']) && ! empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        PrimaryCategory::create($data);

        return redirect()->route('admin.primary-categories.index')->with('success', 'Primary category created.');
    }

    public function edit(PrimaryCategory $primaryCategory): Response
    {
        return Inertia::render('admin/PrimaryCategories/Edit', [
            'primaryCategory' => $primaryCategory,
        ]);
    }

    public function update(UpdatePrimaryCategoryRequest $request, PrimaryCategory $primaryCategory): RedirectResponse
    {
        $data = $request->validated();
        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }
        if (array_key_exists('display_order', $data) && ($data['display_order'] === null || $data['display_order'] === '')) {
            $data['display_order'] = 0;
        }
        if (array_key_exists('slug', $data) && empty($data['slug']) && ! empty($data['name'] ?? $primaryCategory->name)) {
            $data['slug'] = Str::slug($data['name'] ?? $primaryCategory->name);
        }

        $primaryCategory->update($data);

        return redirect()->route('admin.primary-categories.index')->with('success', 'Primary category updated.');
    }

    public function destroy(PrimaryCategory $primaryCategory): RedirectResponse
    {
        $primaryCategory->delete();

        return redirect()->route('admin.primary-categories.index')->with('success', 'Primary category deleted.');
    }
}

