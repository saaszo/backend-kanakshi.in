<?php
/**
 * Reset Password Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(url(isAdmin() ? 'admin/' : 'my-account.php'));
}

$token = inputStr('token', '', 'GET');
$valid = false;
$email = '';

// Check if token is valid
if ($token) {
    $email = verifyResetToken($token);
    if ($email) {
        $valid = true;
    } else {
        setFlash('error', 'The password reset link is invalid or has expired.');
    }
} else {
    redirect(url('login.php'));
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    validateCsrf();
    
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $token    = inputStr('token', '', 'POST');
    
    $result = resetPassword($token, $password, $confirm);
    
    if ($result['success']) {
        setFlash('success', $result['message']);
        redirect(url('login.php'));
    } else {
        setFlash('error', $result['message']);
    }
}

$pageTitle = 'Reset Password';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="auth-card">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary-light text-primary rounded-circle mb-3" style="width: 70px; height: 70px;">
                <i class="fa-solid fa-lock-open fa-2xl"></i>
            </div>
            <h3 class="fw-800 text-dark mb-2">Create New Password</h3>
            <p class="text-secondary small">Your new password must be different from previously used passwords.</p>
        </div>
        
        <?php if ($valid): ?>
            <form action="<?= url('reset-password.php?token=' . e($token)) ?>" method="POST" novalidate id="resetForm">
                <?= csrfField() ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">
                
                <div class="mb-3">
                    <label class="form-label text-muted small">Email Address</label>
                    <input type="email" class="form-control bg-light" value="<?= e($email) ?>" disabled>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" required minlength="8" autofocus>
                    </div>
                    <div class="form-text mt-1 text-muted" style="font-size: .75rem;">Must be at least 8 characters.</div>
                </div>
                
                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-check-double"></i></span>
                        <input type="password" class="form-control border-start-0 ps-0" id="confirm_password" name="confirm_password" required minlength="8">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-2 fw-600 mb-3 text-uppercase ls-1" id="saveBtn">
                    Reset & Login <i class="fa-solid fa-arrow-right-to-bracket ms-1"></i>
                </button>
            </form>
        <?php else: ?>
            <div class="text-center">
                <a href="<?= url('forgot-password.php') ?>" class="btn btn-primary fw-600 px-4">Request New Link</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
$extraJs = <<<JS
<script>
document.getElementById('resetForm')?.addEventListener('submit', function(e) {
    const pwd1 = document.getElementById('password').value;
    const pwd2 = document.getElementById('confirm_password').value;
    if (pwd1 !== pwd2) {
        e.preventDefault();
        showToast('error', 'Passwords do not match.');
        return;
    }
    
    // Disable button to prevent double submit
    const btn = document.getElementById('saveBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';
    }
});
</script>
JS;
require_once __DIR__ . '/includes/footer.php'; 
?>
