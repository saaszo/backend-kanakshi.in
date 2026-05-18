<?php
/**
 * Homepage — Premium Indian Jewelry & Divine Sculpture Store
 */
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Exquisite Indian Jewelry & Divine Sculptures | Timeless Elegance';
$homeStyle = 'marketplace';
$bodyClass = $homeStyle === 'marketplace' ? 'page-market-home' : '';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Fetch dynamic content
$featuredProducts = getProducts(['featured' => 1], 1, 8)['items'];
$newProducts      = getProducts(['sort' => 'newest'], 1, 4)['items'];
$bestSellingProducts = getProducts(['sort' => 'popular'], 1, 8)['items'];
$featuredCats     = getParentCategories(8);

// Fetch active hero banners
$stmt = $db->prepare("SELECT * FROM banners WHERE position = 'hero' AND is_active = 1 ORDER BY sort_order ASC, id DESC");
$stmt->execute();
$heroBanners = $stmt->fetchAll();

if ($homeStyle === 'marketplace') {
    require __DIR__ . '/includes/home/home-marketplace.php';
    require_once __DIR__ . '/includes/footer.php';
    return;
}
?>

<!-- ────────────────────────────────────────────────────────
     HERO SECTION — Dynamic Cinematic Slider (Multiple Slides)
──────────────────────────────────────────────────────── -->
<section class="lux-hero-slider">
    <?php if ($heroBanners): ?>
        <div id="heroCarousel" class="carousel slide carousel-fade h-100" data-bs-ride="carousel" data-bs-interval="6000">
            <!-- Indicators -->
            <?php if (count($heroBanners) > 1): ?>
            <div class="carousel-indicators">
                <?php foreach ($heroBanners as $index => $banner): ?>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>"></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="carousel-inner h-100">
                <?php foreach ($heroBanners as $index => $banner): ?>
                <div class="carousel-item h-100 <?= $index === 0 ? 'active' : '' ?>">
                    <div class="lux-hero-bg-wrapper">
                        <div class="lux-hero-bg" style="background-image: url('<?= url($banner['image']) ?>');"></div>
                    </div>
                    <div class="container h-100 position-relative" style="z-index: 2;">
                        <div class="lux-hero-content-wrapper h-100 d-flex align-items-center justify-content-center">
                            <div class="lux-hero-content text-center">
                                <?php if ($banner['subtitle']): ?>
                                <span class="lux-hero-tag reveal"><?= e($banner['subtitle']) ?></span>
                                <?php endif; ?>
                                
                                <h1 class="shimmer-gold reveal"><?= nl2br(e($banner['title'])) ?></h1>
                                
                                <?php if ($desc = getSetting('site_description')): ?>
                                <p class="reveal"><?= e($desc) ?></p>
                                <?php endif; ?>

                                <?php if ($banner['link']): ?>
                                <div class="reveal">
                                    <a href="<?= e($banner['link']) ?>" class="btn-lux-primary mt-2">
                                        <?= e($banner['button_text'] ?: 'Explore Collection') ?> <i class="fa-solid fa-arrow-right ms-2"></i>
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Controls -->
            <?php if (count($heroBanners) > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Default Fallback if no banners are added -->
        <div class="lux-hero">
            <div class="lux-hero-bg" style="background-image: url('https://images.unsplash.com/photo-1611591437281-460bfbe1220a?q=80&w=1920&auto=format&fit=crop');"></div>
            <div class="container position-relative" style="z-index:2;">
                <div class="lux-hero-content text-center mx-auto">
                    <span class="lux-hero-tag">✦ New Collection 2026 ✦</span>
                    <h1 class="shimmer-gold">Divine Jewelry<br>Sacred Sculptures</h1>
                    <p>Exquisite handcrafted Indian jewelry in 22K Gold & 925 Sterling Silver, alongside sacred divine sculptures.</p>
                    <a href="<?= url('products.php') ?>" class="btn-lux-primary">
                        Explore Collection <i class="fa-solid fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>

<!-- ────────────────────────────────────────────────────────
     TRUST STRIP — Dark Confidence Builders
──────────────────────────────────────────────────────── -->
<section class="trust-strip reveal">
    <div class="container">
        <div class="row g-0">
            <div class="col-6 col-md-3">
                <div class="trust-item">
                    <i class="fa-solid fa-gem"></i>
                    <h6>Certified Jewelry</h6>
                    <p>BIS Hallmarked gold</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="trust-item">
                    <i class="fa-solid fa-truck-fast"></i>
                    <h6>Free Shipping</h6>
                    <p>Orders above <?= formatPrice(FREE_SHIPPING_ABOVE) ?></p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="trust-item">
                    <i class="fa-solid fa-rotate-left"></i>
                    <h6>Easy Returns</h6>
                    <p>30-day return policy</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="trust-item">
                    <i class="fa-solid fa-shield-halved"></i>
                    <h6>Secure Payment</h6>
                    <p>100% encrypted checkout</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ────────────────────────────────────────────────────────
     SHOP BY CATEGORY — Elegant Circles on Dark
──────────────────────────────────────────────────────── -->
<section class="py-5 mt-3" style="background: var(--bg-main);">
    <div class="container text-center">
        <span class="section-tag reveal">Browse</span>
        <h2 class="section-title text-center reveal">Shop By Category</h2>
        <div class="section-divider reveal"></div>
        <div class="row g-4 justify-content-center">
            <?php foreach ($featuredCats as $cat): ?>
                <div class="col-4 col-md-3 col-lg-2 reveal">
                    <a href="<?= url('products.php?category=' . $cat['slug']) ?>" class="text-decoration-none d-block">
                        <div class="cat-bubble">
                            <img src="<?= url($cat['image'] ?: 'assets/img/no-image.svg') ?>" alt="<?= e($cat['name']) ?>" loading="lazy">
                        </div>
                        <h6 style="font-family: var(--font-body); font-size: .72rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: var(--text-secondary);">
                            <?= e($cat['name']) ?>
                        </h6>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ────────────────────────────────────────────────────────
     COLLECTION GRID — Masonry Editorial Dark
──────────────────────────────────────────────────────── -->
<section class="py-4" style="background: var(--bg-main);">
    <div class="container">
        <div class="row g-3">
            <!-- Large Left -->
            <div class="col-md-7 reveal">
                <a href="<?= url('products.php?category=earrings') ?>" class="collection-card d-block" style="min-height: 480px;">
                    <img src="https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?q=80&w=900&auto=format&fit=crop" alt="Indian Earrings Collection" loading="lazy">
                    <div class="collection-card-overlay"></div>
                    <div class="collection-card-content">
                        <h3>The Jhumka Edit</h3>
                        <span class="cta-link">Shop Earrings</span>
                    </div>
                </a>
            </div>
            <!-- Right Stack -->
            <div class="col-md-5">
                <div class="row g-3 h-100">
                    <div class="col-12 reveal" style="height: 50%;">
                        <a href="<?= url('products.php?category=rings') ?>" class="collection-card d-block h-100" style="min-height: 230px;">
                            <img src="https://images.unsplash.com/photo-1605100804763-247f67b3557e?q=80&w=600&auto=format&fit=crop" alt="Statement Rings" loading="lazy">
                            <div class="collection-card-overlay"></div>
                            <div class="collection-card-content">
                                <h3>Statement Rings</h3>
                                <span class="cta-link">Explore Now</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-12 reveal" style="height: 50%;">
                        <a href="<?= url('products.php?category=necklaces') ?>" class="collection-card d-block h-100" style="min-height: 230px;">
                            <img src="https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?q=80&w=600&auto=format&fit=crop" alt="Gold Necklaces" loading="lazy">
                            <div class="collection-card-overlay"></div>
                            <div class="collection-card-content">
                                <h3>Temple Gold Series</h3>
                                <span class="cta-link">View Collection</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ────────────────────────────────────────────────────────
     BESTSELLERS — Product Grid on Dark
──────────────────────────────────────────────────────── -->
<section class="py-5" style="background: var(--bg-surface);">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-end mb-4 gap-2 reveal">
            <div>
                <span class="section-tag">Curated For You</span>
                <h2 class="section-title mb-0">Our Bestsellers</h2>
            </div>
            <a href="<?= url('products.php') ?>" class="btn-lux-outline" style="padding: .6rem 1.4rem; font-size: .68rem;">
                View All <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="row g-3 g-md-4">
            <?php foreach ($featuredProducts as $p): 
                $thumb = productThumb($p['images']);
                $price = $p['effective_price'];
                $hasDiscount = $p['sale_price'] > 0 && $p['sale_price'] < $p['price'];
            ?>
                <div class="col-6 col-md-4 col-lg-3 reveal">
                    <div class="product-card">
                        <!-- Badges -->
                        <?php if ($hasDiscount): ?>
                            <span class="card-badge sale"><?= round((1 - $p['sale_price'] / $p['price']) * 100) ?>% OFF</span>
                        <?php elseif ($p['is_featured']): ?>
                            <span class="card-badge featured">Signature</span>
                        <?php endif; ?>

                        <!-- Wishlist -->
                        <div class="btn-wishlist <?php echo isset($_SESSION['wishlist'][$p['id']])?'active':'' ?>" data-wishlist="<?= $p['id'] ?>">
                            <i class="<?php echo isset($_SESSION['wishlist'][$p['id']])?'fa-solid':'fa-regular' ?> fa-heart"></i>
                        </div>

                        <a href="<?= url('product.php?slug=' . $p['slug']) ?>" class="card-img-wrap d-block">
                            <!-- Badges -->
                            <?php if ($hasDiscount): ?>
                                <span class="card-badge sale"><?= round((1 - $p['sale_price'] / $p['price']) * 100) ?>% OFF</span>
                            <?php elseif ($p['is_featured']): ?>
                                <span class="card-badge featured">Signature</span>
                            <?php endif; ?>

                            <!-- Wishlist -->
                            <div class="btn-wishlist <?php echo isset($_SESSION['wishlist'][$p['id']])?'active':'' ?>" data-wishlist="<?= $p['id'] ?>">
                                <i class="<?php echo isset($_SESSION['wishlist'][$p['id']])?'fa-solid text-danger':'fa-regular' ?> fa-heart"></i>
                            </div>

                            <img src="<?= url($thumb) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                            
                            <!-- Hover Action -->
                            <div class="card-action-wrap" data-add-cart="<?= $p['id'] ?>">
                                <span class="card-action-btn"><i class="fa-solid fa-plus small me-1"></i> Add to Collection</span>
                            </div>
                        </a>

                        <div class="product-card-body">
                            <div class="product-cat"><?= e($p['category_name'] ?? 'Luxury Jewelry') ?></div>
                            <a href="<?= url('product.php?slug=' . $p['slug']) ?>" class="product-name-link text-truncate px-2" title="<?= e($p['name']) ?>">
                                <?= e($p['name']) ?>
                            </a>

                            <div class="rating-minimal">
                                <?= starRating($p['avg_rating'] ?? 5) ?>
                                <span class="review-count">(<?= $p['review_count'] ?? rand(5, 50) ?>)</span>
                            </div>

                            <div class="price-wrap">
                                <span class="sale-price"><?= formatPrice($price) ?></span>
                                <?php if ($hasDiscount): ?>
                                    <span class="old-price"><?= formatPrice($p['price']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ────────────────────────────────────────────────────────
     EDITORIAL BANNER — Full Width Divine
──────────────────────────────────────────────────────── -->
<section class="position-relative overflow-hidden reveal" style="height: 420px;">
    <img src="https://images.unsplash.com/photo-1573408301185-9146fe634ad0?q=80&w=1920&auto=format&fit=crop" 
         alt="Divine Indian Sculpture" 
         class="w-100 h-100" style="object-fit: cover; filter: brightness(0.75) saturate(0.9);">
    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="z-index:2;">
        <div class="text-center px-3">
            <span style="font-family: var(--font-body); font-size: .65rem; font-weight: 700; letter-spacing: .25em; text-transform: uppercase; color: var(--gold); display: block; margin-bottom: .8rem;">✦ Limited Edition ✦</span>
            <h2 class="shimmer-gold" style="font-family: var(--font-heading); font-size: clamp(1.8rem, 4vw, 3.2rem); font-weight: 400; font-style: italic; margin-bottom: 1rem; line-height: 1.2;">The Bridal Collection</h2>
            <p style="font-size: .95rem; font-weight: 300; color: var(--text-secondary); max-width: 500px; margin: 0 auto 1.5rem;">Exquisite pieces for your most cherished moments. Handcrafted with love and devotion.</p>
            <a href="<?= url('products.php?featured=1') ?>" class="btn-lux-primary">
                Shop Bridal <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- ────────────────────────────────────────────────────────
     NEW ARRIVALS — Dark Background
──────────────────────────────────────────────────────── -->
<?php if (!empty($newProducts)): ?>
<section class="py-5" style="background: var(--bg-main);">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <span class="section-tag">Just Landed</span>
            <h2 class="section-title text-center">New Arrivals</h2>
            <div class="section-divider"></div>
        </div>

        <div class="row g-3 g-md-4">
            <?php foreach ($newProducts as $p): 
                $thumb = productThumb($p['images']);
                $price = $p['effective_price'];
                $hasDiscount = $p['sale_price'] > 0 && $p['sale_price'] < $p['price'];
            ?>
                <div class="col-6 col-md-3 reveal">
                    <div class="product-card">
                        <a href="<?= url('product.php?slug=' . $p['slug']) ?>" class="card-img-wrap d-block">
                            <img src="<?= url($thumb) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                            <span class="card-badge featured">New</span>
                        </a>
                        <div class="card-body text-center">
                            <h6 style="font-family: var(--font-heading); font-size: 1rem; font-weight: 500; color: var(--text-primary); margin-bottom: .4rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?= e($p['name']) ?>
                            </h6>
                            <div class="price-wrap">
                                <span style="font-weight: 700; color: var(--gold); font-size: .95rem;"><?= formatPrice($price) ?></span>
                                <?php if ($hasDiscount): ?>
                                    <span style="font-size: .8rem; color: var(--text-muted); text-decoration: line-through; margin-left: .3rem;"><?= formatPrice($p['price']) ?></span>
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

<!-- ────────────────────────────────────────────────────────
     TESTIMONIALS — Dark Cards with Gold
──────────────────────────────────────────────────────── -->
<section class="py-5" style="background: var(--bg-surface);">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <span class="section-tag">What They Say</span>
            <h2 class="section-title text-center">Our Happy Customers</h2>
            <div class="section-divider"></div>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4 reveal">
                <div class="testimonial-card h-100">
                    <div class="stars">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                    <blockquote>"The craftsmanship is absolutely divine. My temple jewelry set is stunning — I get compliments every time."</blockquote>
                    <div class="author">— Priya M., Mumbai</div>
                </div>
            </div>
            <div class="col-md-4 reveal">
                <div class="testimonial-card h-100">
                    <div class="stars">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                    <blockquote>"Beautiful packaging, quick delivery, and the Ganesh sculpture was even more gorgeous in person. Highly recommend!"</blockquote>
                    <div class="author">— Sneha R., Delhi</div>
                </div>
            </div>
            <div class="col-md-4 reveal">
                <div class="testimonial-card h-100">
                    <div class="stars">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star-half-stroke"></i>
                    </div>
                    <blockquote>"I ordered a bridal set for my daughter. She was overjoyed. The quality of gold plating is exceptional."</blockquote>
                    <div class="author">— Anita K., Bangalore</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ────────────────────────────────────────────────────────
     NEWSLETTER — Dark Elegant Strip
──────────────────────────────────────────────────────── -->
<section class="newsletter-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 text-center reveal">
                <span style="font-family: var(--font-body); font-size: .65rem; font-weight: 700; letter-spacing: .25em; text-transform: uppercase; color: var(--gold); display: block; margin-bottom: .8rem;">✦ Stay Connected ✦</span>
                <h2 style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 400; font-style: italic; margin-bottom: .6rem; color: var(--text-primary);">Join the Inner Circle</h2>
                <p style="color: var(--text-secondary); font-size: .9rem; margin-bottom: 2rem;">Be the first to know about new collections, exclusive offers, and styling tips.</p>
                <form class="d-flex gap-0 mx-auto" style="max-width: 500px;" id="newsletterForm">
                    <input type="email" class="form-control flex-grow-1" placeholder="Your email address" required style="border-radius: 0; border: 1px solid var(--gold-border); background: transparent; color: var(--text-primary); padding: .8rem 1.2rem;">
                    <button type="submit" class="btn-newsletter">Subscribe</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- ────────────────────────────────────────────────────────
     GSAP ANIMATIONS — ScrollTrigger Cinematic Experience
──────────────────────────────────────────────────────── -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Register ScrollTrigger
    gsap.registerPlugin(ScrollTrigger);

    // 1. Hero Content Animations
    const heroTl = gsap.timeline({ defaults: { ease: "power3.out", duration: 1.2 } });
    heroTl.from(".lux-hero-content .lux-hero-tag", { y: 30, opacity: 0, delay: 0.5 })
          .from(".lux-hero-content h1", { y: 40, opacity: 0 }, "-=0.8")
          .from(".lux-hero-content p", { y: 20, opacity: 0 }, "-=1")
          .from(".lux-hero-content .reveal a", { y: 20, opacity: 0 }, "-=1");

    // 2. Scroll Reveal for Sections
    // We target everything with 'reveal' class
    const revealItems = document.querySelectorAll('.reveal');
    
    revealItems.forEach((item, index) => {
        // Initial state set by CSS (opacity: 0, translateY: 30)
        // GSAP will animate to visible state
        gsap.to(item, {
            scrollTrigger: {
                trigger: item,
                start: "top 88%", // Trigger when top of element hits 88% of viewport
                toggleActions: "play none none none",
                once: true
            },
            y: 0,
            opacity: 1,
            duration: 1.2,
            ease: "power2.out",
            delay: (index % 4) * 0.1 // Stagger items in a row
        });
    });

    // 3. Special Gold Shimmer Hover
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            gsap.to(card.querySelector('img'), { scale: 1.1, duration: 1.5, ease: "power2.out" });
        });
        card.addEventListener('mouseleave', () => {
            gsap.to(card.querySelector('img'), { scale: 1, duration: 1.5, ease: "power2.out" });
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
