<x-layout>
    @section('title', 'Your Cart - SastoOffer')

    <div class="container py-12 pb-24" 
        x-data="{ 
            cartItems: {{ json_encode($items) }},
            cartTotal: {{ $total }},
            updating: null,

            async updateQty(itemId, newQty) {
                if (newQty < 1) return;
                this.updating = itemId;
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
                    if (data.status === 'success') {
                        const item = this.cartItems.find(i => i.id === itemId);
                        if (item) item.quantity = newQty;
                        this.cartTotal = data.cartTotal;
                        // Update global cart state
                        this.cart.count = data.cartCount;
                    }
                } finally {
                    this.updating = null;
                }
            },

            async removeItem(itemId) {
                if (!confirm('Remove this item from your cart?')) return;
                this.updating = itemId;
                try {
                    const res = await fetch(`/cart/${itemId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        this.cartItems = this.cartItems.filter(i => i.id !== itemId);
                        this.cartTotal = data.cartTotal;
                        // Update global cart state
                        this.cart.count = data.cartCount;
                    }
                } finally {
                    this.updating = null;
                }
            }
        }"
    >
        <div class="max-w-5xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10 border-b pb-8">
                <div class="space-y-2">
                    <div class="flex items-center gap-3 mb-1">
                        <div class="p-2 h-10 w-10 flex items-center justify-center bg-secondary/10 rounded-full text-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>
                        </div>
                        <span class="text-sm font-semibold text-secondary uppercase tracking-wider">Ready to checkout</span>
                    </div>
                    <h1 class="text-4xl font-extrabold tracking-tight text-teal-950">My Shopping Cart</h1>
                    <p class="text-muted-foreground text-lg max-w-2xl leading-relaxed">
                        Review your items and proceed to checkout to snag these amazing offers.
                    </p>
                </div>
            </div>

            <template x-if="cartItems.length > 0">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {{-- Cart Items List --}}
                    <div class="lg:col-span-2 space-y-4">
                        <template x-for="item in cartItems" :key="item.id">
                            <div 
                                class="bg-white rounded-2xl border p-4 sm:p-6 shadow-sm hover:shadow-md transition-all flex flex-col sm:flex-row gap-6 relative"
                                :class="updating === item.id ? 'opacity-50 pointer-events-none' : ''"
                            >
                                <div class="h-24 w-full sm:w-32 bg-muted rounded-xl overflow-hidden shrink-0 shadow-inner">
                                    <img :src="item.image" :alt="item.title" class="h-full w-full object-cover">
                                </div>
                                <div class="flex-1 flex flex-col justify-between">
                                    <div class="flex justify-between items-start gap-4">
                                        <div>
                                            <h3 class="text-lg font-bold text-teal-950 hover:text-primary transition-colors cursor-pointer" x-text="item.title"></h3>
                                            <p class="text-sm text-primary font-medium mt-1" x-text="item.typeLabel"></p>
                                        </div>
                                        <button @click="removeItem(item.id)" class="text-muted-foreground hover:text-red-500 transition-colors p-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>
                                        </button>
                                    </div>
                                    
                                    <div class="flex items-center justify-between mt-6">
                                        <div class="flex items-center gap-3 bg-muted/30 p-1.5 rounded-full border">
                                            <button 
                                                @click="updateQty(item.id, item.quantity - 1)"
                                                class="h-8 w-8 flex items-center justify-center rounded-full bg-white shadow-sm hover:bg-primary hover:text-white transition-all disabled:opacity-50"
                                                :disabled="item.quantity <= 1"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M5 12h14"></path></svg>
                                            </button>
                                            <span class="w-8 text-center font-bold text-teal-950" x-text="item.quantity"></span>
                                            <button 
                                                @click="updateQty(item.id, item.quantity + 1)"
                                                class="h-8 w-8 flex items-center justify-center rounded-full bg-white shadow-sm hover:bg-primary hover:text-white transition-all"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
                                            </button>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm text-muted-foreground line-through" x-text="`Rs. ${item.originalPrice * item.quantity}`"></p>
                                            <p class="text-xl font-extrabold text-primary" x-text="`Rs. ${item.discountedPrice * item.quantity}`"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Summary Sidebar --}}
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-3xl border p-8 shadow-sm sticky top-32">
                            <h2 class="text-xl font-extrabold text-teal-950 mb-6">Order Summary</h2>
                            
                            <div class="space-y-4 mb-8">
                                <div class="flex justify-between text-muted-foreground">
                                    <span>Subtotal</span>
                                    <span x-text="`Rs. ${cartTotal}`"></span>
                                </div>
                                <div class="flex justify-between text-muted-foreground">
                                    <span>Shipping</span>
                                    <span class="text-primary font-medium">FREE</span>
                                </div>
                                <div class="border-t pt-4 flex justify-between items-center">
                                    <span class="text-lg font-bold text-teal-950">Total</span>
                                    <span class="text-2xl font-black text-primary" x-text="`Rs. ${cartTotal}`"></span>
                                </div>
                            </div>

                            <a 
                                href="/checkout"
                                class="inline-flex w-full items-center justify-center rounded-2xl bg-primary text-primary-foreground text-lg font-bold shadow-lg hover:bg-primary/90 h-14 px-8 transition-all hover:-translate-y-1 active:scale-95 mb-4"
                            >
                                PROCEED TO CHECKOUT
                            </a>
                            
                            <p class="text-center text-xs text-muted-foreground">
                                Secure checkout powered by SastoOffer.
                            </p>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="cartItems.length === 0">
                <div class="flex min-h-[500px] flex-col items-center justify-center rounded-3xl border-2 border-dashed border-muted p-12 text-center bg-muted/10">
                    <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-2xl bg-white shadow-md mb-8 -rotate-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-12 w-12 text-muted-foreground opacity-40"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>
                    </div>
                    <h2 class="text-3xl font-extrabold text-teal-950 mb-4">Your cart is currently empty</h2>
                    <p class="mb-10 text-muted-foreground text-lg max-w-md mx-auto leading-relaxed">
                        Looks like you haven't added any deals to your cart yet. Start exploring or check your wishlist!
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a 
                            href="{{ route('search') }}"
                            class="inline-flex items-center justify-center rounded-full text-base font-bold transition-all bg-primary text-primary-foreground shadow-lg hover:bg-primary/90 h-14 px-10 hover:-translate-y-1 active:scale-95"
                        >
                            START SHOPPING
                        </a>
                        <a 
                            href="{{ route('wishlist.index') }}"
                            class="inline-flex items-center justify-center rounded-full text-base font-bold transition-all border-2 border-border bg-white shadow-sm hover:bg-muted h-14 px-10 active:scale-95"
                        >
                            VIEW WISHLIST
                        </a>
                    </div>
                </div>
            </template>
        </div>
    </div>
</x-layout>
