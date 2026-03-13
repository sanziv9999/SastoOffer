@props(['categories', 'currentCategory', 'minPrice', 'maxPrice', 'dealType', 'isFeatured', 'sortBy', 'isMobile' => false])

<div class="{{ $isMobile ? 'space-y-6' : 'bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden sticky top-32' }}">
    @if(!$isMobile)
        <div class="p-5 border-b border-slate-100">
            <h3 class="font-bold text-slate-800 mb-4">Search</h3>
            <form @submit.prevent="applyFilters" class="relative">
                <input 
                    type="text" 
                    x-model="searchQuery"
                    placeholder="Keywords..." 
                    class="w-full pl-4 pr-10 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                />
                <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                </button>
            </form>
        </div>
    @endif

    {{-- Filter Content --}}
    <div class="divide-y divide-slate-100">
        {{-- Categories --}}
        <div x-data="{ open: true }" class="filter-section">
            <button 
                @click="open = !open" 
                class="w-full flex items-center justify-between p-5 text-sm font-bold text-slate-800 hover:bg-slate-50 transition-colors"
            >
                Categories
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"></path></svg>
            </button>
            <div x-show="open" x-collapse x-cloak>
                <div class="px-5 pb-5 pt-0 space-y-2.5 text-sm md:text-xs lg:text-sm">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input 
                            type="radio" 
                            name="{{ $isMobile ? 'm_' : '' }}category_filter" 
                            value="all" 
                            x-model="selectedCategory"
                            @change="!{{ $isMobile ? 'false' : 'true' }} && applyFilters()"
                            class="w-4 h-4 text-primary border-slate-300 focus:ring-primary/20 cursor-pointer"
                        />
                        <span class="text-slate-600 group-hover:text-slate-900 transition-colors" :class="selectedCategory === 'all' ? 'font-medium text-slate-900' : ''">All Categories</span>
                    </label>
                    @foreach($categories as $category)
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input 
                                type="radio" 
                                name="{{ $isMobile ? 'm_' : '' }}category_filter" 
                                value="{{ $category['slug'] }}" 
                                x-model="selectedCategory"
                                @change="!{{ $isMobile ? 'false' : 'true' }} && applyFilters()"
                                class="w-4 h-4 text-primary border-slate-300 focus:ring-primary/20 cursor-pointer"
                            />
                            <span class="text-slate-600 group-hover:text-slate-900 transition-colors" :class="selectedCategory === '{{ $category['slug'] }}' ? 'font-medium text-slate-900' : ''">{{ $category['name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Price Range --}}
        <div x-data="{ open: true }" class="filter-section">
            <button 
                @click="open = !open" 
                class="w-full flex items-center justify-between p-5 text-sm font-bold text-slate-800 hover:bg-slate-50 transition-colors"
            >
                Price Range
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"></path></svg>
            </button>
            <div x-show="open" x-collapse x-cloak>
                <div class="px-5 pb-8 pt-0 space-y-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                            <span class="bg-slate-50 px-2 py-1 rounded border border-slate-100">$<span x-text="minPrice"></span></span>
                            <span class="bg-slate-50 px-2 py-1 rounded border border-slate-100">$<span x-text="maxPrice"></span></span>
                        </div>
                        <div class="relative h-2 bg-slate-100 rounded-full">
                            <input 
                                type="range" 
                                min="0" 
                                max="1000" 
                                step="10" 
                                x-model="maxPrice"
                                @change="!{{ $isMobile ? 'false' : 'true' }} && applyFilters()"
                                class="absolute inset-0 w-full h-2 appearance-none bg-transparent pointer-events-none z-10 [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-primary [&::-webkit-slider-thumb]:shadow-md [&::-webkit-slider-thumb]:cursor-pointer [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-primary [&::-moz-range-thumb]:border-none [&::-moz-range-thumb]:cursor-pointer"
                            />
                            <div 
                                class="absolute inset-y-0 bg-primary rounded-full transition-all duration-100"
                                :style="`left: 0%; right: ${100 - (maxPrice / 10)}%`"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Deal Type --}}
        <div x-data="{ open: true }" class="filter-section">
            <button 
                @click="open = !open" 
                class="w-full flex items-center justify-between p-5 text-sm font-bold text-slate-800 hover:bg-slate-50 transition-colors"
            >
                Deal Type
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"></path></svg>
            </button>
            <div x-show="open" x-collapse x-cloak>
                <div class="px-5 pb-5 pt-0 space-y-2.5 text-sm md:text-xs lg:text-sm">
                    @foreach([
                        'all' => 'All Types',
                        'percentage' => 'Percentage Off',
                        'fixed' => 'Fixed Price',
                        'bogo' => 'Buy One Get One',
                        'bundle' => 'Bundle Deals'
                    ] as $val => $label)
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input 
                                type="radio" 
                                name="{{ $isMobile ? 'm_' : '' }}deal_type" 
                                value="{{ $val }}" 
                                x-model="dealType"
                                @change="!{{ $isMobile ? 'false' : 'true' }} && applyFilters()"
                                class="w-4 h-4 text-primary border-slate-300 focus:ring-primary/20 cursor-pointer"
                            />
                            <span class="text-slate-600 group-hover:text-slate-900 transition-colors" :class="dealType === '{{ $val }}' ? 'font-medium text-slate-900' : ''">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Options --}}
        <div x-data="{ open: true }" class="filter-section">
            <button 
                @click="open = !open" 
                class="w-full flex items-center justify-between p-5 text-sm font-bold text-slate-800 hover:bg-slate-50 transition-colors"
            >
                Options
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"></path></svg>
            </button>
            <div x-show="open" x-collapse x-cloak>
                <div class="px-5 pb-5 pt-0">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input 
                            type="checkbox" 
                            x-model="isFeatured"
                            @change="!{{ $isMobile ? 'false' : 'true' }} && applyFilters()"
                            class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary/20 cursor-pointer"
                        />
                        <span class="text-sm md:text-xs lg:text-sm text-slate-600 group-hover:text-slate-900 transition-colors" :class="isFeatured ? 'font-medium text-slate-900' : ''">Featured Deals Only</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Apply/Reset buttons (Desktop Sidebar only) --}}
        @if(!$isMobile)
            <div class="p-5 space-y-2">
                <button 
                    @click="applyFilters" 
                    class="w-full py-2 bg-primary text-white rounded-lg text-sm font-bold hover:bg-primary/90 transition-colors shadow-sm"
                >
                    Apply Filters
                </button>
                @if($currentCategory !== 'all' || $sortBy !== 'relevance' || $isFeatured || $dealType !== 'all' || $minPrice > 0 || $maxPrice < 1000)
                    <button 
                        @click="resetFilters" 
                        class="w-full py-2 border border-slate-200 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors"
                    >
                        Reset Filters
                    </button>
                @endif
            </div>
        @endif
    </div>
</div>
