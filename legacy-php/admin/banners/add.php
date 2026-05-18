<?php
/**
 * Admin Add Banner
 */
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    
    $title       = inputStr('title', '', 'POST');
    $subtitle    = inputStr('subtitle', '', 'POST');
    $link        = inputStr('link', '', 'POST');
    $button_text = inputStr('button_text', '', 'POST');
    $position    = inputStr('position', 'hero', 'POST');
    $sort_order  = (int)inputStr('sort_order', 0, 'POST');
    $is_active   = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle Image
    if (empty($_FILES['image']['name'])) {
        setFlash('error', 'Banner image is required.');
        redirect(url('admin/banners/add.php'));
    }
    
    $res = uploadImage($_FILES['image'], 'uploads/banners/');
    if (!$res['success']) {
        setFlash('error', $res['message']);
        redirect(url('admin/banners/add.php'));
    }
    
    $imagePath = $res['path'];
    
    try {
        $stmt = $db->prepare("
            INSERT INTO banners (title, subtitle, image, link, button_text, position, is_active, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $title, $subtitle, $imagePath, $link, $button_text, $position, $is_active, $sort_order
        ]);
        
        setFlash('success', 'Banner created successfully.');
        redirect(url('admin/banners/index.php'));
    } catch (PDOException $e) {
        setFlash('error', 'Database error.');
    }
}

$pageTitle = 'Add Banner';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-800 text-dark mb-0">Add Banner</h3>
    <a href="<?= url('admin/banners/index.php') ?>" class="btn btn-light border fw-600 rounded-pill px-4">
        <i class="fa-solid fa-arrow-left me-2"></i> Back
    </a>
</div>

<div class="admin-card p-4">
    <form action="<?= url('admin/banners/add.php') ?>" method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        
        <div class="row g-4">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label fw-600">Banner Image <span class="text-danger">*</span></label>
                    <input type="file" name="image" class="form-control" accept="image/*" required>
                    <div class="form-text small text-muted">Recommended sizes: Hero (1920x800), Offer (800x400). Max 2MB.</div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-600">Position <span class="text-danger">*</span></label>
                    <select name="position" class="form-select" required>
                        <option value="hero">Hero (Top Carousel)</option>
                        <option value="offer">Offer (Grid Below Featured)</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-600">Title (Optional)</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Mega Summer Sale">
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-600">Subtitle (Optional)</label>
                    <input type="text" name="subtitle" class="form-control" placeholder="e.g. Up to 50% Off">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label fw-600">Link / URL (Optional)</label>
                    <input type="text" name="link" class="form-control" placeholder="e.g. /products.php?category=electronics">
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-600">Button Text (Optional)</label>
                    <input type="text" name="button_text" class="form-control" placeholder="e.g. Shop Now">
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-600">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="0">
                    <div class="form-text small text-muted">Lower numbers appear first.</div>
                </div>
                
                <div class="form-check form-switch mt-4 mb-4">
                    <input class="form-check-input" type="checkbox" name="is_active" id="isActive" checked value="1">
                    <label class="form-check-label fw-600 ms-2" for="isActive">Active (Visible)</label>
                </div>
            </div>
        </div>
        
        <hr class="border-secondary mb-4 opacity-25">
        
        <button type="submit" class="btn btn-primary fw-800 px-5 rounded-pill text-uppercase ls-1 py-3 shadow-sm">
            <i class="fa-solid fa-floppy-disk me-2"></i> Save Banner
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
