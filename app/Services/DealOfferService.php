<?php

namespace App\Services;

use App\Models\Deal;
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
}
