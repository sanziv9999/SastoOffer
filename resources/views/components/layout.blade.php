<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'SastoOffer'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body 
    class="font-sans antialiased bg-muted/30"
    x-data="{ 
        wishlistedIds: {{ auth()->check() ? json_encode(auth()->user()->wishlist()->pluck('deal_offer_type_id')->toArray()) : '[]' }},
        toggleWishlist(id) {
            if (!{{ auth()->check() ? 'true' : 'false' }}) {
                window.location.href = '/login';
                return;
            }
            fetch(`/wishlist/toggle/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'added') {
                    this.wishlistedIds.push(id);
                } else {
                    this.wishlistedIds = this.wishlistedIds.filter(i => i !== id);
                    if (window.location.pathname === '/wishlist') {
                        // Refresh or remove from DOM if we are on the wishlist page
                        window.location.reload();
                    }
                }
            });
        }
    }"
>
    <div class="min-h-screen flex flex-col">
        <div class="fixed top-0 left-0 right-0 z-50">
            <x-navbar />
        </div>

        {{-- Fixed spacer for navbar height --}}
        <div class="h-28 md:h-32"></div>

        <main class="flex-grow">
            {{ $slot }}
        </main>

        <x-footer />
        
        {{-- Mobile: Category Drawer + Bottom Nav (md:hidden) --}}
        {{-- Category slide-out drawer --}}
        <div 
            x-data="{ isOpen: false }" 
            x-on:open-mobile-categories.window="isOpen = true"
            class="fixed inset-0 z-[60] bg-black/30 transition-opacity md:hidden"
            :class="isOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'"
            @click="isOpen = false"
            x-cloak
        >
            <div 
                class="fixed inset-y-0 left-0 max-w-[280px] w-[80vw] bg-white shadow-xl z-[60] transition-transform duration-300 ease-in-out"
                :class="isOpen ? 'translate-x-0' : '-translate-x-full'"
                @click.stop
            >
                <div class="flex items-center justify-between p-4 border-b">
                    <h2 class="font-semibold text-lg">Categories</h2>
                    <button class="p-2 hover:bg-gray-100 rounded-md" @click="isOpen = false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                    </button>
                </div>
                <div class="overflow-y-auto h-[calc(100%-60px)]">
                    @php
                        $mobileCategories = [
                            ['name' => 'Restaurants',     'slug' => 'food-dining'],
                            ['name' => 'Beauty & Spa',    'slug' => 'beauty-spa'],
                            ['name' => 'Activities',      'slug' => 'activities-events'],
                            ['name' => 'Travel',          'slug' => 'travel'],
                            ['name' => 'Electronics',     'slug' => 'electronics'],
                            ['name' => 'Home Services',   'slug' => 'services'],
                            ['name' => 'Health & Fitness','slug' => 'health-fitness'],
                            ['name' => 'Education',       'slug' => 'education'],
                        ];
                    @endphp
                    @foreach($mobileCategories as $cat)
                        <div class="border-b">
                            <a href="{{ route('search', ['category' => $cat['slug']]) }}" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50" @click="isOpen = false">
                                <span class="font-medium">{{ $cat['name'] }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-gray-400"><path d="m9 18 6-6-6-6"></path></svg>
                            </a>
                        </div>
                    @endforeach
                    <div class="p-4">
                        <a href="{{ route('search') }}" class="block w-full py-2 px-4 bg-primary text-white text-center rounded-md" @click="isOpen = false">View All Deals</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sticky bottom nav bar --}}
        <div class="fixed bottom-0 left-0 right-0 bg-white shadow-lg border-t z-50 md:hidden">
            <div class="flex justify-around">
                <button @click="$dispatch('open-mobile-categories')" class="flex flex-col items-center py-2 px-4 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-gray-600"><rect width="7" height="7" x="3" y="3" rx="1"></rect><rect width="7" height="7" x="14" y="3" rx="1"></rect><rect width="7" height="7" x="14" y="14" rx="1"></rect><rect width="7" height="7" x="3" y="14" rx="1"></rect></svg>
                    <span class="text-xs mt-1 text-gray-600">Categories</span>
                </button>
                <a href="{{ route('wishlist.index') }}" class="flex flex-col items-center py-2 px-4 active:text-primary relative">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-gray-600"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg>
                    <span class="text-xs mt-1 text-gray-600">Wishlist</span>
                    <template x-if="wishlistedIds.length > 0">
                        <span 
                            x-text="wishlistedIds.length"
                            class="absolute top-1 right-2 bg-primary text-primary-foreground text-[10px] font-bold h-4 w-4 rounded-full flex items-center justify-center"
                        ></span>
                    </template>
                </a>
                <a href="#" class="flex flex-col items-center py-2 px-4 active:text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-gray-600"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path><path d="M3 6h18"></path><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                    <span class="text-xs mt-1 text-gray-600">Cart</span>
                </a>
                <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="flex flex-col items-center py-2 px-4 active:text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-gray-600"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <span class="text-xs mt-1 text-gray-600">{{ auth()->check() ? 'Account' : 'Sign In' }}</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
