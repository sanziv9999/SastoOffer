<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Banner;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\CustomerProfile;
use App\Models\Deal;
use App\Models\DealOfferType;
use App\Models\FeaturedDealRank;
use App\Models\Image;
use App\Models\OfferType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\Wishlist;
use App\Services\DealOfferService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoShowcaseSeeder extends Seeder
{
    /**
     * Single-run demo dataset for UI showcase and manual editing.
     */
    public function run(): void
    {
        if (User::where('email', 'demo.admin@sastooffer.test')->exists()) {
            $this->command?->warn('Demo showcase data already exists. Skipping one-time insert.');
            return;
        }

        // Prerequisites
        $this->call([
            RoleSeeder::class,
            OfferTypeSeeder::class,
        ]);

        DB::transaction(function () {
            $service = app(DealOfferService::class);

            // Categories (parents + children)
            $foodParent = $this->createCategory(null, 'Food & Dining', 'food-dining', 1, 'utensils');
            $wellnessParent = $this->createCategory(null, 'Wellness & Beauty', 'wellness-beauty', 2, 'heart');
            $electronicsParent = $this->createCategory(null, 'Electronics', 'electronics', 3, 'monitor');
            $travelParent = $this->createCategory(null, 'Travel & Stays', 'travel-stays', 4, 'plane');

            $categories = [
                $this->createCategory($foodParent->id, 'Restaurants', 'restaurants', 1),
                $this->createCategory($foodParent->id, 'Cafe', 'cafe', 2),
                $this->createCategory($wellnessParent->id, 'Salon', 'salon', 1),
                $this->createCategory($wellnessParent->id, 'Spa', 'spa', 2),
                $this->createCategory($electronicsParent->id, 'Mobile', 'mobile', 1),
                $this->createCategory($electronicsParent->id, 'Laptop', 'laptop', 2),
                $this->createCategory($travelParent->id, 'Hotel', 'hotel', 1),
                $this->createCategory($travelParent->id, 'Adventure', 'adventure', 2),
            ];

            // Users + roles (password = password)
            $admin = $this->createUser('Demo Admin', 'demo.admin@sastooffer.test', 'admin');
            $vendorUser1 = $this->createUser('Suvarna Vendor', 'vendor.suvarna@sastooffer.test', 'vendor');
            $vendorUser2 = $this->createUser('Himal Vendor', 'vendor.himal@sastooffer.test', 'vendor');
            $customer1 = $this->createUser('Aarav Customer', 'customer.aarav@sastooffer.test', 'customer');
            $customer2 = $this->createUser('Sita Customer', 'customer.sita@sastooffer.test', 'customer');
            $customer3 = $this->createUser('Nabin Customer', 'customer.nabin@sastooffer.test', 'customer');

            // Addresses (3-4 in same district intentionally)
            $vendorAddress1 = Address::create([
                'user_id' => $vendorUser1->id,
                'province' => 'Bagmati',
                'district' => 'Kathmandu',
                'municipality' => 'Kathmandu Metropolitan City',
                'ward_no' => '10',
                'tole' => 'New Baneshwor',
                'latitude' => 27.6943,
                'longitude' => 85.3420,
                'is_default' => true,
                'label' => 'Office',
            ]);
            $vendorAddress2 = Address::create([
                'user_id' => $vendorUser2->id,
                'province' => 'Bagmati',
                'district' => 'Kathmandu',
                'municipality' => 'Kathmandu Metropolitan City',
                'ward_no' => '5',
                'tole' => 'Thamel',
                'latitude' => 27.7172,
                'longitude' => 85.3240,
                'is_default' => true,
                'label' => 'Office',
            ]);
            $customerAddress1 = Address::create([
                'user_id' => $customer1->id,
                'province' => 'Bagmati',
                'district' => 'Kathmandu',
                'municipality' => 'Lalitpur Metropolitan City',
                'ward_no' => '3',
                'tole' => 'Jawalakhel',
                'latitude' => 27.6724,
                'longitude' => 85.3131,
                'is_default' => true,
                'label' => 'Home',
            ]);
            $customerAddress2 = Address::create([
                'user_id' => $customer2->id,
                'province' => 'Bagmati',
                'district' => 'Bhaktapur',
                'municipality' => 'Bhaktapur Municipality',
                'ward_no' => '7',
                'tole' => 'Suryabinayak',
                'latitude' => 27.6710,
                'longitude' => 85.4298,
                'is_default' => true,
                'label' => 'Home',
            ]);
            $customerAddress3 = Address::create([
                'user_id' => $customer3->id,
                'province' => 'Bagmati',
                'district' => 'Kathmandu',
                'municipality' => 'Kirtipur Municipality',
                'ward_no' => '2',
                'tole' => 'Kirtipur',
                'latitude' => 27.6667,
                'longitude' => 85.2833,
                'is_default' => true,
                'label' => 'Home',
            ]);

            // Profiles
            $vendor1 = VendorProfile::create([
                'user_id' => $vendorUser1->id,
                'business_name' => 'Suvarna Bites',
                'slug' => 'suvarna-bites',
                'business_type' => 'service',
                'category_id' => $foodParent->id,
                'verified_status' => 'verified',
                'verified_at' => now()->subDays(15),
                'verified_by_user_id' => $admin->id,
                'description' => 'Popular dining and cafe offers in Kathmandu.',
                'public_email' => 'contact@suvarnabites.test',
                'public_phone' => '9800000001',
                'website_url' => 'https://example.com/suvarna-bites',
                'default_address_id' => $vendorAddress1->id,
            ]);
            $vendor2 = VendorProfile::create([
                'user_id' => $vendorUser2->id,
                'business_name' => 'Himal Gadgets',
                'slug' => 'himal-gadgets',
                'business_type' => 'product',
                'category_id' => $electronicsParent->id,
                'verified_status' => 'verified',
                'verified_at' => now()->subDays(10),
                'verified_by_user_id' => $admin->id,
                'description' => 'Electronics and travel-ready gadgets at local prices.',
                'public_email' => 'hello@himalgadgets.test',
                'public_phone' => '9800000002',
                'website_url' => 'https://example.com/himal-gadgets',
                'default_address_id' => $vendorAddress2->id,
            ]);

            CustomerProfile::create(['user_id' => $customer1->id, 'full_name' => $customer1->name, 'gender' => 'male', 'phone' => '9811111111', 'default_address_id' => $customerAddress1->id]);
            CustomerProfile::create(['user_id' => $customer2->id, 'full_name' => $customer2->name, 'gender' => 'female', 'phone' => '9822222222', 'default_address_id' => $customerAddress2->id]);
            CustomerProfile::create(['user_id' => $customer3->id, 'full_name' => $customer3->name, 'gender' => 'male', 'phone' => '9833333333', 'default_address_id' => $customerAddress3->id]);

            // Deals + offers
            $deals = [];
            $offerTypes = OfferType::query()->pluck('id', 'name');
            $dealBlueprints = [
                ['vendor' => $vendor1, 'category' => $categories[0], 'title' => 'Family Dinner Combo Deal', 'price' => 2400, 'type' => 'percentage_discount', 'params' => ['discount_percent' => 25], 'featured' => true],
                ['vendor' => $vendor1, 'category' => $categories[2], 'title' => 'Couple Spa Evening Package', 'price' => 5200, 'type' => 'fixed_amount_discount', 'params' => ['discount_amount' => 800], 'featured' => false],
                ['vendor' => $vendor2, 'category' => $categories[4], 'title' => 'Smartphone Weekend Drop', 'price' => 42000, 'type' => 'flash_sale', 'params' => ['discount_percent' => 18], 'featured' => true],
                ['vendor' => $vendor2, 'category' => $categories[5], 'title' => 'Laptop Upgrade Fest', 'price' => 96000, 'type' => 'cashback', 'params' => ['cashback_percent' => 8, 'cashback_amount' => 3000], 'featured' => false],
                ['vendor' => $vendor2, 'category' => $categories[6], 'title' => 'Kathmandu Staycation Night', 'price' => 7800, 'type' => 'free_shipping', 'params' => ['min_order_value' => 5000], 'featured' => true],
            ];

            foreach ($dealBlueprints as $idx => $blueprint) {
                $deal = Deal::create([
                    'vendor_id' => $blueprint['vendor']->id,
                    'category_id' => $blueprint['category']->id,
                    'title' => $blueprint['title'],
                    'slug' => Str::slug($blueprint['title']) . '-demo',
                    'base_price' => $blueprint['price'],
                    'short_description' => 'Demo data for visual testing and editing.',
                    'long_description' => 'This is seeded showcase content. You can edit title, price, offer, media, and status from the dashboard.',
                    'highlights' => ['demo', 'local-deals-nepal', 'editable-content'],
                    'status' => $idx === 4 ? 'pending' : 'active',
                    'total_inventory' => 30 + ($idx * 10),
                ]);

                $offerType = OfferType::find($offerTypes[$blueprint['type']] ?? null);
                if ($offerType) {
                    $pivot = $service->attachOfferToDeal($deal, $offerType, [
                        'original_price' => $blueprint['price'],
                        'params' => $blueprint['params'],
                        'currency_code' => 'NPR',
                        'status' => $idx === 4 ? 'pending' : 'active',
                        'starts_at' => now()->subDays(2),
                        'ends_at' => now()->addDays(10 + $idx),
                        'display_type_names' => $blueprint['featured'] ? ['featured'] : [],
                    ]);

                    if ($pivot && $idx === 0) {
                        // Add second offer to show multiple child offers for a deal.
                        $bogoType = OfferType::where('name', 'bogo')->first();
                        if ($bogoType) {
                            $service->attachOfferToDeal($deal, $bogoType, [
                                'original_price' => $blueprint['price'],
                                'params' => ['buy_quantity' => 1, 'get_quantity' => 1, 'get_discount_percent' => 50],
                                'currency_code' => 'NPR',
                                'status' => 'active',
                                'starts_at' => now()->subDay(),
                                'ends_at' => now()->addDays(7),
                            ]);
                        }
                    }
                }

                $deals[] = $deal;
            }

            // Featured deal ranks
            $featuredDeals = Deal::query()
                ->whereIn('id', collect($deals)->pluck('id'))
                ->get()
                ->filter(fn (Deal $deal) => $deal->is_featured)
                ->values();
            foreach ($featuredDeals as $rank => $deal) {
                FeaturedDealRank::create([
                    'deal_id' => $deal->id,
                    'rank' => $rank + 1,
                ]);
            }

            // Images
            foreach ($deals as $i => $deal) {
                $imgBase = 300 + $i;
                Image::create([
                    'imageable_type' => Deal::class,
                    'imageable_id' => $deal->id,
                    'attribute_name' => 'feature_photo',
                    'image_url' => "https://picsum.photos/seed/deal{$imgBase}/1200/800",
                    'sort_order' => 0,
                ]);
                Image::create([
                    'imageable_type' => Deal::class,
                    'imageable_id' => $deal->id,
                    'attribute_name' => 'gallery',
                    'image_url' => "https://picsum.photos/seed/deal{$imgBase}b/1200/800",
                    'sort_order' => 1,
                ]);
            }
            Image::create(['imageable_type' => VendorProfile::class, 'imageable_id' => $vendor1->id, 'attribute_name' => 'logo', 'image_url' => 'https://picsum.photos/seed/vendor1/300/300', 'sort_order' => 0]);
            Image::create(['imageable_type' => VendorProfile::class, 'imageable_id' => $vendor2->id, 'attribute_name' => 'logo', 'image_url' => 'https://picsum.photos/seed/vendor2/300/300', 'sort_order' => 0]);

            // Reviews (on offers + vendors)
            $offerPivots = DealOfferType::query()->whereIn('deal_id', collect($deals)->pluck('id'))->get();
            foreach ([$customer1, $customer2, $customer3] as $idx => $customer) {
                $pivot = $offerPivots[$idx] ?? $offerPivots->first();
                if ($pivot) {
                    Review::create([
                        'user_id' => $customer->id,
                        'reviewable_type' => DealOfferType::class,
                        'reviewable_id' => $pivot->id,
                        'rating' => 5 - $idx,
                        'comment' => 'Great demo offer experience and smooth redemption flow.',
                    ]);
                }
            }
            Review::create([
                'user_id' => $customer1->id,
                'reviewable_type' => VendorProfile::class,
                'reviewable_id' => $vendor1->id,
                'rating' => 5,
                'comment' => 'Vendor was responsive and service quality was excellent.',
            ]);
            Review::create([
                'user_id' => $customer2->id,
                'reviewable_type' => VendorProfile::class,
                'reviewable_id' => $vendor2->id,
                'rating' => 4,
                'comment' => 'Good products and fair pricing.',
            ]);

            // Orders + items
            $paidOrder = Order::create([
                'user_id' => $customer1->id,
                'vendor_id' => $vendor1->id,
                'order_number' => 'DEMO-1001',
                'status' => 'paid',
                'currency_code' => 'NPR',
                'subtotal' => 2400,
                'discount_total' => 600,
                'tax_total' => 0,
                'grand_total' => 1800,
                'payment_method' => 'esewa',
                'payment_reference' => 'DEMO-PAY-1001',
                'paid_at' => now()->subDay(),
            ]);
            $redeemedOrder = Order::create([
                'user_id' => $customer2->id,
                'vendor_id' => $vendor2->id,
                'order_number' => 'DEMO-1002',
                'status' => 'redeemed',
                'currency_code' => 'NPR',
                'subtotal' => 42000,
                'discount_total' => 7560,
                'tax_total' => 0,
                'grand_total' => 34440,
                'payment_method' => 'khalti',
                'payment_reference' => 'DEMO-PAY-1002',
                'paid_at' => now()->subHours(20),
            ]);

            $paidPivot = DealOfferType::query()->where('deal_id', $deals[0]->id)->first();
            $redeemedPivot = DealOfferType::query()->where('deal_id', $deals[2]->id)->first();
            if ($paidPivot) {
                OrderItem::create([
                    'order_id' => $paidOrder->id,
                    'deal_id' => $deals[0]->id,
                    'deal_offer_type_id' => $paidPivot->id,
                    'title' => $deals[0]->title,
                    'quantity' => 1,
                    'unit_price' => (float) $paidPivot->final_price,
                    'line_total' => (float) $paidPivot->final_price,
                    'meta' => ['seeded' => true],
                ]);
            }
            if ($redeemedPivot) {
                OrderItem::create([
                    'order_id' => $redeemedOrder->id,
                    'deal_id' => $deals[2]->id,
                    'deal_offer_type_id' => $redeemedPivot->id,
                    'title' => $deals[2]->title,
                    'quantity' => 1,
                    'unit_price' => (float) $redeemedPivot->final_price,
                    'line_total' => (float) $redeemedPivot->final_price,
                    'meta' => ['seeded' => true],
                ]);
            }

            // Wishlist + cart
            Wishlist::create(['user_id' => $customer1->id, 'deal_id' => $deals[1]->id]);
            Wishlist::create(['user_id' => $customer2->id, 'deal_id' => $deals[2]->id]);
            if ($redeemedPivot) {
                CartItem::create(['user_id' => $customer3->id, 'deal_offer_type_id' => $redeemedPivot->id, 'quantity' => 1]);
            }
            if ($paidPivot) {
                CartItem::create(['user_id' => $customer2->id, 'deal_offer_type_id' => $paidPivot->id, 'quantity' => 2]);
            }

            // Banners
            $banner1 = Banner::create([
                'title' => 'Kathmandu Weekend Picks',
                'text' => 'Handpicked local featured deals for this weekend.',
                'is_featured' => true,
                'sort_order' => 1,
                'category_id' => $foodParent->id,
            ]);
            $banner2 = Banner::create([
                'title' => 'Tech & Travel Combo Savings',
                'text' => 'Save more with electronics and staycation offers.',
                'is_featured' => true,
                'sort_order' => 2,
                'category_id' => $electronicsParent->id,
            ]);
            Image::create(['imageable_type' => Banner::class, 'imageable_id' => $banner1->id, 'attribute_name' => 'image', 'image_url' => 'https://picsum.photos/seed/banner1/1600/600', 'sort_order' => 0]);
            Image::create(['imageable_type' => Banner::class, 'imageable_id' => $banner2->id, 'attribute_name' => 'image', 'image_url' => 'https://picsum.photos/seed/banner2/1600/600', 'sort_order' => 0]);
        });

        $this->command?->info('Demo showcase data inserted successfully (single-run dataset).');
    }

    protected function createUser(string $name, string $email, string $role): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'password',
            'terms' => true,
        ]);
        $user->forceFill([
            'status' => 'active',
            'email_verified_at' => now(),
        ])->save();
        $user->assignRole($role);

        return $user;
    }

    protected function createCategory(?int $parentId, string $name, string $slug, int $order = 0, ?string $iconKey = null): Category
    {
        return Category::create([
            'parent_id' => $parentId,
            'name' => $name,
            'slug' => $slug,
            'icon_key' => $iconKey,
            'description' => 'Demo category for showcase.',
            'image_url' => 'https://picsum.photos/seed/category-' . $slug . '/800/600',
            'display_order' => $order,
            'is_active' => true,
        ]);
    }
}

