<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\OfferType;
use App\Models\DealOfferType;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DealOfferService
{
    /**
     * Attach a new offer type to a deal + calculate prices
     */
    public function attachOfferToDeal(
        Deal $deal,
        OfferType $offerType,
        array $data
    ): DealOfferType {
        $this->validateAttachData($data);

        return DB::transaction(function () use ($deal, $offerType, $data) {
            $pivotAttributes = [
                'original_price'  => $data['original_price'],
                'currency_code'   => $data['currency_code'] ?? 'NPR',
                'params'          => $data['params'] ?? [],
                'status'          => $data['status'] ?? 'active',
            ];

            // Attach (many-to-many)
            $deal->offerTypes()->attach($offerType->id, $pivotAttributes);

            // Get the fresh pivot record
            $pivot = DealOfferType::where('deal_id', $deal->id)
                ->where('offer_type_id', $offerType->id)
                ->firstOrFail();

            // Run calculation logic (lives in pivot model)
            $pivot->calculatePrices($data['params'] ?? []);

            return $pivot->refresh();
        });
    }

    /**
     * Update existing offer attachment
     */
    public function updateOfferOnDeal(
        Deal $deal,
        OfferType $offerType,
        array $data
    ): ?DealOfferType {
        $pivot = DealOfferType::where('deal_id', $deal->id)
            ->where('offer_type_id', $offerType->id)
            ->first();

        if (!$pivot) {
            return null;
        }

        $pivot->update([
            'original_price' => $data['original_price']         ?? $pivot->original_price,
            'currency_code'  => $data['currency_code']          ?? $pivot->currency_code,
            'params'         => $data['params']                 ?? $pivot->params,
            'status'         => $data['status']                 ?? $pivot->status,
        ]);

        // Recalculate
        $pivot->calculatePrices($data['params'] ?? []);

        return $pivot->refresh();
    }

    /**
     * Remove offer from deal
     */
    public function removeOfferFromDeal(Deal $deal, OfferType $offerType): bool
    {
        return (bool) $deal->offerTypes()->detach($offerType->id);
    }

    /**
     * Bulk recalculate all offers on a deal (e.g. after currency change)
     */
    public function recalculateAllOffers(Deal $deal): void
    {
        $deal->offerTypes->each(function ($offerType) {
            $offerType->pivot->calculatePrices();
        });
    }

    protected function validateAttachData(array $data): void
    {
        if (empty($data['original_price']) || $data['original_price'] <= 0) {
            throw new InvalidArgumentException('Original price is required and must be positive.');
        }
        // Add more validation if needed (you can also use FormRequest)
    }
}