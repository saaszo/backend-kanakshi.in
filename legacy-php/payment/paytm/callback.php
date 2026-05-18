<?php
/**
 * Paytm Simulator Callback
 * 
 * NOTE: For full integration, verify checksum here using PaytmChecksum::verifySignature()
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('index.php'));
}

validateCsrf();

$orderNo  = inputStr('ORDERID', '', 'POST');
$txnId    = inputStr('TXNID', '', 'POST');
$status   = inputStr('STATUS', '', 'POST');
$checksum = inputStr('CHECKSUMHASH', '', 'POST');

if (!$orderNo || !$txnId) {
    setFlash('error', 'Invalid Paytm Response.');
    redirect(url('my-orders.php'));
}

// In a real integration, we would verify $checksum against $merchantKey here
$isValid = ($checksum === 'dummy_hash_for_demo'); 

if ($isValid && $status === 'TXN_SUCCESS') {
    try {
        $stmt = $db->prepare("
            UPDATE orders 
            SET payment_status = 'paid', payment_id = ? 
            WHERE order_number = ? AND payment_status = 'pending'
        ");
        $stmt->execute([$txnId, $orderNo]);
        
        // Send confirmation email after payment success
        sendOrderConfirmationEmail($orderNo);
        
        setFlash('success', 'Payment successful (Mock Paytm)!');
        redirect(url('order-success.php?order=' . $orderNo));
    } catch (PDOException $e) {
        setFlash('error', 'Database error updating payment.');
        redirect(url('my-orders.php'));
    }
} else {
    // Failed or Invalid
    try {
        $stmt = $db->prepare("
            UPDATE orders 
            SET payment_status = 'failed', payment_id = ? 
            WHERE order_number = ?
        ");
        $stmt->execute([$txnId, $orderNo]);
    } catch (PDOException $e) {}
    
    setFlash('error', 'Payment failed or was cancelled.');
    redirect(url('my-orders.php'));
}
