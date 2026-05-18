<?php
/**
 * Admin Header & Sidebar — Professional Enterprise Layout
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../../includes/rbac.php';
requireAdmin();

$adminUser = $_SESSION['user'];
$userRole  = $adminUser['role'] ?? 'admin';

// Determine active menu
$currentFile = basename($_SERVER['SCRIPT_NAME']);
$currentDir  = basename(dirname($_SERVER['SCRIPT_NAME']));
// inventory.php lives in products/ so we need a special check
if ($currentFile === 'inventory.php') {
    $activeMenu = 'inventory.php';
} else {
    $activeMenu = $currentDir === 'admin' ? $currentFile : $currentDir;
}

// Ordered notification badge counts (for header icons)
$db = getDB();
$pendingOrders  = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$pendingReviews = $db->query("SELECT COUNT(*) FROM product_reviews WHERE status = 'pending'")->fetchColumn();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?><?= e(getSetting('site_name', 'Admin')) ?></title>

    <!-- Preloads -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Admin CSS -->
    <link rel="stylesheet" href="<?= url('admin/assets/css/admin.css') ?>?v=<?= filemtime(ROOT_PATH . '/admin/assets/css/admin.css') ?>">

    <!-- TinyMCE (loaded globally) -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

    <?php
    $themeColor = getSetting('theme_primary_color', '#c5a059');
    if ($themeColor && $themeColor !== '#c5a059'):
    ?>
    <style>
        :root {
            --brand:      <?= $themeColor ?>;
            --brand-dark: <?= $themeColor ?>cc;
        }
    </style>
    <?php endif; ?>
</head>
<body>

<?php
// Capture flash HTML — we re-render it as toast style via JS
$_flashHtml = showFlash();
?>
<?php if (!empty($_flashHtml)): ?>
<div id="flashToastRaw" style="display:none"><?= $_flashHtml ?></div>
<div class="flash-alert-bar" id="flashBar">
    <!-- JS will parse flashToastRaw and build toasts -->
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const raw  = document.getElementById('flashToastRaw');
    const bar  = document.getElementById('flashBar');
    if (!raw || !bar) return;
    raw.querySelectorAll('.alert').forEach(alert => {
        const isSuccess = alert.classList.contains('alert-success');
        const isDanger  = alert.classList.contains('alert-danger');
        const isWarning = alert.classList.contains('alert-warning');
        const type = isSuccess ? 'success' : isDanger ? 'error' : isWarning ? 'warning' : 'info';
        const icon = isSuccess ? 'fa-circle-check' : isDanger ? 'fa-circle-xmark' : 'fa-circle-exclamation';
        const msgEl = alert.querySelector('span');
        if (!msgEl) return;
        const toast = document.createElement('div');
        toast.className = `flash-alert flash-${type}`;
        toast.setAttribute('data-dismiss-after', '4500');
        toast.innerHTML = `<div class="flash-icon"><i class="fa-solid ${icon}"></i></div><div class="flash-alert-text">${msgEl.textContent}</div>`;
        bar.appendChild(toast);
    });
    raw.remove();
});
</script>
<?php endif; ?>

<!-- Sidebar overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ══ SIDEBAR ═══════════════════════════════════════════ -->
<aside class="admin-sidebar" id="sidebar">

    <!-- Logo -->
    <a href="<?= url('admin/index.php') ?>" class="sidebar-logo">
        <div class="sidebar-logo-icon">
            <i class="fa-solid fa-gem"></i>
        </div>
        <span class="sidebar-logo-text"><?= e(getSetting('site_name', 'Admin')) ?></span>
        <button class="btn btn-link text-secondary d-lg-none ms-auto p-0 border-0 shadow-none" id="closeSidebar" style="font-size:18px; line-height:1;">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </a>

    <!-- Nav -->
    <nav class="sidebar-nav">

        <!-- Overview -->
        <div class="sidebar-section">
            <a href="<?= url('admin/index.php') ?>" class="sidebar-link <?= $activeMenu === 'index.php' ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i class="fa-solid fa-house-chimney"></i></span>
                Dashboard
            </a>
        </div>

        <!-- Catalog -->
        <?php if (canAccess('listing') || canAccess('inventory')): ?>
        <div class="sidebar-section">
            <span class="sidebar-section-label">Catalog</span>

            <?php if (canAccess('listing')): ?>
            <a href="<?= url('admin/banners/index.php') ?>" class="sidebar-link <?= $activeMenu === 'banners' ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i class="fa-solid fa-image"></i></span>
                Banners
            </a>
            <?php endif; ?>

            <a href="<?= url('admin/products/index.php') ?>" class="sidebar-link <?= $activeMenu === 'products' ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i class="fa-solid fa-box"></i></span>
                Products
            </a>

            <a href="<?= url('admin/products/inventory.php') ?>" class="sidebar-link <?= $activeMenu === 'inventory.php' ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i class="fa-solid fa-warehouse"></i></span>
                Inventory
            </a>

            <a href="<?= url('admin/categories/index.php') ?>" class="sidebar-link <?= $activeMenu === 'categories' ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i class="fa-solid fa-tags"></i></span>
                Categories
            </a>
        </div>
        <?php endif; ?>

        <!-- Content -->
        <div class="sidebar-section">
            <span class="sidebar-section-label">Content</span>
            <a href="<?= url('admin/pages/index.php') ?>" class="sidebar-link <?= $activeMenu === 'pages' ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i class="fa-solid fa-file-lines"></i></span>
                Dynamic Pages
            </a>
        </div>

        <!-- Sales -->
        <?php if (canAccess('orders')): ?>
        <div class="sidebar-section">
            <span class="sidebar-section-label">Sales</span>

            <a href="<?= url('admin/orders/index.php') ?>" class="sidebar-link <?= $activeMenu === 'orders' ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i class="fa-solid fa-bag-shopping"></i></span>
                Orders
                <?php if ($pendingOrders > 0): ?>
                <span class="badge badge-warning ms-auto" style="font-size:10px;"><?= $pendingOrders ?></span>
                <?php endif; ?>
            </a>

            <a href="<?= url('admin/users/index.php') ?>" class="sidebar-link <?= $activeMenu === 'users' ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i class="fa-solid fa-users"></i></span>
                Customers
            </a>

            <a href="<?= url('admin/reviews/index.php') ?>" class="sidebar-link <?= $activeMenu === 'reviews' ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i class="fa-solid fa-star"></i></span>
                Reviews
                <?php if ($pendingReviews > 0): ?>
                <span class="badge badge-warning ms-auto" style="font-size:10px;"><?= $pendingReviews ?></span>
                <?php endif; ?>
            </a>
        </div>

        <!-- Marketing -->
        <div class="sidebar-section">
            <span class="sidebar-section-label">Marketing</span>

            <a href="<?= url('admin/coupons/index.php') ?>" class="sidebar-link <?= $activeMenu === 'coupons' ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i class="fa-solid fa-ticket"></i></span>
                Coupons
            </a>

            <a href="<?= url('admin/subscribers/index.php') ?>" class="sidebar-link <?= $activeMenu === 'subscribers' ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i class="fa-solid fa-envelope"></i></span>
                Newsletter
            </a>

            <a href="<?= url('admin/abandoned-carts/index.php') ?>" class="sidebar-link <?= $activeMenu === 'abandoned-carts' ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i class="fa-solid fa-cart-arrow-down"></i></span>
                Abandoned Carts
            </a>
        </div>
        <?php endif; ?>

        <!-- Settings -->
        <?php if ($userRole === 'admin'): ?>
        <div class="sidebar-section">
            <span class="sidebar-section-label">Settings</span>

            <a href="<?= url('admin/settings/index.php') ?>" class="sidebar-link <?= $activeMenu === 'settings' ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i class="fa-solid fa-sliders"></i></span>
                Settings
            </a>

            <a href="<?= url('admin/users/staff.php') ?>" class="sidebar-link <?= $activeMenu === 'staff' ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i class="fa-solid fa-user-shield"></i></span>
                Staff Team
            </a>
        </div>
        <?php endif; ?>

    </nav>

    <!-- User profile at bottom -->
    <div class="sidebar-footer">
        <div class="dropup">
            <div class="sidebar-user" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="sidebar-user-avatar">
                    <?= strtoupper(substr($adminUser['name'], 0, 1)) ?>
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?= e($adminUser['name']) ?></div>
                    <div class="sidebar-user-role"><?= ucfirst($userRole) ?></div>
                </div>
                <i class="fa-solid fa-ellipsis-vertical text-muted" style="font-size:12px;"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-up shadow-lg mb-1">
                <li>
                    <a class="dropdown-item" href="<?= url() ?>" target="_blank">
                        <i class="fa-solid fa-arrow-up-right-from-square text-muted"></i> View Store
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="<?= url('login.php?action=logout') ?>">
                        <i class="fa-solid fa-right-from-bracket"></i> Log Out
                    </a>
                </li>
            </ul>
        </div>
    </div>
</aside>

<!-- ══ MAIN WRAPPER ═══════════════════════════════════════ -->
<div class="admin-wrapper">

    <!-- Top Header -->
    <header class="admin-header">
        <!-- Mobile hamburger -->
        <button class="header-btn d-lg-none" id="toggleSidebar" style="border:none; padding:0 8px;">
            <i class="fa-solid fa-bars" style="font-size:16px;"></i>
        </button>

        <!-- Page Title -->
        <div>
            <div class="header-title"><?= isset($pageTitle) ? e($pageTitle) : 'Dashboard' ?></div>
        </div>

        <!-- Header Actions -->
        <div class="header-actions">
            <!-- Quick add product -->
            <a href="<?= url('admin/products/add.php') ?>" class="header-btn" title="New Product">
                <i class="fa-solid fa-plus" style="font-size:12px;"></i>
                <span class="d-none d-sm-inline">New Product</span>
            </a>

            <!-- View Store -->
            <a href="<?= url() ?>" target="_blank" class="header-btn" title="View Store">
                <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:11px;"></i>
                <span class="d-none d-md-inline">Live Store</span>
            </a>

            <!-- User dropdown -->
            <div class="dropdown">
                <button class="header-btn" data-bs-toggle="dropdown" style="gap:8px;">
                    <div style="width:24px;height:24px;border-radius:6px;background:var(--brand);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;">
                        <?= strtoupper(substr($adminUser['name'], 0, 1)) ?>
                    </div>
                    <span class="d-none d-sm-inline"><?= e(explode(' ', $adminUser['name'])[0]) ?></span>
                    <i class="fa-solid fa-chevron-down" style="font-size:10px; color:var(--text-muted);"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="px-3 py-2" style="border-bottom:1px solid var(--card-border); margin-bottom:4px;">
                        <div style="font-size:13px;font-weight:600;color:var(--text-primary);"><?= e($adminUser['name']) ?></div>
                        <div style="font-size:11px;color:var(--text-muted);"><?= e($adminUser['email']) ?></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= url('admin/settings/index.php') ?>">
                            <i class="fa-solid fa-sliders text-muted"></i> Settings
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= url() ?>" target="_blank">
                            <i class="fa-solid fa-store text-muted"></i> View Store
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="<?= url('login.php?action=logout') ?>">
                            <i class="fa-solid fa-right-from-bracket"></i> Log Out
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <main class="admin-content">
