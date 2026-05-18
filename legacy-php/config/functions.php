<?php
/**
 * Global Helper Functions
 * Dropshipping Ecommerce Website
 * ------------------------------------------------
 * All reusable utility functions used across the site.
 * Loaded automatically via config.php
 */

// ═══════════════════════════════════════════════════════════════
// SETTINGS
// ═══════════════════════════════════════════════════════════════

/**
 * Get a site setting value by key.
 * Falls back to $default if not found.
 * 
 * Optimized: Uses a static local cache for the duration of the request.
 */
function backendApiConfig(): array
{
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    $defaults = [
        'enabled' => false,
        'base_url' => '',
        'timeout_seconds' => 0.35,
        'verify_ssl' => true,
    ];

    $configFile = CONFIG_PATH . '/backend_api.php';
    if (file_exists($configFile)) {
        $loaded = require $configFile;
        if (is_array($loaded)) {
            $config = array_merge($defaults, $loaded);
            $config['base_url'] = rtrim((string)($config['base_url'] ?? ''), '/');
            $config['enabled'] = !empty($config['enabled']) && $config['base_url'] !== '';
            $config['timeout_seconds'] = max(0.1, (float)($config['timeout_seconds'] ?? 0.35));
            $config['verify_ssl'] = (bool)($config['verify_ssl'] ?? true);
            return $config;
        }
    }

    $envBaseUrl = rtrim((string)(getenv('BACKEND_API_URL') ?: ''), '/');
    if ($envBaseUrl !== '') {
        $defaults['enabled'] = true;
        $defaults['base_url'] = $envBaseUrl;
    }

    $config = $defaults;
    return $config;
}

function backendApiEnabled(): bool
{
    $config = backendApiConfig();
    return !empty($config['enabled']) && !empty($config['base_url']);
}

function backendApiBaseUrl(): string
{
    return (string)(backendApiConfig()['base_url'] ?? '');
}

function backendApiGet(string $path, array $query = []): ?array
{
    static $requestCache = [];

    if (!backendApiEnabled()) {
        return null;
    }

    $cacheKey = $path . '?' . http_build_query($query);
    if (array_key_exists($cacheKey, $requestCache)) {
        return $requestCache[$cacheKey];
    }

    $config = backendApiConfig();
    $url = backendApiBaseUrl() . '/' . ltrim($path, '/');
    if ($query) {
        $url .= '?' . http_build_query($query);
    }

    $body = null;
    $statusCode = 0;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT_MS => (int)round($config['timeout_seconds'] * 1000),
            CURLOPT_CONNECTTIMEOUT_MS => (int)round($config['timeout_seconds'] * 1000),
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_SSL_VERIFYPEER => $config['verify_ssl'],
            CURLOPT_SSL_VERIFYHOST => $config['verify_ssl'] ? 2 : 0,
        ]);
        $body = curl_exec($ch);
        $statusCode = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => (float)$config['timeout_seconds'],
                'header' => "Accept: application/json\r\n",
            ],
            'ssl' => [
                'verify_peer' => $config['verify_ssl'],
                'verify_peer_name' => $config['verify_ssl'],
            ],
        ]);

        $body = @file_get_contents($url, false, $context);
        $responseHeaders = function_exists('http_get_last_response_headers')
            ? (http_get_last_response_headers() ?: [])
            : ($http_response_header ?? []);

        if (isset($responseHeaders[0]) && preg_match('/\s(\d{3})\s/', (string)$responseHeaders[0], $matches)) {
            $statusCode = (int)$matches[1];
        }
    }

    if (!is_string($body) || $body === '' || $statusCode >= 400) {
        $requestCache[$cacheKey] = null;
        return null;
    }

    $decoded = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
        $requestCache[$cacheKey] = null;
        return null;
    }

    $requestCache[$cacheKey] = $decoded;
    return $decoded;
}

function normalizeApiProduct(array $product): array
{
    $images = $product['images'] ?? [];
    if (!is_array($images)) {
        $decoded = json_decode((string)$images, true);
        $images = is_array($decoded) ? $decoded : [];
    }

    $product['images'] = $images;
    $price = isset($product['price']) ? (float)$product['price'] : 0.0;
    $salePrice = isset($product['sale_price']) ? (float)$product['sale_price'] : 0.0;
    $product['price'] = $price;
    $product['sale_price'] = $salePrice;
    $product['effective_price'] = isset($product['effective_price'])
        ? (float)$product['effective_price']
        : ($salePrice > 0 ? $salePrice : $price);

    return $product;
}

function getSetting(string $key, string $default = ''): string
{
    static $settings = null;
    
    // Load all settings once per request
    if ($settings === null) {
        $settings = getCache('site_settings');
        if ($settings === null) {
            try {
                $db   = getDB();
                $stmt = $db->query("SELECT key_name, value FROM settings");
                $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                $settings = $rows ?: [];
                setCache('site_settings', $settings, 3600); // Cache for 1 hour
            } catch (Exception $e) {
                error_log('[SETTINGS] Load failed: ' . $e->getMessage());
                $settings = [];
            }
        }

        $apiSettings = backendApiGet('settings/public');
        if (is_array($apiSettings['data'] ?? null)) {
            $settings = array_merge($settings, $apiSettings['data']);
        }
    }
    
    return $settings[$key] ?? $default;
}

/**
 * Refresh a single setting or entire settings cache.
 */
function refreshSetting(?string $key = null): void
{
    deleteCache('site_settings');
}

/**
 * Normalize and validate header menu items stored in settings.
 */
function sanitizeHeaderMenuItems(array $items): array
{
    $normalized = [];

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $label = trim(strip_tags((string)($item['label'] ?? '')));
        $url = trim(strip_tags((string)($item['url'] ?? '')));

        if ($label === '' || $url === '') {
            continue;
        }

        $normalized[] = [
            'label' => $label,
            'url' => $url,
        ];

        if (count($normalized) >= 12) {
            break;
        }
    }

    return $normalized;
}

/**
 * Default storefront header navigation.
 */
