<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_id'                 => ['required', 'exists:vendor_profiles,id'],
            'business_sub_category_id'  => ['required', 'exists:business_sub_categories,id'],
            'title'                     => ['required', 'string', 'max:255'],
            'slug'                      => ['nullable', 'string', 'max:300', 'unique:deals,slug'],
            'short_description'        => ['nullable', 'string'],
            'long_description'         => ['nullable', 'string'],
            'highlights'                => ['nullable', 'array'],
            'highlights.*'              => ['string'],
            'status'                    => ['nullable', 'in:draft,active,inactive,expired'],
            'total_inventory'           => ['nullable', 'integer', 'min:0'],
            'min_per_customer'          => ['nullable', 'integer', 'min:1'],
            'max_per_customer'          => ['nullable', 'integer', 'min:1'],
            'starts_at'                 => ['nullable', 'date'],
            'ends_at'                   => ['nullable', 'date', 'after_or_equal:starts_at'],
            'voucher_valid_days'        => ['nullable', 'integer', 'min:0'],
            'offer_validation_rules'    => ['nullable', 'array'],
            'offer_types'               => ['nullable', 'array'],
            'offer_types.*.original_price' => ['nullable', 'numeric', 'min:0'],
            'offer_types.*.currency_code'  => ['nullable', 'string', 'size:3'],
            'offer_types.*.params'         => ['nullable', 'array'],
        ];
    }
}
