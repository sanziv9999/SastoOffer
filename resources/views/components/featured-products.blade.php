<section class="py-8 bg-background">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-lg md:text-xl font-bold text-foreground flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-500 mr-2 h-5 w-5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                Discover Amazing Deals
            </h2>
            <a href="{{ route('search', ['featured' => 'true']) }}" class="text-primary hover:underline text-sm font-medium">
                View all
            </a>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 md:gap-4">
            @php
                $featuredDeals = [
                    [
                        'id' => '1',
                        'title' => '50% Off Luxury 5-Course Dinner for Two',
                        'categoryId' => '1',
                        'categoryName' => 'Restaurants',
                        'originalPrice' => 200,
                        'discountedPrice' => 100,
                        'discountPercentage' => 50,
                        'image' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=600&auto=format'
                    ],
                    [
                        'id' => '2',
                        'title' => 'Luxury Spa Day Package - 30% Off',
                        'categoryId' => '2',
                        'categoryName' => 'Beauty & Spa',
                        'originalPrice' => 300,
                        'discountedPrice' => 210,
                        'discountPercentage' => 30,
                        'image' => 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=600&auto=format'
                    ],
                    [
                        'id' => '6',
                        'title' => 'White Water Rafting Adventure - 25% Off',
                        'categoryId' => '3',
                        'categoryName' => 'Activities',
                        'originalPrice' => 120,
                        'discountedPrice' => 90,
                        'discountPercentage' => 25,
                        'image' => 'https://images.unsplash.com/photo-1530866495561-507c58f4cca5?w=600&auto=format'
                    ],
                    [
                        'id' => '4',
                        'title' => 'Weekend Brunch Buffet - 40% Off',
                        'categoryId' => '1',
                        'categoryName' => 'Restaurants',
                        'originalPrice' => 80,
                        'discountedPrice' => 48,
                        'discountPercentage' => 40,
                        'image' => 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=600&auto=format'
                    ],
                    // Repeating some to fill the 8 slots like in React
                    [
                        'id' => '5',
                        'title' => 'Premium Facial Treatment Bundle - Save $75',
                        'categoryId' => '2',
                        'categoryName' => 'Beauty & Spa',
                        'originalPrice' => 225,
                        'discountedPrice' => 150,
                        'discountPercentage' => 33,
                        'image' => 'https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?w=600&auto=format'
                    ],
                    [
                        'id' => '3',
                        'title' => 'Guided Mountain Hiking Tour - BOGO',
                        'categoryId' => '3',
                        'categoryName' => 'Activities',
                        'originalPrice' => 150,
                        'discountedPrice' => 75,
                        'discountPercentage' => 50,
                        'image' => 'https://images.unsplash.com/photo-1551632811-561732d1e306?w=600&auto=format'
                    ],
                ];
                // Add more if needed to reach 8
            @endphp

            @foreach($featuredDeals as $deal)
                <x-deal-card :deal="$deal" />
            @endforeach
        </div>
    </div>
</section>
