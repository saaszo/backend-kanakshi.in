<?php
require_once __DIR__ . '/config/config.php';

$query = trim((string)($_GET['q'] ?? ''));
$target = $query !== ''
    ? url('products.php?q=' . rawurlencode($query))
    : url('products.php');

redirect($target);
?>
