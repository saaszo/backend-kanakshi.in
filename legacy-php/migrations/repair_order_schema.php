<?php
/**
 * Repair missing order compatibility columns so checkout, tracking, and admin pages stay aligned.
 */
require_once __DIR__ . '/../config/config.php';

$db = getDB();

function schemaColumnExists(PDO $db, string $table, string $column): bool
{
    $stmt = $db->prepare("
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?
    ");
    $stmt->execute([$table, $column]);
    return (bool)$stmt->fetchColumn();
}

$schemaMap = [
    'orders' => [
        'tax' => "ALTER TABLE orders ADD COLUMN tax DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER discount",
        'shipping' => "ALTER TABLE orders ADD COLUMN shipping DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER shipping_cost",
        'total_amount' => "ALTER TABLE orders ADD COLUMN total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER total",
        'notes' => "ALTER TABLE orders ADD COLUMN notes TEXT NULL AFTER status",
        'coupon_id' => "ALTER TABLE orders ADD COLUMN coupon_id INT UNSIGNED NULL AFTER notes",
        'tracking_number' => "ALTER TABLE orders ADD COLUMN tracking_number VARCHAR(100) NULL AFTER coupon_id",
        'tracking_url' => "ALTER TABLE orders ADD COLUMN tracking_url VARCHAR(500) NULL AFTER tracking_number"
    ],
    'order_items' => [
        'variant_id' => "ALTER TABLE order_items ADD COLUMN variant_id INT UNSIGNED NULL AFTER product_id",
        'size' => "ALTER TABLE order_items ADD COLUMN size VARCHAR(30) NULL AFTER image",
        'color' => "ALTER TABLE order_items ADD COLUMN color VARCHAR(50) NULL AFTER size",
        'variant_details' => "ALTER TABLE order_items ADD COLUMN variant_details VARCHAR(120) NULL AFTER color",
        'line_total' => "ALTER TABLE order_items ADD COLUMN line_total DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER variant_details",
        'gst_percent' => "ALTER TABLE order_items ADD COLUMN gst_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER line_total",
        'sku' => "ALTER TABLE order_items ADD COLUMN sku VARCHAR(100) NULL AFTER gst_percent",
        'hsn_code' => "ALTER TABLE order_items ADD COLUMN hsn_code VARCHAR(20) NULL AFTER sku"
    ]
];

foreach ($schemaMap as $table => $columns) {
    foreach ($columns as $column => $sql) {
        if (!schemaColumnExists($db, $table, $column)) {
            $db->exec($sql);
            echo "Added {$table}.{$column}\n";
        }
    }
}

$db->exec("
    UPDATE orders
    SET shipping = shipping_cost
    WHERE (shipping IS NULL OR shipping = 0) AND shipping_cost > 0
");

$db->exec("
    UPDATE orders
    SET total_amount = total
    WHERE (total_amount IS NULL OR total_amount = 0) AND total > 0
");

$db->exec("
    UPDATE orders
    SET tax = GREATEST(total - subtotal + discount - shipping_cost, 0)
    WHERE (tax IS NULL OR tax = 0) AND total > 0
");

$db->exec("
    UPDATE order_items
    SET line_total = price * quantity
    WHERE line_total IS NULL OR line_total = 0
");

$db->exec("
    UPDATE order_items
    SET variant_details = TRIM(CONCAT(COALESCE(size, ''), CASE WHEN size IS NOT NULL AND size != '' AND color IS NOT NULL AND color != '' THEN ' / ' ELSE '' END, COALESCE(color, '')))
    WHERE variant_details IS NULL OR variant_details = ''
");

echo "Schema repair completed.\n";
?>
