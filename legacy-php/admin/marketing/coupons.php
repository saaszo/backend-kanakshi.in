<?php
/**
 * Admin: Coupon Management
 */
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$db = getDB();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $id          = (int)($_POST['id'] ?? 0);
        $code        = strtoupper(trim(inputStr('code', '', 'POST')));
        $type        = inputStr('type', 'fixed', 'POST');
        $value       = (float)inputStr('value', 0, 'POST');
        $expiry      = inputStr('expiry', null, 'POST');
        $min_order   = (float)inputStr('min_order', 0, 'POST');
        $max_uses    = (int)($_POST['max_uses'] ?? 0);
        $is_active   = isset($_POST['is_active']) ? 1 : 0;

        if (!$code || $value <= 0) {
            setFlash('error', 'Please provide a valid code and value.');
        } else {
            if ($action === 'add') {
                try {
                    $stmt = $db->prepare("INSERT INTO coupons (code, type, value, expiry_date, min_order_amount, max_uses, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$code, $type, $value, $expiry, $min_order, $max_uses, $is_active]);
                    setFlash('success', 'Coupon created successfully.');
                } catch (Exception $e) {
                    setFlash('error', 'Error: ' . $e->getMessage());
                }
            } else {
                $stmt = $db->prepare("UPDATE coupons SET code = ?, type = ?, value = ?, expiry_date = ?, min_order_amount = ?, max_uses = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$code, $type, $value, $expiry, $min_order, $max_uses, $is_active, $id]);
                setFlash('success', 'Coupon updated successfully.');
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM coupons WHERE id = ?")->execute([$id]);
        setFlash('success', 'Coupon deleted.');
    }
    
    redirect(url('admin/marketing/coupons.php'));
}

// Fetch all coupons
$coupons = $db->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'Manage Coupons';
require_once __DIR__ . '/../../admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-800 text-dark mb-0">Marketing <span class="text-primary">Coupons</span></h3>
        <p class="text-secondary small fw-600 mb-0">Create and manage high-conversion discount codes for your jewelry store.</p>
    </div>
    <button class="btn btn-primary fw-700 px-4 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#addCouponModal">
        <i class="fa-solid fa-plus me-2"></i> Create Coupon
    </button>
</div>

<div class="admin-card overflow-hidden shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-secondary fs-8 text-uppercase ls-1">
                <tr>
                    <th class="py-3 px-4">Coupon Code</th>
                    <th class="py-3 px-3">Type & Value</th>
                    <th class="py-3 px-3">Min Order</th>
                    <th class="py-3 px-3">Usage</th>
                    <th class="py-3 px-3">Expiry</th>
                    <th class="py-3 px-3 text-center">Status</th>
                    <th class="py-3 px-4 text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($coupons)): ?>
                    <tr><td colspan="7" class="text-center py-5 text-muted fw-500">No coupons created yet.</td></tr>
                <?php endif; ?>
                <?php foreach($coupons as $c): 
                    $isExpired = $c['expiry_date'] && strtotime($c['expiry_date']) < time();
                ?>
                    <tr>
                        <td class="px-4 py-3">
                            <span class="badge bg-light text-dark border py-2 px-3 fw-800 ls-1"><?= e($c['code']) ?></span>
                        </td>
                        <td class="px-3 py-3 fw-700 text-dark">
                            <?= $c['type'] === 'percent' ? $c['value'].'%' : formatPrice($c['value']) ?>
                        </td>
                        <td class="px-3 py-3 text-secondary small fw-600">
                            Min: <?= formatPrice($c['min_order_amount']) ?>
                        </td>
                        <td class="px-3 py-3">
                            <div class="small fw-700 text-dark"><?= $c['used_count'] ?> / <?= $c['max_uses'] ?: '∞' ?></div>
                            <div class="progress mt-1" style="height: 4px; width: 80px;">
                                <div class="progress-bar bg-info" style="width: <?= $c['max_uses'] ? min(100, ($c['used_count']/$c['max_uses'])*100) : 10 ?>%"></div>
                            </div>
                        </td>
                        <td class="px-3 py-3 small <?= $isExpired ? 'text-danger fw-700' : 'text-secondary fw-500' ?>">
                            <?= $c['expiry_date'] ? date('d M, Y', strtotime($c['expiry_date'])) : 'Never' ?>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="badge rounded-pill px-3 py-2 <?= ($c['is_active'] && !$isExpired) ? 'bg-success' : 'bg-secondary' ?>">
                                <?= ($c['is_active'] && !$isExpired) ? 'Active' : ($isExpired ? 'Expired' : 'Paused') ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                <button class="btn btn-sm btn-light border edit-coupon" 
                                    data-id="<?= $c['id'] ?>" 
                                    data-code="<?= e($c['code']) ?>" 
                                    data-type="<?= $c['type'] ?>" 
                                    data-value="<?= $c['value'] ?>" 
                                    data-expiry="<?= $c['expiry_date'] ?>"
                                    data-min="<?= $c['min_order_amount'] ?>"
                                    data-max="<?= $c['max_uses'] ?>"
                                    data-active="<?= $c['is_active'] ?>">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form method="POST" onsubmit="return confirm('Delete this coupon?');" class="d-inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash-can"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Coupon Modal -->
<div class="modal fade" id="addCouponModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content border-0 shadow-lg">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="add" id="modalAction">
            <input type="hidden" name="id" value="" id="couponId">
            
            <div class="modal-header border-0 bg-light p-4">
                <h5 class="fw-800 text-dark mb-0" id="modalTitle">Create New Coupon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-600">Coupon Code (Uppercase)</label>
                    <input type="text" name="code" id="couponCode" class="form-control fw-800 text-uppercase ls-1" placeholder="SAVE20" required>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-600">Type</label>
                        <select name="type" id="couponType" class="form-select">
                            <option value="fixed">Fixed Amount (₹)</option>
                            <option value="percent">Percentage (%)</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-600">Value</label>
                        <input type="number" name="value" id="couponValue" class="form-control" step="0.01" required>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-600">Expiry Date</label>
                        <input type="date" name="expiry" id="couponExpiry" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-600">Min Order Requirement</label>
                        <input type="number" name="min_order" id="couponMin" class="form-control" value="0">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-600">Max Total Uses (0 for Unlimited)</label>
                    <input type="number" name="max_uses" id="couponMax" class="form-control" value="0">
                </div>
                <div class="form-check form-switch p-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-check-label fw-600" for="is_active">Active Status</label>
                        <input class="form-check-input ms-0" type="checkbox" name="is_active" id="is_active" checked style="width: 50px; height: 24px;">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light fw-700 px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary fw-800 px-5 rounded-pill shadow">Save Coupon</button>
            </div>
        </form>
    </div>
</div>

<?php 
$extraAdminJs = <<<JS
<script>
document.querySelectorAll('.edit-coupon').forEach(btn => {
    btn.addEventListener('click', function() {
        const d = this.dataset;
        document.getElementById('modalTitle').innerText = 'Edit Coupon: ' + d.code;
        document.getElementById('modalAction').value = 'edit';
        document.getElementById('couponId').value = d.id;
        document.getElementById('couponCode').value = d.code;
        document.getElementById('couponType').value = d.type;
        document.getElementById('couponValue').value = d.value;
        document.getElementById('couponExpiry').value = d.expiry;
        document.getElementById('couponMin').value = d.min;
        document.getElementById('couponMax').value = d.max;
        document.getElementById('is_active').checked = parseInt(d.active) === 1;
        
        const modal = new bootstrap.Modal(document.getElementById('addCouponModal'));
        modal.show();
    });
});
</script>
JS;
require_once __DIR__ . '/../../admin/includes/footer.php';
?>
