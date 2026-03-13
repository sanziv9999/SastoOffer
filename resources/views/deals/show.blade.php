@php
    $discountedPrice = $deal['discountedPrice'] ?? 0;
    $originalPrice = $deal['originalPrice'] ?? 0;
    $savingsAmount = $originalPrice > 0 ? $originalPrice - $discountedPrice : 0;
    $discountPct = $deal['discountPercent'] ?? ($originalPrice > 0 ? round(($savingsAmount / $originalPrice) * 100) : 0);

    $featureImage = collect($deal['images'])->firstWhere('attribute_name', 'feature_photo');
    $galleryImages = collect($deal['images'])->filter(fn($img) => $img['attribute_name'] === 'gallery');

    $endsAt = isset($deal['ends_at']) ? new \DateTime($deal['ends_at']) : null;
    $isExpired = $endsAt && new \DateTime() > $endsAt;
    
    // Simple rough implementation of time remaining
    $timeLeft = null;
    if ($endsAt) {
        $now = new \DateTime();
        if ($now < $endsAt) {
            $diff = $now->diff($endsAt);
            if ($diff->days > 0) $timeLeft = $diff->days . ($diff->days > 1 ? " days" : " day") . " ago";
            elseif ($diff->h > 0) $timeLeft = $diff->h . ($diff->h > 1 ? " hours" : " hour") . " ago";
            else $timeLeft = "just now";
        }
    }
@endphp

<x-layout>
    @section('title', $deal['title'] . ' - SastoOffer')

    <div class="container py-8 max-w-7xl mx-auto px-4"
        x-data="{ 
            quantity: 1,
            handleAddToCart() {
                @if(!auth()->check())
                    alert('Please log in to purchase this deal');
                @else
                    alert('Added to cart: ' + this.quantity + ' × {{ addslashes($deal['title']) }}');
                @endif
            },
            handleBuyNow() {
                @if(!auth()->check())
                    alert('Please log in to purchase this deal');
                @else
                    window.location.href = '/checkout/{{ $deal['id'] }}?qty=' + this.quantity;
                @endif
            }
        }"
    >
        {{-- Breadcrumb --}}
        <div class="text-sm mb-6 flex items-center gap-1 text-muted-foreground">
            <a href="/" class="hover:text-foreground transition-colors">Home</a>
            <span>/</span>
            <a href="/" class="hover:text-foreground transition-colors">Deals</a>
            <span>/</span>
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
                        <span class="inline-flex items-center rounded-md border text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent bg-primary/10 text-primary border-none px-2.5 py-0.5">
                            {{ $discountPct }}% Off
                        </span>
                    @endif
                    @if($deal['is_featured'])
                        <span class="inline-flex items-center rounded-md border border-transparent bg-primary text-primary-foreground shadow hover:bg-primary/80 text-xs font-semibold px-2.5 py-0.5 transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
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
                            href="{{ route('vendor-profile.show', $deal['vendor']['id']) }}"
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
                            NPR {{ number_format($discountedPrice, 2, '.', '') }}
                        </span>
                        @if($originalPrice > 0)
                            <span class="text-lg text-muted-foreground line-through">
                                NPR {{ number_format($originalPrice, 2, '.', '') }}
                            </span>
                        @endif
                        @if($savingsAmount > 0)
                            <span class="text-sm font-medium bg-green-100 text-green-700 px-2 py-0.5 rounded-full">
                                Save NPR {{ number_format($savingsAmount, 2, '.', '') }}
                            </span>
                        @endif
                    </div>

                    @if($timeLeft)
                        <div class="flex items-center text-sm text-muted-foreground mt-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 mr-1.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <span>
                                {{ $isExpired ? 'Offer expired' : 'Offer ends ' . $timeLeft }}
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
                                class="inline-flex items-center justify-center border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-8 w-8 rounded-r-none disabled:pointer-events-none disabled:opacity-50 transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            >–</button>
                            <div class="h-8 px-4 flex items-center justify-center border-y border-input text-sm font-medium" x-text="quantity">
                            </div>
                            <button
                                type="button"
                                @click="quantity++"
                                class="inline-flex items-center justify-center border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-8 w-8 rounded-l-none transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            >+</button>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button 
                            @click="handleAddToCart"
                            class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 flex-1 min-w-[140px]"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>
                            Add to Cart
                        </button>
                        <button 
                            @click="handleBuyNow"
                            class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-10 px-8 py-2 flex-1 min-w-[140px]"
                        >
                            Buy Now
                        </button>
                    </div>

                    <div class="flex gap-3">
                        <button class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 px-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1 h-4 w-4"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg>
                            Save
                        </button>
                        <button class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 px-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1 h-4 w-4"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg>
                            Share
                        </button>
                    </div>
                </div>

                {{-- Highlights --}}
                @if(isset($deal['highlights']) && is_array($deal['highlights']) && count($deal['highlights']) > 0)
                    <div>
                        <h3 class="font-semibold mb-3">Highlights</h3>
                        <ul class="space-y-2">
                            @foreach($deal['highlights'] as $item)
                                <li class="flex items-start text-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-primary mr-2 mt-0.5 shrink-0"><path d="M20 6 9 17l-5-5"></path></svg>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        {{-- Description Section --}}
        <div class="mt-12 grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                @if(isset($deal['short_description']))
                    <div>
                        <h2 class="text-xl font-bold mb-4">Summary</h2>
                        <div class="prose max-w-none text-sm text-muted-foreground">
                            {!! $deal['short_description'] !!}
                        </div>
                    </div>
                @endif

                @if(isset($deal['long_description']))
                    <div class="shrink-0 bg-border h-[1px] w-full my-8"></div>
                    <div>
                        <h2 class="text-xl font-bold mb-4">Full Description</h2>
                        <div class="prose max-w-none text-sm">
                            {!! $deal['long_description'] !!}
                        </div>
                    </div>
                @endif
            </div>

            {{-- Vendor Sidebar --}}
            <div>
                @if(isset($deal['vendor']))
                    <div class="bg-card border rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold mb-3">About the Vendor</h3>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-12 w-12 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-lg">
                                {{ strtoupper(substr($deal['vendor']['business_name'], 0, 1)) }}
                            </div>
                            <div>
                                <h4 class="font-medium text-foreground">{{ $deal['vendor']['business_name'] }}</h4>
                                <div class="flex items-center text-sm text-muted-foreground">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5 text-yellow-500 mr-1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                    <span>Verified Vendor</span>
                                </div>
                            </div>
                        </div>

                        <a 
                            href="{{ route('vendor-profile.show', $deal['vendor']['id']) }}" 
                            class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 w-full"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            View Profile
                        </a>
                    </div>
                @endif

                <div class="mt-4">
                    <a href="/" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 w-full">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4"><path d="m15 18-6-6 6-6"></path></svg>
                        Back to Deals
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layout>
