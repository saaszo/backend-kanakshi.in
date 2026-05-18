<?php
/**
 * Admin Edit Coupon
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();
$id = (int)inputStr('id', 0, 'GET');

// Fetch Coupon
$stmt = $db->prepare("SELECT * FROM coupons WHERE id = ?");
$stmt->execute([$id]);
$coupon = $stmt->fetch();

if (!$coupon) {
    setFlash('error', 'Coupon not found.');
    redirect(url('admin/coupons/index.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    
    $code      = strtoupper(inputStr('code', '', 'POST'));
    $type      = inputStr('type', 'percent', 'POST');
    $value     = (float)inputStr('value', 0, 'POST');
    $minOrder  = (float)inputStr('min_order', 0, 'POST');
    $maxUses   = (int)inputStr('max_uses', 0, 'POST');
    $expiry    = inputStr('expiry_date', '', 'POST');
    $isActive  = (int)isset($_POST['is_active']);
    
    if (!$code || $value <= 0) {
        setFlash('error', 'Discount code and value are required.');
    } else {
        try {
            $stmt = $db->prepare("
                UPDATE coupons 
                SET code = ?, type = ?, value = ?, min_order = ?, max_uses = ?, expiry_date = ?, is_active = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $code, 
                $type, 
                $value, 
                $minOrder, 
                $maxUses > 0 ? $maxUses : NULL, 
                $expiry ?: NULL, 
                $isActive,
                $id
            ]);
            setFlash('success', 'Coupon updated successfully!');
            redirect(url('admin/coupons/index.php'));
        } catch (PDOException $e) {
            setFlash('error', 'Error updating coupon: ' . $e->getMessage());
        }
    }
}

$pageTitle = 'Edit Coupon';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-4 d-flex align-items-center gap-2">
    <a href="<?= url('admin/coupons/index.php') ?>" class="btn btn-light rounded-pill px-3 fw-700 h-40px fs-7 border"><i class="fa-solid fa-arrow-left"></i></a>
    <h3 class="fw-800 text-dark mb-0">Edit <span class="text-primary">Coupon</span></h3>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="admin-card p-4">
            <form action="<?= url('admin/coupons/edit.php?id='.$id) ?>" method="POST">
                <?= csrfField() ?>
                
                <div class="mb-3">
                    <label class="form-label fw-800 text-muted text-uppercase ls-1 fs-8">Coupon Code</label>
                    <input type="text" name="code" class="form-control form-control-lg fw-700 text-uppercase" placeholder="e.g. SAVE50" value="<?= e($coupon['code']) ?>" maxlength="50" required>
                    <div class="form-text small">Enter a unique code like SAVEOFF.</div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-800 text-muted text-uppercase ls-1 fs-8">Discount Type</label>
                        <select name="type" class="form-select fw-600">
                            <option value="percent" <?= $coupon['type'] === 'percent' ? 'selected' : '' ?>>Percentage (%)</option>
                            <option value="fixed" <?= $coupon['type'] === 'fixed' ? 'selected' : '' ?>>Fixed Amount (₹)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-800 text-muted text-uppercase ls-1 fs-8">Discount Value</label>
                        <input type="number" step="0.01" name="value" class="form-control fw-600" placeholder="e.g. 10 or 100" value="<?= (float)$coupon['value'] ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-800 text-muted text-uppercase ls-1 fs-8">Minimum Spend Requirement</label>
                    <input type="number" step="0.01" name="min_order" class="form-control fw-600" placeholder="e.g. 499 (0 for no limit)" value="<?= (float)$coupon['min_order'] ?>">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-800 text-muted text-uppercase ls-1 fs-8">Max Usage Limit</label>
                        <input type="number" name="max_uses" class="form-control fw-600" placeholder="0 = Unlimited" value="<?= (int)$coupon['max_uses'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-800 text-muted text-uppercase ls-1 fs-8">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control fw-600" value="<?= $coupon['expiry_date'] ?>">
                    </div>
                </div>

                <div class="my-4">
                    <div class="form-check form-switch p-0 m-0 d-flex justify-content-between align-items-center">
                        <label class="form-check-label fw-800 text-dark text-uppercase ls-1 fs-8" for="isActive">Set as Active</label>
                        <input class="form-check-input ms-0" type="checkbox" name="is_active" id="isActive" <?= $coupon['is_active'] ? 'checked' : '' ?> style="width: 2.5em; height: 1.2em;">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 fw-800 rounded-pill py-3">
                    <i class="fa-solid fa-check me-2"></i> Update Coupon
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
