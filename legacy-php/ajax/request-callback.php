<?php
/**
 * AJAX Handler: Request Callback
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.');
}

$phone = inputStr('phone', '', 'POST');
$product = inputStr('product_name', 'Unknown Product', 'POST');

if (empty($phone) || strlen($phone) < 10) {
    jsonResponse(false, 'Please enter a valid 10-digit mobile number.');
}

try {
    // 1. Create Notification for Admin
    $msg = "📞 Callback Requested: {$phone} is interested in " . e($product);
    $success = createNotification($msg, 'system');

    if ($success) {
        jsonResponse(true, 'Request sent! Our expert will call you soon.');
    } else {
        jsonResponse(false, 'Failed to send request. Please try again or use WhatsApp.');
    }
} catch (Exception $e) {
    jsonResponse(false, 'System Error: ' . $e->getMessage());
}
