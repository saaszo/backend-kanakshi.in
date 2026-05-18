<?php
/**
 * Admin Users List
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();

// Handle User Block/Unblock
if (isset($_GET['toggle_active']) && is_numeric($_GET['toggle_active'])) {
    $userId = (int)$_GET['toggle_active'];
    
    // Prevent blocking oneself or a protected master account
    $stmtCheck = $db->prepare("SELECT role, is_protected FROM users WHERE id = ?");
    $stmtCheck->execute([$userId]);
    $userCheck = $stmtCheck->fetch();

    if ($userId === $_SESSION['user']['id']) {
        setFlash('error', 'You cannot block your own admin account.');
    } elseif ($userCheck && $userCheck['is_protected'] == 1) {
        setFlash('error', 'This account is protected by Saaszo and cannot be modified.');
    } else {
        $stmtStatus = $db->prepare("SELECT is_active FROM users WHERE id = ?");
        $stmtStatus->execute([$userId]);
        $curr = $stmtStatus->fetchColumn();
        
        if ($curr !== false) {
            $newStatus = $curr == 1 ? 0 : 1;
            $db->prepare("UPDATE users SET is_active = ? WHERE id = ?")->execute([$newStatus, $userId]);
            $action = $newStatus == 1 ? 'activated' : 'blocked';
            setFlash('success', "User account $action successfully.");
        }
    }
    redirect(url('admin/users/index.php'));
}

// Fetch Pagination & Search
$page   = (int)inputStr('page', 1, 'GET');
$search = inputStr('q', '', 'GET');
$limit  = 15;
$offset = ($page - 1) * $limit;

$where = "role = 'customer'";
$params = [];

if ($search) {
    $where .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Count Total Customers
$stmtTotal = $db->prepare("SELECT COUNT(*) FROM users WHERE $where");
$stmtTotal->execute($params);
$totalRows = $stmtTotal->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Fetch Users (Customers) with total spent
$sql = "SELECT u.*, 
               (SELECT COUNT(*) FROM orders WHERE user_id = u.id AND status NOT IN ('cancelled', 'refunded')) as order_count,
               (SELECT SUM(total) FROM orders WHERE user_id = u.id AND status NOT IN ('cancelled', 'refunded')) as total_spent
        FROM users u 
        WHERE $where 
        ORDER BY u.created_at DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$pageTitle = 'Manage Customers';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-900 text-dark mb-1 ls-1">Client Registry</h3>
        <p class="text-muted small mb-0 fw-700 text-uppercase ls-2">Managing Your Valued Patrons</p>
    </div>
</div>

<div class="admin-card mb-4 shadow-sm border-0 overflow-hidden">
    <!-- Search & Filter Bar -->
    <div class="p-4 border-bottom bg-white">
        <form action="" method="GET" class="row g-3">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="q" class="form-control border-light shadow-none" placeholder="Search by Client Name, Email or Phone..." value="<?= e($search) ?>">
                    <?php if($search): ?>
                        <a href="<?= url('admin/users/index.php') ?>" class="btn btn-outline-danger border-light border-start-0 shadow-none"><i class="fa-solid fa-xmark"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-dark w-100 fw-900 rounded-pill text-uppercase ls-1 fs-9 py-2">Consult Registry</button>
            </div>
        </form>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted fs-9 text-uppercase ls-2">
                <tr>
                    <th class="py-3 px-4" style="width: 35%;">Client Identity</th>
                    <th class="py-3 px-3">Communication</th>
                    <th class="py-3 px-3 text-center">Acquisitions</th>
                    <th class="py-3 px-3 text-center">Lifetime Value</th>
                    <th class="py-3 px-3 text-center">Protocol</th>
                    <th class="py-3 px-4 text-end">Control</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($users)): ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted fw-600">The registry is currently empty for this search.</td></tr>
                <?php else: ?>
                    <?php foreach($users as $u): ?>
                        <tr>
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-900 shadow-sm" style="width: 48px; height: 48px; border: 2px solid white;">
                                        <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-900 text-dark mb-0 ls-1 fs-7"><?= e($u['name']) ?></div>
                                        <div class="text-muted small fw-700 text-uppercase ls-1" style="font-size: 0.65rem;">Joined <?= date('M Y', strtotime($u['created_at'])) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <div class="text-dark fw-600 small mb-1"><i class="fa-regular fa-envelope me-2 text-muted"></i> <?= e($u['email']) ?></div>
                                <?php if($u['phone']): ?>
                                    <div class="text-muted small fw-700"><i class="fa-solid fa-mobile-screen me-2 opacity-50"></i> <?= e($u['phone']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <div class="fw-900 text-dark fs-6"><?= $u['order_count'] ?></div>
                                <div class="text-muted small fw-700 text-uppercase ls-1" style="font-size: 0.6rem;">Orders</div>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <div class="fw-900 text-primary fs-6"><?= formatPrice($u['total_spent'] ?: 0) ?></div>
                                <div class="text-muted small fw-700 text-uppercase ls-1" style="font-size: 0.6rem;">Cumulative</div>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <?php if($u['is_active']): ?>
                                    <span class="badge-soft badge-soft-success rounded-pill fw-900 fs-9 ls-1 px-3">Authorized</span>
                                <?php else: ?>
                                    <span class="badge-soft badge-soft-danger rounded-pill fw-900 fs-9 ls-1 px-3">Restricted</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <?php if($u['is_active']): ?>
                                    <button type="button" onclick="moderateUser(<?= $u['id'] ?>, 'block')" class="btn btn-outline-danger btn-sm fw-900 rounded-pill px-3 ls-1 text-uppercase fs-9 border-2">Block access</button>
                                <?php else: ?>
                                    <button type="button" onclick="moderateUser(<?= $u['id'] ?>, 'unblock')" class="btn btn-outline-success btn-sm fw-900 rounded-pill px-3 ls-1 text-uppercase fs-9 border-2">Restore</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Record Pagination -->
    <?php if($totalPages > 1): ?>
        <div class="px-4 py-3 border-top d-flex justify-content-between align-items-center bg-white">
            <div class="text-muted fw-800 fs-9 text-uppercase ls-1">Record <?= ($offset + 1) ?> - <?= ($offset + count($users)) ?> of <?= $totalRows ?></div>
            <div>
                <?= paginationLinks($page, $totalPages, url('admin/users/index.php') . '?' . ($search ? 'q='.urlencode($search).'&' : '')) ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Phase 17 SweetAlert Moderation Protocol
function moderateUser(userId, action) {
    const title = action === 'block' ? 'Restrict Client Access?' : 'Restore Client Access?';
    const text = action === 'block' ? 'This user will be unable to log in or place new acquisitions.' : 'This will reinstate the client\'s ability to engage with the boutique.';
    const confirmText = action === 'block' ? 'Yes, Restrict Client' : 'Yes, Restore Access';
    
    Swal.fire({
        title: title,
        text: text,
        icon: action === 'block' ? 'warning' : 'question',
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
            window.location.href = '<?= url('admin/users/index.php?toggle_active=') ?>' + userId;
        }
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
