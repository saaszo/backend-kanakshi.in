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

        return view('admin.returns.index', [
            'returns' => $query->latest()->paginate(15)->withQueryString(),
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
            'admin_notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($return, $validated): void {
            $previousStatus = $return->status;

            $return->update([
                'status' => $validated['status'],
                'approved_amount' => $validated['approved_amount'] ?? $return->approved_amount,
                'admin_notes' => $validated['admin_notes'] ?? $return->admin_notes,
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
            }

            if ($previousStatus !== $validated['status']) {
                $return->order->trackingUpdates()->create([
                    'status' => 'Return ' . ucfirst($validated['status']),
                    'location' => 'Returns Desk',
                    'message' => 'Return request ' . $return->return_number . ' updated to ' . $validated['status'] . '.',
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
}
