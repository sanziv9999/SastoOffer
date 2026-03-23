@props(['categories', 'locations' => [], 'currentCategory', 'currentLocation' => '', 'minPrice', 'maxPrice', 'dealType', 'isFeatured', 'sortBy', 'isMobile' => false])

<div
    class="{{ $isMobile ? 'space-y-6' : 'bg-card shadow-sm rounded-lg p-5 sticky top-20 border' }}"
    x-init="
        if (typeof selectedCategories === 'undefined') selectedCategories = @js(($currentCategory !== 'all' && $currentCategory !== '') ? array_filter(array_map('trim', explode(',', $currentCategory))) : []);
        if (typeof sortBy === 'undefined') sortBy = @js($sortBy);
        if (typeof minPrice === 'undefined') minPrice = {{ (int) $minPrice }};
        if (typeof maxPrice === 'undefined') maxPrice = {{ (int) $maxPrice }};
        if (typeof availableMinPrice === 'undefined') availableMinPrice = {{ (int) $minPrice }};
        if (typeof availableMaxPrice === 'undefined') availableMaxPrice = {{ (int) $maxPrice }};
        if (typeof selectedLocations === 'undefined') selectedLocations = @js(($currentLocation !== '') ? array_filter(array_map('trim', explode(',', $currentLocation))) : []);
        if (typeof dealTypes === 'undefined') dealTypes = @js(($dealType !== 'all' && $dealType !== '') ? array_filter(array_map('trim', explode(',', $dealType))) : []);
        if (typeof isFeatured === 'undefined') isFeatured = {{ $isFeatured ? 'true' : 'false' }};
        if (typeof searchQuery === 'undefined') searchQuery = '';
    "
