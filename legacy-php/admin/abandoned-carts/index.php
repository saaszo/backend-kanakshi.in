<?php
/**
 * Admin: View Abandoned Carts
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

// Ensure user is admin
requireAdmin();

$db = getDB();
$error = '';
$success = '';

// Handle Delete or Mark as Recovered
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token.";
    } else {
        $id = (int)$_POST['id'];
        
        if ($_POST['action'] === 'delete') {
            $stmt = $db->prepare("DELETE FROM abandoned_carts WHERE id = ?");
            if ($stmt->execute([$id])) {
                $success = "Abandoned cart record deleted.";
            } else {
                $error = "Failed to delete record.";
            }
        } elseif ($_POST['action'] === 'recover') {
            $stmt = $db->prepare("UPDATE abandoned_carts SET is_recovered = 1 WHERE id = ?");
            if ($stmt->execute([$id])) {
                $success = "Cart marked as recovered.";
            } else {
                $error = "Failed to update record.";
            }
        }
    }
}

// Fetch all abandoned carts (not recovered, and older than 1 hour to be officially "abandoned")
$stmt = $db->query("
    SELECT ac.*, u.name as user_name, u.email as user_email, u.phone as user_phone 
    FROM abandoned_carts ac
    LEFT JOIN users u ON ac.user_id = u.id
    ORDER BY ac.last_active DESC
");
$carts = $stmt->fetchAll();

$pageTitle = "Abandoned Carts";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fa-solid fa-cart-arrow-down me-2 text-danger"></i> Abandoned Carts</h2>
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
                    <th class="ps-4">Customer</th>
                    <th>Cart Value</th>
                    <th>Last Active</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($carts)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No abandoned carts right now.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($carts as $c): 
                        // It's technically abandoned if inactive for 30 minutes
                        $isAbandoned = (time() - strtotime($c['last_active'])) > 1800;
                    ?>
                        <tr class="<?= $c['is_recovered'] ? 'opacity-50' : '' ?>">
                            <td class="ps-4">
                                <?php if ($c['user_name']): ?>
                                    <div class="fw-bold text-dark"><?= e($c['user_name']) ?></div>
                                    <div class="text-muted small"><?= e($c['user_email']) ?></div>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Guest User</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="fw-800 text-primary"><?= formatPrice($c['total_value']) ?></span>
                            </td>
                            <td>
                                <?= date('d M, h:i A', strtotime($c['last_active'])) ?>
                            </td>
                            <td>
                                <?php if ($c['is_recovered']): ?>
                                    <span class="badge bg-success">Recovered</span>
                                <?php elseif ($isAbandoned): ?>
                                    <span class="badge bg-danger">Abandoned</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Active Now</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <?php if (!$c['is_recovered']): ?>
                                    <form action="" method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <input type="hidden" name="action" value="recover">
                                        <button type="submit" class="btn btn-sm btn-outline-success me-1" title="Mark Recovered">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#cartModal<?= $c['id'] ?>" title="View Items">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <form action="" method="POST" class="d-inline" onsubmit="return confirm('Delete this record?');">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
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

<!-- Modals for viewing items (Rendered outside table to prevent HTML validation issues) -->
<?php if (!empty($carts)): ?>
    <?php foreach ($carts as $c): ?>
        <div class="modal fade" id="cartModal<?= $c['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-light">
                        <h5 class="modal-title fw-800">Cart Contents</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" tabindex="-1"></button>
                    </div>
                    <div class="modal-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php 
                            $items = json_decode($c['cart_data'], true);
                            if (is_array($items)):
                                foreach($items as $itm): 
                                    $price = (float)$itm['price'];
                                    $qty = (int)$itm['quantity'];
                            ?>
                            <li class="list-group-item p-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold"><?= e($itm['product_name'] ?? 'Unknown Product') ?></div>
                                    <div class="small text-muted">Qty: <?= $qty ?> &times; <?= formatPrice($price) ?></div>
                                </div>
                                <div class="fw-800 text-dark"><?= formatPrice($price * $qty) ?></div>
                            </li>
                            <?php 
                                endforeach; 
                            endif;
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
