<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
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
            'user_id'        => ['sometimes', 'exists:users,id'],
            'address_line'   => ['sometimes', 'string'],
            'city'           => ['sometimes', 'string', 'max:100'],
            'state_province' => ['nullable', 'string', 'max:100'],
            'postal_code'    => ['nullable', 'string', 'max:20'],
            'country_code'   => ['sometimes', 'string', 'size:2'],
            'latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'      => ['nullable', 'numeric', 'between:-180,180'],
            'timezone'       => ['nullable', 'string', 'max:50'],
            'is_default'     => ['nullable', 'boolean'],
            'label'          => ['nullable', 'string', 'in:Home,Office,Work,Pickup Point,Friend/Family,Other,Warehouse'],
        ];
    }
}
