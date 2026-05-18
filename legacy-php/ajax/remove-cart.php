<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.');
}

$cartId = (int)($_POST['cart_id'] ?? 0);

if ($cartId <= 0) {
    jsonResponse(false, 'Invalid data provided.');
}

$db = getDB();
$userId = $_SESSION['user']['id'] ?? null;
$sessionId = session_id();

// Verify ownership
$sqlMatch = "SELECT * FROM cart WHERE id = ? AND " . ($userId ? "user_id = ?" : "session_id = ?");
$paramsMatch = [$cartId, $userId ? $userId : $sessionId];
$stmt = $db->prepare($sqlMatch);
$stmt->execute($paramsMatch);
$cartItem = $stmt->fetch();

if (!$cartItem) {
    jsonResponse(false, 'Cart item not found.');
}

// Delete from cart
$db->prepare("DELETE FROM cart WHERE id = ?")->execute([$cartId]);

// Sync Abandoned Cart
require_once __DIR__ . '/cart.php'; // Reuse updateAbandonedCart() function
updateAbandonedCart();

// Return new totals
$items = getCartItems();
$totals = cartTotals();
$count = empty($items) ? 0 : array_sum(array_column($items, 'quantity'));

jsonResponse(true, 'Item removed.', [
    'cart_count' => $count,
    'subtotal'   => formatPrice($totals['subtotal']),
    'discount'   => formatPrice($totals['discount']),
    'tax'        => formatPrice($totals['tax']),
    'shipping'   => $totals['shipping'] == 0 ? 'FREE' : formatPrice($totals['shipping']),
    'total'      => formatPrice($totals['total'])
]);
