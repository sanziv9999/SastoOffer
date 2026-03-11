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
        $vendor = $this->route('vendorProfile');

        return [
            'business_name'        => ['sometimes', 'string', 'max:150'],
            'slug'                 => ['nullable', 'string', 'max:180', Rule::unique('vendor_profiles', 'slug')->ignore($vendor?->id)],
            'primary_category_id'  => ['nullable', 'exists:primary_categories,id'],
            'verified_status'      => ['sometimes', 'in:pending,verified,rejected,suspended'],
            'verified_by_user_id'  => ['nullable', 'exists:users,id'],
            'description'          => ['nullable', 'string'],
            'website_url'          => ['nullable', 'url', 'max:255'],
            'public_email'         => ['nullable', 'email', 'max:255'],
            'public_phone'         => ['nullable', 'string', 'max:20'],
            'business_hours'       => ['nullable', 'string'],
            'social_media'         => ['nullable', 'array'],
            'social_media.*.platform' => ['string'],
            'social_media.*.url' => ['url'],
            'default_address_id'  => ['nullable', 'exists:addresses,id'],
        ];
    }
}