function defaultHeaderMenuItems(): array
{
    $items = [
        ['label' => 'Home', 'url' => '/'],
    ];

    foreach (getParentCategories(6) as $category) {
        $items[] = [
            'label' => (string)$category['name'],
            'url' => 'products.php?category=' . (string)$category['slug'],
        ];
    }

    $items[] = ['label' => 'Sale', 'url' => 'products.php?featured=1'];

    return sanitizeHeaderMenuItems($items);
}

/**
 * Fetch the configured header navigation or fall back to defaults.
 */
function getHeaderMenuItems(bool $fallbackToDefault = true): array
{
    $raw = trim(getSetting('header_menu_items', ''));

    if ($raw !== '') {
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return sanitizeHeaderMenuItems($decoded);
        }
    }

    return $fallbackToDefault ? defaultHeaderMenuItems() : [];
}

/**
 * Convert a menu link into a usable storefront URL.
 */
function menuItemUrl(string $path): string
{
    $path = trim($path);

    if ($path === '') {
        return '#';
    }

    if (preg_match('/^(https?:|mailto:|tel:|#)/i', $path)) {
        return $path;
    }

    return url(ltrim($path, '/'));
}

/**
 * Check whether a menu item points to the current request.
 */
function isMenuItemActive(string $path): bool
{
    $resolvedUrl = menuItemUrl($path);

    if (preg_match('/^(mailto:|tel:|#)/i', $resolvedUrl)) {
        return false;
    }

    $menuPath = trim((string)parse_url($resolvedUrl, PHP_URL_PATH), '/');
    $currentPath = trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');

    $menuBase = basename($menuPath);
    $currentBase = basename($currentPath);

    $isMenuHome = $menuPath === '' || $menuBase === 'index.php';
    $isCurrentHome = $currentPath === '' || $currentBase === 'index.php';

    if ($isMenuHome && !$isCurrentHome) {
        return false;
    }

    if (!$isMenuHome && $menuPath !== $currentPath && $menuBase !== $currentBase) {
        return false;
    }

    $menuQuery = [];
    parse_str((string)parse_url($resolvedUrl, PHP_URL_QUERY), $menuQuery);

    foreach ($menuQuery as $key => $value) {
        if (!isset($_GET[$key]) || (string)$_GET[$key] !== (string)$value) {
            return false;
        }
    }

    return true;
}

/**
 * Fetch a dynamic page by slug.
 */
function getDynamicPageBySlug(string $slug, bool $activeOnly = true): ?array
{
    $slug = trim($slug);
    if ($slug === '') {
        return null;
    }

    try {
        $sql = "SELECT * FROM pages WHERE slug = ?";
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " LIMIT 1";

        $stmt = getDB()->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    } catch (Exception $e) {
        error_log('[PAGES] getDynamicPageBySlug: ' . $e->getMessage());
        return null;
    }
}

/**
 * Build a public URL for a dynamic page slug.
 * If a dedicated PHP file exists, prefer that file for cleaner URLs.
 */
function dynamicPageUrl(string $slug): string
{
    $slug = trim($slug, '/');
    if ($slug === '') {
        return url();
    }

    $pageFile = ROOT_PATH . '/' . $slug . '.php';
    if (file_exists($pageFile)) {
        return url($slug . '.php');
    }

    return url('page.php?slug=' . rawurlencode($slug));
}

/**
 * Quick runtime database health snapshot for admin diagnostics.
 */
function getDatabaseStatus(): array
{
    try {
        $db = getDB();

        return [
            'connected' => true,
            'database' => (string)$db->query("SELECT DATABASE()")->fetchColumn(),
            'tables' => (int)$db->query("SHOW TABLES")->rowCount(),
            'driver' => (string)$db->getAttribute(PDO::ATTR_DRIVER_NAME),
            'version' => (string)$db->getAttribute(PDO::ATTR_SERVER_VERSION),
            'message' => 'Connection healthy.'
        ];
    } catch (Throwable $e) {
        return [
            'connected' => false,
            'database' => DB_NAME,
            'tables' => 0,
            'driver' => 'mysql',
            'version' => '',
            'message' => $e->getMessage()
        ];
    }
}

// ═══════════════════════════════════════════════════════════════
// CACHING HELPERS (FILE-BASED)
// ═══════════════════════════════════════════════════════════════

/**
 * Store data in a file-based cache.
 */
function setCache(string $key, mixed $data, int $ttl = 3600): bool
{
    $cacheDir = ROOT_PATH . '/tmp/cache';
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0755, true);
    }
    
    if (!is_writable($cacheDir)) {
        return false;
    }
    
    $file = $cacheDir . '/' . md5($key) . '.cache';
    $cacheData = [
        'expiry' => time() + $ttl,
        'data'   => $data
    ];
    
    return @file_put_contents($file, serialize($cacheData)) !== false;
}

/**
 * Retrieve data from file-based cache.
 */
function getCache(string $key): mixed
{
    $file = ROOT_PATH . '/tmp/cache/' . md5($key) . '.cache';
    if (!file_exists($file)) {
        return null;
    }
    
    $raw = file_get_contents($file);
    $cacheData = unserialize($raw);
    
    if (!$cacheData || (isset($cacheData['expiry']) && time() > $cacheData['expiry'])) {
        @unlink($file);
        return null;
    }
    
    return $cacheData['data'] ?? null;
}

/**
 * Delete a specific cache key.
 */
function deleteCache(string $key): void
{
    $file = ROOT_PATH . '/tmp/cache/' . md5($key) . '.cache';
    if (file_exists($file)) {
        @unlink($file);
    }
}

// ═══════════════════════════════════════════════════════════════
// CSRF TOKEN
// ═══════════════════════════════════════════════════════════════

/**
 * Generate (or retrieve) the CSRF token for this session.
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render a hidden CSRF input field.
 */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

/**
 * Validate the submitted CSRF token.
 * Dies with 403 if invalid.
 */
function validateCsrf(): void
{
    $submitted = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals(csrfToken(), $submitted)) {
        http_response_code(403);
        die(json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh and try again.']));
    }
}

// ═══════════════════════════════════════════════════════════════
// OUTPUT SANITIZATION
// ═══════════════════════════════════════════════════════════════

