<?php
/**
 * Checkout Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

// Force login for checkout
requireLogin();

$cartItems = getCartItems();
if (empty($cartItems)) {
    redirect(url('cart.php'));
}

$totals = cartTotals();
$user   = currentUser();
$db     = getDB();

// Log Abandoned Cart Snapshot on Page Load
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $session_id = session_id();
    $uid = $user['id'] ?? null;
    $cartJson = json_encode($cartItems);
    $totVal = $totals['total'];
    
    // Check if cart already logged for this session
    $stmtC = $db->prepare("SELECT id FROM abandoned_carts WHERE session_id = ? AND is_recovered = 0");
    $stmtC->execute([$session_id]);
    if ($ac = $stmtC->fetch()) {
        $db->prepare("UPDATE abandoned_carts SET cart_data = ?, total_value = ?, last_active = NOW() WHERE id = ?")
           ->execute([$cartJson, $totVal, $ac['id']]);
    } else {
        $db->prepare("INSERT INTO abandoned_carts (user_id, session_id, cart_data, total_value) VALUES (?, ?, ?, ?)")
           ->execute([$uid, $session_id, $cartJson, $totVal]);
    }
}

$totals = cartTotals();
$user   = currentUser();
$db     = getDB();

// Handle Order Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    
    // Address info
    $name    = inputStr('name', '', 'POST');
    $email   = inputStr('email', '', 'POST');
    $phone   = inputStr('phone', '', 'POST');
    $address = inputStr('address', '', 'POST');
    $city    = inputStr('city', '', 'POST');
    $state   = inputStr('state', '', 'POST');
    $pincode = inputStr('pincode', '', 'POST');
    $payment = inputStr('payment_method', 'cod', 'POST');
    $notes   = inputStr('notes', '', 'POST');
    
    if (!$name || !$phone || !$address || !$city || !$state || !$pincode) {
        setFlash('error', 'Please fill all required shipping details.');
    } else {
        try {
            $db->beginTransaction();
            
            // 1. Create Order
            $orderNo = generateOrderNumber();
            $stmt = $db->prepare("
                INSERT INTO orders (
                    user_id, order_number, subtotal, discount, tax, shipping_cost, shipping, total, total_amount,
                    ship_name, ship_email, ship_phone, ship_address, ship_city, ship_state, ship_pincode,
                    payment_method, payment_status, status, notes, coupon_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $couponId = $_SESSION['coupon']['id'] ?? null;
            $payStatus = 'pending'; // Initially pending until gateway succeeds
            
            $stmt->execute([
                $user['id'], $orderNo, $totals['subtotal'], $totals['discount'], $totals['tax'], $totals['shipping'], $totals['shipping'], $totals['total'], $totals['total'],
                $name, $email, $phone, $address, $city, $state, $pincode,
                $payment, $payStatus, 'pending', $notes, $couponId
            ]);
            
            $orderId = $db->lastInsertId();
            
            // 2. Insert Order Items & Deduct Stock
            $stmtItem = $db->prepare("
                INSERT INTO order_items (
                    order_id, product_id, variant_id, name, price, quantity, image,
                    size, color, variant_details, line_total, gst_percent, sku, hsn_code
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtStockProd = $db->prepare("UPDATE products SET stock = stock - ?, total_sold = total_sold + ? WHERE id = ?");
            $stmtVarStock  = $db->prepare("UPDATE product_variants SET stock = stock - ? WHERE id = ?");
            
            foreach ($cartItems as $item) {
                $qty   = (int)$item['quantity'];
                $price = (float)$item['unit_price'];
                $imgs  = json_decode($item['images'] ?? '[]', true);
                $thumb = !empty($imgs) ? $imgs[0] : null;
                $variantDetails = trim(implode(' / ', array_filter([$item['size'] ?? '', $item['color'] ?? ''])));
                $lineTotal = round($price * $qty, 2);
                
                $stmtItem->execute([
                    $orderId, $item['product_id'], $item['variant_id'],
                    $item['name'], $price, $qty,
                    $thumb, $item['size'] ?? null, $item['color'] ?? null,
                    $variantDetails ?: null, $lineTotal, (float)($item['gst_percent'] ?? 0), $item['sku'] ?? null, $item['hsn_code'] ?? null
                ]);
                
                // Deduct stock
                if ($item['variant_id']) {
                    $stmtVarStock->execute([$qty, $item['variant_id']]);
                } else {
                    $stmtStockProd->execute([$qty, $qty, $item['product_id']]);
                }
            }
            
            // 3. System notification for new order
            createNotification("New Order Receipt: #{$orderNo} from " . e($name), 'order', null, url('admin/orders/view.php?id=' . $orderId));

            // 4. Clear Cart & Coupon
            if ($user) {
                $db->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user['id']]);
            } else {
                $db->prepare("DELETE FROM cart WHERE session_id = ?")->execute([session_id()]);
            }
            unset($_SESSION['coupon']);

            // 5. Check for low stock notifications
            foreach ($cartItems as $item) {
                $id = $item['product_id'];
                $check = $db->prepare("SELECT name, stock FROM products WHERE id = ?");
                $check->execute([$id]);
                $p = $check->fetch();
                if ($p && $p['stock'] <= 5) {
                    $status = $p['stock'] <= 0 ? "OUT OF STOCK" : "Low Stock Alert";
                    createNotification("{$status}: {$p['name']} (Remaining: {$p['stock']})", 'system', null, url('admin/products/index.php'));
                }
            }
            
            $db->commit();
            
            // Mark Abandoned Cart as Recovered
            $db->prepare("UPDATE abandoned_carts SET is_recovered = 1 WHERE session_id = ?")->execute([session_id()]);
            
            // 6. Handle Gateway Redirect or Success
            if ($payment === 'cod') {
                // Send confirmation email for COD
                sendOrderConfirmationEmail($orderNo);
                redirect(url('order-success.php?order=' . $orderNo));
            } else {
                // Redirect to respective payment gateway initiation script
                redirect(url("payment/{$payment}/initiate.php?order=" . $orderNo));
            }

        } catch (Exception $e) {
            $db->rollBack();
            setFlash('error', 'Error placing order: ' . $e->getMessage());
        }
    }
}

$pageTitle = 'Checkout';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5 checkout-page">
    
    <!-- Checkout Progress -->
    <div class="checkout-steps mb-5 mx-auto" style="max-width: 600px;">
        <div class="step-item done"><i class="fa-solid fa-cart-shopping mb-1 d-block h5"></i> 1. Cart</div>
        <div class="step-item active"><i class="fa-regular fa-address-card mb-1 d-block h5"></i> 2. Checkout</div>
        <div class="step-item"><i class="fa-regular fa-circle-check mb-1 d-block h5"></i> 3. Confirm</div>
    </div>

    <form action="<?= url('checkout.php') ?>" method="POST" id="checkoutForm">
        <?= csrfField() ?>
        
        <div class="row g-5">
            <!-- Left: Shipping & Payment -->
            <div class="col-lg-7">
                
                <!-- 1. Shipping Address -->
                <div class="bg-white rounded-xl shadow-sm border border-light p-4 mb-4">
                    <h5 class="fw-800 text-dark border-bottom pb-3 mb-4 d-flex align-items-center gap-2">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:28px;height:28px;font-size:14px;">1</div> 
                        Shipping Address
                    </h5>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control bg-light" value="<?= e($user['email']) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" value="<?= e($user['phone']) ?>" pattern="[0-9]{10}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">PIN Code <span class="text-danger">*</span></label>
                            <input type="text" name="pincode" class="form-control" value="<?= e($user['pincode']) ?>" pattern="[0-9]{6}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600">Street Address <span class="text-danger">*</span></label>
                            <input type="text" name="address" class="form-control" value="<?= e($user['address']) ?>" placeholder="House no, Street name, Landmark" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">City <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control" value="<?= e($user['city']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">State <span class="text-danger">*</span></label>
                            <select name="state" class="form-select" required>
                                <option value="">Select State</option>
                                <?php 
                                $states = ['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Delhi','Jammu & Kashmir'];
                                foreach($states as $st) {
                                    $sel = ($user['state'] == $st) ? 'selected' : '';
                                    echo "<option value=\"$st\" $sel>$st</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 2. Payment Method -->
                <div class="bg-white rounded-xl shadow-sm border border-light p-4 mb-4">
                    <h5 class="fw-800 text-dark border-bottom pb-3 mb-4 d-flex align-items-center gap-2">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:28px;height:28px;font-size:14px;">2</div> 
                        Payment Method
                    </h5>
                    
                    <div class="payment-methods">
                        <!-- Razorpay -->
                        <label class="payment-method-card d-flex align-items-center gap-3">
                            <input class="form-check-input mt-0 fs-5" type="radio" name="payment_method" value="razorpay">
                            <div class="pm-icon text-primary"><i class="fa-solid fa-credit-card"></i></div>
                            <div class="flex-grow-1">
                                <div class="fw-700 text-dark ls-1">Online Payment</div>
                                <span class="text-secondary small">Cards, UPI, NetBanking via Razorpay</span>
                            </div>
                            <div class="ms-auto text-gold opacity-50"><i class="fa-solid fa-shield-halved"></i></div>
                        </label>
                        
                        <!-- PhonePe -->
                        <label class="payment-method-card d-flex align-items-center gap-3">
                            <input class="form-check-input mt-0 fs-5" type="radio" name="payment_method" value="phonepe">
                            <div class="pm-icon" style="color:#5f259f"><i class="fa-solid fa-mobile-button"></i></div>
                            <div class="flex-grow-1">
                                <div class="fw-700 text-dark ls-1">PhonePe / UPI</div>
                                <span class="text-secondary small">Fast & Secure UPI payments</span>
                            </div>
                        </label>
                        
                        <!-- Paytm -->
                        <label class="payment-method-card d-flex align-items-center gap-3">
                            <input class="form-check-input mt-0 fs-5" type="radio" name="payment_method" value="paytm">
                            <div class="pm-icon" style="color:#002E6E"><i class="fa-solid fa-wallet"></i></div>
                            <div class="flex-grow-1">
                                <div class="fw-700 text-dark ls-1">Paytm</div>
                                <span class="text-secondary small">Wallet & Postpaid payments</span>
                            </div>
                        </label>
                        
                        <!-- COD -->
                        <label class="payment-method-card d-flex align-items-center gap-3 selected">
                            <input class="form-check-input mt-0 fs-5" type="radio" name="payment_method" value="cod" checked>
                            <div class="pm-icon text-success"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                            <div class="flex-grow-1">
                                <div class="fw-700 text-dark ls-1">Cash on Delivery</div>
                                <span class="text-secondary small">Pay when you receive the product</span>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- 3. Additional Notes -->
                <div class="bg-white rounded-xl shadow-sm border border-light p-4">
                    <label class="form-label fw-600"><i class="fa-regular fa-comment-dots me-2"></i>Order Notes (Optional)</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Notes about your order, e.g. special notes for delivery."></textarea>
                </div>
                
            </div>

            <!-- Right: Order Summary -->
            <div class="col-lg-5">
                <div class="order-summary-box shadow-sm border-light bg-light rounded-xl p-0 overflow-hidden">
                    <h5 class="fw-800 text-dark p-4 border-bottom bg-white m-0">Order Summary <span class="badge bg-primary rounded-pill float-end"><?= count($cartItems) ?></span></h5>
                    
                    <div class="p-4 border-bottom bg-white" style="max-height: 350px; overflow-y: auto;">
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                            <?php foreach ($cartItems as $item): 
                                $thumb = $item['thumb'] ?? productThumb($item['images']);
                            ?>
                                <li class="d-flex gap-3 align-items-center">
                                    <div class="position-relative">
                                        <img src="<?= url($thumb) ?>" alt="" class="rounded border" style="width:60px;height:60px;object-fit:cover;">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary border border-white" style="font-size:.7rem;">
                                            <?= $item['quantity'] ?>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 text-truncate">
                                        <h6 class="mb-0 fw-600 text-dark text-truncate fs-7"><?= e($item['name']) ?></h6>
                                        <small class="text-secondary fw-500"><?= e($item['size'] ?? '') ?> <?= e($item['color'] ?? '') ?></small>
                                    </div>
                                    <div class="fw-700 text-dark"><?= formatPrice($item['unit_price'] * $item['quantity']) ?></div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="p-4 bg-white">
                        <div class="d-flex justify-content-between text-secondary fw-600 mb-2">
                            <span>Subtotal</span>
                            <span class="text-dark"><?= formatPrice($totals['subtotal']) ?></span>
                        </div>
                        <?php if ($totals['discount'] > 0): ?>
                            <div class="d-flex justify-content-between text-success fw-600 mb-2">
                                <span>Discount</span>
                                <span>-<?= formatPrice($totals['discount']) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between text-secondary fw-600 mb-2">
                            <span>GST (<?= GST_PERCENT ?>%)</span>
                            <span class="text-dark">+<?= formatPrice($totals['tax']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between text-secondary fw-600 pb-3 border-bottom">
                            <span>Shipping</span>
                            <span class="<?= $totals['shipping'] == 0 ? 'text-success' : 'text-dark' ?>">
                                <?= $totals['shipping'] == 0 ? 'FREE' : '+' . formatPrice($totals['shipping']) ?>
                            </span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center pt-3 mb-4">
                            <span class="fs-5 fw-800 text-dark">Total</span>
                            <span class="fs-4 fw-800 text-primary"><?= formatPrice($totals['total']) ?></span>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-800 text-uppercase ls-1 rounded-pill" id="placeOrderBtn">
                            Place Order
                        </button>
                        
                        <p class="text-center text-muted small mt-3 mb-0">
                            By placing your order, you agree to our <a href="<?= url('terms-conditions.php') ?>" target="_blank">Terms</a> and <a href="<?= url('privacy-policy.php') ?>" target="_blank">Privacy Notice</a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php 
$extraJs = <<<JS
<script>
// Highlight selected payment method
document.querySelectorAll('.payment-method-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.payment-method-card').forEach(c => {
            c.classList.remove('selected');
        });
        
        const radio = this.querySelector('input[type="radio"]');
        radio.checked = true;
        this.classList.add('selected');
    });
});

document.getElementById('checkoutForm').addEventListener('submit', function() {
    const btn = document.getElementById('placeOrderBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
});
</script>
JS;
require_once __DIR__ . '/includes/footer.php'; 
?>
