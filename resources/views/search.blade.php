<x-layout>
    @section('title', $query ? "Search results for \"$query\" - SastoOffer" : "All Deals - SastoOffer")

    <div class="container py-8" 
        x-cloak
        x-data="{ 
            selectedCategories: @js($currentCategory !== 'all' && $currentCategory !== '' ? array_filter(array_map('trim', explode(',', $currentCategory))) : []),
            sortBy: '{{ addslashes($sortBy) }}',
            minPrice: {{ (int)$minPrice }},
            maxPrice: {{ (int)$maxPrice }},
            availableMinPrice: {{ (int)$availableMinPrice }},
            availableMaxPrice: {{ (int)$availableMaxPrice }},
            minRating: {{ $minRating ?: 'null' }},
            dealTypes: @js(($dealType !== 'all' && $dealType !== '') ? array_filter(array_map('trim', explode(',', $dealType))) : []),
            selectedLocations: @js(($currentLocation !== '') ? array_filter(array_map('trim', explode(',', $currentLocation))) : []),
            isFeatured: {{ $isFeatured ? 'true' : 'false' }},
            searchQuery: '{{ addslashes($query) }}',
            localSearchQuery: '{{ addslashes($query) }}',
            viewMode: '{{ $viewMode ?? 'grid' }}',
            resultsCount: {{ count($deals) }},
            isFilterDrawerOpen: false,
            isLoading: false,
            
            init() {
                this.$watch('selectedCategories', () => this.debouncedApplyFilters());
                this.$watch('sortBy', () => this.applyFilters());
                this.$watch('minPrice', () => this.debouncedApplyFilters());
                this.$watch('maxPrice', () => this.debouncedApplyFilters());
                this.$watch('dealTypes', () => this.debouncedApplyFilters());
                this.$watch('selectedLocations', () => this.debouncedApplyFilters());
                this.$watch('isFeatured', () => this.debouncedApplyFilters());
                this.$watch('localSearchQuery', () => this.debouncedApplyFilters());
                this.$watch('minRating', () => this.applyFilters());

                window.addEventListener('popstate', (e) => {
                    window.location.reload();
                });
            },

            debouncedApplyFilters() {
                clearTimeout(this.filterTimeout);
                this.filterTimeout = setTimeout(() => {
                    this.applyFilters();
                }, 400);
            },
            
            async applyFilters() {
                this.isLoading = true;
                let params = new URLSearchParams();
                
                if (this.localSearchQuery) params.set('q', this.localSearchQuery);
                if (this.viewMode !== 'grid') params.set('view', this.viewMode);
                if (this.selectedCategories.length > 0) params.set('category', this.selectedCategories.join(','));
                if (this.sortBy !== 'relevance') params.set('sort', this.sortBy);
                if (this.selectedLocations.length > 0) params.set('location', this.selectedLocations.join(','));
                if (this.isFeatured) params.set('featured', 'true');
                if (this.dealTypes.length > 0) params.set('type', this.dealTypes.join(','));
                if (this.minRating) params.set('minRating', this.minRating);
                
                let min = Math.min(this.minPrice, this.maxPrice);
                let max = Math.max(this.minPrice, this.maxPrice);
                if (min > this.availableMinPrice) params.set('minPrice', min);
                if (max < this.availableMaxPrice) params.set('maxPrice', max);

                const queryString = params.toString();
                const newUrl = window.location.pathname + (queryString ? '?' + queryString : '');
                
                try {
                    const response = await fetch(newUrl + (queryString ? '&' : '?') + 'partial=true');
                    const html = await response.text();
                    
                    const container = document.getElementById('search-results-container');
                    container.innerHTML = html;
                    
                    // Update dynamic results count
                    const meta = container.querySelector('#results-count-meta');
                    if (meta) {
                        this.resultsCount = parseInt(meta.dataset.count);
                    }
                    
                    // Re-initialize Alpine on the new content
                    if (window.Alpine) {
                        window.Alpine.initTree(container);
                    }
                    
                    window.history.pushState({ queryString }, '', newUrl);
                } catch (error) {
                    console.error('Filtering failed:', error);
                } finally {
                    this.isLoading = false;
                }
            },
            
            resetFilters() {
                this.selectedCategories = [];
                this.sortBy = 'relevance';
                this.minPrice = this.availableMinPrice;
                this.maxPrice = this.availableMaxPrice;
                this.dealTypes = [];
                this.selectedLocations = [];
                this.isFeatured = false;
                this.minRating = null;
                this.localSearchQuery = '';
                this.applyFilters();
            }
        }"
    >
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl md:text-3xl font-bold">
                {{ $query ? "Search results for \"$query\"" : "All Deals" }}
            </h1>
            
            <template x-if="searchQuery || selectedCategories.length > 0 || selectedLocations.length > 0 || isFeatured || dealTypes.length > 0 || minPrice > availableMinPrice || maxPrice < availableMaxPrice || minRating">
                <button 
                    @click="resetFilters" 
                    class="hidden md:flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 gap-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                    Clear Filters
                </button>
            </template>
        </div>

        {{-- Mobile Filters Drawer --}}
        <div 
            x-show="isFilterDrawerOpen"
            class="fixed inset-0 z-[100] bg-background/80 backdrop-blur-sm md:hidden"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="isFilterDrawerOpen = false"
            x-cloak
        >
            <div 
                class="fixed inset-y-0 left-0 z-[101] h-[100dvh] w-full sm:max-w-sm border-r bg-background p-6 shadow-xl transition-transform duration-300 ease-in-out flex flex-col"
                :class="isFilterDrawerOpen ? 'translate-x-0' : '-translate-x-full'"
                @click.stop
            >
                <div class="flex flex-col space-y-2 mb-4 shrink-0">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-primary">Filters</h2>
                        <button @click="isFilterDrawerOpen = false" class="p-2 rounded-full hover:bg-accent text-muted-foreground transition-all active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                            <span class="sr-only">Close</span>
                        </button>
                    </div>
                </div>
                
                <div class="flex-grow overflow-y-auto pr-2 -mr-2 scrollbar-hide overscroll-contain">
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
                        :min-rating="$minRating"
                        :is-mobile="true"
                    />
                </div>
                
                <div class="pt-6 mt-auto border-t bg-background shrink-0 space-y-3">
                    <button 
                        @click="isFilterDrawerOpen = false"
                        class="inline-flex items-center justify-center rounded-md text-sm font-bold transition-all bg-primary text-primary-foreground shadow hover:bg-primary/90 h-11 px-4 py-2 w-full active:scale-[0.98]"
                    >
                        Show <span class="mx-1 font-mono" x-text="resultsCount"></span> Results
                    </button>
                    <button 
                        @click="resetFilters"
                        class="inline-flex items-center justify-center rounded-md text-sm font-semibold transition-all border border-input bg-background hover:bg-accent h-11 px-4 py-2 w-full text-muted-foreground active:scale-[0.98]"
                        x-show="searchQuery || selectedCategories.length > 0 || selectedLocations.length > 0 || isFeatured || dealTypes.length > 0 || minPrice > availableMinPrice || maxPrice < availableMaxPrice"
                    >
                        Reset All Filters
                    </button>
                </div>
            </div>
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
                    :min-rating="$minRating"
                />
            </aside>
            
            {{-- Main Content --}}
            <main>
                {{-- Mobile Filter Controls --}}
                <div class="md:hidden mb-4 space-y-4">
                    
                    <div class="flex gap-2">
                        <div class="inline-flex items-center rounded-md border border-input p-1 bg-background">
                            <button
                                @click="viewMode = 'grid'; applyFilters()"
                                class="inline-flex items-center justify-center rounded-sm h-8 w-8 transition-colors"
                                :class="viewMode === 'grid' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'"
                                title="Grid view"
                                aria-label="Grid view"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="7" height="7" x="3" y="3" rx="1"></rect>
                                    <rect width="7" height="7" x="14" y="3" rx="1"></rect>
                                    <rect width="7" height="7" x="3" y="14" rx="1"></rect>
                                    <rect width="7" height="7" x="14" y="14" rx="1"></rect>
                                </svg>
                            </button>
                            <button
                                @click="viewMode = 'list'; applyFilters()"
                                class="inline-flex items-center justify-center rounded-sm h-8 w-8 transition-colors"
                                :class="viewMode === 'list' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'"
                                title="List view"
                                aria-label="List view"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="8" y1="6" x2="21" y2="6"></line>
                                    <line x1="8" y1="12" x2="21" y2="12"></line>
                                    <line x1="8" y1="18" x2="21" y2="18"></line>
                                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                </svg>
                            </button>
                        </div>

                        <button 
                            @click="isFilterDrawerOpen = true"
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
                    
                    <template x-if="searchQuery || selectedCategories.length > 0 || selectedLocations.length > 0 || isFeatured || dealTypes.length > 0 || minPrice > availableMinPrice || maxPrice < availableMaxPrice || minRating">
                        <button @click="resetFilters" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 w-full gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                            Clear All Filters
                        </button>
                    </template>
                </div>

                {{-- Dynamic Results Container --}}
                <div id="search-results-container" :class="isLoading ? 'opacity-50 pointer-events-none transition-opacity duration-150' : 'transition-opacity duration-150'">
                    @include('partials.deals-grid')
                </div>
            </main>
        </div>
    </div>

</div>
</x-layout>