/**
 * Safely escape output to prevent XSS.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Sanitize a plain-text input (trim + strip tags).
 */
function clean(string $value): string
{
    return trim(strip_tags($value));
}

/**
 * Sanitize email input.
 */
function cleanEmail(string $email): string
{
    return strtolower(trim(filter_var($email, FILTER_SANITIZE_EMAIL)));
}

/**
 * Get input from GET/POST and clean it securely.
 * If $shouldClean is false, returns the raw value (useful for passwords).
 */
function inputStr(string $key, mixed $default = '', string $method = 'REQUEST', bool $shouldClean = true): string
{
    $val = match(strtoupper($method)) {
        'POST' => $_POST[$key] ?? $default,
        'GET'  => $_GET[$key] ?? $default,
        default => $_REQUEST[$key] ?? $default,
    };
    return $shouldClean ? clean((string)$val) : (string)$val;
}

// ═══════════════════════════════════════════════════════════════
// URL & REDIRECT
// ═══════════════════════════════════════════════════════════════

/**
 * Redirect to a URL (relative or absolute).
 */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Return the full URL for a given relative path.
 * If the path is already an absolute URL, return it as-is.
 */
function url(string $path = ''): string
{
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        return $path;
    }
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Generate a URL-friendly slug from a string.
 */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

// ═══════════════════════════════════════════════════════════════
// FLASH MESSAGES
// ═══════════════════════════════════════════════════════════════

/**
 * Set a flash message ('success' | 'error' | 'warning' | 'info').
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Render and clear all flash messages (Bootstrap 5 alerts).
 */
function showFlash(): string
{
    if (empty($_SESSION['flash'])) {
        return '';
    }
    $html = '';
    foreach ($_SESSION['flash'] as $flash) {
        $type = e($flash['type']);
        $msg  = e($flash['message']);
        $icon = match($flash['type']) {
            'success' => 'fa-circle-check',
            'error'   => 'fa-circle-xmark',
            'warning' => 'fa-triangle-exclamation',
            default   => 'fa-circle-info',
        };
        $bsType = $flash['type'] === 'error' ? 'danger' : $flash['type'];
        $html .= <<<HTML
        <div class="alert alert-{$bsType} alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="fa-solid {$icon}"></i>
            <span>{$msg}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        HTML;
    }
    unset($_SESSION['flash']);
    return $html;
}

// ═══════════════════════════════════════════════════════════════
// PRICE / CURRENCY
// ═══════════════════════════════════════════════════════════════

/**
 * Format a number as Indian Rupee price.
 * e.g. formatPrice(1234.5) → "₹1,234.50"
 */
function formatPrice(float $amount): string
{
    return SITE_CURRENCY_SYMBOL . number_format($amount, 2, '.', ',');
}

/**
 * Calculate discount percentage.
 */
function discountPercent(?float $original, ?float $sale): int
{
    $original = (float)$original;
    $sale = (float)$sale;
    if ($original <= 0) return 0;
    return (int)round((($original - $sale) / $original) * 100);
}

/**
 * Calculate shipping cost for a cart subtotal.
 */
function calculateShipping(?float $subtotal, ?string $state = null): float
{
    $subtotal = (float)$subtotal;
    if ($subtotal >= FREE_SHIPPING_ABOVE) {
        return 0.0;
    }
    if ($state) {
        try {
            $db   = getDB();
            $stmt = $db->prepare(
                "SELECT shipping_cost FROM shipping_zones WHERE state = ? AND is_active = 1 LIMIT 1"
            );
            $stmt->execute([$state]);
            $row = $stmt->fetch();
            if ($row) return (float)$row['shipping_cost'];
        } catch (Exception $e) {
            error_log('[SHIPPING] ' . $e->getMessage());
        }
    }
    return DEFAULT_SHIPPING;
}

/**
 * Calculate GST on a subtotal (after shipping, before GST).
 */
function calculateGst(?float $amount): float
{
    $amount = (float)$amount;
    return round($amount * (GST_PERCENT / 100), 2);
}

// ═══════════════════════════════════════════════════════════════
// CART
// ═══════════════════════════════════════════════════════════════

/**
 * Return total number of items in the current cart.
 */
function cartCount(): int
{
    $db = getDB();
    if (!empty($_SESSION['user']['id'])) {
        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = ?"
        );
        $stmt->execute([$_SESSION['user']['id']]);
    } else {
        $sid  = session_id();
        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE session_id = ? AND user_id IS NULL"
        );
        $stmt->execute([$sid]);
    }
    return (int)$stmt->fetchColumn();
}

/**
 * Return cart items with product details for the current user/session.
 */
function getCartItems(): array
{
    $db = getDB();
    if (!empty($_SESSION['user']['id'])) {
        $stmt = $db->prepare(
            "SELECT c.*, p.name, p.slug, p.images, p.gst_percent, p.shipping_type, p.shipping_fee, p.sku, p.hsn_code,
                    COALESCE(v.price, p.sale_price, p.price) AS unit_price,
                    p.stock AS total_stock,
                    v.size, v.color, v.stock AS variant_stock
             FROM cart c
             JOIN products p ON c.product_id = p.id
             LEFT JOIN product_variants v ON c.variant_id = v.id
             WHERE c.user_id = ?
             ORDER BY c.id ASC"
        );
        $stmt->execute([$_SESSION['user']['id']]);
    } else {
        $sid  = session_id();
        $stmt = $db->prepare(
            "SELECT c.*, p.name, p.slug, p.images, p.gst_percent, p.shipping_type, p.shipping_fee, p.sku, p.hsn_code,
                    COALESCE(v.price, p.sale_price, p.price) AS unit_price,
                    p.stock AS total_stock,
                    v.size, v.color, v.stock AS variant_stock
             FROM cart c
             JOIN products p ON c.product_id = p.id
             LEFT JOIN product_variants v ON c.variant_id = v.id
             WHERE c.session_id = ? AND c.user_id IS NULL
             ORDER BY c.id ASC"
        );
        $stmt->execute([$sid]);
    }
    $items = $stmt->fetchAll();

    // Decode images JSON & pick first image
    foreach ($items as &$item) {
        $imgs = json_decode($item['images'] ?? '[]', true);
        $item['thumb'] = !empty($imgs) ? $imgs[0] : 'assets/img/no-image.svg';
        $item['line_total'] = $item['unit_price'] * $item['quantity'];
        
        // Calculate per-item GST
        $item['item_gst'] = round(($item['line_total'] * ($item['gst_percent'] / 100)), 2);
        
        // Calculate shipping per item logic
        if ($item['shipping_type'] === 'free') {
            $item['item_shipping'] = 0;
        } elseif ($item['shipping_type'] === 'custom') {
            $item['item_shipping'] = (float)$item['shipping_fee'];
        } else {
            $item['item_shipping'] = -1; // Flag for global default
        }
    }
    return $items;
}

