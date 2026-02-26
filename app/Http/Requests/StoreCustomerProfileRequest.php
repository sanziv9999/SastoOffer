<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'             => ['required', 'exists:users,id', 'unique:customer_profiles,user_id'],
            'full_name'           => ['nullable', 'string', 'max:255'],
            'profile_pic'         => ['nullable', 'string', 'max:255'],
            'date_of_birth'       => ['nullable', 'date'],
            'gender'              => ['nullable', 'string', 'in:male,female,other'],
            'phone'               => ['nullable', 'string', 'max:20'],
            'default_address_id'  => ['nullable', 'exists:addresses,id'],
        ];
    }
}
