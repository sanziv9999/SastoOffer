<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessTypeRequest extends FormRequest
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
        $businessType = $this->route('business_type');

        return [
            'name'          => ['sometimes', 'string', 'max:255'],
            'slug'          => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('business_types', 'slug')->ignore($businessType->id)],
            'description'   => ['nullable', 'string'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active'     => ['nullable', 'boolean'],
        ];
    }
}
