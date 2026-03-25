@props(['categories'])

<section class="py-12 bg-transparent">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold mb-2">Discover Amazing Deals</h2>
            <p class="text-muted-foreground">Explore our curated collection of offers across all categories</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
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
                <a 
                    href="{{ route('search', ['category' => $category->slug]) }}" 
                    class="group relative overflow-hidden rounded-xl aspect-[4/5] hover:scale-[1.03] transition-all duration-300 shadow-sm hover:shadow-xl border border-border/10 bg-muted/20"
                >
                    {{-- The bottom "coloured foreground" --}}
                    <div class="absolute inset-x-0 bottom-0 h-[65%] bg-gradient-to-t {{ $gradient }} group-hover:h-[75%] transition-all duration-500 z-10"></div>
                    
                    @if($imageUrl)
                        <img 
                            src="{{ $imageUrl }}" 
                            alt="{{ $category->name }}" 
                            class="w-full h-full object-cover transition-transform group-hover:scale-110 duration-700 mix-blend-multiply opacity-80 group-hover:opacity-100"
                        />
                    @endif

                    {{-- Top decoration bar --}}
                    <div class="absolute top-4 left-4 h-1 w-10 bg-white/30 backdrop-blur-sm rounded-full z-20 group-hover:bg-white transition-all duration-300"></div>
                    
                    {{-- Common Content --}}
                    <div class="absolute inset-0 z-30 flex flex-col justify-end p-5 text-white">
                        <h3 class="font-bold text-base lg:text-lg mb-0.5 leading-tight">{{ $category->name }}</h3>
                        <p class="text-[10px] lg:text-xs mb-3 font-medium text-white/80 line-clamp-2 uppercase tracking-tight">{{ $category->description ?: 'Explore deals in ' . $category->name }}</p>
                        
                        <div class="overflow-hidden">
                            <span class="text-[10px] sm:text-xs inline-flex items-center font-bold tracking-widest uppercase py-1 px-3 bg-white/10 backdrop-blur-md rounded-lg group-hover:bg-white group-hover:text-black transition-all duration-300">
                                EXPLORE
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="ml-1.5 h-3 w-3"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
