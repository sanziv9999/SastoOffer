@props([
    'deal',
    'featured' => false,
    'compact' => false,
    'showDiscount' => true,
    'showTimeLeft' => true,
    'showFeaturedRibbon' => false,
])

@php
    $originalPrice = (float)($deal['originalPrice'] ?? 0);
    $discountedPrice = (float)($deal['discountedPrice'] ?? 0);
    $discountPercentage = $originalPrice > 0 ? round((($originalPrice - $discountedPrice) / $originalPrice) * 100) : 0;
    if (isset($deal['discountPercentage']) && (float)$deal['discountPercentage'] > 0) {
        $discountPercentage = round((float)$deal['discountPercentage']);
    }
    $featured = $featured || (isset($deal['featured']) && $deal['featured']);
    $dealUrl = $deal['url'] ?? route('deals.show.by-deal', ['deal' => $deal['dealSlug'] ?? $deal['id']]);
    $pivotId = $deal['offerPivotId'] ?? $deal['id'];
    $vendorName = $deal['vendorName'] ?? 'Vendor';
    $vendorRating = (float)($deal['vendorRating'] ?? 0);
    $quantitySold = (int)($deal['quantitySold'] ?? 0);
    $timeLeft = $deal['timeLeft'] ?? null;
    $status = $deal['status'] ?? 'active';
    
    // Category Logic: If subcategory exists, show only subcategory. Else show category.
    $displayCategory = !empty($deal['subcategoryName']) ? $deal['subcategoryName'] : ($deal['categoryName'] ?? 'Uncategorized');
@endphp

@if($compact)
    <div class="group relative bg-white rounded-lg border border-slate-200 overflow-hidden hover:shadow-lg transition-all duration-300 h-full flex flex-col">
        <a href="{{ $dealUrl }}" class="absolute inset-0 z-10"></a>
        
        <div class="relative aspect-video overflow-hidden bg-slate-100">
            <img src="{{ $deal['image'] }}" alt="{{ $deal['title'] }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
            
            @if($showDiscount && $discountPercentage > 0)
                <div class="absolute bottom-1 left-1 px-1.5 py-0.5 bg-red-600 text-white text-[9px] font-black rounded-sm shadow-md z-10">
                    -{{ $discountPercentage }}%
                </div>
            @endif

            <div class="absolute top-1 right-1 flex flex-col gap-1 z-20">
                <button @click.prevent="toggleWishlist({{ $deal['id'] }})" class="p-1 bg-white/90 backdrop-blur-sm rounded-full shadow-sm text-slate-600 hover:text-red-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" :fill="wishlistedIds.includes({{ $deal['id'] }}) ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" :class="wishlistedIds.includes({{ $deal['id'] }}) ? 'text-red-500' : ''"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                </button>
            </div>
        </div>

        <div class="p-2 flex flex-col flex-1 gap-1">
            <div class="text-[9px] font-bold text-slate-800 truncate">
                {{ $vendorName }}
            </div>
            <h3 class="text-[10px] font-bold text-slate-900 line-clamp-1 truncate leading-tight">
                {{ $deal['title'] }}
            </h3>
            
            <div class="mt-auto pt-1 flex items-center justify-between">
                <div class="text-[11px] font-black text-primary">Rs. {{ number_format($discountedPrice) }}</div>
                <button @click.prevent="cart.addItem({{ $pivotId }})" class="text-slate-400 hover:text-primary transition-colors z-20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                </button>
            </div>
        </div>
    </div>
