<?php
/**
 * Abandoned Cart Email Template
 */
function getAbandonedCartEmail(string $customerName, array $cartItems, float $totalValue): string
{
    $siteName = getSetting('site_name', 'MyShop');
    $primaryColor = getSetting('theme_primary_color', '#0d6efd');
    
    $itemsHtml = "";
    foreach ($cartItems as $item) {
        $thumb = productThumb($item['product_images'] ?? $item['image'] ?? '[]');
        $itemsHtml .= "
        <tr>
            <td style='padding: 12px; border-bottom: 1px solid #edf2f7;'>
                <img src='" . url($thumb) . "' width='50' height='50' style='border-radius: 4px; object-fit: cover; vertical-align: middle; margin-right: 12px;'>
                <span style='font-weight: 600; color: #2d3748;'>" . e($item['product_name'] ?? $item['name']) . "</span>
            </td>
            <td style='padding: 12px; border-bottom: 1px solid #edf2f7; text-align: right; color: #4a5568;'>
                " . $item['quantity'] . " x " . formatPrice($item['price']) . "
            </td>
        </tr>";
    }

    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #4a5568; margin: 0; padding: 0; background-color: #f7fafc; }
            .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
            .header { background: {$primaryColor}; padding: 40px 20px; text-align: center; color: #ffffff; }
            .content { padding: 40px 30px; }
            .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #718096; }
            .btn { display: inline-block; padding: 14px 32px; background: {$primaryColor}; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: bold; margin-top: 20px; }
            .price-total { font-size: 20px; font-weight: 800; color: {$primaryColor}; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin: 0; font-size: 28px;'>Don't leave your items behind! 🛍️</h1>
            </div>
            <div class='content'>
                <p>Hi " . e($customerName) . ",</p>
                <p>We noticed you left some items in your shopping cart. Don't worry, we've saved them for you! Stocks are moving fast, so grab them before they're gone.</p>
                
                <table style='width: 100%; border-collapse: collapse; margin: 30px 0;'>
                    {$itemsHtml}
                    <tr>
                        <td style='padding: 20px 12px; font-weight: bold; color: #2d3748;'>Total Value</td>
                        <td style='padding: 20px 12px; text-align: right;'><span class='price-total'>" . formatPrice($totalValue) . "</span></td>
                    </tr>
                </table>

                <div style='text-align: center;'>
                    <p style='margin-bottom: 25px;'>Complete your purchase now and enjoy fast shipping!</p>
                    <a href='" . url('cart.php') . "' class='btn'>Return to My Cart</a>
                </div>
                
                <p style='margin-top: 40px; font-size: 14px; text-align: center; color: #718096;'>
                    Need help? Reply to this email or visit our <a href='" . url('contact.php') . "' style='color: {$primaryColor};'>Support Center</a>.
                </p>
            </div>
            <div class='footer'>
                &copy; " . date('Y') . " " . $siteName . ". All rights reserved.<br>
                You're receiving this because you started a checkout process on our store.
            </div>
        </div>
    </body>
    </html>";
}
