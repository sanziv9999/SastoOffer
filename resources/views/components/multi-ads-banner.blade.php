@props(['categories'])

<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold mb-2">Discover Amazing Deals</h2>
            <p class="text-muted-foreground">Explore our curated collection of offers across all categories</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            @php
                $gradients = [
                    "from-primary/95 to-primary/60",
                    "from-blue-800/95 to-blue-600/60",
                    "from-orange-850/95 to-orange-600/60",
                    "from-green-800/95 to-green-600/60",
                    "from-pink-850/95 to-pink-600/60",
                    "from-purple-850/95 to-purple-600/60",
                ];

                $fallbackImages = [
                    'electronics' => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=600&auto=format',
                    'fashion' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=600&auto=format',
                    'food' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&auto=format',
                    'beauty' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=600&auto=format',
                    'tech' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=600&auto=format',
                    'generic' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=600&auto=format'
                ];
            @endphp

            @foreach($categories as $index => $category)
                @php
                    $gradient = $gradients[$index % count($gradients)];
                    $imageUrl = $category->image_url;
                @endphp
                <a 
                    href="{{ route('search', ['category' => $category->slug]) }}" 
                    class="group relative overflow-hidden rounded-xl aspect-[4/5] hover:scale-[1.03] transition-all duration-300 shadow-sm hover:shadow-xl border border-border/10 bg-white"
                >
                    @if($imageUrl)
                        {{-- Overlay-gradient --}}
                        <div class="absolute inset-x-0 bottom-0 h-[70%] bg-gradient-to-t {{ $gradient }} z-10 transition-opacity duration-300 group-hover:opacity-100"></div>
                        <div class="absolute inset-0 bg-black/10 group-hover:bg-black/0 transition-colors duration-300 z-0"></div>
                        
                        <img 
                            src="{{ $imageUrl }}" 
                            alt="{{ $category->name }}" 
                            class="w-full h-full object-cover transition-transform group-hover:scale-110 duration-700"
                        />
                        
                        <div class="absolute inset-0 z-20 flex flex-col justify-end p-5 text-white">
                            <h3 class="font-bold text-base lg:text-lg mb-1 leading-tight">{{ $category->name }}</h3>
                            <p class="text-xs lg:text-sm mb-4 leading-tight opacity-90 line-clamp-2">{{ $category->description ?: 'Explore the latest deals in ' . $category->name }}</p>
                            <span class="text-xs inline-flex items-center font-bold tracking-wide uppercase group-hover:translate-x-1 transition-transform duration-300">
                                Explore
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="ml-1.5 h-3.5 w-3.5"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                            </span>
                        </div>
                    @else
                        {{-- Design for categories without images --}}
                        <div class="absolute inset-0 bg-muted/20 group-hover:bg-muted/30 transition-colors duration-300"></div>
                        <div class="absolute top-4 left-4 h-1 w-12 bg-primary group-hover:w-20 transition-all duration-300"></div>
                        
                        <div class="absolute inset-0 z-20 flex flex-col justify-end p-5">
                            <h3 class="font-bold text-base lg:text-lg mb-1 leading-tight text-foreground">{{ $category->name }}</h3>
                            <p class="text-xs lg:text-sm mb-4 leading-tight text-muted-foreground line-clamp-2">{{ $category->description ?: 'Explore the latest deals in ' . $category->name }}</p>
                            <span class="text-xs inline-flex items-center font-bold tracking-wide uppercase text-primary group-hover:translate-x-1 transition-transform duration-300">
                                Explore
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="ml-1.5 h-3.5 w-3.5"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                            </span>
                        </div>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</section>
