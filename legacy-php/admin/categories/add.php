<?php
/**
 * Admin Add Category
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    
    $name        = inputStr('name', '', 'POST');
    $parent_id   = (int)inputStr('parent_id', 0, 'POST');
    $description = inputStr('description', '', 'POST');
    $is_active   = isset($_POST['is_active']) ? 1 : 0;
    
    // Generate clean slug
    $slug = slugify($name);
    
    // Ensure slug uniqueness
    $stmtSlug = $db->prepare("SELECT id FROM categories WHERE slug = ?");
    $stmtSlug->execute([$slug]);
    if ($stmtSlug->fetchColumn()) {
        $slug = $slug . '-' . time();
    }
    
    $imagePath = null;
    
    // Handle Image upload
    if (!empty($_FILES['image']['name'])) {
        $res = uploadImage($_FILES['image'], 'uploads/categories/');
        if ($res['success']) {
            $imagePath = $res['path'];
        } else {
            setFlash('error', $res['error']);
        }
    }
    
    try {
        $stmt = $db->prepare("
            INSERT INTO categories (name, slug, image, description, parent_id, is_active) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        // Nullify parent_id if 0 for database integrity
        $pid = $parent_id > 0 ? $parent_id : null;
        
        $stmt->execute([$name, $slug, $imagePath, $description, $pid, $is_active]);
        
        setFlash('success', 'Category added successfully.');
        redirect(url('admin/categories/index.php'));
        
    } catch (PDOException $e) {
        setFlash('error', 'Database error: ' . $e->getMessage());
    }
}

// Fetch only top level categories for Parent selection 
// (assuming max 2 levels deep for simplicity, or we can fetch all and allow deeper nesting)
$stmtCat = $db->query("SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY name ASC");
$parentCats = $stmtCat->fetchAll();

$pageTitle = 'Add Category';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-800 text-dark mb-0">Add Category</h3>
    <a href="<?= url('admin/categories/index.php') ?>" class="btn btn-light border fw-600 rounded-pill px-4">
        <i class="fa-solid fa-arrow-left me-2"></i> Back
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8 mx-auto">
        <form action="<?= url('admin/categories/add.php') ?>" method="POST" enctype="multipart/form-data" class="admin-card p-4 p-md-5">
            <?= csrfField() ?>
            
            <div class="mb-4">
                <label class="form-label fw-600">Category Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control form-control-lg fs-6" required placeholder="e.g. Men's Footwear">
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-600">Parent Category</label>
                <select name="parent_id" class="form-select">
                    <option value="0">--- None (Top Level Category) ---</option>
                    <?php foreach($parentCats as $p): ?>
                        <option value="<?= $p['id'] ?>" class="fw-bold"><?= e($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text small mt-2">If this is a sub-category, choose its parent.</div>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-600">Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Brief description of this category (Optional)"></textarea>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-600">Category Image</label>
                <input class="form-control" type="file" name="image" accept="image/*">
                <div class="form-text small mt-2">Recommended size: 800x800px or larger. Will be displayed on the homepage category blocks.</div>
            </div>
            
            <div class="mb-4 bg-light p-3 rounded border">
                <div class="form-check form-switch m-0">
                    <input class="form-check-input mt-1" type="checkbox" role="switch" name="is_active" id="isActive" checked value="1">
                    <label class="form-check-label fw-700 ms-2 text-dark" for="isActive">Category is Active (Visible to customers)</label>
                </div>
            </div>
            
            <hr class="my-4 text-light">
            
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary fw-800 px-5 rounded-pill text-uppercase ls-1 py-2">
                    <i class="fa-solid fa-plus me-2"></i> Create Category
                </button>
            </div>
            
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
