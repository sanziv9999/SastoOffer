<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Banner;
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
use App\Services\DealOfferService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoShowcaseTopUpSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('email', 'demo.seed2@sastooffer.test')->exists()) {
            $this->command?->warn('Demo showcase top-up already inserted. Skipping.');
            return;
        }

        $this->call([
            RoleSeeder::class,
            OfferTypeSeeder::class,
        ]);

        $service = app(DealOfferService::class);

        // Marker user for one-time behavior
        $marker = $this->makeUser('Demo Seed Marker', 'demo.seed2@sastooffer.test', 'customer', true);
        CustomerProfile::firstOrCreate(
            ['user_id' => $marker->id],
            ['full_name' => $marker->name, 'gender' => 'other']
        );

        // Expand categories: 1-3 subcategories for each new parent
        $categoryPlan = [
            ['Fashion', 'fashion', ['Mens Wear', 'Womens Wear', 'Accessories']],
            ['Home Living', 'home-living', ['Furniture', 'Kitchen']],
            ['Fitness', 'fitness', ['Gym', 'Yoga']],
            ['Automotive', 'automotive', ['Bike Care', 'Car Care']],
            ['Kids', 'kids', ['Toys', 'Learning']],
            ['Events', 'events', ['Concert', 'Workshops']],
        ];

        $leafCategories = Category::whereNotNull('parent_id')->get()->keyBy('slug');
        $order = 100;
        foreach ($categoryPlan as [$parentName, $parentSlug, $children]) {
            $parent = Category::firstOrCreate(
                ['slug' => $parentSlug],
                [
                    'parent_id' => null,
                    'name' => $parentName,
                    'icon_key' => 'gift',
                    'description' => 'Demo expanded category',
                    'image_url' => 'https://picsum.photos/seed/category-' . $parentSlug . '/800/600',
                    'display_order' => $order++,
                    'is_active' => true,
                ]
            );
            foreach ($children as $idx => $childName) {
                $childSlug = Str::slug($childName);
                $child = Category::firstOrCreate(
                    ['slug' => $childSlug],
                    [
                        'parent_id' => $parent->id,
                        'name' => $childName,
                        'description' => 'Demo expanded subcategory',
                        'image_url' => 'https://picsum.photos/seed/category-' . $childSlug . '/800/600',
                        'display_order' => $idx + 1,
                        'is_active' => true,
                    ]
                );
                $leafCategories->put($child->slug, $child);
            }
        }

        // Vendors in Kathmandu/Lalitpur/Bhaktapur
        $admin = User::role('admin')->first();
        $vendorSpecs = [
            ['name' => 'Baneshwor Deals Hub', 'slug' => 'baneshwor-deals-hub', 'email' => 'vendor.baneshwor@sastooffer.test', 'district' => 'Kathmandu', 'tole' => 'New Baneshwor', 'lat' => 27.6943, 'lng' => 85.3420],
            ['name' => 'Lalitpur Value Store', 'slug' => 'lalitpur-value-store', 'email' => 'vendor.lalitpur@sastooffer.test', 'district' => 'Lalitpur', 'tole' => 'Pulchowk', 'lat' => 27.6806, 'lng' => 85.3188],
            ['name' => 'Bhaktapur Smart Market', 'slug' => 'bhaktapur-smart-market', 'email' => 'vendor.bhaktapur@sastooffer.test', 'district' => 'Bhaktapur', 'tole' => 'Suryabinayak', 'lat' => 27.6710, 'lng' => 85.4298],
            ['name' => 'Thamel Offer House', 'slug' => 'thamel-offer-house', 'email' => 'vendor.thamel@sastooffer.test', 'district' => 'Kathmandu', 'tole' => 'Thamel', 'lat' => 27.7172, 'lng' => 85.3240],
        ];

        $vendors = [];
        foreach ($vendorSpecs as $i => $spec) {
            $user = $this->makeUser($spec['name'], $spec['email'], 'vendor', true);
            $addr = Address::firstOrCreate(
                ['user_id' => $user->id, 'tole' => $spec['tole']],
                [
                    'province' => 'Bagmati',
                    'district' => $spec['district'],
                    'municipality' => $spec['district'] . ' Municipality',
                    'ward_no' => (string) (($i % 9) + 1),
                    'latitude' => $spec['lat'],
                    'longitude' => $spec['lng'],
                    'is_default' => true,
                    'label' => 'Office',
                ]
            );

            $parentCategory = Category::whereNull('parent_id')->inRandomOrder()->first();
            $vendor = VendorProfile::firstOrCreate(
                ['slug' => $spec['slug']],
                [
                    'user_id' => $user->id,
                    'business_name' => $spec['name'],
                    'business_type' => 'hybrid',
                    'category_id' => $parentCategory?->id,
                    'verified_status' => 'verified',
                    'verified_at' => now()->subDays(5 + $i),
                    'verified_by_user_id' => $admin?->id,
                    'description' => 'Top-up seeded vendor profile.',
                    'public_email' => $spec['email'],
                    'public_phone' => '98' . str_pad((string) (70000000 + $i), 8, '0', STR_PAD_LEFT),
                    'default_address_id' => $addr->id,
                ]
            );
            $vendors[] = $vendor;

            Image::firstOrCreate(
                ['imageable_type' => VendorProfile::class, 'imageable_id' => $vendor->id, 'attribute_name' => 'logo'],
                ['image_url' => "https://picsum.photos/seed/vendor-topup-{$i}/400/400", 'sort_order' => 0]
            );
        }

        // Customers (mix verified and unverified)
        $customerSpecs = [
            ['Riya Sharma', 'customer.riya@sastooffer.test', true],
            ['Kiran Adhikari', 'customer.kiran@sastooffer.test', true],
            ['Suman Joshi', 'customer.suman@sastooffer.test', false],
            ['Mina Karki', 'customer.mina@sastooffer.test', true],
            ['Pawan Rai', 'customer.pawan@sastooffer.test', false],
            ['Nisha Gurung', 'customer.nisha@sastooffer.test', true],
        ];
        $customers = [];
        foreach ($customerSpecs as $idx => [$name, $email, $verified]) {
            $user = $this->makeUser($name, $email, 'customer', $verified);
            $district = ['Kathmandu', 'Lalitpur', 'Bhaktapur'][$idx % 3];
            $tole = ['New Baneshwor', 'Jawalakhel', 'Suryabinayak'][$idx % 3];
            $addr = Address::firstOrCreate(
                ['user_id' => $user->id, 'tole' => $tole . ' ' . $idx],
                [
                    'province' => 'Bagmati',
                    'district' => $district,
                    'municipality' => $district . ' Municipality',
                    'ward_no' => (string) (($idx % 9) + 1),
                    'latitude' => 27.68 + ($idx * 0.002),
                    'longitude' => 85.33 + ($idx * 0.003),
                    'is_default' => true,
                    'label' => 'Home',
                ]
            );
            CustomerProfile::firstOrCreate(
                ['user_id' => $user->id],
                ['full_name' => $name, 'gender' => $idx % 2 === 0 ? 'female' : 'male', 'phone' => '97' . str_pad((string) (60000000 + $idx), 8, '0', STR_PAD_LEFT), 'default_address_id' => $addr->id]
            );
            $customers[] = $user;
        }

        // Deals: ensure broad categories and 5 products in Kathmandu/New Baneshwor
        $offerTypePool = OfferType::query()->pluck('id', 'name')->all();
        $offerCycle = ['percentage_discount', 'fixed_amount_discount', 'flash_sale', 'bogo', 'cashback', 'free_shipping'];

        $allLeaf = $leafCategories->values();
        $createdDeals = [];
        for ($i = 0; $i < 12; $i++) {
            $vendor = $i < 5 ? $vendors[0] : $vendors[$i % count($vendors)];
            $leaf = $allLeaf[$i % max(1, $allLeaf->count())] ?? Category::whereNotNull('parent_id')->inRandomOrder()->first();
            if (! $leaf) {
                continue;
            }

            $title = ($i < 5 ? 'New Baneshwor Special' : 'City Deal') . ' #' . ($i + 1) . ' - ' . $leaf->name;
            $deal = Deal::create([
                'vendor_id' => $vendor->id,
                'category_id' => $leaf->id,
                'title' => $title,
                'slug' => Str::slug($title) . '-topup-' . $i,
                'base_price' => 1500 + ($i * 1200),
                'short_description' => 'Top-up seeded demo deal.',
                'long_description' => 'Expanded seeded content for visual review across roles, locations, pricing and admin workflows.',
                'highlights' => ['topup-seed', Str::slug($leaf->name), Str::slug($vendor->business_name)],
                'status' => $i % 7 === 0 ? 'pending' : 'active',
                'total_inventory' => 40 + ($i * 3),
            ]);
            $createdDeals[] = $deal;

            $offerTypeName = $offerCycle[$i % count($offerCycle)];
            $offerType = OfferType::find($offerTypePool[$offerTypeName] ?? null);
            if ($offerType) {
                $params = match ($offerTypeName) {
                    'percentage_discount', 'flash_sale' => ['discount_percent' => 10 + ($i % 5) * 5],
                    'fixed_amount_discount' => ['discount_amount' => 300 + ($i * 50)],
                    'bogo' => ['buy_quantity' => 1, 'get_quantity' => 1, 'get_discount_percent' => 50],
                    'cashback' => ['cashback_percent' => 5 + ($i % 4), 'cashback_amount' => 400 + ($i * 40)],
                    'free_shipping' => ['min_order_value' => 2000 + ($i * 200)],
                    default => [],
                };
                $service->attachOfferToDeal($deal, $offerType, [
                    'original_price' => $deal->base_price,
                    'params' => $params,
                    'currency_code' => 'NPR',
                    'status' => $deal->status === 'pending' ? 'pending' : 'active',
                    'starts_at' => now()->subDays(3),
                    'ends_at' => now()->addDays(5 + $i),
                    'display_type_names' => $i % 3 === 0 ? ['featured'] : [],
                ]);
            }

            Image::create([
                'imageable_type' => Deal::class,
                'imageable_id' => $deal->id,
                'attribute_name' => 'feature_photo',
                'image_url' => "https://picsum.photos/seed/topup-deal-{$i}/1200/800",
                'sort_order' => 0,
            ]);
        }

        // Featured ranks
        $featuredDeals = collect($createdDeals)->filter(fn (Deal $deal) => $deal->is_featured)->values();
        foreach ($featuredDeals as $idx => $deal) {
            FeaturedDealRank::firstOrCreate(['deal_id' => $deal->id], ['rank' => 100 + $idx + 1]);
        }

        // Reviews: 0..6 counts per offer, ratings 0..5, with at least 2 verified users participating
        $verifiedCustomers = collect($customers)->filter(fn (User $u) => $u->email_verified_at !== null)->values();
        $offerPivots = DealOfferType::query()
            ->whereIn('deal_id', collect($createdDeals)->pluck('id'))
            ->orderBy('id')
            ->get();

        foreach ($offerPivots as $idx => $pivot) {
            $count = $idx % 7; // 0..6 reviews
            for ($r = 0; $r < $count; $r++) {
                $reviewUser = $customers[($idx + $r) % count($customers)];
                Review::firstOrCreate(
                    [
                        'user_id' => $reviewUser->id,
                        'reviewable_type' => DealOfferType::class,
                        'reviewable_id' => $pivot->id,
                    ],
                    [
                        'rating' => ($idx + $r) % 6, // 0..5
                        'comment' => 'Top-up seeded review for rating variety.',
                    ]
                );
            }

            // ensure 1-2 verified users on early offers
            if ($idx < 4) {
                foreach ($verifiedCustomers->take(2) as $verifiedUser) {
                    Review::firstOrCreate(
                        [
                            'user_id' => $verifiedUser->id,
                            'reviewable_type' => DealOfferType::class,
                            'reviewable_id' => $pivot->id,
                        ],
                        [
                            'rating' => 4 + ($idx % 2),
                            'comment' => 'Verified user demo review.',
                        ]
                    );
                }
            }
        }

        // Orders + items (top up to around 10)
        $ordersToCreate = 10;
        $orderStatuses = ['pending', 'paid', 'redeemed', 'cancelled', 'refunded'];
        $pivotsForOrder = $offerPivots->values();
        for ($i = 0; $i < $ordersToCreate; $i++) {
            $customer = $customers[$i % count($customers)];
            $pivot = $pivotsForOrder[$i % max(1, $pivotsForOrder->count())] ?? null;
            $deal = $pivot?->deal;
            if (! $pivot || ! $deal) {
                continue;
            }

            $qty = ($i % 3) + 1;
            $unit = (float) ($pivot->final_price ?? $pivot->original_price ?? $deal->base_price ?? 0);
            $line = $unit * $qty;
            $status = $orderStatuses[$i % count($orderStatuses)];

            $order = Order::create([
                'user_id' => $customer->id,
                'vendor_id' => $deal->vendor_id,
                'order_number' => 'DEMO2-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'status' => $status,
                'currency_code' => 'NPR',
                'subtotal' => $line,
                'discount_total' => max(0, round($line * 0.08, 2)),
                'tax_total' => 0,
                'grand_total' => round($line * 0.92, 2),
                'payment_method' => $i % 2 === 0 ? 'esewa' : 'khalti',
                'payment_reference' => 'DEMO2-PAY-' . ($i + 1),
                'paid_at' => in_array($status, ['paid', 'redeemed'], true) ? now()->subHours($i + 1) : null,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'deal_id' => $deal->id,
                'deal_offer_type_id' => $pivot->id,
                'title' => $deal->title,
                'quantity' => $qty,
                'unit_price' => $unit,
                'line_total' => $line,
                'meta' => ['seed' => 'demo-topup'],
            ]);
        }

        // Banners top-up to 10
        $currentBannerCount = Banner::count();
        $targetBannerCount = 10;
        $categoriesForBanners = Category::whereNull('parent_id')->get();
        for ($i = $currentBannerCount; $i < $targetBannerCount; $i++) {
            $cat = $categoriesForBanners[$i % max(1, $categoriesForBanners->count())] ?? null;
            $banner = Banner::create([
                'title' => 'Demo Banner ' . ($i + 1),
                'text' => 'Showcase banner for visual testing and admin edits.',
                'is_featured' => $i < 6,
                'sort_order' => $i + 1,
                'category_id' => $cat?->id,
            ]);
            Image::create([
                'imageable_type' => Banner::class,
                'imageable_id' => $banner->id,
                'attribute_name' => 'image',
                'image_url' => "https://picsum.photos/seed/topup-banner-{$i}/1600/600",
                'sort_order' => 0,
            ]);
        }

        $this->command?->info('Demo showcase top-up inserted (expanded categories, locations, deals, reviews, orders, banners).');
    }

    protected function makeUser(string $name, string $email, string $role, bool $verified): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => 'password', 'terms' => true]
        );
        $user->forceFill([
            'name' => $name,
            'status' => $verified ? 'active' : 'pending_verify',
            'email_verified_at' => $verified ? now() : null,
        ])->save();
        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }

        return $user;
    }
}

