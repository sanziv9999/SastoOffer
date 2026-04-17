@props(['categories'])

<section class="py-12 bg-transparent">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold mb-2">Discover Amazing Deals</h2>
                <p class="text-muted-foreground text-sm md:text-base">Explore our curated collection of offers across all categories</p>
            </div>
            <a href="{{ route('search') }}" class="hidden sm:flex items-center text-primary hover:underline text-sm font-semibold group/all">
                View all categories
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="ml-1.5 h-4 w-4 transition-transform group-hover/all:translate-x-1"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
            </a>
        </div>
        
        <div
            class="group/discover-slider"
            x-data="{ 
                scrollLeft() { this.$refs.catContainer.scrollBy({ left: -400, behavior: 'smooth' }) },
                scrollRight() { this.$refs.catContainer.scrollBy({ left: 400, behavior: 'smooth' }) }
            }"
        >
            <div class="relative">
                <div class="overflow-hidden">
                    <div 
                        x-ref="catContainer"
                        class="flex gap-4 pb-6 overflow-x-auto scrollbar-hide scroll-smooth" 
                        style="scrollbar-width: none; ms-overflow-style: none;"
                    >
                @php
                    $gradients = [
                        "from-violet-600 to-violet-900/40",
                        "from-blue-600 to-blue-900/40" ,
                        "from-orange-500 to-orange-700/40",
                        "from-green-600 to-green-900/40",
                        "from-rose-600 to-rose-900/40",
                        "from-indigo-600 to-indigo-900/40",
                    ];
                @endphp

                @foreach($categories as $index => $category)
                    @php
                        $gradient = $gradients[$index % count($gradients)];
                        $imageUrl = $category->image_url;
                    @endphp
                    <div class="flex-shrink-0 w-[180px] sm:w-[220px] md:w-[240px]">
                        <a 
                            href="{{ route('search', ['category' => $category->slug]) }}" 
                            class="group/category-card relative block overflow-hidden rounded-xl aspect-[4/5] hover:scale-[1.03] transition-all duration-300 shadow-sm hover:shadow-xl border border-border/10 bg-muted/20"
                        >
                            {{-- The bottom "coloured foreground" --}}
                            <div class="absolute inset-x-0 bottom-0 h-[65%] bg-gradient-to-t {{ $gradient }} group-hover/category-card:h-[75%] transition-all duration-500 z-10"></div>
                            
                            @if($imageUrl)
                                <img 
                                    src="{{ $imageUrl }}" 
                                    alt="{{ $category->name }}" 
                                    class="w-full h-full object-cover transition-transform group-hover/category-card:scale-110 duration-700 mix-blend-multiply opacity-80 group-hover/category-card:opacity-100"
                                />
                            @endif

                            {{-- Top decoration bar --}}
                            <div class="absolute top-4 left-4 h-1 w-10 bg-white/30 backdrop-blur-sm rounded-full z-20 group-hover/category-card:bg-white transition-all duration-300"></div>
                            
                            {{-- Common Content --}}
                            <div class="absolute inset-0 z-30 flex flex-col justify-end p-5 text-white">
                                <h3 class="font-bold text-base lg:text-lg mb-0.5 leading-tight">{{ $category->name }}</h3>
                                <p class="text-[10px] lg:text-xs mb-3 font-medium text-white/80 line-clamp-2 uppercase tracking-tight">{{ $category->description ?: 'Explore deals in ' . $category->name }}</p>
                                
                                <div class="overflow-hidden">
                                    <span class="text-[10px] sm:text-xs inline-flex items-center font-bold tracking-widest uppercase py-1 px-3 bg-white/10 backdrop-blur-md rounded-lg group-hover/category-card:bg-white group-hover/category-card:text-black transition-all duration-300">
                                        EXPLORE
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="ml-1.5 h-3 w-3"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

                </div>
                <button 
                    type="button"
                    @click.stop.prevent="scrollLeft()"
                    class="hidden md:flex absolute left-3 top-1/2 -translate-y-1/2 h-10 w-10 items-center justify-center bg-white shadow-xl rounded-full border border-border/50 text-foreground hover:bg-primary hover:text-white transition-all opacity-0 md:group-hover/discover-slider:opacity-100 z-[60]"
                    aria-label="Scroll left"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="m15 18-6-6 6-6"></path></svg>
                </button>
                <button 
                    type="button"
                    @click.stop.prevent="scrollRight()"
                    class="hidden md:flex absolute right-3 top-1/2 -translate-y-1/2 h-10 w-10 items-center justify-center bg-white shadow-xl rounded-full border border-border/50 text-foreground hover:bg-primary hover:text-white transition-all opacity-0 md:group-hover/discover-slider:opacity-100 z-[60]"
                    aria-label="Scroll right"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="m9 18 6-6-6-6"></path></svg>
                </button>
            </div>
        </div>
    </div>
</section>
