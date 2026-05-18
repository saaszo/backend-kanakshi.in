-- ============================================================
-- Saaszo Pro — Unified Enterprise Jewelry Database Schema (V14.1)
-- PHP + MySQL | Production Ready
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+05:30";

-- 1. USERS (Security + 2FA + Protected)
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`                VARCHAR(100) NOT NULL,
  `email`               VARCHAR(150) NOT NULL,
  `password`            VARCHAR(255) NOT NULL,
  `phone`               VARCHAR(15)  DEFAULT NULL,
  `address`             TEXT         DEFAULT NULL,
  `city`                VARCHAR(100) DEFAULT NULL,
  `state`               VARCHAR(100) DEFAULT NULL,
  `pincode`             VARCHAR(10)  DEFAULT NULL,
  `role`                ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  `is_active`           TINYINT(1) NOT NULL DEFAULT 1,
  `is_protected`        TINYINT(1) NOT NULL DEFAULT 0,
  `two_factor_enabled`  TINYINT(1) NOT NULL DEFAULT 0,
  `two_factor_code`     VARCHAR(10) DEFAULT NULL,
  `two_factor_expires`  TIMESTAMP NULL DEFAULT NULL,
  `remember_token`      VARCHAR(255) DEFAULT NULL,
  `last_login`          DATETIME DEFAULT NULL,
  `created_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. CATEGORIES
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100) NOT NULL,
  `slug`        VARCHAR(120) NOT NULL,
  `image`       VARCHAR(255) DEFAULT NULL,
  `description` TEXT         DEFAULT NULL,
  `parent_id`   INT UNSIGNED DEFAULT NULL,
  `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order`  INT NOT NULL DEFAULT 0,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_slug` (`slug`),
  CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. PRODUCTS (Marketing + A+ Listings)
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id`   INT UNSIGNED NOT NULL,
  `name`          VARCHAR(200) NOT NULL,
  `slug`          VARCHAR(220) NOT NULL,
  `description`   LONGTEXT     DEFAULT NULL,
  `short_desc`    VARCHAR(500) DEFAULT NULL,
  `bullet_points` TEXT         DEFAULT NULL,
  `hsn_code`      VARCHAR(20)  DEFAULT NULL,
  `gst_percent`   DECIMAL(5,2) DEFAULT 3.00,
  `price`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `sale_price`    DECIMAL(10,2) DEFAULT NULL,
  `shipping_type` ENUM('free','flat') NOT NULL DEFAULT 'free',
  `shipping_fee`  DECIMAL(10,2) DEFAULT 0.00,
  `stock`         INT NOT NULL DEFAULT 0,
  `sku`           VARCHAR(100) DEFAULT NULL,
  `images`        JSON         DEFAULT NULL,
  `is_featured`   TINYINT(1) NOT NULL DEFAULT 0,
  `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
  `total_sold`    INT NOT NULL DEFAULT 0,
  `avg_rating`    DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  `review_count`  INT NOT NULL DEFAULT 0,
  `meta_title`    VARCHAR(200) DEFAULT NULL,
  `meta_desc`     VARCHAR(320) DEFAULT NULL,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_products_slug` (`slug`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. PRODUCT VARIANTS (Missing previously)
DROP TABLE IF EXISTS `product_variants`;
CREATE TABLE `product_variants` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id`  INT UNSIGNED NOT NULL,
  `size`        VARCHAR(30)   DEFAULT NULL,
  `color`       VARCHAR(50)   DEFAULT NULL,
  `price`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `stock`       INT NOT NULL DEFAULT 0,
  `sku`         VARCHAR(100)  DEFAULT NULL,
  `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at`  DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_variants_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. PRODUCT REVIEWS (Social Proof)
DROP TABLE IF EXISTS `product_reviews`;
CREATE TABLE `product_reviews` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id`  INT UNSIGNED NOT NULL,
  `user_id`     INT UNSIGNED NOT NULL,
  `rating`      TINYINT(1) NOT NULL DEFAULT 5,
  `comment`     TEXT DEFAULT NULL,
  `images`      JSON DEFAULT NULL,
  `status`      ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. ADMIN LOGS (Audit)
DROP TABLE IF EXISTS `admin_logs`;
CREATE TABLE `admin_logs` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT UNSIGNED NOT NULL,
    `action`      VARCHAR(255) NOT NULL,
    `target_type` VARCHAR(50),
    `target_id`   INT,
    `details`     TEXT,
    `ip_address`  VARCHAR(45),
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. ORDERS
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`         INT UNSIGNED DEFAULT NULL,
  `order_number`    VARCHAR(50) NOT NULL,
  `status`          ENUM('pending','confirmed','processing','shipped','delivered','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `subtotal`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `discount`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `tax`             DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `shipping_cost`   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `shipping`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total`           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_amount`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `payment_method`  ENUM('razorpay','phonepe','paytm','cod') NOT NULL DEFAULT 'cod',
  `payment_status`  ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `payment_id`      VARCHAR(200) DEFAULT NULL,
  `ship_name`       VARCHAR(100),
  `ship_email`      VARCHAR(150),
  `ship_phone`      VARCHAR(15),
  `ship_address`    TEXT,
  `ship_city`       VARCHAR(100),
  `ship_state`      VARCHAR(100),
  `ship_pincode`    VARCHAR(10),
  `notes`           TEXT DEFAULT NULL,
  `coupon_id`       INT UNSIGNED DEFAULT NULL,
  `tracking_number` VARCHAR(100) DEFAULT NULL,
  `tracking_url`    VARCHAR(500) DEFAULT NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_orders_number` (`order_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. ORDER ITEMS
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id`    INT UNSIGNED NOT NULL,
  `product_id`  INT UNSIGNED DEFAULT NULL,
  `variant_id`  INT UNSIGNED DEFAULT NULL,
  `name`        VARCHAR(200) NOT NULL,
  `price`       DECIMAL(10,2) NOT NULL,
  `quantity`    INT NOT NULL DEFAULT 1,
  `image`       VARCHAR(255),
  `size`        VARCHAR(30) DEFAULT NULL,
  `color`       VARCHAR(50) DEFAULT NULL,
  `variant_details` VARCHAR(120) DEFAULT NULL,
  `line_total`  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `gst_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `sku`         VARCHAR(100) DEFAULT NULL,
  `hsn_code`    VARCHAR(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_oi_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. ORDER RETURNS
DROP TABLE IF EXISTS `order_returns`;
CREATE TABLE `order_returns` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id`       INT UNSIGNED NOT NULL,
    `product_id`     INT UNSIGNED NOT NULL,
    `user_id`        INT UNSIGNED NOT NULL,
    `reason`         VARCHAR(255) NOT NULL,
    `comment`        TEXT,
    `images`         JSON,
    `status`         ENUM('pending','approved','rejected','completed') DEFAULT 'pending',
    `admin_note`     TEXT,
    `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. ORDER TRACKING (Missing previously)
DROP TABLE IF EXISTS `order_tracking`;
CREATE TABLE `order_tracking` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id`        INT UNSIGNED NOT NULL,
  `tracking_number` VARCHAR(100) DEFAULT NULL,
  `courier_name`    VARCHAR(100) DEFAULT NULL,
  `status`          VARCHAR(100) NOT NULL,
  `location`        VARCHAR(200) DEFAULT NULL,
  `message`         TEXT DEFAULT NULL,
  `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_ot_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. COUPONS
DROP TABLE IF EXISTS `coupons`;
CREATE TABLE `coupons` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`        VARCHAR(50) NOT NULL,
  `type`        ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
  `value`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `min_order`   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `expiry_date` DATE DEFAULT NULL,
  `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_coupons_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. BANNERS
DROP TABLE IF EXISTS `banners`;
CREATE TABLE `banners` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(200),
  `subtitle`    VARCHAR(300),
  `image`       VARCHAR(255) NOT NULL,
  `link`        VARCHAR(500),
  `button_text` VARCHAR(50),
  `position`    ENUM('hero','offer','sidebar') NOT NULL DEFAULT 'hero',
  `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order`  INT NOT NULL DEFAULT 0,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. SETTINGS
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key_name`    VARCHAR(100) NOT NULL,
  `value`       TEXT DEFAULT NULL,
  `label`       VARCHAR(150) DEFAULT NULL,
  `group_name`  VARCHAR(50)  NOT NULL DEFAULT 'general',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. WISHLISTS
DROP TABLE IF EXISTS `wishlists`;
CREATE TABLE `wishlists` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. CART
DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED,
  `session_id` VARCHAR(100),
  `product_id` INT UNSIGNED NOT NULL,
  `variant_id` INT UNSIGNED DEFAULT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. SUBSCRIBERS
DROP TABLE IF EXISTS `subscribers`;
CREATE TABLE `subscribers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. PASSWORD RESETS (Missing previously)
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`       VARCHAR(150) NOT NULL,
  `token`       VARCHAR(255) NOT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at`  DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pr_email` (`email`),
  KEY `idx_pr_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. PAGES (Missing previously)
DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(200) NOT NULL,
  `slug`        VARCHAR(220) NOT NULL,
  `content`     LONGTEXT DEFAULT NULL,
  `meta_title`  VARCHAR(200) DEFAULT NULL,
  `meta_desc`   VARCHAR(320) DEFAULT NULL,
  `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at`  DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pages_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. NOTIFICATIONS (Missing previously)
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED DEFAULT NULL,
  `type`        ENUM('order','payment','account','promo','system') NOT NULL DEFAULT 'system',
  `message`     TEXT NOT NULL,
  `link`        VARCHAR(500) DEFAULT NULL,
  `is_read`     TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user_id` (`user_id`),
  KEY `idx_notif_is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. SHIPPING ZONES (Missing previously)
DROP TABLE IF EXISTS `shipping_zones`;
CREATE TABLE `shipping_zones` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `state`         VARCHAR(100) NOT NULL,
  `pincode`       VARCHAR(10)  DEFAULT NULL,
  `shipping_cost` DECIMAL(10,2) NOT NULL DEFAULT 49.00,
  `min_days`      INT NOT NULL DEFAULT 3,
  `max_days`      INT NOT NULL DEFAULT 7,
  `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at`    DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. ABANDONED CARTS (Missing previously)
DROP TABLE IF EXISTS `abandoned_carts`;
CREATE TABLE `abandoned_carts` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`         INT UNSIGNED DEFAULT NULL,
  `session_id`      VARCHAR(100) NOT NULL,
  `cart_data`       JSON NOT NULL,
  `total_value`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `is_recovered`    TINYINT(1) NOT NULL DEFAULT 0,
  `email_sent`      TINYINT(1) NOT NULL DEFAULT 0,
  `last_active`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ac_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- SEED DATA (Enterprise Defaults)
INSERT INTO `settings` (`key_name`, `value`, `label`, `group_name`) VALUES
('site_name', 'Luxury Jewelry Store', 'Site Name', 'general'),
('site_tagline', 'Timeless Elegance.', 'Site Tagline', 'general'),
('site_email', 'admin@saaszo.in', 'Site Email', 'general'),
('site_currency', 'INR', 'Currency', 'general'),
('site_currency_symbol', '₹', 'Symbol', 'general'),
('theme_primary_color', '#c5a059', 'Primary Color', 'general'),
('home_style', 'editorial', 'Homepage Style', 'general'),
('header_menu_items', '', 'Header Menu Items', 'general'),
('site_logo', 'uploads/logo_default.svg', 'Logo', 'general');
