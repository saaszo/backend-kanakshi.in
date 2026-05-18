<?php
require_once __DIR__ . '/config/config.php';
$db = getDB();

try {
    echo "Checking products table indices...\n";
    
    // 1. Try to drop the old index if it exists (might have tags which is missing)
    try {
        $db->exec("ALTER TABLE products DROP INDEX ft_search");
        echo "Dropped old ft_search index.\n";
    } catch (PDOException $e) {
        echo "No old ft_search index to drop.\n";
    }

    // 2. Add a clean FULLTEXT index on name and description
    echo "Adding FULLTEXT index on (name, description)...\n";
    $db->exec("ALTER TABLE products ADD FULLTEXT INDEX ft_search (name, description)");
    echo "Success: FULLTEXT index 'ft_search' added to products(name, description).\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
