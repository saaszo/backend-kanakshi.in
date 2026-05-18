<?php
/**
 * Admin Review Moderation
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    validateCsrf();
    $id = (int)$_GET['id'];
    $act = $_GET['action'];
    
    if ($act === 'approve') {
        $db->prepare("UPDATE product_reviews SET status = 'approved' WHERE id = ?")->execute([$id]);
        setFlash('success', 'Review approved.');
    } elseif ($act === 'hide') {
        $db->prepare("UPDATE product_reviews SET status = 'pending' WHERE id = ?")->execute([$id]);
        setFlash('success', 'Review hidden.');
    } elseif ($act === 'delete') {
        $db->prepare("DELETE FROM product_reviews WHERE id = ?")->execute([$id]);
        setFlash('success', 'Review deleted.');
    }
    
    // Recalculate average rating for the product
    $stmt = $db->prepare("SELECT product_id FROM product_reviews WHERE id = ?");
    $stmt->execute([$id]);
    $pid = $stmt->fetchColumn();
    // (Actual recalculation logic could be added here if needed, or rely on real-time calc)
    
    redirect(url('admin/reviews/index.php'));
}

// Fetch Reviews
$stmt = $db->query("
    SELECT r.*, u.name as user_name, p.name as product_name, p.slug as product_slug
    FROM product_reviews r
    JOIN users u ON r.user_id = u.id
    JOIN products p ON r.product_id = p.id
    ORDER BY r.status ASC, r.created_at DESC
");
$reviews = $stmt->fetchAll();

$pageTitle = 'Review Moderation';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-900 text-dark mb-1 ls-1">Brand Trust Center</h3>
        <p class="text-muted small mb-0 fw-700 text-uppercase ls-2">Moderating Client Testimonies</p>
    </div>
</div>

<div class="admin-card mb-4 shadow-sm border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted fs-9 text-uppercase ls-2">
                <tr>
                    <th class="py-3 px-4" style="width: 30%;">Client & Masterpiece</th>
                    <th class="py-3 px-3 text-center">Satisfaction</th>
                    <th class="py-3 px-3">Testimony</th>
                    <th class="py-3 px-3 text-center">Audit Status</th>
                    <th class="py-3 px-4 text-end">Control</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($reviews)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted fw-600">No client testimonies have been recorded yet.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach($reviews as $r): ?>
                    <tr class="<?= $r['status'] === 'approved' ? '' : 'bg-primary bg-opacity-5' ?> animate-in">
                        <td class="px-4 py-3">
                            <div class="fw-900 text-dark mb-1 ls-1"><?= e($r['user_name']) ?></div>
                            <a href="<?= url('product.php?slug='.$r['product_slug']) ?>" target="_blank" class="small fw-800 text-primary text-decoration-none text-uppercase ls-1" style="font-size: 0.65rem;">
                                <?= e($r['product_name']) ?> <i class="fa-solid fa-external-link fs-10 ms-1 opacity-50"></i>
                            </a>
                            <div class="text-muted small fw-700 mt-1" style="font-size: 0.65rem;"><?= date('d F, Y', strtotime($r['created_at'])) ?></div>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <div class="text-warning mb-1" style="font-size: 0.7rem;"><?= starRating($r['rating']) ?></div>
                            <div class="fw-900 text-dark fs-8"><?= $r['rating'] ?> / 5</div>
                        </td>
                        <td class="px-3 py-3">
                            <div class="fs-8 text-secondary fw-600 lh-sm fst-italic shadow-text" style="max-width: 320px;">
                                "<?= nl2br(e($r['comment'])) ?>"
                            </div>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <?php if($r['status'] === 'approved'): ?>
                                <span class="badge-soft badge-soft-success rounded-pill fw-900 fs-9 ls-1 px-3">Public</span>
                            <?php else: ?>
                                <span class="badge-soft badge-soft-warning rounded-pill fw-900 fs-9 ls-1 px-3">In Review</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <?php if($r['status'] !== 'approved'): ?>
                                    <button type="button" onclick="moderateReview(<?= $r['id'] ?>, 'approve')" class="btn btn-success btn-sm fw-900 rounded-pill px-3 ls-1 text-uppercase fs-9 py-2 shadow-sm">
                                        Approve
                                    </button>
                                <?php else: ?>
                                    <button type="button" onclick="moderateReview(<?= $r['id'] ?>, 'hide')" class="btn btn-outline-secondary btn-sm fw-900 rounded-pill px-3 ls-1 text-uppercase fs-9 py-2">
                                        Archive
                                    </button>
                                <?php endif; ?>
                                <button type="button" onclick="moderateReview(<?= $r['id'] ?>, 'delete')" class="btn btn-outline-danger btn-sm fw-900 rounded-pill px-3 ls-1 text-uppercase fs-9 py-2 border-2">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Phase 17 SweetAlert Brand Trust Protocol
function moderateReview(reviewId, action) {
    let title, text, confirmText, icon;
    const csrf = '<?= csrfToken() ?>';
    
    switch(action) {
        case 'approve':
            title = 'Approve Testimony?';
            text = 'This review will become visible to all boutique visitors immediately.';
            confirmText = 'Yes, Publish Testimony';
            icon = 'success';
            break;
        case 'hide':
            title = 'Archive Testimony?';
            text = 'This review will be hidden from the public boutique showcase.';
            confirmText = 'Yes, Archive';
            icon = 'info';
            break;
        case 'delete':
            title = 'Purge Testimony?';
            text = 'This record will be permanently removed from the brand vault.';
            confirmText = 'Yes, Purge Permanently';
            icon = 'warning';
            break;
    }
    
    Swal.fire({
        title: title,
        text: text,
        icon: icon,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: 'Cancel Protocol',
        customClass: {
            confirmButton: 'btn btn-primary px-4 py-2 rounded-pill fw-900 ls-1 shadow-gold',
            cancelButton: 'btn btn-light px-4 py-2 rounded-pill fw-900 ls-1 border ms-2'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= url('admin/reviews/index.php?action=') ?>' + action + '&id=' + reviewId + '&csrf=' + '<?= csrfToken() ?>';
        }
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
