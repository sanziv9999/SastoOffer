<header
    class="bg-background shadow-sm"
    x-data="{
        mobileMenuOpen: false,
        searchQuery: '{{ addslashes(request('q', '')) }}',
        selectedCity: '{{ addslashes(request('city', request('district', 'All Districts'))) }}',
        submitSearch() {
            const params = new URLSearchParams(window.location.search);
            const query = this.searchQuery.trim();

            if (query) {
                params.set('q', query);
            } else {
                params.delete('q');
            }

            if (this.selectedCity && this.selectedCity !== 'All Districts') {
                params.set('city', this.selectedCity);
            } else {
                params.delete('city');
            }

            window.location.href = '{{ route('search') }}' + (params.toString() ? `?${params.toString()}` : '');
        }
    }"
>
    <div class="container mx-auto px-4">
        {{-- Full header with categories always visible --}}
        <div class="flex flex-col">
            <div class="flex items-center justify-between py-3">
                <a href="{{ route('home') }}" class="flex-shrink-0">
                    <div class="flex items-center gap-1">
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M7 7l3.5 3.5c.3.3.4.7.4 1.1V19c0 .6-.4 1-1 1s-1-.4-1-1v-6.6c0-.4-.1-.8-.4-1.1L5 7.8c-.5-.5-.5-1.4 0-1.9.5-.5 1.4-.5 2 0l.1.1c.5.5.5 1.4 0 1.9L7 7z"></path><path d="M12.5 2h3s2.5 0 2.5 2.5V7c0 2.5-2.5 2.5-2.5 2.5h-3c-2.5 0-2.5-2.5-2.5-2.5V4.5C10 2 12.5 2 12.5 2z"></path><path d="M15 12a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"></path><circle cx="15" cy="15" r="3"></circle></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute bottom-0 right-0 text-secondary"><line x1="19" x2="5" y1="5" y2="19"></line><circle cx="6.5" cy="6.5" r="2.5"></circle><circle cx="17.5" cy="17.5" r="2.5"></circle></svg>
                        </div>
                        <span class="text-2xl font-bold text-primary">Offer Oasis</span>
                    </div>
                </a>

                {{-- Desktop search bar --}}
                <div class="hidden md:flex flex-1 max-w-xl mx-6">
                    <form class="flex w-full bg-muted rounded-lg border border-border focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                        <div class="relative flex-grow">
                            <input
                                type="search"
                                placeholder="Search deals..."
                                class="w-full pl-10 border-0 shadow-none bg-transparent rounded-lg h-10 text-sm outline-none"
                                x-model="searchQuery"
                            >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-3 h-4 w-4 text-muted-foreground"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                        </div>
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg h-10 w-10 bg-primary text-primary-foreground hover:bg-primary/90 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                        </button>
                    </form>
                </div>

                {{-- Right side actions --}}
                <div class="flex items-center gap-2" x-data="{ cityOpen: false, userOpen: false }">
                    <div class="hidden md:flex items-center relative">
                        <button 
                            @click="cityOpen = !cityOpen"
                            class="h-9 border-0 bg-muted/50 rounded-full px-3 text-sm gap-1 flex items-center min-w-[120px] hover:bg-muted/70 transition-colors"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary flex-shrink-0"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <span x-text="selectedCity"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-auto opacity-50"><path d="m6 9 6 6 6-6"></path></svg>
                        </button>
                        {{-- District Dropdown --}}
                        <div 
                            x-show="cityOpen" 
                            @click.outside="cityOpen = false"
                            class="absolute top-full right-0 mt-2 w-48 bg-background border border-border shadow-lg rounded-md z-50 py-1 max-h-[300px] overflow-y-auto"
                            x-cloak
                        >
                            @php
                                $cities = [
                                    "All Districts",
                                    "Achham",
                                    "Arghakhanchi",
                                    "Baglung",
                                    "Baitadi",
                                    "Bajhang",
                                    "Bajura",
                                    "Banke",
                                    "Bara",
                                    "Bardiya",
                                    "Bhaktapur",
                                    "Bhojpur",
                                    "Chitwan",
                                    "Dadeldhura",
                                    "Dailekh",
                                    "Dang",
                                    "Darchula",
                                    "Dhading",
                                    "Dhankuta",
                                    "Dhanusha",
                                    "Dolakha",
                                    "Dolpa",
                                    "Doti",
                                    "Eastern Rukum",
                                    "Gorkha",
                                    "Gulmi",
                                    "Humla",
                                    "Ilam",
                                    "Jajarkot",
                                    "Jhapa",
                                    "Jumla",
                                    "Kailali",
                                    "Kalikot",
                                    "Kanchanpur",
                                    "Kapilvastu",
                                    "Kaski",
                                    "Kathmandu",
                                    "Kavrepalanchok",
                                    "Khotang",
                                    "Lalitpur",
                                    "Lamjung",
                                    "Mahottari",
                                    "Makwanpur",
                                    "Manang",
                                    "Morang",
                                    "Mugu",
                                    "Mustang",
                                    "Myagdi",
                                    "Nawalpur",
                                    "Nuwakot",
                                    "Okhaldhunga",
                                    "Palpa",
                                    "Panchthar",
                                    "Parasi",
                                    "Parbat",
                                    "Parsa",
                                    "Pyuthan",
                                    "Ramechhap",
                                    "Rasuwa",
                                    "Rautahat",
                                    "Rolpa",
                                    "Rupandehi",
                                    "Salyan",
                                    "Sankhuwasabha",
                                    "Saptari",
                                    "Sarlahi",
                                    "Sindhuli",
                                    "Sindhupalchok",
                                    "Siraha",
                                    "Solukhumbu",
                                    "Sunsari",
                                    "Surkhet",
                                    "Syangja",
                                    "Tanahun",
                                    "Taplejung",
                                    "Tehrathum",
                                    "Udayapur",
                                    "Western Rukum"
                                ];
                            @endphp
                            @foreach($cities as $city)
                                <button 
                                    class="w-full text-left px-4 py-2 text-sm hover:bg-primary hover:text-primary-foreground transition-colors"
                                    @click="selectedCity = '{{ $city }}'; cityOpen = false; submitSearch();"
                                >
                                    {{ $city }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="hidden md:flex items-center gap-1">
                        {{-- Wishlist/Cart buttons placeholders --}}
                        <a href="{{ route('wishlist.index') }}" class="p-2 hover:bg-muted rounded-full transition-colors relative" title="My Wishlist">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg>
                            <template x-if="wishlistedIds.length > 0">
                                <span 
                                    x-text="wishlistedIds.length"
                                    class="absolute -top-1 -right-1 bg-primary text-primary-foreground text-[10px] font-bold h-4 w-4 rounded-full flex items-center justify-center animate-in zoom-in duration-300"
                                ></span>
                            </template>
                        </a>
                        {{-- Cart Button & Dropdown --}}
                        <div 
                            class="relative"
                            x-on:mouseenter="if(!cart.isOpen) cart.fetchSummary()"
                        >
                            <button 
                                @click="cart.isOpen = !cart.isOpen"
                                class="p-2 hover:bg-muted rounded-full transition-colors relative"
                                title="My Cart"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>
                                <template x-if="cart.count > 0">
                                    <span 
                                        x-text="cart.count"
                                        class="absolute -top-1 -right-1 bg-secondary text-white text-[10px] font-bold h-4 w-4 rounded-full flex items-center justify-center animate-in zoom-in duration-300"
                                    ></span>
                                </template>
                            </button>

                            {{-- Mini Cart Modal/Dropdown --}}
                            <div 
                                x-show="cart.isOpen" 
                                @click.outside="cart.isOpen = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                class="absolute right-0 mt-3 w-80 bg-white border border-border rounded-xl shadow-2xl z-50 overflow-hidden"
                                x-cloak
                            >
                                <div class="p-4 border-b bg-muted/20">
                                    <div class="flex items-center justify-between">
                                        <h3 class="font-bold text-teal-950">Your Cart</h3>
                                        <span class="text-xs text-muted-foreground" x-text="`${cart.count} items`"></span>
                                    </div>
                                </div>

                                <div class="max-h-[350px] overflow-y-auto overflow-x-hidden">
                                    <template x-if="cart.loading">
                                        <div class="p-8 text-center">
                                            <div class="animate-spin h-6 w-6 border-2 border-primary border-t-transparent rounded-full mx-auto"></div>
                                        </div>
                                    </template>

                                    <template x-if="!cart.loading && cart.items.length === 0">
                                        <div class="p-10 text-center">
                                            <div class="h-16 w-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-4 opacity-50">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8 text-muted-foreground"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>
                                            </div>
                                            <p class="text-sm font-medium text-muted-foreground">Your cart is empty</p>
                                        </div>
                                    </template>

                                    <template x-for="item in cart.items" :key="item.id">
                                        <div class="p-4 border-b hover:bg-muted/10 flex gap-3 group transition-colors relative">
                                            <a :href="item.url" class="h-16 w-16 bg-muted rounded-md overflow-hidden shrink-0">
                                                <img :src="item.image" :alt="item.title" class="h-full w-full object-cover">
                                            </a>
                                            <div class="flex-1 min-w-0 pr-6">
                                                <a :href="item.url" class="text-sm font-semibold text-foreground line-clamp-1 hover:text-primary transition-colors" x-text="item.title"></a>
                                                <div class="flex items-center justify-between mt-2">
                                                    {{-- Mini Quantity Toggle --}}
                                                    <div class="flex items-center gap-2 bg-muted/40 p-1 rounded-full border border-border/50">
                                                        <button 
                                                            @click.stop="cart.updateQty(item.id, item.quantity - 1)"
                                                            class="h-5 w-5 flex items-center justify-center rounded-full bg-white shadow-sm hover:bg-primary hover:text-white transition-all disabled:opacity-30"
                                                            :disabled="item.quantity <= 1"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path></svg>
                                                        </button>
                                                        <span class="text-xs font-bold w-4 text-center" x-text="item.quantity"></span>
                                                        <button 
                                                            @click.stop="cart.updateQty(item.id, item.quantity + 1)"
                                                            class="h-5 w-5 flex items-center justify-center rounded-full bg-white shadow-sm hover:bg-primary hover:text-white transition-all"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
                                                        </button>
                                                    </div>
                                                    <span class="text-sm font-bold text-primary" x-text="`Rs. ${item.quantity * item.discountedPrice}`"></span>
                                                </div>
                                            </div>
                                            {{-- Remove Button --}}
                                            <button 
                                                @click.stop="cart.removeItem(item.id)"
                                                class="absolute top-4 right-3 text-muted-foreground/40 hover:text-red-500 transition-colors p-1"
                                                title="Remove Item"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>

                                <div class="p-4 bg-muted/5 space-y-3">
                                    <div class="flex items-center justify-between font-bold text-lg px-2">
                                        <span>Total</span>
                                        <span x-text="`Rs. ${cart.total}`"></span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <a href="{{ route('cart.index') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus:ring-1 focus:ring-ring border bg-white hover:bg-muted h-10 px-4">
                                            View Cart
                                        </a>
                                        <a href="/checkout" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus:ring-1 focus:ring-ring bg-secondary text-white hover:bg-secondary/90 h-10 px-4 shadow-sm">
                                            Checkout
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @auth
                        <div class="relative">
                            <button @click="userOpen = !userOpen" class="inline-flex items-center justify-center h-9 w-9 rounded-full hover:bg-muted transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </button>
                            <div 
                                x-show="userOpen" 
                                @click.outside="userOpen = false"
                                class="absolute top-full right-0 mt-2 w-56 bg-white border border-border shadow-lg rounded-md z-50 py-1"
                                x-cloak
                            >
                                <div class="px-4 py-2 font-semibold text-sm border-b">My Account</div>
                                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2 text-sm hover:bg-muted transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4"><rect width="7" height="9" x="3" y="3" rx="1"></rect><rect width="7" height="5" x="14" y="3" rx="1"></rect><rect width="7" height="9" x="14" y="12" rx="1"></rect><rect width="7" height="5" x="3" y="16" rx="1"></rect></svg>
                                    Dashboard
                                </a>
                                <div class="border-t"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center px-4 py-2 text-sm text-destructive hover:bg-muted transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                        Log out
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center h-9 px-5 rounded-full bg-primary text-primary-foreground text-sm font-medium hover:bg-primary/90 transition-colors">
                            Sign In
                        </a>
                    @endauth

                    <button class="md:hidden inline-flex items-center justify-center p-2 rounded-md hover:bg-muted transition-colors" @click="mobileMenuOpen = !mobileMenuOpen">
                        <svg x-show="!mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><line x1="4" x2="20" y1="12" y2="12"></line><line x1="4" x2="20" y1="6" y2="6"></line><line x1="4" x2="20" y1="18" y2="18"></line></svg>
                        <svg x-show="mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" x-cloak><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                    </button>
                </div>
            </div>

            {{-- Mobile search --}}
            <div class="md:hidden pb-3">
                <form class="flex bg-muted rounded-lg border border-border overflow-hidden focus-within:border-primary">
                    <div class="relative flex-grow flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 h-4 w-4 text-muted-foreground"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                        <input
                            type="search"
                            placeholder="Search deals..."
                            class="w-full pl-10 border-0 shadow-none bg-transparent h-9 rounded-lg text-sm outline-none"
                            x-model="searchQuery"
                        >
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg h-9 w-9 bg-primary text-primary-foreground hover:bg-primary/90 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                    </button>
                </form>
            </div>
            
            <div 
                class="relative group transition-all duration-300"
            >
                <div 
                    class="py-2"
                >
                    <nav class="flex flex-wrap items-center gap-0.5" x-data="{ openCategory: null }">
                        @foreach($parentCategories as $category)
                            <div
                                class="relative"
                                @mouseenter="openCategory = '{{ $category['id'] }}'"
                                @mouseleave="openCategory = null"
                            >
                                <button 
                                    @click="openCategory = (openCategory === '{{ $category['id'] }}' ? null : '{{ $category['id'] }}')"
                                    class="flex items-center gap-1.5 text-foreground h-8 px-3 py-1 text-sm bg-transparent hover:bg-primary hover:text-primary-foreground rounded-full transition-colors font-medium"
                                >
                                    @switch($category['icon'])
                                        @case('utensils')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 flex-shrink-0"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"></path><path d="M7 2v20"></path><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"></path></svg>
                                            @break
                                        @case('scissors')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 flex-shrink-0"><circle cx="6" cy="6" r="3"></circle><path d="M8.12 8.12 12 12"></path><path d="M20 4 8.12 15.88"></path><circle cx="6" cy="18" r="3"></circle><path d="M14.8 14.8 20 20"></path></svg>
                                            @break
                                        @case('coffee')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 flex-shrink-0"><path d="M17 8h1a4 4 0 1 1 0 8h-1"></path><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"></path><line x1="6" x2="6" y1="2" y2="4"></line><line x1="10" x2="10" y1="2" y2="4"></line><line x1="14" x2="14" y1="2" y2="4"></line></svg>
                                            @break
                                        @case('plane')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 flex-shrink-0"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1Z"></path></svg>
                                            @break
                                        @case('smartphone')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 flex-shrink-0"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"></rect><path d="M12 18h.01"></path></svg>
                                            @break
                                        @case('heart')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 flex-shrink-0"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg>
                                            @break
                                        @case('book')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 flex-shrink-0"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                                            @break
                                        @default
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 flex-shrink-0"><path d="M20 12V22H4V12"></path><path d="M22 7H2v5h20V7z"></path><path d="M12 22V7"></path><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path></svg>
                                    @endswitch
                                    <span>{{ $category['name'] }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="h-3 w-3 opacity-60 flex-shrink-0"><path d="m6 9 6 6 6-6"></path></svg>
                                </button>

                                {{-- ultra-Compact Dropdown --}}
                                <div
                                    x-show="openCategory === '{{ $category['id'] }}'"
                                    x-cloak
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-1"
                                    class="absolute left-0 top-full mt-1 z-50 w-60 bg-background border border-border shadow-xl rounded-lg p-2"
                                >
                                    <div class="flex items-center justify-between mb-2 px-2 pb-1 border-b border-border/40">
                                        <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">{{ $category['name'] }}</span>
                                        <a href="{{ route('search', ['category' => $category->slug]) }}" class="text-[10px] font-bold text-primary hover:underline underline-offset-2">All</a>
                                    </div>
                                    
                                    <div class="flex flex-col gap-0.5">
                                        @foreach($category->children as $sub)
                                            <a 
                                                href="{{ route('search', ['category' => $category->slug, 'subcategory' => $sub->slug]) }}" 
                                                class="flex items-center px-2 py-1.5 rounded hover:bg-muted transition-colors group/sub"
                                            >
                                                <span class="text-xs font-semibold text-slate-600 group-hover/sub:text-primary transition-colors">{{ $sub->name }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- All Deals link --}}
                        <a
                            href="{{ route('search') }}"
                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 h-8 text-sm font-medium text-primary hover:bg-muted transition-colors flex-shrink-0"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>
                            All Deals
                        </a>
                    </nav>
                </div>
            </div>

            {{-- Mobile menu --}}
            <div 
                x-show="mobileMenuOpen" 
                x-cloak 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="md:hidden py-4 border-t border-border"
            >
                <div class="flex items-center gap-2 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-primary"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    <select 
                        x-model="selectedCity"
                        @change="submitSearch()"
                        class="flex-1 h-9 rounded-full bg-muted border-0 text-sm px-3 outline-none"
                    >
                        @foreach($cities as $city)
                            <option value="{{ $city }}">{{ $city }}</option>
                        @endforeach
                    </select>
                </div>
                
                @guest
                    <div class="flex flex-col gap-2 pt-3 border-t border-border">
                        <a href="{{ route('login') }}" class="w-full h-10 inline-flex items-center justify-center rounded-full bg-primary text-primary-foreground font-medium transition-colors">Sign In</a>
                        <a href="{{ route('register') }}" class="w-full h-10 inline-flex items-center justify-center rounded-full border border-border bg-background hover:bg-muted transition-colors">Sign Up</a>
                    </div>
                @else
                    <div class="flex flex-col gap-3 pt-3 border-t border-border">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-foreground hover:bg-primary hover:text-primary-foreground px-3 py-2 rounded-lg transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><rect width="7" height="9" x="3" y="3" rx="1"></rect><rect width="7" height="5" x="14" y="3" rx="1"></rect><rect width="7" height="9" x="14" y="12" rx="1"></rect><rect width="7" height="5" x="3" y="16" rx="1"></rect></svg>
                            <span>Dashboard</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center gap-2 text-destructive hover:text-destructive/80 text-left py-2 px-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                <span>Log out</span>
                            </button>
                        </form>
                    </div>
                @endguest
            </div>
        </div>
    </div>
</header>
