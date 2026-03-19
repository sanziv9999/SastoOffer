<?php

namespace App\Http\Controllers;

use App\Models\DealOfferType;
use App\Models\Review;
use App\Models\VendorProfile;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    protected function resolveReviewable(string $type, int $id)
    {
        return match ($type) {
            'deal_offer' => DealOfferType::findOrFail($id),
            'vendor' => VendorProfile::findOrFail($id),
            default => abort(422, 'Invalid reviewable type.'),
        };
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'reviewable_type' => ['required', 'in:deal_offer,vendor'],
            'reviewable_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $reviewable = $this->resolveReviewable($data['reviewable_type'], $data['reviewable_id']);

        $existing = Review::where('user_id', auth()->id())
            ->where('reviewable_type', get_class($reviewable))
            ->where('reviewable_id', $reviewable->id)
            ->first();

        if ($existing) {
            $existing->update([
                'rating' => $data['rating'],
                'comment' => $data['comment'],
            ]);
        } else {
            Review::create([
                'user_id' => auth()->id(),
                'reviewable_type' => get_class($reviewable),
                'reviewable_id' => $reviewable->id,
                'rating' => $data['rating'],
                'comment' => $data['comment'],
            ]);
        }

        return back()->with('success', 'Review submitted successfully.');
    }

    public function update(Request $request, Review $review)
    {
        if ((int) $review->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $review->update($data);

        return back()->with('success', 'Review updated successfully.');
    }

    public function destroy(Review $review)
    {
        if ((int) $review->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $review->delete();

        return back()->with('success', 'Review deleted successfully.');
    }

    public function vendorReply(Request $request, Review $review)
    {
        $vendor = auth()->user()->vendorProfile;

        if (! $vendor) {
            abort(403);
        }

        $isVendorReview = $review->reviewable_type === VendorProfile::class
            && (int) $review->reviewable_id === (int) $vendor->id;

        $isDealReview = $review->reviewable_type === DealOfferType::class
            && DealOfferType::where('id', $review->reviewable_id)
                ->whereHas('deal', fn ($q) => $q->where('vendor_id', $vendor->id))
                ->exists();

        if (! $isVendorReview && ! $isDealReview) {
            abort(403);
        }

        $data = $request->validate([
            'vendor_reply' => ['required', 'string', 'max:2000'],
        ]);

        $review->update([
            'vendor_reply' => $data['vendor_reply'],
            'vendor_replied_at' => now(),
        ]);

        return back()->with('success', 'Reply posted successfully.');
    }
}
