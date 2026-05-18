<?php
/**
 * Admin Auth & Helpers
 */
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';

// Check if trying to access admin panel without being logged in as admin
function requireAdmin() {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        setFlash('error', 'You do not have permission to access the admin panel.');
        redirect(url('admin/login.php'));
    }
}
