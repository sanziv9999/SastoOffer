<?php

namespace App\Support;

use App\Models\Deal;
use App\Models\DealOfferType;
use Illuminate\Support\Str;

class DealUrl
{
    /**
     * @return array{0: string, 1: string, 2: string} [mainCategory, subCategory, location]
     */
    public static function segments(Deal $deal): array
    {
        $deal->loadMissing('category.parent', 'vendor.defaultAddress');

        $cat = $deal->category;
        if ($cat) {
            $cat->loadMissing('parent');
        }
        if ($cat && $cat->parent_id && $cat->parent) {
            $main = $cat->parent->slug ?: 'uncategorized';
            $sub = $cat->slug ?: 'general';
        } elseif ($cat) {
            $main = $cat->slug ?: 'uncategorized';
            $sub = 'general';
        } else {
            $main = 'uncategorized';
            $sub = 'general';
        }

        $district = $deal->vendor?->defaultAddress?->district;
        $loc = $district ? Str::slug((string) $district) : 'unknown';
        if ($loc === '') {
            $loc = 'unknown';
        }

        return [$main, $sub, $loc];
    }

    public static function canonical(Deal $deal, string $offerTypeSlug): string
    {
        if ($deal->slug === null || $deal->slug === '') {
            return route('deals.show.by-deal', ['deal' => $deal->id]);
        }

        [$main, $sub, $loc] = self::segments($deal);

        return route('deals.show.canonical', [
            'mainCategory' => $main,
            'subCategory' => $sub,
            'location' => $loc,
            'dealSlug' => $deal->slug,
            'offerTypeSlug' => $offerTypeSlug,
        ]);
    }

    public static function fromPivot(DealOfferType $pivot): string
    {
        $pivot->loadMissing(['deal.category.parent', 'deal.vendor.defaultAddress', 'offerType']);
        $deal = $pivot->deal;
        if (! $deal) {
            return url('/');
        }

        $slug = $pivot->offerType?->slug;
        if (! $slug) {
            return $deal->slug
                ? route('deals.show.by-deal', ['deal' => $deal->slug])
                : route('deals.show.by-deal', ['deal' => $deal->id]);
        }

        return self::canonical($deal, $slug);
    }

    public static function forDealFirstOffer(Deal $deal): string
    {
        $deal->loadMissing(['activeOfferTypes', 'category.parent', 'vendor.defaultAddress']);
        $first = $deal->activeOfferTypes->first();
        $slug = $first?->slug;
        if (! $slug || ! $deal->slug) {
            return $deal->slug
                ? route('deals.show.by-deal', ['deal' => $deal->slug])
                : route('deals.show.by-deal', ['deal' => $deal->id]);
        }

        return self::canonical($deal, $slug);
    }
}
