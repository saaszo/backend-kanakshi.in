<?php
/**
 * Product Detail Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

$slug = inputStr('slug', '', 'GET');
if (!$slug) redirect(url('products.php'));

$product = getProductBySlug($slug);

if (!$product) {
    setFlash('error', 'Product not found.');
    redirect(url('products.php'));
}

$productId = $product['id'];
$db = getDB();

// 2. Decode Images & Tags
$images = json_decode($product['images'] ?? '[]', true) ?: [];
$tags   = json_decode($product['tags'] ?? '[]', true) ?: [];
$specs  = json_decode($product['specifications'] ?? '[]', true) ?: [];

// 3. Setup Pricing
$price       = $product['price'];
$salePrice   = $product['sale_price'];
$hasDiscount = $salePrice > 0 && $salePrice < $price;
$activePrice = $hasDiscount ? $salePrice : $price;

// 4. Fetch Variants
$stmtVar = $db->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC");
$stmtVar->execute([$productId]);
$variants = $stmtVar->fetchAll();

$sizes  = [];
$colors = [];
foreach ($variants as $v) {
    if ($v['size'] && !in_array($v['size'], $sizes)) $sizes[] = $v['size'];
    if ($v['color'] && !in_array($v['color'], $colors)) $colors[] = $v['color'];
}

// 5. Fetch Reviews
$stmtRev = $db->prepare("
    SELECT r.*, u.name as user_name 
    FROM product_reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.product_id = ? AND r.status = 'approved' 
    ORDER BY r.created_at DESC
");
$stmtRev->execute([$productId]);
$reviews = $stmtRev->fetchAll();

// 5.1 Check if current user can review (Verified Purchase)
$canReview = false;
$user = currentUser();
if ($user) {
    $stmtCheck = $db->prepare("
        SELECT COUNT(*) 
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'delivered'
    ");
    $stmtCheck->execute([$user['id'], $productId]);
    if ($stmtCheck->fetchColumn() > 0) {
        $canReview = true;
    }
}

// Calculate Rating
$avgRating = 0;
if (count($reviews) > 0) {
    $sum = array_sum(array_column($reviews, 'rating'));
    $avgRating = round($sum / count($reviews), 1);
}

// 6. Fetch Related Products
$relStmt = $db->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1
    ORDER BY p.id DESC LIMIT 4
");
$relStmt->execute([$product['category_id'], $productId]);
$related = $relStmt->fetchAll();

$pageTitle = $product['name'];
$metaTitle = $product['meta_title'] ?: $product['name'];
$metaDesc  = $product['meta_desc'] ?: $product['short_desc'];

// 7. SEO Schema Generation
$schemaJson = '';
if (!empty($product['custom_schema'])) {
    $schemaJson = $product['custom_schema'];
} else {
    $schema = [
        "@context" => "https://schema.org/",
        "@type" => "Product",
        "name" => $product['name'],
        "image" => !empty($images) ? array_map(function($img) { return url($img); }, $images) : [url('assets/img/no-image.svg')],
        "description" => $product['short_desc'] ?: trim(strip_tags($product['description'])),
        "sku" => $product['sku'] ?: trim(str_replace('-', '', $product['slug'])),
        "offers" => [
            "@type" => "Offer",
            "url" => url('product.php?slug='.$product['slug']),
            "priceCurrency" => getSetting('currency', 'INR'),
            "price" => $activePrice,
            "availability" => $product['stock'] > 0 ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
            "itemCondition" => "https://schema.org/NewCondition"
        ]
    ];
    
    if ($avgRating > 0 && count($reviews) > 0) {
        $schema["aggregateRating"] = [
            "@type" => "AggregateRating",
            "ratingValue" => $avgRating,
            "reviewCount" => count($reviews)
        ];
    }
    $schemaJson = "<script type=\"application/ld+json\">\n" . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n</script>";
}

require_once __DIR__ . '/includes/header.php';
// Render the product schema directly after the head
echo $schemaJson;
?>
<!-- Fancybox & Swiper for cinematic gallery -->
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    Fancybox.bind("[data-fancybox]", {
      Images: { zoom: true },
      Carousel: { infinite: false }
    });
</script>

<div class="container py-4 product-detail-page">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url() ?>"><i class="fa-solid fa-house mb-1"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('products.php') ?>">Products</a></li>
            <?php if ($product['category_name']): ?>
                <li class="breadcrumb-item"><a href="<?= url('products.php?category=' . $product['category_slug']) ?>"><?= e($product['category_name']) ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 200px;"><?= e($product['name']) ?></li>
        </ol>
    </nav>

    <div class="row g-5">
        <!-- Left: Images Gallery (Cinematic Slider) -->
        <div class="col-lg-6">
            <div class="product-gallery-container">
                <!-- Main Slider -->
                <div class="swiper productMainSlider mb-3 shadow-sm border border-light rounded-3 bg-white">
                    <div class="swiper-wrapper">
                        <?php if (!empty($images)): ?>
                            <?php foreach ($images as $img): ?>
                                <div class="swiper-slide text-center overflow-hidden">
                                    <a href="<?= url($img) ?>" data-fancybox="gallery" class="d-block py-4">
                                        <img src="<?= url($img) ?>" alt="<?= e($product['name']) ?>" class="img-fluid" style="max-height: 550px; object-fit: contain;">
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="swiper-slide text-center py-5">
                                <img src="<?= url('assets/img/no-image.svg') ?>" alt="No Image" class="img-fluid" style="max-height: 500px; object-fit: contain;">
                            </div>
                        <?php endif; ?>

                        <!-- Video Slide if exists -->
                        <?php if (!empty($product['video_url'])): ?>
                            <div class="swiper-slide d-flex align-items-center justify-content-center bg-black rounded-3">
                                <div class="ratio ratio-16x9">
                                    <?php 
                                        $vidId = '';
                                        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $product['video_url'], $match)) {
                                            $vidId = $match[1];
                                        }
                                    ?>
                                    <iframe src="https://www.youtube.com/embed/<?= $vidId ?>" title="YouTube video" allowfullscreen></iframe>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Navigation Buttons -->
                    <div class="swiper-button-next text-gold"></div>
                    <div class="swiper-button-prev text-gold"></div>
                    
                    <!-- Sale Badge -->
                    <?php if ($hasDiscount): ?>
                        <div class="lux-sale-tag">SALE</div>
                    <?php endif; ?>

                    <button class="btn-wishlist fs-5" style="top:20px; right:20px; width:45px; height:45px;" data-wishlist="<?= $productId ?>">
                        <i class="fa-regular fa-heart"></i>
                    </button>
                </div>

                <!-- Thumbnails Slider (Amazon Style) -->
                <div class="swiper productThumbSlider">
                    <div class="swiper-wrapper">
                        <?php if (!empty($images)): ?>
                            <?php foreach ($images as $img): ?>
                                <div class="swiper-slide pointer border rounded overflow-hidden">
                                    <img src="<?= url($img) ?>" class="img-fluid w-100 h-100 object-fit-cover">
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if (!empty($product['video_url'])): ?>
                            <div class="swiper-slide pointer border rounded overflow-hidden position-relative bg-dark d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-play text-white fs-4"></i>
                                <span class="position-absolute bottom-0 start-0 w-100 bg-black bg-opacity-75 text-white fs-9 text-center py-1">VIDEO</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var thumbSwiper = new Swiper(".productThumbSlider", {
                        spaceBetween: 12,
                        slidesPerView: 4,
                        freeMode: true,
                        watchSlidesProgress: true,
                        breakpoints: {
                            480: { slidesPerView: 5 },
                            768: { slidesPerView: 6 }
                        }
                    });
                    var mainSwiper = new Swiper(".productMainSlider", {
                        spaceBetween: 0,
                        autoHeight: true,
                        navigation: {
                            nextEl: ".swiper-button-next",
                            prevEl: ".swiper-button-prev",
                        },
                        thumbs: {
                            swiper: thumbSwiper,
                        },
                    });
                });
            </script>
        </div>

        <!-- Right: Product Info -->
        <div class="col-lg-6">
            <h1 class="fw-800 text-dark mb-2 lh-sm"><?= e($product['name']) ?></h1>
            
            <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom border-light">
                <div class="d-flex align-items-center text-warning fs-6">
                    <?= starRating($avgRating) ?>
                    <span class="text-secondary small fw-500 ms-2">(<?= count($reviews) ?> Reviews)</span>
                </div>
                <div class="vr bg-secondary opacity-25"></div>
                <div class="text-secondary small fw-600">SKU: <span class="text-dark"><?= e($product['sku'] ?: 'N/A') ?></span></div>
            </div>

            <div class="mb-4">
                <div class="d-flex align-items-baseline gap-3 mb-1" id="priceContainer">
                    <span class="display-5 fw-900 text-dark" id="productPrice"><?= formatPrice($activePrice) ?></span>
                    <?php if ($hasDiscount): ?>
                        <span class="fs-5 text-muted text-decoration-line-through"><?= formatPrice($price) ?></span>
                        <span class="badge bg-danger rounded-pill fw-800 ls-1 px-3 py-2">-<?= discountPercent($price, $salePrice) ?>%</span>
                    <?php endif; ?>
                </div>
                <?php if ($hasDiscount): ?>
                    <div class="text-danger small fw-800 mb-2">
                        <i class="fa-solid fa-tag me-1"></i> You Save: <?= formatPrice($price - $salePrice) ?>
                    </div>
                <?php endif; ?>
                <div id="productStock">
                    <?php if ($product['stock'] > 0): ?>
                        <?php if ($product['stock'] <= 15): ?>
                            <div class="text-danger fw-700 mb-2 mt-1"><i class="fa-solid fa-fire me-1"></i> Hurry! Only <?= $product['stock'] ?> left in stock.</div>
                            <div class="progress" style="height: 6px;" title="Low Stock">
                                <div class="progress-bar bg-danger progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= max(10, $product['stock'] * 6) ?>%"></div>
                            </div>
                        <?php else: ?>
                            <span class="small fw-600 text-success"><i class="fa-solid fa-check-circle me-1"></i> In Stock</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="small fw-600 text-danger"><i class="fa-solid fa-times-circle me-1"></i> Out of Stock</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($product['short_desc']): ?>
                <p class="text-secondary fs-6 mb-4 lh-lg"><?= nl2br(e($product['short_desc'])) ?></p>
            <?php endif; ?>

            <!-- Variants Logic -->
            <div id="variantContainer" data-product-id="<?= $productId ?>">
                <!-- Sizes -->
                <?php if (!empty($sizes)): ?>
                    <div class="mb-3" data-variant-group="size">
                        <label class="form-label text-dark fw-700 text-uppercase fs-8 ls-1">Select Size</label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($sizes as $s): ?>
                                <button type="button" class="variant-btn" data-size="<?= e($s) ?>"><?= e($s) ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Colors -->
                <?php if (!empty($colors)): ?>
                    <div class="mb-4" data-variant-group="color">
                        <label class="form-label text-dark fw-700 text-uppercase fs-8 ls-1">Select Color</label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($colors as $c): ?>
                                <button type="button" class="variant-btn" data-color="<?= e($c) ?>"><?= e($c) ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Add to Cart Form -->
            <div class="row g-3 align-items-center mb-4 pb-4 border-bottom border-light">
                <div class="col-auto">
                    <label class="form-label d-block text-dark fw-700 text-uppercase fs-8 ls-1 mb-2">Quantity</label>
                    <div class="qty-control border border-secondary border-opacity-25 rounded px-2 py-1 bg-white">
                        <button type="button" class="btn text-secondary hover-primary" onclick="if(qty.value>1)qty.value--"><i class="fa-solid fa-minus"></i></button>
                        <input type="number" id="qty" value="1" min="1" max="<?= $product['stock'] > 0 ? (int)$product['stock'] : 1 ?>" class="border-0 shadow-none bg-transparent" readonly>
                        <button type="button" class="btn text-secondary hover-primary" onclick="qty.value++"><i class="fa-solid fa-plus"></i></button>
                    </div>
                </div>
                <div class="col">
                    <label class="form-label d-block text-white mb-2">&nbsp;</label>
                    <button type="button" class="btn btn-lux-primary btn-lg w-100 fw-700 text-uppercase ls-1 py-3 hover-shadow d-flex align-items-center justify-content-center gap-2" 
                            id="addToCartBtn" 
                            data-add-cart="<?= $productId ?>" 
                            data-variant-id="" 
                            <?= $product['stock'] <= 0 ? 'disabled' : '' ?>
                            onclick="this.dataset.qty = document.getElementById('qty').value">
                        <i class="fa-solid fa-bag-shopping fa-lg"></i> Add to Cart
                    </button>
                </div>
            </div>

            <!-- Removed Pincode & Payment Badges as requested -->
            
            <!-- Features list (Bullet Points) -->

             <!-- Features list (Bullet Points) -->
            <?php 
            $bullets = json_decode($product['bullet_points'] ?? '[]', true);
            if (!empty($bullets)): ?>
                <div class="mt-4 mb-4">
                    <h6 class="fw-900 text-dark text-uppercase ls-2 mb-3" style="font-size: 0.75rem;">Masterpiece Intelligence</h6>
                    <ul class="list-unstyled d-flex flex-column gap-3 text-dark fw-600 mb-0 ps-0">
                        <?php foreach($bullets as $bp): ?>
                            <li class="d-flex align-items-start gap-2 fs-7">
                                <i class="fa-solid fa-circle-check text-primary mt-1"></i> 
                                <span><?= e($bp) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else: ?>
                <ul class="list-unstyled d-flex flex-column gap-2 text-secondary small fw-500 mb-4">
                    <li><i class="fa-solid fa-eye text-primary me-2"></i> <strong class="text-dark">High Demand:</strong> <span class="text-danger fw-700"><?= rand(24, 89) ?> people</span> are looking at this right now.</li>
                    <li><i class="fa-solid fa-truck-fast text-success me-2"></i> Dispatches within 24 Hours</li>
                    <li><i class="fa-solid fa-money-bill-wave text-success me-2"></i> Cash on Delivery available</li>
                    <li><i class="fa-solid fa-rotate-left text-success me-2"></i> Easy 7 days hassle-free returns</li>
                </ul>
            <?php endif; ?>

        </div>
    </div> <!-- /.row -->

    <!-- A+ Enhanced Content Section -->
    <?php 
    $aplus = json_decode($product['aplus_content'] ?? '[]', true);
    if (!empty($aplus)): ?>
        <div class="mt-5 pt-4">
            <h4 class="fw-800 text-dark text-uppercase ls-1 fs-6 mb-4 border-bottom pb-2">Product Story</h4>
            <div class="row g-5">
                <?php foreach($aplus as $block): ?>
                    <div class="col-12 mb-4">
                        <div class="row align-items-center g-4">
                            <?php if (!empty($block['image'])): ?>
                                <div class="col-md-6">
                                    <img src="<?= e($block['image']) ?>" class="img-fluid rounded-4 shadow-sm" alt="<?= e($block['title']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <h4 class="fw-800 mb-3"><?= e($block['title']) ?></h4>
                                    <div class="text-secondary lh-lg fs-6"><?= nl2br(e($block['content'])) ?></div>
                                </div>
                            <?php else: ?>
                                <div class="col-12 text-center py-4">
                                    <h4 class="fw-800 mb-3"><?= e($block['title']) ?></h4>
                                    <div class="text-secondary lh-lg fs-6 max-w-800 mx-auto"><?= nl2br(e($block['content'])) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Technical Specifications Table (Amazon Style) -->
    <?php if (!empty($specs)): ?>
        <div class="mt-5 pt-4" id="technical-details">
            <h4 class="fw-900 text-dark text-uppercase ls-2 fs-6 mb-4 border-bottom pb-2">Technical Specifications</h4>
            <div class="row">
                <div class="col-lg-8">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <tbody>
                                <?php foreach($specs as $label => $value): ?>
                                    <tr>
                                        <th class="bg-light text-muted fw-700 py-3 px-4" style="width: 35%; fs-8"><?= e($label) ?></th>
                                        <td class="py-3 px-4 fw-600 text-dark"><?= e($value) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-4 d-none d-lg-block">
                    <div class="bg-light rounded-4 p-4 h-100 border text-center d-flex flex-column justify-content-center">
                        <i class="fa-solid fa-gem text-primary display-4 mb-3 opacity-25"></i>
                        <h6 class="fw-800 mb-2">Authentic Masterpiece</h6>
                        <p class="small text-muted mb-0">Every detail in this specification is verified for authenticity and brand standards.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Tabs Content -->
    <div class="row mt-5 pt-4">
        <div class="col-12">
            <ul class="nav nav-tabs border-bottom-0 gap-2 mb-4" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill fw-600 px-4 border shadow-sm" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc" type="button" role="tab">Description</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-600 px-4 border shadow-sm" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">Reviews (<?= count($reviews) ?>)</button>
                </li>
            </ul>
            
            <div class="tab-content bg-white p-4 p-md-5 rounded-4 border shadow-sm" id="productTabsContent">
                <!-- Description Tab -->
                <div class="tab-pane fade show active" id="desc" role="tabpanel">
                    <div class="text-secondary lh-lg" style="font-size: 1.05rem;">
                        <?= $product['description'] ? $product['description'] : '<p class="text-muted fst-italic">No detailed description available.</p>' ?>
                    </div>
                </div>
                
                <!-- Reviews Tab -->
                <div class="tab-pane fade" id="reviews" role="tabpanel">
                    <div class="row g-5">
                        <div class="col-md-5 border-end">
                            <h4 class="fw-800 mb-3">Customer Reviews</h4>
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <h1 class="display-4 fw-800 text-dark mb-0"><?= number_format($avgRating, 1) ?></h1>
                                <div>
                                    <div class="fs-5 text-warning mb-1"><?= starRating($avgRating) ?></div>
                                    <div class="text-secondary small fw-600">Based on <?= count($reviews) ?> reviews</div>
                                </div>
                            </div>
                            
                            <!-- Verified Purchase Review Check -->
                            <?php if (isLoggedIn()): ?>
                                <?php if ($canReview): ?>
                                    <button class="btn btn-outline-primary fw-600 mt-2 rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                        <i class="fa-solid fa-pencil ms-1"></i> Write a Review
                                    </button>
                                <?php else: ?>
                                    <div class="alert alert-light border small fw-600 mt-3 rounded-4">
                                        <i class="fa-solid fa-lock me-2 text-warning"></i> Only customers who have purchased this product can leave a review.
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="<?= url('login.php') ?>" class="btn btn-outline-secondary fw-600 mt-2 rounded-pill px-4">Log in to Review</a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-7">
                            <?php if (empty($reviews)): ?>
                                <div class="text-center py-4">
                                    <h5 class="text-muted fw-600">No reviews yet</h5>
                                    <p class="text-secondary small mb-0">Be the first to review this product!</p>
                                </div>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-4">
                                    <?php foreach ($reviews as $rev): ?>
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="mb-0 fw-700 text-dark"><?= e($rev['user_name']) ?></h6>
                                                <small class="text-secondary fw-500"><?= date('M j, Y', strtotime($rev['created_at'])) ?></small>
                                            </div>
                                            <div class="text-warning fs-8 mb-2"><?= starRating($rev['rating']) ?></div>
                                            <?php if ($rev['comment']): ?>
                                                <p class="mb-0 text-secondary fs-6 lh-base"><?= nl2br(e($rev['comment'])) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if ($related): ?>
    <div class="mt-5 pt-5 border-top">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h3 class="fw-900 text-dark mb-1 ls-1">Recommended Acquisitions</h3>
                <p class="text-muted small mb-0 fw-700 text-uppercase ls-2">Curated selection for your distinguished taste</p>
            </div>
            <a href="<?= url('products.php?category=' . $product['category_slug']) ?>" class="btn btn-link text-primary fw-800 text-decoration-none text-uppercase ls-1 fs-9">View Collection <i class="fa-solid fa-arrow-right ms-1"></i></a>
        </div>
        
        <div class="row g-4">
            <?php foreach ($related as $p): 
                $rThumb = productThumb($p['images']);
                $rPrice = $p['sale_price'] > 0 ? $p['sale_price'] : $p['price'];
                $rOldPrice = $p['sale_price'] > 0 ? $p['price'] : 0;
            ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card">
                        <a href="<?= url('product.php?slug=' . $p['slug']) ?>" class="card-img-wrap d-block">
                            <!-- Badges -->
                            <?php if ($rOldPrice > 0): ?>
                                <span class="card-badge sale"><?= discountPercent($rOldPrice, $rPrice) ?>% OFF</span>
                            <?php endif; ?>

                            <!-- Wishlist -->
                            <div class="btn-wishlist <?php echo isset($_SESSION['wishlist'][$p['id']])?'active':'' ?>" data-wishlist="<?= $p['id'] ?>">
                                <i class="<?php echo isset($_SESSION['wishlist'][$p['id']])?'fa-solid text-danger':'fa-regular' ?> fa-heart"></i>
                            </div>

                            <img src="<?= url($rThumb) ?>" alt="<?= e($p['name']) ?>" class="img-fluid w-100 h-100 object-fit-contain p-3">
                            
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
                                <span class="sale-price"><?= formatPrice($rPrice) ?></span>
                                <?php if ($rOldPrice > 0): ?>
                                    <span class="old-price"><?= formatPrice($rOldPrice) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <style>
        .h-40px { height: 40px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
    </style>
    <?php endif; ?>

</div>

<!-- Review Modal -->
<?php if (isLoggedIn()): ?>
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-800">Write a Review</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="<?= url('ajax/submit-review.php') ?>" method="POST" id="reviewForm">
                    <?= csrfField() ?>
                    <input type="hidden" name="product_id" value="<?= $productId ?>">
                    
                    <div class="mb-4 text-center">
                        <label class="form-label d-block fw-700">Rating</label>
                        <div class="rating-stars d-inline-flex gap-1 flex-row-reverse fs-3 text-warning">
                            <!-- CSS logic puts empty stars visually on right, visually reversed. Simple vanilla implementation -->
                            <input type="radio" name="rating" value="5" id="s5" class="d-none"><label for="s5" class="cursor-pointer"><i class="fa-solid fa-star"></i></label>
                            <input type="radio" name="rating" value="4" id="s4" class="d-none"><label for="s4" class="cursor-pointer"><i class="fa-regular fa-star"></i></label>
                            <input type="radio" name="rating" value="3" id="s3" class="d-none"><label for="s3" class="cursor-pointer"><i class="fa-regular fa-star"></i></label>
                            <input type="radio" name="rating" value="2" id="s2" class="d-none"><label for="s2" class="cursor-pointer"><i class="fa-regular fa-star"></i></label>
                            <input type="radio" name="rating" value="1" id="s1" class="d-none" checked><label for="s1" class="cursor-pointer"><i class="fa-regular fa-star"></i></label>
                        </div>
                        <style>
                            .rating-stars label { opacity: 0.3; transition: all 0.2s; }
                            .rating-stars label:hover, .rating-stars label:hover ~ label, 
                            .rating-stars input:checked ~ label { opacity: 1; }
                            .rating-stars input:checked ~ label i { font-weight: 900; }
                        </style>
                    </div>
                    
                    <div class="mb-4">
                        <label for="comment" class="form-label fw-700">Review</label>
                        <textarea class="form-control bg-light border-0 shadow-none" id="comment" name="comment" rows="4" placeholder="What did you like or dislike?"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 fw-700 py-3 rounded-pill">Submit Review</button>
                    <p class="text-center text-muted small mt-3 mb-0">Your review will be published after approval.</p>
                </form>
            </div>
        </div>
    </div>
</div>
<?php 
$extraJs = <<<JS
<script>
document.getElementById('reviewForm')?.addEventListener('submit', async function(e){
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Submitting...';
    
    const res = await postAjax(this.action, Object.fromEntries(new FormData(this)));
    if(res.success) {
        bootstrap.Modal.getInstance(document.getElementById('reviewModal')).hide();
        showToast('success', res.message);
        this.reset();
    } else {
        showToast('error', res.message);
    }
    btn.disabled = false;
    btn.innerHTML = 'Submit Review';
});

// Sticky Add To Cart Logic
document.addEventListener('DOMContentLoaded', function() {
    const mainAddToCartBtn = document.getElementById('addToCartBtn');
    const stickyBar = document.getElementById('stickyCartBar');
    
    if (mainAddToCartBtn && stickyBar) {
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].intersectionRatio === 0) {
                // Main button is completely out of view
                stickyBar.classList.add('show');
            } else {
                // Main button is visible
                stickyBar.classList.remove('show');
            }
        }, { threshold: 0 });
        
        observer.observe(mainAddToCartBtn);
        
        // Sync button click
        document.getElementById('stickyAddToCartBtn').addEventListener('click', function() {
            mainAddToCartBtn.click(); // Trigger original button containing variants logic
        });
    }

    // Callback Form Logic
    const callbackForm = document.getElementById('callbackForm');
    if (callbackForm) {
        callbackForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('callbackBtn');
            const msg = document.getElementById('callbackMsg');
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            btn.disabled = true;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            
            const res = await postAjax(BASE_URL + 'ajax/request-callback.php', data);
            
            if (res.success) {
                msg.innerHTML = '<span class="text-success small fw-700"><i class="fa-solid fa-check-circle me-1"></i> Requested! We will call you soon.</span>';
                this.reset();
                btn.innerHTML = '<i class="fa-solid fa-check"></i>';
            } else {
                msg.innerHTML = '<span class="text-danger small fw-700">' + res.message + "</span>";
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    }
});
</script>
JS;
endif; ?>

<!-- ────────────────────────────────────────────────────────
     STICKY ADD TO CART BAR
──────────────────────────────────────────────────────── -->
<div id="stickyCartBar" class="sticky-cart-bar shadow-lg border-top border-light">
    <div class="container h-100">
        <div class="row h-100 align-items-center justify-content-between">
            <div class="col-auto d-none d-md-flex align-items-center gap-3">
                <img src="<?= !empty($images) ? url($images[0]) : url('assets/img/no-image.svg') ?>" alt="Product" class="rounded bg-white border" style="width: 50px; height: 50px; object-fit: cover;">
                <div>
                    <div class="fw-700 text-dark lh-sm text-truncate" style="max-width: 300px;"><?= e($product['name']) ?></div>
                    <div class="text-primary fw-800"><?= formatPrice($activePrice) ?></div>
                </div>
            </div>
            <div class="col col-md-auto d-flex gap-3 align-items-center justify-content-end w-100">
                <div class="d-md-none text-primary fw-800 me-auto fs-5"><?= formatPrice($activePrice) ?></div>
                <button type="button" id="stickyAddToCartBtn" class="btn btn-lux-primary fw-800 px-4 py-2 rounded-pill shadow-sm" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                    Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.sticky-cart-bar {
    position: fixed;
    bottom: -100px; /* Hidden state */
    left: 0;
    width: 100%;
    height: 80px;
    background-color: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    z-index: 1040;
    transition: bottom 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.sticky-cart-bar.show {
    bottom: 0;
}
/* Ensure it doesn't overlap the scroll-top or whatsapp float */
@media (max-width: 768px) {
    .whatsapp-float { bottom: 100px !important; }
    .btn-scroll-top { bottom: 100px !important; }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
