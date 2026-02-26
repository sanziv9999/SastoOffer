<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBusinessSubCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $subCategory = $this->route('business_sub_category');

        return [
            'business_type_id' => ['sometimes', 'exists:business_types,id'],
            'name'             => ['sometimes', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255', Rule::unique('business_sub_categories', 'slug')->ignore($subCategory->id)],
            'description'      => ['nullable', 'string'],
            'display_order'     => ['nullable', 'integer', 'min:0'],
            'is_active'        => ['nullable', 'boolean'],
        ];
    }
}
