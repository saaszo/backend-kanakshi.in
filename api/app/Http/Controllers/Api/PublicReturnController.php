<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\OrderReturn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicReturnController
{
    /**
     * Look up order by Order Number and Email/Phone for return requests.
     */
    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string', 'max:60'],
            'identifier' => ['required', 'string', 'max:150'], // Email or 10-digit Phone
        ]);

        $orderNumber = trim($validated['order_number']);
        $identifier = trim($validated['identifier']);
        $cleanPhone = preg_replace('/\D+/', '', $identifier);

        $order = Order::query()
            ->with(['items', 'returns' => function ($q) {
                $q->orderByDesc('created_at');
            }, 'trackingUpdates' => function ($q) {
                $q->orderByDesc('created_at');
            }])
            ->where('order_number', $orderNumber)
            ->where(function ($q) use ($identifier, $cleanPhone): void {
                $q->where('ship_email', $identifier)
                    ->orWhere('ship_phone', 'like', '%' . $cleanPhone . '%')
                    ->orWhereHas('user', function ($uq) use ($identifier, $cleanPhone): void {
                        $uq->where('email', $identifier)
                            ->orWhere('phone', 'like', '%' . $cleanPhone . '%');
                    });
            })
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'No matching order found for this order number and email/phone combination.',
            ], 404);
        }

        $activeReturn = $order->returns->first();

        return response()->json([
            'success' => true,
            'message' => 'Order details retrieved successfully for return.',
            'data' => [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'created_at' => $order->created_at?->toIso8601String(),
                'subtotal' => (float) $order->subtotal,
                'total_amount' => (float) $order->total_amount,
                'ship_name' => $order->ship_name,
                'ship_email' => $order->ship_email,
                'ship_phone' => $order->ship_phone,
                'is_return_eligible' => in_array($order->status, ['shipped', 'delivered'], true),
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'name' => $item->name,
                    'quantity' => (int) $item->quantity,
                    'price' => (float) $item->price,
                    'image' => $item->image,
                    'size' => $item->size,
                    'color' => $item->color,
                    'sku' => $item->sku,
                ]),
                'existing_return' => $activeReturn ? [
                    'id' => $activeReturn->id,
                    'return_number' => $activeReturn->return_number,
                    'status' => $activeReturn->status,
                    'reason' => $activeReturn->reason,
                    'reason_detail' => $activeReturn->reason_detail,
                    'customer_notes' => $activeReturn->customer_notes,
                    'refund_mode' => $activeReturn->refund_mode ?: 'wallet',
                    'refund_processed_at' => $activeReturn->refund_processed_at?->toIso8601String(),
                    'pickup_courier_name' => $activeReturn->pickup_courier_name,
                    'pickup_tracking_number' => $activeReturn->pickup_tracking_number,
                    'pickup_tracking_url' => $activeReturn->pickup_tracking_url,
                    'pickup_scheduled_date' => optional($activeReturn->pickup_scheduled_date)->format('Y-m-d'),
                    'requested_items' => $activeReturn->requested_items,
                    'requested_amount' => (float) $activeReturn->requested_amount,
                    'approved_amount' => (float) ($activeReturn->approved_amount ?: $activeReturn->requested_amount),
                    'admin_notes' => $activeReturn->admin_notes,
                    'requested_at' => $activeReturn->requested_at?->toIso8601String() ?: $activeReturn->created_at?->toIso8601String(),
                    'resolved_at' => $activeReturn->resolved_at?->toIso8601String(),
                ] : null,
            ],
        ]);
    }

    /**
     * Submit a return request for the verified order.
     */
    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string', 'max:60'],
            'identifier' => ['required', 'string', 'max:150'],
            'reason' => ['required', 'string', 'max:150'],
            'reason_detail' => ['nullable', 'string', 'max:255'],
            'refund_mode' => ['nullable', 'string', 'in:wallet,original_payment'],
            'customer_notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.variant_id' => ['nullable', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'images' => ['nullable', 'array', 'max:4'],
            'images.*' => ['nullable', 'string', 'max:500'],
        ]);

        $orderNumber = trim($validated['order_number']);
        $identifier = trim($validated['identifier']);
        $cleanPhone = preg_replace('/\D+/', '', $identifier);

        $order = Order::query()
            ->with(['items', 'returns'])
            ->where('order_number', $orderNumber)
            ->where(function ($q) use ($identifier, $cleanPhone): void {
                $q->where('ship_email', $identifier)
                    ->orWhere('ship_phone', 'like', '%' . $cleanPhone . '%')
                    ->orWhereHas('user', function ($uq) use ($identifier, $cleanPhone): void {
                        $uq->where('email', $identifier)
                            ->orWhere('phone', 'like', '%' . $cleanPhone . '%');
                    });
            })
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found with provided credentials.',
            ], 404);
        }

        if (!in_array($order->status, ['shipped', 'delivered'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Return requests can only be initiated for orders that have been shipped or delivered.',
            ], 422);
        }

        $existingPendingReturn = $order->returns->firstWhere('status', 'requested');
        if ($existingPendingReturn) {
            return response()->json([
                'success' => false,
                'message' => 'A return request (' . $existingPendingReturn->return_number . ') is already in review for this order.',
            ], 422);
        }

        $normalizedItems = [];
        $requestedAmount = 0;

        foreach ($validated['items'] as $requestItem) {
            $orderItem = $order->items->first(function ($item) use ($requestItem) {
                return (int) $item->product_id === (int) $requestItem['product_id']
                    && (int) ($item->variant_id ?? 0) === (int) ($requestItem['variant_id'] ?? 0);
            });

            if (!$orderItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more items selected do not belong to this order.',
                ], 422);
            }

            if ((int) $requestItem['quantity'] > (int) $orderItem->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Return quantity cannot exceed delivered item quantity.',
                ], 422);
            }

            $normalizedItems[] = [
                'product_id' => $orderItem->product_id,
                'variant_id' => $orderItem->variant_id,
                'name' => $orderItem->name,
                'quantity' => (int) $requestItem['quantity'],
                'price' => (float) $orderItem->price,
                'image' => $orderItem->image,
                'sku' => $orderItem->sku,
            ];

            $requestedAmount += ((float) $orderItem->price * (int) $requestItem['quantity']);
        }

        $returnNumber = 'RET-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
        $refundMode = $validated['refund_mode'] ?? 'wallet';

        $returnRequest = OrderReturn::query()->create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'return_number' => $returnNumber,
            'status' => 'requested',
            'reason' => $validated['reason'],
            'reason_detail' => $validated['reason_detail'] ?? null,
            'refund_mode' => $refundMode,
            'customer_notes' => $validated['customer_notes'] ?? null,
            'requested_items' => $normalizedItems,
            'images' => $validated['images'] ?? [],
            'requested_amount' => $requestedAmount,
            'requested_at' => now(),
        ]);

        $order->trackingUpdates()->create([
            'status' => 'Return Requested',
            'location' => 'Online Return Desk',
            'message' => 'Customer initiated return #' . $returnRequest->return_number . ' for ₹' . number_format($requestedAmount, 2) . ' (Refund mode: ' . ($refundMode === 'wallet' ? 'Instant Wallet Cash' : 'Original Payment') . ').',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Return request submitted successfully. Our after-sales team will review and approve pickup within 24 hours.',
            'data' => [
                'return_number' => $returnRequest->return_number,
                'status' => $returnRequest->status,
                'refund_mode' => $returnRequest->refund_mode,
                'requested_amount' => (float) $returnRequest->requested_amount,
            ],
        ], 201);
    }
}
