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
        $defaults = $this->ensureArray($offerType->default_values);
        $params = $this->ensureArray($data['params'] ?? []);
        $merged = array_merge($defaults, $params);
        $this->validateRequiredParams($offerType, $merged);

        return DB::transaction(function () use ($deal, $offerType, $data, $merged) {
            $pivotAttributes = [
                'original_price'  => $data['original_price'],
                'currency_code'   => $data['currency_code'] ?? 'NPR',
                'params'          => $merged,
                'status'          => $data['status'] ?? 'active',
            ];

            $deal->offerTypes()->attach($offerType->id, $pivotAttributes);

            $pivot = DealOfferType::where('deal_id', $deal->id)
                ->where('offer_type_id', $offerType->id)
                ->firstOrFail();

            $pivot->calculatePrices($merged);

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

        $defaults = $this->ensureArray($offerType->default_values);
        $pivotParams = $this->ensureArray($pivot->params);
        $inputParams = $this->ensureArray($data['params'] ?? []);
        $mergedParams = array_merge($defaults, $pivotParams, $inputParams);
        $this->validateRequiredParams($offerType, $mergedParams);

        $pivot->update([
            'original_price' => $data['original_price']         ?? $pivot->original_price,
            'currency_code'  => $data['currency_code']          ?? $pivot->currency_code,
            'params'         => $mergedParams,
            'status'         => $data['status']                 ?? $pivot->status,
        ]);

        $pivot->calculatePrices($mergedParams);

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
    }

    /**
     * Ensure merged params contain every key required by the offer type's rule.
     *
     * @throws InvalidArgumentException
     */
    protected function validateRequiredParams(OfferType $offerType, array $mergedParams): void
    {
        $required = $this->ensureArray($offerType->required_params);
        if (empty($required)) {
            return;
        }
        $missing = array_diff($required, array_keys($mergedParams));
        if ($missing !== []) {
            throw new InvalidArgumentException(
                'Missing required offer parameters: ' . implode(', ', $missing)
            );
        }
    }

    /**
     * Ensure value is array (decode JSON string if needed, for DB columns not cast).
     */
    protected function ensureArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }
}