<?php
/**
 * Forgot Password Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(url(isAdmin() ? 'admin/' : 'my-account.php'));
}

$email = '';
$sent  = false;

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    
    $email = inputStr('email', '', 'POST');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'Please enter a valid email address.');
    } else {
        $result = createPasswordResetToken($email);
        
        if ($result['success']) {
            $sent = true;
            
            $resetUrl = url('reset-password.php?token=' . $result['token']);
            $mailSent = sendResetEmail($email, $resetUrl);
            
            // FOR DEVELOPMENT ONLY: Show the token so we can test it without emails setup
            if ($result['token'] && !$mailSent) {
                $devUrl = url('reset-password.php?token=' . $result['token']);
                setFlash('info', 'DEV MODE (Email Failed): Reset link generated -> <a href="' . $devUrl . '">Click Here</a>');
            }
        } else {
            setFlash('error', $result['message']);
        }
    }
}

$pageTitle = 'Forgot Password';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="auth-card">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary-light text-primary rounded-circle mb-3" style="width: 70px; height: 70px;">
                <i class="fa-solid fa-key fa-2xl"></i>
            </div>
            <h3 class="fw-800 text-dark mb-2">Forgot Password?</h3>
            <p class="text-secondary small">No worries! Enter your email address below and we'll send you a link to reset your password.</p>
        </div>
        
        <?php if ($sent): ?>
            <div class="alert alert-success mt-3 text-center p-4">
                <i class="fa-regular fa-envelope-open fa-3x mb-3 text-success"></i>
                <h5 class="fw-700">Check Your Email</h5>
                <p class="mb-0 small text-dark">If an account exists with <strong><?= e($email) ?></strong>, we have sent a password reset link.</p>
                <div class="mt-4">
                    <a href="<?= url('login.php') ?>" class="btn btn-outline-success fw-600">Return to Login</a>
                </div>
            </div>
        <?php else: ?>
            <form action="<?= url('forgot-password.php') ?>" method="POST" novalidate id="forgotForm">
                <?= csrfField() ?>
                
                <div class="mb-4">
                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary border-end-0">
                            <i class="fa-solid fa-envelope"></i>
                        </span>
                        <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" value="<?= e($email) ?>" placeholder="e.g. name@example.com" required autofocus>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-2 fw-600 mb-3 text-uppercase ls-1" id="resetBtn">
                    Send Reset Link <i class="fa-solid fa-paper-plane ms-1"></i>
                </button>
                
                <p class="text-center mb-0 mt-4">
                    <a href="<?= url('login.php') ?>" class="text-secondary small fw-600 text-decoration-none hover-primary">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to Login
                    </a>
                </p>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php 
$extraJs = <<<JS
<script>
document.getElementById('forgotForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('resetBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Sending...';
});
</script>
JS;
require_once __DIR__ . '/includes/footer.php'; 
?>
