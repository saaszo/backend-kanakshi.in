<?php
/**
 * AJAX: Apply Coupon to Cart
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.');
}

$code = strtoupper(trim(inputStr('code', '', 'POST')));

if (empty($code)) {
    // Clear coupon if empty code submitted
    unset($_SESSION['coupon']);
    $_SESSION['coupon_discount'] = 0;
    jsonResponse(true, 'Coupon cleared.');
}

$db = getDB();

// 1. Fetch Coupon
$stmt = $db->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
$stmt->execute([$code]);
$coupon = $stmt->fetch();

if (!$coupon) {
    jsonResponse(false, 'Invalid coupon code.');
}

// 2. Check Expiry
if ($coupon['expiry_date'] && strtotime($coupon['expiry_date']) < time()) {
    jsonResponse(false, 'This coupon has expired.');
}

// 3. Check Usage Limits
if ($coupon['max_uses'] > 0 && $coupon['used_count'] >= $coupon['max_uses']) {
    jsonResponse(false, 'This coupon limit has been reached.');
}

// 4. Check Order Minimum
$cartItems = getCartItems();
$subtotal  = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['line_total'];
}

if ($subtotal < $coupon['min_order_amount']) {
    jsonResponse(false, 'Minimum order amount of ' . formatPrice($coupon['min_order_amount']) . ' required for this coupon.');
}

// 5. Calculate Discount
$discount = 0;
if ($coupon['type'] === 'percent') {
    $discount = ($subtotal * ($coupon['value'] / 100));
} else {
    $discount = (float)$coupon['value'];
}

// 6. Save to Session
$_SESSION['coupon'] = [
    'id'       => $coupon['id'],
    'code'     => $coupon['code'],
    'type'     => $coupon['type'],
    'value'    => $coupon['value'],
    'discount' => $discount
];
$_SESSION['coupon_discount'] = $discount;

jsonResponse(true, 'Coupon applied successfully! You saved ' . formatPrice($discount), [
    'discount' => $discount,
    'total'    => cartTotals($discount)['total']
]);
