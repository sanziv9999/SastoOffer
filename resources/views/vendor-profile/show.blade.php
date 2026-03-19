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
                                    <span class="ml-1 font-medium">{{ $vendor->rating ?? 4.5 }}</span>
                                    <span class="mx-1 opacity-50">•</span>
                                    <span>{{ $vendor->review_count ?? 42 }} reviews</span>
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
                    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                        Active Deals
                        <span class="text-xs font-normal text-muted-foreground">({{ $deals->count() }})</span>
                    </h2>

                    @if($deals->isEmpty())
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
                            @foreach($deals as $deal)
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
            @php
                $reviewsList = $reviews ?? collect();
                $avgRating = $reviewsList->count() > 0 ? round($reviewsList->avg('rating'), 1) : 0;
                $ratingCounts = [];
                for ($i = 5; $i >= 1; $i--) { $ratingCounts[$i] = $reviewsList->where('rating', $i)->count(); }
            @endphp
            <div class="mt-12" id="vendor-reviews">
                <h2 class="text-2xl font-bold mb-6">Vendor Reviews</h2>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                    {{-- Summary --}}
                    <div class="bg-card rounded-xl border p-6 text-center">
                        <div class="text-4xl font-bold text-primary mb-1">{{ $avgRating > 0 ? $avgRating : '-' }}</div>
                        <div class="flex justify-center gap-0.5 mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="{{ $i <= round($avgRating) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" class="h-4 w-4 {{ $i <= round($avgRating) ? 'text-yellow-500' : 'text-gray-300' }}"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            @endfor
                        </div>
                        <p class="text-sm text-muted-foreground">Based on {{ $reviewsList->count() }} {{ Str::plural('review', $reviewsList->count()) }}</p>

                        <div class="mt-4 space-y-1.5">
                            @for($i = 5; $i >= 1; $i--)
                                @php $pct = $reviewsList->count() > 0 ? round(($ratingCounts[$i] / $reviewsList->count()) * 100) : 0; @endphp
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="w-3 font-medium">{{ $i }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" class="h-3 w-3 text-yellow-500"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <div class="flex-1 h-2 bg-muted rounded-full overflow-hidden">
                                        <div class="h-full bg-yellow-500 rounded-full" style="width:{{ $pct }}%"></div>
                                    </div>
                                    <span class="w-6 text-right text-muted-foreground">{{ $ratingCounts[$i] }}</span>
                                </div>
                            @endfor
                        </div>
                    </div>

                    {{-- Write Review Form --}}
                    <div class="lg:col-span-2 bg-card rounded-xl border p-6">
                        @auth
                            @if($userReview ?? false)
                                <h3 class="font-semibold mb-3 text-lg">Update Your Review</h3>
                                <form method="POST" action="{{ route('reviews.update', $userReview['id']) }}" x-data="{ rating: {{ $userReview['rating'] }}, hoveredStar: 0 }">
                                    @csrf
                                    @method('PUT')
                            @else
                                <h3 class="font-semibold mb-3 text-lg">Rate This Vendor</h3>
                                <form method="POST" action="{{ route('reviews.store') }}" x-data="{ rating: 0, hoveredStar: 0 }">
                                    @csrf
                                    <input type="hidden" name="reviewable_type" value="vendor">
                                    <input type="hidden" name="reviewable_id" value="{{ $vendor->id }}">
                            @endif
                                <div class="mb-4">
                                    <label class="block text-sm font-medium mb-2">Rating</label>
                                    <div class="flex gap-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <button type="button"
                                                @click="rating = {{ $i }}"
                                                @mouseenter="hoveredStar = {{ $i }}"
                                                @mouseleave="hoveredStar = 0"
                                                class="focus:outline-none transition-transform hover:scale-110">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                                                    :fill="{{ $i }} <= (hoveredStar || rating) ? 'currentColor' : 'none'"
                                                    stroke="currentColor" stroke-width="2"
                                                    :class="{{ $i }} <= (hoveredStar || rating) ? 'text-yellow-500' : 'text-gray-300'"
                                                    class="h-7 w-7 cursor-pointer"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                            </button>
                                        @endfor
                                    </div>
                                    <input type="hidden" name="rating" :value="rating">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium mb-2">Comment</label>
                                    <textarea name="comment" rows="3" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring" placeholder="How was your experience with this vendor?">{{ ($userReview['comment'] ?? '') }}</textarea>
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2">
                                        {{ ($userReview ?? false) ? 'Update Review' : 'Submit Review' }}
                                    </button>
                                    @if($userReview ?? false)
                                        <form method="POST" action="{{ route('reviews.destroy', $userReview['id']) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Delete your review?')" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 text-destructive">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </form>
                        @else
                            <div class="text-center py-8">
                                <p class="text-muted-foreground mb-3">Please log in to review this vendor.</p>
                                <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2">Log In</a>
                            </div>
                        @endauth
                    </div>
                </div>

                {{-- Review Cards --}}
                @if($reviewsList->count() > 0)
                    <div class="space-y-4">
                        @foreach($reviewsList as $rv)
                            <div class="bg-card rounded-xl border p-5">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 rounded-full bg-muted flex items-center justify-center font-bold text-sm text-muted-foreground">{{ strtoupper(substr($rv['userName'], 0, 1)) }}</div>
                                        <div>
                                            <p class="font-medium text-sm">{{ $rv['userName'] }}</p>
                                            <div class="flex items-center gap-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="{{ $i <= $rv['rating'] ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" class="h-3 w-3 {{ $i <= $rv['rating'] ? 'text-yellow-500' : 'text-gray-300' }}"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                    <span class="text-xs text-muted-foreground">{{ \Carbon\Carbon::parse($rv['createdAt'])->diffForHumans() }}</span>
                                </div>
                                @if($rv['comment'])
                                    <p class="text-sm text-foreground/80 leading-relaxed mt-2">{{ $rv['comment'] }}</p>
                                @endif
                                @if($rv['vendorReply'])
                                    <div class="mt-3 bg-primary/5 border border-primary/10 rounded-lg p-3">
                                        <p class="text-xs font-semibold text-primary mb-1">Vendor Reply</p>
                                        <p class="text-sm italic">"{{ $rv['vendorReply'] }}"</p>
                                        @if($rv['vendorRepliedAt'])
                                            <span class="text-[10px] text-muted-foreground mt-1 block">{{ \Carbon\Carbon::parse($rv['vendorRepliedAt'])->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-12 text-center border-2 border-dashed rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mx-auto mb-3 text-muted-foreground/30"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <p class="text-muted-foreground">No reviews yet. Be the first to review!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layout>
