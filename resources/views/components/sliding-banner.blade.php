@php
    $dbBanners = \App\Models\Banner::query()
        ->featured()
        ->orderedForLanding()
        ->with([
            'category',
            'images' => fn ($q) => $q->where('attribute_name', 'image'),
        ])
        ->get();

    $realBanners = $dbBanners
        ->map(function (\App\Models\Banner $b) {
            $img = $b->images?->firstWhere('attribute_name', 'image')?->image_url;
            $categorySlug = $b->category?->slug;

            return [
                'id' => $b->id,
                'title' => (string) $b->title,
                'description' => (string) ($b->text ?? ''),
                'imageUrl' => $img ?: 'https://images.unsplash.com/photo-1534349762230-e0cadf78f5da?w=1200&auto=format',
                'link' => $categorySlug
                    ? route('search', ['category' => $categorySlug])
                    : route('search'),
            ];
        })
        ->filter(fn ($row) => ! empty($row['title']))
        ->values()
        ->all();

    // Fallback to static slides if no DB banners exist yet
    if (count($realBanners) === 0) {
        $realBanners = [
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
    }

    // Build the set with clones for seamless looping: [Last, 1, 2, 3, First]
    $banners = array_merge([$realBanners[count($realBanners)-1]], $realBanners, [$realBanners[0]]);
    $realCount = count($realBanners);
@endphp

<section 
    class="relative w-full py-4 bg-transparent" 
    x-data="{ 
        currentBanner: 1, 
        realCount: {{ $realCount }},
        isTransitioning: true,
        autoPlay: true,
        interval: null,
        
        next() {
            if (this.currentBanner >= this.realCount + 1) return;
            this.isTransitioning = true;
            this.currentBanner++;
            
            if (this.currentBanner === this.realCount + 1) {
                setTimeout(() => {
                    this.isTransitioning = false;
                    this.currentBanner = 1;
                }, 500);
            }
        },
        prev() {
            if (this.currentBanner <= 0) return;
            this.isTransitioning = true;
            this.currentBanner--;
            
            if (this.currentBanner === 0) {
                setTimeout(() => {
                    this.isTransitioning = false;
                    this.currentBanner = this.realCount;
                }, 500);
            }
        },
        startAutoPlay() {
            if (this.autoPlay) {
                this.interval = setInterval(() => this.next(), 6000);
            }
        },
        stopAutoPlay() {
            if (this.interval) clearInterval(this.interval);
        },
        init() {
            this.startAutoPlay();
        }
    }"
>
    <div class="container mx-auto px-4">
        <div 
            class="relative w-full overflow-hidden rounded-lg group"
            @mouseenter="stopAutoPlay()"
            @mouseleave="startAutoPlay()"
        >
            <div 
                class="flex transition-all duration-500 ease-in-out"
                :class="{ 'transition-none': !isTransitioning }"
                :style="`transform: translateX(-${currentBanner * 100}%)`"
            >
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
                @click="prev()"
                class="absolute left-4 md:left-8 top-1/2 -translate-y-1/2 h-8 w-8 md:h-10 md:w-10 rounded-full bg-white/80 border border-border shadow-sm flex items-center justify-center text-foreground hover:bg-white transition-all opacity-0 group-hover:opacity-100"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 md:h-5 md:w-5"><path d="m15 18-6-6 6-6"></path></svg>
            </button>

            {{-- Next Button --}}
            <button 
                @click="next()"
                class="absolute right-4 md:right-8 top-1/2 -translate-y-1/2 h-8 w-8 md:h-10 md:w-10 rounded-full bg-white/80 border border-border shadow-sm flex items-center justify-center text-foreground hover:bg-white transition-all opacity-0 group-hover:opacity-100"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 md:h-5 md:w-5 rotate-180"><path d="m15 18-6-6 6-6"></path></svg>
            </button>
            
            {{-- Dots Indicator --}}
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-1.5 px-3 py-1.5 bg-black/20 backdrop-blur-sm rounded-full">
                @foreach($realBanners as $idx => $b)
                    <button 
                        @click="isTransitioning = true; currentBanner = {{ $idx + 1 }}"
                        class="h-1.5 rounded-full transition-all duration-300"
                        :class="((currentBanner-1 + realCount) % realCount === {{ $idx }}) ? 'w-6 bg-white' : 'w-1.5 bg-white/50 hover:bg-white/80'"
                    ></button>
                @endforeach
            </div>
        </div>
    </div>
</section>
