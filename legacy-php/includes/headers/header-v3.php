<?php $headerMenuItems = getHeaderMenuItems(); ?>
<header class="header-v3 bg-white fixed-top transition-all py-2" id="header-v3">
    <div class="container-fluid px-lg-5">
        <div class="d-flex align-items-center justify-content-between">
            <!-- Logo Left -->
            <a class="navbar-brand fw-bold fs-2 text-dark m-0" href="<?= BASE_URL ?>/">
                <?php $logo = getSetting('site_logo'); if ($logo && file_exists(ROOT_PATH . '/' . $logo)): ?>
                    <img src="<?= BASE_URL . '/' . e($logo) ?>" alt="<?= e(SITE_NAME) ?>" style="max-height: 40px;">
                <?php else: ?>
                    <span class="fw-800 text-uppercase ls-1"><?= e(SITE_NAME) ?></span>
                <?php endif; ?>
            </a>

            <!-- Nav Center -->
            <?php if ($headerMenuItems): ?>
                <nav class="d-none d-lg-flex align-items-center gap-1">
                    <?php foreach ($headerMenuItems as $item): ?>
                        <a href="<?= e(menuItemUrl($item['url'])) ?>" class="nav-link <?= isMenuItemActive($item['url']) ? 'active' : '' ?>">
                            <?= e($item['label']) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>

            <!-- Icons Right -->
            <div class="d-flex align-items-center gap-4">
                <form class="d-none d-md-flex position-relative me-2" action="<?= BASE_URL ?>/products.php" method="GET">
                    <input type="search" name="q" class="form-control rounded-pill bg-light border-0 ps-4" placeholder="Search..." style="width:200px;">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 translate-middle-y end-0 me-3 text-muted"></i>
                </form>
                <a href="<?= BASE_URL ?>/cart.php" class="text-dark position-relative fs-5">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span class="badge bg-dark rounded-circle position-absolute top-0 start-100 translate-middle" style="font-size:9px;"><?= $_cartCount ?></span>
                </a>
                <?php if (isLoggedIn()): ?>
                    <a href="<?= BASE_URL ?>/account.php" class="text-dark fs-5"><i class="fa-solid fa-user-circle"></i></a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/account.php" class="btn btn-dark rounded-pill px-4 btn-sm fw-700">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
<div style="height: 70px;"></div> <!-- Spacer for fixed header -->
