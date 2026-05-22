<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\CustomerAccessToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomerOrderController
{
    /**
     * Display a listing of the customer's orders.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveCustomerFromRequest($request);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized customer session.',
            ], 401);
        }

        $orders = Order::query()
            ->with(['items'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Customer orders retrieved successfully.',
            'data' => $orders->map(function (Order $order) {
                return $this->formatOrder($order);
            }),
        ]);
    }

    /**
     * Display the specified customer order.
     */
    public function show(Request $request, string $order_number): JsonResponse
    {
        $user = $this->resolveCustomerFromRequest($request);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized customer session.',
            ], 401);
        }

        $order = Order::query()
            ->with(['items', 'trackingUpdates' => function ($q) {
                $q->orderByDesc('created_at');
            }])
            ->where('user_id', $user->id)
            ->where('order_number', $order_number)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order details retrieved successfully.',
            'data' => $this->formatOrderDetails($order),
        ]);
    }

    /**
     * Format general order response list.
     */
    private function formatOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'subtotal' => (float) $order->subtotal,
            'discount' => (float) $order->discount,
            'tax' => (float) $order->tax,
            'shipping_cost' => (float) $order->shipping_cost,
            'total_amount' => (float) $order->total_amount,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'ship_name' => $order->ship_name,
            'created_at' => $order->created_at->toIso8601String(),
            'items_count' => $order->items->sum('quantity'),
            'first_item_image' => $order->items->first()?->image,
            'first_item_name' => $order->items->first()?->name,
        ];
    }

    /**
     * Format full details of the order.
     */
    private function formatOrderDetails(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'subtotal' => (float) $order->subtotal,
            'discount' => (float) $order->discount,
            'tax' => (float) $order->tax,
            'shipping_cost' => (float) $order->shipping_cost,
            'total_amount' => (float) $order->total_amount,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'payment_id' => $order->payment_id,
            'ship_name' => $order->ship_name,
            'ship_email' => $order->ship_email,
            'ship_phone' => $order->ship_phone,
            'ship_address' => $order->ship_address,
            'ship_city' => $order->ship_city,
            'ship_state' => $order->ship_state,
            'ship_pincode' => $order->ship_pincode,
            'notes' => $order->notes,
            'tracking_number' => $order->tracking_number,
            'tracking_url' => $order->tracking_url,
            'created_at' => $order->created_at->toIso8601String(),
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'name' => $item->name,
                    'price' => (float) $item->price,
                    'quantity' => (int) $item->quantity,
                    'image' => $item->image,
                    'size' => $item->size,
                    'color' => $item->color,
                    'variant_details' => $item->variant_details,
                    'line_total' => (float) $item->line_total,
                    'sku' => $item->sku,
                ];
            }),
            'tracking' => $order->trackingUpdates->map(function ($track) {
                return [
                    'id' => $track->id,
                    'status' => $track->status,
                    'location' => $track->location,
                    'message' => $track->message,
                    'created_at' => $track->created_at->toIso8601String(),
                ];
            }),
        ];
    }

    /**
     * Resolve the customer from request headers.
     */
    private function resolveCustomerFromRequest(Request $request): ?User
    {
        $bearer = $request->bearerToken();

        if (!$bearer) {
            return null;
        }

        $token = CustomerAccessToken::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $bearer))
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$token) {
            return null;
        }

        $token->forceFill([
            'last_used_at' => now(),
        ])->save();

        return $token->user;
    }
}
