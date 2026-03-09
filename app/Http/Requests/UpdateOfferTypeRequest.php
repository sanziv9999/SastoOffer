<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOfferTypeRequest extends FormRequest
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
        $offerType = $this->route('offer_type');

        return [
            'name'                => ['sometimes', 'string', 'max:100', \Illuminate\Validation\Rule::unique('offer_types', 'name')->ignore($offerType->id)],
            'display_name'        => ['sometimes', 'string', 'max:100'],
            'slug'                => ['nullable', 'string', 'max:120', \Illuminate\Validation\Rule::unique('offer_types', 'slug')->ignore($offerType->id)],
            'description'         => ['nullable', 'string'],
            'formula_final_price'  => ['nullable', 'string', 'max:500'],
            'rule_type'           => ['nullable', 'string', 'max:50'],
            'display_template'    => ['nullable', 'string', 'max:255'],
            'required_params_str' => ['nullable', 'string', 'max:500'],
            'default_values_json' => [
                'nullable',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === '' || $value === null) {
                        return;
                    }
                    json_decode($value, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $fail('Must be valid JSON (e.g. {"discount_percent": 10}).');
                    }
                },
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
