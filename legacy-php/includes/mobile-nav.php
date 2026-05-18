<?php
/**
 * Mobile Bottom Navigation Bar
 * Included in footer.php — only visible on mobile.
 */
?>
<div class="mobile-bottom-nav d-md-none border-top shadow-lg">
    <div class="container-fluid">
        <div class="row g-0">
            <!-- Home -->
            <div class="col text-center">
                <a href="<?= url('index.php') ?>" class="nav-item <?= isActivePage('index.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-house"></i>
                    <span>Home</span>
                </a>
            </div>
            
            <!-- Categories -->
            <div class="col text-center">
                <a href="<?= url('products.php') ?>" class="nav-item <?= isActivePage('products.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Categories</span>
                </a>
            </div>
            
            <!-- Search -->
            <div class="col text-center">
                <a href="javascript:void(0)" class="nav-item" onclick="toggleMobileSearch()">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Search</span>
                </a>
            </div>
            
            <!-- Cart -->
            <div class="col text-center">
                <a href="<?= url('cart.php') ?>" class="nav-item <?= isActivePage('cart.php') ? 'active' : '' ?> position-relative">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span class="cart-badge-dot" id="mobileCartCount" style="display: <?= $_cartCount > 0 ? 'block' : 'none' ?>;"><?= $_cartCount ?></span>
                    <span>Cart</span>
                </a>
            </div>
            
            <!-- Account -->
            <div class="col text-center">
                <a href="<?= url('login.php') ?>" class="nav-item <?= isActivePage('login.php') || isActivePage('register.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-user"></i>
                    <span>Account</span>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 767.98px) {
    .whatsapp-float { bottom: 94px !important; }
    .btn-scroll-top { bottom: 94px !important; }
    body.storefront-v2 { padding-bottom: 88px !important; }
}
</style>
