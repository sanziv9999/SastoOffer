<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    ];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function offerType(): BelongsTo
    {
        return $this->belongsTo(OfferType::class);
    }

    public function calculatePrices(array $override = []): self
    {
        $rule = $this->offerType?->calculation_rule ?? [];

        if (empty($rule['type'])) {
            $this->final_price = $this->original_price ?? 0;
            $this->saveQuietly();
            return $this;
        }

        $params = array_merge(
            $this->offerType->default_values ?? [],
            $this->params ?? [],
            $override
        );

        $base = (float) ($this->original_price ?? 0);

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
}