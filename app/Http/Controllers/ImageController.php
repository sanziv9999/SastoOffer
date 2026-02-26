<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Deal;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\CustomerProfile;
use App\Models\BusinessType;
use App\Models\BusinessSubCategory;
use App\Http\Requests\StoreImageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ImageController extends Controller
{
    public function store(StoreImageRequest $request)
    {
        $imageable = $this->resolveImageable(
            $request->input('imageable_type'),
            $request->input('imageable_id')
        );
        if (! $imageable) {
            abort(Response::HTTP_NOT_FOUND, 'Invalid imageable.');
        }
        if (! $this->canManageImages($request->user(), $imageable)) {
            abort(Response::HTTP_FORBIDDEN, 'You cannot add images to this.');
        }

        $attributeName = $request->input('attribute_name');
        $imageUrl = $request->input('image_url');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');
            $imageUrl = '/storage/' . $path;
        }

        $sortOrder = 0;
        if ($imageable->relationLoaded('images')) {
            $sortOrder = $imageable->images->max('sort_order') + 1;
        } else {
            $max = Image::where('imageable_type', get_class($imageable))
                ->where('imageable_id', $imageable->getKey())
                ->max('sort_order');
            $sortOrder = ($max ?? 0) + 1;
        }

        $imageable->images()->create([
            'attribute_name' => $attributeName,
            'image_url'      => $imageUrl,
            'sort_order'     => $sortOrder,
        ]);

        return redirect()->back()->with('success', 'Image added.');
    }

    public function destroy(Request $request, Image $image)
    {
        $image->load('imageable');
        $imageable = $image->imageable;
        if (! $imageable || ! $this->canManageImages($request->user(), $imageable)) {
            abort(Response::HTTP_FORBIDDEN, 'You cannot remove this image.');
        }

        $url = $image->image_url;
        $image->delete();

        if ($url && str_starts_with($url, '/storage/')) {
            $path = str_replace('/storage/', '', $url);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        return redirect()->back()->with('success', 'Image removed.');
    }

    protected function resolveImageable(string $type, int $id)
    {
        $class = StoreImageRequest::IMAGEABLE_TYPES[$type] ?? null;
        if (! $class || ! class_exists($class)) {
            return null;
        }
        return $class::find($id);
    }

    protected function canManageImages(User $user, $imageable): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($imageable instanceof User) {
            return $imageable->id === $user->id;
        }
        if ($imageable instanceof VendorProfile) {
            return $imageable->user_id === $user->id;
        }
        if ($imageable instanceof CustomerProfile) {
            return $imageable->user_id === $user->id;
        }
        if ($imageable instanceof Deal) {
            return $imageable->vendor && $imageable->vendor->user_id === $user->id;
        }
        if ($imageable instanceof BusinessType || $imageable instanceof BusinessSubCategory) {
            return $user->hasRole('admin');
        }

        return false;
    }
}
