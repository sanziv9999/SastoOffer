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
            'business_type_id'     => ['nullable', 'exists:business_types,id'],
            'pan_number'           => ['nullable', 'string', 'max:50', 'unique:vendor_profiles,pan_number'],
            'verified_status'      => ['nullable', 'in:pending,verified,rejected,suspended'],
            'commission_rate'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'description'          => ['nullable', 'string'],
            'website_url'          => ['nullable', 'url', 'max:255'],
            'default_location_id'  => ['nullable', 'exists:addresses,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('business_name') && ! $this->filled('slug')) {
            $this->merge(['slug' => Str::slug($this->business_name)]);
        }
    }
}
