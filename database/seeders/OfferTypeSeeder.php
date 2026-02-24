<?php

namespace Database\Seeders;

use App\Models\OfferType;
use Illuminate\Database\Seeder;

class OfferTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [

            // Percentage-based offers (most popular)
            [
                'name'         => 'percentage_discount',
                'display_name' => 'Percentage Discount',
                'slug'         => 'percentage-discount',
                'description'  => 'Discount applied as a percentage of the original price (e.g., 20% off, 50% off)',
                'is_active'    => true,
            ],

            [
                'name'         => 'fixed_amount_discount',
                'display_name' => 'Flat Amount Discount',
                'slug'         => 'flat-amount-discount',
                'description'  => 'Fixed amount deducted from the original price (e.g., Rs 200 off, Rs 500 off)',
                'is_active'    => true,
            ],

            // Buy & Get style offers
            [
                'name'         => 'bogo',
                'display_name' => 'Buy One Get One (BOGO)',
                'slug'         => 'bogo',
                'description'  => 'Buy one item and get another free or at a reduced price (Buy 1 Get 1)',
                'is_active'    => true,
            ],

            [
                'name'         => 'buy_x_get_y',
                'display_name' => 'Buy X Get Y',
                'slug'         => 'buy-x-get-y',
                'description'  => 'Buy a specific quantity and get additional items free or discounted (e.g., Buy 2 Get 1 Free)',
                'is_active'    => true,
            ],

            // Time-sensitive and special promotions
            [
                'name'         => 'flash_sale',
                'display_name' => 'Flash Sale',
                'slug'         => 'flash-sale',
                'description'  => 'Limited-time, high-discount offer (usually very short duration with limited stock)',
                'is_active'    => true,
            ],

            [
                'name'         => 'first_order',
                'display_name' => 'First Order Discount',
                'slug'         => 'first-order-discount',
                'description'  => 'Special discount for new customers on their first purchase',
                'is_active'    => true,
            ],

            // Delivery & service related
            [
                'name'         => 'free_shipping',
                'display_name' => 'Free Shipping',
                'slug'         => 'free-shipping',
                'description'  => 'No delivery charges on qualifying orders (e.g., above Rs 1000)',
                'is_active'    => true,
            ],

            [
                'name'         => 'cashback',
                'display_name' => 'Cashback Offer',
                'slug'         => 'cashback',
                'description'  => 'Get a percentage or fixed amount back to wallet/bank after purchase',
                'is_active'    => true,
            ],

            // Bundle / combo style
            [
                'name'         => 'combo_bundle',
                'display_name' => 'Combo / Bundle Offer',
                'slug'         => 'combo-bundle',
                'description'  => 'Multiple products sold together at a discounted package price',
                'is_active'    => true,
            ],

            // Fallback / custom
            [
                'name'         => 'other',
                'display_name' => 'Other / Custom Offer',
                'slug'         => 'other',
                'description'  => 'Any offer that doesn’t fit the above standard types',
                'is_active'    => true,
            ],
        ];

        foreach ($types as $type) {
            OfferType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}