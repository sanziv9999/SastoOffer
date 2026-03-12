<section class="py-8 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-slate-800 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary mr-2 h-5 w-5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                Recent Offers
            </h2>
            <a href="{{ route('search', ['sort' => 'newest']) }}" class="text-primary hover:underline text-sm font-medium flex items-center">
                View all
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-1 h-4 w-4"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
            </a>
        </div>
        
        <div 
            class="relative group overflow-hidden"
            x-data="{ 
                scrollLeft() { this.$refs.scrollContainer.scrollBy({ left: -300, behavior: 'smooth' }) },
                scrollRight() { this.$refs.scrollContainer.scrollBy({ left: 300, behavior: 'smooth' }) }
            }"
        >
            <div 
                x-ref="scrollContainer"
                class="flex gap-4 py-2 overflow-x-auto scrollbar-hide scroll-smooth cursor-grab" 
                style="scrollbar-width: none; ms-overflow-style: none;"
            >
                @php
                    $recentDeals = [
                        [
                            'id' => '1',
                            'title' => '50% Off Luxury 5-Course Dinner for Two',
                            'originalPrice' => 200,
                            'discountedPrice' => 100,
                            'discountPercentage' => 50,
                            'image' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=600&auto=format',
                            'timeAgo' => '2 hours ago'
                        ],
                        [
                            'id' => '2',
                            'title' => 'Luxury Spa Day Package - 30% Off',
                            'originalPrice' => 300,
                            'discountedPrice' => 210,
                            'discountPercentage' => 30,
                            'image' => 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=600&auto=format',
                            'timeAgo' => '5 hours ago'
                        ],
                        [
                            'id' => '3',
                            'title' => 'Guided Mountain Hiking Tour - BOGO',
                            'originalPrice' => 150,
                            'discountedPrice' => 75,
                            'discountPercentage' => 50,
                            'image' => 'https://images.unsplash.com/photo-1551632811-561732d1e306?w=600&auto=format',
                            'timeAgo' => '1 day ago'
                        ],
                        // ... more deals to fill
                    ];
                @endphp

                @foreach($recentDeals as $deal)
                    <div class="bg-card text-card-foreground rounded-lg border border-border overflow-hidden flex-shrink-0 w-[280px] hover:shadow-md transition-shadow group">
                        <div class="relative">
                            <img 
                                src="{{ $deal['image'] }}" 
                                alt="{{ $deal['title'] }}" 
                                class="h-40 w-full object-cover transition-transform duration-300 group-hover:scale-105" 
                            />
                            <div class="absolute top-2 right-2 bg-green-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded">
                                {{ $deal['discountPercentage'] }}% OFF
                            </div>
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent text-white p-2">
                                <div class="flex items-center text-xs">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3 w-3 mr-1"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    <span>Added {{ $deal['timeAgo'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <a href="{{ route('deals.show', ['deal' => $deal['id']]) }}">
                                <h3 class="font-semibold text-slate-800 mb-2 line-clamp-2 hover:text-primary transition-colors">
                                    {{ $deal['title'] }}
                                </h3>
                            </a>
                            <div class="flex items-baseline">
                                <span class="text-lg font-bold text-primary mr-2">
                                    ${{ $deal['discountedPrice'] }}
                                </span>
                                <span class="text-sm line-through text-gray-400">
                                    ${{ $deal['originalPrice'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            {{-- Manual scroll controls --}}
            <button 
                @click="scrollLeft()"
                class="hidden md:flex absolute left-4 top-1/2 transform -translate-y-1/2 h-8 w-8 rounded-full bg-white/90 border border-border shadow-md z-10 opacity-0 group-hover:opacity-100 transition-opacity items-center justify-center hover:bg-white"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="m15 18-6-6 6-6"></path></svg>
            </button>
            
            <button 
                @click="scrollRight()"
                class="hidden md:flex absolute right-4 top-1/2 transform -translate-y-1/2 h-8 w-8 rounded-full bg-white/90 border border-border shadow-md z-10 opacity-0 group-hover:opacity-100 transition-opacity items-center justify-center hover:bg-white"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="m9 18 6-6-6-6"></path></svg>
            </button>
        </div>
    </div>
</section>
