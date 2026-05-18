<?php
/**
 * Admin AJAX: Handle Return Request Action
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.');
}

// Security: Check admin
if (!isAdmin()) {
    jsonResponse(false, 'Unauthorized access.');
}

$id     = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$id || !in_array($action, ['approved', 'rejected', 'completed'])) {
    jsonResponse(false, 'Invalid request parameters.');
}

$db = getDB();

try {
    $db->beginTransaction();
    
    // 1. Fetch Return Request
    $stmt = $db->prepare("SELECT * FROM order_returns WHERE id = ?");
    $stmt->execute([$id]);
    $return = $stmt->fetch();
    
    if (!$return) {
        throw new Exception('Return request not found.');
    }
    
    // 2. Update Status
    $stmtUpdate = $db->prepare("UPDATE order_returns SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmtUpdate->execute([$action, $id]);
    
    // 3. Special Logic: Mark Order as Refunded if completed
    if ($action == 'completed') {
        $stmtOrder = $db->prepare("UPDATE orders SET status = 'refunded' WHERE id = ?");
        $stmtOrder->execute([$return['order_id']]);
    }
    
    $db->commit();

    // 4. Send Email Notification to Customer
    $stmtUser = $db->prepare("SELECT u.email, u.name, o.order_number FROM users u JOIN orders o ON o.user_id = u.id WHERE o.id = ?");
    $stmtUser->execute([$return['order_id']]);
    $user = $stmtUser->fetch();

    if ($user) {
        $siteName = getSetting('site_name', 'Saaszo Store');
        $subject = "Update on your Return Request - " . $user['order_number'];
        $body = "
            <h2 style='color: #333;'>Return Request Update</h2>
            <p>Hi " . e($user['name']) . ",</p>
            <p>The status of your return request for order <strong>#" . e($user['order_number']) . "</strong> has been updated to: <strong style='text-transform: uppercase; color: #0d6efd;'>" . e($action) . "</strong>.</p>
            <p>Our team has processed your request accordingly. Log in to your dashboard to view more details.</p>
            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . url('my-orders.php') . "' style='padding: 12px 25px; background: #000; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold;'>View My Account</a>
            </div>
            <p style='font-size: 13px; color: #666;'>Thank you for shopping with " . e($siteName) . "!</p>
        ";
        try {
            sendEmail($user['email'], $subject, $body);
        } catch (Exception $e) {
            error_log('[RETURN-AJAX] Failed to email customer: ' . $e->getMessage());
        }
    }
    
    jsonResponse(true, 'Return request marked as ' . e($action) . ' successfully.');
    
} catch (Exception $e) {
    if($db->inTransaction()) $db->rollBack();
    jsonResponse(false, 'Failed to update request: ' . $e->getMessage());
}
