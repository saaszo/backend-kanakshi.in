<footer class="footer-v2 bg-light text-dark pt-5 pb-4 border-top">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4">
                <a class="navbar-brand fw-bold fs-3 text-primary d-block mb-3" href="<?= BASE_URL ?>/"><?= e(SITE_NAME) ?></a>
                <p class="text-muted mb-4"><?= e(getSetting('site_tagline', 'Your one-stop shop for premium products.')) ?></p>
                <div class="d-flex gap-3">
                    <?php if ($fb = getSetting('facebook_url')): ?><a href="<?= e($fb) ?>" class="text-dark fs-5"><i class="fa-brands fa-facebook"></i></a><?php endif; ?>
                    <?php if ($ig = getSetting('instagram_url')): ?><a href="<?= e($ig) ?>" class="text-dark fs-5"><i class="fa-brands fa-instagram"></i></a><?php endif; ?>
                    <?php if ($wa = getSetting('whatsapp_number')): ?><a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $wa)) ?>" class="text-dark fs-5"><i class="fa-brands fa-whatsapp"></i></a><?php endif; ?>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="footer-title">Shop</h6>
                <ul class="list-unstyled d-flex flex-column gap-2">
                    <li><a href="<?= BASE_URL ?>/products.php" class="text-muted text-decoration-none small">All Categories</a></li>
                    <li><a href="<?= BASE_URL ?>/products.php?featured=1" class="text-muted text-decoration-none small">Featured Items</a></li>
                    <li><a href="<?= BASE_URL ?>/track-order.php" class="text-muted text-decoration-none small">Track Order</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="footer-title">Company</h6>
                <ul class="list-unstyled d-flex flex-column gap-2">
                    <li><a href="<?= BASE_URL ?>/about-us.php" class="text-muted text-decoration-none small">About Us</a></li>
                    <li><a href="<?= BASE_URL ?>/contact-us.php" class="text-muted text-decoration-none small">Contact Us</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h6 class="footer-title">Newsletter</h6>
                <p class="text-muted small">Stay updated with our latest offers and news.</p>
                <form class="input-group">
                    <input type="email" class="form-control border-secondary" placeholder="Email Address">
                    <button class="btn btn-dark" type="submit">Join</button>
                </form>
            </div>
        </div>
        <hr class="my-4 opacity-10">
        <div class="d-md-flex justify-content-between align-items-center text-center text-md-start">
            <p class="text-muted small m-0">&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>. All rights reserved.</p>
            <p class="text-muted small m-0">Powered by <a href="https://saaszo.in" target="_blank" class="text-primary text-decoration-none fw-600">Saaszo</a></p>
        </div>
    </div>
</footer>
