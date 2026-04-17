<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Image;
use App\Support\GdImageConverter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BannerCrudController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $banners = Banner::query()
            ->with([
                'category.parent',
                'images' => fn ($q) => $q->where('attribute_name', 'image'),
            ])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('title', 'like', "%{$search}%")
                        ->orWhere('text', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('id', 'desc')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Banner $banner) => $this->bannerPayload($banner));

        return Inertia::render('admin/Banners/Index', [
            'banners' => $banners,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/Banners/Create', [
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }

    public function store(StoreBannerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_featured'] = $request->boolean('is_featured');
        if (! isset($data['sort_order']) || $data['sort_order'] === null || $data['sort_order'] === '') {
            $data['sort_order'] = 0;
        }
        unset($data['image']);

        $banner = Banner::create($data);

        if ($request->hasFile('image')) {
            $this->replaceBannerImage($banner, $request->file('image'));
        }

        return redirect()->route('admin.banners.index')->with('success', 'Banner created.');
    }

    public function edit(Banner $banner): Response
    {
        $banner->load([
            'category.parent',
            'images' => fn ($q) => $q->where('attribute_name', 'image'),
        ]);

        return Inertia::render('admin/Banners/Edit', [
            'banner' => $this->bannerPayload($banner),
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }

    public function update(UpdateBannerRequest $request, Banner $banner): RedirectResponse
    {
        $data = $request->validated();
        if ($request->has('is_featured')) {
            $data['is_featured'] = $request->boolean('is_featured');
        }
        if (array_key_exists('sort_order', $data) && ($data['sort_order'] === null || $data['sort_order'] === '')) {
            $data['sort_order'] = 0;
        }
        unset($data['image'], $data['remove_image']);

        $banner->update($data);

        if ($request->boolean('remove_image')) {
            $this->deleteBannerImage($banner);
        }

        if ($request->hasFile('image')) {
            $this->replaceBannerImage($banner, $request->file('image'));
        }

        return redirect()->route('admin.banners.index')->with('success', 'Banner updated.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $this->deleteBannerImage($banner);
        $banner->images()->delete();
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Banner deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function bannerPayload(Banner $banner): array
    {
        $img = $banner->relationLoaded('images')
            ? $banner->images->firstWhere('attribute_name', 'image')
            : $banner->images()->where('attribute_name', 'image')->first();

        $category = $banner->relationLoaded('category') ? $banner->category : null;

        return [
            'id' => $banner->id,
            'title' => $banner->title,
            'text' => $banner->text,
            'is_featured' => $banner->is_featured,
            'sort_order' => $banner->sort_order,
            'category_id' => $banner->category_id,
            'category_label' => $category ? $this->categoryLabel($category) : null,
            'image_url' => $img instanceof Image ? $img->image_url : null,
            'updated_at' => $banner->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<int, array{id: int, label: string}>
     */
    protected function categoryOptions(): array
    {
        return Category::query()
            ->with('parent')
            ->get()
            ->sortBy(fn (Category $c) => mb_strtolower(
                ($c->parent ? $c->parent->name.' ' : '').$c->name
            ))
            ->values()
            ->map(fn (Category $c) => [
                'id' => $c->id,
                'label' => $this->categoryLabel($c),
            ])
            ->all();
    }

    protected function categoryLabel(Category $category): string
    {
        if ($category->relationLoaded('parent') && $category->parent) {
            return $category->parent->name.' › '.$category->name;
        }

        if ($category->parent_id) {
            $category->loadMissing('parent');

            return $category->parent
                ? $category->parent->name.' › '.$category->name
                : $category->name;
        }

        return $category->name;
    }

    protected function replaceBannerImage(Banner $banner, UploadedFile $file): void
    {
        $this->deleteBannerImage($banner);

        $path = GdImageConverter::convertUploadedToJpeg($file, 'banners');
        $banner->images()->create([
            'attribute_name' => 'image',
            'image_url' => '/storage/'.$path,
            'sort_order' => 0,
        ]);
    }

    protected function deleteBannerImage(Banner $banner): void
    {
        $existing = $banner->images()->where('attribute_name', 'image')->get();
        foreach ($existing as $img) {
            if ($img->image_url && str_starts_with((string) $img->image_url, '/storage/')) {
                $storagePath = str_replace('/storage/', '', $img->image_url);
                Storage::disk('public')->delete($storagePath);
            }
        }
        $banner->images()->where('attribute_name', 'image')->delete();
    }
}
