<?php
/**
 * Professional Invoice — Printable receipt for customers & admins
 * URL: domain.com/invoice.php?id=[ORDER_NUMBER]
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

$db = getDB();
$orderLookup = inputStr('id', '', 'GET');

if (!$orderLookup) {
    die('Order number required.');
}

// Fetch Order
$stmt = $db->prepare("
    SELECT *,
           COALESCE(total_amount, total) AS total_amount,
           COALESCE(shipping, shipping_cost) AS shipping
    FROM orders
    WHERE order_number = ? OR id = ?
    LIMIT 1
");
$stmt->execute([$orderLookup, ctype_digit($orderLookup) ? (int)$orderLookup : 0]);
$order = $stmt->fetch();

if (!$order) {
    die('Order not found.');
}

$orderNumber = $order['order_number'];

// Security Check: Only Admin or the placing Customer can view
$user = currentUser();
$isAdmin = $user && $user['role'] === 'admin';
$isOwner = $user && (int)$user['id'] === (int)$order['user_id'];

// Guest access (optional- allow via session or link if token exists, but let's stick to simple auth for now)
if (!$isAdmin && !$isOwner) {
    // If guest, check session match (for immediate post-purchase view)
    if (isset($_SESSION['last_order_number']) && $_SESSION['last_order_number'] === $order['order_number']) {
        $isOwner = true;
    } else {
         die('Unauthorized access. Please login to view your invoice.');
    }
}

// Fetch Items
$stmtItems = $db->prepare("
    SELECT *,
           COALESCE(line_total, price * quantity) AS line_total,
           COALESCE(NULLIF(variant_details, ''), TRIM(CONCAT(COALESCE(size, ''), ' ', COALESCE(color, '')))) AS variant_details,
           COALESCE(gst_percent, 0) AS gst_percent
    FROM order_items
    WHERE order_id = ?
");
$stmtItems->execute([$order['id']]);
$items = $stmtItems->fetchAll();

$siteLogo = getSetting('site_logo', 'uploads/logo_default.svg');
$siteName = e(getSetting('site_name', 'Saaszo Store'));
$siteEmail = e(getSetting('site_email', 'support@saaszo.in'));
$sitePhone = e(getSetting('site_phone', '9891659423'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= $orderNumber ?> | <?= $siteName ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; color: #1e293b; background: #f8fafc; }
        .invoice-wrapper { max-width: 850px; margin: 40px auto; background: #fff; box-shadow: 0 10px 40px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden; }
        .invoice-header { background: #18181b; color: #fff; padding: 40px; }
        .invoice-body { padding: 40px; }
        .invoice-footer { padding: 20px 40px; background: #f1f5f9; border-top: 1px solid #e2e8f0; font-size: 0.85rem; color: #64748b; }
        .table thead th { background: #f8fafc; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; border-bottom: 2px solid #e2e8f0; }
        .badge-status { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; padding: 5px 12px; border-radius: 50px; }
        
        @media print {
            body { background: #fff; margin: 0; padding: 0; }
            .invoice-wrapper { margin: 0; box-shadow: none; max-width: 100%; border-radius: 0; }
            .no-print { display: none !important; }
            .invoice-header { background: #000 !important; color: #fff !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<div class="container py-4 no-print text-center">
    <button onclick="window.print()" class="btn btn-primary px-4 rounded-pill shadow-sm fw-600">
        <i class="fa-solid fa-print me-2"></i> Print or Save PDF
    </button>
    <a href="<?= $isAdmin ? url('admin/orders/view.php?id='.$order['id']) : url('index.php') ?>" class="btn btn-outline-secondary px-4 rounded-pill ms-2 fw-600">
        <i class="fa-solid fa-arrow-left me-2"></i> Back
    </a>
</div>

<div class="invoice-wrapper">
    <!-- Header -->
    <div class="invoice-header">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <img src="<?= url($siteLogo) ?>" alt="Logo" style="max-height: 50px;" class="mb-3">
                <h2 class="fw-800 mb-0">TAX INVOICE</h2>
                <div class="opacity-75 small">Generated on <?= date('d M, Y') ?></div>
            </div>
            <div class="col-sm-6 text-sm-end mt-4 mt-sm-0">
                <h4 class="fw-700 mb-1">#<?= $orderNumber ?></h4>
                <div class="opacity-75 small">Status: <strong><?= strtoupper($order['status']) ?></strong></div>
                <div class="opacity-75 small">Payment: <strong><?= strtoupper($order['payment_status']) ?> (<?= strtoupper($order['payment_method']) ?>)</strong></div>
            </div>
        </div>
    </div>

    <!-- Body -->
    <div class="invoice-body">
        <div class="row mb-5">
            <div class="col-sm-6">
                <h6 class="text-uppercase fw-700 text-muted fs-8 mb-3 ls-1">Billed To (Customer)</h6>
                <div class="fw-700 text-dark fs-5 mb-1"><?= e($order['ship_name']) ?></div>
                <div class="text-secondary small mb-1"><?= e($order['ship_email']) ?></div>
                <div class="text-secondary small"><?= e($order['ship_phone']) ?></div>
                <div class="text-dark mt-3 small lh-lg">
                    <?= nl2br(e($order['ship_address'])) ?><br>
                    <?= e($order['ship_city']) ?>, <?= e($order['ship_state']) ?> - <?= e($order['ship_pincode']) ?>
                </div>
            </div>
            <div class="col-sm-6 text-sm-end">
                <h6 class="text-uppercase fw-700 text-muted fs-8 mb-3 ls-1">Shipped From (Store)</h6>
                <div class="fw-700 text-dark fs-5 mb-1"><?= $siteName ?></div>
                <div class="text-secondary small mb-1"><?= $siteEmail ?></div>
                <div class="text-secondary small"><?= $sitePhone ?></div>
                <div class="text-dark mt-3 small lh-lg">
                   Saaszo Tech Park, Ground Floor<br>
                   Sector 15, Rohini, New Delhi - 110085<br>
                   GSTIN: 07AAACS1234F1Z1 (Sample)
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th class="py-3 border-0">Item Description</th>
                        <th class="py-3 border-0 text-center" style="width: 100px;">HSN</th>
                        <th class="py-3 border-0 text-center" style="width: 100px;">Price</th>
                        <th class="py-3 border-0 text-center" style="width: 80px;">Qty</th>
                        <th class="py-3 border-0 text-center" style="width: 100px;">GST</th>
                        <th class="py-3 border-0 text-end" style="width: 120px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $item): ?>
                        <tr>
                            <td class="py-4 border-0">
                                <div class="fw-700 text-dark mb-0"><?= e($item['name']) ?></div>
                                <div class="text-muted small"><?= e($item['variant_details'] ?: 'Standard Variant') ?></div>
                                <div class="fs-8 text-secondary">SKU: <?= e($item['sku'] ?: 'N/A') ?></div>
                            </td>
                            <td class="py-4 border-0 text-center small text-muted"><?= e($item['hsn_code'] ?: '7117') ?></td>
                            <td class="py-4 border-0 text-center fw-600"><?= formatPrice($item['price']) ?></td>
                            <td class="py-4 border-0 text-center fw-700 text-dark">x<?= $item['quantity'] ?></td>
                            <td class="py-4 border-0 text-center small fw-600"><?= $item['gst_percent'] ?>%</td>
                            <td class="py-4 border-0 text-end fw-800 text-dark"><?= formatPrice($item['line_total'] + ($item['line_total'] * ($item['gst_percent']/100))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Totals Card -->
        <div class="row justify-content-end mt-4">
            <div class="col-md-5">
                <div class="bg-light p-4 rounded">
                   <div class="d-flex justify-content-between mb-2 fs-7 fw-600">
                       <span class="text-secondary">Subtotal</span>
                       <span class="text-dark"><?= formatPrice($order['subtotal']) ?></span>
                   </div>
                   <?php if($order['discount'] > 0): ?>
                   <div class="d-flex justify-content-between mb-2 fs-7 fw-600">
                       <span class="text-secondary">Discount</span>
                       <span class="text-success">-<?= formatPrice($order['discount']) ?></span>
                   </div>
                   <?php endif; ?>
                   <div class="d-flex justify-content-between mb-2 fs-7 fw-600">
                       <span class="text-secondary">Shipping</span>
                       <span class="text-dark"><?= $order['shipping'] > 0 ? '+' . formatPrice($order['shipping']) : 'FREE' ?></span>
                   </div>
                   <div class="d-flex justify-content-between mb-3 fs-7 fw-600 pb-3 border-bottom border-secondary border-opacity-10">
                       <span class="text-secondary">Estimated Tax</span>
                       <span class="text-dark">+<?= formatPrice($order['tax']) ?></span>
                   </div>
                   <div class="d-flex justify-content-between align-items-center">
                       <span class="fw-800 text-dark fs-5 text-uppercase">Total Amount</span>
                       <span class="fw-800 text-primary fs-4"><?= formatPrice($order['total_amount']) ?></span>
                   </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="invoice-footer">
        <div class="row">
            <div class="col-sm-8">
                <h6 class="fw-700 text-dark mb-1">Return Policy</h6>
                <div>This is a computer-generated invoice. No signature required. Returns accepted within 7 days of delivery for anti-tarnish jewelry.</div>
            </div>
            <div class="col-sm-4 text-sm-end mt-3 mt-sm-0">
                <div class="fw-700">Thank you for shopping!</div>
                <div>Powered by <a href="https://saaszo.in" target="_blank" class="text-decoration-none fw-600">Saaszo.in</a></div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
