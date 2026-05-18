<?php
/**
 * Customer Order History Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

// Force Login
if (!isLoggedIn()) {
    setFlash('info', 'Please login to view your order history.');
    redirect(url('login.php?redirect=my-orders.php'));
}

$db = getDB();
$userId = currentUserId();

// Fetch Orders
$stmt = $db->prepare("SELECT *, COALESCE(total_amount, total) AS total_amount FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

$pageTitle = 'My Orders';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5 mt-4" style="min-height: 60vh;">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-800 text-dark mb-0">My <span class="text-primary">Orders</span></h2>
                <a href="<?= url('products.php') ?>" class="btn btn-outline-primary btn-sm fw-600 rounded-pill px-3">
                    <i class="fa-solid fa-plus me-1"></i> New Order
                </a>
            </div>

            <?php if (empty($orders)): ?>
                <div class="admin-card text-center p-5 shadow-sm">
                    <div class="mb-4">
                        <i class="fa-solid fa-bag-shopping fa-5x text-light"></i>
                    </div>
                    <h4 class="fw-700 text-dark">No orders yet</h4>
                    <p class="text-secondary mb-4">You haven't placed any orders with us yet. Start shopping our latest collection!</p>
                    <a href="<?= url('products.php') ?>" class="btn btn-primary fw-800 rounded-pill px-5 py-3 ls-1 text-uppercase">
                        Explore Collection
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle border shadow-sm rounded-3 overflow-hidden" style="background: #fff;">
                        <thead class="table-light text-secondary small text-uppercase ls-1">
                            <tr>
                                <th class="py-3 px-4">Order #</th>
                                <th class="py-3 px-3">Date</th>
                                <th class="py-3 px-3">Status</th>
                                <th class="py-3 px-3">Amount</th>
                                <th class="py-3 px-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $o): ?>
                                <tr class="border-bottom">
                                    <td class="px-4 py-4 fw-800 text-dark">#<?= e($o['order_number']) ?></td>
                                    <td class="px-3 py-4 text-secondary small fw-500">
                                        <?= date('d M, Y', strtotime($o['created_at'])) ?>
                                    </td>
                                    <td class="px-3 py-4">
                                        <?php
                                            $badge = 'bg-secondary';
                                            if($o['status'] == 'pending') $badge='bg-warning text-dark';
                                            if($o['status'] == 'shipped') $badge='bg-primary';
                                            if($o['status'] == 'delivered') $badge='bg-success';
                                            if($o['status'] == 'cancelled') $badge='bg-danger';
                                        ?>
                                        <span class="badge rounded-pill <?= $badge ?> px-3 py-2 fw-700 text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                                            <?= $o['status'] ?>
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 fw-800 text-dark"><?= formatPrice($o['total_amount']) ?></td>
                                    <td class="px-4 py-4 text-end">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <a href="<?= url('order-details.php?id=' . $o['order_number']) ?>" class="btn btn-light border btn-sm fw-700 px-3 rounded-pill" title="View Details">
                                                Details
                                            </a>
                                            <a href="<?= url('invoice.php?id=' . $o['order_number']) ?>" target="_blank" class="btn btn-outline-dark btn-sm fw-700 px-3 rounded-pill" title="Download Invoice">
                                                <i class="fa-solid fa-file-invoice"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
