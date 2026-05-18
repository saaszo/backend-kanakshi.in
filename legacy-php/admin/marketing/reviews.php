<?php
/**
 * Admin: Product Reviews Moderation
 */
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$db = getDB();

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);
    
    if ($id > 0) {
        if ($action === 'approve') {
            $db->prepare("UPDATE product_reviews SET status = 'approved' WHERE id = ?")->execute([$id]);
            
            // Recalculate Product Stats
            $rev = $db->query("SELECT product_id FROM product_reviews WHERE id = $id")->fetch();
            if ($rev) {
                $pid = $rev['product_id'];
                $db->prepare("
                    UPDATE products p 
                    SET 
                        avg_rating = (SELECT IFNULL(AVG(rating), 0) FROM product_reviews WHERE product_id = ? AND status = 'approved'),
                        review_count = (SELECT COUNT(*) FROM product_reviews WHERE product_id = ? AND status = 'approved')
                    WHERE id = ?
                ")->execute([$pid, $pid, $pid]);
            }
            
            setFlash('success', 'Review approved and product stats updated.');
        } elseif ($action === 'reject') {
            $db->prepare("UPDATE product_reviews SET status = 'rejected' WHERE id = ?")->execute([$id]);
            setFlash('info', 'Review rejected.');
        } elseif ($action === 'delete') {
            $db->prepare("DELETE FROM product_reviews WHERE id = ?")->execute([$id]);
            setFlash('success', 'Review deleted permanently.');
        }
    }
    redirect(url('admin/marketing/reviews.php'));
}

// Fetch Reviews
$stmt = $db->query("
    SELECT r.*, p.name as product_name, p.slug as product_slug, u.name as user_name, u.email as user_email
    FROM product_reviews r
    JOIN products p ON r.product_id = p.id
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
");
$reviews = $stmt->fetchAll();

$pageTitle = 'Manage Reviews';
require_once __DIR__ . '/../../admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-800 text-dark mb-0">Product <span class="text-primary">Reviews</span></h3>
        <p class="text-secondary small fw-600 mb-0">Moderate customer feedback and build social proof for your jewelry collection.</p>
    </div>
</div>

<div class="admin-card overflow-hidden shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-secondary fs-8 text-uppercase ls-1">
                <tr>
                    <th class="py-3 px-4">Product</th>
                    <th class="py-3 px-3">Customer</th>
                    <th class="py-3 px-3">Rating</th>
                    <th class="py-3 px-3" style="width: 300px;">Review Content</th>
                    <th class="py-3 px-3 text-center">Status</th>
                    <th class="py-3 px-4 text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($reviews)): ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted fw-500">No reviews submitted yet.</td></tr>
                <?php endif; ?>
                <?php foreach($reviews as $r): ?>
                    <tr>
                        <td class="px-4 py-3">
                            <div class="fw-700 text-dark small text-truncate" style="max-width: 150px;"><?= e($r['product_name']) ?></div>
                            <a href="<?= url('product.php?slug='.$r['product_slug']) ?>" target="_blank" class="fs-9 text-primary text-decoration-none fw-600">View Product &rarr;</a>
                        </td>
                        <td class="px-3 py-3">
                            <div class="fw-700 text-dark small"><?= e($r['user_name']) ?></div>
                            <div class="fs-9 text-secondary"><?= e($r['user_email']) ?></div>
                        </td>
                        <td class="px-3 py-3">
                            <div class="text-warning small">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="fa-<?= $i <= $r['rating'] ? 'solid' : 'regular' ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                        </td>
                        <td class="px-3 py-3 lh-sm">
                            <div class="text-dark small fw-500 border-start ps-3 border-2"><?= e($r['comment']) ?></div>
                            <?php 
                                $imgs = json_decode($r['images'] ?? '[]', true);
                                if(!empty($imgs)): 
                            ?>
                                <div class="mt-2 d-flex gap-1">
                                    <?php foreach($imgs as $img): ?>
                                        <img src="<?= url($img) ?>" class="rounded border p-1" style="width: 40px; height: 40px; object-fit: cover;">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <?php
                                $badge = 'bg-warning text-dark';
                                if($r['status'] == 'approved') $badge = 'bg-success';
                                if($r['status'] == 'rejected') $badge = 'bg-danger';
                            ?>
                            <span class="badge rounded-pill px-3 py-2 <?= $badge ?> text-uppercase ls-1 fs-9">
                                <?= e($r['status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border fw-700 rounded-pill px-3 dropdown-toggle shadow-none" data-bs-toggle="dropdown">Action</button>
                                <ul class="dropdown-menu shadow border-0">
                                    <?php if($r['status'] !== 'approved'): ?>
                                        <li><form method="POST"><?= csrfField() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="approve"><button class="dropdown-item fw-600 text-success"><i class="fa-solid fa-check me-2"></i> Approve</button></form></li>
                                    <?php endif; ?>
                                    <?php if($r['status'] !== 'rejected'): ?>
                                        <li><form method="POST"><?= csrfField() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="reject"><button class="dropdown-item fw-600 text-danger"><i class="fa-solid fa-ban me-2"></i> Reject</button></form></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><form method="POST" onsubmit="return confirm('Delete this review permanently?');"><?= csrfField() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="delete"><button class="dropdown-item fw-600 text-dark"><i class="fa-solid fa-trash-can me-2"></i> Delete</button></form></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../admin/includes/footer.php'; ?>
