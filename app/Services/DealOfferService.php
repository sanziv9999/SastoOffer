<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\DisplayType;
use App\Models\OfferType;
use App\Models\DealOfferType;

class DealOfferService
{
    /**
     * Attach an offer type to a deal with specific parameters.
     */
    public function attachOfferToDeal(Deal $deal, OfferType $offerType, array $data): DealOfferType
    {
        $pivotData = [
            'original_price' => $data['original_price'] ?? 0,
            'currency_code'  => $data['currency_code'] ?? 'NPR',
            'params'         => $data['params'] ?? [],
            'status'         => $data['status'] ?? 'active',
            'starts_at'      => $data['starts_at'] ?? null,
            'ends_at'        => $data['ends_at'] ?? null,
        ];

        $deal->offerTypes()->attach($offerType->id, $pivotData);
        
        $pivot = DealOfferType::with('offerType')->where('deal_id', $deal->id)
            ->where('offer_type_id', $offerType->id)
            ->first();

        if ($pivot) {
            $pivot->calculatePrices();
            $this->syncDisplayTypes($pivot, $data['display_type_names'] ?? ($data['display_as'] ?? null));
        }

        return $pivot;
    }

    /**
     * Update an existing offer on a deal.
     */
    public function updateOfferOnDeal(Deal $deal, OfferType $offerType, array $data): DealOfferType
    {
        $pivot = DealOfferType::with('offerType')->where('deal_id', $deal->id)
            ->where('offer_type_id', $offerType->id)
            ->first();

        if ($pivot) {
            $pivot->update([
                'original_price' => $data['original_price'] ?? $pivot->original_price,
                'currency_code'  => $data['currency_code'] ?? $pivot->currency_code,
                'params'         => $data['params'] ?? $pivot->params,
                'status'         => $data['status'] ?? $pivot->status,
                'starts_at'      => $data['starts_at'] ?? $pivot->starts_at,
                'ends_at'        => $data['ends_at'] ?? $pivot->ends_at,
            ]);
            if (array_key_exists('display_type_names', $data) || array_key_exists('display_as', $data)) {
                $this->syncDisplayTypes($pivot, $data['display_type_names'] ?? ($data['display_as'] ?? null));
            }
            $pivot->calculatePrices();
        }

        return $pivot;
    }

    /**
     * Remove an offer from a deal.
     */
    public function removeOfferFromDeal(Deal $deal, OfferType $offerType): void
    {
        $deal->offerTypes()->detach($offerType->id);
    }

    /**
     * @param array<string>|string|null $names
     */
    protected function syncDisplayTypes(DealOfferType $pivot, array|string|null $names): void
    {
        if ($names === null) {
            return;
        }

        $resolvedNames = is_array($names) ? $names : [$names];
        $resolvedNames = array_values(array_unique(array_filter(array_map(
            fn ($n) => is_string($n) ? trim($n) : '',
            $resolvedNames
        ))));

        if (empty($resolvedNames)) {
            $pivot->displayTypes()->sync([]);
            return;
        }

        $ids = [];
        foreach ($resolvedNames as $name) {
            $displayType = DisplayType::firstOrCreate(['name' => $name]);
            $ids[] = $displayType->id;
        }

        $pivot->displayTypes()->sync($ids);
    }
}