@else
    <div {{ $attributes->merge(['class' => 'group relative bg-white rounded-xl border border-slate-200 overflow-hidden hover:shadow-2xl transition-all duration-300 flex flex-col ' . ($featured ? 'ring-1 ring-primary/40' : '')]) }}>
        {{-- Image Canvas --}}
        <div class="relative h-48 overflow-hidden bg-slate-50">
            <a href="{{ $dealUrl }}" class="block w-full h-full">
                <img src="{{ $deal['image'] }}" alt="{{ $deal['title'] }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy" />
            </a>
            
            {{-- Elite Discount Badge --}}
            @if($showDiscount && $discountPercentage > 0)
                <div class="absolute bottom-3 left-3 z-10 bg-red-600 text-white text-[10px] font-black px-2 py-0.5 rounded shadow-xl tracking-tight uppercase">
                    Save {{ $discountPercentage }}%
                </div>
            @endif

            @if($showFeaturedRibbon && $featured)
                <div class="absolute top-2 -left-8 z-20 w-28 -rotate-45 bg-red-600 text-white text-[10px] font-black py-1 text-center shadow-xl tracking-wider uppercase">
                    Featured
                </div>
            @endif

            {{-- Quality Actions --}}
            <div class="absolute top-4 right-4 flex flex-col gap-2 z-20">
                <button @click.prevent="toggleWishlist({{ $deal['id'] }})" class="w-9 h-9 bg-white/95 backdrop-blur-sm rounded-full shadow-lg flex items-center justify-center text-slate-600 hover:text-red-500 transition-all transform active:scale-90">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" :fill="wishlistedIds.includes({{ $deal['id'] }}) ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" :class="wishlistedIds.includes({{ $deal['id'] }}) ? 'text-red-500' : ''"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                </button>
                <button @click.prevent="cart.addItem({{ $pivotId }})" class="w-9 h-9 bg-white/95 backdrop-blur-sm rounded-full shadow-lg flex items-center justify-center text-slate-600 hover:text-primary transition-all transform active:scale-90">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                </button>
            </div>
        </div>

        {{-- Professional Details --}}
        <div class="p-3 flex flex-col flex-1 gap-1.5">
            {{-- Header: Vendor & Title --}}
            <div class="space-y-0.5">
                <div class="text-[11px] font-bold text-slate-600 truncate leading-none">
                    {{ $vendorName }}
                </div>
                <a href="{{ $dealUrl }}" class="block">
                    <h3 class="text-base font-bold text-slate-900 leading-tight line-clamp-2 hover:text-primary transition-colors">
                        {{ $deal['title'] }}
                    </h3>
                </a>
            </div>

            {{-- Context: Category & Location --}}
            <div class="flex items-center gap-2">
                <div class="inline-flex px-1.5 py-0.5 bg-primary/5 text-primary text-[9px] font-black uppercase tracking-wider rounded border border-primary/10">
                    {{ $displayCategory }}
                </div>

                @if(!empty($deal['locationLabel']))
                    <div class="text-[10px] text-slate-500 flex items-center gap-1 font-semibold truncate">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-3 h-3 text-slate-400"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $deal['locationLabel'] }}
                    </div>
                @endif
            </div>

            {{-- Trust: Ratings --}}
            <div class="flex items-center gap-1 mt-0.5">
                <div class="flex items-center text-yellow-500">
                    @for($i = 0; $i < 5; $i++)
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="{{ $vendorRating > $i ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" class="w-3.5 h-3.5"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    @endfor
                </div>
                <span class="text-xs font-black text-slate-900">{{ number_format($vendorRating, 1) }}</span>
            </div>

            {{-- Value: Pricing --}}
            <div class="mt-auto pt-1.5 border-t border-slate-100 flex flex-col">
                <div class="flex items-baseline gap-1.5">
                    <div class="text-xl font-black text-slate-900 leading-none tracking-tight">
                        <span class="text-sm font-bold mr-0.5">Rs.</span>{{ number_format($discountedPrice) }}
                    </div>
                    @if($originalPrice > $discountedPrice)
                        <div class="text-xs text-slate-400 font-bold line-through">
                            Rs. {{ number_format($originalPrice) }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Footer: Status & Urgency --}}
            <div class="flex items-center justify-between pt-1.5 border-t border-slate-50 text-[9px] font-black uppercase tracking-tighter">
                <div class="flex flex-1 items-center gap-1.5">
                    @if($timeLeft)
                        <div class="flex items-center gap-1 {{ $status === 'expired' ? 'text-red-500' : 'text-amber-600' }}">
                            {{ $status === 'expired' ? 'Expired' : 'Ends ' . $timeLeft }}
                        </div>
                    @endif
                </div>

                @if($quantitySold > 0)
                    <div class="text-slate-500 flex items-center gap-1">
                        {{ $quantitySold }} Sold
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
