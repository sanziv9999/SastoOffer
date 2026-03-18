@props(['categories', 'currentCategory', 'minPrice', 'maxPrice', 'dealType', 'isFeatured', 'sortBy', 'isMobile' => false])

<div
    class="{{ $isMobile ? 'space-y-6' : 'bg-card shadow-sm rounded-lg p-5 sticky top-20 border' }}"
    x-init="
        if (typeof selectedCategory === 'undefined') selectedCategory = @js($currentCategory);
        if (typeof sortBy === 'undefined') sortBy = @js($sortBy);
        if (typeof minPrice === 'undefined') minPrice = {{ (int) $minPrice }};
        if (typeof maxPrice === 'undefined') maxPrice = {{ (int) $maxPrice }};
        if (typeof availableMinPrice === 'undefined') availableMinPrice = {{ (int) $minPrice }};
        if (typeof availableMaxPrice === 'undefined') availableMaxPrice = {{ (int) $maxPrice }};
        if (typeof dealType === 'undefined') dealType = @js($dealType);
        if (typeof isFeatured === 'undefined') isFeatured = {{ $isFeatured ? 'true' : 'false' }};
        if (typeof searchQuery === 'undefined') searchQuery = '';
    "
>
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
            <div class="space-y-4 w-full">
                {{-- Price input fields --}}
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <input 
                            type="number" 
                            x-model.number="minPrice" 
                            @blur="
                                if(minPrice === null || minPrice === '') minPrice = availableMinPrice;
                                if(minPrice < availableMinPrice) minPrice = availableMinPrice;
                                if(minPrice > availableMaxPrice) minPrice = availableMaxPrice;
                            "
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        />
                    </div>
                    <div class="text-muted-foreground flex items-center">–</div>
                    <div class="relative flex-1">
                        <input 
                            type="number" 
                            x-model.number="maxPrice" 
                            @blur="
                                if(maxPrice === null || maxPrice === '') maxPrice = availableMaxPrice;
                                if(maxPrice > availableMaxPrice) maxPrice = availableMaxPrice;
                                if(maxPrice < availableMinPrice) maxPrice = availableMinPrice;
                            "
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        />
                    </div>
                </div>

                {{-- Dual Slider --}}
                <div class="relative w-full h-5 mt-2 flex items-center">
                    {{-- Track background --}}
                    <div class="absolute w-full h-1.5 bg-secondary rounded-full"></div>
                    {{-- Filled portion --}}
                    <div 
                        class="absolute h-1.5 bg-primary rounded-full pointer-events-none"
                        :style="`left: ${Math.max(0, Math.min(100, ((Math.min(minPrice, maxPrice) - availableMinPrice) / Math.max(1, (availableMaxPrice - availableMinPrice))) * 100))}%; right: ${Math.max(0, Math.min(100, 100 - ((Math.max(minPrice, maxPrice) - availableMinPrice) / Math.max(1, (availableMaxPrice - availableMinPrice))) * 100))}%`"
                    ></div>
                    
                    {{-- Min Range Input --}}
                    <input 
                        type="range" 
                        :min="availableMinPrice" 
                        :max="availableMaxPrice" 
                        step="1" 
                        x-model.number="minPrice"
                        @input="
                            if(minPrice < availableMinPrice) minPrice = availableMinPrice;
                            if(minPrice > availableMaxPrice) minPrice = availableMaxPrice;
                        "
                        class="absolute w-full h-1.5 appearance-none bg-transparent pointer-events-none 
                            [&::-webkit-slider-thumb]:pointer-events-auto
                            [&::-webkit-slider-thumb]:appearance-none 
                            [&::-webkit-slider-thumb]:w-4 
                            [&::-webkit-slider-thumb]:h-4 
                            [&::-webkit-slider-thumb]:rounded-full 
                            [&::-webkit-slider-thumb]:bg-primary 
                            [&::-webkit-slider-thumb]:border-2
                            [&::-webkit-slider-thumb]:border-primary-foreground 
                            [&::-webkit-slider-thumb]:shadow-md
                            [&::-moz-range-thumb]:pointer-events-auto
                            [&::-moz-range-thumb]:w-4 
                            [&::-moz-range-thumb]:h-4 
                            [&::-moz-range-thumb]:rounded-full 
                            [&::-moz-range-thumb]:bg-primary 
                            [&::-moz-range-thumb]:border-2
                            [&::-moz-range-thumb]:border-primary-foreground
                            focus:outline-none z-10"
                    />
                    
                    {{-- Max Range Input --}}
                    <input 
                        type="range" 
                        :min="availableMinPrice" 
                        :max="availableMaxPrice" 
                        step="1" 
                        x-model.number="maxPrice"
                        @input="
                            if(maxPrice > availableMaxPrice) maxPrice = availableMaxPrice;
                            if(maxPrice < availableMinPrice) maxPrice = availableMinPrice;
                        "
                        class="absolute w-full h-1.5 appearance-none bg-transparent pointer-events-none 
                            [&::-webkit-slider-thumb]:pointer-events-auto
                            [&::-webkit-slider-thumb]:appearance-none 
                            [&::-webkit-slider-thumb]:w-4 
                            [&::-webkit-slider-thumb]:h-4 
                            [&::-webkit-slider-thumb]:rounded-full 
                            [&::-webkit-slider-thumb]:bg-primary 
                            [&::-webkit-slider-thumb]:border-2
                            [&::-webkit-slider-thumb]:border-primary-foreground 
                            [&::-webkit-slider-thumb]:shadow-md
                            [&::-moz-range-thumb]:pointer-events-auto
                            [&::-moz-range-thumb]:w-4 
                            [&::-moz-range-thumb]:h-4 
                            [&::-moz-range-thumb]:rounded-full 
                            [&::-moz-range-thumb]:bg-primary 
                            [&::-moz-range-thumb]:border-2
                            [&::-moz-range-thumb]:border-primary-foreground
                            focus:outline-none z-20"
                    />
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
                    'percentage_discount' => 'Percentage Discount',
                    'fixed_amount_discount' => 'Fixed Amount Discount',
                    'bogo' => 'Buy One Get One',
                    'flash_sale' => 'Flash Sale',
                    'free_shipping' => 'Free Shipping',
                    'cashback' => 'Cashback Offer',
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

    {{-- Action Buttons removed for real-time filtering --}}
</div>
