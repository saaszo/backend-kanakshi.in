<?php
/**
 * Account entry point.
 * Guests are sent to login/register.
 * Logged-in users are sent to their profile dashboard.
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

$user = currentUser();

if ($user) {
    redirect(url(isAdmin() ? 'admin/' : 'my-account.php'));
}

if (isLoggedIn() && !$user) {
    logoutUser();
}

$_SESSION['redirect_after_login'] = url('my-account.php');
setFlash('info', 'Please login or create an account to access your profile.');
redirect(url('login.php'));
