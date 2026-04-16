@props(['recentOffers' => []])

@if(count($recentOffers) > 0)
<section class="py-12 bg-transparent">
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
            class="group/recent-offers"
            x-data="{ 
                scrollLeft() { this.$refs.scrollContainer.scrollBy({ left: -300, behavior: 'smooth' }) },
                scrollRight() { this.$refs.scrollContainer.scrollBy({ left: 300, behavior: 'smooth' }) }
            }"
        >
            <div class="relative">
                {{-- Content --}}
                <div class="overflow-hidden">
                    <div 
                        x-ref="scrollContainer"
                        class="flex gap-4 py-2 overflow-x-auto scrollbar-hide scroll-smooth cursor-grab" 
                        style="scrollbar-width: none; ms-overflow-style: none;"
                    >
                        @foreach($recentOffers as $deal)
                            <div class="flex-shrink-0 w-[280px]">
                                <x-deal-card :deal="$deal" />
                            </div>
                        @endforeach
                    </div>
                </div>
                <button
                    @click="scrollLeft()"
                    class="hidden md:flex absolute left-3 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-white/90 border border-border shadow-md opacity-0 group-hover/recent-offers:opacity-100 transition-all items-center justify-center text-foreground hover:bg-primary hover:text-white z-20"
                    aria-label="Scroll left"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="m15 18-6-6 6-6"></path></svg>
                </button>
                <button
                    @click="scrollRight()"
                    class="hidden md:flex absolute right-3 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-white/90 border border-border shadow-md opacity-0 group-hover/recent-offers:opacity-100 transition-all items-center justify-center text-foreground hover:bg-primary hover:text-white z-20"
                    aria-label="Scroll right"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="m9 18 6-6-6-6"></path></svg>
                </button>
            </div>
        </div>
    </div>
</section>
@endif
