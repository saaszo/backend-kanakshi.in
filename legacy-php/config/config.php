<?php
/**
 * Central Configuration File
 * Dropshipping Ecommerce Website
 * ------------------------------------------------
 * Include this file at the top of every page.
 * It sets up the session, loads settings from DB,
 * and defines all global constants.
 */

// ─── Error Reporting ──────────────────────────────────────────────────────────
$appEnvironment = getenv('APP_ENV') ?: 'production';
$displayErrors = in_array($appEnvironment, ['local', 'development', 'testing'], true);
error_reporting(E_ALL);
ini_set('display_errors', $displayErrors ? '1' : '0');
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// ─── Timezone ─────────────────────────────────────────────────────────────────
date_default_timezone_set('Asia/Kolkata');

// ─── Base Paths ───────────────────────────────────────────────────────────────
define('ROOT_PATH',   dirname(__DIR__));
define('CONFIG_PATH', __DIR__);
define('UPLOAD_PATH', ROOT_PATH . '/uploads');

// Detect site base URL automatically (Robust for subdirectories and proxies)
$protocol = 'http';
if ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)) || 
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
    $protocol = 'https';
}
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Robust base path calculation
$projectRoot = str_replace('\\', '/', dirname(__DIR__));
$docRoot     = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);

if (strpos($projectRoot, $docRoot) === 0) {
    $basePath = substr($projectRoot, strlen($docRoot));
} else {
    // If DOCUMENT_ROOT is not a prefix (common with some symlinks/hosting)
    // Try a simpler approach using SCRIPT_NAME
    $basePath = dirname($_SERVER['SCRIPT_NAME']);
    // This is less reliable as a global root but works as a fallback
    // In our case, the current config.php approach is already decent.
    $basePath = str_replace($docRoot, '', $projectRoot); 
}

$basePath = '/' . ltrim(str_replace('\\', '/', $basePath), '/');
$basePath = rtrim($basePath, '/');

define('BASE_URL',   $protocol . '://' . $host . $basePath);
define('UPLOAD_URL',  BASE_URL . '/uploads');
define('ASSETS_URL',  BASE_URL . '/assets');

// ─── Force HTTPS in Production ────────────────────────────────────────────────
// Uncomment the lines below on a live server with SSL certificate:
// if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
//     header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
//     exit;
// }

// ─── Security Headers ─────────────────────────────────────────────────────────
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ─── Session Configuration ────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE && !defined('SKIP_SESSION')) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
    if ($protocol === 'https') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

// ─── Session Timeout (30 minutes inactivity) ─────────────────────────────────
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds

if (!defined('SKIP_SESSION') && isset($_SESSION['last_activity'])) {
    if ((time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        // Session expired — destroy and restart
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['last_activity'] = time(); // update last activity time stamp
}
if (!defined('SKIP_SESSION') && !isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
}

// ─── Regenerate Session ID periodically (anti session fixation) ───────────────
if (!defined('SKIP_SESSION')) {
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } elseif ((time() - $_SESSION['created']) > 300) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// ─── Load Core Files ──────────────────────────────────────────────────────────
require_once CONFIG_PATH . '/db.php';
require_once CONFIG_PATH . '/functions.php';

define('BACKEND_API_URL', backendApiBaseUrl());

// ─── App Constants (from settings table) ─────────────────────────────────────
define('SITE_NAME',           getSetting('site_name',           'UVIRA'));
define('SITE_EMAIL',          getSetting('site_email',          'support@uvira.com'));
define('SITE_CURRENCY',       getSetting('site_currency',       'INR'));
define('SITE_CURRENCY_SYMBOL',getSetting('site_currency_symbol','₹'));
define('GST_PERCENT',   (float)getSetting('gst_percent',        '18'));
define('DEFAULT_SHIPPING',
    (float)getSetting('default_shipping_cost', '49'));
define('FREE_SHIPPING_ABOVE',
    (float)getSetting('min_order_free_shipping', '499'));

// ─── Image Upload Limits ──────────────────────────────────────────────────────
define('MAX_FILE_SIZE',    2 * 1024 * 1024); // 2 MB
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

// ─── Rate Limiting ────────────────────────────────────────────────────────────
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_MINS', 15);

// ─── Password Reset Expiry ────────────────────────────────────────────────────
define('RESET_TOKEN_EXPIRY', 3600); // 1 hour in seconds

// ─── Pagination ───────────────────────────────────────────────────────────────
define('PRODUCTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE',   10);
define('ADMIN_PER_PAGE',    20);
