<?php
/**
 * Order Success Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

// Require login to view order success
requireLogin();

$orderNo = inputStr('order', '', 'GET');
if (!$orderNo) {
    redirect(url('my-account.php'));
}

$db = getDB();
$user = currentUser();

// Fetch Order Details
$stmt = $db->prepare("
    SELECT *, COALESCE(total_amount, total) AS total_amount
    FROM orders 
    WHERE order_number = ? AND user_id = ?
");
$stmt->execute([$orderNo, $user['id']]);
$order = $stmt->fetch();

if (!$order) {
    setFlash('error', 'Order not found or access denied.');
    redirect(url('my-account.php'));
}

// Fetch Items
$stmtItems = $db->prepare("
    SELECT *,
           name AS product_name,
           COALESCE(line_total, price * quantity) AS line_total,
           COALESCE(NULLIF(variant_details, ''), TRIM(CONCAT(COALESCE(size, ''), ' ', COALESCE(color, '')))) AS variant_details
    FROM order_items
    WHERE order_id = ?
");
$stmtItems->execute([$order['id']]);
$items = $stmtItems->fetchAll();

// Clear session coupon just in case
unset($_SESSION['coupon']);

$pageTitle = 'Order Placed Successfully';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    
    <!-- Checkout Progress -->
    <div class="checkout-steps mb-5 mx-auto" style="max-width: 600px;">
        <div class="step-item done"><i class="fa-solid fa-cart-shopping mb-1 d-block h5"></i> 1. Cart</div>
        <div class="step-item done"><i class="fa-regular fa-address-card mb-1 d-block h5"></i> 2. Checkout</div>
        <div class="step-item active"><i class="fa-regular fa-circle-check mb-1 d-block h5"></i> 3. Confirm</div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="bg-white rounded-xl shadow border border-light p-4 p-md-5 text-center mb-5">
                <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle mb-4 shadow" style="width: 80px; height: 80px;">
                    <i class="fa-solid fa-check fa-3x"></i>
                </div>
                
                <h2 class="fw-800 text-dark mb-2 mt-2">Thank you for your order!</h2>
                <p class="text-secondary fs-6 mb-4">Your order has been placed successfully. We will send you an email confirmation shortly.</p>
                
                <div class="bg-light rounded p-4 mb-4 d-inline-block text-start border border-secondary border-opacity-25" style="min-width: 300px;">
                    <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-muted fw-600">Order Number:</span>
                        <strong class="text-dark fs-5"><?= e($order['order_number']) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted fw-600">Date:</span>
                        <span class="text-dark fw-600"><?= date('d M, Y', strtotime($order['created_at'])) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted fw-600">Total Amount:</span>
                        <strong class="text-primary fs-5"><?= formatPrice($order['total_amount']) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted fw-600">Payment Method:</span>
                        <span class="text-dark fw-600 text-uppercase"><?= e($order['payment_method']) ?></span>
                    </div>
                </div>
                
                <p class="small text-secondary fw-500 mb-5">
                    For any queries regarding your order, please contact us at <a href="mailto:<?= e(getSetting('site_email')) ?>" class="fw-600 text-primary text-decoration-none"><?= e(getSetting('site_email')) ?></a>
                </p>
                
                <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                    <a href="<?= url('my-orders.php') ?>" class="btn btn-outline-primary btn-lg fw-700 rounded-pill px-4">
                        <i class="fa-regular fa-file-lines me-2"></i> View Order Details
                    </a>
                    <a href="<?= url('products.php') ?>" class="btn btn-primary btn-lg fw-700 rounded-pill px-4">
                        Continue Shopping <i class="fa-solid fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
            
            <!-- Order Snapshot -->
            <div class="card border-light shadow-sm mb-4 rounded-xl overflow-hidden">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-800 text-dark">Items Ordered</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php foreach($items as $item): ?>
                            <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 fw-700 text-dark"><?= e($item['product_name']) ?></h6>
                                    <?php if ($item['variant_details']): ?>
                                        <small class="text-secondary fw-600 d-block mb-1 border rounded bg-light px-2 py-1 d-inline-block"><?= e($item['variant_details']) ?></small>
                                    <?php endif; ?>
                                    <div class="text-muted small fw-600 mt-1">Qty: <?= $item['quantity'] ?> &times; <?= formatPrice($item['price']) ?></div>
                                </div>
                                <div class="fw-800 text-dark">
                                    <?= formatPrice($item['line_total']) ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php 
// Clear Cart items from UI manually just in case session wasn't fully cleared yet due to timings
$extraJs = <<<JS
<script>
    // Reset cart badge to 0
    const badge = document.getElementById('cartCount');
    if (badge) {
        badge.textContent = '0';
        badge.classList.add('d-none');
    }
</script>
JS;
require_once __DIR__ . '/includes/footer.php'; 
?>
