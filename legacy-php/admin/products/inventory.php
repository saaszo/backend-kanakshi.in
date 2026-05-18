<?php
/**
 * Admin Inventory Management Suite
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();

// Handle AJAX/POST Stock Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    validateCsrf();
    $id    = (int)$_POST['id'];
    $type  = $_POST['type']; // 'product' or 'variant'
    $stock = (int)$_POST['stock'];
    
    if ($type === 'product') {
        $stmt = $db->prepare("UPDATE products SET stock = ? WHERE id = ?");
        $stmt->execute([$stock, $id]);
    } else {
        $stmt = $db->prepare("UPDATE product_variants SET stock = ? WHERE id = ?");
        $stmt->execute([$stock, $id]);
    }
    
    if (isset($_POST['ajax'])) {
        echo json_encode(['success' => true]);
        exit;
    }
    setFlash('success', 'Stock updated successfully.');
    redirect(url('admin/products/inventory.php'));
}

// Search & Filter
$search = inputStr('q', '', 'GET');
$catId  = (int)inputStr('cat', 0, 'GET');

$where = "1=1";
$params = [];

if ($search) {
    $where .= " AND (p.name LIKE ? OR p.sku LIKE ? OR v.sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($catId) {
    $where .= " AND p.category_id = ?";
    $params[] = $catId;
}

// Fetch Products with their Variants in a flat list for inventory management
$sql = "SELECT p.id as pid, p.name as pname, p.sku as psku, p.stock as pstock, 
               v.id as vid, v.size as vsize, v.color as vcolor, v.sku as vsku, v.stock as vstock, 
               c.name as cat_name
        FROM products p
        LEFT JOIN product_variants v ON p.id = v.product_id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE $where
        ORDER BY p.id DESC, v.id ASC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$inventory = $stmt->fetchAll();

$stmtCat = $db->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $stmtCat->fetchAll();

$pageTitle = 'Inventory Management Suite';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-900 text-dark mb-0 ls-1">Inventory Management</h3>
    <a href="<?= url('admin/products/index.php') ?>" class="btn btn-light border fw-800 rounded-pill px-4 fs-8 text-uppercase">
        <i class="fa-solid fa-box me-2"></i> Catalog View
    </a>
</div>

<div class="admin-card mb-4 shadow-sm border-0">
    <div class="p-4 border-bottom bg-white rounded-top">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="q" class="form-control border-light shadow-none" placeholder="Search product name or SKU..." value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="cat" class="form-select border-light shadow-none">
                    <option value="">All Categories</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $catId == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-dark w-100 fw-800 rounded-3">Filter</button>
            </div>
            <?php if($search || $catId): ?>
                <div class="col-md-2">
                    <a href="<?= url('admin/products/inventory.php') ?>" class="btn btn-outline-danger w-100 fw-700 rounded-3">Clear</a>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted fs-9 text-uppercase ls-2">
                <tr>
                    <th class="py-3 px-4">Jewelry Collection Item</th>
                    <th class="py-3 px-3">SKU</th>
                    <th class="py-3 px-3" style="width: 150px;">Stock Control</th>
                    <th class="py-3 px-4 text-end">Last Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($inventory)): ?>
                    <tr><td colspan="4" class="text-center py-5 text-muted">No items found matching your criteria.</td></tr>
                <?php else: ?>
                    <?php 
                    $lastPid = 0;
                    foreach($inventory as $item): 
                        $isVariant = !is_null($item['vid']);
                        $uniqueId = $isVariant ? 'v' . $item['vid'] : 'p' . $item['pid'];
                        $isFirstOfProd = ($item['pid'] != $lastPid);
                        $lastPid = $item['pid'];
                    ?>
                        <tr class="<?= $isFirstOfProd ? 'border-top-thick' : '' ?>">
                            <td class="px-4 py-3">
                                <?php if($isVariant): ?>
                                    <div class="ps-4 border-start border-2 border-primary ms-2 fs-8 fw-600 text-dark">
                                        <span class="text-muted fw-500">Variant:</span> <?= e($item['vsize']) ?> <?= $item['vcolor'] ? '/ ' . e($item['vcolor']) : '' ?>
                                    </div>
                                <?php else: ?>
                                    <div class="fw-800 text-dark"><?= e($item['pname']) ?></div>
                                    <div class="fs-9 text-muted fw-600 text-uppercase ls-1"><?= e($item['cat_name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-3">
                                <span class="font-monospace fs-8 p-1 bg-light rounded border px-2"><?= e($isVariant ? $item['vsku'] : $item['psku']) ?></span>
                            </td>
                            <td class="px-3">
                                <form action="" method="POST" class="stock-form" data-id="<?= $isVariant ? $item['vid'] : $item['pid'] ?>" data-type="<?= $isVariant ? 'variant' : 'product' ?>">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="update_stock" value="1">
                                    <input type="hidden" name="id" value="<?= $isVariant ? $item['vid'] : $item['pid'] ?>">
                                    <input type="hidden" name="type" value="<?= $isVariant ? 'variant' : 'product' ?>">
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="stock" class="form-control fw-900 text-center border-silver rounded-start bg-white stock-input" value="<?= $isVariant ? $item['vstock'] : $item['pstock'] ?>" style="max-width: 80px;">
                                        <button type="submit" class="btn btn-primary fw-800 px-2" title="Update Stock"><i class="fa-solid fa-check"></i></button>
                                    </div>
                                </form>
                            </td>
                            <td class="px-4 text-end">
                                <?php 
                                    $currentStock = $isVariant ? $item['vstock'] : $item['pstock'];
                                    if($currentStock <= 0) echo '<span class="badge-soft badge-soft-danger">Out of Stock</span>';
                                    elseif($currentStock <= 5) echo '<span class="badge-soft badge-soft-warning">Low Stock</span>';
                                    else echo '<span class="badge-soft badge-soft-success">In Stock</span>';
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.border-top-thick { border-top: 2px solid #f1f2f4; }
.stock-input { transition: background 0.3s; }
.stock-input.saving { background: #fff9db !important; }
.stock-input.saved { background: #e6fcf5 !important; }
</style>

<script>
document.querySelectorAll('.stock-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const input = this.querySelector('.stock-input');
        const btn = this.querySelector('button');
        const originalBtn = btn.innerHTML;
        
        input.classList.add('saving');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';

        const formData = new FormData(this);
        formData.append('ajax', '1');

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                input.classList.remove('saving');
                input.classList.add('saved');
                setTimeout(() => input.classList.remove('saved'), 2000);
            }
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalBtn;
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
