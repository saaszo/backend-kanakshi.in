<?php
require_once __DIR__ . '/config/config.php';
$db = getDB();

$legalPages = [
    [
        'title' => 'Privacy Policy',
        'slug' => 'privacy-policy',
        'content' => '<h2>Privacy Policy</h2><p>Welcome to our Privacy Policy. Your privacy is critically important to us.</p><p>We specialize in luxury jewelry and divine sculptures. To provide you with the best experience, we collect certain information as described below.</p><h3>1. Information We Collect</h3><p>We collect personal information that you provide to us when you register, make a purchase, or contact us.</p><h3>2. How We Use Your Information</h3><p>We use your information to process orders, provide customer support, and send you exclusive updates about our luxury collection.</p>',
        'meta_title' => 'Privacy Policy | Luxury Jewelry Store',
        'meta_desc' => 'Read our privacy policy to understand how we protect your information at our luxury jewelry boutique.'
    ],
    [
        'title' => 'Terms & Conditions',
        'slug' => 'terms-conditions',
        'content' => '<h2>Terms & Conditions</h2><p>By accessing this website, you agree to these terms and conditions.</p><h3>1. Intellectual Property</h3><p>All designs, images, and content are the exclusive property of our luxury store.</p><h3>2. Luxury Guarantee</h3><p>We guarantee the authenticity of every piece in our collection, from dainty earrings to divine sculptures.</p>',
        'meta_title' => 'Terms & Conditions | Luxury Jewelry Store',
        'meta_desc' => 'Review the terms and conditions for shopping at our exquisite jewelry and sculptures store.'
    ],
    [
        'title' => 'Refund Policy',
        'slug' => 'refund-policy',
        'content' => '<h2>Refund & Return Policy</h2><p>At our boutique, your satisfaction with our luxury pieces is paramount.</p><h3>1. Returns</h3><p>Due to the bespoke nature of our jewelry and sculptures, returns are accepted within 30 days of delivery in original condition.</p><h3>2. Refunds</h3><p>Once inspected, refunds will be processed to your original payment method within 7 business days.</p>',
        'meta_title' => 'Refund Policy | Luxury Jewelry Store',
        'meta_desc' => 'Learn about our 30-day return and refund policy for luxury jewelry and divine sculptures.'
    ],
    [
        'title' => 'About Us',
        'slug' => 'about-us',
        'content' => '<h2>Our Story</h2><p>We curate jewelry, gifting pieces, and devotional decor that feel timeless in modern homes.</p><h3>Why We Exist</h3><p>Our team believes online shopping should feel as thoughtful as stepping into a beautifully arranged boutique.</p><h3>What We Promise</h3><p>Clear craftsmanship details, secure delivery, responsive support, and premium presentation in every order.</p>',
        'meta_title' => 'About Us | Luxury Jewelry Store',
        'meta_desc' => 'Discover the story, values, and craftsmanship behind our premium boutique.'
    ],
    [
        'title' => 'Shipping Policy',
        'slug' => 'shipping-policy',
        'content' => '<h2>Shipping Policy</h2><p>We dispatch orders quickly and share status updates at every major milestone.</p><h3>Dispatch Timeline</h3><p>Most in-stock orders are processed within 24 to 48 business hours.</p><h3>Delivery Support</h3><p>If your shipment is delayed, our support team will help you with updates and resolution.</p>',
        'meta_title' => 'Shipping Policy | Luxury Jewelry Store',
        'meta_desc' => 'Read about dispatch timelines, delivery updates, and shipping support for your orders.'
    ]
];

foreach ($legalPages as $page) {
    // Check if slug already exists
    $stmt = $db->prepare("SELECT id FROM pages WHERE slug = ?");
    $stmt->execute([$page['slug']]);
    if ($stmt->fetch()) {
        // Update existing
        $stmt = $db->prepare("UPDATE pages SET title = ?, content = ?, meta_title = ?, meta_desc = ?, is_active = 1 WHERE slug = ?");
        $stmt->execute([$page['title'], $page['content'], $page['meta_title'], $page['meta_desc'], $page['slug']]);
        echo "Updated: " . $page['title'] . "<br>";
    } else {
        // Insert new
        $stmt = $db->prepare("INSERT INTO pages (title, slug, content, meta_title, meta_desc, is_active) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->execute([$page['title'], $page['slug'], $page['content'], $page['meta_title'], $page['meta_desc']]);
        echo "Inserted: " . $page['title'] . "<br>";
    }
}
?>
