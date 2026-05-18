<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.');
}

$cartId = (int)($_POST['cart_id'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 1);

if ($cartId <= 0 || $quantity <= 0) {
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

$productId = $cartItem['product_id'];
$variantId = $cartItem['variant_id'];

// Check stock limit for update
$stockQ = $variantId ? "SELECT stock FROM product_variants WHERE id = ?" : "SELECT stock FROM products WHERE id = ?";
$stockP = $variantId ? [$variantId] : [$productId];
$st = $db->prepare($stockQ);
$st->execute($stockP);
$availStock = (int)$st->fetchColumn();

if ($quantity > $availStock) {
    jsonResponse(false, 'Not enough stock available.');
}

// Update DB
$upd = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
$upd->execute([$quantity, $cartId]);

// Sync Abandoned Cart
require_once __DIR__ . '/cart.php'; // Reuse updateAbandonedCart() function
updateAbandonedCart();

// Fetch new totals to respond
$items = getCartItems();
$totals = cartTotals();
$count = array_sum(array_column($items, 'quantity'));

// Get updated item's line total
$lineTotal = 0;
foreach ($items as $itm) {
    if ($itm['id'] == $cartId) {
        $lineTotal = formatPrice($itm['price'] * $itm['quantity']);
        break;
    }
}

jsonResponse(true, 'Cart updated.', [
    'cart_count' => $count,
    'subtotal'   => formatPrice($totals['subtotal']),
    'discount'   => formatPrice($totals['discount']),
    'tax'        => formatPrice($totals['tax']),
    'shipping'   => $totals['shipping'] == 0 ? 'FREE' : formatPrice($totals['shipping']),
    'total'      => formatPrice($totals['total']),
    'line_total' => $lineTotal
]);
