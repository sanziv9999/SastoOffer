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
            'category_id'               => ['required', 'exists:categories,id'],
            'title'                     => ['required', 'string', 'max:255'],
            'slug'                      => ['nullable', 'string', 'max:300', 'unique:deals,slug'],
            'short_description'        => ['nullable', 'string'],
            'long_description'         => ['nullable', 'string'],
            'highlights'                => ['nullable', 'array'],
            'highlights.*'              => ['string'],
            'status'                    => ['nullable', 'in:draft,pending,active,inactive,expired'],
            'total_inventory'           => ['nullable', 'integer', 'min:0'],
            'offer_types'               => ['nullable', 'array'],
            'offer_types.*.original_price' => ['nullable', 'numeric', 'min:0'],
            'offer_types.*.currency_code'  => ['nullable', 'string', 'size:3'],
            'offer_types.*.params'         => ['nullable', 'array'],
        ];
    }
}
