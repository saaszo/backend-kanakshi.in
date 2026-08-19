<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderReturnController extends Controller
{
    public function index(Request $request): View
    {
        $status = trim((string) $request->string('status'));
        $search = trim((string) $request->string('q'));

        $query = OrderReturn::query()->with(['order', 'user']);

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $term = '%' . $search . '%';
            $query->where(function ($builder) use ($term): void {
                $builder->where('return_number', 'like', $term)
                    ->orWhere('reason', 'like', $term)
                    ->orWhereHas('order', function ($orderQuery) use ($term): void {
                        $orderQuery->where('order_number', 'like', $term)
                            ->orWhere('ship_name', 'like', $term)
                            ->orWhere('ship_email', 'like', $term);
                    });
            });
        }

        $stats = [
            'total' => OrderReturn::query()->count(),
            'requested' => OrderReturn::query()->where('status', 'requested')->count(),
            'approved' => OrderReturn::query()->where('status', 'approved')->count(),
            'received' => OrderReturn::query()->where('status', 'received')->count(),
            'refunded' => OrderReturn::query()->where('status', 'refunded')->count(),
            'rejected' => OrderReturn::query()->where('status', 'rejected')->count(),
            'total_refunded_amount' => (float) OrderReturn::query()->where('status', 'refunded')->sum('approved_amount'),
        ];

        return view('admin.returns.index', [
            'returns' => $query->latest()->paginate(15)->withQueryString(),
            'stats' => $stats,
            'filters' => [
                'status' => $status,
                'q' => $search,
            ],
        ]);
    }

    public function show(OrderReturn $return): View
    {
        $return->load(['order.items', 'user']);

        return view('admin.returns.show', [
            'returnRequest' => $return,
        ]);
    }

    public function update(Request $request, OrderReturn $return): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:requested,approved,rejected,received,refunded'],
            'approved_amount' => ['nullable', 'numeric', 'min:0'],
            'refund_mode' => ['nullable', 'string', 'in:wallet,original_payment'],
            'admin_notes' => ['nullable', 'string'],
            'pickup_courier_name' => ['nullable', 'string', 'max:100'],
            'pickup_tracking_number' => ['nullable', 'string', 'max:100'],
            'pickup_tracking_url' => ['nullable', 'url', 'max:500'],
            'pickup_scheduled_date' => ['nullable', 'date'],
        ]);

        DB::transaction(function () use ($return, $validated): void {
            $previousStatus = $return->status;
            $pickupCourier = $validated['pickup_courier_name'] ?? $return->pickup_courier_name;
            $pickupTrackingNo = $validated['pickup_tracking_number'] ?? $return->pickup_tracking_number;
            $pickupTrackingUrl = $validated['pickup_tracking_url'] ?? $return->pickup_tracking_url;
            $refundMode = $validated['refund_mode'] ?? $return->refund_mode ?? 'wallet';

            if ($pickupCourier && $pickupTrackingNo && empty($pickupTrackingUrl)) {
                $pickupTrackingUrl = $this->buildCourierTrackingUrl($pickupCourier, $pickupTrackingNo);
            }

            $approvedAmount = (float) ($validated['approved_amount'] ?? $return->approved_amount ?: $return->requested_amount);

            $return->update([
                'status' => $validated['status'],
                'approved_amount' => $approvedAmount,
                'refund_mode' => $refundMode,
                'admin_notes' => $validated['admin_notes'] ?? $return->admin_notes,
                'pickup_courier_name' => $pickupCourier,
                'pickup_tracking_number' => $pickupTrackingNo,
                'pickup_tracking_url' => $pickupTrackingUrl,
                'pickup_scheduled_date' => $validated['pickup_scheduled_date'] ?? $return->pickup_scheduled_date,
                'resolved_at' => in_array($validated['status'], ['rejected', 'refunded'], true) ? now() : $return->resolved_at,
            ]);

            if (
                in_array($validated['status'], ['received', 'refunded'], true)
                && !$return->stock_restored_at
            ) {
                $this->restoreReturnedStock($return);
            }

            if ($validated['status'] === 'refunded') {
                $return->order()->update([
                    'status' => $return->order->status === 'delivered' ? 'refunded' : $return->order->status,
                    'payment_status' => 'refunded',
                ]);

                // Cancel pending loyalty cashback for returned order
                \App\Models\CustomerWalletTransaction::query()
                    ->where('order_id', $return->order_id)
                    ->where('status', 'pending_clearance')
                    ->update(['status' => 'cancelled']);

                // Credit customer wallet if refund mode is wallet and not already credited
                if ($refundMode === 'wallet' && !$return->refund_processed_at) {
                    $user = $return->user ?? $return->order->user;
                    if ($user) {
                        $user->creditWallet(
                            $approvedAmount,
                            'order_refund',
                            $return->order_id,
                            "Refund for Return #{$return->return_number} (Credited to Kanakshi Wallet for shopping)"
                        );
                    }
                    $return->forceFill(['refund_processed_at' => now()])->save();
                }
            }

            if ($previousStatus !== $validated['status']) {
                $return->order->trackingUpdates()->create([
                    'status' => 'Return ' . ucfirst($validated['status']),
                    'location' => 'Returns Desk',
                    'message' => 'Return request ' . $return->return_number . ' updated to ' . $validated['status'] . ($validated['status'] === 'refunded' ? ' (Refund of ₹' . number_format($approvedAmount, 2) . ' processed via ' . ($refundMode === 'wallet' ? 'Wallet Credit' : 'Original Payment') . ').' : '.'),
                ]);
            }
        });

        return back()->with('status', 'Return request updated successfully.');
    }

    private function restoreReturnedStock(OrderReturn $return): void
    {
        $items = is_array($return->requested_items) ? $return->requested_items : [];

        foreach ($items as $item) {
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $variantId = isset($item['variant_id']) ? (int) $item['variant_id'] : null;
            $productId = isset($item['product_id']) ? (int) $item['product_id'] : null;

            if ($variantId) {
                $variant = ProductVariant::query()->lockForUpdate()->find($variantId);
                if ($variant) {
                    $variant->increment('stock', $quantity);
                }
            } elseif ($productId) {
                $product = Product::query()->lockForUpdate()->find($productId);
                if ($product) {
                    $product->increment('stock', $quantity);
                }
            }
        }

        $return->forceFill([
            'stock_restored_at' => now(),
        ])->save();
    }

    private function buildCourierTrackingUrl(string $courier, string $awb): ?string
    {
        $cleanAwb = urlencode(trim($awb));
        $lower = strtolower($courier);

        if (str_contains($lower, 'delhivery')) {
            return "https://www.delhivery.com/track/package/{$cleanAwb}";
        }
        if (str_contains($lower, 'bluedart') || str_contains($lower, 'blue dart')) {
            return "https://www.bluedart.com/tracking?trackNumber={$cleanAwb}";
        }
        if (str_contains($lower, 'dtdc')) {
            return "https://www.dtdc.in/tracking/tracking_results.asp?Ttype=awb_no&strCNNO={$cleanAwb}";
        }
        if (str_contains($lower, 'shiprocket')) {
            return "https://shiprocket.co/tracking/{$cleanAwb}";
        }
        if (str_contains($lower, 'xpressbee') || str_contains($lower, 'xpressbees')) {
            return "https://www.xpressbees.com/track?isawb=Yes&trackid={$cleanAwb}";
        }
        if (str_contains($lower, 'shadowfax')) {
            return "https://tracker.shadowfax.in/track?orderId={$cleanAwb}";
        }
        if (str_contains($lower, 'ekart')) {
            return "https://ekartlogistics.com/shipmenttrack/{$cleanAwb}";
        }
        if (str_contains($lower, 'ecom')) {
            return "https://ecomexpress.in/tracking/?awb_field={$cleanAwb}";
        }
        if (str_contains($lower, 'post') || str_contains($lower, 'speed post') || str_contains($lower, 'india post')) {
            return "https://www.indiapost.gov.in/_layouts/15/dop.portal.tracking/trackconsignment.aspx";
        }

        return null;
    }
}
