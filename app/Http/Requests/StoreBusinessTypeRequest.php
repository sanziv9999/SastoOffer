<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBusinessTypeRequest extends FormRequest
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
            'slug'          => ['nullable', 'string', 'max:255', 'unique:business_types,slug'],
            'description'   => ['nullable', 'string'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active'     => ['nullable', 'boolean'],
        ];
    }
}
