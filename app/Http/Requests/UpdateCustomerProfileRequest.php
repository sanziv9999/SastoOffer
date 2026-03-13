<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name'           => ['sometimes', 'nullable', 'string', 'max:255'],
            'date_of_birth'       => ['nullable', 'date'],
            'gender'              => ['nullable', 'string', 'in:male,female,other'],
            'phone'               => ['nullable', 'string', 'max:20'],
            'default_address_id'  => ['nullable', 'exists:addresses,id'],
        ];
    }
}
