<?php $headerMenuItems = getHeaderMenuItems(); ?>
<header class="header-v2 border-bottom bg-white sticky-top shadow-sm">
    <div class="topbar bg-light text-muted py-1 border-bottom d-none d-md-block">
        <div class="container d-flex justify-content-center align-items-center gap-4">
            <small><i class="fa-solid fa-truck-fast me-1"></i> Free Shipping above <?= formatPrice(FREE_SHIPPING_ABOVE) ?></small>
            <small><i class="fa-solid fa-headset me-1"></i> 24/7 Support</small>
        </div>
    </div>
    
    <nav class="navbar navbar-expand-lg navbar-light py-3">
        <div class="container position-relative">
            <!-- Left Nav -->
            <div class="collapse navbar-collapse nav-left">
                <ul class="navbar-nav gap-3">
                    <?php foreach ($headerMenuItems as $item): ?>
                        <li class="nav-item">
                            <a class="nav-link fw-600 <?= isMenuItemActive($item['url']) ? 'active' : '' ?>" href="<?= e(menuItemUrl($item['url'])) ?>">
                                <?= e($item['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Centered Logo -->
            <a class="navbar-brand mx-auto" href="<?= BASE_URL ?>/">
                <?php $logo = getSetting('site_logo'); if ($logo && file_exists(ROOT_PATH . '/' . $logo)): ?>
                    <img src="<?= BASE_URL . '/' . e($logo) ?>" alt="<?= e(SITE_NAME) ?>" style="max-height: 50px;">
                <?php else: ?>
                    <span class="fw-800 fs-4 text-primary"><?= e(SITE_NAME) ?></span>
                <?php endif; ?>
            </a>

            <!-- Right Nav / Icons -->
            <div class="d-flex align-items-center gap-3 nav-right">
                <a href="#" class="text-dark" data-bs-toggle="collapse" data-bs-target="#searchCollapse"><i class="fa-solid fa-magnifying-glass"></i></a>
                <a href="<?= BASE_URL ?>/wishlist.php" class="text-dark position-relative d-none d-md-block"><i class="fa-regular fa-heart"></i></a>
                <a href="<?= BASE_URL ?>/cart.php" class="text-dark position-relative">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span class="badge bg-primary rounded-pill position-absolute top-0 start-100 translate-middle" style="font-size:10px;"><?= $_cartCount ?></span>
                </a>
                <?php if (isLoggedIn()): ?>
                    <a href="<?= BASE_URL ?>/account.php" class="text-dark"><i class="fa-regular fa-user"></i></a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/account.php" class="text-dark small fw-700 text-uppercase d-none d-md-inline">Login</a>
                <?php endif; ?>
                <button class="navbar-toggler border-0 p-0" type="button" data-bs-toggle="collapse" data-bs-target=".navbar-collapse">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </div>
    </nav>
    <div class="collapse bg-light border-top" id="searchCollapse">
        <div class="container py-3">
            <form action="<?= BASE_URL ?>/products.php" method="GET">
                <input type="search" name="q" class="form-control form-control-lg border-0 bg-transparent" placeholder="What are you looking for?" autofocus>
            </form>
        </div>
    </div>
</header>
