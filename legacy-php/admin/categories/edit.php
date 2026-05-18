<?php
/**
 * Admin Edit Category
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();

$editId = (int)inputStr('id', 0, 'GET');

// Fetch existing category
$stmtCat = $db->prepare("SELECT * FROM categories WHERE id = ?");
$stmtCat->execute([$editId]);
$category = $stmtCat->fetch();

if (!$category) {
    setFlash('error', 'Category not found.');
    redirect(url('admin/categories/index.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    
    $name        = inputStr('name', '', 'POST');
    $parent_id   = (int)inputStr('parent_id', 0, 'POST');
    $description = inputStr('description', '', 'POST');
    $is_active   = isset($_POST['is_active']) ? 1 : 0;
    
    // Prevent setting self as parent
    if ($parent_id === $editId) {
        $parent_id = 0;
    }
    
    // Check Slug Uniqueness (excluding self)
    $slug = slugify($name);
    $stmtSlug = $db->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
    $stmtSlug->execute([$slug, $editId]);
    if ($stmtSlug->fetchColumn()) {
        $slug = $slug . '-' . time();
    }
    
    $imagePath = $category['image'];
    
    // Check if removing existing image
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
        if ($imagePath) {
            $fPath = __DIR__ . '/../../' . $imagePath;
            if (file_exists($fPath)) @unlink($fPath);
            $imagePath = null;
        }
    }
    
    // Handle New Image upload
    if (!empty($_FILES['image']['name'])) {
        $res = uploadImage($_FILES['image'], 'uploads/categories/');
        if ($res['success']) {
            // remove old one if we upload a new one
            if ($imagePath && $imagePath !== $res['path']) {
                $fPath = __DIR__ . '/../../' . $imagePath;
                if (file_exists($fPath)) @unlink($fPath);
            }
            $imagePath = $res['path'];
        } else {
            setFlash('error', $res['error']);
        }
    }
    
    try {
        $stmt = $db->prepare("
            UPDATE categories SET 
                name = ?, slug = ?, image = ?, description = ?, parent_id = ?, is_active = ?
            WHERE id = ?
        ");
        $pid = $parent_id > 0 ? $parent_id : null;
        $stmt->execute([$name, $slug, $imagePath, $description, $pid, $is_active, $editId]);
        
        setFlash('success', 'Category updated successfully.');
        redirect(url('admin/categories/index.php'));
        
    } catch (PDOException $e) {
        setFlash('error', 'Database error: ' . $e->getMessage());
    }
}

// Fetch potential parents (exclude self and its children to prevent circular reference)
// Simplification: just exclude self
$stmtParents = $db->prepare("SELECT id, name FROM categories WHERE parent_id IS NULL AND id != ? ORDER BY name ASC");
$stmtParents->execute([$editId]);
$parentCats = $stmtParents->fetchAll();

$pageTitle = 'Edit Category';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-800 text-dark mb-1">Edit Category</h3>
        <p class="text-secondary small mb-0">Editing: <span class="fw-700 text-dark"><?= e($category['name']) ?></span></p>
    </div>
    <a href="<?= url('admin/categories/index.php') ?>" class="btn btn-light border fw-600 rounded-pill px-4">
        <i class="fa-solid fa-arrow-left me-2"></i> Back
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8 mx-auto">
        <form action="<?= url('admin/categories/edit.php?id=' . $editId) ?>" method="POST" enctype="multipart/form-data" class="admin-card p-4 p-md-5">
            <?= csrfField() ?>
            
            <div class="mb-4">
                <label class="form-label fw-600">Category Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control form-control-lg fs-6" value="<?= e($category['name']) ?>" required>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-600">Parent Category</label>
                <select name="parent_id" class="form-select">
                    <option value="0" <?= is_null($category['parent_id']) ? 'selected' : '' ?>>--- None (Top Level Category) ---</option>
                    <?php foreach($parentCats as $p): ?>
                        <option value="<?= $p['id'] ?>" class="fw-bold" <?= $category['parent_id'] == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text small mt-2">If this is a sub-category, choose its parent. Top-level categories have no parent.</div>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-600">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= e($category['description']) ?></textarea>
            </div>
            
            <div class="mb-4 border rounded p-3 bg-light">
                <label class="form-label fw-600 border-bottom pb-2 w-100">Category Image</label>
                
                <?php if($category['image']): ?>
                    <div class="d-flex align-items-center gap-4 mb-3">
                        <div class="bg-white rounded border p-1" style="width: 100px; height: 100px;">
                            <img src="<?= url($category['image']) ?>" alt="Category" class="img-fluid rounded" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input mb-1" type="checkbox" name="remove_image" value="1" id="remImg">
                            <label class="form-check-label text-danger fw-600" for="remImg">
                                Remove Current Image
                            </label>
                        </div>
                    </div>
                <?php endif; ?>
                
                <input class="form-control" type="file" name="image" accept="image/*">
                <div class="form-text small mt-2">Recommended: 800x800px. Uploading a new image will replace the existing one.</div>
            </div>
            
            <div class="mb-4 bg-light p-3 rounded border">
                <div class="form-check form-switch m-0">
                    <input class="form-check-input mt-1" type="checkbox" role="switch" name="is_active" id="isActive" value="1" <?= $category['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label fw-700 ms-2 text-dark" for="isActive">Category is Active</label>
                </div>
            </div>
            
            <hr class="my-4 text-light">
            
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary fw-800 px-5 rounded-pill text-uppercase ls-1 py-2">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Update Category
                </button>
            </div>
            
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
