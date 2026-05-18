<?php
/**
 * Admin Abandoned Cart Management
 */
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$db = getDB();

// Fetch Abandoned Carts
$stmt = $db->query("
    SELECT ac.*, u.name as customer_name, u.email as customer_email
    FROM abandoned_carts ac
    LEFT JOIN users u ON ac.user_id = u.id
    ORDER BY ac.last_active DESC
");
$carts = $stmt->fetchAll();

$pageTitle = 'Abandoned Cart Recovery';
require_once __DIR__ . '/../../admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-800 text-dark mb-1">Abandoned <span class="text-primary">Carts</span></h3>
        <p class="text-secondary small fw-500 mb-0">Track and recover lost sales from users who left items in their cart.</p>
    </div>
</div>

<div class="admin-card overflow-hidden shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-secondary small fw-800 text-uppercase ls-1">
                <tr>
                    <th class="py-3 px-4">Customer / Session</th>
                    <th class="py-3 px-3">Cart Details</th>
                    <th class="py-3 px-3">Value</th>
                    <th class="py-3 px-3">Last Active</th>
                    <th class="py-3 px-3">Status</th>
                    <th class="py-3 px-4 text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($carts)): ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted">No abandoned carts found yet.</td></tr>
                <?php endif; ?>
                <?php foreach($carts as $c): 
                    $items = json_decode($c['cart_data'], true) ?: [];
                    $itemCount = array_sum(array_column($items, 'quantity'));
                ?>
                    <tr>
                        <td class="px-4 py-3">
                            <?php if($c['customer_name']): ?>
                                <div class="fw-700 text-dark"><?= e($c['customer_name']) ?></div>
                                <div class="small fw-600 text-primary"><?= e($c['customer_email']) ?></div>
                            <?php else: ?>
                                <div class="fw-700 text-secondary">Guest Session</div>
                                <div class="small fw-500 text-muted ls-0"><?= substr($c['session_id'], 0, 12) ?>...</div>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-3">
                            <div class="small fw-700 text-dark"><?= $itemCount ?> Items</div>
                            <div class="dropdown">
                                <button class="btn btn-link btn-sm p-0 fs-8 text-secondary text-decoration-none dropdown-toggle ls-0" data-bs-toggle="dropdown">View Items</button>
                                <div class="dropdown-menu p-3 shadow border-0" style="min-width: 250px;">
                                    <?php foreach($items as $it): ?>
                                        <div class="d-flex gap-2 mb-2 pb-2 border-bottom border-light align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="fw-700 text-dark small ls-0 lh-sm"><?= e($it['product_name'] ?? $it['name']) ?></div>
                                                <div class="text-muted fs-9"><?= $it['quantity'] ?> x <?= formatPrice($it['price']) ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-3 fw-800 text-dark"><?= formatPrice($c['total_value']) ?></td>
                        <td class="px-3 py-3">
                            <div class="small fw-600 text-dark"><?= date('d M, h:i A', strtotime($c['last_active'])) ?></div>
                            <div class="fs-9 text-muted"><?= timeAgo($c['last_active']) ?></div>
                        </td>
                        <td class="px-3 py-3">
                            <?php if($c['is_recovered']): ?>
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2"><i class="fa-solid fa-check-circle me-1"></i> Recovered</span>
                            <?php else: ?>
                                <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-2"><i class="fa-solid fa-clock me-1"></i> Abandoned</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-end">
                            <?php if($c['customer_email'] && !$c['is_recovered']): ?>
                                <button class="btn btn-primary btn-sm rounded-pill px-3 fw-700 send-recovery-btn" 
                                        data-cart-id="<?= $c['id'] ?>" 
                                        data-email="<?= e($c['customer_email']) ?>">
                                    <i class="fa-solid fa-paper-plane me-1"></i> Send Reminder
                                </button>
                            <?php else: ?>
                                <button class="btn btn-light btn-sm rounded-pill px-3 fw-700 border" disabled>
                                    N/A
                                </button>
                            <?php endif; ?>
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
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.send-recovery-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.cartId;
            const originalHtml = this.innerHTML;
            
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';
            
            const res = await postAjax(BASE_URL + 'admin/marketing/send-abandoned-email.php', { cart_id: id });
            
            this.disabled = false;
            this.innerHTML = originalHtml;
            
            if (res.success) {
                showToast('success', res.message);
                this.classList.replace('btn-primary', 'btn-success');
                this.innerHTML = '<i class="fa-solid fa-check"></i> Sent';
            } else {
                showToast('error', res.message);
            }
        });
    });
});
</script>
JS;
require_once __DIR__ . '/../../admin/includes/header.php'; // Included again for footer scripts properly in some structure, but usually just footer.
require_once __DIR__ . '/../../admin/includes/footer.php'; 
?>
