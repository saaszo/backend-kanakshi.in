<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

$productId = (int)($_GET['product_id'] ?? 0);
$size = trim((string)($_GET['size'] ?? ''));
$color = trim((string)($_GET['color'] ?? ''));

if ($productId <= 0) {
    echo json_encode(['price' => formatPrice(0), 'stock' => 0, 'variant_id' => null]);
    exit;
}

$db = getDB();
$stmtProduct = $db->prepare("SELECT price, sale_price, stock FROM products WHERE id = ? LIMIT 1");
$stmtProduct->execute([$productId]);
$product = $stmtProduct->fetch();

if (!$product) {
    echo json_encode(['price' => formatPrice(0), 'stock' => 0, 'variant_id' => null]);
    exit;
}

$where = ['product_id = ?', 'is_active = 1'];
$params = [$productId];

if ($size !== '') {
    $where[] = 'size = ?';
    $params[] = $size;
}
if ($color !== '') {
    $where[] = 'color = ?';
    $params[] = $color;
}

$stmtVariant = $db->prepare("SELECT id, price, stock FROM product_variants WHERE " . implode(' AND ', $where) . " ORDER BY stock DESC, id ASC LIMIT 1");
$stmtVariant->execute($params);
$variant = $stmtVariant->fetch();

if ($variant) {
    echo json_encode([
        'price' => formatPrice((float)$variant['price']),
        'stock' => (int)$variant['stock'],
        'variant_id' => (int)$variant['id']
    ]);
    exit;
}

$effectivePrice = (float)($product['sale_price'] > 0 ? $product['sale_price'] : $product['price']);
echo json_encode([
    'price' => formatPrice($effectivePrice),
    'stock' => (int)$product['stock'],
    'variant_id' => null
]);
exit;
?>
