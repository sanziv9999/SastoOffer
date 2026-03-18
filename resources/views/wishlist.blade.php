<x-layout>
    @section('title', 'My Wishlist - SastoOffer')

    <div class="container py-12 pb-24">
        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10 border-b pb-8">
            <div class="space-y-2">
                <div class="flex items-center gap-3 mb-1">
                    <div class="p-2 h-10 w-10 flex items-center justify-center bg-primary/10 rounded-full text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 fill-current"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                    </div>
                    <span class="text-sm font-semibold text-primary uppercase tracking-wider">Shopping list</span>
                </div>
                <h1 class="text-4xl font-extrabold tracking-tight text-teal-950">My Wishlist</h1>
                <p class="text-muted-foreground text-lg max-w-2xl leading-relaxed">
                    All your saved deals and favorites in one place. Keep an eye on price drops and snag them before they expire.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div class="bg-card px-4 py-2 border rounded-full text-sm font-medium flex items-center gap-2 shadow-sm">
                    <span class="text-primary">{{ count($deals) }}</span> 
                    <span class="text-muted-foreground">{{ count($deals) === 1 ? 'Saved Deal' : 'Saved Deals' }}</span>
                </div>
                <a href="{{ route('search') }}" class="inline-flex items-center justify-center rounded-full text-sm font-medium transition-all hover:bg-muted h-10 px-6 border bg-white shadow-sm hover:shadow active:scale-95">
                    Browse More
                </a>
            </div>
        </div>

        {{-- Wishlist Content --}}
        @if(count($deals) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                @foreach($deals as $deal)
                    <div class="relative group animate-in fade-in slide-in-from-bottom-4 duration-500 stagger-{{ ($loop->index % 10) * 100 }}">
                        <x-deal-card :deal="$deal" :featured="$deal['featured']" />
                    </div>
                @endforeach
            </div>
            
        @else
            <div class="flex min-h-[500px] flex-col items-center justify-center rounded-3xl border-2 border-dashed border-muted p-12 text-center bg-muted/10">
                <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-2xl bg-white shadow-md mb-8 rotate-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-12 w-12 text-muted-foreground opacity-40"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                </div>
                <h2 class="text-3xl font-extrabold text-teal-950 mb-4">Your wishlist is currently feeling empty</h2>
                <p class="mb-10 text-muted-foreground text-lg max-w-md mx-auto leading-relaxed">
                    Don't miss out on amazing deals! Start browsing and save your favorite offers to find them easily later.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a 
                        href="{{ route('search') }}"
                        class="inline-flex items-center justify-center rounded-full text-base font-bold transition-all bg-primary text-primary-foreground shadow-lg hover:bg-primary/90 h-14 px-10 hover:-translate-y-1 active:scale-95"
                    >
                        START EXPLORING
                    </a>
                    <a 
                        href="{{ route('home') }}"
                        class="inline-flex items-center justify-center rounded-full text-base font-bold transition-all border-2 border-border bg-white shadow-sm hover:bg-muted h-14 px-10 active:scale-95"
                    >
                        BACK TO HOME
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-layout>
