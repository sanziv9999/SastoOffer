<?php

namespace Database\Seeders;

use App\Models\BusinessSubCategory;
use App\Models\Offer;
use App\Models\OfferType;
use App\Models\VendorProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first vendor profile (only one exists now)
        $vendorProfile = VendorProfile::first();

        if (!$vendorProfile) {
            $this->command->warn('OfferSeeder skipped: No vendor profiles found.');
            $this->command->warn('Run PermissionSeeder first to create at least one vendor.');
            return;
        }

        $vendorId = $vendorProfile->id; // offers.vendor_id points to users.id

        // Fetch sub-categories and offer types
        $subCategories = BusinessSubCategory::inRandomOrder()->limit(10)->get();
        $offerTypes = OfferType::pluck('id', 'name')->toArray();

        if ($subCategories->isEmpty() || count($offerTypes) === 0) {
            $this->command->warn('OfferSeeder skipped: Missing sub-categories or offer types.');
            $this->command->warn('Run BusinessSubCategorySeeder and OfferTypeSeeder first.');
            return;
        }

        $examples = [

            // 1. Percentage discount - Mobile Shop
            [
                'vendor_id' => $vendorId,
                'business_sub_category_id' => $subCategories[0]->id ?? null,
                'offer_type_id' => $offerTypes['percentage_discount'] ?? null,
                'title' => '20% Off on All Smartphones',
                'slug' => Str::slug('20% Off on All Smartphones'),
                'short_description' => 'Get 20% discount on latest smartphones',
                'long_description' => 'Limited time offer on all brands - Samsung, iPhone, Xiaomi, etc.',
                'highlights' => json_encode(['Free screen protector', '1 year warranty', 'Cash on delivery']),
                'original_price' => 25000.00,
                'currency_code' => 'NPR',
                'total_inventory' => 50,
                'min_per_customer' => 1,
                'max_per_customer' => 2,
                'starts_at' => now(),
                'ends_at' => now()->addDays(7),
                'voucher_valid_days' => 7,
                'is_featured' => true,
                'offer_validation_rules' => json_encode([
                    'discount_percent' => 'required|numeric|between:10,30',
                    'original_price'   => 'required|numeric|min:5000',
                ]),
            ],

            // 2. Fixed amount discount - Restaurant
            [
                'vendor_id' => $vendorId,
                'business_sub_category_id' => $subCategories->random()->id ?? null,
                'offer_type_id' => $offerTypes['fixed_amount_discount'] ?? null,
                'title' => 'Rs 300 Off on Orders Above Rs 1000',
                'slug' => Str::slug('Rs 300 Off on Orders Above Rs 1000'),
                'short_description' => 'Flat Rs 300 discount on food orders',
                'long_description' => 'Valid on all items - momo, chowmein, thakali set, pizza',
                'highlights' => json_encode(['Free cold drink', 'Home delivery available']),
                'original_price' => 1500.00,
                'currency_code' => 'NPR',
                'total_inventory' => 100,
                'min_per_customer' => 1,
                'max_per_customer' => 5,
                'starts_at' => now(),
                'ends_at' => now()->addDays(3),
                'voucher_valid_days' => 3,
                'offer_validation_rules' => json_encode([
                    'discount_amount' => 'required|numeric|min:100|max:500',
                    'min_order_value' => 'required|numeric|min:1000',
                ]),
            ],

            // 3. BOGO - Fashion Store
            [
                'vendor_id' => $vendorId,
                'business_sub_category_id' => $subCategories->random()->id ?? null,
                'offer_type_id' => $offerTypes['bogo'] ?? null,
                'title' => 'Buy 1 Get 1 Free on Kurtis',
                'slug' => Str::slug('Buy 1 Get 1 Free on Kurtis'),
                'short_description' => 'BOGO offer on selected kurtis & tops',
                'long_description' => 'Buy any kurti and get second one free (same or lower price)',
                'highlights' => json_encode(['Latest collection', 'Sizes S to XXL']),
                'original_price' => 1800.00,
                'currency_code' => 'NPR',
                'total_inventory' => 80,
                'min_per_customer' => 2,
                'max_per_customer' => 4,
                'starts_at' => now(),
                'ends_at' => now()->addDays(10),
                'voucher_valid_days' => 10,
                'offer_validation_rules' => json_encode([
                    'buy_quantity' => 'required|integer|min:1',
                    'get_quantity' => 'required|integer|min:1',
                ]),
            ],

            // 4. Flash Sale - Electronics
            [
                'vendor_id' => $vendorId,
                'business_sub_category_id' => $subCategories->random()->id ?? null,
                'offer_type_id' => $offerTypes['flash_sale'] ?? null,
                'title' => 'Flash Sale: 40% Off Laptops (24 Hours Only)',
                'slug' => Str::slug('Flash Sale 40% Off Laptops'),
                'short_description' => 'Limited stock - hurry!',
                'long_description' => 'Valid only for 24 hours - selected laptop models',
                'highlights' => json_encode(['Dell, HP, Lenovo', 'Up to 40% off']),
                'original_price' => 85000.00,
                'currency_code' => 'NPR',
                'total_inventory' => 15,
                'min_per_customer' => 1,
                'max_per_customer' => 1,
                'starts_at' => now(),
                'ends_at' => now()->addHours(24),
                'voucher_valid_days' => 1,
                'is_featured' => true,
                'offer_validation_rules' => json_encode([
                    'discount_percent' => 'required|numeric|between:30,50',
                    'max_per_customer' => 'required|integer|max:1',
                ]),
            ],

            // 5. Free Shipping - Grocery
            [
                'vendor_id' => $vendorId,
                'business_sub_category_id' => $subCategories->random()->id ?? null,
                'offer_type_id' => $offerTypes['free_shipping'] ?? null,
                'title' => 'Free Delivery on Orders Above Rs 1500',
                'slug' => Str::slug('Free Delivery Above Rs 1500'),
                'short_description' => 'No delivery charge on orders Rs 1500+',
                'long_description' => 'Applicable in Kathmandu valley only',
                'highlights' => json_encode(['Grocery, vegetables, daily essentials']),
                'original_price' => 2000.00,
                'currency_code' => 'NPR',
                'total_inventory' => 200,
                'min_per_customer' => 1,
                'offer_validation_rules' => json_encode([
                    'min_order_value' => 'required|numeric|min:1500',
                ]),
            ],
        ];

        $createdCount = 0;

        foreach ($examples as $data) {
            if (!$data['vendor_id'] || !$data['business_sub_category_id'] || !$data['offer_type_id']) {
                continue;
            }

            Offer::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            $createdCount++;
        }

        $this->command->info("Successfully seeded $createdCount offer examples using the single vendor profile.");
    }
}