<?php
/**
 * Razorpay Payment Initiation
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

// Get Razorpay Keys
$rzpKey = getSetting('razorpay_key');
$rzpSecret = getSetting('razorpay_secret');

if (empty($rzpKey) || empty($rzpSecret)) {
    // Fallback if keys are not set
    setFlash('error', 'Razorpay is not configured. Switching to Cash on Delivery.');
    $db->prepare("UPDATE orders SET payment_method = 'cod' WHERE id = ?")->execute([$order['id']]);
    redirect(url('order-success.php?order=' . $orderNo));
}

// 1. Create Razorpay Order via API
$amountPaise = round($order['total_amount'] * 100);
$ch = curl_init('https://api.razorpay.com/v1/orders');
curl_setopt($ch, CURLOPT_USERPWD, $rzpKey . ':' . $rzpSecret);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'amount'   => $amountPaise,
    'currency' => 'INR',
    'receipt'  => $orderNo
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$rzpData = json_decode($response, true);

if ($httpStatus !== 200 || !isset($rzpData['id'])) {
    setFlash('error', 'Failed to initialize payment gateway. Please try again later.');
    redirect(url('my-orders.php'));
}

$rzpOrderId = $rzpData['id'];

// Save Razorpay Order ID to our local order notes momentarily or process JS directly
// Razorpay Standard Checkout
$siteName = getSetting('site_name', 'MyShop');
$user = currentUser();

$pageTitle = 'Processing Payment...';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5 text-center" style="min-height: 50vh;">
    <div class="spinner-border text-primary mb-4" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Loading...</span>
    </div>
    <h3 class="fw-800 text-dark">Redirecting to Secure Payment...</h3>
    <p class="text-secondary">Please do not refresh this page or press back.</p>

    <form id="rzpForm" action="<?= url('payment/razorpay/callback.php') ?>" method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="order_number" value="<?= e($orderNo) ?>">
        <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
        <input type="hidden" name="razorpay_signature" id="razorpay_signature">
        <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
    </form>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
var options = {
    "key": "<?= $rzpKey ?>",
    "amount": "<?= $amountPaise ?>",
    "currency": "INR",
    "name": "<?= e($siteName) ?>",
    "description": "Order #<?= e($orderNo) ?>",
    "image": "<?= url(getSetting('site_logo', 'uploads/logo_default.svg')) ?>",
    "order_id": "<?= $rzpOrderId ?>",
    "handler": function (response){
        // Success
        document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
        document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
        document.getElementById('razorpay_signature').value = response.razorpay_signature;
        document.getElementById('rzpForm').submit();
    },
    "prefill": {
        "name": "<?= e($user['name']) ?>",
        "email": "<?= e($user['email']) ?>",
        "contact": "<?= e($order['ship_phone'] ?: $user['phone']) ?>"
    },
    "theme": {
        "color": "#0d6efd"
    },
    "modal": {
        "ondismiss": function(){
            window.location.href = "<?= url('my-orders.php') ?>";
        }
    }
};

var rzp1 = new Razorpay(options);
rzp1.on('payment.failed', function (response){
    alert("Payment Failed. Reason: " + response.error.description);
    window.location.href = "<?= url('my-orders.php') ?>";
});

window.onload = function() {
    rzp1.open();
};
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