/**
 * Return cart totals array: subtotal, shipping, tax, discount, total.
 */
function cartTotals(?float $couponDiscount = null, ?string $state = null): array
{
    $items    = getCartItems();
    $subtotal = 0;
    $totalGst = 0;
    $totalShip = 0;
    $hasDefaultShip = false;

    foreach ($items as $item) {
        $subtotal  += $item['line_total'];
        $totalGst += $item['item_gst'];
        
        if ($item['item_shipping'] === -1.0 || $item['item_shipping'] === -1) {
            $hasDefaultShip = true;
        } else {
            $totalShip += $item['item_shipping'];
        }
    }

    // Handline Global Default Shipping if any item uses it
    if ($hasDefaultShip) {
        $totalShip += calculateShipping($subtotal, $state);
    }

    $discount = $couponDiscount ?? ($_SESSION['coupon_discount'] ?? 0);
    $tax      = $totalGst; // Already calculated per product
    $shipping = $totalShip;
    $total    = max(0, $subtotal - $discount) + $shipping + $tax;

    return compact('subtotal', 'shipping', 'discount', 'tax', 'total');
}

// ═══════════════════════════════════════════════════════════════
// ORDER
// ═══════════════════════════════════════════════════════════════

/**
 * Generate a unique order number e.g. ORD-2026-AB12CD
 */
function generateOrderNumber(): string
{
    $prefix = 'ORD-' . date('Y') . '-';
    $unique = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    return $prefix . $unique;
}

/**
 * Get human-readable order status label.
 */
function orderStatusLabel(string $status): string
{
    return match($status) {
        'pending'    => 'Pending',
        'confirmed'  => 'Confirmed',
        'processing' => 'Processing',
        'shipped'    => 'Shipped',
        'delivered'  => 'Delivered',
        'cancelled'  => 'Cancelled',
        'refunded'   => 'Refunded',
        default      => ucfirst($status),
    };
}

/**
 * Get Bootstrap badge color class for order status.
 */
function orderStatusBadge(string $status): string
{
    return match($status) {
        'pending'    => 'warning',
        'confirmed'  => 'info',
        'processing' => 'primary',
        'shipped'    => 'indigo',
        'delivered'  => 'success',
        'cancelled'  => 'danger',
        'refunded'   => 'secondary',
        default      => 'secondary',
    };
}

/**
 * Get Bootstrap badge color class for payment status.
 */
function paymentStatusBadge(string $status): string
{
    return match($status) {
        'paid'     => 'success',
        'pending'  => 'warning',
        'failed'   => 'danger',
        'refunded' => 'secondary',
        default    => 'secondary',
    };
}

// ═══════════════════════════════════════════════════════════════
// PRODUCT
// ═══════════════════════════════════════════════════════════════

/**
 * Get the first image of a product (from JSON images column).
 */
function productThumb(mixed $images, string $fallback = 'assets/img/no-image.svg'): string
{
    if (is_string($images)) {
        $images = json_decode($images, true);
    }
    if (!empty($images) && is_array($images)) {
        return e($images[0]);
    }
    return e($fallback);
}

/**
 * Get product by slug (active only).
 */
function getProductBySlug(string $slug): ?array
{
    $apiProduct = backendApiGet('catalog/products/' . rawurlencode($slug));
    if (!empty($apiProduct['success']) && is_array($apiProduct['data'] ?? null)) {
        return normalizeApiProduct($apiProduct['data']);
    }

    try {
        $db   = getDB();
        $stmt = $db->prepare(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM products p
             JOIN categories c ON p.category_id = c.id
             WHERE p.slug = ? AND p.is_active = 1
             LIMIT 1"
        );
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    } catch (Exception $e) {
        error_log('[PRODUCT] getProductBySlug: ' . $e->getMessage());
        return null;
    }
}

/**
 * Get a paginated list of active products.
 */
