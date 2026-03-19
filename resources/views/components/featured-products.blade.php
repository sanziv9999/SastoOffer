@props(['featuredDeals' => []])

@if(count($featuredDeals) > 0)
<section class="py-8 bg-background">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-lg md:text-xl font-bold text-foreground flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-500 mr-2 h-5 w-5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                Discover Amazing Deals
            </h2>
            <a href="{{ route('search', ['featured' => 'true']) }}" class="text-primary hover:underline text-sm font-medium">
                View all
            </a>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 md:gap-4">
            @foreach($featuredDeals as $deal)
                <x-deal-card :deal="$deal" compact />
            @endforeach
        </div>
    </div>
</section>
@endif
