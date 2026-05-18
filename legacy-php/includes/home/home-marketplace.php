<?php
$heroPrimary = array_merge([
    'title' => 'Handcrafted Decor, Divine Accents, And Gifting Pieces For A Warmer Home',
    'subtitle' => 'Curated arrivals for pooja, decor, and thoughtful gifting',
    'link' => 'products.php',
    'button_text' => 'Explore Store',
    'image' => '',
], is_array($heroBanners[0] ?? null) ? $heroBanners[0] : []);

$heroSecondary = array_merge([
    'title' => 'Statement corners built from bestsellers and festive edits',
    'subtitle' => 'Most loved right now',
    'link' => 'products.php?featured=1',
    'image' => '',
], is_array($heroBanners[1] ?? null) ? $heroBanners[1] : []);

$bestSellingProducts = $bestSellingProducts ?? getProducts(['sort' => 'popular'], 1, 8)['items'];
$newProducts = $newProducts ?? getProducts(['sort' => 'newest'], 1, 4)['items'];
$shopCollections = array_values(array_slice($featuredCats ?? [], 0, 8));
$heroCollections = array_slice($shopCollections, 0, 5);
$occasionCollections = array_slice($shopCollections, 0, 4);
$storyCollections = array_slice($shopCollections, 0, 3);
$productShowcase = array_slice($bestSellingProducts, 0, 8);
$freshArrivals = array_slice($newProducts, 0, 4);
$heroCopy = trim((string) getSetting('site_description', ''));

if ($heroCopy === '') {
    $heroCopy = 'Discover elevated pieces for sacred spaces, hosting moments, and gifting seasons in a storefront designed for faster browsing.';
}

$trustPoints = [
    'Hand-finished pieces',
    'Secure checkout',
    'Delivery tracking',
    'Gift-ready packaging',
];

$occasionNotes = [
    'Festive-ready picks for warm celebrations',
    'Decor ideas for inviting entryways and shelves',
    'Giftable pieces with premium presence',
    'Everyday utility with crafted character',
];

$featureNotes = [
    'Designed to anchor statement spaces.',
    'Layer detail, texture, and warmth into everyday styling.',
    'Browse handcrafted pieces with a richer finish.',
];

$heroImage = $heroPrimary['image'] ?: (($productShowcase[0]['images'] ?? null) ? productThumb($productShowcase[0]['images']) : 'assets/img/no-image.svg');
$secondaryImage = $heroSecondary['image'] ?: (($productShowcase[1]['images'] ?? null) ? productThumb($productShowcase[1]['images']) : $heroImage);
?>

<section class="market-announce-bar">
    <div class="container">
        <div class="market-announce-inner">
            <span>Curated festive store experience</span>
            <span>Signature gifting edits</span>
            <span>Craft-led decor collections</span>
        </div>
    </div>
</section>

