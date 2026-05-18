<?php
/**
 * Razorpay Payment Callback Handler
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('index.php'));
}

// Ignore CSRF for gateway callbacks if needed, but our JS checkout uses a local form with CSRF.
validateCsrf();

$paymentId   = inputStr('razorpay_payment_id', '', 'POST');
$orderId     = inputStr('razorpay_order_id', '', 'POST');
$signature   = inputStr('razorpay_signature', '', 'POST');
$orderNumber = inputStr('order_number', '', 'POST');

if (!$paymentId || !$orderId || !$signature || !$orderNumber) {
    setFlash('error', 'Invalid payment response.');
    redirect(url('my-orders.php'));
}

$rzpSecret = getSetting('razorpay_secret');

// Verify Signature
// HMAC hex digest of order_id + "|" + payment_id using secret
$generatedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $rzpSecret);

if (hash_equals($generatedSignature, $signature)) {
    // Payment is valid
    try {
        $stmt = $db->prepare("
            UPDATE orders 
            SET payment_status = 'paid', payment_id = ? 
            WHERE order_number = ? AND payment_status = 'pending'
        ");
        $stmt->execute([$paymentId, $orderNumber]);
        
        // Send confirmation email after payment success
        sendOrderConfirmationEmail($orderNumber);
        
        // Log transaction or anything else
        
        redirect(url('order-success.php?order=' . $orderNumber));
    } catch (PDOException $e) {
        setFlash('error', 'Database error updating payment status: ' . $e->getMessage());
        redirect(url('my-orders.php'));
    }
} else {
    // Payment failed or invalid signature
    try {
        $stmt = $db->prepare("
            UPDATE orders 
            SET payment_status = 'failed', payment_id = ? 
            WHERE order_number = ?
        ");
        $stmt->execute([$paymentId, $orderNumber]);
    } catch (PDOException $e) {}
    
    setFlash('error', 'Payment verification failed. Please contact support if amount was deducted.');
    redirect(url('my-orders.php'));
}
