<?php
/**
 * Admin Forgot Password Page
 * Allows admin users to reset their password via email.
 */
require_once __DIR__ . '/includes/auth.php';

// If already logged in as admin, redirect to dashboard
if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
    redirect(url('admin/index.php'));
}

$sent  = false;
$email = '';
$step  = 'request'; // 'request' or 'reset'

// ── STEP 1: Handle reset request (email form) ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_link') {
    validateCsrf();

    $email = cleanEmail($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Please enter a valid email address.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id, name FROM users WHERE email = ? AND role = 'admin' AND is_active = 1 LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        // Always show success to prevent email enumeration
        $sent = true;

        if ($admin) {
            // Delete old tokens for this email
            $db->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

            $token     = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            $db->prepare(
                "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)"
            )->execute([$email, $token, $expiresAt]);

            $resetUrl = url('admin/reset-password.php?token=' . $token);

            // Attempt to send email
            $mailSent = sendResetEmail($email, $resetUrl, true);

            if (!$mailSent) {
                // Dev mode: show reset link directly if email fails
                $devLink = $resetUrl;
            }

            appLog('info', "Admin password reset requested: {$email}");
        }
    }
}

// ── STEP 2: Handle password reset form ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'do_reset') {
    validateCsrf();

    $token           = clean($_POST['token'] ?? '');
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $result = resetPassword($token, $newPassword, $confirmPassword);

    if ($result['success']) {
        $successMsg = $result['message'];
        $step = 'done';
    } else {
        $errorMsg = $result['message'];
        $step = 'reset';
    }
} elseif (isset($_GET['token']) && !empty($_GET['token'])) {
    $step  = 'reset';
    $token = clean($_GET['token']);

    // Validate the token first
    $tokenEmail = verifyResetToken($token);
    if (!$tokenEmail) {
        $step     = 'request';
        $errorMsg = 'This reset link has expired or is invalid. Please request a new one.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Forgot Password | <?= e(getSetting('site_name')) ?></title>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .card-wrap {
            width: 100%;
            max-width: 440px;
        }
        .fp-card {
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 8px 32px rgba(13,110,253,0.08), 0 2px 8px rgba(0,0,0,0.04);
            background: #fff;
            padding: 2.5rem;
        }
        .icon-circle {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
        }
        .btn-primary {
            background: linear-gradient(90deg, #0d6efd, #3b82f6);
            border: none;
            letter-spacing: 0.5px;
        }
        .btn-primary:hover { filter: brightness(1.08); }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13,110,253,.15);
        }
        .input-group-text {
            background: #f8faff;
            border-color: #dee2e6;
            color: #6b7280;
        }
        .step-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eff6ff;
            color: #2563eb;
            border-radius: 20px;
            padding: 4px 14px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .dev-alert {
            background: #fff7ed;
            border: 1px dashed #f97316;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 0.82rem;
        }
        .password-strength { height: 4px; border-radius: 2px; margin-top: 4px; transition: all .3s; }
    </style>
</head>
<body>

