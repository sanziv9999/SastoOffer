@php
    $sortByOptions = [
        'relevance' => 'Relevance',
        'newest' => 'Newest',
        'priceAsc' => 'Price: Low to High',
        'priceDesc' => 'Price: High to Low',
        'discountDesc' => 'Biggest Discount',
        'endingSoon' => 'Ending Soon',
    ];
@endphp

<x-layout>
    @section('title', $query ? "Search results for \"$query\" - SastoOffer" : "All Deals - SastoOffer")

    <div class="container py-8" 
        x-data="{ 
            selectedCategories: @js($currentCategory !== 'all' && $currentCategory !== '' ? array_filter(array_map('trim', explode(',', $currentCategory))) : []),
            sortBy: '{{ addslashes($sortBy) }}',
            minPrice: {{ (int)$minPrice }},
            maxPrice: {{ (int)$maxPrice }},
            availableMinPrice: {{ (int)$availableMinPrice }},
            availableMaxPrice: {{ (int)$availableMaxPrice }},
            dealTypes: @js(($dealType !== 'all' && $dealType !== '') ? array_filter(array_map('trim', explode(',', $dealType))) : []),
            selectedLocations: @js(($currentLocation !== '') ? array_filter(array_map('trim', explode(',', $currentLocation))) : []),
            isFeatured: {{ $isFeatured ? 'true' : 'false' }},
            searchQuery: '{{ addslashes($query) }}',
            
            init() {
                this.$watch('selectedCategories', () => this.debouncedApplyFilters());
                this.$watch('sortBy', () => this.applyFilters());
                this.$watch('minPrice', () => this.debouncedApplyFilters());
                this.$watch('maxPrice', () => this.debouncedApplyFilters());
                this.$watch('dealTypes', () => this.debouncedApplyFilters());
                this.$watch('selectedLocations', () => this.debouncedApplyFilters());
                this.$watch('isFeatured', () => this.debouncedApplyFilters());
                this.$watch('searchQuery', () => this.debouncedApplyFilters());
            },

            debouncedApplyFilters() {
                clearTimeout(this.filterTimeout);
                this.filterTimeout = setTimeout(() => {
                    this.applyFilters();
                }, 500);
            },
            
            applyFilters() {
                let params = new URLSearchParams(window.location.search);
                
                if (this.searchQuery) params.set('q', this.searchQuery);
                else params.delete('q');

                if (this.selectedCategories.length > 0) params.set('category', this.selectedCategories.join(','));
                else params.delete('category');

                if (this.sortBy !== 'relevance') params.set('sort', this.sortBy);
                else params.delete('sort');

                if (this.selectedLocations.length > 0) params.set('location', this.selectedLocations.join(','));
                else params.delete('location');

                if (this.isFeatured) params.set('featured', 'true');
                else params.delete('featured');

                if (this.dealTypes.length > 0) params.set('type', this.dealTypes.join(','));
                else params.delete('type');
                
                let min = Math.min(this.minPrice, this.maxPrice);
                let max = Math.max(this.minPrice, this.maxPrice);
                
                if (min > this.availableMinPrice) params.set('minPrice', min);
                else params.delete('minPrice');

                if (max < this.availableMaxPrice) params.set('maxPrice', max);
                else params.delete('maxPrice');

                const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                if (window.location.href !== window.location.origin + newUrl) {
                    window.location.href = newUrl;
                }
            },
            
            resetFilters() {
                const params = new URLSearchParams(window.location.search);
                const query = params.get('q');
                const city = params.get('city');
                const district = params.get('district');

                let nextParams = new URLSearchParams();
                if (query) nextParams.set('q', query);
                if (city) nextParams.set('city', city);
                if (district) nextParams.set('district', district);

                window.location.href = '{{ route('search') }}' + (nextParams.toString() ? '?' + nextParams.toString() : '');
            }
        }"
    >
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl md:text-3xl font-bold">
                {{ $query ? "Search results for \"$query\"" : "All Deals" }}
            </h1>
            
            <template x-if="searchQuery || selectedCategories.length > 0 || selectedLocations.length > 0 || isFeatured || dealTypes.length > 0 || minPrice > availableMinPrice || maxPrice < availableMaxPrice">
                <button 
                    @click="resetFilters" 
                    class="hidden md:flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 gap-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                    Clear Filters
                </button>
            </template>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-[250px_1fr] gap-8">
            {{-- Sidebar - Desktop Filter --}}
            <aside class="hidden md:block">
                <x-search-filters 
                    :categories="$categories"
                    :locations="$locations"
                    :current-category="$currentCategory"
                    :current-location="$currentLocation"
                    :min-price="$minPrice"
                    :max-price="$maxPrice"
                    :deal-type="$dealType"
                    :is-featured="$isFeatured"
                    :sort-by="$sortBy"
                />
            </aside>
            
            {{-- Main Content --}}
            <main>
                {{-- Mobile Filter Controls --}}
                <div class="md:hidden mb-4 space-y-4">
                    
                    <div class="flex gap-2">
                        <button 
                            @click="$dispatch('open-mobile-filters')"
                            class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 flex-1"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4"><path d="M20 7H4"/><path d="M16 12H8"/><path d="M12 17H12"/></svg>
                            Filters
                        </button>
                        
                        <div class="flex-1 relative" x-data="{ open: false }">
                            <button 
                                @click="open = !open"
                                class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 w-full"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg>
                                Sort
                            </button>
                            <div 
                                x-show="open" 
                                @click.away="open = false"
                                class="absolute right-0 top-full mt-2 w-48 z-50 overflow-hidden rounded-md border bg-popover p-1 text-popover-foreground shadow-md transition-all outline-none"
                                x-cloak
                            >
                                @foreach($sortByOptions as $val => $label)
                                    <button 
                                        @click="sortBy = '{{ $val }}'; applyFilters(); open = false"
                                        class="relative flex w-full cursor-default select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground"
                                        :class="sortBy === '{{ $val }}' ? 'bg-accent text-accent-foreground' : ''"
                                    >
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <template x-if="searchQuery || selectedCategory !== 'all' || isFeatured || dealType !== 'all' || minPrice > availableMinPrice || maxPrice < availableMaxPrice">
                        <button @click="resetFilters" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 w-full gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                            Clear All Filters
                        </button>
                    </template>
                </div>

                {{-- Results Control - Desktop --}}
                <div class="hidden md:flex items-center justify-between mb-6">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            <span x-text="{{ count($deals) }}"></span> {{ count($deals) === 1 ? 'result' : 'results' }}
                        </p>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <select 
                            x-model="sortBy" 
                            @change="applyFilters"
                            class="flex h-9 w-48 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            @foreach($sortByOptions as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Results Grid --}}
                @if(count($deals) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($deals as $deal)
                            <x-deal-card :deal="$deal" :featured="$deal['featured']" />
                        @endforeach
                    </div>
                @else
                    <div class="flex min-h-[400px] flex-col items-center justify-center rounded-md border border-dashed p-8 text-center animate-in fade-in-50">
                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-10 w-10 text-muted-foreground"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                        </div>
                        <h2 class="mt-6 text-xl font-semibold">No deals found</h2>
                        <p class="mb-8 mt-2 text-center text-sm font-normal leading-6 text-muted-foreground max-w-sm">
                            Try adjusting your search or filter criteria
                        </p>
                        <button 
                            @click="resetFilters"
                            class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2"
                        >
                            Reset All Filters
                        </button>
                    </div>
                @endif
            </main>
        </div>
    </div>

    {{-- Mobile Filters Drawer --}}
    <div 
        x-data="{ isOpen: false }" 
        x-on:open-mobile-filters.window="isOpen = true"
        x-show="isOpen"
        class="fixed inset-0 z-50 bg-background/80 backdrop-blur-sm md:hidden"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="isOpen = false"
        x-cloak
    >
        <div 
            class="fixed inset-y-0 left-0 z-50 h-full w-full sm:max-w-sm border-r bg-background p-6 shadow-lg transition-transform duration-300 ease-in-out flex flex-col"
            :class="isOpen ? 'translate-x-0' : '-translate-x-full'"
            @click.stop
        >
            <div class="flex flex-col space-y-2 mb-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Filters</h2>
                    <button @click="isOpen = false" class="rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                        <span class="sr-only">Close</span>
                    </button>
                </div>
                <p class="text-sm text-muted-foreground">
                    Narrow down deals to find exactly what you're looking for.
                </p>
            </div>
            
            <div class="flex-grow overflow-y-auto pr-2 -mr-2">
                <x-search-filters 
                    :categories="$categories"
                    :locations="$locations"
                    :current-category="$currentCategory"
                    :current-location="$currentLocation"
                    :min-price="$minPrice"
                    :max-price="$maxPrice"
                    :deal-type="$dealType"
                    :is-featured="$isFeatured"
                    :sort-by="$sortBy"
                    :is-mobile="true"
                />
            </div>
            
            <div class="pt-6 mt-auto">
                <button 
                    @click="isOpen = false"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-11 px-4 py-2 w-full"
                >
                    Show <span class="mx-1" x-text="{{ count($deals) }}"></span> Results
                </button>
            </div>
        </div>
    </div>
</x-layout>
