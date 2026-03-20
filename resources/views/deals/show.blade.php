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
        $galleryImages = $sortedImages->filter(fn($img) => ($img['id'] ?? null) !== ($featureImage['id'] ?? null))->values();

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
            showImageModal: false,
            activeImageIndex: 0,
            allImages: @js($sortedImages->toArray()),
            
            openModal(index) {
                this.activeImageIndex = index;
                this.showImageModal = true;
                document.body.style.overflow = 'hidden';
            },
            closeModal() {
                this.showImageModal = false;
                document.body.style.overflow = '';
            },
            nextImage() {
                this.activeImageIndex = (this.activeImageIndex + 1) % this.allImages.length;
            },
            prevImage() {
                this.activeImageIndex = (this.activeImageIndex - 1 + this.allImages.length) % this.allImages.length;
            },
            handleAddToCart() {
                this.cart.addItem({{ $deal['offerPivotId'] }}, this.quantity);
            },
            async handleBuyNow() {
                await this.cart.addItem({{ $deal['offerPivotId'] }}, this.quantity);
                window.location.href = '/cart';
            }
        }"
        @keydown.escape.window="closeModal()"
        @keydown.left.window="if(showImageModal) prevImage()"
        @keydown.right.window="if(showImageModal) nextImage()"
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
                    <div 
                        class="rounded-2xl overflow-hidden shadow-md mb-3 bg-muted group cursor-zoom-in relative"
                        @click="openModal(0)"
                    >
                        <img
                            src="{{ $featureImage['image_url'] }}"
                            alt="{{ $deal['title'] }}"
                            class="w-full aspect-video object-cover transition-transform duration-500 group-hover:scale-105"
                        />
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors flex items-center justify-center">
                            <div class="h-12 w-12 rounded-full bg-white/90 shadow-lg text-primary items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hidden md:flex">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="rotate-180"><path d="m15 18-6-6 6-6"/></svg>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="rounded-2xl overflow-hidden shadow-md mb-3 bg-muted/40 w-full aspect-video flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-16 w-16 text-muted-foreground/30"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"></path><path d="M7 7h.01"></path></svg>
                    </div>
                @endif

                @if($galleryImages->count() > 0)
                    <div class="grid grid-cols-4 gap-2">
                        @foreach($galleryImages as $index => $img)
                            <div 
                                class="rounded-lg overflow-hidden shadow-sm bg-muted group cursor-zoom-in relative"
                                @click="openModal({{ $index + 1 }})"
                            >
                                <img
                                    src="{{ $img['image_url'] }}"
                                    alt="{{ $deal['title'] }}"
                                    class="w-full aspect-square object-cover transition-transform duration-500 group-hover:scale-110"
                                />
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"></div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Image Lightbox Modal --}}
            <template x-teleport="body">
                <div 
                    x-show="showImageModal" 
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-[100] bg-black/95 flex items-center justify-center p-4 md:p-10"
                    x-cloak
                >
                    {{-- Close button --}}
                    <button 
                        @click="closeModal()" 
                        class="absolute top-6 right-6 text-white/70 hover:text-white transition-colors z-[110] bg-white/10 hover:bg-white/20 rounded-full p-2"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                    </button>

                    {{-- Navigation buttons --}}
                    <template x-if="allImages.length > 1">
                        <div class="contents">
                            <button 
                                @click.stop="prevImage()" 
                                class="absolute left-4 md:left-10 top-1/2 -translate-y-1/2 text-white/70 hover:text-white transition-all z-[110] bg-white/5 hover:bg-white/10 rounded-full p-3 md:p-4 backdrop-blur-sm"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"></path></svg>
                            </button>
                            <button 
                                @click.stop="nextImage()" 
                                class="absolute right-4 md:right-10 top-1/2 -translate-y-1/2 text-white/70 hover:text-white transition-all z-[110] bg-white/5 hover:bg-white/10 rounded-full p-3 md:p-4 backdrop-blur-sm"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="rotate-180"><path d="m15 18-6-6 6-6"></path></svg>
                            </button>
                        </div>
                    </template>

                    {{-- Main Image Container --}}
                    <div class="relative w-full h-full flex items-center justify-center overflow-hidden" @click="closeModal()">
                        <img 
                            :src="allImages[activeImageIndex].image_url" 
                            class="max-w-full max-h-full object-contain select-none transition-all duration-300"
                            x-transition:enter="transition ease-out duration-300 transform"
                            x-transition:enter-start="scale-95 opacity-0"
                            x-transition:enter-end="scale-100 opacity-100"
                            @click.stop
                        >
                        
                        {{-- Image Counter --}}
                        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 px-4 py-2 bg-white/10 backdrop-blur-md rounded-full text-white/80 text-sm font-medium">
                            <span x-text="activeImageIndex + 1"></span> / <span x-text="allImages.length"></span>
                        </div>
                    </div>
                </div>
            </template>

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
        <div class="mt-16" id="reviews">
            <h2 class="text-2xl font-bold mb-6">Customer Reviews</h2>
            <x-review-list
                :reviews="$reviews"
                :user-review="$userReview"
                reviewable-type="deal_offer"
                :reviewable-id="$deal['offerPivotId']"
            />
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
