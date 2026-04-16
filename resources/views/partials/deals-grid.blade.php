<div id="search-results-grid">
    <div id="results-count-meta" class="hidden" data-count="{{ count($deals) }}"></div>
    {{-- Results Control - Desktop --}}
    <div class="hidden md:flex items-center justify-between mb-6">
        <div>
            <p class="text-sm text-muted-foreground">
                <span>{{ count($deals) }}</span> {{ count($deals) === 1 ? 'result' : 'results' }}
            </p>
        </div>
        
        <div class="flex items-center gap-3">
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

    {{-- Results Grid/List --}}
    @if(count($deals) > 0)
        @if(($viewMode ?? 'grid') === 'list')
            <div class="space-y-4">
                @foreach($deals as $deal)
                    <article class="group bg-white border border-slate-200 rounded-xl overflow-hidden hover:shadow-lg transition-all">
                        <div class="flex flex-col sm:flex-row">
                            <a href="{{ $deal['url'] ?? '#' }}" class="block relative sm:w-64 h-44 sm:h-auto bg-slate-100 overflow-hidden">
                                <img
                                    src="{{ $deal['image'] }}"
                                    alt="{{ $deal['title'] }}"
                                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                    loading="lazy"
                                />
                                @if(!empty($deal['featured']))
                                    <div class="absolute top-2 -left-8 z-20 w-28 -rotate-45 bg-red-600 text-white text-[10px] font-black py-1 text-center shadow-xl tracking-wider uppercase">
                                        Featured
                                    </div>
                                @endif
                            </a>

                            <div class="flex-1 p-4 flex flex-col gap-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold text-slate-500">{{ $deal['vendorName'] ?? 'Sasto Offer Vendor' }}</p>
                                        <a href="{{ $deal['url'] ?? '#' }}" class="block">
                                            <h3 class="text-lg font-bold text-slate-900 hover:text-primary transition-colors line-clamp-2">
                                                {{ $deal['title'] }}
                                            </h3>
                                        </a>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-xs text-slate-500">Starting at</p>
                                        <p class="text-xl font-black text-slate-900">Rs. {{ number_format((float) ($deal['discountedPrice'] ?? 0)) }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span class="inline-flex px-2 py-1 rounded bg-primary/5 text-primary font-semibold">
                                        {{ $deal['subcategoryName'] ?? $deal['categoryName'] ?? 'Uncategorized' }}
                                    </span>
                                    @if(!empty($deal['locationLabel']))
                                        <span class="text-slate-600">{{ $deal['locationLabel'] }}</span>
                                    @endif
                                    @if(!empty($deal['quantitySold']))
                                        <span class="text-slate-600">{{ (int) $deal['quantitySold'] }} sold</span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2 text-xs">
                                    @php
                                        $listRating = (float) ($deal['vendorRating'] ?? 0);
                                        $listReviewCount = (int) ($deal['vendorReviewCount'] ?? 0);
                                    @endphp
                                    <div class="flex items-center text-yellow-500">
                                        @for($i = 0; $i < 5; $i++)
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="{{ $listRating > $i ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2">
                                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path>
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="font-semibold text-slate-700">{{ number_format($listRating, 1) }}</span>
                                    <span class="text-slate-500">({{ $listReviewCount }})</span>
                                </div>

                                <div class="mt-auto flex items-center gap-2">
                                    <a
                                        href="{{ $deal['url'] ?? '#' }}"
                                        class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4"
                                    >
                                        View Deal
                                    </a>
                                    @if(!empty($deal['offerPivotId']))
                                        <button
                                            @click.prevent="cart.addItem({{ (int) $deal['offerPivotId'] }})"
                                            class="inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-background hover:bg-accent h-9 px-4"
                                        >
                                            Add to Cart
                                        </button>
                                    @endif
                                    <button
                                        @click.prevent="toggleWishlist({{ (int) $deal['id'] }})"
                                        class="inline-flex items-center justify-center rounded-md text-sm font-medium h-9 px-3 transition-colors duration-200 hover:bg-accent"
                                        :class="wishlistedIds.includes(Number({{ (int) $deal['id'] }})) ? 'text-destructive' : 'text-muted-foreground hover:text-foreground'"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="16" height="16"
                                            viewBox="0 0 24 24"
                                            :fill="wishlistedIds.includes(Number({{ (int) $deal['id'] }})) ? 'currentColor' : 'none'"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="mr-1.5 h-4 w-4 transition-all"
                                            :class="wishlistedIds.includes(Number({{ (int) $deal['id'] }})) ? 'fill-destructive scale-110' : ''"
                                        >
                                            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                                        </svg>
                                        <span x-text="wishlistedIds.includes(Number({{ (int) $deal['id'] }})) ? 'Saved' : 'Save'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($deals as $deal)
                    <article class="group relative bg-white rounded-xl border border-slate-200 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col">
                        <a href="{{ $deal['url'] ?? '#' }}" class="block relative h-48 overflow-hidden bg-slate-100">
                            <img
                                src="{{ $deal['image'] }}"
                                alt="{{ $deal['title'] }}"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                loading="lazy"
                            />
                            @if(!empty($deal['featured']))
                                <div class="absolute top-2 -left-8 z-20 w-28 -rotate-45 bg-red-600 text-white text-[10px] font-black py-1 text-center shadow-xl tracking-wider uppercase">
                                    Featured
                                </div>
                            @endif
                        </a>

                        <div class="absolute top-3 right-3 flex flex-col gap-2 z-20">
                            <button
                                @click.prevent="toggleWishlist({{ (int) $deal['id'] }})"
                                class="w-9 h-9 bg-white/95 backdrop-blur-sm rounded-full shadow-lg flex items-center justify-center text-slate-600 hover:text-red-500 transition-all transform active:scale-90"
                                title="Toggle wishlist"
                                aria-label="Toggle wishlist"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" :fill="wishlistedIds.includes({{ (int) $deal['id'] }}) ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" :class="wishlistedIds.includes({{ (int) $deal['id'] }}) ? 'text-red-500' : ''"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                            </button>
                            @if(!empty($deal['offerPivotId']))
                                <button
                                    @click.prevent="cart.addItem({{ (int) $deal['offerPivotId'] }})"
                                    class="w-9 h-9 bg-white/95 backdrop-blur-sm rounded-full shadow-lg flex items-center justify-center text-slate-600 hover:text-primary transition-all transform active:scale-90"
                                    title="Add to cart"
                                    aria-label="Add to cart"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                                </button>
                            @endif
                        </div>

                        <div class="p-4 flex flex-col flex-1 gap-2">
                            <p class="text-xs font-semibold text-slate-500">{{ $deal['vendorName'] ?? 'Sasto Offer Vendor' }}</p>
                            <a href="{{ $deal['url'] ?? '#' }}" class="block">
                                <h3 class="text-base font-bold text-slate-900 leading-tight line-clamp-2 hover:text-primary transition-colors">
                                    {{ $deal['title'] }}
                                </h3>
                            </a>

                            <div class="flex items-center gap-2 text-xs">
                                <span class="inline-flex px-2 py-1 rounded bg-primary/5 text-primary font-semibold">
                                    {{ $deal['subcategoryName'] ?? $deal['categoryName'] ?? 'Uncategorized' }}
                                </span>
                                @if(!empty($deal['locationLabel']))
                                    <span class="text-slate-600 truncate">{{ $deal['locationLabel'] }}</span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 text-xs mt-1">
                                @php
                                    $gridRating = (float) ($deal['vendorRating'] ?? 0);
                                    $gridReviewCount = (int) ($deal['vendorReviewCount'] ?? 0);
                                @endphp
                                <div class="flex items-center text-yellow-500">
                                    @for($i = 0; $i < 5; $i++)
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="{{ $gridRating > $i ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2">
                                            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path>
                                        </svg>
                                    @endfor
                                </div>
                                <span class="font-semibold text-slate-700">{{ number_format($gridRating, 1) }}</span>
                                <span class="text-slate-500">({{ $gridReviewCount }})</span>
                                @if(!empty($deal['quantitySold']))
                                    <span class="text-slate-500">{{ (int) $deal['quantitySold'] }} sold</span>
                                @endif
                            </div>

                            <div class="mt-auto pt-2 border-t border-slate-100">
                                <p class="text-xs text-slate-500">Starting at</p>
                                <p class="text-xl font-black text-slate-900">Rs. {{ number_format((float) ($deal['discountedPrice'] ?? 0)) }}</p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
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
</div>
