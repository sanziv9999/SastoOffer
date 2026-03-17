<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrimaryCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'slug'          => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
            'description'   => ['nullable', 'string'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active'     => ['nullable', 'boolean'],
            // Optional parent: when set, this row becomes a sub-category
            'parent_id'     => ['nullable', 'exists:categories,id'],
            'image'         => ['nullable', 'image', 'max:2048'],
        ];
    }
}