function getProducts(array $filters = [], int $page = 1, int $perPage = PRODUCTS_PER_PAGE): array
{
    $query = [
        'page' => max(1, $page),
        'per_page' => max(1, $perPage),
    ];

    if (!empty($filters['category'])) {
        $query['category'] = (string)$filters['category'];
    }
    if (!empty($filters['featured'])) {
        $query['featured'] = 1;
    }
    if (!empty($filters['search'])) {
        $query['q'] = (string)$filters['search'];
    }
    if (!empty($filters['sort'])) {
        $query['sort'] = (string)$filters['sort'];
    }
    if (!empty($filters['min_price'])) {
        $query['min_price'] = (float)$filters['min_price'];
    }
    if (!empty($filters['max_price'])) {
        $query['max_price'] = (float)$filters['max_price'];
    }

    $apiProducts = backendApiGet('catalog/products', $query);
    if (!empty($apiProducts['success']) && is_array($apiProducts['data'] ?? null)) {
        $payload = $apiProducts['data'];
        $items = array_map('normalizeApiProduct', (array)($payload['items'] ?? []));
        $pagination = (array)($payload['pagination'] ?? []);

        return [
            'items' => $items,
            'total' => (int)($pagination['total'] ?? count($items)),
            'page' => (int)($pagination['current_page'] ?? $page),
            'per_page' => (int)($pagination['per_page'] ?? $perPage),
            'total_pages' => (int)($pagination['last_page'] ?? 1),
        ];
    }

    $db     = getDB();
    $where  = ['p.is_active = 1'];
    $params = [];

    if (!empty($filters['category_id'])) {
        $where[]  = 'p.category_id = ?';
        $params[] = (int)$filters['category_id'];
    }
    if (!empty($filters['min_price'])) {
        $where[]  = 'COALESCE(p.sale_price, p.price) >= ?';
        $params[] = (float)$filters['min_price'];
    }
    if (!empty($filters['max_price'])) {
        $where[]  = 'COALESCE(p.sale_price, p.price) <= ?';
        $params[] = (float)$filters['max_price'];
    }
    if (!empty($filters['featured'])) {
        $where[] = 'p.is_featured = 1';
    }
    if (!empty($filters['search'])) {
        $term = '%' . $filters['search'] . '%';
        $where[]  = '(p.name LIKE ? OR p.description LIKE ?)';
        $params[] = $term;
        $params[] = $term;
    }

    $orderMap = [
        'price_asc'  => 'COALESCE(p.sale_price, p.price) ASC',
        'price_desc' => 'COALESCE(p.sale_price, p.price) DESC',
        'newest'     => 'p.created_at DESC',
        'popular'    => 'p.total_sold DESC',
        'rating'     => 'p.avg_rating DESC',
    ];
    $orderBy = $orderMap[$filters['sort'] ?? ''] ?? 'p.created_at DESC';

    $whereSql = implode(' AND ', $where);
    $offset   = ($page - 1) * $perPage;

    // Total count
    $countStmt = $db->prepare(
        "SELECT COUNT(*) FROM products p WHERE {$whereSql}"
    );
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    // Paginated results
    $stmt = $db->prepare(
        "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                COALESCE(p.sale_price, p.price) AS effective_price
         FROM products p
         JOIN categories c ON p.category_id = c.id
         WHERE {$whereSql}
         ORDER BY {$orderBy}
         LIMIT {$perPage} OFFSET {$offset}"
    );
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    return [
        'items'       => $items,
        'total'       => $total,
        'page'        => $page,
        'per_page'    => $perPage,
        'total_pages' => (int)ceil($total / $perPage),
    ];
}

// ═══════════════════════════════════════════════════════════════
// CATEGORIES
// ═══════════════════════════════════════════════════════════════

/**
 * Get all active parent categories.
 */
function getParentCategories(?int $limit = null): array
{
    $query = [];
    if ($limit) {
        $query['limit'] = $limit;
    }

    $apiCategories = backendApiGet('catalog/categories', $query);
    if (!empty($apiCategories['success']) && is_array($apiCategories['data'] ?? null)) {
        return (array)$apiCategories['data'];
    }

    try {
        $sql = "SELECT * FROM categories WHERE parent_id IS NULL AND is_active = 1 ORDER BY sort_order, name";
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        $stmt = getDB()->query($sql);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log('[CAT] getParentCategories: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get sub-categories of a parent.
 */
function getSubCategories(int $parentId): array
{
    try {
        $stmt = getDB()->prepare(
            "SELECT * FROM categories WHERE parent_id = ? AND is_active = 1 ORDER BY sort_order, name"
        );
        $stmt->execute([$parentId]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get all categories in a flat array (for admin dropdowns).
 */
function getAllCategories(): array
{
    try {
        $stmt = getDB()->query(
            "SELECT c.*, p.name AS parent_name
             FROM categories c
             LEFT JOIN categories p ON c.parent_id = p.id
             ORDER BY COALESCE(c.parent_id, c.id), c.sort_order, c.name"
        );
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

// ═══════════════════════════════════════════════════════════════
// COUPON
// ═══════════════════════════════════════════════════════════════

/**
 * Validate a coupon code against a subtotal.
 * Returns ['success'=>bool, 'message'=>string, 'discount'=>float, 'coupon'=>array|null]
 */
function validateCoupon(string $code, float $subtotal): array
{
    $code = strtoupper(trim($code));
    try {
        $db   = getDB();
        $stmt = $db->prepare(
            "SELECT * FROM coupons WHERE code = ? AND is_active = 1 LIMIT 1"
        );
        $stmt->execute([$code]);
        $coupon = $stmt->fetch();

        if (!$coupon) {
            return ['success' => false, 'message' => 'Invalid coupon code.', 'discount' => 0, 'coupon' => null];
        }
        if ($coupon['expiry_date'] && strtotime($coupon['expiry_date']) < time()) {
            return ['success' => false, 'message' => 'This coupon has expired.', 'discount' => 0, 'coupon' => null];
        }
        if ($coupon['max_uses'] !== null && $coupon['used_count'] >= $coupon['max_uses']) {
            return ['success' => false, 'message' => 'Coupon usage limit reached.', 'discount' => 0, 'coupon' => null];
        }

        // --- NEW: One-time use per customer check ---
        $userId = currentUserId();
        if ($userId) {
            $stmtUsage = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND coupon_id = ? AND status != 'cancelled'");
            $stmtUsage->execute([$userId, $coupon['id']]);
            if ($stmtUsage->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'You have already used this coupon once.', 'discount' => 0, 'coupon' => null];
            }
        }
        // --------------------------------------------

        if ($subtotal < $coupon['min_order']) {
            return [
                'success'  => false,
                'message'  => 'Minimum order of ' . formatPrice($coupon['min_order']) . ' required.',
                'discount' => 0,
                'coupon'   => null,
            ];
        }

        $discount = ($coupon['type'] === 'percent')
            ? round($subtotal * ($coupon['value'] / 100), 2)
            : $coupon['value'];

        $discount = min($discount, $subtotal); // Cannot exceed subtotal

        return ['success' => true, 'message' => 'Coupon applied!', 'discount' => $discount, 'coupon' => $coupon];
    } catch (Exception $e) {
        error_log('[COUPON] ' . $e->getMessage());
        return ['success' => false, 'message' => 'Error validating coupon.', 'discount' => 0, 'coupon' => null];
    }
}

// ═══════════════════════════════════════════════════════════════
// IMAGE UPLOAD
// ═══════════════════════════════════════════════════════════════

/**
 * Upload and validate an image file.
 * Returns the relative path on success, false on failure.
 */
function uploadImage(array $file, string $subfolder = 'products'): string|false
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }

    // Validate MIME type from file content (not just extension)
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_MIME_TYPES, true)) {
        return false;
    }

    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS, true)) {
        return false;
    }

    $dir = UPLOAD_PATH . '/' . $subfolder . '/';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $filename = bin2hex(random_bytes(12)) . '_' . time() . '.' . $ext;
    $dest     = $dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return false;
    }

    return 'uploads/' . $subfolder . '/' . $filename;
}

