<section 
    class="relative w-full py-4 bg-white" 
    x-data="{ 
        currentBanner: 0, 
        bannersCount: 3,
        autoPlay: true,
        init() {
            if (this.autoPlay) {
                setInterval(() => {
                    this.currentBanner = (this.currentBanner + 1) % this.bannersCount;
                }, 5000);
            }
        }
    }"
>
    <div class="container mx-auto px-4">
        <div class="relative w-full overflow-hidden rounded-lg group">
            <div 
                class="flex transition-transform duration-500 ease-in-out"
                :style="`transform: translateX(-${currentBanner * 100}%)`"
            >
                @php
                    $banners = [
                        [
                            'id' => 1,
                            'title' => "Summer Deals",
                            'description' => "Up to 70% off on selected summer items",
                            'imageUrl' => "https://images.unsplash.com/photo-1534349762230-e0cadf78f5da?w=1200&auto=format",
                            'link' => route('search', ['category' => 'summer'])
                        ],
                        [
                            'id' => 2,
                            'title' => "Tech Sale",
                            'description' => "Latest gadgets at unbeatable prices",
                            'imageUrl' => "https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=1200&auto=format",
                            'link' => route('search', ['category' => 'tech'])
                        ],
                        [
                            'id' => 3,
                            'title' => "Travel Offers",
                            'description' => "Exclusive holiday packages",
                            'imageUrl' => "https://images.unsplash.com/photo-1506929562872-bb421503ef21?w=1200&auto=format",
                            'link' => route('search', ['category' => 'travel'])
                        ]
                    ];
                @endphp

                @foreach($banners as $banner)
                    <div class="w-full flex-shrink-0">
                        <a href="{{ $banner['link'] }}" class="block relative overflow-hidden">
                            <div class="aspect-[21/9] md:aspect-[21/6] w-full relative">
                                <img 
                                    src="{{ $banner['imageUrl'] }}" 
                                    alt="{{ $banner['title'] }}" 
                                    class="w-full h-full object-cover"
                                >
                                <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/30 to-transparent flex flex-col justify-center pl-14 md:pl-20 pr-14 md:pr-20">
                                    <h2 class="text-xl md:text-3xl font-bold text-white mb-2">{{ $banner['title'] }}</h2>
                                    <p class="text-sm md:text-base text-white/90 mb-4 max-w-md">{{ $banner['description'] }}</p>
                                    <div class="flex items-center text-white text-sm md:text-base font-medium">
                                        <span>View Offers</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 h-4 w-4"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            {{-- Previous Button --}}
            <button 
                @click="currentBanner = currentBanner === 0 ? bannersCount - 1 : currentBanner - 1"
                class="absolute left-4 md:left-8 top-1/2 -translate-y-1/2 h-8 w-8 md:h-10 md:w-10 rounded-full bg-white/80 border border-border shadow-sm flex items-center justify-center text-foreground hover:bg-white transition-all"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 md:h-5 md:w-5"><path d="m15 18-6-6 6-6"></path></svg>
            </button>

            {{-- Next Button --}}
            <button 
                @click="currentBanner = (currentBanner + 1) % bannersCount"
                class="absolute right-4 md:right-8 top-1/2 -translate-y-1/2 h-8 w-8 md:h-10 md:w-10 rounded-full bg-white/80 border border-border shadow-sm flex items-center justify-center text-foreground hover:bg-white transition-all"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 md:h-5 md:w-5"><path d="m9 18 6-6-6-6"></path></svg>
            </button>
        </div>
    </div>
</section>
