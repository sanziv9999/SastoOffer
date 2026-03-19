@php
    if ($deal) {
        $discountedPrice = $deal['discountedPrice'] ?? 0;
        $originalPrice = $deal['originalPrice'] ?? 0;
        $savingsAmount = $originalPrice > 0 ? $originalPrice - $discountedPrice : 0;
        $discountPct = $deal['discountPercent'] ?? ($originalPrice > 0 ? round(($savingsAmount / $originalPrice) * 100) : 0);

        $sortedImages = collect($deal['images'] ?? [])
            ->sortBy(fn ($img) => (int) ($img['sort_order'] ?? 0))
            ->values();
        $featureImage = $sortedImages->firstWhere('attribute_name', 'feature_photo') ?? $sortedImages->first();
        $galleryImages = $sortedImages->filter(fn($img) => ($img['id'] ?? null) !== ($featureImage['id'] ?? null));

        $endsAt = isset($deal['ends_at']) ? new \DateTime($deal['ends_at']) : null;
        $isExpired = $endsAt && new \DateTime() > $endsAt;
        
        $timeLeft = null;
        if ($endsAt) {
            $now = new \DateTime();
            if ($now < $endsAt) {
                $diff = $now->diff($endsAt);
                if ($diff->days > 0) $timeLeft = $diff->days . ($diff->days > 1 ? " days" : " day");
                elseif ($diff->h > 0) $timeLeft = $diff->h . ($diff->h > 1 ? " hours" : " hour");
                elseif ($diff->i > 0) $timeLeft = $diff->i . ($diff->i > 1 ? " minutes" : " minute");
                else $timeLeft = "just now";
            }
        }
    }
@endphp

