<?php
/**
 * Contact Us
 */
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Contact Us';
$metaDesc = 'Reach our support team by WhatsApp, email, phone, or request a callback.';
$siteEmail = getSetting('site_email', 'support@example.com');
$sitePhone = getSetting('site_phone', '+91 98765 43210');
$siteAddress = getSetting('site_address', 'India');
$whatsapp = preg_replace('/[^0-9]/', '', getSetting('whatsapp_number', '919876543210'));
require_once __DIR__ . '/includes/header.php';
?>

<section class="py-5" style="background:linear-gradient(135deg,#2d241e 0%,#5c4331 45%,#d4bba3 100%); color:#fcfaf5;">
    <div class="container py-4">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <span class="section-tag reveal" style="color:#f8e3ca;">Contact</span>
                <h1 class="reveal" style="color:#fff; font-style:normal;">Support That Feels Personal</h1>
                <p class="reveal" style="color:rgba(252,250,245,.82);">Use the contact options below for order help, product questions, delivery support, and bulk or gifting enquiries.</p>
                <div class="d-flex flex-column gap-3 mt-4 reveal">
                    <div><i class="fa-solid fa-envelope me-2"></i> <a href="mailto:<?= e($siteEmail) ?>" style="color:#fff;"><?= e($siteEmail) ?></a></div>
                    <div><i class="fa-solid fa-phone me-2"></i> <a href="tel:<?= e(preg_replace('/\s+/', '', $sitePhone)) ?>" style="color:#fff;"><?= e($sitePhone) ?></a></div>
                    <div><i class="fa-solid fa-location-dot me-2"></i> <?= e($siteAddress) ?></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="bg-white text-dark rounded-4 shadow-lg p-4 p-md-5 reveal">
                    <h2 class="fs-3 mb-2" style="font-style:normal;">Request A Callback</h2>
                    <p class="text-secondary mb-4">Share your number and our team can reach out for product or order help.</p>
                    <form id="callbackRequestForm">
                        <div class="mb-3">
                            <label class="form-label fw-600">Mobile Number</label>
                            <input type="tel" name="phone" class="form-control" placeholder="10-digit mobile number" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-600">What do you need help with?</label>
                            <input type="text" name="product_name" class="form-control" placeholder="Order update, gifting help, product name...">
                        </div>
                        <button type="submit" class="btn-lux-primary w-100">Request Callback</button>
                    </form>
                    <div class="d-flex gap-2 mt-3">
                        <a href="mailto:<?= e($siteEmail) ?>" class="btn btn-outline-dark w-100 rounded-pill fw-700">Email</a>
                        <a href="https://wa.me/<?= e($whatsapp) ?>?text=Hi%2C%20I%20need%20help%20with%20my%20order." target="_blank" rel="noopener" class="btn btn-success w-100 rounded-pill fw-700">WhatsApp</a>
                    </div>
                    <p class="small text-muted mb-0 mt-3">Fastest response is usually via WhatsApp during business hours.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4 reveal">
                <div class="rounded-4 border shadow-sm p-4 h-100">
                    <div class="text-primary fs-2 mb-3"><i class="fa-brands fa-whatsapp"></i></div>
                    <h3 class="fs-4" style="font-style:normal;">WhatsApp Support</h3>
                    <p class="text-secondary">Best for urgent order questions, dispatch updates, and product guidance.</p>
                    <a href="https://wa.me/<?= e($whatsapp) ?>?text=Hi%2C%20I%20have%20a%20question%20about%20a%20product." target="_blank" rel="noopener" class="btn btn-success rounded-pill fw-700">Start Chat</a>
                </div>
            </div>
            <div class="col-md-4 reveal">
                <div class="rounded-4 border shadow-sm p-4 h-100">
                    <div class="text-primary fs-2 mb-3"><i class="fa-solid fa-envelope-open-text"></i></div>
                    <h3 class="fs-4" style="font-style:normal;">Email Assistance</h3>
                    <p class="text-secondary">Great for invoices, partnership enquiries, and detailed support requests.</p>
                    <a href="mailto:<?= e($siteEmail) ?>" class="btn btn-outline-dark rounded-pill fw-700">Send Email</a>
                </div>
            </div>
            <div class="col-md-4 reveal">
                <div class="rounded-4 border shadow-sm p-4 h-100">
                    <div class="text-primary fs-2 mb-3"><i class="fa-solid fa-truck-fast"></i></div>
                    <h3 class="fs-4" style="font-style:normal;">Track Orders</h3>
                    <p class="text-secondary">Already placed an order? Use the tracking page for the latest fulfillment status.</p>
                    <a href="<?= url('track-order.php') ?>" class="btn btn-outline-dark rounded-pill fw-700">Track Order</a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('callbackRequestForm')?.addEventListener('submit', async function(event) {
    event.preventDefault();
    const formData = new FormData(this);
    const response = await fetch('<?= url('ajax/request-callback.php') ?>', {
        method: 'POST',
        body: formData
    });
    const data = await response.json();
    showToast(data.success ? 'success' : 'error', data.message);
    if (data.success) this.reset();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
