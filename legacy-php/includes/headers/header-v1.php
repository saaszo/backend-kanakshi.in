<?php
$headerMenuItems = getHeaderMenuItems();
$logo = getSetting('site_logo');
$phone = getSetting('site_phone');
$headerHighlights = [
    'Free shipping above ' . formatPrice(FREE_SHIPPING_ABOVE),
    'Secure checkout',
    'Handpicked premium collections',
];
?>
<div class="topbar d-none d-md-block" id="topBar">
    <div class="container">
        <div class="storefront-topbar">
            <div class="storefront-topbar-note">Curated premium storefront</div>
            <div class="storefront-topbar-items">
                <?php foreach ($headerHighlights as $highlight): ?>
                    <span><?= e($highlight) ?></span>
                <?php endforeach; ?>
            </div>
            <div class="storefront-topbar-link">
                <a href="<?= BASE_URL ?>/track-order.php">Track Order</a>
            </div>
        </div>
    </div>
</div>

<header class="site-header" id="mainHeader">
    <div class="container">
        <div class="site-header-shell">
            <div class="header-main-row">
                <div class="header-brand-block">
                    <a href="<?= BASE_URL ?>/" class="header-logo">
                        <?php if ($logo && file_exists(ROOT_PATH . '/' . $logo)): ?>
                            <img src="<?= BASE_URL . '/' . e($logo) ?>" alt="<?= e(SITE_NAME) ?>" class="img-fluid">
                        <?php else: ?>
                            <?= e(SITE_NAME) ?>
                        <?php endif; ?>
                    </a>
                    <div class="header-brand-meta d-none d-xl-flex">
                        <span>Premium gifting</span>
                        <span>Jewelry and decor</span>
                        <?php if ($phone): ?>
                            <span><?= e($phone) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="header-search">
                    <form action="<?= BASE_URL ?>/products.php" method="GET" id="searchForm" class="header-search-card">
                        <div class="header-search-label">Search the collection</div>
                        <div class="header-search-input-wrap">
                            <input
                                type="search"
                                name="q"
                                id="searchInput"
                                placeholder="Jewelry, sculptures, gifting, festive edits..."
                                value="<?= isset($_GET['q']) ? e($_GET['q']) : '' ?>"
                                autocomplete="off"
                            >
                            <button type="submit" aria-label="Search products">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                        </div>
                        <div id="searchSuggestions" class="position-absolute shadow-soft mt-2 w-100 d-none header-search-suggestions"></div>
                    </form>
                </div>

                <div class="header-icons">
                    <a href="<?= BASE_URL ?>/<?= isLoggedIn() ? 'account.php' : 'login.php' ?>" class="header-icon-btn" title="Account">
                        <i class="fa-regular fa-user fa-fw header-icon-glyph header-icon-user"></i>
                    </a>

                    <a href="<?= BASE_URL ?>/wishlist.php" class="header-icon-btn d-none d-md-inline-grid" title="Wishlist">
                        <i class="fa-regular fa-heart fa-fw header-icon-glyph header-icon-heart"></i>
                    </a>

                    <a href="<?= BASE_URL ?>/cart.php" class="header-icon-btn" title="Cart">
                        <i class="fa-solid fa-bag-shopping fa-fw header-icon-glyph header-icon-cart"></i>
                        <?php if ($_cartCount > 0): ?>
                            <span class="header-badge"><?= $_cartCount ?></span>
                        <?php endif; ?>
                    </a>

                    <button class="header-icon-btn mobile-menu-btn d-inline-flex d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenuOffcanvas" aria-controls="mobileMenuOffcanvas" aria-label="Open mobile menu">
                        <i class="fa-solid fa-bars fa-fw header-icon-glyph"></i>
                    </button>
                </div>
            </div>

            <div class="header-utility-row d-none d-lg-flex">
                <div class="header-utility-chips">
                    <span><i class="fa-solid fa-shield-halved"></i> Trusted payments</span>
                    <span><i class="fa-solid fa-gift"></i> Gift-ready packaging</span>
                    <span><i class="fa-solid fa-truck-fast"></i> Pan-India delivery</span>
                </div>
                <div class="header-utility-links">
                    <a href="<?= BASE_URL ?>/about-us.php">Our Story</a>
                    <a href="<?= BASE_URL ?>/contact-us.php">Support</a>
                </div>
            </div>

            <?php if ($headerMenuItems): ?>
            <div class="header-nav-row d-none d-lg-block">
                <ul class="nav-list pb-0 mb-0">
                    <?php foreach ($headerMenuItems as $item): ?>
                        <li>
                            <a href="<?= e(menuItemUrl($item['url'])) ?>" class="<?= isMenuItemActive($item['url']) ? 'active' : '' ?>">
                                <?= e($item['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li>
                        <a href="<?= BASE_URL ?>/products.php?featured=1" class="nav-pill">Trending Picks</a>
                    </li>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenuOffcanvas" aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header">
        <div>
            <h5 class="offcanvas-title mb-1" id="mobileMenuLabel"><?= e(SITE_NAME) ?></h5>
            <p class="offcanvas-subtitle mb-0">Shop cleaner, faster, and with better discovery.</p>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form action="<?= BASE_URL ?>/products.php" method="GET" class="mobile-search-form">
            <input
                type="search"
                class="form-control"
                name="q"
                placeholder="Search the storefront"
                value="<?= isset($_GET['q']) ? e($_GET['q']) : '' ?>"
            >
            <button type="submit" class="header-icon-btn mobile-search-btn">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </form>

        <?php if ($headerMenuItems): ?>
        <ul class="mobile-nav-list mb-4">
            <?php foreach ($headerMenuItems as $item): ?>
                <li><a href="<?= e(menuItemUrl($item['url'])) ?>"><?= e($item['label']) ?></a></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <div class="mobile-offcanvas-links">
            <a href="<?= BASE_URL ?>/products.php?featured=1">Trending Picks</a>
            <a href="<?= BASE_URL ?>/track-order.php">Track Order</a>
            <a href="<?= BASE_URL ?>/contact-us.php">Contact Support</a>
        </div>

        <?php if (!isLoggedIn()): ?>
            <a href="<?= BASE_URL ?>/login.php" class="btn-lux-primary w-100 mb-3">Login / Register</a>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>/wishlist.php" class="btn-lux-outline w-100">My Wishlist</a>
    </div>
</div>

<!-- Sticky Header Script -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    var topBar = document.getElementById('topBar');
    var header = document.getElementById('mainHeader');
    var hasEditorialHero = document.querySelector('.lux-hero-slider') || document.querySelector('.lux-hero');
    var hasMarketplaceHero = document.querySelector('.home-market-hero');
    
    if (hasMarketplaceHero) {
        document.body.classList.add('page-market-home');
    }

    // Add page-home class only for the legacy cinematic hero homepage
    if (hasEditorialHero && !hasMarketplaceHero) {
        document.body.classList.add('page-home');
    }
    
    if (topBar) {
        document.body.classList.add('has-topbar');
    }

    function syncHeaderOffset() {
        var topBarHeight = 0;

        if (topBar) {
            var topBarStyles = window.getComputedStyle(topBar);
            if (topBarStyles.display !== 'none') {
                topBarHeight = topBar.offsetHeight;
            }
        }

        var headerHeight = header ? header.offsetHeight : 0;
        document.documentElement.style.setProperty('--site-header-offset', (topBarHeight + headerHeight + 12) + 'px');
    }

    syncHeaderOffset();
    window.addEventListener('resize', syncHeaderOffset);
    window.addEventListener('load', syncHeaderOffset);

    // Sticky Header
    window.addEventListener('scroll', function() {
        if (window.scrollY > 40) {
            header.classList.add('scrolled');
            document.body.classList.add('scrolled-passed');
        } else {
            header.classList.remove('scrolled');
            document.body.classList.remove('scrolled-passed');
        }

        syncHeaderOffset();
    });

    // Submenu toggles for dropdowns 
    var dropdowns = document.querySelectorAll('.dropdown-toggle');
    dropdowns.forEach(function (dd) {
        dd.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    });
});
</script>