// ═══════════════════════════════════════════════════════════════
// PAGINATION
// ═══════════════════════════════════════════════════════════════

/**
 * Render Bootstrap 5 pagination HTML.
 */
function paginationLinks(int $currentPage, int $totalPages, string $baseUrl): string
{
    if ($totalPages <= 1) return '';

    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center flex-wrap">';

    // Previous
    if ($currentPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . e($baseUrl . '&page=' . ($currentPage - 1)) . '">&laquo;</a></li>';
    }

    // Pages
    $start = max(1, $currentPage - 2);
    $end   = min($totalPages, $currentPage + 2);

    if ($start > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . e($baseUrl . '&page=1') . '">1</a></li>';
        if ($start > 2) $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
    }

    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $currentPage ? ' active' : '';
        $html  .= '<li class="page-item' . $active . '"><a class="page-link" href="' . e($baseUrl . '&page=' . $i) . '">' . $i . '</a></li>';
    }

    if ($end < $totalPages) {
        if ($end < $totalPages - 1) $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
        $html .= '<li class="page-item"><a class="page-link" href="' . e($baseUrl . '&page=' . $totalPages) . '">' . $totalPages . '</a></li>';
    }

    // Next
    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . e($baseUrl . '&page=' . ($currentPage + 1)) . '">&raquo;</a></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}

// ═══════════════════════════════════════════════════════════════
// USER / AUTH
// ═══════════════════════════════════════════════════════════════

/**
 * Return currently logged-in user ID (or null).
 */
function currentUserId(): ?int
{
    return isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
}

/**
 * Check if a user is logged in.
 */
function isLoggedIn(): bool
{
    return !empty($_SESSION['user']['id']);
}

/**
 * Check if the current user is an admin.
 */
function isAdmin(): bool
{
    return !empty($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
}

/**
 * Require login — redirect to login page if not logged in.
 */
function requireLogin(string $redirectTo = '/login.php'): void
{
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect(url('login.php'));
    }
}

/**
 * Get the logged-in user's data from DB.
 */
function currentUser(): ?array
{
    if (!isLoggedIn()) return null;
    try {
        $stmt = getDB()->prepare(
            "SELECT id, name, email, phone, address, city, state, pincode, role FROM users WHERE id = ? AND is_active = 1"
        );
        $stmt->execute([$_SESSION['user']['id']]);
        return $stmt->fetch() ?: null;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Keep wishlist session data aligned for logged-in users.
 */
function syncWishlistSession(): void
{
    static $synced = false;

    if ($synced || !isLoggedIn()) {
        return;
    }

    try {
        $stmt = getDB()->prepare("SELECT product_id FROM wishlists WHERE user_id = ?");
        $stmt->execute([currentUserId()]);
        $wishlist = [];

        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $productId) {
            $wishlist[(int)$productId] = true;
        }

        $_SESSION['wishlist'] = $wishlist;
        $synced = true;
    } catch (Exception $e) {
        error_log('[WISHLIST] syncWishlistSession: ' . $e->getMessage());
    }
}

/**
 * Send a professional HTML email using PHPMailer and SMTP settings.
 * Returns true on success, false on failure.
 */
function sendEmail(string $to, string $subject, string $body, array $attachments = [], bool $isAdminReset = false): bool
{
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // --- TIERED SMTP CONFIGURATION ---
        
        // Master Saaszo Credentials (Provided)
        $masterConfig = [
            'host' => 'smtp.hostinger.com',
            'user' => 'noreply@saaszo.in',
            'pass' => 'Saaszo@9891659423',
            'port' => 465,
            'enc'  => 'ssl'
        ];

        // Fetch Custom Settings (if any)
        $customHost = getSetting('smtp_host');
        $customUser = getSetting('smtp_user');
        $customPass = getSetting('smtp_pass');
        $customPort = (int)getSetting('smtp_port', '587');
        $customEnc  = getSetting('smtp_encryption', 'tls');

        // Logic Selection
        if ($isAdminReset || empty($customHost) || empty($customUser)) {
            // Use Master Fallback
            $host = $masterConfig['host'];
            $user = $masterConfig['user'];
            $pass = $masterConfig['pass'];
            $port = $masterConfig['port'];
            $enc  = $masterConfig['enc'];
        } else {
            // Use Custom Store SMTP
            $host = $customHost;
            $user = $customUser;
            $pass = $customPass;
            $port = $customPort;
            $enc  = $customEnc;
        }

        // Server settings
        $mail->isSMTP();
        $mail->Host       = $host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $user;
        $mail->Password   = $pass;
        $mail->SMTPSecure = ($enc === 'none') ? false : $enc;
        $mail->Port       = $port;
        
        // Timeout
        $mail->Timeout    = 10;

        // Recipients
        // We use the Store Name as the sender name even if using fallback mailer
        $fromName = getSetting('site_name', 'MyShop');
        if ($isAdminReset) $fromName = "Admin - " . $fromName;
        
        $mail->setFrom($user, $fromName);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        
        // Wrap body in a professional template
        $primaryColor = getSetting('theme_primary_color', '#0d6efd');
        $siteName     = e(getSetting('site_name', 'MyShop'));
        
        $styledBody = <<<HTML
        <div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; line-height: 1.6; max-width: 600px; margin: 0 auto; border: 1px solid #eee; border-radius: 8px; overflow: hidden;">
            <div style="background-color: {$primaryColor}; padding: 30px; text-align: center;">
                <h1 style="color: #fff; margin: 0; font-size: 24px;">{$siteName}</h1>
            </div>
            <div style="padding: 30px; background-color: #ffffff;">
                {$body}
            </div>
            <div style="padding: 20px; background-color: #f8f9fa; border-top: 1px solid #eee; text-align: center; font-size: 12px; color: #6c757d;">
                &copy; {{year}} {$siteName}. All rights reserved.<br>
                If you have any questions, please contact our support team.
            </div>
        </div>
        HTML;
        
        $mail->Body = str_replace('{{year}}', date('Y'), $styledBody);

        // Attachments
        foreach ($attachments as $path => $name) {
            if (is_numeric($path)) {
                $mail->addAttachment($name); // If only path is provided
            } else {
                $mail->addAttachment($path, $name); // Path and Custom name
            }
        }

        return $mail->send();
    } catch (Exception $e) {
        error_log('[MAIL] Error: ' . $mail->ErrorInfo . ' | Msg: ' . $e->getMessage());
        return false;
    }
}

