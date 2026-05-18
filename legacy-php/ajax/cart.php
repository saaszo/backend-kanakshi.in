<?php
/**
 * AJAX Cart Handler & Abandoned Cart Tracking
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php'; // For getCartItems, etc.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.');
}

$action = $_POST['action'] ?? '';
$db = getDB();

// Handle 'add' action
if ($action === 'add') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $variantId = !empty($_POST['variant_id']) ? (int)$_POST['variant_id'] : null;
    $quantity  = max(1, (int)($_POST['quantity'] ?? 1));

    if (!$productId) jsonResponse(false, 'Invalid product.');

    // Check stock
    $stockQ = "SELECT stock FROM products WHERE id = ?";
    $stockParams = [$productId];
    if ($variantId) {
        $stockQ = "SELECT stock FROM product_variants WHERE id = ?";
        $stockParams = [$variantId];
    }
    $stmt = $db->prepare($stockQ);
    $stmt->execute($stockParams);
    $availStock = (int)$stmt->fetchColumn();

    if ($availStock < $quantity) {
        jsonResponse(false, 'Not enough stock available.');
    }

    $userId = $_SESSION['user']['id'] ?? null;
    $sessionId = session_id();

    // Check if item already in cart
    $sqlCheck = "SELECT id, quantity FROM cart WHERE product_id = ? AND " . 
                ($variantId ? "variant_id = ?" : "variant_id IS NULL") . " AND " .
                ($userId ? "user_id = ?" : "session_id = ?");
    
    $paramsCheck = $variantId ? [$productId, $variantId] : [$productId];
    $paramsCheck[] = $userId ? $userId : $sessionId;

    $stmt = $db->prepare($sqlCheck);
    $stmt->execute($paramsCheck);
    $existing = $stmt->fetch();

    if ($existing) {
        $newQty = $existing['quantity'] + $quantity;
        if ($newQty > $availStock) jsonResponse(false, 'Cannot add more. Max stock reached.');
        $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$newQty, $existing['id']]);
    } else {
        $sqlIns = "INSERT INTO cart (user_id, session_id, product_id, variant_id, quantity) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sqlIns);
        $stmt->execute([$userId, $sessionId, $productId, $variantId, $quantity]);
    }

    updateAbandonedCart();

    $cartItems = getCartItems();
    $count = array_sum(array_column($cartItems, 'quantity'));
    
    jsonResponse(true, 'Added to cart successfully.', ['cart_count' => $count]);
}

// Handle 'update-cart.php' and 'remove-cart.php' equivalents which the JS calls separately,
// Wait, the frontend JS calls POST to 'ajax/update-cart.php' instead of using an 'action' param!
// Let me just route them by creating the separate files or adjusting the current logic here contextually.

jsonResponse(false, 'Action not recognized.');

/**
 * Updates the abandoned_carts table state for tracking
 */
function updateAbandonedCart() {
    $db = getDB();
    $userId = $_SESSION['user']['id'] ?? null;
    $sessionId = session_id();
    
    $items = getCartItems();
    if (empty($items)) {
        // If cart goes empty, we can mark as recovered or delete
        $stmt = $db->prepare("DELETE FROM abandoned_carts WHERE session_id = ?");
        $stmt->execute([$sessionId]);
        return;
    }
    
    $totals = cartTotals();
    $cartData = json_encode($items);
    
    // Check if exists
    $stmt = $db->prepare("SELECT id FROM abandoned_carts WHERE session_id = ?");
    $stmt->execute([$sessionId]);
    if ($stmt->fetch()) {
        $upd = $db->prepare("UPDATE abandoned_carts SET user_id = ?, cart_data = ?, total_value = ?, last_active = NOW() WHERE session_id = ?");
        $upd->execute([$userId, $cartData, $totals['total'], $sessionId]);
    } else {
        $ins = $db->prepare("INSERT INTO abandoned_carts (user_id, session_id, cart_data, total_value) VALUES (?, ?, ?, ?)");
        $ins->execute([$userId, $sessionId, $cartData, $totals['total']]);
    }
}
