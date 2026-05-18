<?php
/**
 * Guest + Customer Order Tracking
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Track Order';
$metaDesc = 'Track your order status with order number and email.';
$db = getDB();

$orderNumber = trim((string)($_REQUEST['order_number'] ?? $_GET['order'] ?? ''));
$email = trim((string)($_REQUEST['email'] ?? (currentUser()['email'] ?? '')));
$trackingOrder = null;
$trackingEvents = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $orderNumber !== '') {
    if ($orderNumber !== '') {
        if (isLoggedIn()) {
            $stmt = $db->prepare("
                SELECT *,
                       COALESCE(total_amount, total) AS total_amount,
                       COALESCE(shipping, shipping_cost) AS shipping
                FROM orders
                WHERE order_number = ? AND (user_id = ? OR ship_email = ?)
                LIMIT 1
            ");
            $stmt->execute([$orderNumber, currentUserId(), currentUser()['email'] ?? $email]);
        } else {
            $stmt = $db->prepare("
                SELECT *,
                       COALESCE(total_amount, total) AS total_amount,
                       COALESCE(shipping, shipping_cost) AS shipping
                FROM orders
                WHERE order_number = ? AND ship_email = ?
                LIMIT 1
            ");
            $stmt->execute([$orderNumber, $email]);
        }

        $trackingOrder = $stmt->fetch() ?: null;

        if ($trackingOrder) {
            $stmtTrack = $db->prepare("
                SELECT tracking_number, courier_name, status, location, message, updated_at
                FROM order_tracking
                WHERE order_id = ?
                ORDER BY updated_at DESC, id DESC
            ");
            $stmtTrack->execute([$trackingOrder['id']]);
            $trackingEvents = $stmtTrack->fetchAll();

            if (!$trackingEvents) {
                $trackingEvents[] = [
                    'tracking_number' => $trackingOrder['tracking_number'] ?? '',
                    'courier_name' => 'Store Update',
                    'status' => ucfirst($trackingOrder['status']),
                    'location' => $trackingOrder['ship_city'] . (!empty($trackingOrder['ship_state']) ? ', ' . $trackingOrder['ship_state'] : ''),
                    'message' => 'Your order is currently marked as ' . $trackingOrder['status'] . '.',
                    'updated_at' => $trackingOrder['updated_at'] ?: $trackingOrder['created_at']
                ];
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            setFlash('error', 'We could not find an order with those details.');
        }
    }
}

$statusSequence = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
$currentStatusIndex = $trackingOrder ? array_search($trackingOrder['status'], $statusSequence, true) : false;

require_once __DIR__ . '/includes/header.php';
?>

<section class="py-5" style="background:linear-gradient(135deg,#f8f4ec 0%,#fff 55%,#eee2d3 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="text-center mb-5">
                    <span class="section-tag reveal">Track Order</span>
                    <h1 class="reveal" style="font-style:normal;">Stay Updated On Every Fulfillment Step</h1>
                    <p class="text-secondary reveal">Customers can track with their order number. Guests should also enter the email used at checkout.</p>
                </div>

                <div class="bg-white rounded-4 shadow-sm border p-4 p-md-5 mb-4 reveal">
                    <form method="POST" class="row g-3 align-items-end">
                        <?= csrfField() ?>
                        <div class="col-md-5">
                            <label class="form-label fw-700">Order Number</label>
                            <input type="text" name="order_number" class="form-control" value="<?= e($orderNumber) ?>" placeholder="Example: ORD123456" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-700">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?= e($email) ?>" placeholder="Email used at checkout" <?= isLoggedIn() ? 'readonly' : 'required' ?>>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn-lux-primary w-100">Track</button>
                        </div>
                    </form>
                </div>

                <?php if ($trackingOrder): ?>
                    <div class="row g-4">
                        <div class="col-lg-5 reveal">
                            <div class="bg-white rounded-4 shadow-sm border p-4 h-100">
                                <div class="d-flex justify-content-between align-items-start mb-4 gap-3">
                                    <div>
                                        <div class="small text-uppercase ls-2 text-muted fw-700">Order</div>
                                        <h2 class="fs-3 mb-0" style="font-style:normal;">#<?= e($trackingOrder['order_number']) ?></h2>
                                    </div>
                                    <span class="badge rounded-pill px-3 py-2 <?= $trackingOrder['status'] === 'delivered' ? 'bg-success' : ($trackingOrder['status'] === 'shipped' ? 'bg-primary' : 'bg-dark') ?>">
                                        <?= e(ucfirst($trackingOrder['status'])) ?>
                                    </span>
                                </div>

                                <div class="d-flex flex-column gap-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-secondary">Placed On</span>
                                        <strong><?= date('d M Y, h:i A', strtotime($trackingOrder['created_at'])) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-secondary">Customer</span>
                                        <strong><?= e($trackingOrder['ship_name']) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-secondary">Total</span>
                                        <strong><?= formatPrice((float)$trackingOrder['total_amount']) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-secondary">Payment</span>
                                        <strong><?= e(strtoupper($trackingOrder['payment_status'])) ?></strong>
                                    </div>
                                    <?php if (!empty($trackingOrder['tracking_number'])): ?>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-secondary">Tracking No.</span>
                                            <strong><?= e($trackingOrder['tracking_number']) ?></strong>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="mt-4 pt-4 border-top">
                                    <div class="small text-uppercase ls-2 text-muted fw-700 mb-3">Delivery Address</div>
                                    <div class="text-dark fw-600">
                                        <?= nl2br(e($trackingOrder['ship_address'])) ?><br>
                                        <?= e($trackingOrder['ship_city']) ?>, <?= e($trackingOrder['ship_state']) ?> - <?= e($trackingOrder['ship_pincode']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7 reveal">
                            <div class="bg-white rounded-4 shadow-sm border p-4 h-100">
                                <div class="small text-uppercase ls-2 text-muted fw-700 mb-4">Progress</div>
                                <div class="d-flex justify-content-between gap-2 flex-wrap mb-4">
                                    <?php foreach ($statusSequence as $index => $status): ?>
                                        <?php $active = $currentStatusIndex !== false && $index <= $currentStatusIndex; ?>
                                        <div class="text-center flex-fill" style="min-width:90px;">
                                            <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;background:<?= $active ? '#7a0f0f' : '#f3ede5' ?>;color:<?= $active ? '#fff' : '#8a6245' ?>;">
                                                <i class="fa-solid <?= $status === 'pending' ? 'fa-clock' : ($status === 'confirmed' ? 'fa-badge-check' : ($status === 'processing' ? 'fa-box-open' : ($status === 'shipped' ? 'fa-truck-fast' : 'fa-house-circle-check'))) ?>"></i>
                                            </div>
                                            <div class="small fw-700 text-uppercase"><?= e($status) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="small text-uppercase ls-2 text-muted fw-700 mb-3">Latest Updates</div>
                                <div class="d-flex flex-column gap-3">
                                    <?php foreach ($trackingEvents as $event): ?>
                                        <div class="rounded-4 border p-3">
                                            <div class="d-flex justify-content-between align-items-start gap-3">
                                                <div>
                                                    <div class="fw-800 text-dark"><?= e($event['status']) ?></div>
                                                    <?php if (!empty($event['message'])): ?>
                                                        <div class="text-secondary small mt-1"><?= e($event['message']) ?></div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($event['location'])): ?>
                                                        <div class="small mt-2"><i class="fa-solid fa-location-dot me-1 text-primary"></i><?= e($event['location']) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-end small text-muted">
                                                    <div><?= date('d M Y', strtotime($event['updated_at'])) ?></div>
                                                    <div><?= date('h:i A', strtotime($event['updated_at'])) ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="d-flex flex-wrap gap-3 mt-4">
                                    <a href="<?= url('contact-us.php') ?>" class="btn-lux-outline">Need Help?</a>
                                    <?php if (isLoggedIn()): ?>
                                        <a href="<?= url('my-orders.php') ?>" class="btn btn-outline-dark rounded-pill fw-700 px-4">My Orders</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
