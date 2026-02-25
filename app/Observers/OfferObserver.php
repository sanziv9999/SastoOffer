<?php

namespace App\Observers;

use App\Models\Offer;
use Illuminate\Support\Facades\Log;

class OfferObserver
{
    /**
     * Handle the offer "saving" event (runs before create & update)
     */
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

        // Reset derived fields first
        $offer->offer_price = $offer->original_price;
        $offer->discount_percent = 0;
        $offer->discount_amount = 0;
        $offer->savings_amount = 0;
        $offer->savings_percent = 0;

        // Calculate based on offer type
        match ($typeName) {
            'percentage_discount'    => $this->calculatePercentageDiscount($offer),
            'fixed_amount_discount'  => $this->calculateFixedAmountDiscount($offer),
            'bogo'                   => $this->calculateBogo($offer),
            'buy_x_get_y'            => $this->calculateBuyXGetY($offer),
            'flash_sale'             => $this->calculateFlashSale($offer),
            'free_shipping'          => $this->calculateFreeShipping($offer),
            'cashback'               => $this->calculateCashback($offer),
            'combo_bundle'           => $this->calculateComboBundle($offer),
            default                  => $this->setDefaults($offer),
        };

        // Always calculate savings (after main calculation)
        $this->calculateSavings($offer);
    }

    /**
     * Set default values when calculation can't be done
     */
    private function setDefaults(Offer $offer): void
    {
        $offer->offer_price = $offer->original_price ?? 0;
        $offer->discount_percent = 0;
        $offer->discount_amount = 0;
        $offer->savings_amount = 0;
        $offer->savings_percent = 0;
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

    private function calculatePercentageDiscount(Offer $offer): void
    {
        $rules = $offer->offer_validation_rules ?? [];

        $percent = $rules['discount_percent'] ?? 0;

        $offer->discount_percent = $percent;
        $offer->offer_price = $offer->original_price * (1 - $percent / 100);
        $offer->discount_amount = $offer->original_price - $offer->offer_price;
    }

    private function calculateFixedAmountDiscount(Offer $offer): void
    {
        $rules = $offer->offer_validation_rules ?? [];

        $amount = $rules['discount_amount'] ?? 0;

        $offer->discount_amount = $amount;
        $offer->offer_price = max(0, $offer->original_price - $amount);
        $offer->discount_percent = $offer->original_price > 0 
            ? ($amount / $offer->original_price) * 100 
            : 0;
    }

    private function calculateBogo(Offer $offer): void
    {
        // Typical BOGO: buy 1 get 1 free → 50% off
        // You can make it more dynamic with rules
        $offer->offer_price = $offer->original_price / 2;
        $offer->discount_percent = 50;
        $offer->discount_amount = $offer->original_price / 2;
    }

    private function calculateBuyXGetY(Offer $offer): void
    {
        $rules = $offer->offer_validation_rules ?? [];

        $buyQty = $rules['buy_quantity'] ?? 1;
        $getQty = $rules['get_quantity'] ?? 1;
        $getDiscount = $rules['get_discount_percent'] ?? 100; // 100% = free

        // Simple average price calculation
        $totalItems = $buyQty + $getQty;
        $paidPrice = $offer->original_price * $buyQty;
        $freePrice = $offer->original_price * $getQty * (1 - $getDiscount / 100);

        $offer->offer_price = ($paidPrice + $freePrice) / $totalItems;
        $offer->discount_percent = 100 * ($getQty / $totalItems) * ($getDiscount / 100);
    }

    private function calculateFlashSale(Offer $offer): void
    {
        // Flash sale often uses same logic as percentage but with time pressure
        // Reuse percentage logic or apply extra discount
        $this->calculatePercentageDiscount($offer);
    }

    private function calculateFreeShipping(Offer $offer): void
    {
        // Price unchanged, shipping = 0 (handled elsewhere)
        $offer->offer_price = $offer->original_price;
    }

    private function calculateCashback(Offer $offer): void
    {
        // Cashback usually doesn't reduce price upfront
        $offer->offer_price = $offer->original_price;
        // Cashback amount can be stored separately if needed
    }

    private function calculateComboBundle(Offer $offer): void
    {
        // Combo: fixed bundle price
        $rules = $offer->offer_validation_rules ?? [];
        $bundlePrice = $rules['bundle_price'] ?? $offer->original_price * 0.8; // example 20% off
        $offer->offer_price = $bundlePrice;
        $offer->discount_percent = $offer->original_price > 0 
            ? (($offer->original_price - $bundlePrice) / $offer->original_price) * 100 
            : 0;
    }
}