<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderTracking;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Coupon;
use App\Models\CustomerAccessToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController
{
    /**
     * Handle the secure web checkout order creation.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ship_name' => ['required', 'string', 'max:100'],
            'ship_email' => ['required', 'email', 'max:150'],
            'ship_phone' => ['required', 'string', 'max:20'],
            'ship_address' => ['required', 'string'],
            'ship_city' => ['required', 'string', 'max:100'],
            'ship_state' => ['required', 'string', 'max:100'],
            'ship_pincode' => ['required', 'string', 'max:10'],
            'payment_method' => ['required', 'string', 'in:cod,razorpay,phonepe'],
            'payment_id' => ['nullable', 'string', 'max:150'],
            'coupon_code' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        // Resolve customer if authenticated via token
        $user = $this->resolveCustomerFromRequest($request);

        try {
            return DB::transaction(function () use ($validated, $user, $request): JsonResponse {
                $subtotal = 0.00;
                $totalTax = 0.00;
                $orderItemsData = [];

                // Step 1: Process each item in the cart
                foreach ($validated['items'] as $itemData) {
                    $product = Product::query()->lockForUpdate()->find($itemData['product_id']);
                    
                    if (!$product || !$product->is_active) {
                        return response()->json([
                            'success' => false,
                            'message' => "The product '{$itemData['product_id']}' is no longer active or available.",
                        ], 422);
                    }

                    $variant = null;
                    $price = $product->sale_price ?: $product->price;
                    $sku = $product->sku;
                    $size = null;
                    $color = null;
                    $variantDetails = null;

                    // If variant is specified, fetch variant and verify it belongs to the product
                    if (!empty($itemData['variant_id'])) {
                        $variant = ProductVariant::query()
                            ->lockForUpdate()
                            ->where('product_id', $product->id)
                            ->find($itemData['variant_id']);

                        if (!$variant || !$variant->is_active) {
                            return response()->json([
                                'success' => false,
                                'message' => "The selected product variant is no longer active or available.",
                            ], 422);
                        }

                        $price = $variant->price ?: $price;
                        $sku = $variant->sku ?: $sku;
                        $size = $variant->size;
                        $color = $variant->color;

                        $details = [];
                        if ($size) $details[] = "Size: {$size}";
                        if ($color) $details[] = "Color: {$color}";
                        $variantDetails = implode(', ', $details);
                    }

                    // Check stock levels
                    $availableStock = $variant ? $variant->stock : $product->stock;
                    if ($availableStock < $itemData['quantity']) {
                        return response()->json([
                            'success' => false,
                            'message' => "Insufficient stock for '{$product->name}'. Only {$availableStock} items are remaining.",
                        ], 422);
                    }

                    // Deduct stock
                    if ($variant) {
                        $variant->decrement('stock', $itemData['quantity']);
                    } else {
                        $product->decrement('stock', $itemData['quantity']);
                    }

                    // Calculate pricing
                    $lineTotal = $price * $itemData['quantity'];
                    $subtotal += $lineTotal;

                    // Tax calculation (GST is inclusive in price)
                    $gstPercent = $product->gst_percent ?: 3.00;
                    $gstAmount = $lineTotal - ($lineTotal / (1 + ($gstPercent / 100)));
                    $totalTax += $gstAmount;

                    // Determine product image
                    $productImage = null;
                    if ($product->images && is_array($product->images) && count($product->images) > 0) {
                        $productImage = $product->images[0];
                    }

                    // Increment sold counter
                    $product->increment('total_sold', $itemData['quantity']);

                    $orderItemsData[] = [
                        'product_id' => $product->id,
                        'variant_id' => $variant ? $variant->id : null,
                        'name' => $product->name,
                        'price' => $price,
                        'quantity' => $itemData['quantity'],
                        'image' => $productImage,
                        'size' => $size,
                        'color' => $color,
                        'variant_details' => $variantDetails,
                        'line_total' => $lineTotal,
                        'gst_percent' => $gstPercent,
                        'sku' => $sku,
                        'hsn_code' => $product->hsn_code,
                    ];
                }

                // Step 2: Handle coupons & discount
                $discount = 0.00;
                $coupon = null;
                if (!empty($validated['coupon_code'])) {
                    $coupon = Coupon::query()
                        ->where('code', $validated['coupon_code'])
                        ->first();

                    if ($coupon && $coupon->isCurrentlyActive()) {
                        if ($coupon->min_order_amount && $subtotal < $coupon->min_order_amount) {
                            return response()->json([
                                'success' => false,
                                'message' => "This coupon requires a minimum spend of ₹{$coupon->min_order_amount}.",
                            ], 422);
                        }

                        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
                            return response()->json([
                                'success' => false,
                                'message' => "This coupon has reached its usage limit.",
                            ], 422);
                        }

                        // Calculate discount
                        if ($coupon->type === 'percent') {
                            $discount = $subtotal * ($coupon->value / 100);
                        } else {
                            $discount = $coupon->value;
                        }

                        // Discount cannot exceed subtotal
                        if ($discount > $subtotal) {
                            $discount = $subtotal;
                        }

                        // Increment coupon usage
                        $coupon->increment('used_count');
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => "The coupon code '{$validated['coupon_code']}' is invalid or expired.",
                        ], 422);
                    }
                }

                // Step 3: Shipping calculations
                // Flat ₹99 shipping, free for net totals of ₹999 or more
                $netSubtotal = $subtotal - $discount;
                $shippingCost = $netSubtotal >= 999 ? 0.00 : 99.00;
                
                // Final order amount
                $totalAmount = $netSubtotal + $shippingCost;

                // Step 4: Payments simulation
                $paymentStatus = 'pending';
                if ($validated['payment_method'] === 'razorpay' || $validated['payment_method'] === 'phonepe') {
                    // Simulating successful online payment
                    $paymentStatus = 'paid';
                }

                // Step 5: Save Order
                $order = Order::query()->create([
                    'user_id' => $user ? $user->id : null,
                    'status' => 'pending',
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $totalTax,
                    'shipping_cost' => $shippingCost,
                    'total_amount' => $totalAmount,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => $paymentStatus,
                    'payment_id' => $validated['payment_id'] ?? null,
                    'ship_name' => $validated['ship_name'],
                    'ship_email' => $validated['ship_email'],
                    'ship_phone' => $validated['ship_phone'],
                    'ship_address' => $validated['ship_address'],
                    'ship_city' => $validated['ship_city'],
                    'ship_state' => $validated['ship_state'],
                    'ship_pincode' => $validated['ship_pincode'],
                    'notes' => $validated['notes'] ?? null,
                    'coupon_id' => $coupon ? $coupon->id : null,
                ]);

                // Save Order Items
                foreach ($orderItemsData as $orderItemData) {
                    $orderItemData['order_id'] = $order->id;
                    OrderItem::query()->create($orderItemData);
                }

                // Step 6: Create initial tracking event
                OrderTracking::query()->create([
                    'order_id' => $order->id,
                    'status' => 'Placed',
                    'location' => 'Mumbai Warehouse',
                    'message' => 'Order has been successfully placed. COD verification or payment authorization complete.',
                ]);

                // Create a few standard sample updates ahead of time for realistic simulation
                // but marked as in the future or we can keep it with just 1 milestone, 
                // and let the tracker show standard milestones based on current status.
                // Let's seed just the Placed log first.

                return response()->json([
                    'success' => true,
                    'message' => 'Order placed successfully.',
                    'data' => [
                        'order_number' => $order->order_number,
                        'total_amount' => $order->total_amount,
                        'payment_method' => $order->payment_method,
                        'payment_status' => $order->payment_status,
                        'ship_name' => $order->ship_name,
                        'ship_email' => $order->ship_email,
                        'estimated_delivery' => now()->addDays(5)->format('d M, Y'),
                    ]
                ], 201);
            });
        } catch (\Throwable $e) {
            Log::error('Checkout execution failed: ' . $e->getMessage(), [
                'exception' => $e,
                'input' => $validated,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while placing your order. Please try again. details: ' . $e->getMessage(),
            ], 500);
        }
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
