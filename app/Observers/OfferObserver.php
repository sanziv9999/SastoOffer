<?php

namespace App\Observers;

use App\Models\Offer;

class OfferObserver
{
    public function saving(Offer $offer): void
    {
        // Skip if no original price or offer type
        if (!$offer->original_price || !$offer->offer_type_id) {
            $this->setDefaults($offer);
            return;
        }

        $type = $offer->offerType;

        if (!$type) {
            $this->setDefaults($offer);
            return;
        }

        $typeName = $type->name;

        // Reset derived fields
        $offer->offer_price = $offer->original_price;
        $offer->discount_percent = 0;
        $offer->discount_amount = 0;

        // Read actual values from input (these come from form, not from rules JSON)
        $input = $offer->getAttributes(); // or $offer->getDirty() for changed fields

        // Calculate based on type
        match ($typeName) {
            'percentage_discount' => $this->calculatePercentageDiscount($offer, $input),
            'fixed_amount_discount' => $this->calculateFixedAmountDiscount($offer, $input),
            'bogo' => $this->calculateBogo($offer, $input),
            'buy_x_get_y' => $this->calculateBuyXGetY($offer, $input),
            'flash_sale' => $this->calculateFlashSale($offer, $input),
            'free_shipping' => $this->calculateFreeShipping($offer, $input),
            'cashback' => $this->calculateCashback($offer, $input),
            'combo_bundle' => $this->calculateComboBundle($offer, $input),
            default => $this->setDefaults($offer),
        };

        // Always calculate savings
        $this->calculateSavings($offer);
    }

    private function setDefaults(Offer $offer): void
    {
        $offer->offer_price = $offer->original_price ?? 0;
        $offer->discount_percent = 0;
        $offer->discount_amount = 0;
    }

    private function calculateSavings(Offer $offer): void
    {
        if ($offer->original_price > 0) {
            $offer->savings_amount = $offer->original_price - $offer->offer_price;
            $offer->savings_percent = ($offer->savings_amount / $offer->original_price) * 100;
        } else {
            $offer->savings_amount = 0;
            $offer->savings_percent = 0;
        }
    }

    private function calculatePercentageDiscount(Offer $offer, array $input): void
    {
        $percent = (float) ($input['discount_percent'] ?? 0);
        $offer->discount_percent = $percent;
        $offer->offer_price = $offer->original_price * (1 - $percent / 100);
        $offer->discount_amount = $offer->original_price - $offer->offer_price;
    }

    private function calculateFixedAmountDiscount(Offer $offer, array $input): void
    {
        $amount = (float) ($input['discount_amount'] ?? 0);
        $offer->discount_amount = $amount;
        $offer->offer_price = max(0, $offer->original_price - $amount);
        $offer->discount_percent = $offer->original_price > 0 
            ? ($amount / $offer->original_price) * 100 
            : 0;
    }

    private function calculateBogo(Offer $offer, array $rules): void
    {
        // BOGO: Buy 1 Get 1 Free = 50% off (or use rules if provided)
        $buyQty = $rules['buy_quantity'] ?? 1;
        $getQty = $rules['get_quantity'] ?? 1;
        $getDiscount = $rules['get_discount_percent'] ?? 100; // 100% = free

        $totalItems = $buyQty + $getQty;
        $paidPrice = $offer->original_price * $buyQty;
        $freePrice = $offer->original_price * $getQty * (1 - $getDiscount / 100);

        $offer->offer_price = ($paidPrice + $freePrice) / $totalItems;
        $offer->discount_percent = 100 * ($getQty / $totalItems) * ($getDiscount / 100);
        $offer->discount_amount = $offer->original_price - $offer->offer_price;
    }

    private function calculateBuyXGetY(Offer $offer, array $rules): void
    {
        // Similar to BOGO but more flexible
        $buyQty = $rules['buy_quantity'] ?? 1;
        $getQty = $rules['get_quantity'] ?? 1;
        $getDiscount = $rules['get_discount_percent'] ?? 100;

        $totalItems = $buyQty + $getQty;
        $paidPrice = $offer->original_price * $buyQty;
        $freePrice = $offer->original_price * $getQty * (1 - $getDiscount / 100);

        $offer->offer_price = ($paidPrice + $freePrice) / $totalItems;
        $offer->discount_percent = 100 * ($getQty / $totalItems) * ($getDiscount / 100);
        $offer->discount_amount = $offer->original_price - $offer->offer_price;
    }

    private function calculateFlashSale(Offer $offer, array $rules): void
    {
        // Flash sale: usually higher % discount
        $percent = $rules['discount_percent'] ?? 40; // default 40% if not in rules
        $offer->discount_percent = $percent;
        $offer->offer_price = $offer->original_price * (1 - $percent / 100);
        $offer->discount_amount = $offer->original_price - $offer->offer_price;
    }

    private function calculateFreeShipping(Offer $offer, array $rules): void
    {
        // Price unchanged, but you can add min_order_value check if needed
        $offer->offer_price = $offer->original_price;
        $offer->discount_amount = 0;
        $offer->discount_percent = 0;
    }

    private function calculateCashback(Offer $offer, array $rules): void
    {
        // Cashback doesn't reduce price upfront
        $offer->offer_price = $offer->original_price;
        $offer->discount_amount = 0;
        $offer->discount_percent = 0;
        // Cashback value can be stored in rules or separate field if needed
    }

    private function calculateComboBundle(Offer $offer, array $rules): void
    {
        $bundlePrice = $rules['bundle_price'] ?? $offer->original_price * 0.8;
        $offer->offer_price = $bundlePrice;
        $offer->discount_amount = $offer->original_price - $bundlePrice;
        $offer->discount_percent = $offer->original_price > 0 
            ? ($offer->discount_amount / $offer->original_price) * 100 
            : 0;
    }
}