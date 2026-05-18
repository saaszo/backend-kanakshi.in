<footer class="footer-v3 bg-dark text-white pt-5 pb-4">
    <div class="container pb-3 border-bottom border-light border-opacity-10 mb-5">
        <div class="row g-4 d-flex align-items-center">
            <div class="col-md-3">
                <div class="trust-card text-center text-md-start">
                    <i class="fa-solid fa-shield-check fs-2 text-primary mb-3"></i>
                    <h6 class="fw-bold mb-1">Secure Shopping</h6>
                    <p class="text-muted small m-0">Encrypted payments & secure checkout.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="trust-card text-center text-md-start">
                    <i class="fa-solid fa-truck-fast fs-2 text-primary mb-3"></i>
                    <h6 class="fw-bold mb-1">Fast Delivery</h6>
                    <p class="text-muted small m-0">Shipments sent within 24 hours.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="trust-card text-center text-md-start">
                    <i class="fa-solid fa-rotate-left fs-2 text-primary mb-3"></i>
                    <h6 class="fw-bold mb-1">Easy Returns</h6>
                    <p class="text-muted small m-0">7-day hassle-free return policy.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="trust-card text-center text-md-start">
                    <i class="fa-solid fa-headset fs-2 text-primary mb-3"></i>
                    <h6 class="fw-bold mb-1">Expert Support</h6>
                    <p class="text-muted small m-0">Need help? Chat with us anytime.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="row g-5 mb-5">
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-800 text-uppercase ls-1 mb-4"><?= e(SITE_NAME) ?></h5>
                <p class="text-muted small mb-4"><?= e(getSetting('site_tagline', 'The Ultimate Destination for Style and Quality.')) ?></p>
                <div class="d-flex flex-column gap-3">
                    <?php if ($addr = getSetting('site_address')): ?>
                        <div class="d-flex align-items-start gap-3">
                            <i class="fa-solid fa-location-dot text-primary mt-1"></i>
                            <span class="text-muted small"><?= e($addr) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($phone = getSetting('site_phone')): ?>
                        <div class="d-flex align-items-center gap-3">
                            <i class="fa-solid fa-phone text-primary"></i>
                            <span class="text-muted small"><?= e($phone) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-6">
                <h6 class="text-uppercase fw-800 ls-1 mb-4 fs-7">Categories</h6>
                <ul class="list-unstyled d-flex flex-column gap-2">
                    <?php foreach (getParentCategories() as $cat): ?>
                        <li><a href="<?= BASE_URL ?>/products.php?category=<?= e($cat['slug']) ?>" class="text-muted text-decoration-none small hover-primary transition-all"><?= e($cat['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6 col-6">
                <h6 class="text-uppercase fw-800 ls-1 mb-4 fs-7">Support</h6>
                <ul class="list-unstyled d-flex flex-column gap-2">
                    <li><a href="<?= BASE_URL ?>/track-order.php" class="text-muted text-decoration-none small underline-hover">Track Your Order</a></li>
                    <li><a href="<?= BASE_URL ?>/shipping-policy.php" class="text-muted text-decoration-none small underline-hover">Shipping Policy</a></li>
                    <li><a href="<?= BASE_URL ?>/contact-us.php" class="text-muted text-decoration-none small underline-hover">Contact Support</a></li>
                    <li><a href="<?= BASE_URL ?>/about-us.php" class="text-muted text-decoration-none small underline-hover">About Story</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h6 class="text-uppercase fw-800 ls-1 mb-4 fs-7">Follow Us</h6>
                <div class="d-flex gap-3 mb-4">
                    <?php if ($fb = getSetting('facebook_url')): ?><a href="<?= e($fb) ?>" class="btn btn-outline-light btn-sm rounded-circle shadow-sm" style="width:36px;height:36px;padding:6px;"><i class="fa-brands fa-facebook-f"></i></a><?php endif; ?>
                    <?php if ($ig = getSetting('instagram_url')): ?><a href="<?= e($ig) ?>" class="btn btn-outline-light btn-sm rounded-circle shadow-sm" style="width:36px;height:36px;padding:6px;"><i class="fa-brands fa-instagram"></i></a><?php endif; ?>
                </div>
                <h6 class="text-uppercase fw-800 ls-1 mb-3 fs-8">Payment Secure</h6>
                <div class="d-flex gap-2 flex-wrap">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/1280px-Mastercard-logo.svg.png" height="15" alt="MasterCard" class="opacity-75 grayscale hover-color">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/2560px-Visa_Inc._logo.svg.png" height="15" alt="Visa" class="opacity-75 grayscale hover-color">
                </div>
            </div>
        </div>
        
        <div class="d-md-flex justify-content-between pt-4 border-top border-light border-opacity-10 align-items-center">
            <p class="text-muted small m-0">&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>. All rights reserved.</p>
            <p class="text-muted small m-0">Powered by <a href="https://saaszo.in" target="_blank" class="text-white text-decoration-underline opacity-50 hover-opacity-100 transition-all">Saaszo</a></p>
        </div>
    </div>
</footer>
