<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOfferTypeRequest;
use App\Http\Requests\UpdateOfferTypeRequest;
use App\Models\OfferType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OfferTypeCrudController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $offerTypes = OfferType::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('display_name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/OfferTypes/Index', [
            'offerTypes' => $offerTypes,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/OfferTypes/Create');
    }

    public function store(StoreOfferTypeRequest $request): RedirectResponse
    {
        $data = $this->buildOfferTypeData($request);
        $data['is_active'] = $request->boolean('is_active', true);

        if (empty($data['slug']) && ! empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        OfferType::create($data);

        return redirect()->route('admin.offer-types.index')->with('success', 'Offer type created.');
    }

    public function edit(OfferType $offerType): Response
    {
        $formData = $this->getOfferTypeFormData($offerType);

        return Inertia::render('admin/OfferTypes/Edit', [
            'offerType' => $offerType,
            'formData' => $formData,
        ]);
    }

    public function update(UpdateOfferTypeRequest $request, OfferType $offerType): RedirectResponse
    {
        $data = $this->buildOfferTypeData($request);
        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }
        if (array_key_exists('slug', $data) && empty($data['slug']) && ! empty($data['name'] ?? $offerType->name)) {
            $data['slug'] = Str::slug($data['name'] ?? $offerType->name);
        }

        $offerType->update($data);

        return redirect()->route('admin.offer-types.index')->with('success', 'Offer type updated.');
    }

    public function destroy(OfferType $offerType): RedirectResponse
    {
        $offerType->delete();

        return redirect()->route('admin.offer-types.index')->with('success', 'Offer type deleted.');
    }

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
            'formula_final_price' => $formula,
            'rule_type' => $rule['type'] ?? '',
            'display_template' => $rule['display'] ?? '',
            'required_params_str' => $requiredParamsStr,
            'default_values_json' => $defaultValuesJson,
        ];
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

        return $data;
    }
}

