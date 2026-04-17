<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePrimaryCategoryRequest;
use App\Http\Requests\UpdatePrimaryCategoryRequest;
use App\Models\Category;
use App\Support\GdImageConverter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PrimaryCategoryCrudController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $primaryCategories = Category::query()
            ->whereNull('parent_id')
            ->with('children')
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
        $parentOptions = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('admin/PrimaryCategories/Create', [
            'parentOptions' => $parentOptions,
        ]);
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

        // If parent_id is supplied, this becomes a sub-category under that parent.
        // If not supplied, it will be a top-level category (parent_id = null).
        $data['parent_id'] = $data['parent_id'] ?? null;
        if ($data['parent_id']) {
            $data['icon_key'] = null;
        }

        if ($request->hasFile('image')) {
            $path = GdImageConverter::convertUploadedToJpeg($request->file('image'), 'categories');
            $data['image_url'] = '/storage/' . $path;
        }

        Category::create($data);

        return redirect()->route('admin.primary-categories.index')->with('success', 'Primary category created.');
    }

    public function edit(Category $primaryCategory): Response
    {
        $parentOptions = Category::whereNull('parent_id')
            ->where('id', '!=', $primaryCategory->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('admin/PrimaryCategories/Edit', [
            'primaryCategory' => $primaryCategory,
            'parentOptions'   => $parentOptions,
        ]);
    }

    public function update(UpdatePrimaryCategoryRequest $request, Category $primaryCategory): RedirectResponse
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
        $nextParentId = $data['parent_id'] ?? $primaryCategory->parent_id;
        if ($nextParentId) {
            $data['icon_key'] = null;
        }

        if ($request->boolean('remove_image')) {
            if (! empty($primaryCategory->image_url)) {
                $storagePath = str_replace('/storage/', '', $primaryCategory->image_url);
                Storage::disk('public')->delete($storagePath);
            }
            $data['image_url'] = null;
        }

        if ($request->hasFile('image')) {
            if (! empty($primaryCategory->image_url)) {
                $oldStoragePath = str_replace('/storage/', '', $primaryCategory->image_url);
                Storage::disk('public')->delete($oldStoragePath);
            }
            $path = GdImageConverter::convertUploadedToJpeg($request->file('image'), 'categories');
            $data['image_url'] = '/storage/' . $path;
        }

        $primaryCategory->update($data);

        return redirect()->route('admin.primary-categories.index')->with('success', 'Primary category updated.');
    }

    public function destroy(Category $primaryCategory): RedirectResponse
    {
        $primaryCategory->delete();

        return redirect()->route('admin.primary-categories.index')->with('success', 'Primary category deleted.');
    }
}

