<footer class="footer-v4 bg-white border-top py-5">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-6">
                <!-- Logo -->
                <a class="navbar-brand fw-bold fs-2 text-dark m-0 d-block mb-3" href="<?= BASE_URL ?>/" style="font-family:'Playfair Display', serif; font-style:italic;">
                    <?= e(SITE_NAME) ?>
                </a>
                <p class="text-muted small mb-4 px-lg-5"><?= e(getSetting('site_tagline', 'Crafting exceptional shopping experiences for modern consumers.')) ?></p>
                
                <!-- Social Links -->
                <div class="social-links mb-5">
                    <?php if ($fb = getSetting('facebook_url')): ?><a href="<?= e($fb) ?>" class="text-dark"><i class="fa-brands fa-facebook-f"></i></a><?php endif; ?>
                    <?php if ($ig = getSetting('instagram_url')): ?><a href="<?= e($ig) ?>" class="text-dark"><i class="fa-brands fa-instagram"></i></a><?php endif; ?>
                    <?php if ($tw = getSetting('twitter_url')): ?><a href="<?= e($tw) ?>" class="text-dark"><i class="fa-brands fa-x-twitter"></i></a><?php endif; ?>
                    <?php if ($yt = getSetting('youtube_url')): ?><a href="<?= e($yt) ?>" class="text-dark"><i class="fa-brands fa-youtube"></i></a><?php endif; ?>
                </div>

                <!-- Footer Menu -->
                <ul class="list-inline mb-5 footer-menu-minimal">
                    <li class="list-inline-item mx-3"><a href="<?= BASE_URL ?>/" class="text-dark small text-decoration-none fw-600 text-uppercase ls-1">Home</a></li>
                    <li class="list-inline-item mx-3"><a href="<?= BASE_URL ?>/products.php" class="text-dark small text-decoration-none fw-600 text-uppercase ls-1">Shop</a></li>
                    <li class="list-inline-item mx-3"><a href="<?= BASE_URL ?>/about-us.php" class="text-dark small text-decoration-none fw-600 text-uppercase ls-1">Our Story</a></li>
                    <li class="list-inline-item mx-3"><a href="<?= BASE_URL ?>/contact-us.php" class="text-dark small text-decoration-none fw-600 text-uppercase ls-1">Contact</a></li>
                </ul>

                <hr class="w-25 mx-auto mb-4 opacity-10">

                <p class="text-muted small m-0">&copy; <?= date('Y') ?> <strong class="text-dark"><?= e(SITE_NAME) ?></strong>. All rights reserved.</p>
                <div class="mt-2">
                    <small class="text-muted">Proudly Crafted by <a href="https://saaszo.in" target="_blank" class="text-dark fw-bold text-decoration-none">Saaszo</a></small>
                </div>
            </div>
        </div>
    </div>
</footer>
<style>
.footer-menu-minimal a:hover { color: var(--primary) !important; }
</style>
