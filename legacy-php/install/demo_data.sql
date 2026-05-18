-- ============================================================
-- Saaszo Pro — High-Conversion Jewelry Demo Content
-- Theme: Luxury, Anti-Tarnish, Everyday Elegance 
-- ============================================================

-- CLEAR OLD DATA
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `product_reviews`;
TRUNCATE TABLE `order_returns`;
TRUNCATE TABLE `product_variants`;
TRUNCATE TABLE `products`;
TRUNCATE TABLE `categories`;
TRUNCATE TABLE `banners`;
TRUNCATE TABLE `coupons`;
TRUNCATE TABLE `wishlists`;
TRUNCATE TABLE `cart`;
TRUNCATE TABLE `notifications`;
TRUNCATE TABLE `abandoned_carts`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Insert Luxury Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `image`, `description`, `parent_id`, `is_active`, `sort_order`) VALUES
(1, 'Luxury Rings', 'luxury-rings', 'https://images.unsplash.com/photo-1544274974-958564177ed8?auto=format&fit=crop&w=800&q=80', 'Premium 18K gold-plated anti-tarnish rings.', NULL, 1, 1),
(2, 'Statement Necklaces', 'statement-necklaces', 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=800&q=80', 'Waterproof necklaces designed for the modern queen.', NULL, 1, 2),
(3, 'Dainty Earrings', 'dainty-earrings', 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=800&q=80', 'Hypoallergenic studs and hoops for sensitive ears.', NULL, 1, 3);

-- 2. Insert Pro Jewelry Banners
INSERT INTO `banners` (`title`, `subtitle`, `image`, `link`, `button_text`, `position`, `is_active`, `sort_order`) VALUES
('The Eternal Radiance', '18K Gold Plated. Anti-Tarnish. Waterproof.', 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=1920&q=80', '/products.php', 'Shop Collection', 'hero', 1, 1),
('Waterproof Minimalist', 'Wear your shine everywhere—even to the pool.', 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=1920&q=80', '/products.php', 'Explore Store', 'hero', 1, 2);

-- 3. Insert A+ Jewelry Products
-- Note: 'bullet_points' is a markdown/text field for A+ listings
INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `short_desc`, `bullet_points`, `price`, `sale_price`, `stock`, `sku`, `images`, `is_featured`, `is_active`, `hsn_code`, `gst_percent`) VALUES
(1, 'Eternal Gold Band', 'eternal-gold-band', 
  '<p>Elevate your everyday style with the <strong>Eternal Gold Band</strong>. Crafted from high-grade stainless steel and dipped in 18K gold, this ring is designed to be your constant companion.</p><h4>Why Choose This?</h4><p>Unlike traditional jewelry, our anti-tarnish coating ensures that your ring stays as bright as day one, even after contact with water, sweat, or perfume.</p>', 
  'Minimalist 18K Gold Plated Ring. Anti-Tarnish & Waterproof.', 
  '* 18K Real Gold Plated\n* 316L Stainless Steel Base\n* 100% Anti-Tarnish\n* Waterproof & Sweatproof\n* Hypoallergenic for Sensitive Skin',
  1299.00, 999.00, 50, 'RNG-GLD-01', '["https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=800&q=80"]', 1, 1, '711319', 3.00),

(2, 'Celestial Pearl Pendant', 'celestial-pearl-pendant', 
  '<p>The <strong>Celestial Pearl Pendant</strong> combines timeless sophistication with modern durability. Featuring a hand-selected freshwater pearl on a sleek snake chain.</p>', 
  'Freshwater Pearl Necklace with 18K Gold Snake Chain.', 
  '* Genuine Freshwater Pearl\n* Sleek 18K Gold Snake Chain\n* Adjustable Length (16" + 2" Extension)\n* Nickel-free & Lead-free',
  2499.00, 1999.00, 25, 'NEC-PRL-01', '["https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=800&q=80"]', 1, 1, '711319', 3.00),

(3, 'Glossy Gold Hoops', 'glossy-gold-hoops', 
  '<p>Lightweight, classy, and essential. Our <strong>Glossy Gold Hoops</strong> are the perfect finish to any outfit, from office wear to a night out.</p>', 
  'Essential 18K Gold Hoop Earrings. Lightweight.', 
  '* High-Shine Mirror Finish\n* Lightweight Hollow Design\n* Secure Snap Closure\n* Safe for Sensitive Ears',
  999.00, 699.00, 60, 'EAR-HUP-01', '["https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=800&q=80"]', 1, 1, '711319', 3.00);

-- 5. Insert Product Variants
-- Gold Band Variants
INSERT INTO `product_variants` (`product_id`, `size`, `color`, `price`, `stock`, `sku`) VALUES
(1, 'Size 6', 'Gold', 1299.00, 20, 'RNG-GLD-01-S6'),
(1, 'Size 7', 'Gold', 1299.00, 20, 'RNG-GLD-01-S7'),
(1, 'Size 8', 'Gold', 1299.00, 10, 'RNG-GLD-01-S8');

-- 6. Insert Social Proof Demo (Pre-seeded Reviews)
-- Note: status='approved' so they show up immediately
INSERT INTO `product_reviews` (`product_id`, `user_id`, `rating`, `comment`, `status`) VALUES
(1, 1, 5, 'Absolutely love the quality! I wear it while swimming and it hasnt changed color at all. Highy recommend.', 'approved'),
(1, 1, 4, 'Very shiny and fits perfectly. The packaging was also very premium.', 'approved'),
(2, 1, 5, 'The pearl looks so expensive and real. The chain is very smooth on the skin.', 'approved'),
(3, 1, 5, 'Perfect size hoops! Not too big, not too small. Very comfortable for all-day wear.', 'approved');

-- 7. Insert Sample Coupons
INSERT INTO `coupons` (`code`, `type`, `value`, `min_order`, `expiry_date`, `is_active`) VALUES
('WELCOME10', 'percent', 10.00, 499.00, '2026-12-31', 1),
('FESTIVE500', 'fixed', 500.00, 2499.00, '2026-12-31', 1);
