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
        {{-- Cover Image --}}
        <div class="h-48 md:h-64 w-full relative">
            <img
                src="{{ $coverUrl }}"
                alt="{{ $vendor->business_name }} cover"
                class="w-full h-full object-cover"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
        </div>

        {{-- Vendor Info Section --}}
        <div class="container mx-auto px-4 relative">
            <div class="flex flex-col md:flex-row gap-6 -mt-16 mb-8">
                {{-- Logo --}}
                <div class="w-32 h-32 rounded-xl overflow-hidden border-4 border-white shadow-lg bg-white z-10 shrink-0 mx-auto md:mx-0">
                    <img
                        src="{{ $logoUrl }}"
                        alt="{{ $vendor->business_name }} logo"
                        class="w-full h-full object-cover"
                    />
                </div>

                {{-- Header Details --}}
                <div class="flex-1 mt-6 md:mt-0">
                    <div class="bg-card rounded-lg shadow-sm p-6 border">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h1 class="text-2xl font-bold flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-primary"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                    {{ $vendor->business_name }}
                                </h1>
                                <div class="flex items-center mt-2 text-sm text-muted-foreground">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-yellow-500"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <span class="ml-1 font-medium">{{ number_format($vendor->reviews_avg_rating ?? 0, 1) }}</span>
                                    <span class="mx-1 opacity-50">•</span>
                                    <span>{{ $vendor->reviews_count ?? 0 }} reviews</span>
                                    @if($vendor->primaryCategory)
                                        <span class="mx-1 opacity-50">•</span>
                                        <span class="text-primary font-medium">{{ $vendor->primaryCategory->name }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="/search?vendorId={{ $vendor->id }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2">
                                    View All Deals
                                </a>
                            </div>
                        </div>

                        <p class="my-4 text-muted-foreground leading-relaxed">{{ $vendor->description }}</p>

                        {{-- Details Grid --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8 pt-6 border-t">
                            {{-- Address --}}
                            <div class="flex items-start gap-3">
                                <div class="p-2 rounded-md bg-primary/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-primary"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Address</p>
                                    <p class="text-sm font-medium">{{ $displayAddress }}</p>
                                </div>
                            </div>

                            {{-- Phone --}}
                            <div class="flex items-start gap-3">
                                <div class="p-2 rounded-md bg-emerald-500/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-emerald-600"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Phone</p>
                                    <p class="text-sm font-medium">{{ $vendor->public_phone ?? 'N/A' }}</p>
                                </div>
                            </div>

                            {{-- Email --}}
                            <div class="flex items-start gap-3">
                                <div class="p-2 rounded-md bg-blue-500/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-blue-600"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Email</p>
                                    <p class="text-sm font-medium line-clamp-1">{{ $vendor->public_email ?? 'N/A' }}</p>
                                </div>
                            </div>

                            {{-- Website --}}
                            <div class="flex items-start gap-3">
                                <div class="p-2 rounded-md bg-indigo-500/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-indigo-600"><circle cx="12" cy="12" r="10"/><line x1="2" x2="22" y1="12" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Website</p>
                                    @if($vendor->website_url)
                                        <a href="{{ $vendor->website_url }}" target="_blank" class="text-sm font-medium text-primary hover:underline">{{ $vendor->website_url }}</a>
                                    @else
                                        <p class="text-sm font-medium">N/A</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Business Type --}}
                            <div class="flex items-start gap-3">
                                <div class="p-2 rounded-md bg-amber-500/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-amber-600"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Business Type</p>
                                    <p class="text-sm font-medium capitalize">{{ $vendor->business_type ?? 'N/A' }}</p>
                                </div>
                            </div>

                            {{-- Business Hours Summary --}}
                            <div class="flex items-start gap-3">
                                <div class="p-2 rounded-md bg-slate-500/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-slate-600"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Status</p>
                                    <p class="text-sm font-medium">Open Now</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Main Content: Deals --}}
                <div class="lg:col-span-2">
                    @php
                        $activeDeals = $deals?->where('status', 'active')->values() ?? collect();
                        $expiredDeals = $deals?->where('status', 'expired')->values() ?? collect();
                    @endphp

                    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                        Active Deals
                        <span class="text-xs font-normal text-muted-foreground">({{ $activeDeals->count() }})</span>
                    </h2>

                    @if($activeDeals->isEmpty())
                        <div class="bg-card rounded-lg border border-dashed p-12 text-center">
                            <div class="bg-muted rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                            </div>
                            <h3 class="font-medium text-lg">No active deals</h3>
                            <p class="text-muted-foreground mb-6">This vendor doesn't have any deals active at the moment.</p>
                            <a href="/search" class="text-primary hover:underline font-medium">Browse other deals</a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            @foreach($activeDeals as $deal)
                                <x-deal-card :deal="$deal" :featured="$deal['featured']" />
                            @endforeach
                        </div>
                    @endif

                    @if($expiredDeals->isNotEmpty())
                        <h2 class="text-2xl font-bold mt-10 mb-6 flex items-center gap-2">
                            Expired Deals
                            <span class="text-xs font-normal text-muted-foreground">({{ $expiredDeals->count() }})</span>
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            @foreach($expiredDeals as $deal)
                                <x-deal-card :deal="$deal" :featured="$deal['featured']" />
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Sidebar: Information --}}
                <div class="space-y-6">
                    {{-- Business Hours Card --}}
                    <div class="bg-card rounded-lg border p-6">
                        <h3 class="font-bold mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-primary"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Business Hours
                        </h3>
                        <div class="space-y-3">
                            @if(is_array($businessHours) && count($businessHours) > 0)
                                @foreach($businessHours as $hour)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-muted-foreground">{{ $hour['day'] }}</span>
                                        <span class="font-medium">
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

                    {{-- Map Placeholder --}}
                    <div class="bg-card rounded-lg border overflow-hidden">
                        <div class="h-48 bg-muted flex items-center justify-center relative">
                             <div class="text-center p-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-10 w-10 text-primary mx-auto mb-2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                <p class="text-xs font-semibold uppercase text-muted-foreground">Location Pin</p>
                                <p class="text-sm px-4">{{ $displayAddress }}</p>
                             </div>
                             {{-- Placeholder Background --}}
                             <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(circle, #000 1px, transparent 1px); background-size: 20px 20px;"></div>
                        </div>
                        <div class="p-4 bg-muted/30 border-t flex justify-center">
                            <button class="text-xs font-bold text-primary hover:underline">OPEN IN GOOGLE MAPS</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Vendor Reviews Section --}}
            <div class="mt-12" id="vendor-reviews">
                <h2 class="text-2xl font-bold mb-6">Vendor Reviews</h2>
                <x-review-list
                    :reviews="$reviews"
                    :user-review="$userReview"
                    reviewable-type="vendor"
                    :reviewable-id="$vendor->id"
                />
            </div>
        </div>
    </div>
</x-layout>
