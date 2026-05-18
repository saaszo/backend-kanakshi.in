<?php
/**
 * Authentication Middleware & Helpers
 * Dropshipping Ecommerce Website
 * ------------------------------------------------
 * Include this file where auth checks are needed.
 * config.php must already be loaded before this file.
 */

// ═══════════════════════════════════════════════════════════
// LOGIN
// ═══════════════════════════════════════════════════════════

/**
 * Attempt to log in a user by email + password.
 * Handles rate limiting (5 attempts / 15 min block).
 * Returns ['success'=>bool, 'message'=>string]
 */
function loginUser(string $email, string $password): array
{
    $email = cleanEmail($email);

    if (empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Email and password are required.'];
    }

    try {
        $db   = getDB();
        $stmt = $db->prepare(
            "SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1"
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        // ── Rate limiting check ──────────────────────────────
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $remaining = ceil((strtotime($user['locked_until']) - time()) / 60);
            return [
                'success' => false,
                'message' => "Too many login attempts. Account locked for {$remaining} more minute(s)."
            ];
        }

        // ── Password verify ──────────────────────────────────
        if (!password_verify($password, $user['password'])) {
            $attempts = $user['login_attempts'] + 1;

            if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                $lockedUntil = date('Y-m-d H:i:s', time() + (LOGIN_LOCKOUT_MINS * 60));
                $db->prepare(
                    "UPDATE users SET login_attempts = ?, locked_until = ? WHERE id = ?"
                )->execute([$attempts, $lockedUntil, $user['id']]);
                return [
                    'success' => false,
                    'message' => 'Too many failed attempts. Account locked for ' . LOGIN_LOCKOUT_MINS . ' minutes.'
                ];
            }

            $db->prepare(
                "UPDATE users SET login_attempts = ? WHERE id = ?"
            )->execute([$attempts, $user['id']]);

            $left = MAX_LOGIN_ATTEMPTS - $attempts;
            return [
                'success' => false,
                'message' => "Invalid email or password. {$left} attempt(s) remaining."
            ];
        }

        // ── Successful login ─────────────────────────────────
        // Reset attempts, update last_login
        $db->prepare(
            "UPDATE users SET login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = ?"
        )->execute([$user['id']]);

        // Merge guest cart into user cart
        mergeGuestCart($user['id']);

        // Set session
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role']
        ];
        $_SESSION['created']    = time();
        $_SESSION['last_activity'] = time();

        appLog('info', "User login: {$email}", ['user_id' => $user['id']]);

        // ── Admin Login Notification ─────────────────────────
        if ($user['role'] === 'admin') {
            createNotification(
                "🛡️ Admin Login: {$user['name']} ({$user['email']}) logged in from " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown IP'),
                'system'
            );
        }

        return ['success' => true, 'message' => 'Welcome back, ' . $user['name'] . '!'];

    } catch (Exception $e) {
        error_log('[AUTH] loginUser: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Login failed. Please try again.'];
    }
}

// ═══════════════════════════════════════════════════════════
// REGISTER
// ═══════════════════════════════════════════════════════════

/**
 * Register a new customer account.
 * Returns ['success'=>bool, 'message'=>string]
 */
