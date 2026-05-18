<?php
/**
 * PhonePe Payment Callback & Status Check
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('index.php'));
}

// Data received from PhonePe (can be base64 response or direct POST fields depending on integration)
$code          = $_POST['code'] ?? '';
$merchantId    = $_POST['merchantId'] ?? '';
$transactionId = $_POST['transactionId'] ?? ''; // This is our order_number

if (!$transactionId || !$merchantId) {
    setFlash('error', 'Invalid PhonePe Response.');
    redirect(url('my-orders.php'));
}

// We always verify with the S2S Status API for security
$savedMerchantId = getSetting('phonepe_merchant_id');
$saltKey         = getSetting('phonepe_salt_key');
$env             = getSetting('phonepe_env', 'UAT');
$saltIndex       = 1;

if ($merchantId !== $savedMerchantId) {
    setFlash('error', 'Security Error: Merchant validation failed.');
    redirect(url('my-orders.php'));
}

// 1. Prepare Status Check API Call
$apiEndpoint  = "/pg/v1/status/{$merchantId}/{$transactionId}";
$stringToHash = $apiEndpoint . $saltKey;
$sha256       = hash('sha256', $stringToHash);
$xVerify      = $sha256 . "###" . $saltIndex;

$url = ($env === 'PROD') 
    ? "https://api.phonepe.com/apis/hermes" . $apiEndpoint
    : "https://api-preprod.phonepe.com/apis/pg-sandbox" . $apiEndpoint;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "X-VERIFY: " . $xVerify,
    "X-MERCHANT-ID: " . $merchantId
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$resData = json_decode($response, true);

// 2. Process Response
if ($httpCode === 200 && isset($resData['success']) && $resData['success'] === true) {
    
    $statusState = $resData['data']['state'] ?? ''; // COMPLETED, FAILED, PENDING
    $phonePeTxnId = $resData['data']['transactionId'] ?? $transactionId; // bank/gateway txn id
    // Sometimes it's providerReferenceId
    $bankTxnId = $resData['data']['paymentInstrument']['utr'] ?? ($resData['data']['providerReferenceId'] ?? $phonePeTxnId);
    
    if ($statusState === 'COMPLETED') {
        try {
            $stmt = $db->prepare("
                UPDATE orders 
                SET payment_status = 'paid', payment_id = ? 
                WHERE order_number = ? AND payment_status = 'pending'
            ");
            $stmt->execute([$bankTxnId, $transactionId]);
            
            // Send confirmation email after payment success
            sendOrderConfirmationEmail($transactionId);
            
            setFlash('success', 'Payment successful!');
            redirect(url('order-success.php?order=' . $transactionId));
        } catch (PDOException $e) {
            setFlash('error', 'Database error updating payment.');
            redirect(url('my-orders.php'));
        }
    } else {
        // Failed or Pending
        try {
            $stmt = $db->prepare("
                UPDATE orders 
                SET payment_status = 'failed', payment_id = ? 
                WHERE order_number = ?
            ");
            $stmt->execute([$bankTxnId, $transactionId]);
        } catch (PDOException $e) {}
        
        setFlash('error', 'Payment failed or was cancelled (Status: '.$statusState.').');
        redirect(url('my-orders.php'));
    }
} else {
    // API verification failed
    setFlash('error', 'Payment Verification Failed: ' . ($resData['message'] ?? 'Unknown API Error'));
    redirect(url('my-orders.php'));
}
