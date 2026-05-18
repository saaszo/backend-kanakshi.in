<?php
/**
 * Registration Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(url(isAdmin() ? 'admin/' : 'my-account.php'));
}

$name  = '';
$email = '';
$phone = '';

// Handle Register Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    
    $name  = inputStr('name', '', 'POST');
    $email = inputStr('email', '', 'POST');
    $phone = inputStr('phone', '', 'POST');
    
    $result = registerUser($_POST);
    
    if ($result['success']) {
        setFlash('success', $result['message']);
        
        // In a real app, you might send a welcome email here
        // sendWelcomeEmail($email, $name);
        
        redirect(url('my-account.php'));
    } else {
        setFlash('error', $result['message']);
    }
}

$pageTitle = 'Create Account';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="auth-card" style="max-width: 550px;">
        <div class="auth-logo">
            <h3 class="fw-800 text-primary mb-1">Create Account</h3>
            <p class="text-secondary mb-0">Join us to manage orders, wishlists and track shipments fast & easily.</p>
        </div>
        
        <form action="<?= url('register.php') ?>" method="POST" novalidate>
            <?= csrfField() ?>
            
            <div class="mb-3">
                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-user"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" id="name" name="name" value="<?= e($name) ?>" required>
                </div>
            </div>
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" value="<?= e($email) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label">Mobile Number (Optional)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-phone"></i></span>
                        <input type="tel" class="form-control border-start-0 ps-0" id="phone" name="phone" value="<?= e($phone) ?>" pattern="[6-9][0-9]{9}" maxlength="10">
                    </div>
                </div>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" required minlength="8">
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-check-double"></i></span>
                        <input type="password" class="form-control border-start-0 ps-0" id="confirm_password" name="confirm_password" required minlength="8">
                    </div>
                </div>
            </div>
            
            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                <label class="form-check-label text-secondary small" for="terms">
                    I agree to the <a href="<?= url('terms-conditions.php') ?>" class="text-decoration-none" target="_blank">Terms & Conditions</a> and <a href="<?= url('privacy-policy.php') ?>" class="text-decoration-none" target="_blank">Privacy Policy</a>
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 py-2 fw-600 mb-3 text-uppercase ls-1" id="registerSubmitBtn">
                Create Account <i class="fa-solid fa-user-plus ms-1"></i>
            </button>
            
            <p class="text-center text-secondary small mb-0">
                Already have an account? 
                <a href="<?= url('login.php') ?>" class="fw-700 text-primary text-decoration-none">Log In</a>
            </p>
        </form>
    </div>
</div>

<?php 
$extraJs = <<<JS
<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const terms = document.getElementById('terms');
    if (!terms.checked) {
        e.preventDefault();
        showToast('error', 'You must agree to the Terms & Conditions.');
        return;
    }
    
    const pwd1 = document.getElementById('password').value;
    const pwd2 = document.getElementById('confirm_password').value;
    if (pwd1 !== pwd2) {
        e.preventDefault();
        showToast('error', 'Passwords do not match.');
        return;
    }
    
    // Disable button to prevent double submit
    const btn = document.getElementById('registerSubmitBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Creating...';
    }
});
</script>
JS;
require_once __DIR__ . '/includes/footer.php'; 
?>
