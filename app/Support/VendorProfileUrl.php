<?php

namespace App\Support;

use App\Models\VendorProfile;
use Illuminate\Support\Str;

class VendorProfileUrl
{
    /**
     * @return array{0: string, 1: string, 2: string} [category, location, businessType]
     */
    public static function segments(VendorProfile $vendorProfile): array
    {
        $vendorProfile->loadMissing(['primaryCategory.parent', 'defaultAddress']);

        $category = $vendorProfile->primaryCategory?->parent?->slug
            ?: $vendorProfile->primaryCategory?->slug
            ?: 'uncategorized';

        $location = Str::slug((string) ($vendorProfile->defaultAddress?->district ?? ''));
        if ($location === '') {
            $location = 'unknown';
        }

        $businessType = Str::slug((string) ($vendorProfile->business_type ?? ''));
        if ($businessType === '') {
            $businessType = 'general';
        }

        return [$category, $location, $businessType];
    }

    public static function canonical(VendorProfile $vendorProfile): string
    {
        [$category, $location, $businessType] = self::segments($vendorProfile);

        return route('vendor-profile.show.canonical', [
            'category' => $category,
            'location' => $location,
            'businessType' => $businessType,
            'vendorProfile' => $vendorProfile->slug ?: $vendorProfile->id,
        ]);
    }
}

