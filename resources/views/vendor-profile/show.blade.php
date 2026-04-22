<x-layout>
    @section('title', $vendor->business_name . ' - SastoOffer')

    @php
        $logoUrl = $logo ?? 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800&auto=format&fit=crop&q=60';
        $coverUrl = $cover ?? 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&auto=format&fit=crop&q=60';
        
        $displayAddress = $vendor->defaultAddress 
            ? "{$vendor->defaultAddress->tole}, {$vendor->defaultAddress->municipality}, {$vendor->defaultAddress->district}"
            : 'No address provided';
            
        $businessHours = $vendor->business_hours ?? [];
    @endphp

    <div class="min-h-screen pb-12">
        <div class="max-w-6xl mx-auto px-4 relative pt-4 md:pt-6">
            @php
                $activeDeals = $deals?->where('status', 'active')->values() ?? collect();
                $vendorLat = $vendor->defaultAddress->latitude ?? null;
                $vendorLng = $vendor->defaultAddress->longitude ?? null;
                $hasCoords = $vendorLat !== null && $vendorLng !== null && $vendorLat !== '' && $vendorLng !== '';
            @endphp

            <div class="mb-3">
                <button
                    type="button"
                    onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href='{{ route('home') }}'; }"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3"
                >
                    Go Back
                </button>
            </div>

            {{-- Compact vendor header --}}
            <div class="mb-4">
                <div class="bg-card rounded-xl shadow-sm border p-4 md:p-5">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-start gap-3">
                            <div class="w-14 h-14 rounded-lg overflow-hidden border bg-white shrink-0">
                                <img
                                    src="{{ $logoUrl }}"
                                    alt="{{ $vendor->business_name }} logo"
                                    class="w-full h-full object-cover"
                                />
                            </div>

                            <div class="min-w-0 flex-1">
                                <h1 class="text-lg md:text-xl font-semibold tracking-tight leading-tight">{{ $vendor->business_name }}</h1>
                                <div class="mt-1.5 flex flex-wrap items-center gap-x-2.5 gap-y-1 text-xs md:text-sm text-muted-foreground">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-yellow-500"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        <span class="font-medium text-foreground">{{ number_format($vendor->reviews_avg_rating ?? 0, 1) }}</span>
                                        <span>({{ $vendor->reviews_count ?? 0 }} reviews)</span>
                                    </span>
                                    @if($vendor->primaryCategory)
                                        <span class="h-1 w-1 rounded-full bg-border"></span>
                                        <span>{{ $vendor->primaryCategory->name }}</span>
                                    @endif
                                    <span class="h-1 w-1 rounded-full bg-border"></span>
                                    <span>{{ $activeDeals->count() }} active offers</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Tab navigation --}}
            <div class="mb-3 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="inline-flex w-full md:w-auto items-center rounded-lg border bg-muted/40 p-1 gap-1" role="tablist" aria-label="Vendor profile tabs">
                    <button
                        type="button"
                        class="vendor-tab-btn rounded-md px-4 py-2.5 text-sm font-medium transition-colors bg-background text-foreground shadow-sm"
                        role="tab"
                        aria-selected="true"
                        aria-controls="tab-deals"
                        data-tab="deals"
                    >
                        Deals
                    </button>
                    <button
                        type="button"
                        class="vendor-tab-btn rounded-md px-4 py-2.5 text-sm font-medium transition-colors text-muted-foreground hover:text-foreground"
                        role="tab"
                        aria-selected="false"
                        aria-controls="tab-about"
                        data-tab="about"
                    >
                        About
                    </button>
                    <button
                        type="button"
                        class="vendor-tab-btn rounded-md px-4 py-2.5 text-sm font-medium transition-colors text-muted-foreground hover:text-foreground"
                        role="tab"
                        aria-selected="false"
                        aria-controls="tab-reviews"
                        data-tab="reviews"
                    >
                        Reviews
                    </button>
                </div>
                <div id="vendor-store-search-desktop-wrap" class="hidden lg:block w-full lg:w-[320px]">
                    <label for="vendor-store-search" class="sr-only">Search in Store</label>
                    <div class="relative">
                        <input
                            id="vendor-store-search"
                            type="search"
                            placeholder="Search in Store"
                            class="w-full rounded-md border border-input bg-background py-2 pl-10 pr-3 text-sm outline-none focus:ring-2 focus:ring-ring"
                        />
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                </div>
            </div>

            <div id="vendor-store-search-mobile-wrap" class="lg:hidden sticky top-16 z-30 -mx-4 px-4 py-2 mb-3 bg-background border-y border-border/60 shadow-sm">
                <label for="vendor-store-search-mobile" class="sr-only">Search in Store</label>
                <div class="relative">
                    <input
                        id="vendor-store-search-mobile"
                        type="search"
                        placeholder="Search in Store"
                        class="w-full rounded-lg border border-input bg-background py-2.5 pl-10 pr-3 text-sm outline-none focus:ring-2 focus:ring-ring"
                    />
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
            </div>

            {{-- Deals tab --}}
            <section id="tab-deals" class="vendor-tab-panel opacity-100 translate-y-0 transition-all duration-300 ease-out" role="tabpanel">
                @if($activeDeals->isEmpty())
                    <div class="bg-card rounded-xl border border-dashed p-12 text-center">
                        <div class="bg-muted rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                        </div>
                        <h3 class="font-medium text-lg">No active offers right now</h3>
                        <p class="text-muted-foreground mb-6">This vendor does not have active listings at the moment.</p>
                        <a href="/search" class="text-primary hover:underline font-medium">Browse other deals</a>
                    </div>
                @else
                    <div id="vendor-products-grid" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-4">
                        @foreach($activeDeals as $deal)
                            @php
                                $searchText = strtolower(trim(($deal['title'] ?? '') . ' ' . ($deal['categoryName'] ?? '') . ' ' . ($deal['offerTypeTitle'] ?? '')));
                            @endphp
                            <article class="vendor-product-item" data-search="{{ $searchText }}">
                                <x-deal-card :deal="$deal" :featured="false" :compact="true" />
                            </article>
                        @endforeach
                    </div>
                    <p id="vendor-products-empty-search" class="hidden text-sm text-muted-foreground mt-4">
                        No matching products/services found.
                    </p>
                @endif
            </section>

            {{-- Profile / About tab --}}
            <section id="tab-about" class="vendor-tab-panel hidden opacity-0 translate-y-2 transition-all duration-300 ease-out" role="tabpanel">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-card rounded-xl border p-5 md:p-6">
                            <h2 class="text-xl font-semibold tracking-tight mb-3">About {{ $vendor->business_name }}</h2>
                            <p class="text-sm leading-relaxed text-muted-foreground">
                                {{ $vendor->description ?: 'No description provided yet.' }}
                            </p>
                        </div>

                        <div class="bg-card rounded-xl border p-5 md:p-6">
                            <h3 class="text-sm font-semibold mb-4">Contact &amp; Business Info</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-xs uppercase tracking-wider text-muted-foreground font-semibold mb-1">Address</p>
                                    <p class="font-medium">{{ $displayAddress }}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wider text-muted-foreground font-semibold mb-1">Phone</p>
                                    <p class="font-medium">{{ $vendor->public_phone ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wider text-muted-foreground font-semibold mb-1">Email</p>
                                    <p class="font-medium break-words">{{ $vendor->public_email ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wider text-muted-foreground font-semibold mb-1">Website</p>
                                    @if($vendor->website_url)
                                        <a href="{{ $vendor->website_url }}" target="_blank" class="font-medium text-primary hover:underline break-all">{{ $vendor->website_url }}</a>
                                    @else
                                        <p class="font-medium">N/A</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="bg-card rounded-xl border p-5 md:p-6">
                            <h3 class="text-sm font-semibold mb-4">Business Hours</h3>
                            <div class="space-y-2">
                                @if(is_array($businessHours) && count($businessHours) > 0)
                                    @foreach($businessHours as $hour)
                                        <div class="flex justify-between text-sm gap-3">
                                            <span class="text-muted-foreground">{{ $hour['day'] }}</span>
                                            <span class="font-medium text-right">
                                                @if($hour['is_closed'])
                                                    <span class="text-destructive">Closed</span>
                                                @else
                                                    {{ $hour['open'] }} - {{ $hour['close'] }}
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-sm text-muted-foreground italic">Business hours not provided.</p>
                                @endif
                            </div>
                        </div>

                    </div>

                    <aside class="lg:col-span-1">
                        <div class="bg-card rounded-xl border overflow-hidden">
                            <div class="h-56 bg-muted relative">
                                @if($hasCoords)
                                    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
                                    <div id="vendor-osm-map" class="absolute inset-0"></div>

                                    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function () {
                                            var lat = parseFloat({{ $vendorLat }});
                                            var lng = parseFloat({{ $vendorLng }});
                                            if (Number.isNaN(lat) || Number.isNaN(lng)) return;

                                            var map = L.map('vendor-osm-map', { zoomControl: true }).setView([lat, lng], 13);
                                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                                            }).addTo(map);

                                            L.marker([lat, lng]).addTo(map).bindPopup(@json($vendor->business_name ?? 'Vendor'));
                                        });
                                    </script>
                                @else
                                    <div class="text-center p-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-10 w-10 text-primary mx-auto mb-2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                        <p class="text-xs font-semibold uppercase text-muted-foreground">Location Pin</p>
                                        <p class="text-sm px-4">{{ $displayAddress }}</p>
                                    </div>
                                    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(circle, #000 1px, transparent 1px); background-size: 20px 20px;"></div>
                                @endif
                            </div>
                            <div class="p-4 bg-muted/30 border-t flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Vendor Location</p>
                                    <p class="text-sm font-medium line-clamp-2">{{ $displayAddress }}</p>
                                </div>
                                @if($hasCoords)
                                    <a
                                        class="text-xs font-bold text-primary hover:underline whitespace-nowrap"
                                        href="https://www.openstreetmap.org/?mlat={{ $vendorLat }}&mlon={{ $vendorLng }}#map=14/{{ $vendorLat }}/{{ $vendorLng }}"
                                        target="_blank"
                                        rel="noreferrer"
                                    >
                                        View on OSM
                                    </a>
                                @else
                                    <span class="text-xs font-bold text-muted-foreground whitespace-nowrap">No coordinates</span>
                                @endif
                            </div>
                        </div>
                    </aside>
                </div>
            </section>

            {{-- Reviews tab --}}
            <section id="tab-reviews" class="vendor-tab-panel hidden opacity-0 translate-y-2 transition-all duration-300 ease-out" role="tabpanel">
                <div class="bg-card rounded-xl border p-5 md:p-6" id="vendor-reviews">
                    <h3 class="text-xl font-semibold mb-4">Vendor Reviews</h3>
                    <x-review-list
                        :reviews="$reviews"
                        :user-review="$userReview"
                        reviewable-type="vendor"
                        :reviewable-id="$vendor->id"
                    />
                </div>
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var buttons = Array.from(document.querySelectorAll('.vendor-tab-btn'));
            var triggerButtons = Array.from(document.querySelectorAll('[data-tab-trigger]'));
            var panels = {
                deals: document.getElementById('tab-deals'),
                about: document.getElementById('tab-about'),
                reviews: document.getElementById('tab-reviews'),
            };
            var desktopSearchInput = document.getElementById('vendor-store-search');
            var mobileSearchInput = document.getElementById('vendor-store-search-mobile');
            var productItems = Array.from(document.querySelectorAll('.vendor-product-item'));
            var emptySearchState = document.getElementById('vendor-products-empty-search');
            var desktopSearchWrap = document.getElementById('vendor-store-search-desktop-wrap');
            var mobileSearchWrap = document.getElementById('vendor-store-search-mobile-wrap');

            function setTab(tabName) {
                Object.entries(panels).forEach(function ([name, panel]) {
                    if (!panel) return;
                    if (name === tabName) {
                        panel.classList.remove('hidden');
                        requestAnimationFrame(function () {
                            panel.classList.remove('opacity-0', 'translate-y-2');
                            panel.classList.add('opacity-100', 'translate-y-0');
                        });
                    } else {
                        panel.classList.add('opacity-0', 'translate-y-2');
                        panel.classList.remove('opacity-100', 'translate-y-0');
                        setTimeout(function () {
                            if (!panel.classList.contains('opacity-100')) {
                                panel.classList.add('hidden');
                            }
                        }, 220);
                    }
                });

                buttons.forEach(function (btn) {
                    var active = btn.dataset.tab === tabName;
                    btn.setAttribute('aria-selected', active ? 'true' : 'false');
                    btn.classList.toggle('bg-background', active);
                    btn.classList.toggle('text-foreground', active);
                    btn.classList.toggle('shadow-sm', active);
                    btn.classList.toggle('text-muted-foreground', !active);
                });

                var showStoreSearch = tabName === 'deals';
                if (desktopSearchWrap) {
                    desktopSearchWrap.style.display = showStoreSearch ? '' : 'none';
                }
                if (mobileSearchWrap) {
                    mobileSearchWrap.style.display = showStoreSearch ? '' : 'none';
                }
            }

            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    setTab(btn.dataset.tab);
                });
            });

            triggerButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var target = btn.dataset.tabTrigger;
                    if (target === 'about' || target === 'deals' || target === 'reviews') {
                        setTab(target);
                    }
                });
            });

            function applyStoreSearch() {
                if (productItems.length === 0) return;
                var activeInput = mobileSearchInput && mobileSearchInput.value.trim() !== ''
                    ? mobileSearchInput
                    : desktopSearchInput;
                var q = activeInput ? activeInput.value.trim().toLowerCase() : '';
                var visibleCount = 0;
                productItems.forEach(function (item) {
                    var hay = item.getAttribute('data-search') || '';
                    var isVisible = q === '' || hay.includes(q);
                    item.classList.toggle('hidden', !isVisible);
                    if (isVisible) visibleCount += 1;
                });
                if (emptySearchState) {
                    emptySearchState.classList.toggle('hidden', visibleCount > 0);
                }
            }

            function syncSearchInputs(source, target) {
                if (!source || !target) return;
                target.value = source.value;
            }

            if (desktopSearchInput) {
                desktopSearchInput.addEventListener('input', function () {
                    syncSearchInputs(desktopSearchInput, mobileSearchInput);
                    applyStoreSearch();
                });
            }

            if (mobileSearchInput) {
                mobileSearchInput.addEventListener('input', function () {
                    syncSearchInputs(mobileSearchInput, desktopSearchInput);
                    applyStoreSearch();
                });
            }

            setTab('deals');
            applyStoreSearch();
        });
    </script>
</x-layout>
