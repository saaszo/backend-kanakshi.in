<?php
/**
 * Migration: Add Auth Columns
 * Resolves "Undefined array key" issues in auth.php
 */
require_once __DIR__ . '/../config/config.php';
$db = getDB();

try {
    // Check if columns exist first (optional but safer)
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS login_attempts INT DEFAULT 0 AFTER email_verify_token");
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS locked_until DATETIME NULL AFTER login_attempts");
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login DATETIME NULL AFTER locked_until");
    
    echo "Auth columns added successfully!\n";
} catch (Exception $e) {
    // If IF NOT EXISTS is not supported, this might fail, let's try a fallback
    try {
        $db->exec("ALTER TABLE users ADD COLUMN login_attempts INT DEFAULT 0");
        $db->exec("ALTER TABLE users ADD COLUMN locked_until DATETIME NULL");
        $db->exec("ALTER TABLE users ADD COLUMN last_login DATETIME NULL");
        echo "Auth columns added successfully (fallback method)!\n";
    } catch (Exception $e2) {
        echo "Error adding columns: " . $e2->getMessage() . "\n";
    }
}
