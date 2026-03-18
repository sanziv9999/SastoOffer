<?php

namespace App\Models;

use App\Services\OfferRuleCalculator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DealOfferType extends Pivot
{
    protected $table = 'deal_offer_type';

    protected $casts = [
        'params'           => 'array',
        'original_price'   => 'decimal:2',
        'discount_percent' => 'decimal:4',
        'discount_amount'  => 'decimal:2',
        'savings_amount'   => 'decimal:2',
        'savings_percent'  => 'decimal:4',
        'final_price'      => 'decimal:2',
        'starts_at'        => 'datetime',
        'ends_at'          => 'datetime',
    ];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function offerType(): BelongsTo
    {
        return $this->belongsTo(OfferType::class);
    }

    public function displayTypes(): BelongsToMany
    {
        return $this->belongsToMany(DisplayType::class, 'deal_offer_display', 'deal_offer_type_id', 'display_as')
            ->withTimestamps();
    }

    public function calculatePrices(array $override = []): self
    {
        $this->loadMissing('offerType');

        if (!$this->offerType) {
            // Can't calculate without offer type definition; keep final price as base.
            $this->final_price = (float) ($this->original_price ?? 0);
            $this->saveQuietly();
            return $this;
        }

        $rule = $this->offerType?->calculation_rule ?? [];
        if (is_string($rule)) {
            $rule = json_decode($rule, true) ?: [];
        }
        $rule = is_array($rule) ? $rule : [];

        $defaults = $this->ensureArray($this->offerType->default_values ?? null);
        $pivotParams = $this->ensureArray($this->params ?? null);
        $override = $this->ensureArray($override);

        $params = array_merge($defaults, $pivotParams, $override);

        $base = (float) ($this->original_price ?? 0);

        // Approach A: try dynamic formula first (formula_final_price or formula RHS)
        $calculator = app(OfferRuleCalculator::class);
        $computedFinal = $calculator->evaluateFinalPrice($base, $params, $rule);

        if ($computedFinal !== null) {
            $this->applyFormulaResult($base, $computedFinal, $params);
            $this->saveQuietly();
            return $this;
        }

        // Fallback: type-based switch (no formula or evaluation failed)
        if (empty($rule['type'])) {
            $this->final_price = $base;
            $this->saveQuietly();
            return $this;
        }

        switch (strtolower($rule['type'])) {
            case 'percentage':
                $pct = (float) ($params['discount_percent'] ?? 0);
                $disc = $base * ($pct / 100);
                $this->discount_percent = $pct;
                $this->discount_amount  = round($disc, 2);
                $this->final_price      = round($base - $disc, 2);
                $this->savings_amount   = $this->discount_amount;
                $this->savings_percent  = $pct;
                break;

            case 'fixed':
                $amt = (float) ($params['discount_amount'] ?? 0);
                $disc = min($amt, $base);
                $this->discount_amount  = round($disc, 2);
                $this->final_price      = round($base - $disc, 2);
                $this->savings_amount   = $this->discount_amount;
                $this->savings_percent  = $base > 0 ? round(($disc / $base) * 100, 4) : 0;
                break;

            default:
                $this->final_price = $base;
        }

        $this->saveQuietly();
        return $this;
    }

    /**
     * Set pivot fields from a formula-computed final price (and derive discount/savings).
     */
    protected function applyFormulaResult(float $originalPrice, float $finalPrice, array $params): void
    {
        $finalPrice = round(max(0, $finalPrice), 2);
        $discountAmount = round(max(0, $originalPrice - $finalPrice), 2);

        $this->final_price = $finalPrice;
        $this->discount_amount = $discountAmount;
        $this->savings_amount = $discountAmount;
        $this->savings_percent = $originalPrice > 0
            ? round(($discountAmount / $originalPrice) * 100, 4)
            : 0;
        $this->discount_percent = $params['discount_percent'] ?? $this->savings_percent;
    }

    /**
     * Ensure value is array (decode JSON string if needed).
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