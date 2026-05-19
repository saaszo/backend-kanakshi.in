<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$appBasePath = __DIR__ . '/../laravel_app';

if (file_exists($maintenance = $appBasePath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $appBasePath . '/vendor/autoload.php';

/** @var Application $app */
$app = require_once $appBasePath . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
