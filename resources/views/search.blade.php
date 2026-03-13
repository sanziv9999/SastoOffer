<x-layout>
    @section('title', $query ? "Search results for \"$query\" - SastoOffer" : "All Deals - SastoOffer")

    <div class="container mx-auto px-4 py-8" 
        x-data="{ 
            selectedCategory: '{{ addslashes($currentCategory) }}',
            sortBy: '{{ addslashes($sortBy) }}',
            minPrice: {{ (int)$minPrice }},
            maxPrice: {{ (int)$maxPrice }},
            dealType: '{{ addslashes($dealType) }}',
            isFeatured: {{ $isFeatured ? 'true' : 'false' }},
            searchQuery: '{{ addslashes($query) }}',
            
            applyFilters() {
                let params = new URLSearchParams();
                if (this.searchQuery) params.set('q', this.searchQuery);
                if (this.selectedCategory !== 'all') params.set('category', this.selectedCategory);
                if (this.sortBy !== 'relevance') params.set('sort', this.sortBy);
                if (this.isFeatured) params.set('featured', 'true');
                if (this.dealType !== 'all') params.set('type', this.dealType);
                if (this.minPrice > 0) params.set('minPrice', this.minPrice);
                if (this.maxPrice < 1000) params.set('maxPrice', this.maxPrice);
                
                window.location.search = params.toString();
            },
            
            resetFilters() {
                window.location.href = '{{ route('search') }}';
            }
        }"
    >
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-800">
                {{ $query ? "Search results for \"$query\"" : "All Deals" }}
            </h1>
            
            @if($query || $currentCategory !== 'all' || $sortBy !== 'relevance' || $isFeatured || $dealType !== 'all' || $minPrice > 0 || $maxPrice < 1000)
                <button 
                    @click="resetFilters" 
                    class="hidden md:flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-primary transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                    Clear Filters
                </button>
            @endif
        </div>
        
        <div class="flex flex-col md:flex-row gap-8">
            {{-- Sidebar - Desktop Filter --}}
            <aside class="hidden md:block w-full md:w-[280px] flex-shrink-0">
                <x-search-filters 
                    :categories="$categories" 
                    :current-category="$currentCategory"
                    :min-price="$minPrice"
                    :max-price="$maxPrice"
                    :deal-type="$dealType"
                    :is-featured="$isFeatured"
                    :sort-by="$sortBy"
                />
            </aside>
            
            {{-- Main Content --}}
            <main class="flex-grow min-w-0">
                {{-- Mobile Controls --}}
                <div class="md:hidden mb-6 space-y-4">
                    <form @submit.prevent="applyFilters" class="flex gap-2">
                        <div class="relative flex-grow">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                            </span>
                            <input 
                                type="text" 
                                x-model="searchQuery"
                                placeholder="Search deals..." 
                                class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                            />
                        </div>
                        <button type="submit" class="bg-primary text-white p-2 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                        </button>
                    </form>
                    
                    <div class="flex gap-2">
                        <button 
                            @click="$dispatch('open-mobile-filters')"
                            class="flex-1 flex items-center justify-center gap-2 py-2 border border-slate-200 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-slate-500"><path d="m4 6 8 8 8-8"></path><path d="m12 14v7"></path></svg>
                            Filters
                        </button>
                        
                        <div class="flex-1 relative" x-data="{ open: false }">
                            <button 
                                @click="open = !open"
                                class="w-full flex items-center justify-center gap-2 py-2 border border-slate-200 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-slate-500"><path d="m21 16-4 4-4-4"></path><path d="M17 20V4"></path><path d="m3 8 4-4 4 4"></path><path d="M7 4v16"></path></svg>
                                Sort
                            </button>
                            <div 
                                x-show="open" 
                                @click.away="open = false"
                                class="absolute right-0 top-full mt-2 w-48 bg-white border border-slate-200 rounded-lg shadow-lg z-20 py-1"
                                x-cloak
                            >
                                @foreach([
                                    'relevance' => 'Relevance',
                                    'newest' => 'Newest',
                                    'priceAsc' => 'Price: Low to High',
                                    'priceDesc' => 'Price: High to Low',
                                    'discountDesc' => 'Biggest Discount'
                                ] as $val => $label)
                                    <button 
                                        @click="sortBy = '{{ $val }}'; applyFilters(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50 transition-colors"
                                        :class="sortBy === '{{ $val }}' ? 'text-primary font-medium' : 'text-slate-600'"
                                    >
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Results Control - Desktop --}}
                <div class="hidden md:flex items-center justify-between mb-6">
                    <p class="text-slate-500 text-sm">
                        Showing <span class="font-medium text-slate-800">{{ count($deals) }}</span> {{ count($deals) === 1 ? 'deal' : 'deals' }} found
                    </p>
                    
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-slate-500">Sort by:</span>
                        <select 
                            x-model="sortBy" 
                            @change="applyFilters"
                            class="text-sm border-slate-200 rounded-lg focus:ring-primary/20 focus:border-primary outline-none py-1.5 pl-3 pr-8"
                        >
                            <option value="relevance">Relevance</option>
                            <option value="newest">Newest</option>
                            <option value="priceAsc">Price: Low to High</option>
                            <option value="priceDesc">Price: High to Low</option>
                            <option value="discountDesc">Biggest Discount</option>
                        </select>
                    </div>
                </div>

                {{-- Results Grid --}}
                @if(count($deals) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($deals as $deal)
                            <x-deal-card :deal="$deal" :featured="$deal['featured']" />
                        @endforeach
                    </div>
                @else
                    <div class="py-20 text-center bg-white border border-dashed border-slate-200 rounded-2xl">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-slate-50 mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                        </div>
                        <h2 class="text-xl font-bold text-slate-800 mb-2">No deals found</h2>
                        <p class="text-slate-500 mb-8 max-w-xs mx-auto">
                            We couldn't find any deals matching your current search or filter criteria.
                        </p>
                        <button 
                            @click="resetFilters"
                            class="bg-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-primary/90 transition-colors"
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
        class="fixed inset-0 z-[100] bg-black/50 transition-opacity md:hidden"
        :class="isOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'"
        @click="isOpen = false"
        x-cloak
    >
        <div 
            class="fixed inset-y-0 right-0 max-w-xs w-full bg-white shadow-xl z-[101] transition-transform duration-300 ease-in-out px-6 py-8 flex flex-col"
            :class="isOpen ? 'translate-x-0' : 'translate-x-full'"
            @click.stop
        >
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-xl font-bold text-slate-800">Filters</h2>
                <button @click="isOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                </button>
            </div>
            
            <div class="flex-grow overflow-y-auto pr-2 -mr-2 space-y-8">
                <x-search-filters 
                    :categories="$categories" 
                    :current-category="$currentCategory"
                    :min-price="$minPrice"
                    :max-price="$maxPrice"
                    :deal-type="$dealType"
                    :is-featured="$isFeatured"
                    :sort-by="$sortBy"
                    :is-mobile="true"
                />
            </div>
            
            <div class="pt-6 border-t border-slate-100 mt-auto flex gap-3">
                <button 
                    @click="applyFilters(); isOpen = false"
                    class="flex-1 bg-primary text-white py-3 rounded-lg font-bold shadow-lg shadow-primary/20 hover:bg-primary/90 transition-colors"
                >
                    Apply Filters
                </button>
                <button 
                    @click="resetFilters(); isOpen = false"
                    class="px-4 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors text-slate-600"
                >
                    Reset
                </button>
            </div>
        </div>
    </div>
</x-layout>
