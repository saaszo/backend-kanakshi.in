<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

$query = trim((string)($_GET['q'] ?? ''));
if (mb_strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

$term = '%' . $query . '%';
$stmt = getDB()->prepare("
    SELECT slug, name, images, COALESCE(sale_price, price) AS effective_price
    FROM products
    WHERE is_active = 1 AND (name LIKE ? OR short_desc LIKE ? OR description LIKE ?)
    ORDER BY is_featured DESC, total_sold DESC, created_at DESC
    LIMIT 8
");
$stmt->execute([$term, $term, $term]);

$results = [];
foreach ($stmt->fetchAll() as $product) {
    $results[] = [
        'slug' => $product['slug'],
        'name' => $product['name'],
        'thumb' => url(productThumb($product['images'])),
        'price' => formatPrice((float)$product['effective_price'])
    ];
}

echo json_encode(['results' => $results]);
exit;
?>