>
    {{-- Categories (hierarchical checkboxes) --}}
    <div x-data="{ open: true }" class="border-b">
        <button
            @click="open = !open"
            class="flex flex-1 items-center justify-between py-4 text-sm font-medium transition-all hover:underline w-full"
            :data-state="open ? 'open' : 'closed'"
        >
            Categories
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"></path></svg>
        </button>
        <div x-show="open" x-collapse class="pb-4 pt-0 text-sm">
            <div class="space-y-1">

                {{-- "All Categories" clears selection --}}
                <label class="flex items-center gap-2 py-1 cursor-pointer group">
                    <input
                        type="checkbox"
                        :checked="selectedCategories.length === 0"
                        @change="selectedCategories = []; debouncedApplyFilters()"
                        class="h-4 w-4 rounded border border-primary text-primary shadow focus:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    />
                    <span class="font-medium leading-none text-foreground group-hover:text-primary transition-colors">All Categories</span>
                </label>

                @foreach($categories as $category)
                    {{-- Parent category --}}
                    <div x-data="{ subOpen: selectedCategories.some(s => s === '{{ $category['slug'] }}' || {{ count($category['children']) > 0 ? '[' . implode(',', array_map(fn($c) => "'" . $c['slug'] . "'", $category['children'])) . ']' : '[]' }}.includes(s)) }" class="mt-1">
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 py-1 cursor-pointer group flex-1">
                                <input
                                    type="checkbox"
                                    value="{{ $category['slug'] }}"
                                    :checked="selectedCategories.includes('{{ $category['slug'] }}')"
                                    @change="
                                        if ($event.target.checked) {
                                            if (!selectedCategories.includes('{{ $category['slug'] }}')) selectedCategories.push('{{ $category['slug'] }}');
                                        } else {
                                            selectedCategories = selectedCategories.filter(s => s !== '{{ $category['slug'] }}');
                                        }
                                        debouncedApplyFilters();
                                    "
                                    class="h-4 w-4 rounded border border-primary text-primary shadow focus:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                />
                                <span class="font-medium leading-none text-foreground group-hover:text-primary transition-colors">{{ $category['name'] }}</span>
                            </label>
                            @if(count($category['children']) > 0)
                                <button @click="subOpen = !subOpen" class="p-1 text-muted-foreground hover:text-foreground transition-colors" type="button">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="subOpen ? 'rotate-180' : ''" class="transition-transform duration-150"><path d="m6 9 6 6 6-6"></path></svg>
                                </button>
                            @endif
                        </div>

                        {{-- Subcategories --}}
                        @if(count($category['children']) > 0)
                            <div x-show="subOpen" x-collapse class="ml-5 mt-1 space-y-1 border-l border-border pl-3">
                                @foreach($category['children'] as $sub)
                                    <label class="flex items-center gap-2 py-0.5 cursor-pointer group">
                                        <input
                                            type="checkbox"
                                            value="{{ $sub['slug'] }}"
                                            :checked="selectedCategories.includes('{{ $sub['slug'] }}')"
                                            @change="
                                                if ($event.target.checked) {
                                                    if (!selectedCategories.includes('{{ $sub['slug'] }}')) selectedCategories.push('{{ $sub['slug'] }}');
                                                } else {
                                                    selectedCategories = selectedCategories.filter(s => s !== '{{ $sub['slug'] }}');
                                                }
                                                debouncedApplyFilters();
                                            "
                                            class="h-3.5 w-3.5 rounded border border-primary/70 text-primary shadow focus:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                        />
                                        <span class="text-xs leading-none text-muted-foreground group-hover:text-foreground transition-colors">{{ $sub['name'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Price Range --}}
    <div x-data="{ open: true }" class="border-b">
        <button
            @click="open = !open"
            class="flex flex-1 items-center justify-between py-4 text-sm font-medium transition-all hover:underline w-full"
            :data-state="open ? 'open' : 'closed'"
        >
            Price Range
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"></path></svg>
        </button>
        <div x-show="open" x-collapse class="pb-4 pt-0 text-sm">
            <div class="space-y-4 w-full">
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
                    <div class="absolute w-full h-1.5 bg-secondary rounded-full"></div>
                    <div
                        class="absolute h-1.5 bg-primary rounded-full pointer-events-none"
                        :style="`left: ${Math.max(0, Math.min(100, ((Math.min(minPrice, maxPrice) - availableMinPrice) / Math.max(1, (availableMaxPrice - availableMinPrice))) * 100))}%; right: ${Math.max(0, Math.min(100, 100 - ((Math.max(minPrice, maxPrice) - availableMinPrice) / Math.max(1, (availableMaxPrice - availableMinPrice))) * 100))}%`"
                    ></div>
                    <input
                        type="range"
                        :min="availableMinPrice"
                        :max="availableMaxPrice"
                        step="1"
                        x-model.number="minPrice"
                        class="absolute w-full h-1.5 appearance-none bg-transparent pointer-events-none [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-primary [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-primary-foreground [&::-webkit-slider-thumb]:shadow-md [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-primary [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-primary-foreground focus:outline-none z-10"
                    />
                    <input
                        type="range"
                        :min="availableMinPrice"
                        :max="availableMaxPrice"
                        step="1"
                        x-model.number="maxPrice"
                        class="absolute w-full h-1.5 appearance-none bg-transparent pointer-events-none [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-primary [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-primary-foreground [&::-webkit-slider-thumb]:shadow-md [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-primary [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-primary-foreground focus:outline-none z-20"
                    />
                </div>
            </div>
        </div>
    </div>

    {{-- Deal Type --}}
    <div x-data="{ open: true }" class="border-b">
        <button
            @click="open = !open"
            class="flex flex-1 items-center justify-between py-4 text-sm font-medium transition-all hover:underline w-full"
            :data-state="open ? 'open' : 'closed'"
        >
            Deal Type
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"></path></svg>
        </button>
        <div x-show="open" x-collapse class="pb-4 pt-0 text-sm">
            <div class="space-y-1">
                {{-- All Types --}}
                <label class="flex items-center gap-2 py-1 cursor-pointer group">
                    <input
                        type="checkbox"
                        :checked="dealTypes.length === 0"
                        @change="dealTypes = []; debouncedApplyFilters()"
                        class="h-4 w-4 rounded border border-primary text-primary shadow focus:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    />
                    <span class="font-medium leading-none text-foreground group-hover:text-primary transition-colors">All Types</span>
                </label>
                @foreach([
                    'percentage-discount' => 'Percentage Discount',
                    'fixed-amount-discount' => 'Fixed Amount Discount',
                    'buy-one-get-one' => 'Buy One Get One',
                    'flash-sale' => 'Flash Sale',
                    'free-shipping' => 'Free Shipping',
                    'cashback-offer' => 'Cashback Offer',
                ] as $val => $label)
                    <label class="flex items-center gap-2 py-0.5 cursor-pointer group">
                        <input
                            type="checkbox"
                            value="{{ $val }}"
                            :checked="dealTypes.includes('{{ $val }}')"
                            @change="
                                if ($event.target.checked) {
                                    if (!dealTypes.includes('{{ $val }}')) dealTypes.push('{{ $val }}');
                                } else {
                                    dealTypes = dealTypes.filter(t => t !== '{{ $val }}');
                                }
                                debouncedApplyFilters();
                            "
                            class="h-4 w-4 rounded border border-primary text-primary shadow focus:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        />
                        <span class="text-sm font-medium leading-none text-foreground group-hover:text-primary transition-colors">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Location Filter --}}
    @if(count($locations) > 0)
    <div x-data="{ open: true, locationSearch: '' }" class="border-b">
        <button
            @click="open = !open"
            class="flex flex-1 items-center justify-between py-4 text-sm font-medium transition-all hover:underline w-full"
        >
            Location
            <div class="flex items-center gap-1.5">
                <template x-if="selectedLocations.length > 0">
                    <span class="inline-flex items-center rounded-full bg-primary/10 text-primary text-[10px] font-semibold px-1.5 py-0.5" x-text="selectedLocations.length"></span>
                </template>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"></path></svg>
            </div>
        </button>
        <div x-show="open" x-collapse class="pb-4 pt-0 text-sm">
            {{-- Inline search --}}
            <div class="relative mb-2">
                <input
                    type="search"
                    x-model="locationSearch"
                    placeholder="Search locations..."
                    class="w-full h-8 pl-7 pr-3 rounded-md border border-input bg-transparent text-xs placeholder:text-muted-foreground focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                />
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-2 top-1/2 -translate-y-1/2 text-muted-foreground h-3 w-3"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
            </div>
            <div class="space-y-1 max-h-48 overflow-y-auto pr-1">
                {{-- All Locations --}}
                <label class="flex items-center gap-2 py-0.5 cursor-pointer group" x-show="locationSearch === ''">
                    <input
                        type="checkbox"
                        :checked="selectedLocations.length === 0"
                        @change="selectedLocations = []; debouncedApplyFilters()"
                        class="h-4 w-4 rounded border border-primary text-primary shadow focus:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    />
                    <span class="font-medium leading-none text-foreground group-hover:text-primary transition-colors">All Locations</span>
                </label>
                @foreach($locations as $district)
                    <label
                        class="flex items-center gap-2 py-0.5 cursor-pointer group"
                        x-show="locationSearch === '' || '{{ strtolower($district) }}'.includes(locationSearch.toLowerCase())"
                    >
                        <input
                            type="checkbox"
                            value="{{ $district }}"
                            :checked="selectedLocations.includes('{{ addslashes($district) }}')"
                            @change="
                                if ($event.target.checked) {
                                    if (!selectedLocations.includes('{{ addslashes($district) }}')) selectedLocations.push('{{ addslashes($district) }}');
                                } else {
                                    selectedLocations = selectedLocations.filter(l => l !== '{{ addslashes($district) }}');
                                }
                                debouncedApplyFilters();
                            "
                            class="h-4 w-4 rounded border border-primary text-primary shadow focus:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        />
                        <span class="text-sm leading-none text-foreground group-hover:text-primary transition-colors">{{ $district }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Options --}}
    <div x-data="{ open: true }" class="border-b">
        <button
            @click="open = !open"
            class="flex flex-1 items-center justify-between py-4 text-sm font-medium transition-all hover:underline w-full"
            :data-state="open ? 'open' : 'closed'"
        >
            Options
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"></path></svg>
        </button>
        <div x-show="open" x-collapse class="pb-4 pt-0 text-sm">
            <div class="space-y-2">
                <label class="flex items-center gap-2 py-0.5 cursor-pointer group">
                    <input
                        type="checkbox"
                        id="{{ $isMobile ? 'm-' : '' }}featured-only"
                        x-model="isFeatured"
                        class="peer h-4 w-4 shrink-0 rounded border border-primary shadow focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    />
                    <span class="text-sm font-medium leading-none group-hover:text-primary transition-colors">Featured Deals Only</span>
                </label>
            </div>
        </div>
    </div>
</div>
