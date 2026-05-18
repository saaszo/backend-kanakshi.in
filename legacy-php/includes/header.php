<?php
/**
 * Global Header — included at top of every frontend page.
 * Variables you can set BEFORE including this file:
 *   $pageTitle   string  — <title> tag content
 *   $metaDesc    string  — meta description
 *   $metaKeywords string — meta keywords (optional)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth.php';

$_pageTitle    = isset($pageTitle)    ? e($pageTitle)    : e(SITE_NAME);
$_metaDesc     = isset($metaDesc)     ? e($metaDesc)     : getSetting('site_tagline', 'Best deals on electronics, fashion, home essentials & more.');
$_metaKeywords = isset($metaKeywords) ? e($metaKeywords) : 'online shopping, india, discount, electronics, fashion';
$_ogImage      = isset($ogImage)      ? e($ogImage)      : url(getSetting('site_logo', 'uploads/logo_default.svg'));
$_protocol     = ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) ? 'https' : 'http';
$_canonical    = $_protocol . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '');

$_cartCount    = cartCount();
$_categories   = getParentCategories();
$_currentUser  = currentUser();
syncWishlistSession();

// Active nav link helper
$_currentPage  = basename($_SERVER['PHP_SELF']);
function isActivePage(string $page): string {
    global $_currentPage;
    return ($_currentPage === $page) ? ' active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $_metaDesc ?>">
    <meta name="keywords"    content="<?= $_metaKeywords ?>">
    <meta name="robots"      content="index, follow">
    <link rel="canonical" href="<?= $_canonical ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="<?= $_canonical ?>">
    <meta property="og:title"       content="<?= $_pageTitle ?>">
    <meta property="og:description" content="<?= $_metaDesc ?>">
    <meta property="og:image"       content="<?= $_ogImage ?>">
    <meta property="og:site_name"   content="<?= e(SITE_NAME) ?>">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= $_canonical ?>">
    <meta property="twitter:title" content="<?= $_pageTitle ?>">
    <meta property="twitter:description" content="<?= $_metaDesc ?>">
    <meta property="twitter:image" content="<?= $_ogImage ?>">

    <title><?= $_pageTitle ?> | <?= e(SITE_NAME) ?></title>
    
    <!-- Meta Tags for JS -->
    <meta name="base-url" content="<?= BASE_URL ?>/">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <meta name="backend-api-url" content="<?= e(BACKEND_API_URL) ?>">

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,500;1,600&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&display=swap">
    <!-- Main CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= time() ?>">
    <!-- Headers & Footers Variations -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/headers-footers.css?v=<?= time() ?>">
    <!-- PWA Setup -->
    <link rel="manifest" href="<?= url('manifest.json') ?>">
    <meta name="theme-color" content="#0a0a0a">
    
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('<?= url('sw.js') ?>')
                .then(reg => console.log('ServiceWorker registered'))
                .catch(err => console.log('ServiceWorker error', err));
        });
    }
    
    // Force the storefront to use the premium light theme.
    (() => {
        localStorage.setItem('theme', 'light');
        document.documentElement.setAttribute('data-theme', 'light');
    })();
    </script>

    <!-- GSAP — Ultra-Premium Animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollToPlugin.min.js"></script>

    <?php 
    /* Dynamic theme color override disabled — using fixed dark luxury palette */
    ?>
</head>
<?php $_bodyClasses = trim((!empty($bodyClass) ? $bodyClass . ' ' : '') . 'storefront-v2'); ?>
<body<?= $_bodyClasses !== '' ? ' class="' . e($_bodyClasses) . '"' : '' ?>>

<?php 
$headerStyle = (int)getSetting('header_style', 1);
$headerFile  = __DIR__ . "/headers/header-v{$headerStyle}.php";
if (file_exists($headerFile)) {
    include $headerFile;
} else {
    include __DIR__ . "/headers/header-v1.php";
}
?>


<!-- ────────────────────────────────────────────────────────
     FLASH MESSAGES
──────────────────────────────────────────────────────── -->
<?php $flashes = showFlash(); if ($flashes): ?>
    <div class="container mt-3">
        <?= $flashes ?>
    </div>
<?php endif; ?>

<!-- ────────────────────────────────────────────────────────
     PAGE CONTENT STARTS
──────────────────────────────────────────────────────── -->
