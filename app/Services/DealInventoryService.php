<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\DealOfferType;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DealInventoryService
{
    public function syncForOrderStatusChange(Order $order, string $previousStatus, string $newStatus): void
    {
        $order->loadMissing('items');

        if ($previousStatus !== 'fulfilled' && $newStatus === 'fulfilled') {
            $this->decrementInventoryForFulfilledOrder($order);
            return;
        }

        if ($previousStatus === 'fulfilled' && in_array($newStatus, ['cancelled', 'refunded'], true)) {
            $this->incrementInventoryForCancelledFulfilledOrder($order);
            return;
        }
    }

    private function decrementInventoryForFulfilledOrder(Order $order): void
    {
        $dealQuantities = $order->items
            ->whereNotNull('deal_id')
            ->groupBy('deal_id')
            ->map(fn ($items) => (int) $items->sum('quantity'))
            ->all();

        if (empty($dealQuantities)) {
            return;
        }

        foreach ($dealQuantities as $dealId => $qtyToSubtract) {
            DB::table('deals')
                ->where('id', $dealId)
                ->lock(true);

            /** @var Deal|null $deal */
            $deal = Deal::query()->where('id', $dealId)->lockForUpdate()->first();
            if (! $deal) {
                continue;
            }

            // null => unlimited inventory
            if ($deal->total_inventory === null) {
                continue;
            }

            $deal->total_inventory = max(0, (int) $deal->total_inventory - $qtyToSubtract);
            $deal->save();

            if ((int) $deal->total_inventory <= 0) {
                // Hide all active offers when inventory is fully consumed.
                DealOfferType::query()
                    ->where('deal_id', $dealId)
                    ->where('status', 'active')
                    ->update([
                        'status' => 'completed',
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    private function incrementInventoryForCancelledFulfilledOrder(Order $order): void
    {
        $dealQuantities = $order->items
            ->whereNotNull('deal_id')
            ->groupBy('deal_id')
            ->map(fn ($items) => (int) $items->sum('quantity'))
            ->all();

        if (empty($dealQuantities)) {
            return;
        }

        foreach ($dealQuantities as $dealId => $qtyToAdd) {
            /** @var Deal|null $deal */
            $deal = Deal::query()->where('id', $dealId)->lockForUpdate()->first();
            if (! $deal) {
                continue;
            }

            // null => unlimited inventory
            if ($deal->total_inventory === null) {
                continue;
            }

            $deal->total_inventory = (int) $deal->total_inventory + $qtyToAdd;
            $deal->save();

            if ((int) $deal->total_inventory > 0) {
                // Re-enable offers that were marked completed due to inventory,
                // but only if their time validity is still in the future (or null).
                DealOfferType::query()
                    ->where('deal_id', $dealId)
                    ->where('status', 'completed')
                    ->update([
                        'status' => DB::raw("CASE
                            WHEN ends_at IS NULL THEN 'active'
                            WHEN ends_at > NOW() THEN 'active'
                            ELSE 'expired'
                        END"),
                        'updated_at' => now(),
                    ]);
            }
        }
    }
}

