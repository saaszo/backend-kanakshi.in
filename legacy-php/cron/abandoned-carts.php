<?php
/**
 * Cron Job: Abandoned Cart Recovery
 * Runs hourly to send recovery emails with a generated 5% auto-discount.
 */
// Simulate CLI/Cron environment path loading
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/functions.php';

// Log execution
error_log("[CRON] Abandoned Cart Recovery Script Started: " . date('Y-m-d H:i:s'));

$db = getDB();
$siteName = getSetting('site_name', 'Saaszo Store');
$siteUrl = rtrim(getSetting('site_url', BASE_URL), '/');

// 1. Find abandoned carts (Inactive for > 2 hours, < 24 hours, not recovered, not emailed yet)
$stmtFind = $db->query("
    SELECT ac.*, u.name, u.email 
    FROM abandoned_carts ac
    JOIN users u ON ac.user_id = u.id
    WHERE ac.is_recovered = 0 
      AND ac.email_sent = 0
      AND ac.last_active < DATE_SUB(NOW(), INTERVAL 2 HOUR)
      AND ac.last_active > DATE_SUB(NOW(), INTERVAL 24 HOUR)
");
$carts = $stmtFind->fetchAll();

if (empty($carts)) {
    error_log("[CRON] No abandoned carts found.");
    exit();
}

foreach ($carts as $cart) {
    try {
        $db->beginTransaction();

        $cartData = json_decode($cart['cart_data'], true);
        if (empty($cartData)) {
            $db->rollBack();
            $db->prepare("UPDATE abandoned_carts SET email_sent = 1 WHERE id = ?")->execute([$cart['id']]);
            continue;
        }

        // 2. Generate a Unique 5% Off Coupon
        $couponCode = strtoupper('COMEBACK5-' . substr(md5($cart['id'] . time()), 0, 6));
        $expiry = date('Y-m-d', strtotime('+3 days'));
        
        $db->prepare("
            INSERT INTO coupons (code, type, value, min_order, expiry_date, is_active)
            VALUES (?, 'percent', 5, 0, ?, 1)
        ")->execute([$couponCode, $expiry]);
        
        // 3. Prepare Email
        $subject = "Did you forget something, " . explode(' ', $cart['name'])[0] . "?";
        
        // Build beautiful HTML for items
        $itemsHtml = '<table style="width:100%; border-collapse: collapse; margin-top: 20px;">';
        foreach ($cartData as $item) {
            $img = !empty($item['images']) ? json_decode($item['images'])[0] : '';
            $itemsHtml .= "
                <tr>
                    <td style='padding: 10px; border-bottom: 1px solid #eee;'>
                        <div style='font-weight: bold; font-size: 16px; color: #333;'>{$item['name']}</div>
                        <div style='color: #888; font-size: 13px;'>Qty: {$item['quantity']}</div>
                    </td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right; font-weight: bold;'>
                        ₹{$item['unit_price']}
                    </td>
                </tr>
            ";
        }
        $itemsHtml .= "</table>";

        $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333; line-height: 1.6;'>
                <h2 style='text-align: center; color: #18181b;'>Your cart is waiting for you!</h2>
                <p>Hi {$cart['name']},</p>
                <p>We noticed you left some amazing items in your cart at <strong>{$siteName}</strong>. To help you make up your mind, we're giving you an exclusive <strong>5% OFF</strong> if you complete your purchase now.</p>
                
                <div style='background: #f8f9fa; padding: 20px; text-align: center; border-radius: 8px; margin: 30px 0;'>
                    <div style='font-size: 14px; color: #666; text-transform: uppercase; letter-spacing: 1px;'>Use Promo Code</div>
                    <div style='font-size: 28px; font-weight: bold; color: #000; letter-spacing: 2px; margin: 10px 0;'>{$couponCode}</div>
                </div>

                $itemsHtml

                <div style='text-align: center; margin: 40px 0;'>
                    <a href='{$siteUrl}/checkout.php' style='background: #000; color: #fff; text-decoration: none; padding: 15px 30px; border-radius: 50px; font-weight: bold; font-size: 16px; display: inline-block;'>Return to Checkout</a>
                </div>
                
                <p style='font-size: 12px; color: #999; text-align: center;'>This coupon will expire in 3 days. We hope to see you soon!</p>
            </div>
        ";

        // 4. Send Email
        sendEmail($cart['email'], $subject, $body);

        // 5. Mark as Sent
        $db->prepare("UPDATE abandoned_carts SET email_sent = 1 WHERE id = ?")->execute([$cart['id']]);

        $db->commit();
        error_log("[CRON] Recover email sent to: " . $cart['email']);

    } catch (Exception $e) {
        if($db->inTransaction()) $db->rollBack();
        error_log("[CRON] Error processing cart ID {$cart['id']}: " . $e->getMessage());
    }
}

error_log("[CRON] Complete. Processed " . count($carts) . " carts.");
