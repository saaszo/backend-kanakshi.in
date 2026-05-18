<?php $headerMenuItems = getHeaderMenuItems(); ?>
<header class="header-v4 bg-white py-4 border-bottom">
    <div class="container d-flex align-items-center justify-content-between">
        
        <!-- Shopping Links -->
        <?php if ($headerMenuItems): ?>
            <div class="d-none d-lg-flex align-items-center gap-4">
                <?php foreach ($headerMenuItems as $item): ?>
                    <a href="<?= e(menuItemUrl($item['url'])) ?>" class="nav-link text-uppercase ls-1 fw-500 <?= isMenuItemActive($item['url']) ? 'active' : '' ?>">
                        <?= e($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Centered Brand -->
        <div class="brand-centered text-center">
            <a class="navbar-brand d-block m-0" href="<?= BASE_URL ?>/">
                <?php $logo = getSetting('site_logo'); if ($logo && file_exists(ROOT_PATH . '/' . $logo)): ?>
                    <img src="<?= BASE_URL . '/' . e($logo) ?>" alt="<?= e(SITE_NAME) ?>" style="max-height: 80px;">
                <?php else: ?>
                    <div class="fw-700 fs-1 text-primary shadow-sm px-3" style="font-family:'Playfair Display', serif; font-style:italic; border-bottom: 2px solid var(--primary);"><?= e(SITE_NAME) ?></div>
                <?php endif; ?>
            </a>
            <p class="text-uppercase m-0 mt-2 text-muted ls-1" style="font-size:10px;">The Ultimate Luxury Jewelry Destination</p>
        </div>

        <!-- Action Icons -->
        <div class="d-flex align-items-center gap-4">
            <a href="<?= BASE_URL ?>/wishlist.php" class="text-muted"><i class="fa-regular fa-heart"></i></a>
            <a href="<?= BASE_URL ?>/cart.php" class="text-muted position-relative">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="badge bg-primary rounded-circle position-absolute top-0 start-100 translate-middle" style="font-size:9px;"><?= $_cartCount ?></span>
            </a>
            <a href="<?= BASE_URL ?>/account.php" class="text-muted"><i class="fa-solid fa-user"></i></a>
            <button class="btn btn-link fs-4 d-lg-none p-0 text-muted" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>
</header>

<!-- Mobile Offcanvas -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileNav">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold">Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <?php if ($headerMenuItems): ?>
            <nav class="d-flex flex-column gap-3">
                <?php foreach ($headerMenuItems as $item): ?>
                    <a href="<?= e(menuItemUrl($item['url'])) ?>" class="nav-link fs-5 fw-600">
                        <?= e($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
    </div>
</div>
