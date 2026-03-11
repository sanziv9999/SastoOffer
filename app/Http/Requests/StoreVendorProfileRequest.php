<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreVendorProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'              => ['required', 'exists:users,id', 'unique:vendor_profiles,user_id'],
            'business_name'        => ['required', 'string', 'max:150'],
            'business_type'        => ['nullable', 'in:service,product,hybrid'],
            'slug'                 => ['nullable', 'string', 'max:180', 'unique:vendor_profiles,slug'],
            'primary_category_id'  => ['nullable', 'exists:primary_categories,id'],
            'verified_status'      => ['nullable', 'in:pending,verified,rejected,suspended'],
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
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('business_name') && ! $this->filled('slug')) {
            $this->merge(['slug' => Str::slug($this->business_name)]);
        }
    }
}
