<?php
/**
 * Admin: Manage Subscribers
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/auth.php';

// Ensure user is admin
requireAdmin();

$db = getDB();
$error = '';
$success = '';

// Handle Delete/Toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token.";
    } else {
        $id = (int)$_POST['id'];
        
        if ($_POST['action'] === 'delete') {
            $stmt = $db->prepare("DELETE FROM subscribers WHERE id = ?");
            if ($stmt->execute([$id])) {
                $success = "Subscriber deleted successfully.";
            } else {
                $error = "Failed to delete subscriber.";
            }
        } elseif ($_POST['action'] === 'toggle') {
            $stmt = $db->prepare("UPDATE subscribers SET is_active = NOT is_active WHERE id = ?");
            if ($stmt->execute([$id])) {
                $success = "Subscriber status updated.";
            } else {
                $error = "Failed to update status.";
            }
        }
    }
}

// Fetch all subscribers
$stmt = $db->query("SELECT * FROM subscribers ORDER BY created_at DESC");
$subscribers = $stmt->fetchAll();

$pageTitle = "Newsletter Subscribers";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fa-solid fa-envelope-open-text me-2"></i> Newsletter Subscribers</h2>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Email Address</th>
                    <th>Subscribed On</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($subscribers)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No subscribers found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($subscribers as $sub): ?>
                        <tr>
                            <td class="ps-4">#<?= $sub['id'] ?></td>
                            <td class="fw-500"><?= e($sub['email']) ?></td>
                            <td><?= date('d M Y, h:i A', strtotime($sub['created_at'])) ?></td>
                            <td>
                                <?php if ($sub['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Unsubscribed</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <form action="" method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <input type="hidden" name="id" value="<?= $sub['id'] ?>">
                                    <input type="hidden" name="action" value="toggle">
                                    <button type="submit" class="btn btn-sm <?= $sub['is_active'] ? 'btn-outline-warning' : 'btn-outline-success' ?> me-1" title="<?= $sub['is_active'] ? 'Unsubscribe' : 'Resubscribe' ?>">
                                        <i class="fa-solid <?= $sub['is_active'] ? 'fa-ban' : 'fa-check' ?>"></i>
                                    </button>
                                </form>
                                <form action="" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this subscriber?');">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <input type="hidden" name="id" value="<?= $sub['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
