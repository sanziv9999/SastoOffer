<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
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
            'province'       => ['sometimes', 'string'],
            'district'       => ['sometimes', 'string', 'max:100'],
            'municipality'   => ['nullable', 'string', 'max:100'],
            'ward_no'        => ['nullable', 'string', 'max:20'],
            'tole'           => ['sometimes', 'string', 'max:255'],
            'latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'      => ['nullable', 'numeric', 'between:-180,180'],
            'is_default'     => ['nullable', 'boolean'],
            'label'          => ['nullable', 'string', 'in:Home,Office,Work,Pickup Point,Friend/Family,Other,Warehouse'],
        ];
    }
}
