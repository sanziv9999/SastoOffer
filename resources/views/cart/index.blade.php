<x-layout>
    @section('title', 'Your Cart - SastoOffer')

    <div class="container pt-24 md:pt-12 pb-16 md:pb-24" 
        x-init="cart.items = {{ json_encode($items) }}; cart.total = {{ $total }};"
        x-data="{ 
            updating: null,

            async wrappedUpdateQty(itemId, newQty) {
                this.updating = itemId;
                await this.cart.updateQty(itemId, newQty);
                this.updating = null;
            },

            async wrappedRemoveItem(itemId) {
                if (!confirm('Remove this item from your cart?')) return;
                this.updating = itemId;
                await this.cart.removeItem(itemId);
                this.updating = null;
            }
        }"
    >
        <div class="max-w-5xl mx-auto px-4">
            @if (session('error'))
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif
            @if (session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6 border-b pb-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 mb-0.5">
                        <div class="p-1 h-6 w-6 flex items-center justify-center bg-secondary/10 rounded-full text-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>
                        </div>
                        <span class="text-[9px] font-bold text-secondary uppercase tracking-widest">Ready to checkout</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-teal-950">My Shopping Cart</h1>
                    <p class="text-muted-foreground text-xs md:text-sm max-w-2xl leading-relaxed">
                        Review your items and proceed to checkout.
                    </p>
                </div>
            </div>

            <template x-if="cart.items.length > 0">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {{-- Cart Items List --}}
                    <div class="lg:col-span-2 space-y-4">
                        <template x-for="item in cart.items" :key="item.id">
                            <div 
                                class="bg-white rounded-2xl border p-4 sm:p-6 shadow-sm hover:shadow-md transition-all flex flex-col sm:flex-row gap-6 relative"
                                :class="updating === item.id ? 'opacity-50 pointer-events-none' : ''"
                            >
                                <a :href="item.url" class="h-24 w-full sm:w-32 bg-muted rounded-xl overflow-hidden shrink-0 shadow-inner">
                                    <img :src="item.image" :alt="item.title" class="h-full w-full object-cover">
                                </a>
                                <div class="flex-1 flex flex-col justify-between">
                                    <div class="flex justify-between items-start gap-4">
                                        <div>
                                            <a :href="item.url" class="text-base md:text-lg font-bold text-teal-950 hover:text-primary transition-colors cursor-pointer" x-text="item.title"></a>
                                            <p class="text-xs md:text-sm text-primary font-medium mt-1" x-text="item.typeLabel"></p>
                                        </div>
                                        <button @click="wrappedRemoveItem(item.id)" class="text-muted-foreground/40 hover:text-red-500 transition-colors p-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>
                                        </button>
                                    </div>
                                    
                                    <div class="flex items-center justify-between mt-6">
                                        <div class="flex items-center gap-3 bg-muted/40 p-1.5 rounded-full border border-border/50 shadow-sm">
                                            <button 
                                                @click="wrappedUpdateQty(item.id, item.quantity - 1)"
                                                class="h-8 w-8 flex items-center justify-center rounded-full bg-white shadow-sm hover:bg-primary hover:text-white transition-all disabled:opacity-30 disabled:hover:bg-white disabled:hover:text-black"
                                                :disabled="item.quantity <= 1"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M5 12h14"></path></svg>
                                            </button>
                                            <span class="w-8 text-center font-bold text-teal-950" x-text="item.quantity"></span>
                                            <button 
                                                @click="wrappedUpdateQty(item.id, item.quantity + 1)"
                                                class="h-8 w-8 flex items-center justify-center rounded-full bg-white shadow-sm hover:bg-primary hover:text-white transition-all"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
                                            </button>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[10px] text-muted-foreground/50 line-through mb-0.5" x-text="`Rs. ${item.originalPrice * item.quantity}`"></p>
                                            <p class="text-base md:text-lg font-bold text-teal-950 leading-none">
                                                <span class="text-xs font-normal text-muted-foreground/70 mr-0.5">Rs.</span>
                                                <span x-text="item.discountedPrice * item.quantity"></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Summary Sidebar --}}
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-2xl border p-6 shadow-sm hover:shadow-md transition-shadow sticky top-32">
                            <h2 class="text-lg font-bold text-teal-950 mb-5">Order Summary</h2>
                            
                            <div class="space-y-3 mb-6">
                                <div class="flex justify-between text-sm text-muted-foreground">
                                    <span>Subtotal</span>
                                    <span class="text-teal-900 font-medium whitespace-nowrap">
                                        <span class="text-[10px] font-normal opacity-70">Rs.</span> <span x-text="cart.total"></span>
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm text-muted-foreground">
                                    <span>Shipping</span>
                                    <span class="text-primary font-bold uppercase text-[10px] tracking-wider">Free</span>
                                </div>
                                <div class="border-t border-border/60 pt-4 flex justify-between items-end">
                                    <span class="text-sm md:text-base font-bold text-teal-950">Total</span>
                                    <div class="text-right">
                                        <span class="text-[10px] text-muted-foreground block leading-none mb-1">Net Amount</span>
                                        <span class="text-xl md:text-2xl font-bold text-teal-950">
                                            <span class="text-xs font-normal mr-0.5 opacity-70">Rs.</span><span x-text="cart.total"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('checkout.place') }}">
                                @csrf
                                <button 
                                    type="submit"
                                    class="group relative inline-flex w-full items-center justify-center rounded-xl bg-teal-950 text-white text-sm font-semibold shadow-lg hover:bg-teal-900 h-12 px-6 transition-all hover:translate-y-[-1px] active:translate-y-[1px] overflow-hidden"
                                >
                                    <div class="absolute inset-0 bg-gradient-to-r from-primary/10 via-transparent to-primary/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                    <span class="relative flex items-center gap-2">
                                        PROCEED TO CHECKOUT
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="transition-transform group-hover:translate-x-1"><path d="m9 18 6-6-6-6"></path></svg>
                                    </span>
                                </button>
                            </form>
                            
                            <div class="mt-6 flex flex-col items-center">
                                <p class="text-center text-xs text-muted-foreground opacity-60">
                                    All transactions are secure and encrypted.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Empty Shopping Cart View --}}
            <template x-if="cart.items.length === 0">
                <div class="flex min-h-[350px] flex-col items-center justify-center rounded-3xl border-2 border-dashed border-muted p-8 text-center bg-muted/10">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-md mb-5 rotate-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8 text-muted-foreground opacity-40"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>
                    </div>
                    <h2 class="text-xl md:text-2xl font-bold text-teal-950 mb-2">Your cart is empty</h2>
                    <p class="mb-6 text-muted-foreground text-xs md:text-sm max-w-md mx-auto">
                        Looks like you haven't added anything to your cart yet. Browse our amazing deals and start saving today!
                    </p>
                    <a 
                        href="{{ route('search') }}"
                        class="inline-flex items-center justify-center rounded-full text-sm font-bold transition-all bg-primary text-primary-foreground shadow-lg hover:bg-primary/90 h-12 px-8 active:scale-95"
                    >
                        BROWSE DEALS
                    </a>
                </div>
            </template>
        </div>

        {{-- Featured Recommendations --}}
        <div class="mt-20">
            <x-featured-products :featuredDeals="$featuredDeals" />
        </div>
    </div>
</x-layout>
