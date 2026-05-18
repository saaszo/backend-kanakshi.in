<?php
require_once __DIR__ . '/../config/config.php';
$db = getDB();

try {
    echo "Adding video_url column to products table...\n";
    $db->exec("ALTER TABLE products ADD COLUMN video_url VARCHAR(255) NULL AFTER images");
    echo "Success: video_url column added.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Note: video_url column already exists.\n";
    } else {
        die("Error: " . $e->getMessage() . "\n");
    }
}
