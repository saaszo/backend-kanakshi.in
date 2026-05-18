<?php
/**
 * PhonePe Payment Initiation
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Ensure user is logged in
requireLogin();

$db = getDB();
$orderNo = inputStr('order', '', 'GET');

if (!$orderNo) {
    setFlash('error', 'Invalid order.');
    redirect(url('my-orders.php'));
}

// Fetch Order
$stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ? AND payment_status = 'pending'");
$stmt->execute([$orderNo, currentUser()['id']]);
$order = $stmt->fetch();

if (!$order) {
    setFlash('error', 'Order not found or already paid.');
    redirect(url('my-orders.php'));
}

// Get PhonePe Keys
$merchantId = getSetting('phonepe_merchant_id');
$saltKey    = getSetting('phonepe_salt_key');
$env        = getSetting('phonepe_env', 'UAT');
$saltIndex  = 1; // standard default

if (empty($merchantId) || empty($saltKey)) {
    // Fallback if keys are not set
    setFlash('error', 'PhonePe is not configured. Switching to Cash on Delivery.');
    $db->prepare("UPDATE orders SET payment_method = 'cod' WHERE id = ?")->execute([$order['id']]);
    redirect(url('order-success.php?order=' . $orderNo));
}

// Prepare Payload
$amountPaise = round($order['total_amount'] * 100);
$user = currentUser();

$payloadData = [
    "merchantId" => $merchantId,
    "merchantTransactionId" => $orderNo,
    "merchantUserId" => "U" . $user['id'],
    "amount" => $amountPaise,
    "redirectUrl" => url('payment/phonepe/callback.php'),
    "redirectMode" => "POST",
    "callbackUrl" => url('payment/phonepe/callback.php'),
    "mobileNumber" => $order['ship_phone'] ?: $user['phone'],
    "paymentInstrument" => [
        "type" => "PAY_PAGE"
    ]
];

$payloadJson = json_encode($payloadData);
$base64Payload = base64_encode($payloadJson);

$apiEndpoint = "/pg/v1/pay";
$stringToHash = $base64Payload . $apiEndpoint . $saltKey;
$sha256 = hash('sha256', $stringToHash);
$xVerify = $sha256 . "###" . $saltIndex;

// API Call
$url = $env === 'PROD' 
    ? "https://api.phonepe.com/apis/hermes/pg/v1/pay" 
    : "https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/pay";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['request' => $base64Payload]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "X-VERIFY: " . $xVerify
]);

$response = curl_exec($ch);
$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$resData = json_decode($response, true);

if ($httpStatus === 200 && isset($resData['success']) && $resData['success'] === true) {
    // Redirect to PhonePe page
    $redirectUrl = $resData['data']['instrumentResponse']['redirectInfo']['url'];
    if ($redirectUrl) {
        header("Location: " . $redirectUrl);
        exit;
    }
}

// If fail
setFlash('error', 'PhonePe Gateway Initialization Failed.');
redirect(url('my-orders.php'));
