<?php
/**
 * AJAX Handler: Request Order Return
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.');
}

// Security: Check login
if (!isLoggedIn()) {
    jsonResponse(false, 'Please login to request a return.');
}

$user = currentUser();
$orderId = (int)($_POST['order_id'] ?? 0);
$reason  = inputStr('reason', '', 'POST');
$details = inputStr('details', '', 'POST');

if (!$orderId || !$reason) {
    jsonResponse(false, 'Order ID and Reason are required.');
}

$db = getDB();

// 1. Verify Order Ownership and Status
$stmt = $db->prepare("SELECT id, order_number, status FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $user['id']]);
$order = $stmt->fetch();

if (!$order) {
    jsonResponse(false, 'Order not found or unauthorized.');
}

if ($order['status'] !== 'delivered') {
    jsonResponse(false, 'Returns can only be requested for delivered orders.');
}

// 2. Check if already requested
$stmtCheck = $db->prepare("SELECT id FROM order_returns WHERE order_id = ?");
$stmtCheck->execute([$orderId]);
if ($stmtCheck->fetch()) {
    jsonResponse(false, 'A return request already exists for this order.');
}

// 3. Insert Request
try {
    $fullReason = $reason . (!empty($details) ? " - " . $details : "");
    $stmtInsert = $db->prepare("INSERT INTO order_returns (order_id, user_id, reason, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
    $stmtInsert->execute([$orderId, $user['id'], $fullReason]);
    
    // 4. Create Notification for Admin
    createNotification(
        "Return Requested: Order #{$order['order_number']} by " . e($user['name']), 
        'system', 
        null, 
        url('admin/orders/returns.php')
    );

    jsonResponse(true, 'Your return request has been submitted successfully. Our team will review it soon.');
} catch (Exception $e) {
    jsonResponse(false, 'Failed to submit request: ' . $e->getMessage());
}
