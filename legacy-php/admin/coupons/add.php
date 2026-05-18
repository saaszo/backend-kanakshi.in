<?php
/**
 * Admin Create Coupon
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    
    $code      = strtoupper(inputStr('code', '', 'POST'));
    $type      = inputStr('type', 'percent', 'POST');
    $value     = (float)inputStr('value', 0, 'POST');
    $minOrder  = (float)inputStr('min_order', 0, 'POST');
    $maxUses   = (int)inputStr('max_uses', 0, 'POST');
    $expiry    = inputStr('expiry_date', '', 'POST');
    $isActive  = (int)isset($_POST['is_active']);
    
    // Simple Validation
    if (!$code || $value <= 0) {
        setFlash('error', 'Discount code and value are required.');
    } else {
        try {
            $stmt = $db->prepare("
                INSERT INTO coupons (code, type, value, min_order, max_uses, expiry_date, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $code, 
                $type, 
                $value, 
                $minOrder, 
                $maxUses > 0 ? $maxUses : NULL, 
                $expiry ?: NULL, 
                $isActive
            ]);
            setFlash('success', 'Coupon created successfully!');
            redirect(url('admin/coupons/index.php'));
        } catch (PDOException $e) {
            setFlash('error', 'Error creating coupon: ' . $e->getMessage());
        }
    }
}

$pageTitle = 'Add Coupon';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-4 d-flex align-items-center gap-2">
    <a href="<?= url('admin/coupons/index.php') ?>" class="btn btn-light rounded-pill px-3 fw-700 h-40px fs-7 border"><i class="fa-solid fa-arrow-left"></i></a>
    <h3 class="fw-800 text-dark mb-0">Add New <span class="text-primary">Coupon</span></h3>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="admin-card p-4">
            <form action="<?= url('admin/coupons/add.php') ?>" method="POST">
                <?= csrfField() ?>
                
                <div class="mb-3">
                    <label class="form-label fw-800 text-muted text-uppercase ls-1 fs-8">Coupon Code</label>
                    <input type="text" name="code" class="form-control form-control-lg fw-700 text-uppercase" placeholder="e.g. SAVE50" maxlength="50" required>
                    <div class="form-text small">Enter a unique code like SAVEOFF.</div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-800 text-muted text-uppercase ls-1 fs-8">Discount Type</label>
                        <select name="type" class="form-select fw-600">
                            <option value="percent">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (₹)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-800 text-muted text-uppercase ls-1 fs-8">Discount Value</label>
                        <input type="number" step="0.01" name="value" class="form-control fw-600" placeholder="e.g. 10 or 100" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-800 text-muted text-uppercase ls-1 fs-8">Minimum Spend Requirement</label>
                    <input type="number" step="0.01" name="min_order" class="form-control fw-600" placeholder="e.g. 499 (0 for no limit)" value="0">
                    <div class="form-text small">Coupon only applies if order subtotal is ≥ this value.</div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-800 text-muted text-uppercase ls-1 fs-8">Max Usage Limit</label>
                        <input type="number" name="max_uses" class="form-control fw-600" placeholder="0 = Unlimited" value="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-800 text-muted text-uppercase ls-1 fs-8">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control fw-600" value="<?= date('Y-12-31') ?>">
                    </div>
                </div>

                <div class="my-4">
                    <div class="form-check form-switch p-0 m-0 d-flex justify-content-between align-items-center">
                        <label class="form-check-label fw-800 text-dark text-uppercase ls-1 fs-8" for="isActive">Set as Active</label>
                        <input class="form-check-input ms-0" type="checkbox" name="is_active" id="isActive" checked style="width: 2.5em; height: 1.2em;">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 fw-800 rounded-pill py-3">
                    <i class="fa-solid fa-save me-2"></i> Save Coupon
                </button>
            </form>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="alert alert-info border-0 rounded-4 p-4 shadow-sm bg-primary bg-opacity-10 text-primary-emphasis">
            <h5 class="fw-800 mb-3"><i class="fa-solid fa-lightbulb me-2"></i> Coupon Strategies</h5>
            <ul class="mb-0 fs-7 fw-500 lh-lg">
                <li><strong>Percentage Discounts</strong>: Great for big sales (e.g., 20% OFF).</li>
                <li><strong>Fixed Discounts</strong>: Best to lower the entry price for new users (e.g., ₹100 Off).</li>
                <li><strong>Minimum Spend</strong>: Use this to increase your <strong>Average Order Value</strong> (e.g., ₹200 off above ₹2000).</li>
                <li><strong>Limited Stock</strong>: Set a "Max Usage Limit" (e.g., First 100 Customers) to create urgency.</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
