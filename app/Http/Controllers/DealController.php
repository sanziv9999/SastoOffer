<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealOfferType;
use App\Models\OfferType;
use App\Models\VendorProfile;
use App\Models\BusinessSubCategory;
use App\Services\DealOfferService;
use App\Http\Requests\StoreDealRequest;
use App\Http\Requests\UpdateDealRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DealController extends Controller
{
    public function index(Request $request)
    {
        $deals = Deal::query()
            ->with(['vendor', 'subCategory'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('vendor_id'), fn ($q) => $q->where('vendor_id', $request->vendor_id))
            ->latest()
            ->paginate(15);

        return view('deals.index', compact('deals'));
    }

    public function create()
    {
        $vendors = VendorProfile::orderBy('business_name')->get();
        $subCategories = BusinessSubCategory::active()->orderBy('display_order')->get();
        $offerTypes = OfferType::where('is_active', true)->orderBy('display_name')->get();

        return view('deals.create', compact('vendors', 'subCategories', 'offerTypes'));
    }

    public function store(StoreDealRequest $request)
    {
        $data = $request->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }
        $data['status'] = $data['status'] ?? 'draft';
        $data['is_featured'] = $request->boolean('is_featured');

        $offerTypesInput = $request->input('offer_types', []);
        unset($data['offer_types']);

        $deal = Deal::create($data);

        $this->syncDealOffers($deal, $offerTypesInput);

        return redirect()->route('deals.index')->with('success', 'Deal created successfully.');
    }

    public function show(Deal $deal)
    {
        $deal->load(['vendor', 'subCategory', 'offerTypes', 'images']);

        return view('deals.show', compact('deal'));
    }

    public function edit(Deal $deal)
    {
        $vendors = VendorProfile::orderBy('business_name')->get();
        $subCategories = BusinessSubCategory::active()->orderBy('display_order')->get();
        $offerTypes = OfferType::where('is_active', true)->orderBy('display_name')->get();
        $deal->load(['offerTypes', 'images']);

        return view('deals.edit', compact('deal', 'vendors', 'subCategories', 'offerTypes'));
    }

    public function update(UpdateDealRequest $request, Deal $deal)
    {
        $data = $request->validated();
        if (array_key_exists('title', $data) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }
        if ($request->has('is_featured')) {
            $data['is_featured'] = $request->boolean('is_featured');
        }

        $offerTypesInput = $request->input('offer_types', []);
        unset($data['offer_types']);

        $deal->update($data);

        $this->syncDealOffers($deal, $offerTypesInput);

        return redirect()->route('deals.show', $deal)->with('success', 'Deal updated successfully.');
    }

    protected function syncDealOffers(Deal $deal, array $offerTypesInput): void
    {
        $service = app(DealOfferService::class);
        $submittedIds = [];
        foreach ($offerTypesInput as $offerTypeId => $payload) {
            $offerTypeId = (int) $offerTypeId;
            $originalPrice = isset($payload['original_price']) ? (float) $payload['original_price'] : 0;
            if ($originalPrice <= 0) {
                continue;
            }
            $offerType = OfferType::find($offerTypeId);
            if (! $offerType) {
                continue;
            }
            $params = $payload['params'] ?? [];
            $params = is_array($params) ? $params : [];
            $params = array_filter($params, fn ($v) => $v !== '' && $v !== null);
            $data = [
                'original_price' => $originalPrice,
                'currency_code'  => $payload['currency_code'] ?? 'NPR',
                'params'         => $params,
                'status'         => $payload['status'] ?? 'active',
            ];
            $pivot = DealOfferType::where('deal_id', $deal->id)->where('offer_type_id', $offerTypeId)->first();
            if ($pivot) {
                $service->updateOfferOnDeal($deal, $offerType, $data);
            } else {
                $service->attachOfferToDeal($deal, $offerType, $data);
            }
            $submittedIds[] = $offerTypeId;
        }
        $deal->load('offerTypes');
        foreach ($deal->offerTypes as $attached) {
            if (! in_array($attached->id, $submittedIds, true)) {
                $service->removeOfferFromDeal($deal, $attached);
            }
        }
    }

    public function destroy(Deal $deal)
    {
        $deal->delete();

        return redirect()->route('deals.index')->with('success', 'Deal deleted successfully.');
    }
}
