<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderTrackingController
{
    /**
     * Track an order using Order Number and Shipping Email/Phone.
     */
    public function track(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'number' => ['required', 'string', 'max:50'],
            'contact' => ['required', 'string', 'max:150'],
        ]);

        $orderNumber = trim($validated['number']);
        $contact = trim($validated['contact']);
        $cleanContactPhone = preg_replace('/\D+/', '', $contact);

        // Look up order matching the number
        $order = Order::query()
            ->with(['items', 'trackingUpdates' => function ($q) {
                $q->orderBy('created_at', 'asc'); // Milestone chronological order
            }])
            ->where('order_number', $orderNumber)
            ->first();

        // Security check: Must match order ship_email or ship_phone
        $emailMatch = $order && strtolower((string) $order->ship_email) === strtolower($contact);
        $cleanOrderPhone = $order ? preg_replace('/\D+/', '', (string) $order->ship_phone) : '';
        $phoneMatch = $order && $cleanContactPhone !== '' && (
            str_contains($cleanOrderPhone, $cleanContactPhone) || str_contains($cleanContactPhone, $cleanOrderPhone)
        );

        if (!$order || (!$emailMatch && !$phoneMatch)) {
            return response()->json([
                'success' => false,
                'message' => 'No order found with the matching order number and email/phone number combination.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order tracking data retrieved successfully.',
            'data' => [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'ship_name' => $order->ship_name,
                'ship_city' => $order->ship_city,
                'ship_state' => $order->ship_state,
                'created_at' => $order->created_at->toIso8601String(),
                'courier_name' => $order->courier_name,
                'tracking_number' => $order->tracking_number,
                'tracking_url' => $order->tracking_url,
                'dispatched_at' => optional($order->dispatched_at)->toIso8601String(),
                'estimated_delivery_date' => optional($order->estimated_delivery_date)->format('Y-m-d'),
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'total_amount' => (float) $order->total_amount,
                'items' => $order->items->map(function ($item) {
                    return [
                        'name' => $item->name,
                        'price' => (float) $item->price,
                        'quantity' => (int) $item->quantity,
                        'image' => $item->image,
                        'size' => $item->size,
                        'color' => $item->color,
                        'variant_details' => $item->variant_details,
                    ];
                }),
                'tracking_milestones' => $order->trackingUpdates->map(function ($track) {
                    return [
                        'id' => $track->id,
                        'status' => $track->status, // Placed, Confirmed, Processing, Shipped, Delivered, Cancelled etc
                        'location' => $track->location,
                        'message' => $track->message,
                        'created_at' => $track->created_at->toIso8601String(),
                    ];
                }),
            ]
        ]);
    }
}
