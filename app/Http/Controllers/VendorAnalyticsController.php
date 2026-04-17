<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\ActivityMailer;
use App\Services\FirstXCustomerOfferService;
use App\Services\DealInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VendorAnalyticsController extends Controller
{
    /**
     * @return array<string, mixed>
     */
    protected function mapOrderForVendor(Order $order): array
    {
        return [
            'id' => $order->order_number,
            'orderId' => $order->id,
            'customer' => $order->user?->name ?? 'Customer',
            'customerEmail' => $order->user?->email ?? '',
            'cancellationReason' => $order->metadata['cancel_reason'] ?? null,
            'subtotal' => (float) $order->subtotal,
            'discountTotal' => (float) $order->discount_total,
            'taxTotal' => (float) $order->tax_total,
            'total' => (float) $order->grand_total,
            'currencyCode' => $order->currency_code,
            'paymentMethod' => $order->payment_method,
            'paymentReference' => $order->payment_reference,
            'paidAt' => $order->paid_at?->toIso8601String(),
            'quantity' => $order->items->sum('quantity'),
            'status' => $order->status,
            'date' => $order->created_at?->toIso8601String(),
            'items' => $order->items->map(fn (OrderItem $item) => [
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
    }

    protected function getVendorDeals()
    {
        $user = auth()->user();
        $vendor = $user->vendorProfile;

        if (! $vendor) {
            return collect();
        }

        $dealSales = OrderItem::query()
            ->selectRaw('deal_id, SUM(quantity) as quantity_sold, SUM(line_total) as total_revenue, COUNT(DISTINCT order_id) as orders_count')
            ->whereHas('order', fn ($q) => $q
                ->where('vendor_id', $vendor->id)
                ->whereNotIn('status', ['cancelled', 'refunded']))
            ->groupBy('deal_id')
            ->get()
            ->keyBy('deal_id');

        return Deal::where('vendor_id', $vendor->id)
            ->with(['offerTypes', 'images'])
            ->latest()
            ->get()
            ->map(function ($deal) use ($dealSales) {
                $offer = $deal->offerTypes->first()?->pivot;
                $sale = $dealSales->get($deal->id);
                $quantitySold = (int) ($sale->quantity_sold ?? 0);

                return [
                    'id' => $deal->id,
                    'title' => $deal->title,
                    'status' => $deal->status,
                    'discountedPrice' => $offer ? (float) $offer->final_price : 0,
                    'originalPrice' => $offer ? (float) $offer->original_price : 0,
                    'quantitySold' => $quantitySold,
                    'revenue' => round((float) ($sale->total_revenue ?? 0), 2),
                    'ordersCount' => (int) ($sale->orders_count ?? 0),
                    'endDate' => $offer?->ends_at?->toIso8601String(),
                    'image' => $deal->featuredImageUrl(),
                ];
            });
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $vendor = $user->vendorProfile;
        if (! $vendor) {
            return \Inertia\Inertia::render('vendor/Analytics', [
                'stats' => [
                    'totalRevenue' => 0,
                    'totalSales' => 0,
                    'totalOrders' => 0,
                    'avgOrderValue' => 0,
                    'pageViews' => 0,
                    'conversionRate' => 0,
                    'activeDealsCount' => 0,
                ],
                'topDeals' => [],
                'monthlySales' => [],
            ]);
        }

        $deals = $this->getVendorDeals();
        $salesOrders = Order::where('vendor_id', $vendor->id)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->get();
        $totalOrders = $salesOrders->count();

        $totalSales = (int) $deals->sum('quantitySold');
        $totalRevenue = round((float) $salesOrders->sum('grand_total'), 2);
        $monthlySales = $salesOrders
            ->groupBy(fn ($o) => $o->created_at->format('Y-m'))
            ->sortKeys()
            ->map(fn ($group, $key) => [
                'month' => \Carbon\Carbon::parse($key.'-01')->format('M'),
                'amount' => round((float) $group->sum('grand_total'), 2),
                'orders' => $group->count(),
            ])
            ->values()
            ->slice(-6)
            ->values();

        $stats = [
            'totalRevenue' => $totalRevenue,
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders,
            'avgOrderValue' => $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0,
            'pageViews' => 0,
            'conversionRate' => 0,
            'activeDealsCount' => $deals->where('status', 'active')->count(),
        ];

        $topDeals = $deals
            ->sortByDesc('quantitySold')
            ->values();

        return \Inertia\Inertia::render('vendor/Analytics', [
            'stats' => $stats,
            'topDeals' => $topDeals,
            'monthlySales' => $monthlySales,
        ]);
    }

    public function orders(Request $request)
    {
        $user = auth()->user();
        $vendor = $user->vendorProfile;

        if (! $vendor) {
            return \Inertia\Inertia::render('vendor/Orders', ['orders' => []]);
        }

        $orders = Order::where('vendor_id', $vendor->id)
            ->with(['user', 'items.deal'])
            ->latest()
            ->get()
            ->map(fn (Order $order) => $this->mapOrderForVendor($order))
            ->values();

        return \Inertia\Inertia::render('vendor/Orders', [
            'orders' => $orders,
        ]);
    }

    public function showOrder(Order $order)
    {
        $user = auth()->user();
        $vendor = $user->vendorProfile;

        if (! $vendor || (int) $order->vendor_id !== (int) $vendor->id) {
            abort(403);
        }

        $order->load(['user', 'items.deal']);

        return \Inertia\Inertia::render('vendor/OrderShow', [
            'order' => $this->mapOrderForVendor($order),
        ]);
    }

    public function updateOrderStatus(Request $request, Order $order, ActivityMailer $activityMailer)
    {
        $user = auth()->user();
        $vendor = $user->vendorProfile;

        if (! $vendor || (int) $order->vendor_id !== (int) $vendor->id) {
            abort(403);
        }

        $data = $request->validate([
            'status' => ['required', 'in:pending,paid,fulfilled,cancelled,refunded'],
        ]);

        $previousStatus = $order->status;
        $newStatus = $data['status'];

        DB::transaction(function () use ($order, $newStatus, $previousStatus) {
            $order->status = $newStatus;
            if ($newStatus === 'paid' && ! $order->paid_at) {
                $order->paid_at = now();
            }
            $order->save();

            if ($newStatus === 'fulfilled') {
                app(FirstXCustomerOfferService::class)->handleFulfilledOrder($order);
            }

            // Inventory lifecycle for the underlying deal.
            app(DealInventoryService::class)->syncForOrderStatusChange($order, $previousStatus, $newStatus);
        });

        if ($previousStatus !== $newStatus) {
            try {
                $activityMailer->sendOrderStatusChangedCustomer($order, $newStatus);
                $activityMailer->sendOrderStatusChangedVendor($order, $newStatus);
            } catch (\Throwable $e) {
                Log::warning('Order status change mail failed', [
                    'order_id' => $order->id,
                    'status' => $newStatus,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return back()->with('success', 'Order status updated successfully.');
    }

    public function claimOrderByCode(Request $request, ActivityMailer $activityMailer)
    {
        $user = auth()->user();
        $vendor = $user->vendorProfile;

        if (! $vendor) {
            abort(403);
        }

        $data = $request->validate([
            'claim_code' => ['required', 'string', 'max:100'],
        ]);

        $claimCode = trim((string) $data['claim_code']);

        $orderItem = OrderItem::query()
            ->where('meta->claim_token', $claimCode)
            ->whereHas('order', fn ($q) => $q->where('vendor_id', $vendor->id))
            ->with('order.items')
            ->latest('id')
            ->first();

        // Backward compatible path: allow vendors to redeem by order number as well.
        if (! $orderItem) {
            $orderByNumber = Order::query()
                ->where('vendor_id', $vendor->id)
                ->where('order_number', $claimCode)
                ->with('items')
                ->latest('id')
                ->first();

            if (! $orderByNumber) {
                throw ValidationException::withMessages([
                    'claim_code' => 'Claim token or order ID not found for your offers.',
                ]);
            }

            if (in_array($orderByNumber->status, ['cancelled', 'refunded'], true)) {
                throw ValidationException::withMessages([
                    'claim_code' => 'This order is cancelled/refunded and cannot be redeemed.',
                ]);
            }

            if ($orderByNumber->status === 'fulfilled') {
                return back()->with('success', 'This order is already redeemed.');
            }

            $previousStatus = $orderByNumber->status;
            $newStatus = 'fulfilled';

            DB::transaction(function () use ($orderByNumber, $user, $claimCode) {
                foreach ($orderByNumber->items as $item) {
                    $meta = is_array($item->meta) ? $item->meta : [];
                    if (empty($meta['claim_token'])) {
                        $meta['claim_token'] = $claimCode;
                    }
                    if (empty($meta['claimed_at'])) {
                        $meta['claimed_at'] = now()->toIso8601String();
                        $meta['claimed_by_user_id'] = $user?->id;
                    }
                    $item->meta = $meta;
                    $item->save();
                }

                $orderByNumber->status = 'fulfilled';
                if (! $orderByNumber->paid_at) {
                    $orderByNumber->paid_at = now();
                }
                $orderByNumber->save();
            });

            DB::transaction(function () use ($orderByNumber, $previousStatus, $newStatus) {
                app(FirstXCustomerOfferService::class)->handleFulfilledOrder($orderByNumber);
                app(DealInventoryService::class)->syncForOrderStatusChange($orderByNumber, $previousStatus, $newStatus);
            });

            try {
                $activityMailer->sendOrderStatusChangedCustomer($orderByNumber, 'fulfilled');
                $activityMailer->sendOrderStatusChangedVendor($orderByNumber, 'fulfilled');
            } catch (\Throwable $e) {
                Log::warning('Order claim status mail failed', [
                    'order_id' => $orderByNumber->id,
                    'status' => 'fulfilled',
                    'error' => $e->getMessage(),
                ]);
            }

            return back()->with('success', 'Order verified and redeemed successfully.');
        }

        $order = $orderItem->order;
        if (! $order) {
            throw ValidationException::withMessages([
                'claim_code' => 'Claim token is invalid.',
            ]);
        }

        if (in_array($order->status, ['cancelled', 'refunded'], true)) {
            throw ValidationException::withMessages([
                'claim_code' => 'This claim token belongs to a cancelled/refunded order.',
            ]);
        }

        $itemMeta = is_array($orderItem->meta) ? $orderItem->meta : [];
        if (! empty($itemMeta['claimed_at'])) {
            return back()->with('success', 'This claimed offer is already redeemed.');
        }

        $previousStatus = $order->status;
        $newStatus = $previousStatus;
        $isOrderNowFullyClaimed = false;

        DB::transaction(function () use ($order, $orderItem, $user, $claimCode, &$newStatus, &$isOrderNowFullyClaimed) {
            $itemMeta = is_array($orderItem->meta) ? $orderItem->meta : [];
            $itemMeta['claimed_at'] = now()->toIso8601String();
            $itemMeta['claimed_by_user_id'] = $user?->id;
            $itemMeta['claim_token'] = $claimCode;
            $orderItem->meta = $itemMeta;
            $orderItem->save();

            $order->refresh()->load('items');

            $isOrderNowFullyClaimed = $order->items->every(function (OrderItem $item) {
                $meta = is_array($item->meta) ? $item->meta : [];
                return ! empty($meta['claimed_at']);
            });

            if ($isOrderNowFullyClaimed) {
                $newStatus = 'fulfilled';
            } elseif ($order->status === 'pending') {
                $newStatus = 'paid';
            }

            if ($order->status !== $newStatus) {
                $order->status = $newStatus;
                if (in_array($newStatus, ['paid', 'fulfilled'], true) && ! $order->paid_at) {
                    $order->paid_at = now();
                }
                $order->save();
            }
        });

        if ($previousStatus !== $newStatus && $newStatus === 'fulfilled') {
            DB::transaction(function () use ($order, $previousStatus, $newStatus) {
                app(FirstXCustomerOfferService::class)->handleFulfilledOrder($order);
                app(DealInventoryService::class)->syncForOrderStatusChange($order, $previousStatus, $newStatus);
            });
        }

        if ($previousStatus !== $newStatus) {
            try {
                $activityMailer->sendOrderStatusChangedCustomer($order, $newStatus);
                $activityMailer->sendOrderStatusChangedVendor($order, $newStatus);
            } catch (\Throwable $e) {
                Log::warning('Order claim status mail failed', [
                    'order_id' => $order->id,
                    'status' => $newStatus,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($isOrderNowFullyClaimed) {
            return back()->with('success', 'Claim token verified. All offers redeemed and order marked fulfilled.');
        }

        return back()->with('success', 'Claim token verified. Offer line marked as claimed.');
    }

    public function customers(Request $request)
    {
        $user = auth()->user();
        $vendor = $user->vendorProfile;

        if (! $vendor) {
            return \Inertia\Inertia::render('vendor/Customers', [
                'customers' => [],
            ]);
        }

        $orders = Order::where('vendor_id', $vendor->id)
            ->with(['user.defaultAddress', 'items'])
            ->latest()
            ->get();

        $customers = $orders
            ->filter(fn (Order $order) => $order->user !== null)
            ->groupBy('user_id')
            ->map(function ($customerOrders) {
                /** @var \Illuminate\Support\Collection<int, \App\Models\Order> $customerOrders */
                $firstOrder = $customerOrders->first();
                $customer = $firstOrder?->user;

                if (! $customer) {
                    return null;
                }

                $totalSpent = (float) $customerOrders->sum(fn (Order $order) => (float) $order->grand_total);
                $totalOrders = $customerOrders->count();
                $lastOrderAt = $customerOrders->max('created_at');

                $distinctDeals = $customerOrders
                    ->flatMap(fn (Order $order) => $order->items->pluck('deal_id'))
                    ->filter()
                    ->unique()
                    ->count();

                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'city' => $customer->defaultAddress?->district ?: 'N/A',
                    'totalOrders' => $totalOrders,
                    'totalSpent' => $totalSpent,
                    'dealsPurchased' => $distinctDeals,
                    // "Active" = purchased within the last 90 days.
                    'status' => $lastOrderAt && $lastOrderAt->gte(now()->subDays(90)) ? 'active' : 'inactive',
                    'rating' => null,
                    'lastOrderAt' => $lastOrderAt?->toIso8601String(),
                ];
            })
            ->filter()
            ->sortByDesc('lastOrderAt')
            ->values();

        return \Inertia\Inertia::render('vendor/Customers', [
            'customers' => $customers,
        ]);
    }

    public function customerHistory(Request $request)
    {
        // Placeholder history based on derived orders
        $deals = $this->getVendorDeals();

        $history = $deals->flatMap(function ($deal) {
            if (($deal['quantitySold'] ?? 0) <= 0) {
                return [];
            }

            return [[
                'id' => 'HIST-'.$deal['id'],
                'customer' => 'Customer',
                'deal' => $deal['title'],
                'quantity' => $deal['quantitySold'],
                'total' => ($deal['quantitySold'] ?? 0) * ($deal['discountedPrice'] ?? 0),
                'status' => 'completed',
                'date' => $deal['endDate'] ?? now()->toIso8601String(),
            ]];
        })->values();

        return \Inertia\Inertia::render('vendor/CustomerHistory', [
            'history' => $history,
        ]);
    }

    public function salesHistory(Request $request)
    {
        $user = auth()->user();
        $vendor = $user->vendorProfile;

        if (! $vendor) {
            return \Inertia\Inertia::render('vendor/SalesHistory', [
                'sales' => [],
            ]);
        }

        $sales = OrderItem::query()
            ->whereHas('order', fn ($q) => $q->where('vendor_id', $vendor->id))
            ->with(['order.user'])
            ->latest()
            ->get()
            ->map(function (OrderItem $item) {
                $order = $item->order;

                return [
                    'id' => $order?->order_number.'-I'.$item->id,
                    'deal' => $item->title,
                    'customer' => $order?->user?->name ?? 'Customer',
                    'quantity' => (int) $item->quantity,
                    'unitPrice' => (float) $item->unit_price,
                    'total' => (float) $item->line_total,
                    'status' => $order?->status ?? 'pending',
                    'date' => $order?->created_at?->toDateString(),
                ];
            })
            ->values();

        return \Inertia\Inertia::render('vendor/SalesHistory', [
            'sales' => $sales,
        ]);
    }

    public function reviews(Request $request)
    {
        $user = auth()->user();
        $vendor = $user->vendorProfile;

        if (! $vendor) {
            return \Inertia\Inertia::render('vendor/Reviews', ['reviews' => [], 'deals' => []]);
        }

        $vendorReviews = \App\Models\Review::where('reviewable_type', \App\Models\VendorProfile::class)
            ->where('reviewable_id', $vendor->id)
            ->with('user')
            ->latest()
            ->get();

        $dealOfferIds = \App\Models\DealOfferType::whereHas('deal', fn ($q) => $q->where('vendor_id', $vendor->id))
            ->pluck('id');

        $dealReviews = \App\Models\Review::where('reviewable_type', \App\Models\DealOfferType::class)
            ->whereIn('reviewable_id', $dealOfferIds)
            ->with(['user', 'reviewable.deal'])
            ->latest()
            ->get();

        $allReviews = $vendorReviews->concat($dealReviews)
            ->sortByDesc('created_at')
            ->values()
            ->map(function (\App\Models\Review $r) {
                $dealTitle = null;
                $dealId = null;

                if ($r->reviewable_type === \App\Models\DealOfferType::class) {
                    $dealTitle = $r->reviewable?->deal?->title ?? 'Unknown Deal';
                    $dealId = $r->reviewable?->deal_id;
                } else {
                    $dealTitle = 'Vendor Profile';
                }

                return [
                    'id' => (string) $r->id,
                    'customerName' => $r->user?->name ?? 'Anonymous',
                    'rating' => $r->rating,
                    'comment' => $r->comment,
                    'dealId' => $dealId ? (string) $dealId : null,
                    'dealTitle' => $dealTitle,
                    'type' => $r->reviewable_type === \App\Models\VendorProfile::class ? 'vendor' : 'deal',
                    'isHidden' => (bool) $r->is_hidden,
                    'createdAt' => $r->created_at->toIso8601String(),
                    'merchantReply' => $r->vendor_reply ? [
                        'comment' => $r->vendor_reply,
                        'createdAt' => $r->vendor_replied_at?->toIso8601String(),
                    ] : null,
                ];
            });

        $deals = \App\Models\Deal::where('vendor_id', $vendor->id)
            ->select('id', 'title')
            ->get()
            ->map(fn ($d) => ['id' => (string) $d->id, 'title' => $d->title]);

        return \Inertia\Inertia::render('vendor/Reviews', [
            'reviews' => $allReviews,
            'deals' => $deals,
        ]);
    }
}
