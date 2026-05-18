<?php
/**
 * Professional Shipping Label (4x6 format)
 * URL: admin/orders/shipping-label.php?id=[ORDER_ID]
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../includes/rbac.php';
guardPermission('shipping');

$db = getDB();
$id = (int)inputStr('id', 0, 'GET');

$stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) die('Order not found.');

$siteName = getSetting('site_name', 'Saaszo Store');
$sitePhone = getSetting('site_phone', '9891659423');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Label_<?= $order['order_number'] ?></title>
    <style>
        @page { size: 4in 6in; margin: 0; }
        body { font-family: 'Courier New', Courier, monospace; margin: 0; padding: 15px; width: 4in; height: 6in; box-sizing: border-box; font-size: 11px; }
        .border-box { border: 2px solid #000; height: 100%; display: flex; flex-direction: column; }
        .section { border-bottom: 2px solid #000; padding: 10px; }
        .no-border { border-bottom: 0; }
        .row { display: flex; justify-content: space-between; }
        .bold { font-weight: bold; font-size: 14px; }
        .text-center { text-align: center; }
        .barcode { height: 60px; width: 100%; background: #eee; margin: 5px 0; border: 1px dashed #666; display: flex; align-items: center; justify-content: center; font-size: 12px; }
        .qr-placeholder { width: 60px; height: 60px; background: #eee; border: 1px solid #000; float: right; }
        .tag { background: #000; color: #fff; padding: 2px 5px; font-weight: bold; margin-bottom: 5px; display: inline-block; }
    </style>
</head>
<body onload="window.print()">

<div class="border-box">
    <!-- Header: Carrier & Order info -->
    <div class="section text-center">
        <div class="row">
            <div style="text-align: left;">
                <div class="tag">STANDARD DELIVERY</div><br>
                <span class="bold">ORD# <?= $order['order_number'] ?></span>
            </div>
            <div class="qr-placeholder text-center">QR</div>
        </div>
    </div>

    <!-- Barcode Section -->
    <div class="section text-center">
        <div class="barcode">|||| ||| |||| |||| || ||| |||| ||||</div>
        <div class="bold"><?= $order['order_number'] ?></div>
    </div>

    <!-- Shipping Address -->
    <div class="section" style="flex-grow: 1;">
        <span class="tag">SHIP TO:</span><br>
        <span class="bold" style="font-size: 18px;"><?= strtoupper(e($order['ship_name'])) ?></span><br>
        <div style="font-size: 14px; line-height: 1.4; margin-top: 5px;">
            <?= nl2br(e($order['ship_address'])) ?><br>
            <?= strtoupper(e($order['ship_city'])) ?>, <?= strtoupper(e($order['ship_state'])) ?> - <?= e($order['ship_pincode']) ?><br>
            Ph: <?= e($order['ship_phone']) ?>
        </div>
    </div>

    <!-- Payment & Weight Info -->
    <div class="section">
        <div class="row">
            <div>
                <span class="bold"><?= strtoupper($order['payment_method']) ?></span><br>
                <span>Items: <?= $db->query("SELECT COUNT(*) FROM order_items WHERE order_id = ".$order['id'])->fetchColumn() ?></span>
            </div>
            <div style="text-align: right;">
                <span class="bold"><?= $order['payment_status'] === 'paid' ? 'PREPAID' : 'COLLECT: '.formatPrice($order['total']) ?></span>
            </div>
        </div>
    </div>

    <!-- Return Address -->
    <div class="section no-border" style="font-size: 9px; opacity: 0.8;">
        <span class="bold" style="font-size: 10px;">RETURN IF UNDELIVERED TO:</span><br>
        <?= $siteName ?> - Warehouse Dept.<br>
        Rohini Sec-15, New Delhi 110085<br>
        Phone: <?= $sitePhone ?>
    </div>
</div>

</body>
</html>
