<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrimaryCategoryRequest extends FormRequest
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
        $primaryCategory = $this->route('primary_category');

        return [
            'name'          => ['sometimes', 'string', 'max:255'],
            'slug'          => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('categories', 'slug')->ignore($primaryCategory->id)],
            'description'   => ['nullable', 'string'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active'     => ['nullable', 'boolean'],
            'parent_id'     => ['nullable', 'exists:categories,id'],
            'image'         => ['nullable', 'image', 'max:2048'],
        ];
    }
}
