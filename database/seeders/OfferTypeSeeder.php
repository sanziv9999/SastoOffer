<?php

namespace Database\Seeders;

use App\Models\OfferType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OfferTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $offerTypes = [
            [
                'name'          => 'percentage_discount',
                'display_name'  => 'Percentage Discount',
                'slug'          => Str::slug('Percentage Discount'),
                'description'   => 'Apply a percentage discount on the original price',
                'calculation_rule' => json_encode([
                    'type'    => 'percentage',
                    'formula' => 'final_price = original_price * (1 - discount_percent / 100)',
                    'display' => '{discount_percent}% OFF',
                ]),
                'required_params' => json_encode(['discount_percent']),
                'default_values'  => json_encode(['discount_percent' => 10]),
                'is_active'       => true,
            ],

            [
                'name'          => 'fixed_amount_discount',
                'display_name'  => 'Fixed Amount Discount',
                'slug'          => Str::slug('Fixed Amount Discount'),
                'description'   => 'Subtract a fixed amount from the original price',
                'calculation_rule' => json_encode([
                    'type'    => 'fixed',
                    'formula' => 'final_price = original_price - discount_amount',
                    'display' => 'Rs {discount_amount} OFF',
                ]),
                'required_params' => json_encode(['discount_amount']),
                'default_values'  => json_encode(['discount_amount' => 200]),
                'is_active'       => true,
            ],

            [
                'name'          => 'bogo',
                'display_name'  => 'Buy One Get One',
                'slug'          => Str::slug('Buy One Get One'),
                'description'   => 'Buy X get Y free or at discount (BOGO style)',
                'calculation_rule' => json_encode([
                    'type'    => 'bogo',
                    'formula' => 'effective_price_per_item = (original_price * buy_quantity + original_price * get_quantity * (1 - get_discount_percent/100)) / (buy_quantity + get_quantity)',
                    'display' => 'Buy {buy_quantity} Get {get_quantity} at {get_discount_percent}% OFF',
                ]),
                'required_params' => json_encode([
                    'buy_quantity',
                    'get_quantity',
                    'get_discount_percent',
                ]),
                'default_values'  => json_encode([
                    'buy_quantity'       => 1,
                    'get_quantity'       => 1,
                    'get_discount_percent' => 100,
                ]),
                'is_active'       => true,
            ],

            [
                'name'          => 'flash_sale',
                'display_name'  => 'Flash Sale',
                'slug'          => Str::slug('Flash Sale'),
                'description'   => 'Time-limited deep discount (usually percentage-based)',
                'calculation_rule' => json_encode([
                    'type'    => 'percentage',
                    'formula' => 'final_price = original_price * (1 - discount_percent / 100)',
                    'display' => 'FLASH SALE: {discount_percent}% OFF (Limited Time)',
                ]),
                'required_params' => json_encode(['discount_percent']),
                'default_values'  => json_encode(['discount_percent' => 40]),
                'is_active'       => true,
            ],

            [
                'name'          => 'free_shipping',
                'display_name'  => 'Free Shipping',
                'slug'          => Str::slug('Free Shipping'),
                'description'   => 'No delivery charge (price unchanged, but validation on min order)',
                'calculation_rule' => json_encode([
                    'type'    => 'free_shipping',
                    'formula' => 'final_price = original_price', // no price change
                    'display' => 'FREE DELIVERY',
                ]),
                'required_params' => json_encode(['min_order_value']),
                'default_values'  => json_encode(['min_order_value' => 1000]),
                'is_active'       => true,
            ],

            // Optional: Add more types as needed
            [
                'name'          => 'cashback',
                'display_name'  => 'Cashback Offer',
                'slug'          => Str::slug('Cashback Offer'),
                'description'   => 'Cashback after purchase (no upfront discount)',
                'calculation_rule' => json_encode([
                    'type'    => 'cashback',
                    'formula' => 'final_price = original_price', // price unchanged
                    'display' => '{cashback_amount} Cashback',
                ]),
                'required_params' => json_encode(['cashback_amount', 'cashback_percent']),
                'default_values'  => json_encode(['cashback_percent' => 5]),
                'is_active'       => true,
            ],
        ];

        $count = 0;

        foreach ($offerTypes as $data) {
            OfferType::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
            $count++;
        }

        $this->command->info("Seeded {$count} offer types successfully.");
    }
}