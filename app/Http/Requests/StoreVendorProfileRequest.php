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
            'slug'                 => ['nullable', 'string', 'max:180', 'unique:vendor_profiles,slug'],
            'primary_category_id'  => ['nullable', 'exists:primary_categories,id'],
            'verified_status'      => ['nullable', 'in:pending,verified,rejected,suspended'],
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

    protected function prepareForValidation(): void
    {
        if ($this->filled('business_name') && ! $this->filled('slug')) {
            $this->merge(['slug' => Str::slug($this->business_name)]);
        }
    }
}
