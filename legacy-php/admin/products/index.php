<?php
/**
 * Admin Products List
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();

// Handle Delete (Soft or Hard depending on your logic - we'll do soft delete by setting is_active=0 for safety, 
// or hard delete if really needed. Let's do Hard Delete for complete removal).
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    
    // Check if product exists and get images to delete from server
    $stmt = $db->prepare("SELECT images FROM products WHERE id = ?");
    $stmt->execute([$delId]);
    $prod = $stmt->fetch();
    
    if ($prod) {
        $images = json_decode($prod['images'] ?? '[]', true) ?: [];
        foreach ($images as $img) {
            $filePath = __DIR__ . '/../../' . $img;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
        
        $db->prepare("DELETE FROM products WHERE id = ?")->execute([$delId]);
        setFlash('success', 'Product deleted successfully.');
    }
    redirect(url('admin/products/index.php'));
}

// Fetch Pagination & Search
$page   = (int)inputStr('page', 1, 'GET');
$search = inputStr('q', '', 'GET');
$limit  = 15;
$offset = ($page - 1) * $limit;

$where = "1=1";
$params = [];

if ($search) {
    $where .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Count Total
$stmtTotal = $db->prepare("SELECT COUNT(*) FROM products p WHERE $where");
$stmtTotal->execute($params);
$totalRows = $stmtTotal->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Fetch Products
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE $where 
        ORDER BY p.id DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$pageTitle = 'Manage Products';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-900 text-dark mb-0 ls-1">Boutique Catalog</h3>
    <a href="<?= url('admin/products/add.php') ?>" class="btn btn-primary fw-900 rounded-pill px-5 py-3 ls-1 fs-8 text-uppercase shadow-gold">
        <i class="fa-solid fa-plus-circle me-2"></i> Add Masterpiece
    </a>
</div>

<div class="admin-card mb-4 shadow-sm border-0 overflow-hidden">
    <!-- Search & Filter Bar -->
    <div class="p-4 border-bottom bg-white">
        <form action="" method="GET" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="q" class="form-control border-light shadow-none" placeholder="Search by name, SKU or gemstone..." value="<?= e($search) ?>">
                    <?php if($search): ?>
                        <a href="<?= url('admin/products/index.php') ?>" class="btn btn-outline-danger border-light border-start-0 shadow-none"><i class="fa-solid fa-xmark"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-dark w-100 fw-800 rounded-3">Filter Catalog</button>
            </div>
            <div class="col-md-3">
                <a href="<?= url('admin/products/inventory.php') ?>" class="btn btn-outline-primary w-100 fw-800 rounded-3">
                    <i class="fa-solid fa-boxes-stacked me-1"></i> Stock Suite
                </a>
            </div>
        </form>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted fs-9 text-uppercase ls-2">
                <tr>
                    <th class="py-3 px-4" style="width: 40%;">Product Essence</th>
                    <th class="py-3 px-3">Valuation</th>
                    <th class="py-3 px-3 text-center">Availability</th>
                    <th class="py-3 px-3 text-center">Visibility</th>
                    <th class="py-3 px-4 text-end">Control</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($products)): ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted fw-600">The vault is currently empty for this search.</td></tr>
                <?php else: ?>
                    <?php foreach($products as $p): 
                        $thumb = productThumb($p['images']);
                    ?>
                        <tr>
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-light rounded p-1 border shadow-sm flex-shrink-0" style="width: 60px; height: 60px; overflow: hidden;">
                                        <img src="<?= url($thumb) ?>" alt="" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px;">
                                    </div>
                                    <div>
                                        <div class="fw-900 text-dark mb-1 ls-1"><?= e($p['name']) ?></div>
                                        <div class="d-flex gap-2 text-muted fs-9 fw-700 text-uppercase ls-1">
                                            <span>SKU: <span class="text-primary"><?= e($p['sku'] ?: 'N/A') ?></span></span>
                                            <span class="opacity-25">|</span>
                                            <span><?= e($p['category_name']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <div class="fw-900 text-dark fs-7"><?= formatPrice($p['sale_price'] > 0 ? $p['sale_price'] : $p['price']) ?></div>
                                <?php if($p['sale_price'] > 0): ?>
                                    <div class="text-danger small fw-700 text-decoration-line-through opacity-50" style="font-size: 0.75rem;"><?= formatPrice($p['price']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <?php if($p['stock'] <= 0): ?>
                                    <span class="badge-soft badge-soft-danger px-3 py-1 rounded-pill fw-800 fs-9">Sold Out</span>
                                <?php elseif($p['stock'] <= 5): ?>
                                    <span class="badge-soft badge-soft-warning px-3 py-1 rounded-pill fw-800 fs-9"><?= $p['stock'] ?> Left</span>
                                <?php else: ?>
                                    <span class="badge-soft badge-soft-success px-3 py-1 rounded-pill fw-800 fs-9"><?= $p['stock'] ?> In Vault</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <?php if($p['is_active']): ?>
                                    <span class="text-success small fw-900 fs-9 text-uppercase ls-1"><i class="fa-solid fa-circle me-1" style="font-size: 8px;"></i> Showcased</span>
                                <?php else: ?>
                                    <span class="text-muted small fw-900 fs-9 text-uppercase ls-1"><i class="fa-solid fa-circle me-1" style="font-size: 8px;"></i> Private</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <div class="dropdown">
                                    <button class="btn btn-light bg-white border shadow-sm rounded-circle d-flex align-items-center justify-content-center p-0" style="width: 32px; height: 32px;" type="button" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-ellipsis-vertical fs-9"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 py-2">
                                        <li><a class="dropdown-item fw-700 py-2 fs-8" href="<?= url('product.php?slug=' . $p['slug']) ?>" target="_blank"><i class="fa-solid fa-eye me-2 text-primary opacity-75"></i> View Details</a></li>
                                        <li><a class="dropdown-item fw-700 py-2 fs-8" href="<?= url('admin/products/edit.php?id=' . $p['id']) ?>"><i class="fa-solid fa-pen-to-square me-2 text-dark opacity-75"></i> Refine Content</a></li>
                                        <li><hr class="dropdown-divider opacity-25"></li>
                                        <li>
                                            <a class="dropdown-item fw-700 py-2 fs-8 text-danger" href="javascript:void(0)" onclick="if(confirm('Securely remove this masterpiece from the vault?')) window.location.href='<?= url('admin/products/index.php?delete=' . $p['id']) ?>'">
                                                <i class="fa-solid fa-trash-can me-2"></i> Remove Item
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Professional Pagination -->
    <?php if($totalPages > 1): ?>
        <div class="px-4 py-3 border-top d-flex justify-content-between align-items-center bg-white">
            <div class="text-muted fw-700 fs-9 text-uppercase ls-1">Page <?= $page ?> of <?= $totalPages ?></div>
            <div>
                <?= paginationLinks($page, $totalPages, url('admin/products/index.php') . '?' . ($search ? 'q='.urlencode($search).'&' : '')) ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
