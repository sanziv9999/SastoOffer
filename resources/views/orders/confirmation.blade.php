<x-layout>
    @section('title', 'Offer Claimed - SastoOffer')

    <div class="container py-12">
        <div class="max-w-2xl mx-auto px-4">
            {{-- Success Banner --}}
            <div class="text-center mb-10">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8 text-green-600"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><path d="m9 11 3 3L22 4"></path></svg>
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight text-teal-950 mb-2">Offer Claimed!</h1>
                <p class="text-muted-foreground">
                    Your claim <span class="font-mono font-semibold text-teal-900">{{ $order->order_number }}</span> has been created and is <span class="font-semibold text-amber-600">{{ $order->status }}</span>.
                </p>
            </div>

            {{-- Order Summary Card --}}
            <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
                <div class="p-6 border-b bg-muted/30">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Claim Reference</p>
                            <p class="text-lg font-mono font-bold text-teal-950">{{ $order->order_number }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-amber-100 text-amber-800 text-xs font-semibold px-3 py-1">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    @if($order->vendor)
                        <p class="text-sm text-muted-foreground mt-1">Vendor: <span class="font-medium text-teal-900">{{ $order->vendor->business_name }}</span></p>
                    @endif
                </div>

                {{-- Items --}}
                <div class="divide-y">
                    @foreach($order->items as $item)
                        <div class="flex items-center gap-4 p-4">
                            @if(!empty($item->meta['deal_image']))
                                <img src="{{ $item->meta['deal_image'] }}" alt="{{ $item->title }}" class="h-14 w-14 rounded-lg object-cover flex-shrink-0 border">
                            @else
                                <div class="h-14 w-14 rounded-lg bg-muted flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-muted-foreground"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path></svg>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-teal-950 truncate">{{ $item->title }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ $item->meta['offer_type'] ?? 'Offer' }} &middot; Qty: {{ $item->quantity }}
                                </p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="font-bold text-teal-950">Rs. {{ number_format($item->line_total, 2) }}</p>
                                <p class="text-[10px] text-muted-foreground">@ Rs. {{ number_format($item->unit_price, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Totals --}}
                <div class="p-6 border-t bg-muted/20 space-y-2">
                    @if((float)$order->discount_total > 0)
                        <div class="flex justify-between text-sm text-muted-foreground">
                            <span>Savings</span>
                            <span class="text-green-600 font-medium">- Rs. {{ number_format($order->discount_total, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm text-muted-foreground">
                        <span>Subtotal</span>
                        <span>Rs. {{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    <div class="border-t pt-3 flex justify-between items-end">
                        <span class="text-base font-bold text-teal-950">Total</span>
                        <span class="text-2xl font-bold text-teal-950">
                            <span class="text-xs font-normal mr-0.5 opacity-70">Rs.</span>{{ number_format($order->grand_total, 2) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('dashboard.purchases') }}" class="inline-flex items-center justify-center rounded-xl bg-teal-950 text-white text-sm font-semibold shadow-lg hover:bg-teal-900 h-11 px-6 transition-all">
                    View My Claimed Offers
                </a>
                <a href="{{ route('search') }}" class="inline-flex items-center justify-center rounded-xl border border-border bg-white text-teal-950 text-sm font-semibold shadow-sm hover:bg-muted h-11 px-6 transition-all">
                    Continue Shopping
                </a>
            </div>
        </div>
    </div>
</x-layout>