<x-layout>
    @section('title', ($deal['title'] ?? 'Deal Not Found') . ' - SastoOffer')

    @if(!$deal)
        <div class="container py-20 text-center">
            <h1 class="text-4xl font-bold mb-4">Deal Not Found</h1>
            <p class="text-muted-foreground mb-8 text-lg">The deal you're looking for doesn't exist or may have expired.</p>
            <a href="/" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-11 px-8">
                Back to Homepage
            </a>
        </div>
    @else
    <div class="container py-8 max-w-7xl mx-auto px-4"
        x-data="{ 
            quantity: 1,
            handleAddToCart() {
                this.cart.addItem({{ $deal['offerPivotId'] }}, this.quantity);
            },
            async handleBuyNow() {
                await this.cart.addItem({{ $deal['offerPivotId'] }}, this.quantity);
                window.location.href = '/cart'; // Redirect to cart or checkout? User is asking for Groupon style, usually cart first.
            }
        }"
    >
        {{-- Breadcrumb --}}
        <div class="text-sm mb-6 flex items-center gap-1 text-muted-foreground">
            <a href="/" class="hover:text-foreground transition-colors">Home</a>
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5"><path d="m9 18 6-6-6-6"/></svg>
            <a href="/" class="hover:text-foreground transition-colors">Deals</a>
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5"><path d="m9 18 6-6-6-6"/></svg>
            <span class="font-medium text-foreground truncate">{{ $deal['title'] }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            {{-- Left Column – Images --}}
            <div>
                @if($featureImage)
                    <div class="rounded-2xl overflow-hidden shadow-md mb-3 bg-muted">
                        <img
                            src="{{ $featureImage['image_url'] }}"
                            alt="{{ $deal['title'] }}"
                            class="w-full aspect-video object-cover"
                        />
                    </div>
                @else
                    <div class="rounded-2xl overflow-hidden shadow-md mb-3 bg-muted/40 w-full aspect-video flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-16 w-16 text-muted-foreground/30"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"></path><path d="M7 7h.01"></path></svg>
                    </div>
                @endif

                @if($galleryImages->count() > 0)
                    <div class="grid grid-cols-4 gap-2">
                        @foreach($galleryImages as $img)
                            <div class="rounded-lg overflow-hidden shadow-sm bg-muted">
                                <img
                                    src="{{ $img['image_url'] }}"
                                    alt="{{ $deal['title'] }}"
                                    class="w-full aspect-square object-cover"
                                />
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Right Column – Details --}}
            <div>
                {{-- Badges --}}
                <div class="flex flex-wrap gap-2 mb-3">
                    @if($discountPct > 0)
                        <span class="inline-flex items-center rounded-md border text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent bg-green-100 text-green-700 border-none px-2.5 py-0.5">
                            {{ $discountPct }}% Off
                        </span>
                    @endif
                    @if(isset($deal['is_featured']) && $deal['is_featured'])
                        <span class="inline-flex items-center rounded-md border border-transparent bg-destructive text-destructive-foreground shadow hover:bg-destructive/80 text-xs font-semibold px-2.5 py-0.5 transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                            Featured
                        </span>
                    @endif
                    @if(isset($deal['subCategory']))
                        <span class="inline-flex items-center rounded-md border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground text-xs font-semibold px-2.5 py-0.5 transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                            {{ $deal['subCategory']['name'] }}
                        </span>
                    @endif
                    <span class="inline-flex items-center rounded-md border text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-none px-2.5 py-0.5 {{ $deal['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ ucfirst($deal['status']) }}
                    </span>
                </div>

                <h1 class="text-2xl md:text-3xl font-bold mb-4 text-foreground">{{ $deal['title'] }}</h1>

                {{-- Vendor --}}
                @if(isset($deal['vendor']))
                    <div class="flex items-center gap-2 mb-4 text-sm text-muted-foreground">
                        <span>By</span>
                        <a
                            href="{{ route('vendor-profile.show', ['vendorProfile' => $deal['vendor']['slug'] ?? $deal['vendor']['id']]) }}"
                            class="text-primary font-medium hover:underline"
                        >
                            {{ $deal['vendor']['business_name'] }}
                        </a>
                    </div>
                @endif

                {{-- Price --}}
                <div class="bg-muted/50 p-5 rounded-xl mb-6">
                    <div class="flex items-end gap-3 mb-2">
                        <span class="text-3xl font-bold text-primary">
                            Rs. {{ number_format($discountedPrice, 2, '.', '') }}
                        </span>
                        @if($originalPrice > 0)
                            <span class="text-lg text-muted-foreground line-through">
                                Rs. {{ number_format($originalPrice, 2, '.', '') }}
                            </span>
                        @endif
                        @if($savingsAmount > 0)
                            <span class="text-sm font-medium bg-green-100 text-green-700 px-2 py-0.5 rounded-full">
                                Save Rs. {{ number_format($savingsAmount, 2, '.', '') }}
                            </span>
                        @endif
                    </div>

                    @if($timeLeft)
                        <div class="flex items-center text-sm text-amber-600 mt-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 mr-1.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <span>
                                {{ $isExpired ? 'Offer expired' : 'Offer ends in ' . $timeLeft }}
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Quantity + Actions --}}
                <div class="mb-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-medium">Quantity:</span>
                        <div class="flex items-center">
                            <button
                                type="button"
                                @click="quantity = Math.max(1, quantity - 1)"
                                :disabled="quantity <= 1"
                                class="inline-flex items-center justify-center border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 w-9 rounded-md rounded-r-none disabled:pointer-events-none disabled:opacity-50 transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            ><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M5 12h14"/></svg></button>
                            <div class="h-9 px-4 flex items-center justify-center border-y border-input text-sm font-medium min-w-[3rem]" x-text="quantity">
                            </div>
                            <button
                                type="button"
                                @click="quantity++"
                                class="inline-flex items-center justify-center border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 w-9 rounded-md rounded-l-none transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            ><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M5 12h14"/><path d="M12 5v14"/></svg></button>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button 
                            @click="handleAddToCart"
                            class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 flex-1 min-w-[140px]"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                            Add to Cart
                        </button>
                        <button 
                            @click="handleBuyNow"
                            class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-10 px-8 py-2 flex-1 min-w-[140px]"
                        >
                            Buy Now
                        </button>
                    </div>

                    <div class="flex gap-1">
                        @php $pivotId = $deal['offerPivotId']; @endphp
                        <button 
                            @click="toggleWishlist({{ $pivotId }})"
                            class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 px-3 transition-colors duration-200"
                            :class="wishlistedIds.includes({{ $pivotId }}) ? 'text-destructive' : 'text-muted-foreground hover:text-foreground'"
                        >
                            <svg 
                                xmlns="http://www.w3.org/2000/svg" 
                                width="16" height="16" 
                                viewBox="0 0 24 24" 
                                :fill="wishlistedIds.includes({{ $pivotId }}) ? 'currentColor' : 'none'" 
                                stroke="currentColor" 
                                stroke-width="2" 
                                stroke-linecap="round" 
                                stroke-linejoin="round" 
                                class="mr-1.5 h-4 w-4 transition-all"
                                :class="wishlistedIds.includes({{ $pivotId }}) ? 'fill-destructive scale-110' : ''"
                            >
                                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                            </svg>
                            <span x-text="wishlistedIds.includes({{ $pivotId }}) ? 'Saved' : 'Save'"></span>
                        </button>
                        <button class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 px-3 text-muted-foreground hover:text-foreground">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1.5 h-4 w-4"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
                            Share
                        </button>
                    </div>
                </div>

                {{-- Highlights --}}
                @if(isset($deal['highlights']) && is_array($deal['highlights']) && count($deal['highlights']) > 0)
                    <div class="border-t pt-6">
                        <h3 class="font-semibold mb-3">Highlights</h3>
                        <ul class="space-y-2">
                            @foreach($deal['highlights'] as $item)
                                <li class="flex items-start text-sm text-muted-foreground">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-primary mr-2 mt-0.5 shrink-0"><polyline points="20 6 9 17 4 12"/></svg>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        {{-- Description Section --}}
        <div class="mt-12 grid grid-cols-1 lg:grid-cols-3 gap-12">
            <div class="lg:col-span-2 space-y-8">
                @if(isset($deal['short_description']))
                    <div>
                        <h2 class="text-xl font-bold mb-4">Summary</h2>
                        <div class="prose max-w-none text-muted-foreground">
                            {!! $deal['short_description'] !!}
                        </div>
                    </div>
                @endif

                @if(isset($deal['long_description']))
                    <div class="shrink-0 bg-border h-[1px] w-full my-8"></div>
                    <div>
                        <h2 class="text-xl font-bold mb-4">Full Description</h2>
                        <div class="prose max-w-none text-muted-foreground">
                            {!! $deal['long_description'] !!}
                        </div>
                    </div>
                @endif
            </div>

            {{-- Vendor Sidebar --}}
            <div>
                @if(isset($deal['vendor']))
                    <div class="bg-card border rounded-xl p-6 shadow-sm sticky top-24">
                        <h3 class="text-lg font-semibold mb-4 text-teal-800">About the Vendor</h3>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="h-14 w-14 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xl">
                                {{ strtoupper(substr($deal['vendor']['business_name'], 0, 1)) }}
                            </div>
                            <div>
                                <h4 class="font-semibold text-foreground">{{ $deal['vendor']['business_name'] }}</h4>
                                <div class="flex items-center text-xs text-muted-foreground mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5 text-blue-500 fill-blue-500 mr-1"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                    <span>Verified Vendor</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 mb-6">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Rating</span>
                                <div class="flex items-center gap-1 font-medium">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-yellow-500 fill-yellow-500"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <span>{{ $deal['vendor']['rating'] ?? '4.8' }}</span>
                                    <span class="text-muted-foreground font-normal">({{ $deal['vendor']['reviewCount'] ?? '42' }} reviews)</span>
                                </div>
                            </div>
                        </div>

                        <a 
                            href="{{ route('vendor-profile.show', ['vendorProfile' => $deal['vendor']['slug'] ?? $deal['vendor']['id']]) }}" 
                            class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 w-full"
                        >
                            View Vendor Profile
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Reviews Section --}}
        @php
            $reviewsList = $reviews ?? collect();
            $avgRating = $reviewsList->count() > 0 ? round($reviewsList->avg('rating'), 1) : 0;
            $ratingCounts = [];
            for ($i = 5; $i >= 1; $i--) { $ratingCounts[$i] = $reviewsList->where('rating', $i)->count(); }
        @endphp
        <div class="mt-16" id="reviews">
            <h2 class="text-2xl font-bold mb-6">Customer Reviews</h2>

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
                            <h3 class="font-semibold mb-3 text-lg">Write a Review</h3>
                            <form method="POST" action="{{ route('reviews.store') }}" x-data="{ rating: 0, hoveredStar: 0 }">
                                @csrf
                                <input type="hidden" name="reviewable_type" value="deal_offer">
                                <input type="hidden" name="reviewable_id" value="{{ $deal['offerPivotId'] }}">
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
                                <textarea name="comment" rows="3" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring" placeholder="Share your experience...">{{ ($userReview['comment'] ?? '') }}</textarea>
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
                            <p class="text-muted-foreground mb-3">Please log in to write a review.</p>
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

        {{-- Similar Deals --}}
        @if(count($similarDeals) > 0)
            <div class="mt-20">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-bold">Similar Deals</h2>
                    <a href="{{ route('search', ['category' => $deal['category']['name'] ?? '']) }}" class="text-primary hover:underline text-sm font-medium flex items-center">
                        View all
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-1 h-3.5 w-3.5"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                    @foreach($similarDeals as $item)
                        <x-deal-card :deal="$item" :compact="true" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
    @endif
</x-layout>
