<div class="bg-background py-10" x-data="{ 
    scrollLeft() { this.$refs.brandScroll.scrollBy({ left: -200, behavior: 'smooth' }) },
    scrollRight() { this.$refs.brandScroll.scrollBy({ left: 200, behavior: 'smooth' }) }
}">
    <div class="container mx-auto px-4">
        <h2 class="text-xl font-bold text-center mb-8 text-foreground">Popular Brands</h2>
        
        <div class="relative group">
            <div 
                x-ref="brandScroll"
                class="flex overflow-x-auto gap-6 py-4 scrollbar-hide items-center justify-start md:justify-center"
                style="scrollbar-width: none; ms-overflow-style: none;"
            >
                @php
                    $brands = [
                        ['id' => '1', 'name' => 'Gourmet Delights', 'logo' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=200&h=200&auto=format&fit=crop'],
                        ['id' => '2', 'name' => 'Tech Haven', 'logo' => 'https://images.unsplash.com/photo-1622748907213-7d9328be76bc?w=200&h=200&auto=format&fit=crop'],
                        ['id' => '3', 'name' => 'Wellness Spa', 'logo' => 'https://images.unsplash.com/photo-1560750588-73207b1ef5b8?w=200&h=200&auto=format&fit=crop'],
                        ['id' => '4', 'name' => 'Adventure Tours', 'logo' => 'https://images.unsplash.com/photo-1551632436-cbf8dd35adfa?w=200&h=200&auto=format&fit=crop'],
                        ['id' => '5', 'name' => 'Style Boutique', 'logo' => 'https://images.unsplash.com/photo-1589363360147-4f2d51541551?w=200&h=200&auto=format&fit=crop'],
                        ['id' => '6', 'name' => 'Fitness Pro', 'logo' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=200&h=200&auto=format&fit=crop'],
                        ['id' => '7', 'name' => 'Home Decor', 'logo' => 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?w=200&h=200&auto=format&fit=crop'],
                        ['id' => '8', 'name' => 'Bookworm', 'logo' => 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=200&h=200&auto=format&fit=crop'],
                    ];
                @endphp

                @foreach($brands as $brand)
                    <a 
                        href="{{ route('vendor-profile.show', ['vendorProfile' => $brand['id']]) }}"
                        class="flex-shrink-0 group/brand"
                    >
                        <div class="w-24 h-24 sm:w-28 sm:h-28 md:w-32 md:h-32 rounded-xl overflow-hidden border border-border bg-background shadow-sm hover:shadow-md transition-all duration-200 hover:scale-105">
                            <img 
                                src="{{ $brand['logo'] }}" 
                                alt="{{ $brand['name'] }}" 
                                class="w-full h-full object-cover" 
                                loading="lazy"
                            />
                        </div>
                        <p class="text-xs text-center mt-2 text-muted-foreground font-medium truncate max-w-[7rem] mx-auto group-hover/brand:text-primary transition-colors">{{ $brand['name'] }}</p>
                    </a>
                @endforeach
            </div>
            
            <button 
                @click="scrollLeft()"
                class="absolute left-0 top-1/2 -translate-y-1/2 h-8 w-8 rounded-full bg-background/90 border border-border shadow z-10 opacity-0 md:group-hover:opacity-100 transition-opacity flex items-center justify-center hover:bg-background"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="m15 18-6-6 6-6"></path></svg>
            </button>
            
            <button 
                @click="scrollRight()"
                class="absolute right-0 top-1/2 -translate-y-1/2 h-8 w-8 rounded-full bg-background/90 border border-border shadow z-10 opacity-0 md:group-hover:opacity-100 transition-opacity flex items-center justify-center hover:bg-background"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="m9 18 6-6-6-6"></path></svg>
            </button>
        </div>
    </div>
</div>
