<?php
/**
 * Admin Page Edit
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch page
$stmt = $db->prepare("SELECT * FROM pages WHERE id = ?");
$stmt->execute([$id]);
$page = $stmt->fetch();

if (!$page) {
    setFlash('error', 'Page not found.');
    redirect(url('admin/pages/index.php'));
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = trim($_POST['title']);
    $content    = $_POST['content']; // HTML allowed here for pages
    $metaTitle  = trim($_POST['meta_title']);
    $metaDesc   = trim($_POST['meta_desc']);
    $isActive   = isset($_POST['is_active']) ? 1 : 0;

    if (empty($title)) {
        setFlash('error', 'Title is mandatory.');
    } else {
        $stmt = $db->prepare("UPDATE pages SET title = ?, content = ?, meta_title = ?, meta_desc = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$title, $content, $metaTitle, $metaDesc, $isActive, $id]);
        
        setFlash('success', 'Page details updated successfully.');
        redirect(url('admin/pages/index.php'));
    }
}

$pageTitle = 'Sculpt Narrative: ' . $page['title'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-900 text-dark mb-1 ls-1">Edit Dynamic Content</h3>
        <p class="text-muted small mb-0 fw-700 text-uppercase ls-2">Curating the Boutique's Essential Documents</p>
    </div>
    <a href="<?= url('admin/pages/index.php') ?>" class="btn btn-outline-secondary fw-900 rounded-pill px-4 ls-1 fs-8 text-uppercase border-2 py-2">
        <i class="fa-solid fa-arrow-left me-2"></i> Archive List
    </a>
</div>

<form action="<?= url('admin/pages/edit.php?id=' . $id) ?>" method="POST" class="row">
    <div class="col-lg-8">
        <div class="admin-card mb-4 shadow-sm border-0">
            <div class="p-4">
                <div class="mb-4">
                    <label class="form-label fw-900 fs-9 text-uppercase ls-2 text-muted mb-2">Internal Title</label>
                    <input type="text" name="title" class="form-control fw-700 fs-6 py-3 border-2" value="<?= e($page['title']) ?>" placeholder="e.g. Terms & Conditions" required>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-900 fs-9 text-uppercase ls-2 text-muted mb-2">Draft the Narrative</label>
                    <textarea name="content" id="page-content" class="form-control" rows="15"><?= $page['content'] ?></textarea>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="admin-card mb-4 shadow-sm border-0">
            <div class="p-4 border-bottom">
                <h5 class="fw-900 fs-8 text-uppercase ls-2 mb-0">Visibility Protocol</h5>
            </div>
            <div class="p-4">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= $page['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label fw-900 fs-9 text-uppercase ls-2 text-dark ms-2" for="is_active">Public Access</label>
                </div>
                <p class="text-muted small mt-2 mb-0 fw-600">Toggle "Public" to grant customers visibility to this document.</p>
            </div>
        </div>

        <div class="admin-card mb-4 shadow-sm border-0">
            <div class="p-4 border-bottom">
                <h5 class="fw-900 fs-8 text-uppercase ls-2 mb-0">Metadata Architect</h5>
            </div>
            <div class="p-4">
                <div class="mb-4">
                    <label class="form-label fw-900 fs-9 text-uppercase ls-2 text-muted mb-2">Display Title (Meta)</label>
                    <input type="text" name="meta_title" class="form-control fw-600 border-2" value="<?= e($page['meta_title']) ?>" placeholder="Page title for search engines">
                </div>
                <div>
                    <label class="form-label fw-900 fs-9 text-uppercase ls-2 text-muted mb-2">Brief Summary (Meta Desc)</label>
                    <textarea name="meta_desc" class="form-control fw-600 border-2" rows="4" placeholder="Briefly summarize the page content..."><?= e($page['meta_desc']) ?></textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-900 rounded-pill py-3 ls-2 fs-7 text-uppercase shadow-gold mb-4 mt-2">
            <i class="fa-solid fa-cloud-arrow-up me-2"></i> Commit Changes
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    tinymce.init({
        selector: '#page-content',
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
        toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        menubar: false,
        skin: 'oxide',
        content_css: 'default',
        height: 500,
        branding: false,
        promotion: false
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
