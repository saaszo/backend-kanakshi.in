<?php
require_once __DIR__ . '/config/config.php';
$db = getDB();
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

echo "<h1>Tables in " . DB_NAME . "</h1>";
echo "<ul>";
foreach ($tables as $table) {
    echo "<li>$table</li>";
}
echo "</ul>";

if (in_array('settings', $tables)) {
    echo "<h2>Settings Table Structure</h2>";
    $stmt = $db->query("DESCRIBE settings");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}

if (in_array('pages', $tables)) {
    echo "<h2>Pages Table Structure</h2>";
    $stmt = $db->query("DESCRIBE pages");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
?>
