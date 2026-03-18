<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Deal;
use App\Models\OfferType;
use App\Services\DealOfferService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendorId = 1; // Hardcoded as requested - only one vendor exists now

        // Fetch required data
        $categories = Category::whereNotNull('parent_id')->inRandomOrder()->limit(10)->get();
        $offerTypesByName = OfferType::pluck('id', 'name')->toArray();

        if ($categories->isEmpty()) {
            $this->command->warn("No sub-categories found in categories table (rows with parent_id). Seed categories first.");
            return;
        }

        if (empty($offerTypesByName)) {
            $this->command->warn("No OfferType records found. Run OfferTypeSeeder first.");
            return;
        }

        $service = app(DealOfferService::class);

        $dealsData = [
            [
                'title' => 'Dashain Special: 25% Off on All Smartphones',
                'short_description' => 'Dashainमा सबै स्मार्टफोनमा २५% छुट',
                'long_description' => 'Samsung, iPhone, Xiaomi, OnePlus सबै ब्रान्डमा ठूलो छुट। स्टक सीमित छ।',
                'highlights' => ['Dashain उपहार', 'EMI उपलब्ध', '१ वर्ष वारेन्टी'],
                'total_inventory' => 45,
                'is_featured' => true,
                'offer_type_name' => 'percentage_discount',
                'pivot' => [
                    'original_price' => 32000.00,
                    'params' => ['discount_percent' => 25],
                    'currency_code' => 'NPR',
                    'status' => 'active',
                    'starts_at' => now(),
                    'ends_at' => now()->addDays(10),
                ],
            ],

            [
                'title' => 'Flat Rs 500 Off on Food Orders Above Rs 1500',
                'short_description' => '१५००+ अर्डरमा ५०० छुट',
                'long_description' => 'म:म:, चाउचाउ, थकाली सेट, बर्गर, पिज्जा सबैमा लागू। काठमाडौं भित्र मात्र।',
                'highlights' => ['फ्रि कोल्ड ड्रिंक', 'होम डेलिभरी'],
                'total_inventory' => 120,
                'offer_type_name' => 'fixed_amount_discount',
                'pivot' => [
                    'original_price' => 1800.00,
                    'params' => ['discount_amount' => 500, 'min_order_value' => 1500],
                    'currency_code' => 'NPR',
                    'status' => 'active',
                    'starts_at' => now(),
                    'ends_at' => now()->addDays(5),
                ],
            ],

            [
                'title' => 'Tihar Lights: Buy 1 Get 1 Free on Decorative Lights',
                'short_description' => 'बत्ती किन्नुहोस्, दोस्रो फ्री',
                'long_description' => 'Tihar lights, diyo, bandar jhula, rangoli सबैमा BOGO अफर।',
                'highlights' => ['LED lights', 'कलरफुल डिजाइन', 'लामो आयु'],
                'total_inventory' => 200,
                'offer_type_name' => 'bogo',
                'pivot' => [
                    'original_price' => 1200.00,
                    'params' => [
                        'buy_quantity' => 1,
                        'get_quantity' => 1,
                        'get_discount_percent' => 100,
                    ],
                    'currency_code' => 'NPR',
                    'status' => 'active',
                    'starts_at' => now(),
                    'ends_at' => now()->addDays(12),
                ],
            ],

            [
                'title' => 'Flash Sale: 40% Off Laptops & Tablets (48 Hours Only)',
                'short_description' => '४८ घण्टा मात्र - ४०% छुट',
                'long_description' => 'Dell, HP, Lenovo, Acer ल्यापटप र ट्याब्लेटमा ठूलो छुट। स्टक सीमित।',
                'highlights' => ['Core i5/i7', '१६GB RAM', 'SSD'],
                'total_inventory' => 18,
                'is_featured' => true,
                'offer_type_name' => 'flash_sale',
                'pivot' => [
                    'original_price' => 95000.00,
                    'params' => ['discount_percent' => 40],
                    'currency_code' => 'NPR',
                    'status' => 'active',
                    'starts_at' => now(),
                    'ends_at' => now()->addHours(48),
                ],
            ],

            [
                'title' => 'Free Delivery on Grocery Orders Above Rs 2000',
                'short_description' => '२०००+ अर्डरमा फ्री डेलिभरी',
                'long_description' => 'तरकारी, फलफूल, दाल, चामल, तेल सबैमा फ्री होम डेलिभरी। काठमाडौं उपत्यका भित्र मात्र।',
                'highlights' => ['ताजा सामान', 'सुविधाजनक समय'],
                'total_inventory' => 300,
                'offer_type_name' => 'free_shipping',
                'pivot' => [
                    'original_price' => 2500.00,
                    'params' => ['min_order_value' => 2000],
                    'currency_code' => 'NPR',
                    'status' => 'active',
                    'starts_at' => now(),
                    'ends_at' => now()->addDays(15),
                ],
            ],
        ];

        $created = 0;

        foreach ($dealsData as $data) {
            $subCategory = $categories->random();

            // Create or update deal (core fields only)
            $isFeatured = (bool) ($data['is_featured'] ?? false);
            $deal = Deal::updateOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'vendor_id' => $vendorId,
                    'category_id' => $subCategory->id,
                    'title' => $data['title'],
                    'slug' => Str::slug($data['title']),
                    'base_price' => $data['pivot']['original_price'] ?? null,
                    'short_description' => $data['short_description'],
                    'long_description' => $data['long_description'],
                    'highlights' => $data['highlights'],
                    'total_inventory' => $data['total_inventory'],
                    'status' => 'active',
                ]
            );

            // Attach offer via service (calculates prices automatically)
            $offerTypeId = $offerTypesByName[$data['offer_type_name']] ?? null;

            if ($offerTypeId) {
                $offerType = OfferType::find($offerTypeId);
                $pivotData = $data['pivot'];
                if ($isFeatured) {
                    $pivotData['display_type_names'] = ['featured'];
                }
                $service->attachOfferToDeal($deal, $offerType, $pivotData);
                $created++;
                $this->command->info("Created: {$deal->title} (with {$data['offer_type_name']})");
            } else {
                $this->command->warn("Skipped {$data['title']} - offer type '{$data['offer_type_name']}' not found");
            }
        }

        $this->command->info("\nSeeded {$created} deals successfully for vendor ID 1.");
    }
}