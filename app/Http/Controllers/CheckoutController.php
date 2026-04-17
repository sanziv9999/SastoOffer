<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\DealOfferType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\ActivityMailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function placeOrder(Request $request, ActivityMailer $activityMailer)
    {
        $user = auth()->user();

        $cartItems = $user->cartItems()
            ->with(['offerType.deal.vendor', 'offerType.offerType'])
            ->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Your cart is empty.');
        }

        // Block checkout if any cart offer pivot is not active.
        $inactiveOfferPivots = $cartItems
            ->filter(fn ($ci) => ($ci->offerType?->status ?? null) !== 'active')
            ->values()
            ->all();

        if (! empty($inactiveOfferPivots)) {
            return back()->with('error', 'Some offers in your cart have expired. Please refresh your cart.');
        }

        // First X customers offers: one quantity per customer.
        foreach ($cartItems as $cartItem) {
            $pivot = $cartItem->offerType;
            if (! $pivot) {
                continue;
            }

            if ($this->isFirstXCustomersOffer($pivot) && (int) $cartItem->quantity > 1) {
                return back()->with('error', 'This is a limited first-X offer. You can order only 1 quantity for this offer.');
            }
        }

        $orders = DB::transaction(function () use ($user, $cartItems) {
            $grouped = $cartItems->groupBy(fn (CartItem $item) => $item->offerType->deal->vendor_id ?? 0);
            $sharedOrderNumber = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));

            $createdOrders = [];

            foreach ($grouped as $vendorId => $items) {
                $subtotal = $items->sum(fn (CartItem $item) => (float) $item->offerType->final_price * $item->quantity);
                $discountTotal = $items->sum(function (CartItem $item) {
                    $original = (float) $item->offerType->original_price;
                    $final = (float) $item->offerType->final_price;
                    return max(0, ($original - $final) * $item->quantity);
                });

                $order = Order::create([
                    'user_id' => $user->id,
                    'vendor_id' => $vendorId ?: null,
                    'order_number' => $sharedOrderNumber,
                    'status' => 'pending',
                    'currency_code' => 'NPR',
                    'subtotal' => $subtotal,
                    'discount_total' => $discountTotal,
                    'tax_total' => 0,
                    'grand_total' => $subtotal,
                ]);

                foreach ($items as $cartItem) {
                    $pivot = $cartItem->offerType;
                    $deal = $pivot->deal;
                    $claimToken = $this->generateClaimToken();

                    OrderItem::create([
                        'order_id' => $order->id,
                        'deal_id' => $deal->id,
                        'deal_offer_type_id' => $pivot->id,
                        'title' => $deal->title,
                        'quantity' => $cartItem->quantity,
                        'unit_price' => (float) $pivot->final_price,
                        'line_total' => (float) $pivot->final_price * $cartItem->quantity,
                        'meta' => [
                            'offer_type' => $pivot->offerType?->display_name ?? $pivot->offerType?->name,
                            'original_price' => (float) $pivot->original_price,
                            'deal_slug' => $deal->slug,
                            'deal_image' => $deal->featuredImageUrl(),
                            'claim_token' => $claimToken,
                            'claimed_at' => null,
                            'claimed_by_user_id' => null,
                        ],
                    ]);
                }

                $createdOrders[] = $order;
            }

            $user->cartItems()->delete();

            return $createdOrders;
        });

        $firstOrder = $orders[0] ?? null;

        foreach ($orders as $createdOrder) {
            try {
                $activityMailer->sendOrderPlacedVendor($createdOrder);
            } catch (\Throwable $e) {
                Log::warning('Order activity mail failed', [
                    'order_id' => $createdOrder->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        if ($firstOrder) {
            try {
                // Send one customer confirmation for the shared checkout order number.
                $activityMailer->sendOrderPlacedCustomer($firstOrder);
            } catch (\Throwable $e) {
                Log::warning('Customer order confirmation mail failed', [
                    'order_id' => $firstOrder->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()
            ->route('order.confirmation', $firstOrder->id)
            ->with('success', 'Order placed successfully!');
    }

    public function confirmation(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items', 'vendor');

        return view('orders.confirmation', [
            'order' => $order,
        ]);
    }

    public function myOrders()
    {
        $groupedOrders = Order::where('user_id', auth()->id())
            ->with(['items.deal', 'vendor'])
            ->latest()
            ->get()
            ->groupBy(fn (Order $order) => $order->order_number ?: ('ORD-' . $order->id))
            ->map(function ($ordersInGroup, $orderNumber) {
                /** @var \Illuminate\Support\Collection<int, Order> $ordersInGroup */
                $firstOrder = $ordersInGroup->first();
                $vendors = $ordersInGroup->pluck('vendor')->filter();
                $uniqueVendorNames = $vendors->pluck('business_name')->filter()->unique()->values();
                $uniqueVendorSlugs = $vendors->pluck('slug')->filter()->unique()->values();
                $allItems = $ordersInGroup->flatMap(fn (Order $order) => $order->items);

                $statuses = $ordersInGroup->pluck('status')->filter()->values();
                $statusPriority = ['pending', 'paid', 'redeemed', 'cancelled', 'refunded'];
                $aggregatedStatus = collect($statusPriority)->first(fn ($status) => $statuses->contains($status)) ?? 'pending';
                $canCancel = $ordersInGroup->every(fn (Order $o) => $o->status === 'pending');

                return [
                    'id' => $firstOrder?->id,
                    'orderNumber' => $orderNumber,
                    'status' => $aggregatedStatus,
                    'canCancel' => $canCancel,
                    'cancellationReason' => $ordersInGroup
                        ->pluck('metadata')
                        ->filter()
                        ->map(fn ($m) => is_array($m) ? ($m['cancel_reason'] ?? null) : null)
                        ->filter()
                        ->first(),
                    'subtotal' => (float) $ordersInGroup->sum('subtotal'),
                    'discountTotal' => (float) $ordersInGroup->sum('discount_total'),
                    'taxTotal' => (float) $ordersInGroup->sum('tax_total'),
                    'grandTotal' => (float) $ordersInGroup->sum('grand_total'),
                    'currencyCode' => $firstOrder?->currency_code,
                    'paymentMethod' => $firstOrder?->payment_method,
                    'paidAt' => $ordersInGroup->pluck('paid_at')->filter()->max()?->toIso8601String(),
                    'vendorName' => $uniqueVendorNames->count() > 1
                        ? 'Multiple vendors (' . $uniqueVendorNames->count() . ')'
                        : ($uniqueVendorNames->first() ?? 'Unknown Vendor'),
                    'vendorSlug' => $uniqueVendorSlugs->count() === 1 ? $uniqueVendorSlugs->first() : null,
                    'createdAt' => $ordersInGroup->max('created_at')?->toIso8601String(),
                    'itemCount' => (int) $allItems->sum('quantity'),
                    'items' => $allItems->map(fn (OrderItem $item) => [
                        'id' => $item->id,
                        'dealId' => $item->deal_id,
                        'dealOfferTypeId' => $item->deal_offer_type_id,
                        'dealSlug' => $item->meta['deal_slug'] ?? $item->deal?->slug,
                        'title' => $item->title,
                        'quantity' => $item->quantity,
                        'unitPrice' => (float) $item->unit_price,
                        'originalPrice' => (float) ($item->meta['original_price'] ?? $item->unit_price),
                        'lineTotal' => (float) $item->line_total,
                        'image' => $item->meta['deal_image'] ?? '',
                        'offerType' => $item->meta['offer_type'] ?? 'Offer',
                        'claimToken' => $item->meta['claim_token'] ?? null,
                        'claimedAt' => $item->meta['claimed_at'] ?? null,
                        'isClaimed' => ! empty($item->meta['claimed_at']),
                    ])->values()->all(),
                ];
            })
            ->sortByDesc('createdAt')
            ->values();

        return \Inertia\Inertia::render('dashboard/MyPurchases', [
            'purchases' => $groupedOrders,
        ]);
    }

    public function cancelOrderGroup(Request $request, string $orderNumber, ActivityMailer $activityMailer)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);
        $reason = trim((string) $data['reason']);

        $orders = Order::where('user_id', auth()->id())
            ->where('order_number', $orderNumber)
            ->with('vendor.user')
            ->get();

        if ($orders->isEmpty()) {
            return back()->with('error', 'Order not found.');
        }

        if ($orders->contains(fn (Order $order) => $order->status !== 'pending')) {
            return back()->with('error', 'Only pending orders can be cancelled.');
        }

        DB::transaction(function () use ($orders, $reason) {
            foreach ($orders as $order) {
                $order->status = 'cancelled';
                $meta = is_array($order->metadata) ? $order->metadata : [];
                $meta['cancel_reason'] = $reason;
                $meta['cancelled_at'] = now()->toIso8601String();
                $meta['cancelled_by_user_id'] = auth()->id();
                $order->metadata = $meta;
                $order->save();
            }
        });

        try {
            $firstOrder = $orders->first();
            if ($firstOrder) {
                $activityMailer->sendOrderStatusChangedCustomer($firstOrder, 'cancelled');
            }
            foreach ($orders as $order) {
                $activityMailer->sendOrderStatusChangedVendor($order, 'cancelled');
            }
        } catch (\Throwable $e) {
            Log::warning('Order cancel status mail failed', [
                'order_number' => $orderNumber,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Order cancelled successfully.');
    }

    private function isFirstXCustomersOffer(?DealOfferType $pivot): bool
    {
        if (! $pivot) {
            return false;
        }

        $pivot->loadMissing('offerType');
        $rule = $pivot->offerType?->calculation_rule;
        if (is_string($rule)) {
            $rule = json_decode($rule, true) ?: [];
        }
        if (! is_array($rule)) {
            return false;
        }

        $availability = $rule['availability'] ?? null;
        return is_array($availability) && (($availability['mode'] ?? null) === 'first_x_customers');
    }

    private function generateClaimToken(): string
    {
        do {
            $token = 'CLM-' . now()->format('ymd') . '-' . strtoupper(Str::random(8));
            $exists = OrderItem::query()
                ->where('meta->claim_token', $token)
                ->exists();
        } while ($exists);

        return $token;
    }
}