<section class="advitya-home-hero">
    <div class="container py-4 py-lg-5">
        <div class="advitya-hero-shell reveal">
            <div class="advitya-hero-copy">
                <span class="advitya-kicker"><?= e($heroPrimary['subtitle']) ?></span>
                <h1><?= e($heroPrimary['title']) ?></h1>
                <p><?= e($heroCopy) ?></p>

                <div class="advitya-trust-list">
                    <?php foreach ($trustPoints as $point): ?>
                        <span><i class="fa-solid fa-plus"></i><?= e($point) ?></span>
                    <?php endforeach; ?>
                </div>

                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= e(url($heroPrimary['link'])) ?>" class="btn-lux-primary">
                        <?= e($heroPrimary['button_text']) ?> <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    <a href="<?= url('products.php?featured=1') ?>" class="btn-lux-outline">Shop Bestsellers</a>
                </div>

                <?php if ($heroCollections): ?>
                    <div class="advitya-chip-row">
                        <?php foreach ($heroCollections as $category): ?>
                            <a href="<?= url('products.php?category=' . $category['slug']) ?>" class="advitya-chip">
                                <?= e($category['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="advitya-hero-visual">
                <div class="advitya-hero-main-media">
                    <img src="<?= e(url($heroImage)) ?>" alt="<?= e($heroPrimary['title']) ?>">
                    <div class="advitya-floating-panel">
                        <small>Storefront Focus</small>
                        <strong>Category-first discovery with premium visual rhythm</strong>
                        <span>Built to surface collections, bestsellers, and festive shopping moments in one clean flow.</span>
                    </div>
                </div>

                <a href="<?= e(url($heroSecondary['link'])) ?>" class="advitya-editorial-card">
                    <div class="advitya-editorial-copy">
                        <small><?= e($heroSecondary['subtitle']) ?></small>
                        <strong><?= e($heroSecondary['title']) ?></strong>
                        <span>Browse the edit <i class="fa-solid fa-arrow-right"></i></span>
                    </div>
                    <img src="<?= e(url($secondaryImage)) ?>" alt="<?= e($heroSecondary['title']) ?>">
                </a>
            </div>
        </div>
    </div>
</section>

<?php if ($shopCollections): ?>
<section class="advitya-section bg-white">
    <div class="container">
        <div class="advitya-section-head reveal">
            <div>
                <span class="section-tag">Collections</span>
                <h2 class="section-title mb-0">Browse Signature Categories</h2>
            </div>
            <a href="<?= url('products.php') ?>" class="btn-lux-outline">View All</a>
        </div>

        <div class="advitya-category-grid">
            <?php foreach ($shopCollections as $category): ?>
                <a href="<?= url('products.php?category=' . $category['slug']) ?>" class="advitya-category-card reveal">
                    <div class="advitya-category-media">
                        <img src="<?= e(url($category['image'] ?: 'assets/img/no-image.svg')) ?>" alt="<?= e($category['name']) ?>">
                    </div>
                    <div class="advitya-category-copy">
                        <small>Curated Collection</small>
                        <span><?= e($category['name']) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($occasionCollections): ?>
<section class="advitya-section advitya-occasion-band">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-tag reveal">Occasion Picks</span>
            <h2 class="section-title text-center reveal">Shopping Moments We Surface First</h2>
            <div class="section-divider reveal"></div>
        </div>

        <div class="row g-4">
            <?php foreach ($occasionCollections as $index => $category): ?>
                <div class="col-6 col-lg-3 reveal">
                    <a href="<?= url('products.php?category=' . $category['slug']) ?>" class="advitya-occasion-card">
                        <img src="<?= e(url($category['image'] ?: 'assets/img/no-image.svg')) ?>" alt="<?= e($category['name']) ?>">
                        <div class="advitya-occasion-overlay"></div>
                        <div class="advitya-occasion-copy">
                            <small>Editorial Pick</small>
                            <strong><?= e($category['name']) ?></strong>
                            <span><?= e($occasionNotes[$index] ?? $occasionNotes[array_key_last($occasionNotes)]) ?></span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($storyCollections): ?>
<section class="advitya-section bg-white">
    <div class="container">
        <div class="advitya-story-grid">
            <?php foreach ($storyCollections as $index => $category): ?>
                <a href="<?= url('products.php?category=' . $category['slug']) ?>" class="advitya-story-card reveal advitya-story-card-<?= $index ?>">
                    <img src="<?= e(url($category['image'] ?: 'assets/img/no-image.svg')) ?>" alt="<?= e($category['name']) ?>">
                    <div class="advitya-story-overlay"></div>
                    <div class="advitya-story-copy">
                        <small>Signature Space</small>
                        <h3><?= e($category['name']) ?></h3>
                        <p><?= e($featureNotes[$index] ?? $featureNotes[array_key_last($featureNotes)]) ?></p>
                        <span>Explore now <i class="fa-solid fa-arrow-right"></i></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($productShowcase): ?>
<section class="advitya-section advitya-product-band">
    <div class="container">
        <div class="advitya-section-head reveal">
            <div>
                <span class="section-tag">Best Sellers</span>
                <h2 class="section-title mb-0">Most Loved Across The Storefront</h2>
            </div>
            <a href="<?= url('products.php?featured=1') ?>" class="btn-lux-outline">View Sale</a>
        </div>

        <div class="row g-4">
            <?php foreach ($productShowcase as $product): ?>
                <?php
                $hasDiscount = !empty($product['sale_price']) && (float) $product['sale_price'] > 0 && (float) $product['sale_price'] < (float) $product['price'];
                ?>
                <div class="col-6 col-md-4 col-xl-3 reveal">
                    <div class="product-card h-100 advitya-product-card">
                        <a href="<?= url('product.php?slug=' . $product['slug']) ?>" class="card-img-wrap d-block">
                            <img src="<?= e(url(productThumb($product['images']))) ?>" alt="<?= e($product['name']) ?>">
                            <?php if ($hasDiscount): ?>
                                <span class="card-badge sale"><?= discountPercent((float) $product['price'], (float) $product['sale_price']) ?>% OFF</span>
                            <?php endif; ?>
                        </a>
                        <div class="product-card-body">
                            <div class="product-cat"><?= e($product['category_name'] ?? 'Best Seller') ?></div>
                            <a href="<?= url('product.php?slug=' . $product['slug']) ?>" class="product-name-link"><?= e($product['name']) ?></a>
                            <div class="rating-minimal">
                                <?= starRating((float) ($product['avg_rating'] ?? 5)) ?>
                                <span class="review-count">(<?= (int) ($product['review_count'] ?? 12) ?>)</span>
                            </div>
                            <div class="price-wrap">
                                <span class="sale-price"><?= formatPrice((float) $product['effective_price']) ?></span>
                                <?php if ($hasDiscount): ?>
                                    <span class="old-price"><?= formatPrice((float) $product['price']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="advitya-section advitya-wide-callout">
    <div class="container">
        <div class="advitya-callout-shell reveal">
            <div>
                <span class="advitya-kicker">Design Direction</span>
                <h2>Premium, category-led, and visually richer for home decor discovery</h2>
                <p class="mb-0">This layout keeps the storefront product-driven while giving featured categories, curated edits, and seasonal shopping a much stronger presence.</p>
            </div>
            <div class="text-lg-end">
                <a href="<?= url('contact-us.php') ?>" class="btn-lux-primary">Talk To Us</a>
            </div>
        </div>
    </div>
</section>

<?php if ($freshArrivals): ?>
<section class="advitya-section bg-white">
    <div class="container">
        <div class="advitya-section-head reveal">
            <div>
                <span class="section-tag">New Arrivals</span>
                <h2 class="section-title mb-0">Fresh Pieces Worth A First Look</h2>
            </div>
            <a href="<?= url('products.php?sort=newest') ?>" class="btn-lux-outline">Shop New</a>
        </div>

        <div class="row g-4">
            <?php foreach ($freshArrivals as $product): ?>
                <div class="col-6 col-lg-3 reveal">
                    <div class="advitya-arrival-card">
                        <a href="<?= url('product.php?slug=' . $product['slug']) ?>" class="advitya-arrival-media">
                            <img src="<?= e(url(productThumb($product['images']))) ?>" alt="<?= e($product['name']) ?>">
                            <span class="advitya-arrival-badge">New</span>
                        </a>
                        <div class="advitya-arrival-copy">
                            <small><?= e($product['category_name'] ?? 'New Arrival') ?></small>
                            <a href="<?= url('product.php?slug=' . $product['slug']) ?>"><?= e($product['name']) ?></a>
                            <strong><?= formatPrice((float) $product['effective_price']) ?></strong>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="newsletter-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 text-center reveal">
                <span style="font-family: var(--font-body); font-size: .65rem; font-weight: 700; letter-spacing: .25em; text-transform: uppercase; color: var(--gold); display: block; margin-bottom: .8rem;">Store Updates</span>
                <h2 style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 500; margin-bottom: .6rem; color: var(--text-primary);">Stay Close To New Drops</h2>
                <p style="color: var(--text-secondary); font-size: .9rem; margin-bottom: 2rem;">Get curated collection launches, festive edits, and offer updates with a cleaner premium feel.</p>
                <form class="d-flex gap-0 mx-auto" style="max-width: 520px;" id="newsletterForm">
                    <input type="email" class="form-control flex-grow-1" name="email" placeholder="Your email address" required style="border-radius: 999px 0 0 999px; border: 1px solid var(--border-gold); background: transparent; color: var(--text-primary); padding: .9rem 1.2rem;">
                    <button type="submit" class="btn-newsletter" style="border-radius: 0 999px 999px 0;">Subscribe</button>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
.market-announce-bar {
    background: linear-gradient(90deg, #5e3428 0%, #9a6a45 100%);
    color: #fff8f0;
    font-size: 0.72rem;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}

.market-announce-inner {
    display: flex;
    gap: 1rem 2rem;
    justify-content: center;
    flex-wrap: wrap;
    padding: 0.85rem 0;
}

.advitya-home-hero {
    background:
        radial-gradient(circle at top left, rgba(179, 135, 82, 0.15), transparent 28%),
        linear-gradient(180deg, #f8f1e8 0%, #f5ece1 100%);
}

.advitya-hero-shell {
    display: grid;
    grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
    gap: 1.5rem;
    background: rgba(255, 250, 244, 0.92);
    border: 1px solid rgba(179, 135, 82, 0.18);
    border-radius: 36px;
    box-shadow: 0 30px 70px rgba(82, 50, 32, 0.12);
    padding: clamp(1.4rem, 3vw, 2rem);
}

.advitya-hero-copy {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 1.1rem;
    padding: clamp(0.2rem, 1vw, 0.8rem);
}

.advitya-kicker {
    display: inline-flex;
    align-items: center;
    width: fit-content;
    padding: 0.45rem 0.9rem;
    border-radius: 999px;
    background: rgba(179, 135, 82, 0.12);
    color: var(--gold-dark);
    font-size: 0.72rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    font-weight: 700;
}

.advitya-hero-copy h1 {
    margin: 0;
    font-size: clamp(2.5rem, 5vw, 4.3rem);
    line-height: 1.02;
    font-style: normal;
}

.advitya-hero-copy p {
    margin: 0;
    max-width: 58ch;
    color: var(--text-secondary);
    font-size: 1rem;
}

.advitya-trust-list,
.advitya-chip-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.advitya-trust-list span,
.advitya-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    padding: 0.72rem 0.95rem;
    background: #fff;
    border: 1px solid rgba(179, 135, 82, 0.14);
    border-radius: 999px;
    color: var(--text-primary);
    font-size: 0.82rem;
    box-shadow: 0 10px 24px rgba(87, 56, 36, 0.06);
}

.advitya-trust-list i {
    color: var(--gold);
    font-size: 0.68rem;
}

.advitya-chip {
    transition: var(--transition-fast);
}

.advitya-chip:hover {
    transform: translateY(-2px);
    color: var(--gold-dark);
}

.advitya-hero-visual {
    display: grid;
    gap: 1rem;
}

.advitya-hero-main-media,
.advitya-editorial-card {
    position: relative;
    overflow: hidden;
    border-radius: 28px;
}

.advitya-hero-main-media {
    min-height: 470px;
    box-shadow: 0 24px 44px rgba(76, 46, 29, 0.18);
}

.advitya-hero-main-media img,
.advitya-editorial-card img,
.advitya-category-media img,
.advitya-occasion-card img,
.advitya-story-card img,
.advitya-arrival-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.advitya-floating-panel {
    position: absolute;
    left: 1rem;
    right: 1rem;
    bottom: 1rem;
    padding: 1rem 1.1rem;
    border-radius: 22px;
    background: rgba(28, 18, 13, 0.72);
    backdrop-filter: blur(12px);
    color: #fff;
}

.advitya-floating-panel small,
.advitya-editorial-copy small,
.advitya-category-copy small,
.advitya-occasion-copy small,
.advitya-story-copy small,
.advitya-arrival-copy small {
    display: block;
    margin-bottom: 0.35rem;
    text-transform: uppercase;
    letter-spacing: 0.16em;
    font-size: 0.65rem;
    opacity: 0.8;
}

.advitya-floating-panel strong,
.advitya-editorial-copy strong {
    display: block;
    font-size: 1.05rem;
    margin-bottom: 0.25rem;
}

.advitya-floating-panel span,
.advitya-editorial-copy span,
.advitya-occasion-copy span,
.advitya-story-copy p {
    color: rgba(255, 248, 240, 0.82);
    font-size: 0.86rem;
}

.advitya-editorial-card {
    min-height: 180px;
    display: grid;
    grid-template-columns: minmax(0, 1.1fr) 180px;
    background: #2a1a15;
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.advitya-editorial-copy {
    padding: 1.3rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.advitya-editorial-copy span i,
.advitya-story-copy span i {
    margin-left: 0.35rem;
}

.advitya-section {
    padding: 5.5rem 0;
}

.advitya-section-head {
    display: flex;
    justify-content: space-between;
    align-items: end;
    gap: 1rem;
    margin-bottom: 2rem;
}

.advitya-category-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1.1rem;
}

.advitya-category-card {
    background: #fffaf4;
    border: 1px solid rgba(179, 135, 82, 0.14);
    border-radius: 26px;
    overflow: hidden;
    box-shadow: 0 16px 32px rgba(87, 56, 36, 0.07);
    transition: var(--transition);
}

.advitya-category-card:hover,
.advitya-occasion-card:hover,
.advitya-story-card:hover,
.advitya-arrival-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 22px 40px rgba(87, 56, 36, 0.12);
}

.advitya-category-media {
    aspect-ratio: 1 / 1;
    background: #efe2d2;
}

.advitya-category-copy {
    padding: 1rem 1.05rem 1.2rem;
}

.advitya-category-copy span,
.advitya-arrival-copy a {
    display: block;
    color: var(--text-primary);
    font-size: 1rem;
    font-weight: 600;
}

.advitya-occasion-band {
    background: linear-gradient(180deg, #f1e3d4 0%, #f8f1e8 100%);
}

.advitya-occasion-card,
.advitya-story-card,
.advitya-arrival-card {
    position: relative;
    display: block;
    overflow: hidden;
    border-radius: 28px;
    background: #fff;
    box-shadow: 0 16px 36px rgba(87, 56, 36, 0.09);
    transition: var(--transition);
}

.advitya-occasion-card {
    min-height: 340px;
}

.advitya-occasion-overlay,
.advitya-story-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(23, 14, 10, 0.02) 18%, rgba(23, 14, 10, 0.78) 100%);
}

.advitya-occasion-copy,
.advitya-story-copy {
    position: absolute;
    left: 1rem;
    right: 1rem;
    bottom: 1rem;
    z-index: 1;
    color: #fff;
}

.advitya-occasion-copy strong,
.advitya-story-copy h3 {
    display: block;
    color: #fff;
    margin-bottom: 0.35rem;
    font-size: 1.5rem;
    font-style: normal;
}

.advitya-story-grid {
    display: grid;
    grid-template-columns: 1.2fr 0.8fr 0.8fr;
    gap: 1rem;
}

.advitya-story-card {
    min-height: 430px;
}

.advitya-story-card-0 {
    min-height: 520px;
}

.advitya-story-copy span {
    display: inline-flex;
    align-items: center;
    margin-top: 0.35rem;
    color: #f4d2ab;
    font-size: 0.82rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}

.advitya-product-band {
    background: #f7eee4;
}

.advitya-product-card {
    border-radius: 24px;
    overflow: hidden;
    border: 1px solid rgba(179, 135, 82, 0.12);
}

.advitya-wide-callout {
    background: linear-gradient(135deg, #2f1c15 0%, #6f472f 100%);
    color: #fff;
}

.advitya-callout-shell {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.2rem;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 30px;
    padding: clamp(1.4rem, 3vw, 2rem);
}

.advitya-callout-shell h2,
.advitya-callout-shell p,
.advitya-callout-shell .advitya-kicker {
    color: #fff;
}

.advitya-callout-shell .advitya-kicker {
    background: rgba(244, 210, 171, 0.14);
}

.advitya-arrival-card {
    height: 100%;
    border: 1px solid rgba(179, 135, 82, 0.12);
}

.advitya-arrival-media {
    position: relative;
    display: block;
    aspect-ratio: 1 / 1.05;
    overflow: hidden;
    background: #efe3d3;
}

.advitya-arrival-badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
    padding: 0.45rem 0.8rem;
    border-radius: 999px;
    background: rgba(28, 18, 13, 0.8);
    color: #fff;
    font-size: 0.66rem;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}

.advitya-arrival-copy {
    padding: 1rem 1rem 1.2rem;
}

.advitya-arrival-copy strong {
    display: block;
    margin-top: 0.55rem;
    color: var(--gold-dark);
    font-size: 1rem;
}

@media (max-width: 1199.98px) {
    .advitya-category-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .advitya-story-grid {
        grid-template-columns: 1fr 1fr;
    }

    .advitya-story-card-0 {
        grid-column: 1 / -1;
        min-height: 420px;
    }
}

@media (max-width: 991.98px) {
    .advitya-hero-shell,
    .advitya-callout-shell {
        grid-template-columns: 1fr;
        display: grid;
    }

    .advitya-section-head {
        align-items: start;
        flex-direction: column;
    }
}

@media (max-width: 767.98px) {
    .advitya-home-hero {
        background: linear-gradient(180deg, #f8f1e8 0%, #f5ece1 100%);
    }

    .advitya-hero-shell {
        border-radius: 28px;
    }

    .advitya-hero-main-media {
        min-height: 320px;
    }

    .advitya-editorial-card {
        grid-template-columns: 1fr;
    }

    .advitya-editorial-card img {
        min-height: 180px;
    }

    .advitya-category-grid,
    .advitya-story-grid {
        grid-template-columns: 1fr 1fr;
    }

    .advitya-story-card,
    .advitya-story-card-0,
    .advitya-occasion-card {
        min-height: 300px;
    }

    .advitya-section {
        padding: 4rem 0;
    }
}

@media (max-width: 575.98px) {
    .market-announce-inner {
        letter-spacing: 0.12em;
        font-size: 0.64rem;
    }

    .advitya-category-grid,
    .advitya-story-grid {
        grid-template-columns: 1fr;
    }

    .advitya-trust-list span,
    .advitya-chip {
        width: 100%;
        justify-content: center;
    }
}
</style>
