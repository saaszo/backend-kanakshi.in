<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CustomerEmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    public function invoice(Order $order): View
    {
        $order->load(['items.product']);

        return view('admin.orders.invoice', [
            'order' => $order,
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
            if (
                !in_array($oldStatus, ['cancelled', 'refunded'], true)
                && in_array($newStatus, ['cancelled', 'refunded'], true)
            ) {
                $this->restoreInventoryForOrder($order);
                $this->restoreCouponUsageForOrder($order);
            }

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

        if ($oldStatus !== $newStatus) {
            $freshOrder = $order->fresh('items');
            $this->sendOrderMailSafely(
                $freshOrder,
                'Your Little Divinity order status was updated',
                $this->buildOrderMailBody(
                    $freshOrder,
                    ($freshOrder ? ucfirst($freshOrder->status) : ucfirst($newStatus)) . ' update: '
                    . ($freshOrder?->trackingUpdates()->latest()->value('message') ?: 'Your order status has changed.')
                )
            );
        }

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

        $freshOrder = $order->fresh('items');
        $trackingUrl = $freshOrder?->tracking_url ? "\nTracking URL: {$freshOrder->tracking_url}" : '';
        $this->sendOrderMailSafely(
            $freshOrder,
            'Your Little Divinity order has been shipped',
            $this->buildOrderMailBody(
                $freshOrder,
                "Your order has been shipped.\nTracking Number: {$freshOrder?->tracking_number}{$trackingUrl}"
            )
        );

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

        $freshOrder = $order->fresh('items');
        $this->sendOrderMailSafely(
            $freshOrder,
            'A new Little Divinity tracking update is available',
            $this->buildOrderMailBody(
                $freshOrder,
                ($validated['message'] ?: 'A new manual tracking milestone was added to your order.')
                . "\nStatus: {$validated['status']}"
                . ($validated['location'] ? "\nLocation: {$validated['location']}" : '')
            )
        );

        return back()->with('status', 'Manual order tracking milestone added successfully.');
    }

    private function buildOrderMailBody(?Order $order, string $headline): string
    {
        if (! $order) {
            return $headline . "\n\nTeam Little Divinity";
        }

        $itemsSummary = $order->relationLoaded('items')
            ? $order->items->map(fn ($item) => "{$item->name} x {$item->quantity}")->implode("\n")
            : '';

        $lines = [
            "Hello {$order->ship_name},",
            '',
            $headline,
            '',
            "Order Number: {$order->order_number}",
            "Order Status: " . ucfirst((string) $order->status),
            "Payment Status: " . ucfirst((string) $order->payment_status),
        ];

        if ($itemsSummary !== '') {
            $lines[] = '';
            $lines[] = 'Items:';
            $lines[] = $itemsSummary;
        }

        $lines[] = '';
        $lines[] = 'Team Little Divinity';

        return implode("\n", $lines);
    }

    private function sendOrderMailSafely(?Order $order, string $subject, string $body): void
    {
        if (! $order) {
            return;
        }

        try {
            $service = app(CustomerEmailService::class);
            if (! $service->canSendOrderEmails()) {
                return;
            }

            $service->sendOrderMail($order->ship_email, $subject, $body);
        } catch (\Throwable $throwable) {
            Log::warning('Order stage email delivery failed.', [
                'order_number' => $order->order_number,
                'subject' => $subject,
                'error' => $throwable->getMessage(),
            ]);
        }
    }

    private function restoreInventoryForOrder(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
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
    }

    private function restoreCouponUsageForOrder(Order $order): void
    {
        if (!$order->coupon_id) {
            return;
        }

        $coupon = Coupon::query()->lockForUpdate()->find($order->coupon_id);
        if ($coupon && $coupon->used_count > 0) {
            $coupon->decrement('used_count');
        }
    }
}
