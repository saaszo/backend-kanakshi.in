<?php
/**
 * AJAX: Submit Product Review
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.');
}

if (!isLoggedIn()) {
    jsonResponse(false, 'Please login to submit a review.');
}

$userId    = currentUserId();
$productId = (int)($_POST['product_id'] ?? 0);
$rating    = (int)($_POST['rating'] ?? 0);
$comment   = inputStr('comment', '', 'POST');

if (!$productId || $rating < 1 || $rating > 5) {
    jsonResponse(false, 'Please provide a valid rating.');
}

$db = getDB();

// 1. Verify Purchase
$stmtCheck = $db->prepare("
    SELECT COUNT(*) 
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'delivered'
");
$stmtCheck->execute([$userId, $productId]);
if ($stmtCheck->fetchColumn() <= 0) {
    jsonResponse(false, 'You can only review products you have purchased and received.');
}

// 2. Check for Duplicate Review
$stmtDup = $db->prepare("SELECT id FROM product_reviews WHERE product_id = ? AND user_id = ?");
$stmtDup->execute([$productId, $userId]);
if ($stmtDup->fetch()) {
    jsonResponse(false, 'You have already reviewed this product.');
}

// 3. Insert Review
try {
    $stmt = $db->prepare("INSERT INTO product_reviews (product_id, user_id, rating, comment, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->execute([$productId, $userId, $rating, $comment]);
    
    jsonResponse(true, 'Review submitted successfully! It will be visible after admin approval.');
} catch (Exception $e) {
    jsonResponse(false, 'Failed to save review: ' . $e->getMessage());
}
