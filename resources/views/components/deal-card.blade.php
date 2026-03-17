@props(['deal', 'featured' => false, 'compact' => false])

@php
    $originalPrice = $deal['originalPrice'] ?? 0;
    $discountedPrice = $deal['discountedPrice'] ?? 0;
    $discountPercentage = $originalPrice > 0 ? round((($originalPrice - $discountedPrice) / $originalPrice) * 100) : 0;
    if (isset($deal['discountPercentage'])) {
        $discountPercentage = $deal['discountPercentage'];
    }
@endphp

@if($compact)
    <a href="{{ route('deals.show', ['dealOfferType' => $deal['offerPivotId'] ?? $deal['id']]) }}" class="block h-full group">
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden h-full hover:shadow-md transition-all duration-200">
            <div class="relative">
                <img 
                    src="{{ $deal['image'] }}" 
                    alt="{{ $deal['title'] }}" 
                    class="h-28 sm:h-32 md:h-36 w-full object-cover transition-transform duration-500 group-hover:scale-105" 
                    loading="lazy"
                />
                <div class="absolute top-1.5 right-1.5 z-10">
                    <span class="inline-flex items-center rounded-md border border-transparent bg-green-600 text-white shadow text-[10px] sm:text-xs px-1.5 py-0.5 font-semibold">
                        {{ $discountPercentage }}% OFF
                    </span>
                </div>
            </div>
            <div class="p-2.5 sm:p-3">
                <div class="flex items-center mb-1 text-[10px] sm:text-xs text-muted-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-2.5 w-2.5 mr-1 text-primary"><path d="m15 5 4 4"/><path d="M13 7 8.5 15.5c-.4.7-1.3.9-2 .5s-.9-1.3-.5-2L10.5 5.5l.3-.4c.3-.5.9-.9 1.5-.9h4.5z"/></svg>
                    <span>{{ $deal['categoryName'] ?? 'Uncategorized' }}</span>
                </div>
                <h3 class="font-medium text-teal-800 text-xs sm:text-sm line-clamp-2 mb-1.5 group-hover:text-teal-600 transition-colors">
                    {{ $deal['title'] }}
                </h3>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-sm sm:text-base font-bold text-primary">
                        ${{ $deal['discountedPrice'] }}
                    </span>
                    <span class="text-[10px] sm:text-xs line-through text-gray-400">
                        ${{ $deal['originalPrice'] }}
                    </span>
                </div>
            </div>
        </div>
    </a>
@else
    <div {{ $attributes->merge(['class' => 'group bg-white border border-gray-200 rounded-lg overflow-hidden transition-all duration-200 hover:shadow-md relative' . ($featured ? ' shadow-md hover:shadow-lg transform hover:-translate-y-1' : '')]) }}>
        {{-- Featured Badge --}}
        @if($featured)
            <div class="absolute top-3 left-3 z-10">
                <span class="inline-flex items-center rounded-md border border-transparent bg-destructive text-destructive-foreground shadow hover:bg-destructive/80 px-2 py-1 text-xs font-semibold">
                    Featured
                </span>
            </div>
        @endif
        
        {{-- Discount Badge --}}
        <div class="absolute top-3 right-3 z-10">
            <span class="inline-flex items-center rounded-md border border-transparent bg-green-600 text-white shadow hover:bg-green-700 px-2.5 py-0.5 text-xs font-semibold">
                {{ $discountPercentage }}% OFF
            </span>
        </div>
        
        {{-- Image --}}
        <a href="{{ route('deals.show', ['dealOfferType' => $deal['offerPivotId'] ?? $deal['id']]) }}" class="block relative overflow-hidden h-48">
            <img 
                src="{{ $deal['image'] }}" 
                alt="{{ $deal['title'] }}" 
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" 
                loading="lazy"
            />
        </a>
        
        {{-- Content --}}
        <div class="p-4">
            {{-- Category & Location --}}
            <div class="flex justify-between items-center text-xs text-gray-500 mb-2">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3 w-3 mr-1 text-primary"><path d="m15 5 4 4"/><path d="M13 7 8.5 15.5c-.4.7-1.3.9-2 .5s-.9-1.3-.5-2L10.5 5.5l.3-.4c.3-.5.9-.9 1.5-.9h4.5z"/></svg>
                    <span>{{ $deal['categoryName'] ?? 'Uncategorized' }}</span>
                </div>
                @if(isset($deal['cityName']) || isset($deal['location']) || (isset($deal['locationId']) && $deal['locationId']))
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3 w-3 mr-1 text-primary"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span>{{ $deal['cityName'] ?? $deal['location']['city'] ?? 'City Name' }}</span>
                    </div>
                @endif
            </div>

            @if(!empty($deal['offerTypeTitle']))
                <div class="mb-2">
                    <span class="inline-flex items-center rounded-md border border-transparent bg-primary/10 text-primary px-2 py-0.5 text-[11px] font-medium">
                        {{ $deal['offerTypeTitle'] }}
                    </span>
                </div>
            @endif

            {{-- Search page can render one card per offer, so we don't list multiple offers here. --}}
            
            {{-- Title --}}
            <a href="{{ route('deals.show', ['dealOfferType' => $deal['offerPivotId'] ?? $deal['id']]) }}">
                <h3 class="font-semibold text-teal-800 mb-2 line-clamp-2 min-h-[3rem] transition-colors group-hover:text-teal-600">
                    {{ $deal['title'] }}
                </h3>
            </a>
            
            {{-- Pricing --}}
            <div class="flex items-baseline mb-2">
                <span class="text-lg font-bold text-primary mr-2">
                    ${{ $deal['discountedPrice'] }}
                </span>
                <span class="text-sm line-through text-gray-400">
                    ${{ $deal['originalPrice'] }}
                </span>
            </div>
            
            {{-- Time Left --}}
            <div class="flex items-center text-xs text-amber-600 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3 w-3 mr-1"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span>Ends {{ isset($deal['timeLeft']) ? $deal['timeLeft'] : 'soon' }}</span>
            </div>
            
            {{-- Action Button --}}
            <div class="flex justify-between items-center">
                <a 
                    href="{{ route('deals.show', ['dealOfferType' => $deal['offerPivotId'] ?? $deal['id']]) }}"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 px-3 w-full"
                >
                    View Deal
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 ml-1"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            </div>
        </div>
    </div>
@endif