function registerUser(array $data): array
{
    $name     = clean($data['name'] ?? '');
    $email    = cleanEmail($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $confirm  = $data['confirm_password'] ?? '';
    $phone    = clean($data['phone'] ?? '');

    // ── Validation ───────────────────────────────────────────
    $errors = [];
    if (mb_strlen($name) < 2)   $errors[] = 'Name must be at least 2 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
    if (mb_strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm)  $errors[] = 'Passwords do not match.';
    if ($phone && !preg_match('/^[6-9]\d{9}$/', $phone)) {
        $errors[] = 'Enter a valid 10-digit Indian mobile number.';
    }

    if (!empty($errors)) {
        return ['success' => false, 'message' => implode(' ', $errors)];
    }

    try {
        $db = getDB();

        // Check duplicate email
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'An account with this email already exists.'];
        }

        $hash  = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $token = bin2hex(random_bytes(32)); // email verify token

        $insert = $db->prepare(
            "INSERT INTO users (name, email, password, phone, role, is_active, email_verify_token)
             VALUES (?, ?, ?, ?, 'customer', 1, ?)"
        );
        $insert->execute([$name, $email, $hash, $phone, $token]);
        $userId = (int)$db->lastInsertId();

        // Merge guest cart
        mergeGuestCart($userId);

        // Set session
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'    => $userId,
            'name'  => $name,
            'email' => $email,
            'role'  => 'customer'
        ];
        $_SESSION['created']    = time();

        appLog('info', "New user registered: {$email}", ['user_id' => $userId]);

        // ── WELCOME EMAIL ────────────────────────────────────
        $subject = "👋 Welcome to " . getSetting('site_name', 'MyShop') . "!";
        $body = "
            <h2 style='color: #333;'>Hello, {$name}!</h2>
            <p>Thank you for creating an account with <strong>" . getSetting('site_name', 'MyShop') . "</strong>. We are thrilled to have you with us!</p>
            <p>You can now enjoy a seamless shopping experience, track your orders, and manage your wishlist all in one place.</p>
            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . url('products.php') . "' style='display:inline-block; padding:14px 28px; background-color: " . getSetting('theme_primary_color', '#0d6efd') . "; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
                    Start Shopping
                </a>
            </div>
            <p style='font-size: 14px; color: #666;'>If you have any questions, simply reply to this email or visit our <a href='" . url('contact-us.php') . "'>Contact Us</a> page.</p>
            <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
            <p style='font-size: 12px; color: #999;'>You received this email because you recently signed up for an account. If this wasn't you, please ignore this message.</p>
        ";
        sendEmail($email, $subject, $body);

        return ['success' => true, 'message' => "Account created! Welcome, {$name}.", 'user_id' => $userId];

    } catch (Exception $e) {
        error_log('[AUTH] registerUser: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

// ═══════════════════════════════════════════════════════════
// LOGOUT
// ═══════════════════════════════════════════════════════════

/**
 * Log out the current user — destroy session.
 */
function logoutUser(): void
{
    appLog('info', 'User logout', ['user_id' => currentUserId()]);
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}

// ═══════════════════════════════════════════════════════════
// FORGOT PASSWORD
// ═══════════════════════════════════════════════════════════

/**
 * Create a password reset token and return it.
 * Returns ['success'=>bool, 'message'=>string, 'token'=>string|null]
 */
function createPasswordResetToken(string $email): array
{
    $email = cleanEmail($email);

    try {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Always return success (don't reveal if email exists)
        if (!$user) {
            return ['success' => true, 'message' => 'If this email exists, a reset link has been sent.', 'token' => null];
        }

        // Invalidate old tokens
        $db->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

        $token      = bin2hex(random_bytes(32));
        $expiresAt  = date('Y-m-d H:i:s', time() + RESET_TOKEN_EXPIRY);

        $db->prepare(
            "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)"
        )->execute([$email, $token, $expiresAt]);

        appLog('info', "Password reset requested: {$email}");

        return ['success' => true, 'message' => 'Reset link sent.', 'token' => $token, 'user_id' => $user['id']];

    } catch (Exception $e) {
        error_log('[AUTH] createPasswordResetToken: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Error generating reset link.', 'token' => null];
    }
}

/**
 * Verify a password reset token (not expired).
 * Returns the email on success, false on failure.
 */
function verifyResetToken(string $token): string|false
{
    try {
        $db   = getDB();
        $stmt = $db->prepare(
            "SELECT email FROM password_resets
             WHERE token = ? AND expires_at > NOW()
             LIMIT 1"
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $row ? $row['email'] : false;
    } catch (Exception $e) {
        error_log('[AUTH] verifyResetToken: ' . $e->getMessage());
        return false;
    }
}

/**
 * Reset user password using a valid token.
 * Returns ['success'=>bool, 'message'=>string]
 */
function resetPassword(string $token, string $newPassword, string $confirm): array
{
    if (mb_strlen($newPassword) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
    }
    if ($newPassword !== $confirm) {
        return ['success' => false, 'message' => 'Passwords do not match.'];
    }

    $email = verifyResetToken($token);
    if (!$email) {
        return ['success' => false, 'message' => 'Invalid or expired reset link. Please request a new one.'];
    }

    try {
        $db   = getDB();
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $db->prepare("UPDATE users SET password = ? WHERE email = ?")->execute([$hash, $email]);
        $db->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

        appLog('info', "Password reset success: {$email}");
        return ['success' => true, 'message' => 'Password reset successfully. Please login.'];

    } catch (Exception $e) {
        error_log('[AUTH] resetPassword: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Error resetting password.'];
    }
}

// ═══════════════════════════════════════════════════════════
// CART MERGE
// ═══════════════════════════════════════════════════════════

/**
 * Merge guest session cart into a logged-in user's cart
 * after login or registration.
 */
function mergeGuestCart(int $userId): void
{
    $sid = session_id();
    if (empty($sid)) return;

    try {
        $db    = getDB();
        $items = $db->prepare(
            "SELECT * FROM cart WHERE session_id = ? AND user_id IS NULL"
        );
        $items->execute([$sid]);
        $guestItems = $items->fetchAll();

        foreach ($guestItems as $item) {
            // Try to update existing row first
            $exists = $db->prepare(
                "SELECT id, quantity FROM cart
                 WHERE user_id = ? AND product_id = ?
                 AND (variant_id = ? OR (variant_id IS NULL AND ? IS NULL))
                 LIMIT 1"
            );
            $exists->execute([
                $userId, $item['product_id'],
                $item['variant_id'], $item['variant_id']
            ]);
            $existing = $exists->fetch();

            if ($existing) {
                $db->prepare(
                    "UPDATE cart SET quantity = quantity + ? WHERE id = ?"
                )->execute([$item['quantity'], $existing['id']]);
            } else {
                $db->prepare(
                    "INSERT INTO cart (user_id, product_id, variant_id, quantity)
                     VALUES (?, ?, ?, ?)"
                )->execute([$userId, $item['product_id'], $item['variant_id'], $item['quantity']]);
            }
        }

        // Remove guest cart
        $db->prepare("DELETE FROM cart WHERE session_id = ? AND user_id IS NULL")->execute([$sid]);

    } catch (Exception $e) {
        error_log('[AUTH] mergeGuestCart: ' . $e->getMessage());
    }
}

// ═══════════════════════════════════════════════════════════
// ADMIN GUARD
// ═══════════════════════════════════════════════════════════

/**
 * Require admin role — call at top of every /admin/ page.
 * Redirects to admin login if not authenticated.
 */
function requireAdmin(): void
{
    if (!isLoggedIn() || !isAdmin()) {
        setFlash('error', 'Please login to access the admin panel.');
        redirect(url('admin/login.php'));
    }

    // --- Admin Session Timeout (30 Mins) ---
    $timeout = 1800; // 30 minutes in seconds
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        logoutUser();
        setFlash('warning', 'Session expired due to inactivity. Please login again.');
        redirect(url('admin/login.php'));
    }
    $_SESSION['last_activity'] = time();
}
