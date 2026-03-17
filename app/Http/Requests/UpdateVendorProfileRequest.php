<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVendorProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $vendor = $this->route('vendorProfile') ?? \App\Models\VendorProfile::where('user_id', auth()->id())->first();

        return [
            'business_name'        => ['sometimes', 'string', 'max:150'],
            'business_type'        => ['sometimes', 'in:service,product,hybrid'],
            'slug'                 => ['nullable', 'string', 'max:180', Rule::unique('vendor_profiles', 'slug')->ignore($vendor?->id)],
            'category_id'          => ['nullable', 'exists:categories,id'],
            'verified_status'      => ['sometimes', 'in:pending,verified,rejected,suspended'],
            'verified_by_user_id'  => ['nullable', 'exists:users,id'],
            'description'          => ['nullable', 'string'],
            'website_url'          => ['nullable', 'url', 'max:255'],
            'public_email'         => ['nullable', 'email', 'max:255'],
            'public_phone'         => ['nullable', 'string', 'max:20'],
            'business_hours'       => ['nullable', 'array'],
            'business_hours.*.day' => ['string'],
            'business_hours.*.open' => ['nullable', 'string'],
            'business_hours.*.close' => ['nullable', 'string'],
            'business_hours.*.is_closed' => ['boolean'],
            'social_media'         => ['nullable', 'array'],
            'social_media.*.platform' => ['nullable', 'string'],
            'social_media.*.url' => ['nullable', 'url'],
            'default_address_id'  => ['nullable', 'exists:addresses,id'],
            'province'       => ['nullable', 'string'],
            'district'       => ['nullable', 'string', 'max:100'],
            'municipality'   => ['nullable', 'string', 'max:100'],
            'ward_no'        => ['nullable', 'string', 'max:20'],
            'tole'           => ['nullable', 'string', 'max:255'],
            'latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'      => ['nullable', 'numeric', 'between:-180,180'],
            'logo'           => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'cover'          => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }
}
