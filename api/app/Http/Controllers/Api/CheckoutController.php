<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderTracking;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Coupon;
use App\Models\CustomerAccessToken;
use App\Models\CustomerAddress;
use App\Models\PaymentGatewaySetting;
use App\Models\User;
use App\Services\CustomerEmailService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

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
            'ship_alt_phone' => ['nullable', 'string', 'max:20'],
            'ship_address' => ['required', 'string'],
            'save_address' => ['nullable', 'boolean'],
            'address_type' => ['nullable', 'string', 'in:home,office,other'],
            'address_label' => ['nullable', 'string', 'max:60'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'address_landmark' => ['nullable', 'string', 'max:150'],
            'address_is_default' => ['nullable', 'boolean'],
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
                $checkoutCustomer = $this->resolveCheckoutCustomer($validated, $user);
                $subtotal = 0.00;
                $totalTax = 0.00;
                $customShippingCost = 0.00;
                $hasDefaultShippingItem = false;
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

                    $shippingType = (string) ($product->shipping_type ?: 'default');
                    if ($shippingType === 'custom') {
                        $customShippingCost += ((float) $product->shipping_fee) * $itemData['quantity'];
                    } elseif ($shippingType === 'default') {
                        $hasDefaultShippingItem = true;
                    }

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
                $netSubtotal = $subtotal - $discount;
                $defaultShippingCost = 0.00;
                if ($hasDefaultShippingItem && $netSubtotal < $this->shippingThreshold()) {
                    $defaultShippingCost = $this->defaultShippingCost();
                }

                $shippingCost = $defaultShippingCost + $customShippingCost;
                
                // Final order amount
                $totalAmount = $netSubtotal + $shippingCost;

                $gatewaySetting = null;
                $gatewayConfig = null;

                if ($validated['payment_method'] !== 'cod') {
                    $gatewaySetting = $this->resolveStorefrontGateway($validated['payment_method']);

                    if (!$gatewaySetting) {
                        $this->failCheckout('This payment method is currently unavailable.');
                    }
                }

                // Step 4: Payments configuration
                $orderStatus = 'pending';
                $paymentStatus = 'pending';
                
                if ($validated['payment_method'] !== 'cod') {
                    $orderStatus = 'payment_pending';
                }

                // Step 5: Save Order
                $order = Order::query()->create([
                    'user_id' => $checkoutCustomer?->id,
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
                    'ship_alt_phone' => $validated['ship_alt_phone'] ?? null,
                    'ship_address' => $validated['ship_address'],
                    'ship_city' => $validated['ship_city'],
                    'ship_state' => $validated['ship_state'],
                    'ship_pincode' => $validated['ship_pincode'],
                    'notes' => $validated['notes'] ?? null,
                    'coupon_id' => $coupon ? $coupon->id : null,
                ]);

                if ($checkoutCustomer && ($validated['save_address'] ?? false)) {
                    $this->syncCheckoutCustomerAddress($checkoutCustomer, $validated);
                }

                // Save Order Items
                foreach ($orderItemsData as $orderItemData) {
                    $orderItemData['order_id'] = $order->id;
                    OrderItem::query()->create($orderItemData);
                }

                if ($validated['payment_method'] === 'razorpay' && $gatewaySetting) {
                    $pendingAccessToken = $this->issuePendingOrderAccessToken($order);

                    $gatewayConfig = [
                        'public_key' => $gatewaySetting->public_key,
                        'merchant_id' => $gatewaySetting->merchant_id,
                        'is_test_mode' => (bool) $gatewaySetting->is_test_mode,
                        'provider_order_id' => null,
                        'pending_access_token' => $pendingAccessToken,
                    ];

                    if (!$gatewaySetting->is_test_mode) {
                        if (empty($gatewaySetting->public_key) || empty($gatewaySetting->secret_key)) {
                            $this->failCheckout('Razorpay live keys are incomplete. Please contact support.');
                        }

                        $gatewayConfig['provider_order_id'] = $this->createRazorpayOrder($order, $gatewaySetting);
                        $order->forceFill([
                            'provider_order_id' => $gatewayConfig['provider_order_id'],
                        ])->save();
                    }
                } elseif ($validated['payment_method'] === 'phonepe' && $gatewaySetting) {
                    $pendingAccessToken = $this->issuePendingOrderAccessToken($order);

                    $gatewayConfig = [
                        'public_key' => $gatewaySetting->public_key,
                        'merchant_id' => $gatewaySetting->merchant_id,
                        'is_test_mode' => (bool) $gatewaySetting->is_test_mode,
                        'provider_order_id' => null,
                        'checkout_url' => null,
                        'pending_access_token' => $pendingAccessToken,
                    ];

                    if (!$gatewaySetting->is_test_mode) {
                        $phonePeCheckout = $this->createPhonePeCheckoutSession($order, $gatewaySetting, $pendingAccessToken);
                        $gatewayConfig['provider_order_id'] = $phonePeCheckout['provider_order_id'];
                        $gatewayConfig['checkout_url'] = $phonePeCheckout['checkout_url'];
                        $order->forceFill([
                            'provider_order_id' => $phonePeCheckout['provider_order_id'],
                        ])->save();
                    }
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

                if ($orderStatus !== 'payment_pending') {
                    $this->sendOrderMailSafely(
                        $order->fresh('items'),
                        'Your Little Divinity order has been placed',
                        $this->buildOrderMailBody(
                            $order->fresh('items'),
                            'Your order has been placed successfully. We will share each fulfillment stage with you on this email.'
                        )
                    );
                }

                $customerAuth = null;
                if ($checkoutCustomer && ! $user) {
                    [$plainTextToken, $tokenModel] = $this->issueCustomerToken($checkoutCustomer);
                    $customerAuth = [
                        'token' => $plainTextToken,
                        'token_type' => 'Bearer',
                        'expires_at' => optional($tokenModel->expires_at)?->toIso8601String(),
                        'user' => $this->serializeCustomer($checkoutCustomer->fresh()),
                    ];
                }

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
                        'ship_alt_phone' => $order->ship_alt_phone,
                        'estimated_delivery' => now()->addDays(5)->format('d M, Y'),
                        'gateway_config' => $orderStatus === 'payment_pending' ? $gatewayConfig : null,
                        'customer_auth' => $customerAuth,
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
            'access_token' => ['nullable', 'string', 'max:255'],
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
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        if ($order->payment_method !== $validated['payment_method']) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method mismatch for this order.',
            ], 422);
        }

        if (! $this->canAccessPendingOrder($request, $order, $validated['access_token'] ?? null)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this order.',
            ], 403);
        }

        if ($order->payment_status === 'paid' && $order->status !== 'payment_pending') {
            return response()->json([
                'success' => true,
                'message' => 'Payment already verified and order already confirmed.',
            ]);
        }

        if ($order->status !== 'payment_pending') {
            return response()->json([
                'success' => false,
                'message' => 'Order is no longer awaiting payment verification.',
            ], 409);
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
                if (empty($order->provider_order_id) || !hash_equals($order->provider_order_id, $razorpayOrderId)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment order reference mismatch.',
                    ], 422);
                }

                // Real HMAC verification
                $expectedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $paymentId, $secretKey);
                if (hash_equals($expectedSignature, $signature)) {
                    $isVerified = true;
                }
            } elseif ($gateway?->is_test_mode ?? true) {
                $isVerified = true;
            }

            if ($isVerified) {
                $this->markOrderAsPaid(
                    $order,
                    $paymentId ?: 'pay_simulated_' . Str::random(10),
                    $razorpayOrderId ?: $order->provider_order_id,
                    'Online payment successfully verified via Razorpay. Order confirmed.'
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Razorpay payment verified and order placed.',
                ]);
            }
        } elseif ($validated['payment_method'] === 'phonepe') {
            $transactionId = $validated['transaction_id'] ?? '';

            if ($gateway?->is_test_mode ?? true) {
                if (empty($transactionId)) {
                    $transactionId = 'txn_simulated_' . Str::random(10);
                }
                $isVerified = true;
            } else {
                $phonePeStatus = $this->fetchPhonePePaymentStatus($order, $gateway);

                if ($phonePeStatus['state'] === 'paid') {
                    $transactionId = $phonePeStatus['transaction_id'] ?: ('txn_phonepe_' . Str::random(10));
                    $isVerified = true;
                } elseif ($phonePeStatus['state'] === 'failed') {
                    $this->releasePendingOrderReservation(
                        $order,
                        'PhonePe reported a failed or cancelled prepaid attempt. Reserved stock and coupon usage released.',
                        'failed'
                    );

                    return response()->json([
                        'success' => false,
                        'message' => $phonePeStatus['message'] ?: 'PhonePe reported that the payment did not complete.',
                    ], 422);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => $phonePeStatus['message'] ?: 'PhonePe payment is still pending confirmation. Please wait a moment and try again.',
                    ], 409);
                }
            }

            if ($isVerified) {
                $this->markOrderAsPaid(
                    $order,
                    $transactionId,
                    $order->provider_order_id,
                    'Online payment successfully verified via PhonePe. Order confirmed.'
                );

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
     * Handle Razorpay webhook reconciliation for prepaid orders.
     */
    public function razorpayWebhook(Request $request): JsonResponse
    {
        $gateway = PaymentGatewaySetting::query()
            ->where('provider', 'razorpay')
            ->where('is_active', true)
            ->first();

        if (!$gateway || $gateway->is_test_mode) {
            return response()->json([
                'success' => false,
                'message' => 'Razorpay webhook is not enabled for live mode.',
            ], Response::HTTP_NOT_FOUND);
        }

        $signature = (string) $request->header('X-Razorpay-Signature', '');
        $payload = $request->getContent();

        if ($signature === '' || empty($gateway->webhook_secret)) {
            return response()->json([
                'success' => false,
                'message' => 'Missing Razorpay webhook signature or secret.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $expectedSignature = hash_hmac('sha256', $payload, $gateway->webhook_secret);
        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('Rejected Razorpay webhook due to invalid signature.');

            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook signature.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $event = (string) $request->json('event', '');
        $paymentEntity = $request->input('payload.payment.entity', []);
        $orderEntity = $request->input('payload.order.entity', []);

        $providerOrderId = (string) ($paymentEntity['order_id'] ?? $orderEntity['id'] ?? '');
        $paymentId = (string) ($paymentEntity['id'] ?? '');

        if ($providerOrderId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Provider order reference missing from webhook payload.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order = Order::query()
            ->where('provider_order_id', $providerOrderId)
            ->first();

        if (!$order) {
            Log::warning('Razorpay webhook did not match any order.', [
                'provider_order_id' => $providerOrderId,
                'event' => $event,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Webhook accepted. No matching order found.',
            ]);
        }

        if (in_array($event, ['payment.captured', 'order.paid'], true)) {
            if ($order->status === 'payment_pending') {
                $this->markOrderAsPaid(
                    $order,
                    $paymentId !== '' ? $paymentId : ($order->payment_id ?: 'pay_webhook_' . Str::random(10)),
                    $providerOrderId,
                    'Online payment confirmed by Razorpay webhook. Order reconciled automatically.'
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Razorpay payment reconciliation processed.',
            ]);
        }

        if ($event === 'payment.failed') {
            if ($order->status === 'payment_pending') {
                $this->releasePendingOrderReservation(
                    $order,
                    'Payment failed on Razorpay. Reserved stock and coupon usage released.',
                    'failed'
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Razorpay payment failure processed.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook acknowledged.',
        ]);
    }

    /**
     * Cancel a pending order, restoring reserved stock and coupon usage.
     */
    public function cancelOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string', 'exists:orders,order_number'],
            'access_token' => ['nullable', 'string', 'max:255'],
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

        if (! $this->canAccessPendingOrder($request, $order, $validated['access_token'] ?? null)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to cancel this order.',
            ], 403);
        }

        try {
            $this->releasePendingOrderReservation(
                $order,
                'Payment session abandoned or cancelled by customer. Reserved stock and coupon usage released.',
                'failed'
            );

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

    private function resolveCheckoutCustomer(array $validated, ?User $authenticatedUser): ?User
    {
        if ($authenticatedUser) {
            $authenticatedUser->forceFill([
                'name' => $validated['ship_name'],
                'email' => strtolower($validated['ship_email']),
                'phone' => $validated['ship_phone'],
                'address' => $validated['ship_address'],
                'city' => $validated['ship_city'],
                'state' => $validated['ship_state'],
                'pincode' => $validated['ship_pincode'],
                'role' => 'customer',
                'status' => $authenticatedUser->status ?: 'active',
                'is_active' => true,
                'email_verified_at' => $authenticatedUser->email_verified_at ?: now(),
            ])->save();

            return $authenticatedUser;
        }

        $email = strtolower($validated['ship_email']);
        $existingCustomer = User::query()
            ->where('email', $email)
            ->where('role', 'customer')
            ->first();

        if ($existingCustomer) {
            $existingCustomer->forceFill([
                'name' => $validated['ship_name'],
                'phone' => $validated['ship_phone'],
                'address' => $validated['ship_address'],
                'city' => $validated['ship_city'],
                'state' => $validated['ship_state'],
                'pincode' => $validated['ship_pincode'],
                'status' => $existingCustomer->status ?: 'active',
                'is_active' => true,
                'email_verified_at' => $existingCustomer->email_verified_at ?: now(),
            ])->save();

            return $existingCustomer;
        }

        if (User::query()->where('email', $email)->exists()) {
            return null;
        }

        return User::query()->create([
            'name' => $validated['ship_name'],
            'email' => $email,
            'phone' => $validated['ship_phone'],
            'address' => $validated['ship_address'],
            'city' => $validated['ship_city'],
            'state' => $validated['ship_state'],
            'pincode' => $validated['ship_pincode'],
            'role' => 'customer',
            'status' => 'active',
            'is_active' => true,
            'two_factor_enabled' => false,
            'email_verified_at' => now(),
            'password' => Hash::make(Str::password(16)),
        ]);
    }

    private function syncCheckoutCustomerAddress(User $user, array $validated): void
    {
        $addressAttributes = [
            'recipient_name' => $validated['ship_name'],
            'phone' => $validated['ship_phone'],
            'alternate_phone' => $validated['ship_alt_phone'] ?? null,
            'address_line1' => $validated['address_line1'] ?? $validated['ship_address'],
            'address_line2' => $validated['address_line2'] ?? null,
            'city' => $validated['ship_city'],
            'state' => $validated['ship_state'],
            'pincode' => $validated['ship_pincode'],
            'landmark' => $validated['address_landmark'] ?? null,
        ];

        $existingAddress = CustomerAddress::query()
            ->where('user_id', $user->id)
            ->where('address_line1', $validated['address_line1'] ?? $validated['ship_address'])
            ->where('city', $validated['ship_city'])
            ->where('state', $validated['ship_state'])
            ->where('pincode', $validated['ship_pincode'])
            ->first();

        $requestedType = $validated['address_type'] ?? null;
        $requestedLabel = $validated['address_label'] ?? null;
        $requestedDefault = (bool) ($validated['address_is_default'] ?? false);

        if ($existingAddress) {
            $existingAddress->update(array_merge($addressAttributes, [
                'type' => $requestedType ?: ($existingAddress->type ?: 'home'),
                'alternate_phone' => $validated['ship_alt_phone'] ?? $existingAddress->alternate_phone,
                'label' => $requestedLabel ?: $existingAddress->label,
                'is_default' => $requestedDefault || $existingAddress->is_default,
            ]));
            return;
        }

        if ($requestedDefault) {
            CustomerAddress::query()
                ->where('user_id', $user->id)
                ->update(['is_default' => false]);
        }

        $hasDefault = CustomerAddress::query()
            ->where('user_id', $user->id)
            ->where('is_default', true)
            ->exists();

        CustomerAddress::query()->create(array_merge($addressAttributes, [
            'user_id' => $user->id,
            'type' => $requestedType ?: 'home',
            'label' => $requestedLabel ?: 'Checkout Address',
            'alternate_phone' => $validated['ship_alt_phone'] ?? null,
            'is_default' => $requestedDefault || ! $hasDefault,
        ]));
    }

    private function issueCustomerToken(User $user): array
    {
        $plainTextToken = Str::random(64);
        $token = CustomerAccessToken::query()->create([
            'user_id' => $user->id,
            'name' => 'checkout-auto-login',
            'token_hash' => hash('sha256', $plainTextToken),
            'expires_at' => now()->addDays(30),
        ]);

        return [$plainTextToken, $token];
    }

    private function serializeCustomer(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'city' => $user->city,
            'state' => $user->state,
            'pincode' => $user->pincode,
            'email_verified_at' => optional($user->email_verified_at)?->toIso8601String(),
            'role' => $user->role,
        ];
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

                    $this->releasePendingOrderReservation(
                        $expiredOrder,
                        'Pending payment session timed out (15-min limit). Reserved stock and coupon usage released.',
                        'failed'
                    );
                });
            }
        } catch (\Throwable $e) {
            Log::error('Auto cleanup of expired orders failed: ' . $e->getMessage());
        }
    }

    private function canAccessPendingOrder(Request $request, Order $order, ?string $accessToken): bool
    {
        $user = $this->resolveCustomerFromRequest($request);

        if ($user && $order->user_id && (int) $user->id === (int) $order->user_id) {
            return true;
        }

        if (! $accessToken || ! $order->pending_access_token_hash || ! $order->pending_access_expires_at) {
            return false;
        }

        return hash_equals($order->pending_access_token_hash, hash('sha256', $accessToken))
            && now()->lt($order->pending_access_expires_at);
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

    private function resolveStorefrontGateway(string $provider): ?PaymentGatewaySetting
    {
        $gateway = PaymentGatewaySetting::query()
            ->where('provider', $provider)
            ->first();

        if (! $gateway) {
            return null;
        }

        if ($gateway->is_active) {
            return $gateway;
        }

        if ($provider === 'razorpay' && (filled($gateway->public_key) || filled($gateway->secret_key) || filled($gateway->webhook_secret))) {
            return $gateway;
        }

        if ($provider === 'phonepe' && (filled($gateway->merchant_id) || filled($gateway->public_key) || filled($gateway->secret_key))) {
            return $gateway;
        }

        return null;
    }

    private function createPhonePeCheckoutSession(Order $order, PaymentGatewaySetting $gateway, string $pendingAccessToken): array
    {
        $token = $this->fetchPhonePeAccessToken($gateway);
        $extraConfig = $this->phonePeExtraConfig($gateway);
        $apiBaseUrl = rtrim((string) ($extraConfig['api_base_url'] ?? 'https://api.phonepe.com/apis/pg'), '/');
        $redirectUrl = $this->buildPhonePeReturnUrl($order, $pendingAccessToken);
        $providerOrderId = $order->order_number;

        $response = Http::withHeaders([
            'Authorization' => 'O-Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($apiBaseUrl . '/checkout/v2/pay', [
            'merchantOrderId' => $providerOrderId,
            'amount' => (int) round(((float) $order->total_amount) * 100),
            'expireAfter' => (int) ($extraConfig['expire_after'] ?? 1200),
            'metaInfo' => [
                'udf1' => $order->order_number,
                'udf2' => $order->ship_email,
                'udf3' => $order->ship_phone,
            ],
            'paymentFlow' => [
                'type' => 'PG_CHECKOUT',
                'merchantUrls' => [
                    'redirectUrl' => $redirectUrl,
                ],
            ],
        ]);

        $checkoutUrl = (string) (
            $response->json('redirectUrl')
            ?? $response->json('paymentUrl')
            ?? $response->json('data.redirectUrl')
            ?? $response->json('data.paymentUrl')
            ?? $response->json('instrumentResponse.redirectInfo.url')
            ?? ''
        );

        if (!$response->successful() || $checkoutUrl === '') {
            Log::error('PhonePe checkout session creation failed.', [
                'order_number' => $order->order_number,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            $this->failCheckout('Unable to initialize PhonePe payment right now. Please try again.');
        }

        return [
            'provider_order_id' => $providerOrderId,
            'checkout_url' => $checkoutUrl,
        ];
    }

    private function fetchPhonePePaymentStatus(Order $order, ?PaymentGatewaySetting $gateway): array
    {
        if (!$gateway) {
            return [
                'state' => 'pending',
                'transaction_id' => null,
                'message' => 'PhonePe gateway is unavailable right now.',
            ];
        }

        $token = $this->fetchPhonePeAccessToken($gateway);
        $extraConfig = $this->phonePeExtraConfig($gateway);
        $apiBaseUrl = rtrim((string) ($extraConfig['api_base_url'] ?? 'https://api.phonepe.com/apis/pg'), '/');
        $providerOrderId = $order->provider_order_id ?: $order->order_number;

        $response = Http::withHeaders([
            'Authorization' => 'O-Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get($apiBaseUrl . '/checkout/v2/order/' . urlencode($providerOrderId) . '/status');

        if (!$response->successful()) {
            Log::warning('PhonePe order status lookup failed.', [
                'order_number' => $order->order_number,
                'provider_order_id' => $providerOrderId,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return [
                'state' => 'pending',
                'transaction_id' => null,
                'message' => 'We could not confirm the PhonePe payment yet. Please refresh after a moment.',
            ];
        }

        $payload = $response->json();
        $status = strtoupper((string) (
            data_get($payload, 'state')
            ?? data_get($payload, 'data.state')
            ?? data_get($payload, 'paymentState')
            ?? ''
        ));

        $transactionId = (string) (
            data_get($payload, 'paymentDetails.0.transactionId')
            ?? data_get($payload, 'data.paymentDetails.0.transactionId')
            ?? data_get($payload, 'transactionId')
            ?? ''
        );

        if (in_array($status, ['COMPLETED', 'SUCCESS', 'PAYMENT_SUCCESS'], true)) {
            return [
                'state' => 'paid',
                'transaction_id' => $transactionId !== '' ? $transactionId : null,
                'message' => 'PhonePe payment verified successfully.',
            ];
        }

        if (in_array($status, ['FAILED', 'PAYMENT_ERROR', 'ERROR', 'CANCELLED', 'EXPIRED'], true)) {
            return [
                'state' => 'failed',
                'transaction_id' => $transactionId !== '' ? $transactionId : null,
                'message' => 'PhonePe marked this payment as failed or cancelled.',
            ];
        }

        return [
            'state' => 'pending',
            'transaction_id' => $transactionId !== '' ? $transactionId : null,
            'message' => 'PhonePe payment is still pending confirmation.',
        ];
    }

    private function fetchPhonePeAccessToken(PaymentGatewaySetting $gateway): string
    {
        $clientId = trim((string) $gateway->public_key);
        $clientSecret = trim((string) $gateway->secret_key);
        $clientVersion = trim((string) ($gateway->secret_key_secondary ?: '1'));

        if ($clientId === '' || $clientSecret === '') {
            $this->failCheckout('PhonePe live keys are incomplete. Please contact support.');
        }

        $extraConfig = $this->phonePeExtraConfig($gateway);
        $authBaseUrl = rtrim((string) ($extraConfig['auth_base_url'] ?? 'https://api.phonepe.com/apis/identity-manager'), '/');

        $response = Http::asForm()->post($authBaseUrl . '/v1/oauth/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'client_version' => $clientVersion,
            'grant_type' => 'client_credentials',
        ]);

        $accessToken = (string) (
            $response->json('access_token')
            ?? $response->json('token')
            ?? $response->json('data.access_token')
            ?? ''
        );

        if (!$response->successful() || $accessToken === '') {
            Log::error('PhonePe access token creation failed.', [
                'provider' => $gateway->provider,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            $this->failCheckout('Unable to initialize PhonePe payment right now. Please try again.');
        }

        return $accessToken;
    }

    private function buildPhonePeReturnUrl(Order $order, string $accessToken): string
    {
        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');

        return $frontendUrl . '/checkout/phonepe-return?order_number='
            . urlencode($order->order_number)
            . '&access_token=' . urlencode($accessToken);
    }

    private function phonePeExtraConfig(PaymentGatewaySetting $gateway): array
    {
        return is_array($gateway->extra_config) ? $gateway->extra_config : [];
    }

    private function markOrderAsPaid(
        Order $order,
        string $paymentId,
        ?string $providerOrderId,
        string $trackingMessage
    ): void {
        $didConfirmOrder = false;

        DB::transaction(function () use ($order, $paymentId, $providerOrderId, $trackingMessage, &$didConfirmOrder): void {
            $lockedOrder = Order::query()->lockForUpdate()->find($order->id);

            if (!$lockedOrder || $lockedOrder->status !== 'payment_pending') {
                return;
            }

            $lockedOrder->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_id' => $paymentId,
                'provider_order_id' => $providerOrderId ?: $lockedOrder->provider_order_id,
                'pending_access_token_hash' => null,
                'pending_access_expires_at' => null,
            ]);

            OrderTracking::query()->create([
                'order_id' => $lockedOrder->id,
                'status' => 'Placed',
                'location' => 'Mumbai Warehouse',
                'message' => $trackingMessage,
            ]);

            $didConfirmOrder = true;
        });

        if ($didConfirmOrder) {
            $freshOrder = $order->fresh('items');

            if ($freshOrder) {
                $this->sendOrderMailSafely(
                    $freshOrder,
                    'Your Little Divinity order is confirmed',
                    $this->buildOrderMailBody(
                        $freshOrder,
                        'Your payment has been verified and your order is now confirmed.',
                        true
                    )
                );
            }
        }
    }

    private function releasePendingOrderReservation(
        Order $order,
        string $trackingMessage,
        string $paymentStatus
    ): void {
        DB::transaction(function () use ($order, $trackingMessage, $paymentStatus): void {
            $lockedOrder = Order::query()
                ->with('items')
                ->lockForUpdate()
                ->find($order->id);

            if (!$lockedOrder || $lockedOrder->status !== 'payment_pending') {
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
        });
    }

    private function issuePendingOrderAccessToken(Order $order): string
    {
        $plainTextToken = Str::random(64);

        $order->forceFill([
            'pending_access_token_hash' => hash('sha256', $plainTextToken),
            'pending_access_expires_at' => now()->addMinutes(30),
        ])->save();

        return $plainTextToken;
    }

    private function shippingThreshold(): float
    {
        return 499.0;
    }

    private function defaultShippingCost(): float
    {
        return 99.0;
    }

    private function buildOrderMailBody(Order $order, string $headline, bool $includePaymentSummary = false): string
    {
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
            "Order Total: INR " . number_format((float) $order->total_amount, 2),
        ];

        if ($includePaymentSummary) {
            $lines[] = "Payment Method: " . strtoupper((string) $order->payment_method);
        }

        if ($itemsSummary !== '') {
            $lines[] = '';
            $lines[] = "Items:";
            $lines[] = $itemsSummary;
        }

        $lines[] = '';
        $lines[] = 'We will continue sending you order stage updates on this email address.';
        $lines[] = '';
        $lines[] = 'Team Little Divinity';

        return implode("\n", $lines);
    }

    private function sendOrderMailSafely(Order $order, string $subject, string $body): void
    {
        try {
            $service = app(CustomerEmailService::class);
            if (! $service->canSendOrderEmails()) {
                return;
            }

            $service->sendOrderMail($order->ship_email, $subject, $body);
        } catch (\Throwable $throwable) {
            Log::warning('Order email delivery failed.', [
                'order_number' => $order->order_number,
                'subject' => $subject,
                'error' => $throwable->getMessage(),
            ]);
        }
    }

    private function failCheckout(string $message, int $status = 422): never
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $message,
        ], $status));
    }
}
