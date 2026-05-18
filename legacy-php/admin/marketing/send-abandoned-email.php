<?php
/**
 * AJAX Handler: Send Abandoned Cart Recovery Email
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/templates/abandoned-cart-email.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.');
}

// Security: Check if admin
if (!isAdmin()) {
    jsonResponse(false, 'Unauthorized access.');
}

// CSRF Validation (Handled by postAjax in main.js usually, but sometimes admin has different check)
// validateCsrf(); 

$cartId = (int)($_POST['cart_id'] ?? 0);
if (!$cartId) {
    jsonResponse(false, 'Invalid cart ID.');
}

$db = getDB();

// 1. Fetch Cart Data
$stmt = $db->prepare("
    SELECT ac.*, u.name as customer_name, u.email as customer_email
    FROM abandoned_carts ac
    JOIN users u ON ac.user_id = u.id
    WHERE ac.id = ?
");
$stmt->execute([$cartId]);
$cart = $stmt->fetch();

if (!$cart) {
    jsonResponse(false, 'Abandoned cart not found or no user associated.');
}

if (empty($cart['customer_email'])) {
    jsonResponse(false, 'Customer email not found for this cart.');
}

// 2. Build Email
$items = json_decode($cart['cart_data'], true) ?: [];
$body  = getAbandonedCartEmail($cart['customer_name'], $items, (float)$cart['total_value']);
$subject = "🛒 We've saved your cart! — " . getSetting('site_name', 'MyShop');

// 3. Send Email
$success = sendEmail($cart['customer_email'], $subject, $body);

if ($success) {
    // Optional: Log the reminder or update a 'reminder_sent' flag if exists
    // $db->prepare("UPDATE abandoned_carts SET reminder_sent_at = NOW() WHERE id = ?")->execute([$cartId]);
    jsonResponse(true, 'Recovery email sent successfully to ' . $cart['customer_email']);
} else {
    jsonResponse(false, 'Failed to send recovery email. Please check SMTP settings.');
}
