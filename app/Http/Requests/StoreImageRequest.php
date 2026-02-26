<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreImageRequest extends FormRequest
{
    public const IMAGEABLE_TYPES = [
        'user' => \App\Models\User::class,
        'vendor_profile' => \App\Models\VendorProfile::class,
        'customer_profile' => \App\Models\CustomerProfile::class,
        'business_type' => \App\Models\BusinessType::class,
        'business_sub_category' => \App\Models\BusinessSubCategory::class,
        'deal' => \App\Models\Deal::class,
    ];

    public const ATTRIBUTES_BY_TYPE = [
        'user' => ['avatar'],
        'vendor_profile' => ['logo', 'cover', 'gallery'],
        'customer_profile' => ['profile_pic', 'gallery'],
        'business_type' => ['icon', 'banner'],
        'business_sub_category' => ['icon', 'banner', 'image'],
        'deal' => ['cover', 'gallery'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('imageable_type');
        $allowed = $type ? (self::ATTRIBUTES_BY_TYPE[$type] ?? ['image']) : ['image'];

        return [
            'imageable_type' => ['required', 'string', Rule::in(array_keys(self::IMAGEABLE_TYPES))],
            'imageable_id'   => ['required', 'integer'],
            'attribute_name' => ['required', 'string', 'max:100', Rule::in($allowed)],
            'image'          => ['required_without:image_url', 'nullable', 'file', 'image', 'max:5120'],
            'image_url'      => ['required_without:image', 'nullable', 'string', 'url', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required_without' => 'Upload a file or provide an image URL.',
            'image_url.required_without' => 'Provide an image URL or upload a file.',
        ];
    }
}
