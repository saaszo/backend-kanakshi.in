<?php
/**
 * Admin Banners List
 */
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$db = getDB();

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Fetch image to delete file
    $stmt = $db->prepare("SELECT image FROM banners WHERE id = ?");
    $stmt->execute([$id]);
    $banner = $stmt->fetch();
    
    if ($banner) {
        $filePath = __DIR__ . '/../../../' . $banner['image'];
        if (file_exists($filePath)) @unlink($filePath);
        
        $db->prepare("DELETE FROM banners WHERE id = ?")->execute([$id]);
        setFlash('success', 'Banner deleted successfully.');
    }
    redirect(url('admin/banners/index.php'));
}

// Fetch Banners
$stmt = $db->query("SELECT * FROM banners ORDER BY position ASC, sort_order ASC, id DESC");
$banners = $stmt->fetchAll();

$pageTitle = 'Manage Banners';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-800 text-dark mb-0">Manage Banners</h3>
    <a href="<?= url('admin/banners/add.php') ?>" class="btn btn-primary fw-600 rounded-pill px-4">
        <i class="fa-solid fa-plus me-2"></i> Add Banner
    </a>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase fs-8 text-muted ls-1">
                <tr>
                    <th class="ps-4">Image</th>
                    <th>Details</th>
                    <th>Position</th>
                    <th>Sort</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                <?php if ($banners): ?>
                    <?php foreach ($banners as $b): ?>
                        <tr>
                            <td class="ps-4" style="width: 150px;">
                                <img src="<?= url($b['image']) ?>" class="img-fluid rounded border" alt="Banner" style="max-height: 60px; object-fit: cover;">
                            </td>
                            <td>
                                <div class="fw-700 text-dark"><?= e($b['title'] ?: 'No Title') ?></div>
                                <div class="text-muted small"><?= e($b['link'] ?: 'No Link') ?></div>
                            </td>
                            <td>
                                <span class="badge bg-secondary rounded-pill text-uppercase">
                                    <?= e($b['position']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="fw-600 text-muted"><?= $b['sort_order'] ?></span>
                            </td>
                            <td>
                                <?php if ($b['is_active']): ?>
                                    <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-3">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary border-opacity-25 rounded-pill px-3">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <a href="<?= url('admin/banners/edit.php?id=' . $b['id']) ?>" class="btn btn-sm btn-light border shadow-sm text-primary rounded-pill px-3">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                <a href="<?= url('admin/banners/index.php?delete=' . $b['id']) ?>" class="btn btn-sm btn-light border shadow-sm text-danger rounded-pill px-3 ms-1" onclick="return confirm('Delete this banner?')">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-images fa-3x mb-3 text-secondary opacity-50"></i>
                            <h5 class="fw-600">No banners found</h5>
                            <p>Add a banner to display on the homepage.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
