<?php
/**
 * Paytm Simulator (Initiation)
 * 
 * NOTE: For full Paytm integration, a complex AES checksum implementation is required 
 * which usually relies on a Paytm provided SDK (PaytmChecksum.php).
 * For this demo environment, this script simulates a Paytm checkout screen.
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

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

// Check keys
$merchantId = getSetting('paytm_merchant_id');
$merchantKey = getSetting('paytm_merchant_key');

if (empty($merchantId) || empty($merchantKey)) {
    // Missing credentials, warn but proceed with demo
    $warning = "Paytm is not fully configured (Missing API Keys). Continuing in Simulator mode.";
} else {
    $warning = "Entering Paytm Simulator environment.";
}

$pageTitle = 'Paytm Simulator';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white p-4 text-center border-bottom-0">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/2/24/Paytm_Logo_%28standalone%29.svg" alt="Paytm" height="30" class="mb-3">
                    <h5 class="fw-800 text-dark mb-1">Demo Environment</h5>
                    <p class="text-secondary small mb-0"><?= $warning ?></p>
                </div>
                
                <div class="card-body p-4 bg-light">
                    <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-secondary fw-600">Order Ref:</span>
                        <span class="fw-bold fs-6 font-monospace"><?= e($orderNo) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="text-secondary fw-600">Amount Payable:</span>
                        <span class="fw-800 fs-4 text-primary"><?= formatPrice($order['total_amount']) ?></span>
                    </div>
                    
                    <form action="<?= url('payment/paytm/callback.php') ?>" method="POST" class="d-flex gap-2">
                        <?= csrfField() ?>
                        <!-- Simulate POST data similar to what Paytm returns -->
                        <input type="hidden" name="ORDERID" value="<?= e($orderNo) ?>">
                        <input type="hidden" name="TXNID" value="PTM<?= time() . rand(100, 999) ?>">
                        <input type="hidden" name="CHECKSUMHASH" value="dummy_hash_for_demo">
                        
                        <button type="submit" name="STATUS" value="TXN_SUCCESS" class="btn btn-success flex-grow-1 fw-800 rounded-pill py-3">
                            <i class="fa-solid fa-check me-2"></i> Simulate Success
                        </button>
                        <button type="submit" name="STATUS" value="TXN_FAILURE" class="btn btn-outline-danger flex-grow-1 fw-800 rounded-pill py-3">
                            <i class="fa-solid fa-xmark me-2"></i> Fail Payment
                        </button>
                    </form>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
