<?php
/**
 * User Dashboard (My Account)
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

// Force login
requireLogin();

$user = currentUser();
if (!$user) {
    logoutUser();
    setFlash('info', 'Please login to access your account.');
    redirect(url('login.php'));
}

$db   = getDB();

// Handle Profile Update Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    validateCsrf();
    
    $name    = inputStr('name', '', 'POST');
    $phone   = inputStr('phone', '', 'POST');
    $address = inputStr('address', '', 'POST');
    $city    = inputStr('city', '', 'POST');
    $state   = inputStr('state', '', 'POST');
    $pincode = inputStr('pincode', '', 'POST');
    
    // Validate Phone if provided
    if ($phone && !preg_match('/^[6-9]\d{9}$/', $phone)) {
        setFlash('error', 'Please enter a valid 10-digit mobile number.');
    } else {
        $stmt = $db->prepare("
            UPDATE users SET 
            name = ?, phone = ?, address = ?, city = ?, state = ?, pincode = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $phone, $address, $city, $state, $pincode, $user['id']]);
        
        // Update session data
        $_SESSION['user']['name'] = $name;
        
        setFlash('success', 'Profile updated successfully.');
        redirect(url('my-account.php'));
    }
}

// Handle Password Change Verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    validateCsrf();
    
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    $stmtUser = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmtUser->execute([$user['id']]);
    $dbPass = $stmtUser->fetchColumn();
    
    if (!password_verify($current, $dbPass)) {
        setFlash('error', 'Current password is incorrect.');
    } elseif (strlen($new) < 8) {
        setFlash('error', 'New password must be at least 8 characters long.');
    } elseif ($new !== $confirm) {
        setFlash('error', 'New passwords do not match.');
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $user['id']]);
        setFlash('success', 'Password changed successfully!');
        redirect(url('my-account.php'));
    }
}

// Fetch stats for dashboard
$stmtStats = $db->prepare("SELECT COUNT(*) as total_orders FROM orders WHERE user_id = ?");
$stmtStats->execute([$user['id']]);
$totalOrders = $stmtStats->fetchColumn();

// Fetch recent 3 orders
$stmtRec = $db->prepare("SELECT *, COALESCE(total_amount, total) AS total_amount FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
$stmtRec->execute([$user['id']]);
$recentOrders = $stmtRec->fetchAll();

$pageTitle = 'My Account';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="bg-white rounded-xl shadow-sm border border-light p-4 position-sticky" style="top: 90px;">
                <div class="text-center mb-4 pb-4 border-bottom">
                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem; font-weight: 800;">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                    <h5 class="fw-800 text-dark mb-1"><?= e($user['name']) ?></h5>
                    <p class="text-secondary small mb-0"><?= e($user['email']) ?></p>
                </div>
                
                <nav class="nav flex-column account-sidebar gap-1">
                    <a class="nav-link active d-flex align-items-center" href="<?= url('my-account.php') ?>">
                        <i class="fa-regular fa-user" style="width:20px;"></i> Dashboard / Profile
                    </a>
                    <a class="nav-link d-flex align-items-center" href="<?= url('my-orders.php') ?>">
                        <i class="fa-solid fa-box-open" style="width:20px;"></i> My Orders 
                        <span class="badge bg-light text-dark ms-auto rounded-pill border"><?= $totalOrders ?></span>
                    </a>
                    <a class="nav-link d-flex align-items-center" href="<?= url('wishlist.php') ?>">
                        <i class="fa-regular fa-heart" style="width:20px;"></i> Wishlist
                    </a>
                    <a class="nav-link d-flex align-items-center text-danger mt-4" href="<?= url('login.php?action=logout') ?>">
                        <i class="fa-solid fa-arrow-right-from-bracket" style="width:20px;"></i> Logout
                    </a>
                </nav>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <h3 class="fw-800 text-dark mb-4">My Dashboard</h3>
            
            <div class="row g-4 mb-5">
                <!-- Info Card -->
                <div class="col-md-6">
                    <div class="bg-primary bg-gradient text-white rounded-xl p-4 h-100 shadow-sm position-relative overflow-hidden">
                        <i class="fa-solid fa-boxes-stacked position-absolute" style="font-size: 8rem; right: -20px; bottom: -20px; opacity: 0.1;"></i>
                        <h6 class="text-white opacity-75 fw-600 mb-1">Total Orders</h6>
                        <h2 class="display-4 fw-800 mb-0"><?= $totalOrders ?></h2>
                        <a href="<?= url('my-orders.php') ?>" class="text-white text-decoration-none border-bottom border-light pb-1 small fw-600 mt-3 d-inline-block position-relative z-1">View All Orders</a>
                    </div>
                </div>
                <!-- Support Card -->
                <div class="col-md-6">
                    <div class="bg-white rounded-xl shadow-sm border border-light p-4 h-100 d-flex flex-column justify-content-center">
                        <h5 class="fw-800 text-dark mb-2">Need Help?</h5>
                        <p class="text-secondary small mb-3">If you have any questions regarding your account or orders, feel free to contact us.</p>
                        <div>
                            <a href="<?= url('contact-us.php') ?>" class="btn btn-outline-primary fw-600 rounded-pill px-4">Contact Support</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders Snippet -->
            <?php if ($recentOrders): ?>
            <div class="bg-white rounded-xl shadow-sm border border-light p-4 mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-800 text-dark mb-0">Recent Orders</h5>
                    <a href="<?= url('my-orders.php') ?>" class="text-primary fw-600 small text-decoration-none">View All</a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-secondary fs-8 text-uppercase ls-1">
                            <tr>
                                <th class="py-3 px-3">Order #</th>
                                <th class="py-3 px-3">Date</th>
                                <th class="py-3 px-3">Total</th>
                                <th class="py-3 px-3">Status</th>
                                <th class="py-3 px-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recentOrders as $order): ?>
                            <tr>
                                <td class="px-3 fw-600 text-dark">#<?= e($order['order_number']) ?></td>
                                <td class="px-3 text-secondary"><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                <td class="px-3 fw-700 text-primary"><?= formatPrice($order['total_amount']) ?></td>
                                <td class="px-3"><?= orderStatusLabel($order['status']) ?></td>
                                <td class="px-3">
                                    <a href="<?= url('my-orders.php?view=' . $order['order_number']) ?>" class="btn btn-sm btn-light border fw-600 text-secondary hover-primary">View</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Profile Tabs -->
            <div class="bg-white rounded-xl shadow-sm border border-light p-0 overflow-hidden">
                <div class="px-4 pt-4 pb-0 bg-light border-bottom">
                    <ul class="nav nav-tabs border-0" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-600 bg-white border-bottom-0 text-dark rounded-top" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab">Account & Address</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-600 border-0 text-secondary hover-primary rounded-top" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">Security</button>
                        </li>
                    </ul>
                </div>
                
                <div class="tab-content p-4 p-md-5" id="profileTabsContent">
                    
                    <!-- Details Form -->
                    <div class="tab-pane fade show active" id="details" role="tabpanel">
                        <form action="<?= url('my-account.php') ?>" method="POST">
                            <?= csrfField() ?>
                            <input type="hidden" name="update_profile" value="1">
                            
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-600">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-600">Email Address (Read-only)</label>
                                    <input type="email" class="form-control bg-light" value="<?= e($user['email']) ?>" readonly>
                                    <div class="form-text small text-muted mt-1">Contact support to change email.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-600">Mobile Number</label>
                                    <input type="tel" name="phone" class="form-control" value="<?= e($user['phone']) ?>" pattern="^[6-9]\d{9}$">
                                    <div class="form-text small text-muted mt-1">10 digit Indian number</div>
                                </div>
                                
                                <div class="col-12 mt-4">
                                    <h6 class="fw-800 text-dark border-bottom pb-2 mb-3">Saved Address Details</h6>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-600">Street Address</label>
                                    <input type="text" name="address" class="form-control" value="<?= e($user['address']) ?>" placeholder="House no, Street name">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-600">City</label>
                                    <input type="text" name="city" class="form-control" value="<?= e($user['city']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-600">State</label>
                                    <select name="state" class="form-select">
                                        <option value="">Select State</option>
                                        <?php 
                                        $states = ['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Delhi','Jammu & Kashmir'];
                                        foreach($states as $st) {
                                            $sel = ($user['state'] == $st) ? 'selected' : '';
                                            echo "<option value=\"$st\" $sel>$st</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-600">PIN Code</label>
                                    <input type="text" name="pincode" class="form-control" value="<?= e($user['pincode']) ?>" pattern="\d{6}">
                                </div>
                                
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary fw-700 rounded-pill px-4">
                                        <i class="fa-solid fa-floppy-disk me-2"></i> Save Changes
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Security Form -->
                    <div class="tab-pane fade" id="security" role="tabpanel">
                        <form action="<?= url('my-account.php') ?>" method="POST" style="max-width: 500px;">
                            <?= csrfField() ?>
                            <input type="hidden" name="change_password" value="1">
                            
                            <h6 class="fw-800 text-dark border-bottom pb-2 mb-4">Change Password</h6>
                            
                            <div class="mb-3">
                                <label class="form-label fw-600">Current Password <span class="text-danger">*</span></label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-600">New Password <span class="text-danger">*</span></label>
                                <input type="password" name="new_password" class="form-control" required minlength="8">
                                <div class="form-text small text-muted mt-1">Must be at least 8 characters.</div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-600">Confirm New Password <span class="text-danger">*</span></label>
                                <input type="password" name="confirm_password" class="form-control" required minlength="8">
                            </div>
                            
                            <button type="submit" class="btn btn-dark fw-700 rounded-pill px-4">
                                <i class="fa-solid fa-key me-2"></i> Update Password
                            </button>
                        </form>
                    </div>
                    
                </div>
            </div>
            
        </div>
    </div>
    
</div>

<?php 
$extraJs = <<<JS
<script>
// Style active tab link matching bs logic if customized
document.querySelectorAll('#profileTabs .nav-link').forEach(tab => {
    tab.addEventListener('show.bs.tab', event => {
        document.querySelectorAll('#profileTabs .nav-link').forEach(t => {
            t.classList.remove('bg-white', 'text-dark');
            t.classList.add('border-0', 'text-secondary');
        });
        event.target.classList.remove('border-0', 'text-secondary');
        event.target.classList.add('bg-white', 'text-dark', 'border-bottom-0');
    });
});
</script>
JS;
require_once __DIR__ . '/includes/footer.php'; 
?>
