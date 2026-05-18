<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

$pincode = trim((string)($_GET['pincode'] ?? ''));
if (!preg_match('/^\d{6}$/', $pincode)) {
    echo json_encode(['available' => false, 'message' => 'Enter a valid 6-digit pincode.']);
    exit;
}

$stmt = getDB()->prepare("
    SELECT shipping_cost, min_days, max_days
    FROM shipping_zones
    WHERE is_active = 1 AND (pincode = ? OR pincode IS NULL OR pincode = '')
    ORDER BY CASE WHEN pincode = ? THEN 0 ELSE 1 END, id ASC
    LIMIT 1
");
$stmt->execute([$pincode, $pincode]);
$zone = $stmt->fetch();

if ($zone) {
    echo json_encode([
        'available' => true,
        'min_days' => (int)$zone['min_days'],
        'max_days' => (int)$zone['max_days'],
        'cost' => (float)$zone['shipping_cost'] <= 0 ? 'FREE' : formatPrice((float)$zone['shipping_cost'])
    ]);
    exit;
}

echo json_encode([
    'available' => true,
    'min_days' => 3,
    'max_days' => 7,
    'cost' => DEFAULT_SHIPPING <= 0 ? 'FREE' : formatPrice((float)DEFAULT_SHIPPING)
]);
exit;
?>
