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
            'business_type_id'     => ['nullable', 'exists:business_types,id'],
            'pan_number'           => ['nullable', 'string', 'max:50', Rule::unique('vendor_profiles', 'pan_number')->ignore($vendor?->id)],
            'verified_status'      => ['sometimes', 'in:pending,verified,rejected,suspended'],
            'verified_by_user_id'  => ['nullable', 'exists:users,id'],
            'commission_rate'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'description'          => ['nullable', 'string'],
            'website_url'          => ['nullable', 'url', 'max:255'],
            'default_location_id'  => ['nullable', 'exists:addresses,id'],
        ];
    }
}
