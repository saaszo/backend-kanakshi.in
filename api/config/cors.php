<?php

$defaultOrigins = [
    'https://kanakshi.in',
    'https://www.kanakshi.in',
];

if (in_array(env('APP_ENV', 'production'), ['local', 'development', 'testing'], true)) {
    $defaultOrigins = array_merge($defaultOrigins, [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
    ]);
}

$configuredOrigins = array_values(array_filter(array_map(
    static fn (string $origin): string => trim($origin),
    explode(',', (string) env('ECOMMERCE_ALLOWED_ORIGINS', ''))
)));

$allowedOrigins = array_values(array_unique(array_merge(
    $defaultOrigins,
    $configuredOrigins,
)));

$defaultOriginPatterns = [
    '/^https?:\\/\\/.*\\.vercel\\.app$/',
    '/^https?:\\/\\/(localhost|127\\.0\\.0\\.1)(:\\d+)?$/',
];

$configuredOriginPatterns = array_values(array_filter(array_map(
    static fn (string $pattern): string => trim($pattern),
    explode(',', (string) env('ECOMMERCE_ALLOWED_ORIGIN_PATTERNS', ''))
)));

$allowedOriginPatterns = array_values(array_unique(array_merge(
    $defaultOriginPatterns,
    $configuredOriginPatterns,
)));

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => $allowedOrigins,
    'allowed_origins_patterns' => $allowedOriginPatterns,
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
