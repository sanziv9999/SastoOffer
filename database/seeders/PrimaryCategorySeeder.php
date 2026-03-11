<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PrimaryCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
    $types = [
        ['name' => 'Restaurant & Cafe',         'slug' => 'restaurant-cafe',         'description' => 'Restaurants, cafes, bakeries, food stalls'],
        ['name' => 'Grocery & Supermarket',      'slug' => 'grocery-supermarket',      'description' => 'Kirana, supermarkets, organic stores'],
        ['name' => 'Fashion & Apparel',          'slug' => 'fashion-apparel',          'description' => 'Clothing, shoes, accessories, jewellery'],
        ['name' => 'Beauty & Personal Care',     'slug' => 'beauty-personal-care',     'description' => 'Salons, spas, cosmetics, gyms'],
        ['name' => 'Electronics & Gadgets',      'slug' => 'electronics-gadgets',      'description' => 'Mobiles, laptops, home appliances'],
        ['name' => 'Health & Pharmacy',          'slug' => 'health-pharmacy',          'description' => 'Pharmacies, ayurvedic, fitness products'],
        ['name' => 'Home Services',              'slug' => 'home-services',            'description' => 'Plumbing, cleaning, repair services'],
        ['name' => 'Retail Shop / General Store','slug' => 'retail-shop',              'description' => 'General merchandise shops'],
        ['name' => 'Education & Coaching',       'slug' => 'education-coaching',       'description' => 'Tuition, coaching centers, courses'],
        ['name' => 'Others',                     'slug' => 'others',                   'description' => 'Any other business type'],
    ];

    foreach ($types as $type) {
        \App\Models\PrimaryCategory::firstOrCreate(
            ['slug' => $type['slug']],
            $type
        );
    }
    }
}
