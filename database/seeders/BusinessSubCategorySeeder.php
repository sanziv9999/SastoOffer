<?php

namespace Database\Seeders;

use App\Models\PrimaryCategory;
use App\Models\BusinessSubCategory;
use Illuminate\Database\Seeder;

class BusinessSubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // We'll link sub-categories to existing business types
        // Make sure you have run BusinessTypeSeeder first!

        $types = PrimaryCategory::pluck('id', 'name')->toArray();

        // If no types exist yet, you can seed some fallback types
        if (empty($types)) {
            $types = $this->seedFallbackBusinessTypes();
        }

        $subCategories = [

            // Electronics
            [
                'primary_category_id' => $types['Electronics'] ?? null,
                'name'             => 'Mobile Phones & Accessories',
                'slug'             => 'mobile-phones-accessories',
                'description'      => 'Smartphones, cases, chargers, earphones, power banks',
                'display_order'    => 1,
                'is_active'        => true,
            ],
            [
                'primary_category_id' => $types['Electronics'] ?? null,
                'name'             => 'Laptops & Computers',
                'slug'             => 'laptops-computers',
                'description'      => 'Gaming laptops, ultrabooks, desktops, monitors, keyboards',
                'display_order'    => 2,
                'is_active'        => true,
            ],
            [
                'primary_category_id' => $types['Electronics'] ?? null,
                'name'             => 'Home Appliances',
                'slug'             => 'home-appliances',
                'description'      => 'Fans, ACs, refrigerators, washing machines, microwaves',
                'display_order'    => 3,
                'is_active'        => true,
            ],

            // Fashion & Apparel
            [
                'primary_category_id' => $types['Fashion & Apparel'] ?? null,
                'name'             => "Women's Clothing",
                'slug'             => 'womens-clothing',
                'description'      => 'Kurtis, sarees, tops, dresses, ethnic wear',
                'display_order'    => 1,
                'is_active'        => true,
            ],
            [
                'primary_category_id' => $types['Fashion & Apparel'] ?? null,
                'name'             => "Men's Clothing",
                'slug'             => 'mens-clothing',
                'description'      => 'Shirts, t-shirts, jeans, jackets, traditional wear',
                'display_order'    => 2,
                'is_active'        => true,
            ],

            // Beauty & Personal Care
            [
                'primary_category_id' => $types['Beauty & Personal Care'] ?? null,
                'name'             => 'Salons & Spa Services',
                'slug'             => 'salons-spa',
                'description'      => 'Haircut, facial, massage, nail art, bridal makeup',
                'display_order'    => 1,
                'is_active'        => true,
            ],
            [
                'primary_category_id' => $types['Beauty & Personal Care'] ?? null,
                'name'             => 'Cosmetics & Skincare',
                'slug'             => 'cosmetics-skincare',
                'description'      => 'Makeup, creams, face wash, sunscreen, perfumes',
                'display_order'    => 2,
                'is_active'        => true,
            ],

            // Education & Coaching
            [
                'primary_category_id' => $types['Education & Coaching'] ?? null,
                'name'             => 'Tuition & Coaching Classes',
                'slug'             => 'tuition-coaching',
                'description'      => 'SEE, +2, entrance preparation, language classes',
                'display_order'    => 1,
                'is_active'        => true,
            ],
            [
                'primary_category_id' => $types['Education & Coaching'] ?? null,
                'name'             => 'Online Courses & Skill Training',
                'slug'             => 'online-courses',
                'description'      => 'Digital marketing, coding, graphic design, spoken English',
                'display_order'    => 2,
                'is_active'        => true,
            ],

            // Home Services
            [
                'primary_category_id' => $types['Home Services'] ?? null,
                'name'             => 'Plumbing Services',
                'slug'             => 'plumbing',
                'description'      => 'Pipe repair, water tank cleaning, bathroom fitting',
                'display_order'    => 1,
                'is_active'        => true,
            ],
            [
                'primary_category_id' => $types['Home Services'] ?? null,
                'name'             => 'Cleaning Services',
                'slug'             => 'cleaning-services',
                'description'      => 'House cleaning, sofa/carpet cleaning, deep cleaning',
                'display_order'    => 2,
                'is_active'        => true,
            ],
            [
                'primary_category_id' => $types['Home Services'] ?? null,
                'name'             => 'Electrical & Repair Services',
                'slug'             => 'electrical-repair',
                'description'      => 'Wiring, fan/AC repair, inverter service, appliance repair',
                'display_order'    => 3,
                'is_active'        => true,
            ],

            // Food & Restaurant
            [
                'primary_category_id' => $types['Restaurant & Cafe'] ?? null,
                'name'             => 'Nepali & Indian Cuisine',
                'slug'             => 'nepali-indian-cuisine',
                'description'      => 'Dal bhat, momo, thakali set, Indian curries',
                'display_order'    => 1,
                'is_active'        => true,
            ],
            [
                'primary_category_id' => $types['Restaurant & Cafe'] ?? null,
                'name'             => 'Fast Food & Snacks',
                'slug'             => 'fast-food-snacks',
                'description'      => 'Burger, pizza, chowmein, sel roti, chatpate',
                'display_order'    => 2,
                'is_active'        => true,
            ],

            // Add more groups as needed...
        ];

        foreach ($subCategories as $sub) {
            // Skip if business_type_id is null (missing parent type)
            if (!$sub['primary_category_id']) {
                continue;
            }

            BusinessSubCategory::updateOrCreate(
                [
                    'primary_category_id' => $sub['primary_category_id'],
                    'name'             => $sub['name'],
                ],
                $sub
            );
        }
    }

    /**
     * Fallback: Seed minimal business types if none exist
     */
    private function seedFallbackBusinessTypes(): array
    {
        $fallback = [
            ['name' => 'Electronics', 'slug' => 'electronics'],
            ['name' => 'Fashion & Apparel', 'slug' => 'fashion-apparel'],
            ['name' => 'Beauty & Personal Care', 'slug' => 'beauty-personal-care'],
            ['name' => 'Education & Coaching', 'slug' => 'education-coaching'],
            ['name' => 'Home Services', 'slug' => 'home-services'],
            ['name' => 'Restaurant & Cafe', 'slug' => 'restaurant-cafe'],
        ];

        $ids = [];
        foreach ($fallback as $type) {
            $record = PrimaryCategory::firstOrCreate(
                ['name' => $type['name']],
                $type
            );
            $ids[$type['name']] = $record->id;
        }

        return $ids;
    }
}