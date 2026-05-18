<?php
/**
 * Save Image Bridge
 * Accepts Base64 data and saves it to uploads/categories/
 */
$dir = __DIR__ . '/uploads/categories';
if (!is_dir($dir)) mkdir($dir, 0777, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $filename = $data['filename'] ?? null;
    $base64 = $data['base64'] ?? null;

    if ($filename && $base64) {
        $base64 = preg_replace('#^data:image/\w+;base64,#i', '', $base64);
        $binary = base64_decode($base64);
        if (file_put_contents($dir . '/' . $filename, $binary)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save file']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    }
    exit;
}
echo "Bridge is ready.";
?>
