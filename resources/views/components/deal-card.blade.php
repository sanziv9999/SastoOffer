@props(['deal', 'featured' => false])

<div {{ $attributes->merge(['class' => 'group bg-white border border-gray-200 rounded-lg overflow-hidden transition-all duration-200 hover:shadow-md relative' . ($featured ? ' shadow-md hover:shadow-lg transform hover:-translate-y-1' : '')]) }}>
    {{-- Featured Badge --}}
    @if($featured || ($deal['featured'] ?? false))
        <div class="absolute top-3 left-3 z-10">
            <span class="bg-destructive text-destructive-foreground px-2 py-1 text-[10px] font-bold rounded uppercase">
                Featured
            </span>
        </div>
    @endif
    
    {{-- Discount Badge --}}
    <div class="absolute top-3 right-3 z-10">
        <span class="bg-green-600 text-white px-2 py-1 text-[10px] font-bold rounded">
            {{ $deal['discountPercentage'] ?? Math.round((($deal['originalPrice'] - $deal['discountedPrice']) / $deal['originalPrice']) * 100) }}% OFF
        </span>
    </div>
    
    {{-- Image --}}
    <a href="{{ route('deals.show', ['deal' => $deal['id']]) }}" class="block relative overflow-hidden h-40 sm:h-48">
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
        <div class="flex justify-between items-center text-[10px] sm:text-xs text-gray-500 mb-2">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3 w-3 mr-1 text-primary"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"></path><path d="M7 7h.01"></path></svg>
                <span>{{ $deal['categoryName'] ?? 'Category' }}</span>
            </div>
            @if(isset($deal['cityName']) || isset($deal['location']))
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3 w-3 mr-1 text-primary"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    <span>{{ $deal['cityName'] ?? $deal['location'] ?? 'City' }}</span>
                </div>
            @endif
        </div>
        
        {{-- Title --}}
        <a href="{{ route('deals.show', ['deal' => $deal['id']]) }}">
            <h3 class="font-semibold text-slate-800 mb-2 line-clamp-2 min-h-[2.5rem] sm:min-h-[3rem] text-sm sm:text-base transition-colors group-hover:text-primary">
                {{ $deal['title'] }}
            </h3>
        </a>
        
        {{-- Pricing --}}
        <div class="flex items-baseline mb-2">
            <span class="text-base sm:text-lg font-bold text-primary mr-2">
                ${{ $deal['discountedPrice'] }}
            </span>
            <span class="text-xs sm:text-sm line-through text-gray-400">
                ${{ $deal['originalPrice'] }}
            </span>
        </div>
        
        {{-- Time Left --}}
        <div class="flex items-center text-[10px] sm:text-xs text-amber-600 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3 w-3 mr-1"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            <span>Ends in {{ $deal['timeLeft'] ?? 'soon' }}</span>
        </div>
        
        {{-- Action Button --}}
        <div class="flex justify-between items-center">
            <a 
                href="{{ route('deals.show', ['deal' => $deal['id']]) }}" 
                class="w-full flex items-center justify-center gap-1 px-3 py-2 border border-border rounded-md text-sm font-medium hover:bg-muted transition-colors"
            >
                View Deal
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="m9 18 6-6-6-6"></path></svg>
            </a>
        </div>
    </div>
</div>