/**
 * Send a notification to a user.
 */
function addNotification(int $userId, string $message, string $type = 'system', string $link = ''): void
{
    try {
        $db   = getDB();
        $stmt = $db->prepare(
            "INSERT INTO notifications (user_id, type, message, link) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$userId, $type, $message, $link]);
    } catch (Exception $e) {
        error_log('[NOTIF] ' . $e->getMessage());
    }
}

// ═══════════════════════════════════════════════════════════════
// DATE / TIME
// ═══════════════════════════════════════════════════════════════

/**
 * Format a MySQL datetime string to a readable Indian date.
 */
function formatDate(string $datetime, string $format = 'd M Y'): string
{
    return date($format, strtotime($datetime));
}

/**
 * Return a "time ago" string (e.g. "3 hours ago").
 */
function timeAgo(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60)       return 'just now';
    if ($diff < 3600)     return (int)($diff / 60) . ' min ago';
    if ($diff < 86400)    return (int)($diff / 3600) . ' hrs ago';
    if ($diff < 2592000)  return (int)($diff / 86400) . ' days ago';
    return date('d M Y', strtotime($datetime));
}

// ═══════════════════════════════════════════════════════════════
// MISC UTILITIES
// ═══════════════════════════════════════════════════════════════

/**
 * Truncate a string to a max length.
 */
function truncate(string $text, int $length = 100, string $suffix = '…'): string
{
    $text = strip_tags($text);
    return mb_strlen($text) > $length
        ? mb_substr($text, 0, $length) . $suffix
        : $text;
}

/**
 * Return star rating HTML (Font Awesome icons).
 */
function starRating(float $rating, int $maxStars = 5): string
{
    $html = '<span class="text-warning" aria-label="Rating: ' . number_format($rating, 1) . ' / 5">';
    for ($i = 1; $i <= $maxStars; $i++) {
        if ($rating >= $i) {
            $html .= '<i class="fa-solid fa-star"></i>';
        } elseif ($rating >= $i - 0.5) {
            $html .= '<i class="fa-solid fa-star-half-stroke"></i>';
        } else {
            $html .= '<i class="fa-regular fa-star"></i>';
        }
    }
    $html .= '</span>';
    return $html;
}

/**
 * Send a JSON response and exit.
 */
function jsonResponse(bool $success, string $message, array $data = []): never
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

/**
 * Get a safe integer from $_GET or $_POST.
 */
function inputInt(string $key, int $default = 0, string $method = 'GET'): int
{
    $arr = $method === 'POST' ? $_POST : $_GET;
    return isset($arr[$key]) ? (int)filter_var($arr[$key], FILTER_SANITIZE_NUMBER_INT) : $default;
}

/**
 * Convert Hex color to RGB comma-separated string for CSS variables.
 * e.g. #6366f1 → "99, 102, 241"
 */
function hexToRgb(string $hex): string
{
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) === 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    return "$r, $g, $b";
}


/**
 * Log an application event to a daily log file.
 */
