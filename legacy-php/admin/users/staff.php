<?php
/**
 * Admin Staff/Team Management
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../includes/rbac.php';
requireAdmin();

$activeMenu = 'staff';


$db = getDB();

// Handle Staff Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_staff') {
    validateCsrf();
    $name = inputStr('name', '', 'POST');
    $email = inputStr('email', '', 'POST');
    $pass = $_POST['password'] ?? '';
    $role = inputStr('role', 'inventory_mgr', 'POST');

    if (!$name || !$email || !$pass) {
        setFlash('error', 'Name, Email, and Password are all required.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'Invalid email address.');
    } else {
        try {
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role, is_active) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$name, $email, $hashed, $role]);
            setFlash('success', 'Staff member added successfully.');
        } catch (Exception $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
    }
    redirect(url('admin/users/staff.php'));
}

// Fetch Staff (non-customers)
$stmt = $db->query("SELECT * FROM users WHERE role != 'customer' ORDER BY role ASC, name ASC");
$staff = $stmt->fetchAll();

$roles = getAdminRoles();

$pageTitle = 'Manage Staff & Permissions';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-800 text-dark mb-0">Staff & Department Management</h3>
    <button type="button" class="btn btn-primary fw-600 rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addStaffModal">
        <i class="fa-solid fa-user-plus me-2"></i> Add Team Member
    </button>
</div>

<div class="admin-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary fs-8 text-uppercase ls-1 border-bottom">
                <tr>
                    <th class="py-3 px-4">Name & Email</th>
                    <th class="py-3 px-3">Department Role</th>
                    <th class="py-3 px-3 text-center">Status</th>
                    <th class="py-3 px-4 text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($staff as $u): ?>
                    <tr>
                        <td class="px-4">
                            <div class="fw-700 text-dark"><?= e($u['name']) ?></div>
                            <div class="text-secondary small"><?= e($u['email']) ?></div>
                        </td>
                        <td class="px-3">
                            <span class="badge bg-dark rounded-pill fw-600 px-3"><?= $roles[$u['role']] ?? $u['role'] ?></span>
                        </td>
                        <td class="px-3 text-center">
                            <?php if($u['is_active']): ?>
                                <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-3 fw-700">ACTIVE</span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 rounded-pill px-3 fw-700">BLOCKED</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 text-end">
                            <?php if($_SESSION['user']['id'] !== $u['id']): ?>
                                <a href="<?= url('admin/users/index.php?toggle_active=' . $u['id']) ?>" class="btn btn-sm <?= $u['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?> rounded-pill px-3 fw-600">
                                    <?= $u['is_active'] ? 'Block' : 'Activate' ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">You (Self)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add Staff -->
<div class="modal fade" id="addStaffModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" class="modal-content admin-card border-0">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="add_staff">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-800">Add New Team Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-600">Full Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Rahul Sharma">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600">Email Address</label>
                    <input type="email" name="email" class="form-control" required placeholder="e.g. rahul@jewelry-store.com">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600">Initial Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-600">Department / Role</label>
                    <select name="role" class="form-select" required>
                        <?php foreach($roles as $key => $lbl): ?>
                            <option value="<?= $key ?>"><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-top p-3">
                <button type="button" class="btn btn-light fw-600 rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary fw-700 rounded-pill px-4">Create Account</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
