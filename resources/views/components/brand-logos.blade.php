@props(['vendors' => []])

<div class="bg-transparent py-12 border-y border-border/20" x-data="{ 
    scrollLeft() { this.$refs.brandScroll.scrollBy({ left: -240, behavior: 'smooth' }) },
    scrollRight() { this.$refs.brandScroll.scrollBy({ left: 240, behavior: 'smooth' }) }
}">
    <div class="container mx-auto px-4">
        <h2 class="text-xl font-bold text-center mb-8 text-foreground tracking-tight uppercase">Top Rated Vendors</h2>
        
        <div class="group">
            <div class="grid grid-cols-[auto_1fr_auto] items-center gap-3">
                <div class="hidden md:flex justify-start">
                    <button 
                        @click="scrollLeft()"
                        class="h-10 w-10 rounded-full bg-background/90 border border-border shadow opacity-0 md:group-hover:opacity-100 transition-all flex items-center justify-center text-foreground hover:bg-primary hover:text-white"
                        aria-label="Scroll left"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="m15 18-6-6 6-6"></path></svg>
                    </button>
                </div>

                <div class="overflow-hidden">
                    <div 
                        x-ref="brandScroll"
                        class="flex overflow-x-auto gap-6 sm:gap-8 py-4 scrollbar-hide items-center justify-start md:justify-center px-4"
                        style="scrollbar-width: none; ms-overflow-style: none;"
                    >
                        @foreach($vendors as $vendor)
                    @php
                        $logoImg = $vendor->images->where('attribute_name', 'logo')->first();
                        $logoUrl = $logoImg ? $logoImg->image_url : null;
                    @endphp
                    <a 
                        href="{{ route('vendor-profile.show', ['vendorProfile' => $vendor->slug]) }}"
                        class="flex-shrink-0 group/brand flex flex-col items-center"
                    >
                        <div class="w-24 h-24 sm:w-28 sm:h-28 md:w-32 md:h-32 rounded-2xl overflow-hidden border border-border/80 bg-white shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 relative ring-primary/0 hover:ring-2 hover:ring-primary/20">
                            @if($logoUrl)
                                <img 
                                    src="{{ $logoUrl }}" 
                                    alt="{{ $vendor->business_name }}" 
                                    class="w-full h-full object-cover transition-transform group-hover/brand:scale-110 duration-500" 
                                    loading="lazy"
                                />
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 text-slate-400">
                                    <span class="text-3xl font-bold">{{ substr($vendor->business_name, 0, 1) }}</span>
                                </div>
                            @endif
                            
                            {{-- Rating badge on logo --}}
                            @if($vendor->reviews_avg_rating)
                                <div class="absolute bottom-1 right-1 bg-white/95 backdrop-blur-sm rounded-lg px-1.5 py-0.5 shadow-sm border border-border/50 flex items-center gap-0.5 z-10">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="currentColor" class="text-yellow-400"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                                    <span class="text-[10px] font-bold text-slate-800">{{ number_format($vendor->reviews_avg_rating, 1) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="text-center mt-3">
                            <p class="text-xs font-bold text-foreground truncate max-w-[7rem] mx-auto group-hover/brand:text-primary transition-colors">{{ $vendor->business_name }}</p>
                            <p class="text-[10px] text-muted-foreground font-medium uppercase tracking-wider">{{ $vendor->primaryCategory->name ?? 'Vendor' }}</p>
                        </div>
                    </a>
                        @endforeach
                    </div>
                </div>

                <div class="hidden md:flex justify-end">
                    <button 
                        @click="scrollRight()"
                        class="h-10 w-10 rounded-full bg-background/90 border border-border shadow opacity-0 md:group-hover:opacity-100 transition-all flex items-center justify-center text-foreground hover:bg-primary hover:text-white"
                        aria-label="Scroll right"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="m9 18 6-6-6-6"></path></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
