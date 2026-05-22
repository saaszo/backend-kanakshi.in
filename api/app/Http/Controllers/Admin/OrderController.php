<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTracking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('q'));
        $statusFilter = trim((string) $request->string('status'));
        $paymentFilter = trim((string) $request->string('payment_status'));

        $query = Order::query()->with(['items', 'user']);

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $term = '%' . $search . '%';
                $builder->where('order_number', 'like', $term)
                    ->orWhere('ship_name', 'like', $term)
                    ->orWhere('ship_email', 'like', $term)
                    ->orWhere('ship_phone', 'like', $term);
            });
        }

        if ($statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        if ($paymentFilter !== '') {
            $query->where('payment_status', $paymentFilter);
        }

        return view('admin.orders.index', [
            'orders' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => [
                'q' => $search,
                'status' => $statusFilter,
                'payment_status' => $paymentFilter,
            ],
            'stats' => [
                'total' => Order::query()->count(),
                'pending' => Order::query()->where('status', 'pending')->count(),
                'processing' => Order::query()->where('status', 'processing')->count(),
                'shipped' => Order::query()->where('status', 'shipped')->count(),
                'delivered' => Order::query()->where('status', 'delivered')->count(),
            ]
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['items.product', 'trackingUpdates' => function ($q): void {
            $q->orderBy('created_at', 'desc');
        }]);

        return view('admin.orders.show', [
            'order' => $order
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,confirmed,processing,shipped,delivered,cancelled,refunded'],
            'payment_status' => ['required', 'string', 'in:pending,paid,failed,refunded'],
        ]);

        $oldStatus = $order->status;
        $newStatus = $validated['status'];

        DB::transaction(function () use ($order, $validated, $oldStatus, $newStatus): void {
            $order->update([
                'status' => $newStatus,
                'payment_status' => $validated['payment_status']
            ]);

            // If the status has changed, write a default tracking milestone to inform the customer
            if ($oldStatus !== $newStatus) {
                $messages = [
                    'pending' => 'Order has been placed and is awaiting confirmation.',
                    'confirmed' => 'Order has been confirmed! We are packaging your items.',
                    'processing' => 'Your order is currently being processed and prepared for shipping.',
                    'shipped' => 'Your package has been dispatched from our fulfillment center.',
                    'delivered' => 'Your package has been delivered. Thank you for shopping with us!',
                    'cancelled' => 'Your order has been cancelled.',
                    'refunded' => 'A refund has been initiated for your order.',
                ];

                $title = 'Order ' . ucfirst($newStatus);
                if ($newStatus === 'processing') {
                    $title = 'Processing Order';
                } elseif ($newStatus === 'confirmed') {
                    $title = 'Order Confirmed';
                }

                OrderTracking::query()->create([
                    'order_id' => $order->id,
                    'status' => $title,
                    'location' => 'Fulfillment Center',
                    'message' => $messages[$newStatus] ?? 'Order status updated to ' . $newStatus,
                ]);
            }
        });

        return back()->with('status', 'Order and payment status updated successfully.');
    }

    public function updateTracking(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'tracking_number' => ['required', 'string', 'max:100'],
            'tracking_url' => ['nullable', 'url', 'max:500'],
            'courier_name' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($order, $validated): void {
            $courier = $validated['courier_name'] ?? 'Courier Partner';
            $trackingUrl = $validated['tracking_url'];

            // Automatically build standard URL if none provided and courier is Delhivery or BlueDart
            if (empty($trackingUrl)) {
                $cleanNo = trim($validated['tracking_number']);
                if (stripos($courier, 'delhivery') !== false) {
                    $trackingUrl = 'https://www.delhivery.com/track/package/' . $cleanNo;
                } elseif (stripos($courier, 'blue dart') !== false || stripos($courier, 'bluedart') !== false) {
                    $trackingUrl = 'https://www.bluedart.com/tracking';
                }
            }

            $order->update([
                'tracking_number' => $validated['tracking_number'],
                'tracking_url' => $trackingUrl,
                'status' => 'shipped' // Automatically transition status to shipped
            ]);

            OrderTracking::query()->create([
                'order_id' => $order->id,
                'status' => 'Shipped via ' . $courier,
                'location' => 'Dispatched Hub',
                'message' => 'Your order has been dispatched. Track your package using Tracking Number: ' . $validated['tracking_number'],
            ]);
        });

        return back()->with('status', 'Courier tracking information assigned and order marked as shipped.');
    }

    public function addTrackingLog(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:200'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        OrderTracking::query()->create([
            'order_id' => $order->id,
            'status' => $validated['status'],
            'location' => $validated['location'] ?: 'In Transit',
            'message' => $validated['message'],
        ]);

        return back()->with('status', 'Manual order tracking milestone added successfully.');
    }
}
