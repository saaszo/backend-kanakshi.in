<?php
/**
 * About Us
 */
require_once __DIR__ . '/config/config.php';

$dynamicPage = getDynamicPageBySlug('about-us', true);

$pageTitle = $dynamicPage['meta_title'] ?: 'About Us';
$metaDesc = $dynamicPage['meta_desc'] ?: 'Learn about our brand story, craftsmanship, and customer promise.';
$categories = getParentCategories(4);
$featuredProducts = getProducts(['featured' => 1], 1, 3)['items'];
require_once __DIR__ . '/includes/header.php';
?>

<section class="py-5" style="background:linear-gradient(135deg,#fbf7ef 0%,#fff 48%,#f1e8dc 100%);">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="section-tag reveal">Our Story</span>
                <h1 class="reveal" style="font-style:normal;">Crafted To Feel Personal, Premium, And Easy To Trust</h1>
                <p class="text-secondary fs-5 reveal">We built this boutique for customers who want design-led products, clear details, and a shopping journey that feels calm instead of cluttered.</p>
                <div class="d-flex flex-wrap gap-3 mt-4 reveal">
                    <a href="<?= url('products.php') ?>" class="btn-lux-primary">Explore Collection</a>
                    <a href="<?= url('contact-us.php') ?>" class="btn-lux-outline">Talk To Us</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="row g-3 reveal">
                    <div class="col-7">
                        <div class="rounded-4 overflow-hidden shadow-sm h-100" style="min-height:320px;">
                            <img src="https://images.unsplash.com/photo-1617038220319-276d3cfab638?q=80&w=1000&auto=format&fit=crop" alt="Boutique craftsmanship" class="w-100 h-100" style="object-fit:cover;">
                        </div>
                    </div>
                    <div class="col-5 d-flex flex-column gap-3">
                        <div class="rounded-4 p-4 h-100 shadow-sm" style="background:#2d241e; color:#fcfaf5;">
                            <div class="small text-uppercase ls-2 opacity-75 mb-2">Founded With Intention</div>
                            <div class="fs-4 fw-700">Heritage aesthetics, modern usability, and service-first operations.</div>
                        </div>
                        <div class="rounded-4 overflow-hidden shadow-sm h-100" style="min-height:150px;">
                            <img src="https://images.unsplash.com/photo-1512436991641-6745cdb1723f?q=80&w=800&auto=format&fit=crop" alt="Premium packaging" class="w-100 h-100" style="object-fit:cover;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4 reveal">
                <div class="p-4 rounded-4 h-100 shadow-sm border">
                    <div class="text-primary fs-3 mb-3"><i class="fa-solid fa-gem"></i></div>
                    <h3 style="font-style:normal;" class="fs-4">Curated Selection</h3>
                    <p class="text-secondary mb-0">We choose collections that photograph beautifully, ship safely, and feel premium the moment they arrive.</p>
                </div>
            </div>
            <div class="col-md-4 reveal">
                <div class="p-4 rounded-4 h-100 shadow-sm border">
                    <div class="text-primary fs-3 mb-3"><i class="fa-solid fa-box-open"></i></div>
                    <h3 style="font-style:normal;" class="fs-4">Operational Clarity</h3>
                    <p class="text-secondary mb-0">Order tracking, transparent pricing, and clearly organized categories help customers browse with confidence.</p>
                </div>
            </div>
            <div class="col-md-4 reveal">
                <div class="p-4 rounded-4 h-100 shadow-sm border">
                    <div class="text-primary fs-3 mb-3"><i class="fa-solid fa-headset"></i></div>
                    <h3 style="font-style:normal;" class="fs-4">Human Support</h3>
                    <p class="text-secondary mb-0">When customers need help, they can reach us quickly through WhatsApp, email, or a call-back request.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($categories): ?>
<section class="py-5" style="background:#f8f4ec;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4 gap-3">
            <div>
                <span class="section-tag reveal">Browse The Boutique</span>
                <h2 class="section-title mb-0 reveal">What Our Customers Love Most</h2>
            </div>
            <a href="<?= url('products.php') ?>" class="btn-lux-outline reveal">View All</a>
        </div>
        <div class="row g-4">
            <?php foreach ($categories as $category): ?>
                <div class="col-6 col-lg-3 reveal">
                    <a href="<?= url('products.php?category=' . $category['slug']) ?>" class="text-decoration-none">
                        <div class="rounded-4 overflow-hidden shadow-sm mb-3" style="aspect-ratio: 1 / 1.05;">
                            <img src="<?= url($category['image'] ?: 'assets/img/no-image.svg') ?>" alt="<?= e($category['name']) ?>" class="w-100 h-100" style="object-fit:cover;">
                        </div>
                        <h3 class="fs-5 mb-1" style="font-style:normal;"><?= e($category['name']) ?></h3>
                        <p class="text-secondary small mb-0"><?= e(truncate($category['description'] ?: 'Premium pieces curated for modern gifting and everyday elegance.', 80)) ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($featuredProducts): ?>
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-tag reveal">Signature Picks</span>
            <h2 class="section-title text-center reveal">A Quick Look At Our Premium Range</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="col-md-4 reveal">
                    <div class="product-card h-100">
                        <a href="<?= url('product.php?slug=' . $product['slug']) ?>" class="card-img-wrap d-block">
                            <img src="<?= url(productThumb($product['images'])) ?>" alt="<?= e($product['name']) ?>">
                        </a>
                        <div class="product-card-body">
                            <div class="product-cat"><?= e($product['category_name'] ?? 'Signature Collection') ?></div>
                            <a href="<?= url('product.php?slug=' . $product['slug']) ?>" class="product-name-link"><?= e($product['name']) ?></a>
                            <div class="price-wrap">
                                <span class="sale-price"><?= formatPrice((float)$product['effective_price']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($dynamicPage && !empty($dynamicPage['content'])): ?>
<section class="py-5" style="background:#fcfaf5;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9 reveal">
                <div class="bg-white rounded-4 shadow-sm border p-4 p-md-5">
                    <div class="designer-typography">
                        <?= $dynamicPage['content'] ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
.designer-typography {
    color: var(--text-secondary);
    line-height: 1.85;
}
.designer-typography h2,
.designer-typography h3,
.designer-typography h4 {
    color: var(--text-primary);
    font-style: normal;
    margin-bottom: 1rem;
    margin-top: 1.8rem;
}
.designer-typography p,
.designer-typography ul {
    margin-bottom: 1rem;
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
