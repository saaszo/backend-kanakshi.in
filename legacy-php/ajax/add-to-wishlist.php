<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.');
}

validateCsrf();

$productId = (int)($_POST['product_id'] ?? 0);
if ($productId <= 0) {
    jsonResponse(false, 'Invalid product selected.');
}

$added = false;
$db = getDB();

if (isLoggedIn()) {
    $stmt = $db->prepare("SELECT id FROM wishlists WHERE user_id = ? AND product_id = ? LIMIT 1");
    $stmt->execute([currentUserId(), $productId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $db->prepare("DELETE FROM wishlists WHERE id = ?")->execute([$existing['id']]);
        unset($_SESSION['wishlist'][$productId]);
    } else {
        $db->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)")->execute([currentUserId(), $productId]);
        $_SESSION['wishlist'][$productId] = true;
        $added = true;
    }
} else {
    $_SESSION['wishlist'] = $_SESSION['wishlist'] ?? [];
    if (!empty($_SESSION['wishlist'][$productId])) {
        unset($_SESSION['wishlist'][$productId]);
    } else {
        $_SESSION['wishlist'][$productId] = true;
        $added = true;
    }
}

jsonResponse(true, $added ? 'Saved to wishlist.' : 'Removed from wishlist.', ['added' => $added]);
?>
