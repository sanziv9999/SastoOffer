<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SastoOffer')</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

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
        wishlistedIds: {{ auth()->check() ? json_encode(auth()->user()->wishlist()->pluck('deal_id')->toArray()) : '[]' }},
        toasts: [],
        showToast(type, message) {
            const id = `${Date.now()}-${Math.random().toString(16).slice(2)}`;
            const t = { id, type, message: String(message || '') };
            this.toasts.unshift(t);
            this.toasts = this.toasts.slice(0, 3);
            window.setTimeout(() => {
                this.toasts = this.toasts.filter(x => x.id !== id);
            }, 3500);
        },
        cart: {
            items: [],
            count: {{ auth()->check() ? auth()->user()->cartItems()->count() : 0 }},
            total: 0,
            loading: false,
            isOpen: false,
            getErrorMessage(data, fallback = 'Could not update cart. Please try again.') {
                if (!data) return fallback;
                if (typeof data.message === 'string' && data.message.trim() !== '') return data.message;
                if (typeof data.error === 'string' && data.error.trim() !== '') return data.error;
                return fallback;
            },
            async fetchSummary() {
                if (this.loading) return;
                // Only show loading spinner if we don't have items yet
                if (this.items.length === 0) {
                    this.loading = true;
                }
                const res = await fetch('/cart/summary');
                const data = await res.json();
                this.items = data.items;
                this.total = data.total;
                this.count = data.count;
                this.loading = false;
            },
            async addItem(pivotId, qty = 1) {
                if (!{{ auth()->check() ? 'true' : 'false' }}) {
                    window.location.href = '/login';
                    return { success: false };
                }

                try {
                    const res = await fetch('/cart/add', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ offerPivotId: pivotId, quantity: qty })
                    });

                    const data = await res.json();
                    if (res.ok && data.status === 'success') {
                        this.count = data.cartCount;
                        this.isOpen = true; // Open the mini cart to show it was added
                        this.fetchSummary();
                        return { success: true };
                    }

                    window.dispatchEvent(new CustomEvent('sasto-toast', {
                        detail: { type: 'error', message: this.getErrorMessage(data) }
                    }));
                    return { success: false, message: this.getErrorMessage(data) };
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('sasto-toast', {
                        detail: { type: 'error', message: 'Could not add this item to cart. Please try again.' }
                    }));
                    return { success: false };
                }
            },
            async removeItem(itemId) {
                const res = await fetch(`/cart/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.status === 'success') {
                    this.items = this.items.filter(i => i.id !== itemId);
                    this.total = data.cartTotal;
                    this.count = data.cartCount;
                } else {
                    window.dispatchEvent(new CustomEvent('sasto-toast', {
                        detail: { type: 'error', message: this.getErrorMessage(data, 'Could not remove this item. Please try again.') }
                    }));
                }
            },
            async updateQty(itemId, newQty) {
                if (newQty < 1) return;

                try {
                    const res = await fetch(`/cart/${itemId}`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ quantity: newQty })
                    });

                    const data = await res.json();
                    if (res.ok && data.status === 'success') {
                        const item = this.items.find(i => i.id === itemId);
                        if (item) item.quantity = newQty;
                        this.total = data.cartTotal;
                        this.count = data.cartCount;
                        return;
                    }

                    window.dispatchEvent(new CustomEvent('sasto-toast', {
                        detail: { type: 'error', message: this.getErrorMessage(data) }
                    }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('sasto-toast', {
                        detail: { type: 'error', message: 'Could not update quantity. Please try again.' }
                    }));
                }
            }
        },
        toggleWishlist(id) {
            if (!{{ auth()->check() ? 'true' : 'false' }}) {
                window.location.href = '/login';
                return;
            }
            const numericId = Number(id);
            if (!Number.isFinite(numericId) || numericId <= 0) {
                return;
            }
            fetch(`/wishlist/toggle/${numericId}`, {
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
                    if (!this.wishlistedIds.includes(numericId)) {
                        this.wishlistedIds.push(numericId);
                    }
                } else {
                    this.wishlistedIds = this.wishlistedIds.filter(i => Number(i) !== numericId);
                    if (window.location.pathname === '/wishlist') {
                        // Refresh or remove from DOM if we are on the wishlist page
                        window.location.reload();
                    }
                }
            });
        }
    }"
    x-init="window.addEventListener('sasto-toast', (e) => { $data.showToast(e.detail.type, e.detail.message) })"
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

        {{-- Toasts (Alpine-only) --}}
        <div class="fixed top-24 right-4 z-[200] space-y-2 max-w-[320px] pointer-events-none">
            <template x-for="t in toasts" :key="t.id">
                <div
                    class="pointer-events-auto rounded-xl border px-4 py-3 shadow-lg bg-white"
                    :class="t.type === 'error' ? 'border-red-200/80' : 'border-green-200/80'"
                >
                    <div class="text-sm font-semibold" x-text="t.type === 'error' ? 'Error' : 'Success'"></div>
                    <div class="text-xs mt-1 opacity-90" x-text="t.message"></div>
                </div>
            </template>
        </div>
        
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
                    @foreach($parentCategories as $cat)
                        <div class="border-b" x-data="{ open: false }">
                            @if($cat->children->count() > 0)
                                <button 
                                    @click="open = !open"
                                    class="flex items-center justify-between w-full px-4 py-3 hover:bg-gray-50 text-left transition-colors"
                                >
                                    <span class="font-medium text-foreground">{{ $cat->name }}</span>
                                    <svg 
                                        xmlns="http://www.w3.org/2000/svg" 
                                        width="16" height="16" 
                                        viewBox="0 0 24 24" 
                                        fill="none" 
                                        stroke="currentColor" 
                                        stroke-width="2.5" 
                                        stroke-linecap="round" 
                                        stroke-linejoin="round" 
                                        class="h-4 w-4 text-gray-400 transition-transform duration-300"
                                        :class="open ? 'rotate-180' : ''"
                                    >
                                        <path d="m6 9 6 6 6-6"></path>
                                    </svg>
                                </button>
                                
                                <div 
                                    x-show="open" 
                                    x-cloak 
                                    x-collapse
                                    class="bg-muted/30 border-t border-border/10"
                                >
                                    <a 
                                        href="{{ route('search', ['category' => $cat->slug]) }}" 
                                        class="flex items-center px-8 py-2.5 text-sm font-semibold text-primary/80 hover:bg-muted/50"
                                        @click="isOpen = false"
                                    >
                                        All in {{ $cat->name }}
                                    </a>
                                    @foreach($cat->children as $sub)
                                        <a 
                                            href="{{ route('search', ['category' => $cat->slug, 'subcategory' => $sub->slug]) }}" 
                                            class="flex items-center px-8 py-2.5 text-sm text-muted-foreground hover:bg-muted/50 hover:text-foreground transition-colors"
                                            @click="isOpen = false"
                                        >
                                            {{ $sub->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <a 
                                    href="{{ route('search', ['category' => $cat->slug]) }}" 
                                    class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition-colors" 
                                    @click="isOpen = false"
                                >
                                    <span class="font-medium text-foreground">{{ $cat->name }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-gray-400"><path d="m9 18 6-6-6-6"></path></svg>
                                </a>
                            @endif
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
                <a href="{{ route('cart.index') }}" class="flex flex-col items-center py-2 px-4 active:text-primary relative">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-gray-600"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>
                    <span class="text-xs mt-1 text-gray-600">Cart</span>
                    <template x-if="cart.count > 0">
                        <span 
                            x-text="cart.count"
                            class="absolute top-1 right-2 bg-secondary text-white text-[10px] font-bold h-4 w-4 rounded-full flex items-center justify-center"
                        ></span>
                    </template>
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
