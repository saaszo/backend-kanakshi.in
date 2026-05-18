<?php
/**
 * Admin Login Page
 */
require_once __DIR__ . '/includes/auth.php';

// If already admin, redirect to admin dashboard
if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
    redirect(url('admin/index.php'));
}

// Same rate-limiting logic as frontend
$ip = $_SERVER['REMOTE_ADDR'];
$limitKey = 'login_attempts_' . md5($ip);
$attempts = $_SESSION[$limitKey]['count'] ?? 0;
$lockout  = $_SESSION[$limitKey]['lockout'] ?? 0;

if (time() < $lockout) {
    $remaining = ceil(($lockout - time()) / 60);
    $errorMsg = "Too many failed attempts. Try again in $remaining minutes.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    
    $email    = inputStr('email', '', 'POST');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $errorMsg = "Please enter both email and password.";
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            // Success
            if ($admin['is_active'] == 1) {
                $_SESSION['user'] = [
                    'id'    => $admin['id'],
                    'name'  => $admin['name'],
                    'email' => $admin['email'],
                    'role'  => $admin['role']
                ];
                session_regenerate_id(true);
                unset($_SESSION[$limitKey]);
                
                setFlash('success', 'Welcome to Admin Panel, ' . $admin['name']);
                redirect(url('admin/index.php'));
            } else {
                $errorMsg = "Your admin account is disabled.";
            }
        } else {
            // Fail
            $attempts++;
            $_SESSION[$limitKey]['count'] = $attempts;
            if ($attempts >= 5) {
                $_SESSION[$limitKey]['lockout'] = time() + (15 * 60); // 15 mins lock
                $errorMsg = "Too many failed attempts. Try again in 15 minutes.";
            } else {
                $errorMsg = "Invalid admin credentials.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?= e(getSetting('site_name')) ?></title>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0b0b0b; color: #f5f0e8; }
        .login-card { 
            border-radius: 4px; 
            border: 1px solid rgba(212, 175, 55, 0.25); 
            box-shadow: 0 4px 20px rgba(0,0,0,0.4); 
            background: #111111 !important;
        }
        .form-control {
            background: #111111;
            border: 1px solid rgba(212, 175, 55, 0.25);
            color: #f5f0e8;
        }
        .form-control:focus {
            background: #0b0b0b;
            border-color: #d4af37;
            box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.2);
            color: #f5f0e8;
        }
        .input-group-text {
            background: #1a1a1a !important;
            border-color: rgba(212, 175, 55, 0.25) !important;
            color: #d4af37 !important;
        }
        .btn-lux {
            background: #d4af37;
            color: #0b0b0b;
            border: 1px solid #d4af37;
            transition: all 0.3s ease;
        }
        .btn-lux:hover {
            background: #e9d9b5;
            border-color: #e9d9b5;
            transform: translateY(-2px);
        }
        .text-gold { color: #d4af37 !important; }
        .hover-gold:hover { color: #d4af37 !important; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 p-3">

<div class="row justify-content-center w-100 m-0">
    <div class="col-12 col-sm-8 col-md-6 col-lg-4">
        
        <div class="text-center mb-4">
            <h2 class="fw-800 text-gold mb-0">
                <i class="fa-solid fa-gem me-2"></i> Admin Panel
            </h2>
            <p class="text-secondary small mt-1">Sign in to manage your masterpiece collection</p>
        </div>
        
        <div class="card login-card bg-white p-4 p-md-5">
            <?php if (isset($errorMsg)): ?>
                <div class="alert alert-danger py-2 fs-7 fw-500"><i class="fa-solid fa-circle-exclamation me-2"></i> <?= $errorMsg ?></div>
            <?php endif; ?>
            <?php showFlash(); ?>
            
            <form action="<?= url('admin/login.php') ?>" method="POST">
                <?= csrfField() ?>
                
                <div class="mb-3">
                    <label class="form-label fw-600 text-secondary">Admin Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-regular fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control border-start-0 ps-0" required autofocus placeholder="admin@saaszo.in">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-600">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-key"></i></span>
                        <input type="password" name="password" id="pass" class="form-control border-start-0 border-end-0 ps-0 text-dark" required placeholder="••••••••">
                        <span class="input-group-text bg-light border-start-0 cursor-pointer text-muted" onclick="togglePass('pass', this)">
                            <i class="fa-regular fa-eye"></i>
                        </span>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-lux w-100 fw-700 py-3 rounded text-uppercase ls-1">Access Vault</button>
                
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="<?= url('admin/forgot-password.php') ?>" class="text-secondary small fw-600 text-decoration-none hover-gold">
                        <i class="fa-solid fa-key me-1"></i> Forgot Password?
                    </a>
                    <a href="<?= url() ?>" class="text-secondary small text-decoration-none fw-600 hover-gold"><i class="fa-solid fa-arrow-left me-1"></i> Back to Boutique</a>
                </div>
                <div class="mt-4 text-center border-top pt-3">
                    <small class="text-muted">Powered by <a href="https://saaszo.in" target="_blank" class="text-decoration-none fw-600">Saaszo &mdash; saaszo.in</a></small>
                </div>
            </form>
        </div>
        
    </div>
</div>

<script>
function togglePass(id, el) {
    const input = document.getElementById(id);
    const icon = el.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
