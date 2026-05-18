<?php
/**
 * Admin Categories List
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    
    // Check for child categories
    $stmtChild = $db->prepare("SELECT COUNT(*) FROM categories WHERE parent_id = ?");
    $stmtChild->execute([$delId]);
    if ($stmtChild->fetchColumn() > 0) {
        setFlash('error', 'Cannot delete category because it has sub-categories. Delete or move them first.');
    } else {
        // Check for products in this category
        $stmtProd = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $stmtProd->execute([$delId]);
        if ($stmtProd->fetchColumn() > 0) {
            setFlash('error', 'Cannot delete category. There are products linked to it. Reassign them first.');
        } else {
            // Delete image if exists
            $stmtImg = $db->prepare("SELECT image FROM categories WHERE id = ?");
            $stmtImg->execute([$delId]);
            $catImg = $stmtImg->fetchColumn();
            if ($catImg) {
                $filePath = __DIR__ . '/../../' . $catImg;
                if (file_exists($filePath)) @unlink($filePath);
            }
            
            $db->prepare("DELETE FROM categories WHERE id = ?")->execute([$delId]);
            setFlash('success', 'Category deleted successfully.');
        }
    }
    redirect(url('admin/categories/index.php'));
}

// Fetch all categories
$stmtCat = $db->query("SELECT * FROM categories ORDER BY parent_id ASC, name ASC");
$allCats = $stmtCat->fetchAll();

// Build Tree for Table View with Product Counts
$catTree = [];
foreach ($allCats as $c) {
    // Get product count for this category
    $stmtCount = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmtCount->execute([$c['id']]);
    $c['product_count'] = $stmtCount->fetchColumn();

    if (!$c['parent_id']) {
        $catTree[$c['id']] = $c;
        $catTree[$c['id']]['children'] = [];
    } else if (isset($catTree[$c['parent_id']])) {
        $catTree[$c['parent_id']]['children'][] = $c;
    }
}

$pageTitle = 'Collection Categories';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-900 text-dark mb-1 ls-1">Collection Architect</h3>
        <p class="text-muted small mb-0 fw-700 text-uppercase ls-2">Sculpting the Boutique's Hierarchy</p>
    </div>
    <a href="<?= url('admin/categories/add.php') ?>" class="btn btn-primary fw-900 rounded-pill px-4 ls-1 fs-8 text-uppercase shadow-gold py-2">
        <i class="fa-solid fa-plus-circle me-2"></i> New Collection
    </a>
</div>

<div class="admin-card mb-4 shadow-sm border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted fs-9 text-uppercase ls-2">
                <tr>
                    <th class="py-3 px-4" style="width: 45%;">Collection Base</th>
                    <th class="py-3 px-3">Inventory Pulse</th>
                    <th class="py-3 px-3 text-center">Visibility</th>
                    <th class="py-3 px-4 text-end">Architectural Controls</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($catTree)): ?>
                    <tr><td colspan="4" class="text-center py-5 text-muted fw-600">No collections have been defined yet.</td></tr>
                <?php else: ?>
                    <?php foreach($catTree as $parent): ?>
                        <!-- Master Category Row -->
                        <tr class="bg-white border-top animate-in">
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-primary flex-shrink-0 shadow-sm border" style="width: 52px; height: 52px; overflow: hidden;">
                                        <?php if($parent['image']): ?>
                                            <img src="<?= url($parent['image']) ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <i class="fa-solid fa-gem fa-lg opacity-75"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="fw-900 text-dark fs-6 ls-1"><?= e($parent['name']) ?></div>
                                        <div class="fs-9 text-muted fw-700 font-monospace text-uppercase opacity-50">/<?= e($parent['slug']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <span class="badge-soft badge-soft-primary px-3 py-2 rounded-pill fw-900 fs-9 ls-1 text-uppercase">
                                    <i class="fa-solid fa-box-archive me-1"></i> <?= $parent['product_count'] ?> Pieces
                                </span>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <?php if($parent['is_active']): ?>
                                    <span class="badge-soft badge-soft-success px-3 py-1 rounded-pill fw-900 fs-10 text-uppercase ls-1">Public</span>
                                <?php else: ?>
                                    <span class="badge-soft badge-soft-danger px-3 py-1 rounded-pill fw-900 fs-10 text-uppercase ls-1">Archived</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="<?= url('admin/categories/edit.php?id=' . $parent['id']) ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-900 ls-1 fs-10 text-uppercase py-2 shadow-sm border-2">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </a>
                                    <button type="button" onclick="confirmDelete(<?= $parent['id'] ?>, 'collection')" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-900 ls-1 fs-10 text-uppercase py-2 border-2">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Sub-Category Rows -->
                        <?php if(!empty($parent['children'])): ?>
                            <?php foreach($parent['children'] as $child): ?>
                                <tr class="bg-light bg-opacity-10 animate-in">
                                    <td class="px-4 py-2">
                                        <div class="d-flex align-items-center gap-3 ps-5">
                                            <div class="border-start border-2 border-primary opacity-25" style="height: 25px;"></div>
                                            <div class="bg-white rounded d-flex align-items-center justify-content-center text-muted flex-shrink-0 border shadow-sm" style="width: 36px; height: 36px; overflow: hidden;">
                                                <?php if($child['image']): ?>
                                                    <img src="<?= url($child['image']) ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                                <?php else: ?>
                                                    <i class="fa-solid fa-leaf fs-9 opacity-50"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div class="fw-800 text-secondary fs-8 ls-1"><?= e($child['name']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="text-secondary fw-700 fs-9 ls-1 ms-4 opacity-75">
                                            <i class="fa-solid fa-arrow-turn-up fa-rotate-90 me-2 text-primary opacity-50"></i> <?= $child['product_count'] ?> items
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <?php if($child['is_active']): ?>
                                            <span class="text-success small fw-900 fs-10 text-uppercase ls-1">Active</span>
                                        <?php else: ?>
                                            <span class="text-muted small fw-900 fs-10 text-uppercase ls-1">Hidden</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-2 text-end">
                                        <div class="d-flex justify-content-end gap-2 opacity-75">
                                            <a href="<?= url('admin/categories/edit.php?id=' . $child['id']) ?>" class="btn btn-link text-dark p-1" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                            <button type="button" onclick="confirmDelete(<?= $child['id'] ?>, 'sub-collection')" class="btn btn-link text-danger p-1" title="Delete"><i class="fa-solid fa-trash-can"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Phase 17 Collection Architect Protocol
function confirmDelete(id, type) {
    Swal.fire({
        title: 'Demolish ' + type + '?',
        text: "Archiving or deleting a collection segment is irreversible. Ensure all masterpieces are relocated.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Purge Record',
        cancelButtonText: 'Cancel Protocol',
        customClass: {
            confirmButton: 'btn btn-primary px-4 py-2 rounded-pill fw-900 ls-1 shadow-gold',
            cancelButton: 'btn btn-light px-4 py-2 rounded-pill fw-900 ls-1 border ms-2'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= url('admin/categories/index.php?delete=') ?>' + id;
        }
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
