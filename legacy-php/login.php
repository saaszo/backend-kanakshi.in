<?php
/**
 * Login Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logoutUser();
    setFlash('success', 'You have been successfully logged out.');
    redirect(url('login.php'));
}

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(url(isAdmin() ? 'admin/' : 'my-account.php'));
}

$email = '';

// Handle Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    
    $email    = inputStr('email', '', 'POST');
    $password = $_POST['password'] ?? '';
    
    $result = loginUser($email, $password);
    
    if ($result['success']) {
        setFlash('success', $result['message']);
        
        // Redirect to intended page or default
        $redirect = $_SESSION['redirect_after_login'] ?? url(isAdmin() ? 'admin/' : 'my-account.php');
        unset($_SESSION['redirect_after_login']);
        
        redirect($redirect);
    } else {
        setFlash('error', $result['message']);
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
    <div class="auth-bg-ornament auth-bg-ornament-1"></div>
    <div class="auth-bg-ornament auth-bg-ornament-2"></div>
    <div class="container">
        <div class="auth-card-luxury auth-reveal">
            <div class="auth-header-luxury text-center mb-5">
                <h2 class="mb-2">Welcome Back</h2>
                <p class="text-uppercase ls-2 small fw-700">Enter Your Private Vault</p>
            </div>
            
            <form action="<?= url('login.php') ?>" method="POST" novalidate id="login-form">
                <?= csrfField() ?>
                
                <div class="luxury-input-group">
                    <label for="email">Identity / Email</label>
                    <div class="input-wrapper">
                        <input type="email" class="form-control" id="email" name="email" value="<?= e($email) ?>" placeholder="your@email.com" required autofocus>
                        <i class="fa-solid fa-envelope input-icon"></i>
                    </div>
                </div>
                
                <div class="luxury-input-group mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label for="password" class="mb-0">Security Key</label>
                        <a href="<?= url('forgot-password.php') ?>" class="text-decoration-none small fw-700 text-gold-dark hover-gold">Forgot?</a>
                    </div>
                    <div class="input-wrapper">
                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                        <i class="fa-solid fa-shield-halved input-icon"></i>
                        <button class="password-toggle-btn" type="button" id="togglePassword">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-5 luxury-checkbox">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label text-muted small fw-600" for="remember">Keep me recognized</label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-luxury-gold w-100">
                    Access Boutique <i class="fa-solid fa-arrow-right-long ms-2"></i>
                </button>
                
                <div class="auth-footer-links">
                    <p class="text-muted small mb-3">New to our collection?</p>
                    <a href="<?= url('register.php') ?>" class="text-gold text-decoration-none">Request Membership</a>
                </div>
                
                <div class="mt-5 text-center">
                    <div class="opacity-50">
                        <img src="<?= url(getSetting('site_logo', 'uploads/logo_default.svg')) ?>" alt="<?= e(getSetting('site_name', 'Boutique Store')) ?>" style="height: 18px; margin: 0 auto; filter: grayscale(1);">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
$extraJs = <<<JS
<script>
document.addEventListener('DOMContentLoaded', () => {
    // GSAP Entrance
    gsap.from(".auth-card-luxury", {
        y: 40,
        opacity: 0,
        duration: 1.2,
        ease: "power4.out",
        delay: 0.2
    });

    // Password Toggle
    const toggleBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    
    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }

    // Input Focus Effects
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('focus', () => {
            gsap.to(input.parentElement.querySelector('.input-icon'), {
                color: '#7a0f0f',
                duration: 0.3
            });
        });
        input.addEventListener('blur', () => {
            gsap.to(input.parentElement.querySelector('.input-icon'), {
                color: '#b38b6d',
                duration: 0.3
            });
        });
    });
});
</script>
JS;
require_once __DIR__ . '/includes/footer.php'; 
?>
