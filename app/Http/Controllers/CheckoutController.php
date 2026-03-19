<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function placeOrder(Request $request)
    {
        $user = auth()->user();

        $cartItems = $user->cartItems()
            ->with(['offerType.deal.vendor', 'offerType.offerType'])
            ->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Your cart is empty.');
        }

        $orders = DB::transaction(function () use ($user, $cartItems) {
            $grouped = $cartItems->groupBy(fn (CartItem $item) => $item->offerType->deal->vendor_id ?? 0);

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
                    'order_number' => 'TMP-' . uniqid('', true),
                    'status' => 'pending',
                    'currency_code' => 'NPR',
                    'subtotal' => $subtotal,
                    'discount_total' => $discountTotal,
                    'tax_total' => 0,
                    'grand_total' => $subtotal,
                ]);
                $order->update([
                    'order_number' => 'ORD-' . now()->format('Ymd') . '-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
                ]);

                foreach ($items as $cartItem) {
                    $pivot = $cartItem->offerType;
                    $deal = $pivot->deal;

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
                            'deal_image' => $deal->featuredImageUrl(),
                        ],
                    ]);
                }

                $createdOrders[] = $order;
            }

            $user->cartItems()->delete();

            return $createdOrders;
        });

        $firstOrder = $orders[0] ?? null;

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
        $orders = Order::where('user_id', auth()->id())
            ->with(['items', 'vendor'])
            ->latest()
            ->get()
            ->map(function (Order $order) {
                return [
                    'id' => $order->id,
                    'orderNumber' => $order->order_number,
                    'status' => $order->status,
                    'subtotal' => (float) $order->subtotal,
                    'discountTotal' => (float) $order->discount_total,
                    'taxTotal' => (float) $order->tax_total,
                    'grandTotal' => (float) $order->grand_total,
                    'currencyCode' => $order->currency_code,
                    'paymentMethod' => $order->payment_method,
                    'paidAt' => $order->paid_at?->toIso8601String(),
                    'vendorName' => $order->vendor?->business_name ?? 'Unknown Vendor',
                    'createdAt' => $order->created_at->toIso8601String(),
                    'itemCount' => $order->items->sum('quantity'),
                    'items' => $order->items->map(fn (OrderItem $item) => [
                        'id' => $item->id,
                        'dealId' => $item->deal_id,
                        'dealOfferTypeId' => $item->deal_offer_type_id,
                        'title' => $item->title,
                        'quantity' => $item->quantity,
                        'unitPrice' => (float) $item->unit_price,
                        'originalPrice' => (float) ($item->meta['original_price'] ?? $item->unit_price),
                        'lineTotal' => (float) $item->line_total,
                        'image' => $item->meta['deal_image'] ?? '',
                        'offerType' => $item->meta['offer_type'] ?? 'Offer',
                    ])->values()->all(),
                ];
            });

        return \Inertia\Inertia::render('dashboard/MyPurchases', [
            'purchases' => $orders,
        ]);
    }
}
