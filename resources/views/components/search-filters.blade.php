@props(['categories', 'currentCategory', 'minPrice', 'maxPrice', 'dealType', 'isFeatured', 'sortBy', 'isMobile' => false])

<div class="{{ $isMobile ? 'space-y-6' : 'bg-card shadow-sm rounded-lg p-5 sticky top-20 border' }}">
    @if(!$isMobile)
        <div class="mb-4">
            <h3 class="font-medium mb-2">Search</h3>
            <form @submit.prevent="applyFilters" class="flex">
                <input 
                    type="search" 
                    x-model="searchQuery"
                    placeholder="Search deals..." 
                    class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 rounded-r-none"
                />
                <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 w-9 rounded-l-none shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                </button>
            </form>
        </div>
        <div class="shrink-0 bg-border h-[1px] w-full my-4"></div>
    @endif

    {{-- Categories --}}
    <div x-data="{ open: true }" class="border-b">
        <button 
            @click="open = !open" 
            class="flex flex-1 items-center justify-between py-4 text-sm font-medium transition-all hover:underline [&[data-state=open]>svg]:rotate-180 w-full"
            :data-state="open ? 'open' : 'closed'"
        >
            Categories
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0 transition-transform duration-200"><path d="m6 9 6 6 6-6"></path></svg>
        </button>
        <div x-show="open" x-collapse class="pb-4 pt-0 text-sm">
            <div class="space-y-2">
                <div class="flex items-center space-x-2">
                    <input
                        type="radio"
                        id="{{ $isMobile ? 'm-' : '' }}all-categories"
                        name="{{ $isMobile ? 'm-' : '' }}category"
                        value="all"
                        x-model="selectedCategory"
                        class="h-4 w-4 rounded-full border border-primary text-primary shadow focus:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                    />
                    <label for="{{ $isMobile ? 'm-' : '' }}all-categories" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">All Categories</label>
                </div>
                @foreach($categories as $category)
                    <div class="flex items-center space-x-2">
                        <input
                            type="radio"
                            id="{{ $isMobile ? 'm-' : '' }}category-{{ $category['id'] }}"
                            name="{{ $isMobile ? 'm-' : '' }}category"
                            value="{{ $category['slug'] }}"
                            x-model="selectedCategory"
                            class="h-4 w-4 rounded-full border border-primary text-primary shadow focus:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        />
                        <label for="{{ $isMobile ? 'm-' : '' }}category-{{ $category['id'] }}" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">{{ $category['name'] }}</label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Price Range --}}
    <div x-data="{ open: true }" class="border-b">
        <button 
            @click="open = !open" 
            class="flex flex-1 items-center justify-between py-4 text-sm font-medium transition-all hover:underline [&[data-state=open]>svg]:rotate-180 w-full"
            :data-state="open ? 'open' : 'closed'"
        >
            Price Range
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0 transition-transform duration-200"><path d="m6 9 6 6 6-6"></path></svg>
        </button>
        <div x-show="open" x-collapse class="pb-4 pt-0 text-sm">
            <div class="space-y-4">
                <div class="relative w-full h-1.5 bg-secondary rounded-full mt-6">
                    <input 
                        type="range" 
                        min="0" 
                        max="1000" 
                        step="10" 
                        x-model="maxPrice"
                        class="absolute inset-0 w-full h-1.5 appearance-none bg-transparent pointer-events-none z-10 [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-background [&::-webkit-slider-thumb]:border [&::-webkit-slider-thumb]:border-primary [&::-webkit-slider-thumb]:shadow-sm [&::-webkit-slider-thumb]:cursor-pointer [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-background [&::-moz-range-thumb]:border [&::-moz-range-thumb]:border-primary [&::-moz-range-thumb]:cursor-pointer"
                    />
                    <div 
                        class="absolute inset-y-0 bg-primary rounded-full"
                        :style="`left: 0%; right: ${100 - (maxPrice / 10)}%`"
                    ></div>
                </div>
                <div class="flex items-center justify-between mt-2">
                    <div class="bg-muted px-2 py-1 rounded text-xs font-medium">
                        $0
                    </div>
                    <div class="bg-muted px-2 py-1 rounded text-xs font-medium">
                        $<span x-text="maxPrice"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Deal Type --}}
    <div x-data="{ open: true }" class="border-b">
        <button 
            @click="open = !open" 
            class="flex flex-1 items-center justify-between py-4 text-sm font-medium transition-all hover:underline [&[data-state=open]>svg]:rotate-180 w-full"
            :data-state="open ? 'open' : 'closed'"
        >
            Deal Type
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0 transition-transform duration-200"><path d="m6 9 6 6 6-6"></path></svg>
        </button>
        <div x-show="open" x-collapse class="pb-4 pt-0 text-sm">
            <div class="space-y-2">
                @foreach([
                    'all' => 'All Types',
                    'percentage' => 'Percentage Off',
                    'fixed' => 'Fixed Price',
                    'bogo' => 'Buy One Get One',
                    'bundle' => 'Bundle Deals'
                ] as $val => $label)
                    <div class="flex items-center space-x-2">
                        <input
                            type="radio"
                            id="{{ $isMobile ? 'm-' : '' }}{{ $val }}"
                            name="{{ $isMobile ? 'm-' : '' }}dealType"
                            value="{{ $val }}"
                            x-model="dealType"
                            class="h-4 w-4 rounded-full border border-primary text-primary shadow focus:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        />
                        <label for="{{ $isMobile ? 'm-' : '' }}{{ $val }}" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Options --}}
    <div x-data="{ open: true }" class="border-b">
        <button 
            @click="open = !open" 
            class="flex flex-1 items-center justify-between py-4 text-sm font-medium transition-all hover:underline [&[data-state=open]>svg]:rotate-180 w-full"
            :data-state="open ? 'open' : 'closed'"
        >
            Options
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0 transition-transform duration-200"><path d="m6 9 6 6 6-6"></path></svg>
        </button>
        <div x-show="open" x-collapse class="pb-4 pt-0 text-sm">
            <div class="space-y-2">
                <div class="flex items-center space-x-2">
                    <input
                        type="checkbox"
                        id="{{ $isMobile ? 'm-' : '' }}featured-only"
                        x-model="isFeatured"
                        class="peer h-4 w-4 shrink-0 rounded-sm border border-primary shadow focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground"
                    />
                    <label for="{{ $isMobile ? 'm-' : '' }}featured-only" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Featured Deals Only</label>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    @if(!$isMobile)
        <div class="flex flex-col gap-2 mt-6">
            <button 
                @click="applyFilters" 
                class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2 w-full"
            >
                Apply Filters
            </button>
            <template x-if="searchQuery || selectedCategory !== 'all' || isFeatured || dealType !== 'all' || minPrice > 0 || maxPrice < 1000">
                <button 
                    @click="resetFilters" 
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 w-full"
                >
                    Reset Filters
                </button>
            </template>
        </div>
    @endif
</div>
