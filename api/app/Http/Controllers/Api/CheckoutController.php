<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderTracking;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Coupon;
use App\Models\CustomerAccessToken;
use App\Models\PaymentGatewaySetting;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutController
{
    /**
     * Handle the secure web checkout order creation.
     */
    public function store(Request $request): JsonResponse
    {
        $this->cleanupExpiredPendingOrders();

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
                        $this->failCheckout(
                            "The product '{$itemData['product_id']}' is no longer active or available."
                        );
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
                            $this->failCheckout('The selected product variant is no longer active or available.');
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
                        $this->failCheckout(
                            "Insufficient stock for '{$product->name}'. Only {$availableStock} items are remaining."
                        );
                    }

                    // Deduct stock (reserve it)
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
                            $this->failCheckout(
                                "This coupon requires a minimum spend of ₹{$coupon->min_order_amount}."
                            );
                        }

                        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
                            $this->failCheckout('This coupon has reached its usage limit.');
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
                        $this->failCheckout(
                            "The coupon code '{$validated['coupon_code']}' is invalid or expired."
                        );
                    }
                }

                // Step 3: Shipping calculations
                // Flat ₹99 shipping, free for net totals of ₹999 or more
                $netSubtotal = $subtotal - $discount;
                $shippingCost = $netSubtotal >= 999 ? 0.00 : 99.00;
                
                // Final order amount
                $totalAmount = $netSubtotal + $shippingCost;

                $gatewaySetting = null;
                $gatewayConfig = null;

                if ($validated['payment_method'] !== 'cod') {
                    $gatewaySetting = PaymentGatewaySetting::query()
                        ->where('provider', $validated['payment_method'])
                        ->where('is_active', true)
                        ->first();

                    if (!$gatewaySetting) {
                        $this->failCheckout('This payment method is currently unavailable.');
                    }
                }

                if ($validated['payment_method'] === 'phonepe' && $gatewaySetting && !$gatewaySetting->is_test_mode) {
                    $this->failCheckout('PhonePe live checkout is not configured yet. Please use COD or test mode.');
                }

                // Step 4: Payments configuration
                $orderStatus = 'pending';
                $paymentStatus = 'pending';
                
                if ($validated['payment_method'] !== 'cod') {
                    $orderStatus = 'payment_pending';
                }

                // Step 5: Save Order
                $order = Order::query()->create([
                    'user_id' => $user ? $user->id : null,
                    'status' => $orderStatus,
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

                if ($validated['payment_method'] === 'razorpay' && $gatewaySetting) {
                    $gatewayConfig = [
                        'public_key' => $gatewaySetting->public_key,
                        'merchant_id' => $gatewaySetting->merchant_id,
                        'is_test_mode' => (bool) $gatewaySetting->is_test_mode,
                        'provider_order_id' => null,
                    ];

                    if (!$gatewaySetting->is_test_mode) {
                        if (empty($gatewaySetting->public_key) || empty($gatewaySetting->secret_key)) {
                            $this->failCheckout('Razorpay live keys are incomplete. Please contact support.');
                        }

                        $gatewayConfig['provider_order_id'] = $this->createRazorpayOrder($order, $gatewaySetting);
                    }
                } elseif ($validated['payment_method'] === 'phonepe' && $gatewaySetting) {
                    $gatewayConfig = [
                        'public_key' => $gatewaySetting->public_key,
                        'merchant_id' => $gatewaySetting->merchant_id,
                        'is_test_mode' => (bool) $gatewaySetting->is_test_mode,
                        'provider_order_id' => null,
                    ];
                }

                // Step 6: Create initial tracking event
                OrderTracking::query()->create([
                    'order_id' => $order->id,
                    'status' => $orderStatus === 'payment_pending' ? 'Payment Pending' : 'Placed',
                    'location' => 'Mumbai Warehouse',
                    'message' => $orderStatus === 'payment_pending' 
                        ? 'Order initialized. Awaiting secure online payment confirmation.' 
                        : 'Order has been successfully placed. COD verification or payment authorization complete.',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $orderStatus === 'payment_pending' 
                        ? 'Order initialized. Awaiting secure payment.' 
                        : 'Order placed successfully.',
                    'data' => [
                        'order_number' => $order->order_number,
                        'total_amount' => $order->total_amount,
                        'payment_method' => $order->payment_method,
                        'payment_status' => $order->payment_status,
                        'ship_name' => $order->ship_name,
                        'ship_email' => $order->ship_email,
                        'ship_phone' => $order->ship_phone,
                        'estimated_delivery' => now()->addDays(5)->format('d M, Y'),
                        'gateway_config' => $orderStatus === 'payment_pending' ? $gatewayConfig : null
                    ]
                ], 201);
            });
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        } catch (\Throwable $e) {
            Log::error('Checkout execution failed: ' . $e->getMessage(), [
                'exception' => $e,
                'input' => $validated,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while placing your order. Please try again.',
            ], 500);
        }
    }

    /**
     * Verify the payment for online gateways.
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string', 'exists:orders,order_number'],
            'payment_method' => ['required', 'string', 'in:razorpay,phonepe'],
            'order_contact' => ['nullable', 'string', 'max:150'],
            // Razorpay specific inputs
            'razorpay_payment_id' => ['nullable', 'string'],
            'razorpay_order_id' => ['nullable', 'string'],
            'razorpay_signature' => ['nullable', 'string'],
            // PhonePe specific inputs
            'transaction_id' => ['nullable', 'string'],
            'provider_reference_id' => ['nullable', 'string'],
        ]);

        $order = Order::query()
            ->where('order_number', $validated['order_number'])
            ->where('status', 'payment_pending')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or is already processed.',
            ], 404);
        }

        if (!$this->canAccessPendingOrder($request, $order, $validated['order_contact'] ?? null)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this order.',
            ], 403);
        }

        $gateway = PaymentGatewaySetting::query()
            ->where('provider', $validated['payment_method'])
            ->first();

        $isVerified = false;

        // Perform actual signature/checksum validation or fallback to simulation if in test mode or empty keys
        if ($validated['payment_method'] === 'razorpay') {
            $paymentId = $validated['razorpay_payment_id'] ?? '';
            $razorpayOrderId = $validated['razorpay_order_id'] ?? '';
            $signature = $validated['razorpay_signature'] ?? '';

            $secretKey = $gateway ? $gateway->secret_key : null;

            if ($gateway && !$gateway->is_test_mode && !empty($secretKey) && !empty($paymentId) && !empty($razorpayOrderId) && !empty($signature)) {
                // Real HMAC verification
                $expectedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $paymentId, $secretKey);
                if (hash_equals($expectedSignature, $signature)) {
                    $isVerified = true;
                }
            } elseif ($gateway?->is_test_mode ?? true) {
                $isVerified = true;
            }

            if ($isVerified) {
                DB::transaction(function () use ($order, $paymentId): void {
                    $order->update([
                        'status' => 'confirmed',
                        'payment_status' => 'paid',
                        'payment_id' => $paymentId ?: 'pay_simulated_' . Str::random(10),
                    ]);

                    OrderTracking::query()->create([
                        'order_id' => $order->id,
                        'status' => 'Placed',
                        'location' => 'Mumbai Warehouse',
                        'message' => 'Online payment successfully verified via Razorpay. Order confirmed.',
                    ]);
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Razorpay payment verified and order placed.',
                ]);
            }
        } elseif ($validated['payment_method'] === 'phonepe') {
            $transactionId = $validated['transaction_id'] ?? '';

            if ($gateway?->is_test_mode ?? true) {
                $isVerified = !empty($transactionId);
                if ($isVerified && empty($transactionId)) {
                    $transactionId = 'txn_simulated_' . Str::random(10);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'PhonePe live payment verification is not configured yet.',
                ], 422);
            }

            if ($isVerified) {
                DB::transaction(function () use ($order, $transactionId): void {
                    $order->update([
                        'status' => 'confirmed',
                        'payment_status' => 'paid',
                        'payment_id' => $transactionId,
                    ]);

                    OrderTracking::query()->create([
                        'order_id' => $order->id,
                        'status' => 'Placed',
                        'location' => 'Mumbai Warehouse',
                        'message' => 'Online payment successfully verified via PhonePe. Order confirmed.',
                    ]);
                });

                return response()->json([
                    'success' => true,
                    'message' => 'PhonePe payment verified and order placed.',
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment verification failed.',
        ], 400);
    }

    /**
     * Cancel a pending order, restoring reserved stock and coupon usage.
     */
    public function cancelOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string', 'exists:orders,order_number'],
            'order_contact' => ['nullable', 'string', 'max:150'],
        ]);

        $order = Order::query()
            ->where('order_number', $validated['order_number'])
            ->where('status', 'payment_pending')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or cannot be cancelled.',
            ], 404);
        }

        if (!$this->canAccessPendingOrder($request, $order, $validated['order_contact'] ?? null)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to cancel this order.',
            ], 403);
        }

        try {
            DB::transaction(function () use ($order): void {
                // Rollback stock
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

                    // Decrement product total_sold since it was incremented during creation
                    $product = Product::query()->lockForUpdate()->find($item->product_id);
                    if ($product && $product->total_sold >= $item->quantity) {
                        $product->decrement('total_sold', $item->quantity);
                    }
                }

                // Rollback coupon usage
                if ($order->coupon_id) {
                    $coupon = Coupon::query()->lockForUpdate()->find($order->coupon_id);
                    if ($coupon && $coupon->used_count > 0) {
                        $coupon->decrement('used_count');
                    }
                }

                // Update order status
                $order->update([
                    'status' => 'cancelled',
                    'payment_status' => 'failed',
                ]);

                // Create tracking update
                OrderTracking::query()->create([
                    'order_id' => $order->id,
                    'status' => 'Cancelled',
                    'location' => 'Mumbai Warehouse',
                    'message' => 'Payment session abandoned or cancelled by customer. Reserved stock and coupon usage released.',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled and reserved stock restored.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Order cancellation failed: ' . $e->getMessage(), [
                'exception' => $e,
                'order_number' => $order->order_number,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during order cancellation.',
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

    private function cleanupExpiredPendingOrders(): void
    {
        try {
            $expiredOrderIds = Order::query()
                ->where('status', 'payment_pending')
                ->where('created_at', '<', now()->subMinutes(15))
                ->pluck('id');

            foreach ($expiredOrderIds as $orderId) {
                DB::transaction(function () use ($orderId): void {
                    $expiredOrder = Order::query()
                        ->with('items')
                        ->lockForUpdate()
                        ->find($orderId);

                    if (
                        !$expiredOrder ||
                        $expiredOrder->status !== 'payment_pending' ||
                        $expiredOrder->created_at->gte(now()->subMinutes(15))
                    ) {
                        return;
                    }

                    foreach ($expiredOrder->items as $item) {
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

                    if ($expiredOrder->coupon_id) {
                        $coupon = Coupon::query()->lockForUpdate()->find($expiredOrder->coupon_id);
                        if ($coupon && $coupon->used_count > 0) {
                            $coupon->decrement('used_count');
                        }
                    }

                    $expiredOrder->update([
                        'status' => 'cancelled',
                        'payment_status' => 'failed',
                    ]);

                    OrderTracking::query()->create([
                        'order_id' => $expiredOrder->id,
                        'status' => 'Cancelled',
                        'location' => 'Mumbai Warehouse',
                        'message' => 'Pending payment session timed out (15-min limit). Reserved stock and coupon usage released.',
                    ]);
                });
            }
        } catch (\Throwable $e) {
            Log::error('Auto cleanup of expired orders failed: ' . $e->getMessage());
        }
    }

    private function canAccessPendingOrder(Request $request, Order $order, ?string $contact): bool
    {
        $user = $this->resolveCustomerFromRequest($request);

        if ($user && $order->user_id && (int) $user->id === (int) $order->user_id) {
            return true;
        }

        if (!$contact) {
            return false;
        }

        $normalizedInput = $this->normalizeContact($contact);

        return $normalizedInput !== '' && in_array($normalizedInput, [
            $this->normalizeContact($order->ship_email),
            $this->normalizeContact($order->ship_phone),
        ], true);
    }

    private function normalizeContact(?string $value): string
    {
        if (!$value) {
            return '';
        }

        $trimmed = trim(strtolower($value));

        if (filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
            return $trimmed;
        }

        return preg_replace('/\D+/', '', $trimmed) ?: '';
    }

    private function createRazorpayOrder(Order $order, PaymentGatewaySetting $gateway): string
    {
        $response = Http::withBasicAuth($gateway->public_key, $gateway->secret_key)
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => (int) round(((float) $order->total_amount) * 100),
                'currency' => 'INR',
                'receipt' => $order->order_number,
                'payment_capture' => 1,
                'notes' => [
                    'order_number' => $order->order_number,
                    'ship_email' => $order->ship_email,
                ],
            ]);

        if (!$response->successful() || empty($response->json('id'))) {
            Log::error('Razorpay order creation failed.', [
                'order_number' => $order->order_number,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            $this->failCheckout('Unable to initialize Razorpay payment right now. Please try again.');
        }

        return (string) $response->json('id');
    }

    private function failCheckout(string $message, int $status = 422): never
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $message,
        ], $status));
    }
}
