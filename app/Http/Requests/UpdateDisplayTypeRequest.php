<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDisplayTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $displayType = $this->route('display_type');

        return [
            'name' => ['sometimes', 'string', 'max:100', Rule::unique('display_types', 'name')->ignore($displayType->id)],
        ];
    }
}

