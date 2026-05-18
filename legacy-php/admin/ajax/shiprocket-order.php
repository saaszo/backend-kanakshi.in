<?php
/**
 * Admin AJAX: Shiprocket API Dispatch Order
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/shiprocket.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.');
}

if (!isAdmin()) {
    jsonResponse(false, 'Unauthorized access.');
}

$orderId = (int)($_POST['order_id'] ?? 0);
if (!$orderId) {
    jsonResponse(false, 'Invalid order ID.');
}

$db = getDB();

// Fetch settings
$srEmail = getSetting('shiprocket_email');
$srPass  = getSetting('shiprocket_password');

if (empty($srEmail) || empty($srPass)) {
    jsonResponse(false, 'Shiprocket credentials are not configured in Settings.');
}

try {
    // 1. Fetch Full Order Details
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if (!$order) {
        throw new Exception("Order not found.");
    }

    if ($order['tracking_number']) {
        throw new Exception("This order already has a tracking number: " . $order['tracking_number']);
    }

    // 2. Fetch Order Items
    $stmtItems = $db->prepare("
        SELECT oi.*, p.sku, p.hsn_code 
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmtItems->execute([$orderId]);
    $items = $stmtItems->fetchAll();

    $orderItemsPayload = [];
    foreach ($items as $item) {
        $orderItemsPayload[] = [
            'name' => $item['name'],
            'sku' => $item['sku'] ?: 'SKU-' . $item['product_id'],
            'units' => (int)$item['quantity'],
            'selling_price' => (string)$item['price'],
            'discount' => '0',
            'tax' => '0',
            'hsn' => $item['hsn_code'] ?: 71131930
        ];
    }

    // 3. Build Shiprocket Payload
    $srOrderData = [
        "order_id" => $order['order_number'] . '-' . time(),
        "order_date" => date('Y-m-d', strtotime($order['created_at'])),
        "pickup_location" => "Primary",
        "billing_customer_name" => explode(' ', $order['ship_name'])[0],
        "billing_last_name" => explode(' ', $order['ship_name'])[1] ?? '',
        "billing_address" => $order['ship_address'],
        "billing_city" => $order['ship_city'],
        "billing_pincode" => $order['ship_pincode'],
        "billing_state" => $order['ship_state'],
        "billing_country" => "India",
        "billing_email" => $order['ship_email'] ?: getSetting('site_email'),
        "billing_phone" => preg_replace('/[^0-9]/', '', $order['ship_phone']),
        "shipping_is_billing" => true,
        "order_items" => $orderItemsPayload,
        "payment_method" => $order['payment_method'] === 'cod' ? 'COD' : 'Prepaid',
        "shipping_charges" => 0,
        "giftwrap_charges" => 0,
        "transaction_charges" => 0,
        "total_discount" => $order['discount'],
        "sub_total" => $order['total'],
        "length" => 10,
        "breadth" => 10,
        "height" => 5,
        "weight" => 0.5
    ];

    // 4. Initialize API
    $sr = new Shiprocket($srEmail, $srPass);
    if (!$sr->authenticate()) {
        throw new Exception("Shiprocket Authentication Failed. Check credentials in Settings.");
    }

    // 5. Create Order
    $createResponse = $sr->createOrder($srOrderData);
    if (!$createResponse['success']) {
        throw new Exception("Failed to push to Shiprocket: " . ($createResponse['message'] ?? 'Unknown Error'));
    }

    $shipmentId = $createResponse['shipment_id'];

    // 6. Generate AWB
    $awbResponse = $sr->generateAWB($shipmentId);
    if (!$awbResponse['success']) {
        throw new Exception("Order pushed, but AWB generation failed: " . ($awbResponse['message'] ?? 'Unknown Error'));
    }

    $awbCode = $awbResponse['awb_code'];
    $trackingUrl = $awbResponse['tracking_url'] ?? "https://shiprocket.co/tracking/" . $awbCode;

    // 7. Save to Database
    $db->prepare("UPDATE orders SET tracking_number = ?, tracking_url = ?, status = 'shipped' WHERE id = ?")
       ->execute([$awbCode, $trackingUrl, $orderId]);

    $db->prepare("
        INSERT INTO order_tracking (order_id, tracking_number, courier_name, status, location, message, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ")->execute([
        $orderId,
        $awbCode,
        'Shiprocket',
        'Shipped',
        $order['ship_city'] ?: null,
        'Shipment booked successfully and airway bill generated.'
    ]);

    // Send Shipping Email Notification
    sendShippingUpdateEmail($orderId);

    jsonResponse(true, "Shiprocket AWB Generated: " . $awbCode, ['awb' => $awbCode, 'url' => $trackingUrl]);

} catch (Exception $e) {
    error_log('[SHIPROCKET-AJAX] ' . $e->getMessage());
    jsonResponse(false, $e->getMessage());
}
