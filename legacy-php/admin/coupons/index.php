<?php
/**
 * Admin Coupon Management
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();

// Handle Delete/Status Toggle via GET for simplicity (though AJAX is better, we will stick to basic for now)
if (isset($_GET['action']) && isset($_GET['id'])) {
    validateCsrf();
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'delete') {
        $db->prepare("DELETE FROM coupons WHERE id = ?")->execute([$id]);
        setFlash('success', 'Coupon deleted successfully.');
    } elseif ($_GET['action'] === 'toggle') {
        $db->prepare("UPDATE coupons SET is_active = 1 - is_active WHERE id = ?")->execute([$id]);
        setFlash('success', 'Coupon status updated.');
    }
    redirect(url('admin/coupons/index.php'));
}

// Fetch Coupons
$stmt = $db->query("SELECT * FROM coupons ORDER BY created_at DESC");
$coupons = $stmt->fetchAll();

$pageTitle = 'Manage Coupons';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-900 text-dark mb-1 ls-1">Boutique Incentives</h3>
        <p class="text-muted small mb-0 fw-700 text-uppercase ls-2">Curating Exclusive Client Privileges</p>
    </div>
    <a href="<?= url('admin/coupons/add.php') ?>" class="btn btn-primary fw-900 rounded-pill px-4 ls-1 fs-8 text-uppercase shadow-gold py-2">
        <i class="fa-solid fa-plus-circle me-2"></i> New Incentive
    </a>
</div>

<div class="admin-card mb-4 shadow-sm border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted fs-9 text-uppercase ls-2">
                <tr>
                    <th class="py-3 px-4">Incentive Code</th>
                    <th class="py-3 px-3 text-center">Benefit Value</th>
                    <th class="py-3 px-3">Lifecycle Usage</th>
                    <th class="py-3 px-3 text-center">Sanctuary Status</th>
                    <th class="py-3 px-4 text-end">Control</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($coupons)): ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted fw-600">No active incentives curate for this boutique yet.</td></tr>
                <?php endif; ?>
                <?php foreach($coupons as $c): ?>
                    <tr class="animate-in">
                        <td class="px-4 py-3">
                            <span class="badge-soft badge-soft-primary fw-900 fs-7 px-3 py-2 rounded-pill font-monospace ls-1 border border-dashed border-primary">
                                <?= e($c['code']) ?>
                            </span>
                            <div class="text-muted small fw-700 mt-1 text-uppercase ls-1" style="font-size: 0.6rem;">Expires: <?= date('d F, Y', strtotime($c['expiry_date'])) ?></div>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <div class="fw-900 text-dark fs-6 ls-1">
                                <?= $c['type'] === 'fixed' ? formatPrice($c['value']) : (float)$c['value'] . '%' ?>
                            </div>
                            <?php if($c['min_order'] > 0): ?>
                                <div class="text-muted small fw-800 text-uppercase ls-1" style="font-size: 0.6rem;">Threshold: <?= formatPrice($c['min_order']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-3">
                            <div class="fw-900 text-dark mb-1 fs-8"><?= $c['used_count'] ?> Redemptions</div>
                            <?php if($c['max_uses']): ?>
                                <div class="progress rounded-pill bg-light border" style="height: 6px; width: 120px;">
                                    <?php $perc = min(100, ($c['used_count'] / $c['max_uses']) * 100); ?>
                                    <div class="progress-bar bg-primary shadow-sm" role="progressbar" style="width: <?= $perc ?>%;"></div>
                                </div>
                                <div class="text-muted fs-10 mt-1 fw-700 text-uppercase ls-1">Cap: <?= $c['max_uses'] ?> Allocations</div>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <?php 
                            $isExpired = strtotime($c['expiry_date']) < time();
                            if($isExpired): ?>
                                <span class="badge-soft badge-soft-danger px-3 py-1 rounded-pill fw-900 fs-10 text-uppercase ls-1">Expired</span>
                            <?php elseif($c['is_active']): ?>
                                <button type="button" onclick="toggleStatus(<?= $c['id'] ?>, 'Deactivate Incentive')" class="btn p-0 border-0 bg-transparent">
                                    <span class="badge-soft badge-soft-success px-3 py-1 rounded-pill fw-900 fs-10 text-uppercase ls-1">Live</span>
                                </button>
                            <?php else: ?>
                                <button type="button" onclick="toggleStatus(<?= $c['id'] ?>, 'Activate Incentive')" class="btn p-0 border-0 bg-transparent">
                                    <span class="badge-soft badge-soft-warning px-3 py-1 rounded-pill fw-900 fs-10 text-uppercase ls-1">Paused</span>
                                </button>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?= url('admin/coupons/edit.php?id='.$c['id']) ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-900 ls-1 fs-10 text-uppercase py-2 shadow-sm border-2">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <button type="button" onclick="confirmDelete(<?= $c['id'] ?>)" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-900 ls-1 fs-10 text-uppercase py-2 border-2">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Phase 17 Boutique Incentive Protocol
function toggleStatus(id, action) {
    Swal.fire({
        title: action + '?',
        text: "The availability of this discount code will change across the boutique immediately.",
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Yes, Proceed',
        cancelButtonText: 'Cancel Protocol',
        customClass: {
            confirmButton: 'btn btn-primary px-4 py-2 rounded-pill fw-900 ls-1 shadow-gold',
            cancelButton: 'btn btn-light px-4 py-2 rounded-pill fw-900 ls-1 border ms-2'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= url('admin/coupons/index.php?action=toggle&id=') ?>' + id + '&csrf=' + '<?= csrfToken() ?>';
        }
    });
}

function confirmDelete(id) {
    Swal.fire({
        title: 'Purge Incentive?',
        text: "Removing this discount code is irreversible. Previous redemption history will remain in the sales ledger.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Purge Permanently',
        cancelButtonText: 'Cancel Protocol',
        customClass: {
            confirmButton: 'btn btn-primary px-4 py-2 rounded-pill fw-900 ls-1 shadow-gold',
            cancelButton: 'btn btn-light px-4 py-2 rounded-pill fw-900 ls-1 border ms-2'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= url('admin/coupons/index.php?action=delete&id=') ?>' + id + '&csrf=' + '<?= csrfToken() ?>';
        }
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
