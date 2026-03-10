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
        ];

        $deal->offerTypes()->attach($offerType->id, $pivotData);
        
        $pivot = DealOfferType::where('deal_id', $deal->id)
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
        $pivot = DealOfferType::where('deal_id', $deal->id)
            ->where('offer_type_id', $offerType->id)
            ->first();

        if ($pivot) {
            $pivot->update($data);
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
