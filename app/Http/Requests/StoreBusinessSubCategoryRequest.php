<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBusinessSubCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_type_id' => ['required', 'exists:business_types,id'],
            'name'             => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255', 'unique:business_sub_categories,slug'],
            'description'      => ['nullable', 'string'],
            'display_order'     => ['nullable', 'integer', 'min:0'],
            'is_active'        => ['nullable', 'boolean'],
        ];
    }
}