<div class="card-wrap">
    <!-- Logo / Brand -->
    <div class="text-center mb-4">
        <div class="icon-circle">
            <i class="fa-solid <?= $step === 'reset' ? 'fa-key' : 'fa-shield-halved' ?> fa-2x text-primary"></i>
        </div>
        <h4 class="fw-800 text-dark mb-1">
            <?php if ($step === 'reset'): ?>Set New Password
            <?php elseif ($step === 'done'): ?>Password Updated!
            <?php else: ?>Forgot Password?
            <?php endif; ?>
        </h4>
        <p class="text-secondary small mb-0">
            <?php if ($step === 'reset'): ?>Choose a strong new password for your admin account.
            <?php elseif ($step === 'done'): ?>Your admin password has been reset successfully.
            <?php else: ?>Enter your admin email to receive a reset link.
            <?php endif; ?>
        </p>
    </div>

    <div class="fp-card">

        <?php if (isset($errorMsg)): ?>
            <div class="alert alert-danger py-2 fs-7 fw-500 mb-4">
                <i class="fa-solid fa-circle-exclamation me-2"></i><?= e($errorMsg) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($successMsg)): ?>
            <div class="alert alert-success py-2 fs-7 fw-500 mb-4">
                <i class="fa-solid fa-circle-check me-2"></i><?= e($successMsg) ?>
            </div>
        <?php endif; ?>

        <!-- ── STEP: DONE ───────────────────────────────────────────── -->
        <?php if ($step === 'done'): ?>
            <div class="text-center py-3">
                <div class="mb-4">
                    <i class="fa-solid fa-check-circle fa-3x text-success"></i>
                </div>
                <p class="text-secondary fs-7">You can now login to the admin panel with your new password.</p>
                <a href="<?= url('admin/login.php') ?>" class="btn btn-primary fw-700 px-5 py-2 rounded-pill mt-2">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>Go to Login
                </a>
            </div>

        <!-- ── STEP: RESET PASSWORD FORM ───────────────────────────── -->
        <?php elseif ($step === 'reset'): ?>
            <div class="step-badge"><i class="fa-solid fa-circle-2"></i> Step 2 of 2 — Set New Password</div>
            <form action="<?= url('admin/forgot-password.php') ?>" method="POST" id="resetForm" novalidate>
                <?= csrfField() ?>
                <input type="hidden" name="action" value="do_reset">
                <input type="hidden" name="token" value="<?= e($token ?? '') ?>">

                <div class="mb-3">
                    <label class="form-label fw-600">New Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text border-end-0"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="new_password" id="newPass" class="form-control border-start-0 border-end-0 ps-0"
                               placeholder="Minimum 8 characters" required minlength="8"
                               oninput="checkStrength(this.value)">
                        <span class="input-group-text border-start-0 cursor-pointer" onclick="togglePass('newPass', this)" style="cursor:pointer;">
                            <i class="fa-regular fa-eye"></i>
                        </span>
                    </div>
                    <div class="password-strength bg-secondary mt-1" id="strengthBar"></div>
                    <small class="text-muted" id="strengthLabel"></small>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-600">Confirm Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text border-end-0"><i class="fa-solid fa-lock-open"></i></span>
                        <input type="password" name="confirm_password" id="confirmPass" class="form-control border-start-0 border-end-0 ps-0"
                               placeholder="Repeat new password" required>
                        <span class="input-group-text border-start-0" onclick="togglePass('confirmPass', this)" style="cursor:pointer;">
                            <i class="fa-regular fa-eye"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-700 py-3 rounded text-uppercase ls-1" id="resetBtn">
                    <i class="fa-solid fa-lock me-2"></i>Reset Password
                </button>
            </form>

        <!-- ── STEP: REQUEST LINK ───────────────────────────────────── -->
        <?php elseif ($sent): ?>
            <!-- Email sent message -->
            <div class="text-center py-2">
                <i class="fa-regular fa-paper-plane fa-3x text-primary mb-3"></i>
                <h5 class="fw-700 text-dark">Check Your Inbox</h5>
                <p class="text-secondary small">
                    If an admin account exists for <strong><?= e($email) ?></strong>,
                    a password reset link has been sent. It expires in <strong>1 hour</strong>.
                </p>

                <?php if (isset($devLink)): ?>
                    <!-- DEV MODE: Show direct link if email failed -->
                    <div class="dev-alert mt-3 text-start">
                        <strong class="text-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i> Dev Mode:</strong>
                        Email sending failed. Use this link to reset:<br>
                        <a href="<?= e($devLink) ?>" class="text-primary fw-600 text-break" style="font-size:.8rem;"><?= e($devLink) ?></a>
                    </div>
                <?php endif; ?>

                <a href="<?= url('admin/forgot-password.php') ?>" class="btn btn-outline-secondary btn-sm fw-600 mt-4 me-2">
                    <i class="fa-solid fa-rotate-left me-1"></i>Try Again
                </a>
                <a href="<?= url('admin/login.php') ?>" class="btn btn-outline-primary btn-sm fw-600 mt-4">
                    <i class="fa-solid fa-arrow-left me-1"></i>Back to Login
                </a>
            </div>

        <?php else: ?>
            <!-- Request form -->
            <div class="step-badge"><i class="fa-solid fa-circle-1"></i> Step 1 — Enter Email</div>
            <form action="<?= url('admin/forgot-password.php') ?>" method="POST" id="forgotForm" novalidate>
                <?= csrfField() ?>
                <input type="hidden" name="action" value="send_link">

                <div class="mb-4">
                    <label for="email" class="form-label fw-600">Admin Email Address <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text border-end-0"><i class="fa-regular fa-envelope"></i></span>
                        <input type="email" name="email" id="email" class="form-control border-start-0 ps-0"
                               value="<?= e($email) ?>" placeholder="admin@yourdomain.com" required autofocus>
                    </div>
                    <small class="text-muted mt-1 d-block">We'll send a reset link only if this email belongs to an admin account.</small>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-700 py-3 rounded text-uppercase ls-1" id="sendBtn">
                    <i class="fa-solid fa-paper-plane me-2"></i>Send Reset Link
                </button>
            </form>
        <?php endif; ?>

        <!-- Back link -->
        <?php if ($step !== 'done'): ?>
        <div class="text-center mt-4 pt-3 border-top">
            <a href="<?= url('admin/login.php') ?>" class="text-secondary small fw-600 text-decoration-none">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Admin Login
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle password visibility
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

// Password strength checker
function checkStrength(val) {
    const bar   = document.getElementById('strengthBar');
    const label = document.getElementById('strengthLabel');
    if (!bar) return;
    let score = 0;
    if (val.length >= 8)  score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const colors = ['bg-danger','bg-danger','bg-warning','bg-info','bg-success'];
    const labels = ['','Too Weak','Weak','Good','Strong'];
    const widths = ['0%','25%','50%','75%','100%'];

    bar.className = `password-strength ${colors[score]}`;
    bar.style.width = widths[score];
    label.textContent = labels[score];
    label.className = `text-${colors[score].replace('bg-','small ')}`;
}

// Disable button on submit
document.getElementById('sendBtn')?.addEventListener('click', function() {
    const form = document.getElementById('forgotForm');
    if (form.checkValidity()) {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
    }
});
document.getElementById('resetBtn')?.addEventListener('click', function() {
    const np = document.getElementById('newPass')?.value;
    const cp = document.getElementById('confirmPass')?.value;
    if (np !== cp) {
        alert('Passwords do not match!');
        return;
    }
    const form = document.getElementById('resetForm');
    if (form.checkValidity()) {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
    }
});
</script>
</body>
</html>
