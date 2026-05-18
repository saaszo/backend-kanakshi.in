<?php
$socialLinks = [
    ['url' => getSetting('facebook_url'), 'icon' => 'fa-facebook-f', 'label' => 'Facebook'],
    ['url' => getSetting('instagram_url'), 'icon' => 'fa-instagram', 'label' => 'Instagram'],
    ['url' => getSetting('twitter_url'), 'icon' => 'fa-x-twitter', 'label' => 'Twitter'],
    ['url' => getSetting('pinterest_url'), 'icon' => 'fa-pinterest-p', 'label' => 'Pinterest'],
];
?>
<footer class="storefront-footer">
    <div class="storefront-footer-edge"></div>

    <div class="container">
        <div class="footer-newsletter-card reveal">
            <div>
                <span class="footer-kicker">Storefront Refresh</span>
                <h3>Premium discovery with less clutter and better flow.</h3>
                <p>Browse signature edits, gifting collections, and bestselling products through our refreshed premium storefront.</p>
            </div>
            <div class="footer-newsletter-actions">
                <a href="<?= BASE_URL ?>/products.php" class="btn-lux-primary">Shop Collection</a>
                <a href="<?= BASE_URL ?>/contact-us.php" class="btn-lux-outline">Talk To Us</a>
            </div>
        </div>

        <div class="row g-5 py-5">
            <div class="col-lg-4 col-md-6 reveal">
                <h4 class="footer-brand"><?= e(SITE_NAME) ?></h4>
                <p class="footer-brand-blurb">
                    A premium Indian storefront for jewelry, gifting, festive edits, and decorative pieces shaped around trust, clarity, and smooth checkout.
                </p>
                <div class="footer-socials">
                    <?php foreach ($socialLinks as $social): ?>
                        <?php if (!empty($social['url'])): ?>
                            <a href="<?= e($social['url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($social['label']) ?>">
                                <i class="fa-brands <?= e($social['icon']) ?>"></i>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($wa = getSetting('whatsapp_number')): ?>
                        <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $wa)) ?>" target="_blank" rel="noopener" aria-label="WhatsApp">
                            <i class="fa-brands fa-whatsapp"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-6 col-lg-2 reveal">
                <h6 class="footer-heading">Shop</h6>
                <ul class="footer-link-list">
                    <li><a href="<?= BASE_URL ?>/products.php">All Products</a></li>
                    <?php foreach (array_slice($_categories, 0, 4) as $cat): ?>
                        <li><a href="<?= BASE_URL ?>/products.php?category=<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></a></li>
                    <?php endforeach; ?>
                    <li><a href="<?= BASE_URL ?>/products.php?featured=1">Trending Picks</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2 reveal">
                <h6 class="footer-heading">Help</h6>
                <ul class="footer-link-list">
                    <li><a href="<?= BASE_URL ?>/track-order.php">Track Order</a></li>
                    <li><a href="<?= BASE_URL ?>/contact-us.php">Contact Us</a></li>
                    <li><a href="<?= BASE_URL ?>/about-us.php">Our Story</a></li>
                    <li><a href="<?= BASE_URL ?>/my-account.php">My Account</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-6 reveal">
                <h6 class="footer-heading">Policies & Contact</h6>
                <div class="footer-policy-links">
                    <a href="<?= BASE_URL ?>/privacy-policy.php">Privacy</a>
                    <a href="<?= BASE_URL ?>/terms-conditions.php">Terms</a>
                    <a href="<?= BASE_URL ?>/refund-policy.php">Returns</a>
                </div>

                <div class="footer-payment-strip">
                    <i class="fa-brands fa-cc-visa"></i>
                    <i class="fa-brands fa-cc-mastercard"></i>
                    <i class="fa-brands fa-google-pay"></i>
                    <i class="fa-brands fa-cc-amex"></i>
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>

                <div class="footer-contact-stack">
                    <?php if ($addr = getSetting('site_address')): ?>
                        <div><i class="fa-solid fa-location-dot"></i><span><?= e($addr) ?></span></div>
                    <?php endif; ?>
                    <?php if ($phone = getSetting('site_phone')): ?>
                        <div><i class="fa-solid fa-phone"></i><span><?= e($phone) ?></span></div>
                    <?php endif; ?>
                    <?php if ($email = getSetting('site_email')): ?>
                        <div><i class="fa-solid fa-envelope"></i><span><?= e($email) ?></span></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom-bar">
        <div class="container">
            <div class="footer-bottom-shell">
                <p>&copy; <?= date('Y') ?> <strong><?= e(SITE_NAME) ?></strong>. All rights reserved.</p>
                <p>Crafted for a cleaner premium storefront experience.</p>
            </div>
        </div>
    </div>
</footer>