function appLog(string $level, string $message, array $context = []): void
{
    $logDir  = ROOT_PATH . '/logs';
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    $logFile = $logDir . '/' . date('Y-m-d') . '.log';
    $ctx     = !empty($context) ? ' ' . json_encode($context) : '';
    $line    = '[' . date('Y-m-d H:i:s') . '] [' . strtoupper($level) . '] ' . $message . $ctx . PHP_EOL;
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

// ═══════════════════════════════════════════════════════════════
// EMAIL (PHPMAILER)
// ═══════════════════════════════════════════════════════════════

/**
 * Send Password Reset Email using the centralized sendEmail function
 */
function sendResetEmail(string $toEmail, string $resetUrl, bool $isAdmin = false): bool
{
    $siteName = getSetting('site_name', 'MyShop');
    $subject = '🔒 Password Reset Request - ' . $siteName;
    
    $body = "
        <h2 style='color: #333;'>Password Reset Request</h2>
        <p>Hi " . ($isAdmin ? 'Admin' : 'there') . ",</p>
        <p>We received a request to reset your password for <strong>{$siteName}</strong>. If you didn't initiate this, you can safely ignore this email.</p>
        <p>Click the button below to secure your account and set a new password:</p>
        <div style='text-align: center; margin: 30px 0;'>
            <a href='{$resetUrl}' style='display:inline-block; padding:14px 28px; background-color: " . getSetting('theme_primary_color', '#0d6efd') . "; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
                Reset Password
            </a>
        </div>
        <p style='font-size: 14px; color: #666;'>Or copy and paste this URL into your browser:</p>
        <p style='background: #f4f4f4; padding: 10px; font-size: 12px; word-break: break-all; border-radius: 4px;'>{$resetUrl}</p>
        <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
        <p style='font-size: 12px; color: #999; text-align: center;'>This link expires in 1 hour.</p>
    ";

    return sendEmail($toEmail, $subject, $body, [], $isAdmin);
}

/**
 * Send a detailed Order Confirmation email to the buyer.
 */
function sendOrderConfirmationEmail(string $orderNo): bool
{
    try {
        $db = getDB();
        
        // 1. Fetch Order
        $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ?");
        $stmt->execute([$orderNo]);
        $order = $stmt->fetch();
        if (!$order) return false;

        // 2. Fetch Items
        $stmtItems = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmtItems->execute([$order['id']]);
        $items = $stmtItems->fetchAll();

        $siteName = getSetting('site_name', 'MyShop');
        $subject  = "🛍️ Order Confirmed: #{$orderNo} - {$siteName}";
        
        // Build items table
        $itemsHtml = "";
        foreach ($items as $item) {
            $variant = ($item['size'] ? "Size: {$item['size']} " : "") . ($item['color'] ? "Color: {$item['color']}" : "");
            $itemsHtml .= "
                <tr>
                    <td style='padding: 10px; border-bottom: 1px solid #eee;'>
                        <strong>" . e($item['name']) . "</strong><br>
                        <small style='color: #666;'>{$variant} x {$item['quantity']}</small>
                    </td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>" . formatPrice($item['price'] * $item['quantity']) . "</td>
                </tr>
            ";
        }

        $body = "
            <h2 style='color: #333;'>Thank you for your order!</h2>
            <p>Hi " . e($order['ship_name']) . ",</p>
            <p>We've received your order and are getting it ready for shipment. Your order number is <strong>#{$orderNo}</strong>.</p>
            
            <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                <thead>
                    <tr style='background: #f8f9fa;'>
                        <th style='padding: 10px; text-align: left; border-bottom: 2px solid #eee;'>Item</th>
                        <th style='padding: 10px; text-align: right; border-bottom: 2px solid #eee;'>Total</th>
                    </tr>
                </thead>
                <tbody>
                    {$itemsHtml}
                </tbody>
                <tfoot>
                    <tr>
                        <td style='padding: 10px; text-align: right;'><strong>Subtotal:</strong></td>
                        <td style='padding: 10px; text-align: right;'>" . formatPrice($order['subtotal']) . "</td>
                    </tr>
                    " . ($order['discount'] > 0 ? "
                    <tr>
                        <td style='padding: 10px; text-align: right;'><strong>Discount:</strong></td>
                        <td style='padding: 10px; text-align: right; color: #dc3545;'>-" . formatPrice($order['discount']) . "</td>
                    </tr>" : "") . "
                    <tr>
                        <td style='padding: 10px; text-align: right;'><strong>Tax:</strong></td>
                        <td style='padding: 10px; text-align: right;'>" . formatPrice($order['tax']) . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; text-align: right;'><strong>Shipping:</strong></td>
                        <td style='padding: 10px; text-align: right;'>" . ($order['shipping_cost'] == 0 ? 'FREE' : formatPrice($order['shipping_cost'])) . "</td>
                    </tr>
                    <tr style='font-size: 18px; font-weight: bold;'>
                        <td style='padding: 10px; text-align: right; border-top: 2px solid #eee;'>Grand Total:</td>
                        <td style='padding: 10px; text-align: right; color: " . getSetting('theme_primary_color', '#0d6efd') . "; border-top: 2px solid #eee;'>" . formatPrice($order['total']) . "</td>
                    </tr>
                </tfoot>
            </table>

            <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 20px;'>
                <h4 style='margin-top: 0;'>Shipping Address:</h4>
                <p style='margin-bottom: 0; font-size: 14px;'>
                    " . e($order['ship_name']) . "<br>
                    " . nl2br(e($order['ship_address'])) . "<br>
                    " . e($order['ship_city']) . ", " . e($order['ship_state']) . " - " . e($order['ship_pincode']) . "<br>
                    Phone: " . e($order['ship_phone']) . "
                </p>
            </div>

            <p style='margin-top: 30px; text-align: center;'>
                <a href='" . url('my-orders.php') . "' style='display:inline-block; padding:14px 28px; background-color: " . getSetting('theme_primary_color', '#0d6efd') . "; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;'>View Order Status</a>
            </p>
        ";

        return sendEmail($order['ship_email'], $subject, $body);

    } catch (Exception $e) {
        error_log('[MAIL] Order Confirm Exception: ' . $e->getMessage());
        return false;
    }
}

/**
 * Send a Shipping Update email to the buyer.
 */
function sendShippingUpdateEmail(int $orderId): bool
{
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        if (!$order) return false;

        $siteName = getSetting('site_name', 'MyShop');
        $subject  = "🚚 Your Order #{$order['order_number']} has been Shipped! - {$siteName}";
        
        $trackingHtml = "";
        if ($order['tracking_number']) {
            $trackingHtml = "
                <div style='background: #fff3cd; padding: 20px; border: 1px solid #ffeeba; border-radius: 8px; margin: 20px 0;'>
                    <h4 style='margin-top: 0; color: #856404;'>Tracking Information</h4>
                    <p style='margin-bottom: 0;'>
                        <strong>Tracking Number:</strong> <span style='font-family: monospace;'>{$order['tracking_number']}</span>
                    </p>
                </div>
            ";
        }

        $body = "
            <h2 style='color: #333;'>Great news! Your order is on its way.</h2>
            <p>Hi " . e($order['ship_name']) . ",</p>
            <p>Your order <strong>#{$order['order_number']}</strong> has been shipped and is currently in transit.</p>
            
            {$trackingHtml}

            <p>You can track your package and view your order details by clicking the button below:</p>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . url('my-orders.php') . "' style='display:inline-block; padding:14px 28px; background-color: " . getSetting('theme_primary_color', '#0d6efd') . "; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;'>Track My Order</a>
            </div>

            <p style='font-size: 14px; color: #666;'>If you have any questions about your shipment, feel free to contact us.</p>
        ";

        return sendEmail($order['ship_email'], $subject, $body);

    } catch (Exception $e) {
        error_log('[MAIL] Shipping Update Exception: ' . $e->getMessage());
        return false;
    }
}

/**
 * Create a system notification (e.g. for low stock, new orders)
 */
function createNotification(string $message, string $type = 'system', ?int $userId = null, ?string $link = null): bool
{
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO notifications (user_id, type, message, link, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
        return $stmt->execute([$userId, $type, $message, $link]);
    } catch (Exception $e) {
        error_log("Notification Error: " . $e->getMessage());
        return false;
    }
}
