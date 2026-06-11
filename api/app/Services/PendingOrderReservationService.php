<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PendingOrderReservationService
{
    public function releaseExpired(int $minutes = 15): int
    {
        $released = 0;

        $expiredOrderIds = Order::query()
            ->where('status', 'payment_pending')
            ->where('created_at', '<', now()->subMinutes($minutes))
            ->pluck('id');

        foreach ($expiredOrderIds as $orderId) {
            $order = Order::query()->find($orderId);

            if (! $order) {
                continue;
            }

            if ($this->release(
                $order,
                "Pending payment session timed out ({$minutes}-min limit). Reserved stock and coupon usage released.",
                'failed',
                $minutes
            )) {
                $released++;
            }
        }

        return $released;
    }

    public function release(
        Order $order,
        string $trackingMessage,
        string $paymentStatus,
        ?int $minimumAgeMinutes = null
    ): bool {
        $released = false;

        DB::transaction(function () use ($order, $trackingMessage, $paymentStatus, $minimumAgeMinutes, &$released): void {
            $lockedOrder = Order::query()
                ->with('items')
                ->lockForUpdate()
                ->find($order->id);

            if (! $lockedOrder || $lockedOrder->status !== 'payment_pending') {
                return;
            }

            if ($minimumAgeMinutes !== null && $lockedOrder->created_at->gte(now()->subMinutes($minimumAgeMinutes))) {
                return;
            }

            foreach ($lockedOrder->items as $item) {
                if ($item->variant_id) {
                    $variant = ProductVariant::query()->lockForUpdate()->find($item->variant_id);
                    if ($variant) {
                        $variant->increment('stock', $item->quantity);
                    }
                } else {
                    $product = Product::query()->lockForUpdate()->find($item->product_id);
                    if ($product) {
                        $product->increment('stock', $item->quantity);
                    }
                }

                $product = Product::query()->lockForUpdate()->find($item->product_id);
                if ($product && $product->total_sold >= $item->quantity) {
                    $product->decrement('total_sold', $item->quantity);
                }
            }

            if ($lockedOrder->coupon_id) {
                $coupon = Coupon::query()->lockForUpdate()->find($lockedOrder->coupon_id);
                if ($coupon && $coupon->used_count > 0) {
                    $coupon->decrement('used_count');
                }
            }

            $lockedOrder->update([
                'status' => 'cancelled',
                'payment_status' => $paymentStatus,
                'pending_access_token_hash' => null,
                'pending_access_expires_at' => null,
            ]);

            OrderTracking::query()->create([
                'order_id' => $lockedOrder->id,
                'status' => 'Cancelled',
                'location' => 'Mumbai Warehouse',
                'message' => $trackingMessage,
            ]);

            $released = true;
        });

        return $released;
    }

    public function releaseExpiredSafely(int $minutes = 15): int
    {
        try {
            return $this->releaseExpired($minutes);
        } catch (\Throwable $e) {
            Log::error('Auto cleanup of expired orders failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return 0;
        }
    }
}
