<?php
/**
 * Generic Dynamic Page Loader
 */
$_pageSlug = isset($_GET['slug']) ? trim((string)$_GET['slug']) : '';
if ($_pageSlug === '') {
    require_once __DIR__ . '/config/config.php';
    redirect(url('index.php'));
}

require_once __DIR__ . '/includes/templates/dynamic-page.php';
?>
