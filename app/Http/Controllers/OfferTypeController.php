<?php

namespace App\Http\Controllers;

use App\Models\OfferType;
use App\Http\Requests\StoreOfferTypeRequest;
use App\Http\Requests\UpdateOfferTypeRequest;
use Illuminate\Http\Request;

class OfferTypeController extends Controller
{
    public function index(Request $request)
    {
        $offerTypes = OfferType::query()
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('name')
            ->paginate(15);

        return view('offer-types.index', compact('offerTypes'));
    }

    public function create()
    {
        return view('offer-types.create');
    }

    public function store(StoreOfferTypeRequest $request)
    {
        $data = $this->buildOfferTypeData($request);
        $data['is_active'] = $request->boolean('is_active', true);

        OfferType::create($data);

        return redirect()->route('offer-types.index')->with('success', 'Offer type created successfully.');
    }

    public function show(OfferType $offerType)
    {
        return view('offer-types.show', compact('offerType'));
    }

    public function edit(OfferType $offerType)
    {
        $formData = $this->getOfferTypeFormData($offerType);

        return view('offer-types.edit', compact('offerType', 'formData'));
    }

    /**
     * Build form-ready values from an OfferType (handles array cast or raw JSON from DB).
     */
    protected function getOfferTypeFormData(OfferType $offerType): array
    {
        $rule = $offerType->calculation_rule;
        if (is_string($rule)) {
            $rule = json_decode($rule, true) ?: [];
        }
        $rule = is_array($rule) ? $rule : [];

        $formula = $rule['formula_final_price'] ?? $rule['formula'] ?? '';
        if (is_string($formula) && str_contains($formula, '=')) {
            $formula = trim(explode('=', $formula, 2)[1] ?? '');
        }

        $requiredParams = $offerType->required_params;
        if (is_string($requiredParams)) {
            $requiredParams = json_decode($requiredParams, true);
        }
        $requiredParamsStr = is_array($requiredParams) ? implode(', ', $requiredParams) : '';

        $defaultValues = $offerType->default_values;
        if (is_string($defaultValues)) {
            $defaultValues = json_decode($defaultValues, true);
        }
        $defaultValuesJson = is_array($defaultValues) && $defaultValues !== []
            ? json_encode($defaultValues, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            : '';

        return [
            'formula_final_price'  => $formula,
            'rule_type'            => $rule['type'] ?? '',
            'display_template'     => $rule['display'] ?? '',
            'required_params_str'  => $requiredParamsStr,
            'default_values_json'  => $defaultValuesJson,
        ];
    }

    public function update(UpdateOfferTypeRequest $request, OfferType $offerType)
    {
        $data = $this->buildOfferTypeData($request);
        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        $offerType->update($data);

        return redirect()->route('offer-types.show', $offerType)->with('success', 'Offer type updated successfully.');
    }

    protected function buildOfferTypeData(StoreOfferTypeRequest|UpdateOfferTypeRequest $request): array
    {
        $data = $request->validated();

        $calculationRule = [];
        if ($request->filled('formula_final_price')) {
            $calculationRule['formula_final_price'] = trim($request->formula_final_price);
        }
        if ($request->filled('rule_type')) {
            $calculationRule['type'] = trim($request->rule_type);
        }
        if ($request->filled('display_template')) {
            $calculationRule['display'] = trim($request->display_template);
        }
        $data['calculation_rule'] = $calculationRule ?: null;

        if ($request->filled('required_params_str')) {
            $params = array_map('trim', preg_split('/[\s,]+/', $request->required_params_str, -1, PREG_SPLIT_NO_EMPTY));
            $data['required_params'] = array_values(array_unique($params));
        } else {
            $data['required_params'] = [];
        }

        if ($request->filled('default_values_json')) {
            $data['default_values'] = json_decode($request->default_values_json, true) ?: [];
        } else {
            $data['default_values'] = [];
        }

        unset(
            $data['formula_final_price'],
            $data['rule_type'],
            $data['display_template'],
            $data['required_params_str'],
            $data['default_values_json']
        );

        return $data;
    }

    public function destroy(OfferType $offerType)
    {
        $offerType->delete();

        return redirect()->route('offer-types.index')->with('success', 'Offer type deleted successfully.');
    }
}
