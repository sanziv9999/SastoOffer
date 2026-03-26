<?php

namespace App\Services;

use App\Models\DealOfferType;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Throwable;

class FirstXCustomerOfferService
{
    /**
     * Apply/expire "first X customers" offers for the given order.
     */
    public function handleFulfilledOrder(Order $order): void
    {
        $order->loadMissing('items');

        // We may need to recalc order totals only if we adjust item pricing.
        $didAdjustAnyItem = false;

        // Only expire pivots that match this rule and appear in this order.
        $pivotsToCheck = [];

        foreach ($order->items as $item) {
            $pivotId = (int) ($item->deal_offer_type_id ?? 0);
            if ($pivotId <= 0) {
                continue;
            }

            $dealOfferType = DealOfferType::with('offerType')->find($pivotId);
            if (! $dealOfferType || ! $dealOfferType->offerType) {
                continue;
            }

            $availability = $this->getAvailabilityConfig($dealOfferType->offerType->calculation_rule);
            if (! $availability || ($availability['mode'] ?? null) !== 'first_x_customers') {
                continue;
            }

            $limit = $this->getFirstXLimit($dealOfferType, $availability);
            if ($limit <= 0) {
                continue;
            }

            $pivotsToCheck[$pivotId] = $limit;

            // Count customers who fulfilled *before* this one (exclude current user).
            $fulfilledBeforeDistinctCustomers = $this->countFulfilledDistinctCustomersForPivot(
                $pivotId,
                excludeUserId: (int) $order->user_id
            );

            // If current customer is beyond the allowed limit, remove the discount
            // for this particular order item.
            $userAlreadyFulfilledForThisOffer = DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('order_items.deal_offer_type_id', $pivotId)
                ->where('orders.status', 'fulfilled')
                ->where('orders.user_id', (int) $order->user_id)
                ->where('order_items.order_id', '!=', (int) $order->id)
                ->exists();

            if ($userAlreadyFulfilledForThisOffer || $fulfilledBeforeDistinctCustomers >= $limit) {
                $original = (float) ($item->meta['original_price'] ?? $item->unit_price ?? 0);
                $qty = (int) $item->quantity;

                $item->unit_price = $original;
                $item->line_total = round($original * $qty, 2);

                $item->save();
                $didAdjustAnyItem = true;
            }
        }

        if ($didAdjustAnyItem) {
            $this->recalculateOrderTotals($order);
        }

        // Expire pivots if the distinct fulfilled customer count reached the limit.
        foreach (array_keys($pivotsToCheck) as $pivotId) {
            try {
                $dealOfferType = DealOfferType::with('offerType')->find($pivotId);
                if (! $dealOfferType || ! $dealOfferType->offerType) {
                    continue;
                }

                $availability = $this->getAvailabilityConfig($dealOfferType->offerType->calculation_rule);
                if (! $availability || ($availability['mode'] ?? null) !== 'first_x_customers') {
                    continue;
                }

                $limit = $this->getFirstXLimit($dealOfferType, $availability);
                if ($limit <= 0) {
                    continue;
                }

                $fulfilledAfterDistinctCustomers = $this->countFulfilledDistinctCustomersForPivot($pivotId);
                if ($fulfilledAfterDistinctCustomers >= $limit) {
                    $dealOfferType->status = 'expired';
                    $dealOfferType->ends_at = now();
                    $dealOfferType->save();
                }
            } catch (Throwable) {
                // Keep fulfillment resilient; a failure here won't break order fulfillment.
                continue;
            }
        }
    }

    /**
     * @param array|string|null $calculationRule
     * @return array<string, mixed>|null
     */
    private function getAvailabilityConfig($calculationRule): ?array
    {
        if (is_string($calculationRule)) {
            $calculationRule = json_decode($calculationRule, true) ?: [];
        }

        if (! is_array($calculationRule)) {
            return null;
        }

        $availability = $calculationRule['availability'] ?? null;
        return is_array($availability) ? $availability : null;
    }

    /**
     * @param array<string, mixed> $availability
     */
    private function getFirstXLimit(DealOfferType $dealOfferType, array $availability): int
    {
        $limitParam = (string) ($availability['first_x_param'] ?? 'first_x_customers');

        // Merge default_values + pivot params so either can be used.
        $params = array_merge(
            (array) ($dealOfferType->offerType->default_values ?? []),
            (array) ($dealOfferType->params ?? [])
        );

        return (int) ($params[$limitParam] ?? 0);
    }

    private function countFulfilledDistinctCustomersForPivot(int $pivotId, ?int $excludeUserId = null): int
    {
        $query = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.deal_offer_type_id', $pivotId)
            ->where('orders.status', 'fulfilled')
            ->selectRaw('COUNT(DISTINCT orders.user_id) AS cnt');

        if ($excludeUserId !== null) {
            $query->where('orders.user_id', '!=', $excludeUserId);
        }

        return (int) ($query->value('cnt') ?? 0);
    }

    private function recalculateOrderTotals(Order $order): void
    {
        $order->loadMissing('items');

        $subtotal = 0.0;
        $discountTotal = 0.0;

        foreach ($order->items as $item) {
            $qty = (int) $item->quantity;
            $unit = (float) ($item->unit_price ?? 0);
            $subtotal += $unit * $qty;

            $original = (float) ($item->meta['original_price'] ?? $unit);
            $discountTotal += max(0, ($original - $unit) * $qty);
        }

        $order->subtotal = round($subtotal, 2);
        $order->grand_total = round($subtotal, 2);
        $order->discount_total = round($discountTotal, 2);
        $order->tax_total = 0;
        $order->save();
    }
}

