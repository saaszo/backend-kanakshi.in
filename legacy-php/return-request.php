<?php
/**
 * Customer Return Request Form
 * URL: return-request.php?id=[ORDER_NUMBER]
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

$orderNumber = inputStr('id', '', 'GET');

if (!isLoggedIn()) {
    setFlash('info', 'Please login to request a return.');
    redirect(url('login.php?redirect=return-request.php?id='.$orderNumber));
}

$db = getDB();
$userId = currentUserId();

// 1. Fetch Order (Must be 'delivered' and belong to this user)
$stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ? AND status = 'delivered'");
$stmt->execute([$orderNumber, $userId]);
$order = $stmt->fetch();

if (!$order) {
    setFlash('error', 'Order not found or ineligible for return.');
    redirect(url('my-orders.php'));
}

// Ensure return is within 7 days of delivery
$deliveryTime = strtotime($order['updated_at']);
$daysSinceDelivery = floor((time() - $deliveryTime) / (60 * 60 * 24));
if ($daysSinceDelivery > 7) {
    setFlash('error', 'The return window for this order has expired (7 days max).');
    redirect(url('order-details.php?id=' . $orderNumber));
}

// 2. Check if a return already exists
$stmtCheck = $db->prepare("SELECT id FROM order_returns WHERE order_id = ?");
$stmtCheck->execute([$order['id']]);
if ($stmtCheck->fetch()) {
    setFlash('warning', 'A return request for this order is already being processed.');
    redirect(url('order-details.php?id=' . $orderNumber));
}

// 3. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    
    $reason = inputStr('reason', '', 'POST');
    $description = inputStr('description', '', 'POST');
    $images = [];

    // Handle Optional Image Uploads (up to 3)
    if (!empty($_FILES['return_images']['name'][0])) {
        $files = $_FILES['return_images'];
        for ($i=0; $i < count($files['name']); $i++) { 
            if ($i >= 3) break; // Limit to 3 images
            $singleFile = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];
            $res = uploadImage($singleFile, 'uploads/returns/');
            if ($res) {
                $images[] = $res;
            }
        }
    }

    if (!$reason) {
        setFlash('error', 'Please select a reason for the return.');
    } else {
        try {
            $stmtInsert = $db->prepare("INSERT INTO order_returns (order_id, user_id, reason, description, images, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmtInsert->execute([$order['id'], $userId, $reason, $description, json_encode($images)]);
            
            // 4. Notify Admin
            $adminEmail = getSetting('site_email', 'noreply@saaszo.in');
            $subject = "New Return Request - #" . $orderNumber;
            $body = "
                <h2 style='color: #d63384;'>New Return Request Received</h2>
                <p>A new return request has been submitted for order <strong>#" . e($orderNumber) . "</strong>.</p>
                <p><strong>Customer:</strong> " . e($_SESSION['user']['name']) . "</p>
                <p><strong>Reason:</strong> " . e($reason) . "</p>
                <p><strong>Description:</strong> " . e($description) . "</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . url('admin/orders/returns.php') . "' style='padding: 12px 25px; background: #d63384; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold;'>View in Admin Panel</a>
                </div>
            ";
            try {
                sendEmail($adminEmail, $subject, $body);
            } catch (Exception $e) {
                error_log('[RETURN] Email failed: ' . $e->getMessage());
            }

            setFlash('success', 'Your return request has been submitted successfully. Our team will review it within 24-48 hours.');
            redirect(url('order-details.php?id=' . $orderNumber));
        } catch (Exception $e) {
            setFlash('error', 'Failed to submit request: ' . $e->getMessage());
        }
    }
}

$pageTitle = 'Request Return - #' . $orderNumber;
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5 mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-800 text-dark mb-0">Return <span class="text-primary">Request</span></h3>
                <a href="<?= url('order-details.php?id=' . $orderNumber) ?>" class="btn btn-light border btn-sm fw-700 px-4 rounded-pill">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="admin-card p-4 shadow-sm border-0">
                <div class="alert alert-info border-0 rounded-4 small fw-600 mb-4">
                    <i class="fa-solid fa-circle-info me-2"></i> After approval, our courier partner will contact you for pickup. Please keep the original packaging and tags intact.
                </div>

                <form action="" method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    
                    <div class="mb-4">
                        <label class="form-label fw-800 text-dark text-uppercase ls-1 fs-8">1. Reason for Return / Exchange <span class="text-danger">*</span></label>
                        <select name="reason" class="form-select py-3 rounded-4 fw-600 border-2" required>
                            <option value="">-- Choose a reason --</option>
                            <option value="Damaged Product">Damaged or Defective Product</option>
                            <option value="Wrong Item">Received the Wrong Item</option>
                            <option value="Size/Fit Issue">Size or Fitment Issue</option>
                            <option value="Quality Issue">Quality not as expected</option>
                            <option value="Changed My Mind">Changed my mind / No longer needed</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-800 text-dark text-uppercase ls-1 fs-8">2. Additional Details</label>
                        <textarea name="description" class="form-control rounded-4 border-2" rows="4" placeholder="Please describe the issue in detail..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-800 text-dark text-uppercase ls-1 fs-8">3. Upload Photos <small class="text-muted fw-normal">(Optional, max 3)</small></label>
                        <input type="file" name="return_images[]" multiple accept="image/*" class="form-control rounded-4 border-2">
                        <div class="form-text small fw-600 mt-2 text-secondary">
                            <i class="fa-solid fa-camera me-1 text-primary"></i> Providing photos of the product and its packaging helps speed up the approval process.
                        </div>
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="btn btn-primary w-100 fw-800 py-3 rounded-pill text-uppercase ls-1 shadow-sm">
                            Submit Return Request
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
