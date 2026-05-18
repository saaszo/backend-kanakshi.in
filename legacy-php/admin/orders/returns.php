<?php
/**
 * Admin: Manage Return Requests
 */
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$db = getDB();

// Status Helper
function getReturnStatusBadge($status) {
    switch ($status) {
        case 'pending':   return 'bg-warning text-dark';
        case 'approved':  return 'bg-info text-dark';
        case 'rejected':  return 'bg-danger';
        case 'completed': return 'bg-success';
        default:          return 'bg-secondary';
    }
}

// Fetch Returns with Order Info
$stmt = $db->query("
    SELECT r.*, o.order_number, COALESCE(o.total_amount, o.total) as total_amount, u.name as customer_name, u.email as customer_email
    FROM order_returns r
    JOIN orders o ON r.order_id = o.id
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
");
$returns = $stmt->fetchAll();

$pageTitle = 'Manage Returns';
require_once __DIR__ . '/../../admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-800 text-dark mb-0">Return <span class="text-primary">Requests</span></h3>
        <p class="text-secondary small fw-600 mb-0">Review and process customer refund/return requests.</p>
    </div>
</div>

<div class="admin-card overflow-hidden shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-secondary fs-8 text-uppercase ls-1">
                <tr>
                    <th class="py-3 px-4">Order #</th>
                    <th class="py-3 px-3">Customer</th>
                    <th class="py-3 px-3">Reason</th>
                    <th class="py-3 px-3">Amount</th>
                    <th class="py-3 px-3 text-center">Status</th>
                    <th class="py-3 px-4 text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($returns)): ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted fw-500">No return requests found.</td></tr>
                <?php endif; ?>
                <?php foreach($returns as $r): ?>
                    <tr>
                        <td class="px-4 py-3 fw-800 text-dark">
                            <a href="<?= url('admin/orders/view.php?id='.$r['order_id']) ?>" class="text-decoration-none">#<?= e($r['order_number']) ?></a>
                            <div class="fs-9 text-muted mt-1 fw-500"><?= date('d M Y', strtotime($r['created_at'])) ?></div>
                        </td>
                        <td class="px-3 py-3">
                            <div class="fw-700 text-dark small"><?= e($r['customer_name']) ?></div>
                            <div class="fs-9 text-secondary"><?= e($r['customer_email']) ?></div>
                        </td>
                        <td class="px-3 py-3">
                            <div class="text-secondary small fw-600 lh-sm" style="max-width: 250px;"><?= e($r['reason']) ?></div>
                        </td>
                        <td class="px-3 py-3 fw-800 text-primary"><?= formatPrice($r['total_amount']) ?></td>
                        <td class="px-3 py-3 text-center">
                            <span class="badge rounded-pill px-3 py-2 <?= getReturnStatusBadge($r['status']) ?>">
                                <?= ucfirst($r['status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border fw-700 rounded-pill px-3 dropdown-toggle" data-bs-toggle="dropdown">Manage</button>
                                <ul class="dropdown-menu shadow border-0">
                                    <?php if($r['status'] == 'pending'): ?>
                                        <li><a class="dropdown-item fw-600 text-info handle-return" href="#" data-id="<?= $r['id'] ?>" data-action="approved"><i class="fa-solid fa-check me-2"></i> Approve Request</a></li>
                                        <li><a class="dropdown-item fw-600 text-danger handle-return" href="#" data-id="<?= $r['id'] ?>" data-action="rejected"><i class="fa-solid fa-xmark me-2"></i> Reject Request</a></li>
                                    <?php endif; ?>
                                    
                                    <?php if($r['status'] == 'approved'): ?>
                                        <li><a class="dropdown-item fw-600 text-success handle-return" href="#" data-id="<?= $r['id'] ?>" data-action="completed"><i class="fa-solid fa-circle-check me-2"></i> Mark Completed (Refunded)</a></li>
                                    <?php endif; ?>
                                    
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item fw-600 text-secondary" href="<?= url('admin/orders/view.php?id='.$r['order_id']) ?>"><i class="fa-solid fa-eye me-2"></i> View Full Order</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
$extraAdminJs = <<<JS
<script>
document.querySelectorAll('.handle-return').forEach(btn => {
    btn.addEventListener('click', async function(e) {
        e.preventDefault();
        const id = this.dataset.id;
        const action = this.dataset.action;
        const msg = "Are you sure you want to " + action + " this return request?";
        
        if (!confirm(msg)) return;
        
        const res = await postAjax(BASE_URL + 'admin/ajax/handle-return.php', { id, action });
        
        if (res.success) {
            showToast('success', res.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', res.message);
        }
    });
});
</script>
JS;
require_once __DIR__ . '/../../admin/includes/header.php'; // Included for standard footer scripts/layout
require_once __DIR__ . '/../../admin/includes/footer.php';
?>
