<?php
/**
 * Admin Pages List
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();

// Fetch all pages
$stmt = $db->query("SELECT * FROM pages ORDER BY title ASC");
$pages = $stmt->fetchAll();

$pageTitle = 'Dynamic Pages';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-900 text-dark mb-1 ls-1">Content Archive</h3>
        <p class="text-muted small mb-0 fw-700 text-uppercase ls-2">Managing the Boutique's Narrative</p>
    </div>
</div>

<div class="admin-card mb-4 shadow-sm border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted fs-9 text-uppercase ls-2">
                <tr>
                    <th class="py-3 px-4">Document Title</th>
                    <th class="py-3 px-3">Identifier (Slug)</th>
                    <th class="py-3 px-3 text-center">Status</th>
                    <th class="py-3 px-3">Last Revised</th>
                    <th class="py-3 px-4 text-end">Controls</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($pages)): ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted fw-600">No dynamic pages have been drafted yet.</td></tr>
                <?php else: ?>
                    <?php foreach($pages as $page): ?>
                        <tr class="bg-white border-top animate-in">
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-primary flex-shrink-0 shadow-sm border" style="width: 42px; height: 42px;">
                                        <i class="fa-solid fa-file-lines opacity-75"></i>
                                    </div>
                                    <div>
                                        <div class="fw-900 text-dark fs-6 ls-1"><?= e($page['title']) ?></div>
                                        <div class="fs-9 text-muted fw-700 text-uppercase opacity-50"><?= e($page['meta_title']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <code class="text-primary fw-700 fs-9 ls-1">/<?= e($page['slug']) ?></code>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <?php if($page['is_active']): ?>
                                    <span class="badge-soft badge-soft-success px-3 py-1 rounded-pill fw-900 fs-10 text-uppercase ls-1">Published</span>
                                <?php else: ?>
                                    <span class="badge-soft badge-soft-warning px-3 py-1 rounded-pill fw-900 fs-10 text-uppercase ls-1">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-3">
                                <div class="fs-9 text-muted fw-700 text-uppercase ls-1"><?= date('M d, Y', strtotime($page['updated_at'])) ?></div>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="<?= dynamicPageUrl($page['slug']) ?>" target="_blank" class="btn btn-outline-light btn-sm rounded-pill px-3 fw-900 ls-1 fs-10 text-uppercase py-2 border-2 text-dark">
                                        <i class="fa-solid fa-eye"></i> View
                                    </a>
                                    <a href="<?= url('admin/pages/edit.php?id=' . $page['id']) ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-900 ls-1 fs-10 text-uppercase py-2 shadow-sm border-2">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
