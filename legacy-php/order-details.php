<?php
/**
 * Customer Order Details Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

$orderNumber = inputStr('id', '', 'GET');

if (!isLoggedIn()) {
    setFlash('info', 'Please login to view order details.');
    redirect(url('login.php?redirect=order-details.php?id='.$orderNumber));
}

$db = getDB();
$userId = currentUserId();

// Fetch Order
$stmt = $db->prepare("
    SELECT *,
           COALESCE(total_amount, total) AS total_amount,
           COALESCE(shipping, shipping_cost) AS shipping
    FROM orders
    WHERE order_number = ? AND user_id = ?
");
$stmt->execute([$orderNumber, $userId]);
$order = $stmt->fetch();

if (!$order) {
    setFlash('error', 'Order not found.');
    redirect(url('my-orders.php'));
}

// Fetch Items
$stmtItems = $db->prepare("
    SELECT oi.*, p.slug as product_slug,
           COALESCE(oi.line_total, oi.price * oi.quantity) AS line_total,
           COALESCE(NULLIF(oi.variant_details, ''), TRIM(CONCAT(COALESCE(oi.size, ''), ' ', COALESCE(oi.color, '')))) AS variant_details
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmtItems->execute([$order['id']]);
$items = $stmtItems->fetchAll();

// Progress Calculation
$statusSteps = ['pending' => 1, 'confirmed' => 2, 'processing' => 3, 'shipped' => 4, 'delivered' => 5];
$currentStep = $statusSteps[$order['status']] ?? 1;
if ($order['status'] === 'cancelled' || $order['status'] === 'refunded') $currentStep = 0;

$pageTitle = 'Order Details #' . $orderNumber;
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5 mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <h3 class="fw-800 text-dark mb-0">Order <span class="text-primary">#<?= e($orderNumber) ?></span></h3>
                <div class="d-flex gap-2">
                    <a href="<?= url('invoice.php?id=' . $orderNumber) ?>" target="_blank" class="btn btn-outline-dark btn-sm fw-700 px-4 rounded-pill shadow-sm">
                        <i class="fa-solid fa-file-invoice me-2"></i> Invoice
                    </a>
                    <?php if ($order['status'] === 'delivered'): ?>
                        <a href="<?= url('return-request.php?id=' . $orderNumber) ?>" class="btn btn-danger btn-sm fw-700 px-4 rounded-pill shadow-sm">
                            <i class="fa-solid fa-rotate-left me-2"></i> Return / Refund
                        </a>
                    <?php endif; ?>
                    <a href="<?= url('my-orders.php') ?>" class="btn btn-light border btn-sm fw-700 px-4 rounded-pill shadow-sm">
                        <i class="fa-solid fa-arrow-left me-2"></i> Back
                    </a>
                </div>
            </div>

            <!-- Progress Bar -->
            <?php if ($currentStep > 0): ?>
                <div class="admin-card p-4 mb-4 shadow-sm border-0 bg-light-subtle">
                    <h6 class="text-secondary small fw-700 text-uppercase ls-1 mb-4">Delivery Progress</h6>
                    <div class="row text-center position-relative g-0">
                        <!-- Progress Line -->
                        <div class="position-absolute top-50 start-0 w-100 bg-secondary opacity-25" style="height: 2px; transform: translateY(-50%); z-index: 1;"></div>
                        <div class="position-absolute top-50 start-0 bg-primary" style="height: 4px; transform: translateY(-50%); z-index: 2; width: <?= ($currentStep - 1) * 25 ?>%; transition: width 0.5s ease;"></div>
                        
                        <!-- Steps -->
                        <div class="col position-relative" style="z-index: 3;">
                            <div class="step-circle mx-auto <?= $currentStep >= 1 ? 'active' : '' ?>">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div class="small fw-700 mt-2 <?= $currentStep >= 1 ? 'text-primary' : 'text-muted' ?>">Placed</div>
                        </div>
                        <div class="col position-relative" style="z-index: 3;">
                            <div class="step-circle mx-auto <?= $currentStep >= 2 ? 'active' : '' ?>">
                                <i class="fa-solid fa-check-circle"></i>
                            </div>
                            <div class="small fw-700 mt-2 <?= $currentStep >= 2 ? 'text-primary' : 'text-muted' ?>">Confirmed</div>
                        </div>
                        <div class="col position-relative" style="z-index: 3;">
                            <div class="step-circle mx-auto <?= $currentStep >= 3 ? 'active' : '' ?>">
                                <i class="fa-solid fa-arrows-spin"></i>
                            </div>
                            <div class="small fw-700 mt-2 <?= $currentStep >= 3 ? 'text-primary' : 'text-muted' ?>">Processing</div>
                        </div>
                        <div class="col position-relative" style="z-index: 3;">
                            <div class="step-circle mx-auto <?= $currentStep >= 4 ? 'active' : '' ?>">
                                <i class="fa-solid fa-truck-fast"></i>
                            </div>
                            <div class="small fw-700 mt-2 <?= $currentStep >= 4 ? 'text-primary' : 'text-muted' ?>">Shipped</div>
                        </div>
                        <div class="col position-relative" style="z-index: 3;">
                            <div class="step-circle mx-auto <?= $currentStep >= 5 ? 'active' : '' ?>">
                                <i class="fa-solid fa-house-circle-check"></i>
                            </div>
                            <div class="small fw-700 mt-2 <?= $currentStep >= 5 ? 'text-primary' : 'text-muted' ?>">Delivered</div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-danger fw-700 p-4 rounded-4 shadow-sm">
                    <i class="fa-solid fa-circle-exclamation me-2"></i> This order has been <?= $order['status'] ?>.
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Left: Order Summary & Items -->
                <div class="col-lg-7">
                    <div class="admin-card overflow-hidden shadow-sm border-0">
                        <div class="p-3 border-bottom bg-light">
                            <h6 class="fw-800 text-dark text-uppercase ls-1 fs-8 mb-0">Ordered Items</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle">
                                <thead>
                                    <tr class="table-light text-secondary fs-8">
                                        <th class="py-3 px-4">Item</th>
                                        <th class="py-3 px-3 text-center">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($items as $item): ?>
                                        <tr class="border-bottom">
                                            <td class="px-4 py-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="bg-light rounded border-1 overflow-hidden" style="width:60px; height:60px;">
                                                        <img src="<?= url($item['image']) ?>" style="width:100%; height:100%; object-fit:cover;">
                                                    </div>
                                                    <div>
                                                        <div class="fw-700 text-dark"><?= e($item['name']) ?></div>
                                                        <div class="text-secondary small fw-600"><?= e($item['variant_details'] ?: 'Standard') ?> × <?= $item['quantity'] ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-3 py-4 text-center fw-800 text-dark">
                                                <?= formatPrice($item['line_total']) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 bg-light border-top">
                            <div class="d-flex justify-content-between mb-2 small fw-600">
                                <span class="text-secondary">Subtotal</span>
                                <span class="text-dark"><?= formatPrice($order['subtotal']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 small fw-600">
                                <span class="text-secondary">Shipping</span>
                                <span class="text-success"><?= $order['shipping'] == 0 ? 'FREE' : formatPrice($order['shipping']) ?></span>
                            </div>
                            <hr class="my-3 opacity-25">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-800 text-dark fs-5">Paid Total</span>
                                <span class="fw-800 text-primary fs-3"><?= formatPrice($order['total_amount']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Addresses & Info -->
                <div class="col-lg-5">
                    <div class="admin-card p-4 mb-4 shadow-sm border-0 border-start border-4 border-primary">
                        <h6 class="fw-800 text-muted text-uppercase ls-1 fs-8 border-bottom pb-2 mb-3">Shipping Address</h6>
                        <div class="text-dark fw-700 fs-5 mb-1"><?= e($order['ship_name']) ?></div>
                        <div class="text-dark lh-base small fw-500">
                            <?= nl2br(e($order['ship_address'])) ?><br>
                            <?= e($order['ship_city']) ?>, <?= e($order['ship_state']) ?> - <?= e($order['ship_pincode']) ?><br>
                            Ph: <?= e($order['ship_phone']) ?>
                        </div>
                    </div>

                    <?php if ($order['tracking_number']): ?>
                        <div class="admin-card p-4 mb-4 shadow-sm border-0 bg-primary text-white">
                            <h6 class="text-white opacity-75 text-uppercase ls-1 fs-8 border-bottom border-white border-opacity-25 pb-2 mb-3">Tracking Information</h6>
                            <div class="small fw-600 opacity-75">Tracking ID:</div>
                            <div class="fw-800 fs-4 mb-3"><?= e($order['tracking_number']) ?></div>
                            <a href="<?= url('track-order.php?order=' . urlencode($orderNumber)) ?>" class="btn btn-white text-primary fw-800 px-4 rounded-pill w-100 mt-2">
                                <i class="fa-solid fa-location-dot me-2"></i> Track Shipment
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="admin-card p-4 shadow-sm border-0">
                        <h6 class="fw-800 text-muted text-uppercase ls-1 fs-8 border-bottom pb-2 mb-3">Payment Summary</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary small fw-600">Method</span>
                            <span class="text-dark fw-800 text-uppercase"><?= e($order['payment_method']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-0">
                            <span class="text-secondary small fw-600">Status</span>
                            <span class="text-success fw-800 text-uppercase"><?= e($order['payment_status']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
.step-circle {
    width: 45px;
    height: 45px;
    background: #fff;
    border: 2px solid #e2e8f0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    font-size: 1.2rem;
    transition: all 0.3s ease;
}
.step-circle.active {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
