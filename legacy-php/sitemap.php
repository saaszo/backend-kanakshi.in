<?php
/**
 * Dynamic XML Sitemap Generator
 */
require_once __DIR__ . '/config/config.php';

header("Content-Type: application/xml; charset=utf-8");

$db = getDB();

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// 1. Static Pages
$staticPages = [
    '',
    'products.php',
    'about-us.php',
    'contact-us.php',
    'privacy-policy.php',
    'terms-conditions.php',
    'refund-policy.php',
    'shipping-policy.php'
];

foreach ($staticPages as $page) {
    echo '<url>';
    echo '<loc>' . url($page) . '</loc>';
    echo '<changefreq>weekly</changefreq>';
    echo '<priority>0.5</priority>';
    echo '</url>';
}

// 2. Categories
$categories = $db->query("SELECT slug FROM categories WHERE is_active = 1")->fetchAll();
foreach ($categories as $cat) {
    echo '<url>';
    echo '<loc>' . url('products.php?category=' . $cat['slug']) . '</loc>';
    echo '<changefreq>weekly</changefreq>';
    echo '<priority>0.6</priority>';
    echo '</url>';
}

// 3. Products
$products = $db->query("SELECT slug, updated_at FROM products WHERE is_active = 1")->fetchAll();
foreach ($products as $prod) {
    echo '<url>';
    echo '<loc>' . url('product.php?slug=' . $prod['slug']) . '</loc>';
    echo '<lastmod>' . date('Y-m-d', strtotime($prod['updated_at'])) . '</lastmod>';
    echo '<changefreq>monthly</changefreq>';
    echo '<priority>0.8</priority>';
    echo '</url>';
}

// 4. Dynamic Pages
$pages = $db->query("SELECT slug, updated_at FROM pages WHERE is_active = 1")->fetchAll();
foreach ($pages as $page) {
    echo '<url>';
    echo '<loc>' . dynamicPageUrl($page['slug']) . '</loc>';
    if (!empty($page['updated_at'])) {
        echo '<lastmod>' . date('Y-m-d', strtotime($page['updated_at'])) . '</lastmod>';
    }
    echo '<changefreq>monthly</changefreq>';
    echo '<priority>0.5</priority>';
    echo '</url>';
}

echo '</urlset>';
