<?php
/**
 * User Wishlist
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$user = currentUser();
if (!$user) {
    logoutUser();
    setFlash('info', 'Please login to view your wishlist.');
    redirect(url('login.php'));
}

$db   = getDB();

// Handle Remove from Wishlist
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $removeId = (int)$_GET['remove'];
    $stmt = $db->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user['id'], $removeId]);
    
    // Also update session
    if (isset($_SESSION['wishlist'][$removeId])) {
        unset($_SESSION['wishlist'][$removeId]);
    }
    
    setFlash('success', 'Item removed from wishlist.');
    redirect(url('wishlist.php'));
}

// Fetch Wishlist Items
$stmt = $db->prepare("
    SELECT p.*, c.name AS category_name, c.slug AS category_slug, w.created_at as added_at 
    FROM wishlists w
    JOIN products p ON w.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$stmt->execute([$user['id']]);
$items = $stmt->fetchAll();

$wishlistTotalValue = 0;
$wishlistDiscountValue = 0;
$wishlistSaleCount = 0;

foreach ($items as $item) {
    $currentPrice = (float)($item['sale_price'] > 0 ? $item['sale_price'] : $item['price']);
    $wishlistTotalValue += $currentPrice;

    if ((float)$item['sale_price'] > 0 && (float)$item['sale_price'] < (float)$item['price']) {
        $wishlistSaleCount++;
        $wishlistDiscountValue += ((float)$item['price'] - (float)$item['sale_price']);
    }
}

$pageTitle = 'My Wishlist';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="bg-white rounded-xl shadow-sm border border-light p-4 position-sticky" style="top: 90px;">
                <div class="text-center mb-4 pb-4 border-bottom">
                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem; font-weight: 800;">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                    <h5 class="fw-800 text-dark mb-1"><?= e($user['name']) ?></h5>
                    <p class="text-secondary small mb-0"><?= e($user['email']) ?></p>
                </div>
                
                <nav class="nav flex-column account-sidebar gap-1">
                    <a class="nav-link d-flex align-items-center" href="<?= url('my-account.php') ?>">
                        <i class="fa-regular fa-user" style="width:20px;"></i> Dashboard / Profile
                    </a>
                    <a class="nav-link d-flex align-items-center" href="<?= url('my-orders.php') ?>">
                        <i class="fa-solid fa-box-open" style="width:20px;"></i> My Orders 
                    </a>
                    <a class="nav-link active d-flex align-items-center" href="<?= url('wishlist.php') ?>">
                        <i class="fa-regular fa-heart" style="width:20px;"></i> Wishlist
                    </a>
                    <a class="nav-link d-flex align-items-center text-danger mt-4" href="<?= url('login.php?action=logout') ?>">
                        <i class="fa-solid fa-arrow-right-from-bracket" style="width:20px;"></i> Logout
                    </a>
                </nav>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            
            <h3 class="fw-800 text-dark mb-4">My Wishlist <span class="badge bg-primary rounded-pill ms-2 fs-6"><?= count($items) ?></span></h3>

            <?php if (!empty($items)): ?>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="bg-white rounded-xl shadow-sm border border-light p-4 h-100">
                            <div class="small text-uppercase ls-1 text-muted fw-700 mb-2">Saved Products</div>
                            <div class="display-6 fw-800 text-dark mb-0"><?= count($items) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-white rounded-xl shadow-sm border border-light p-4 h-100">
                            <div class="small text-uppercase ls-1 text-muted fw-700 mb-2">Wishlist Value</div>
                            <div class="display-6 fw-800 text-dark mb-0"><?= formatPrice($wishlistTotalValue) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-white rounded-xl shadow-sm border border-light p-4 h-100">
                            <div class="small text-uppercase ls-1 text-muted fw-700 mb-2">Live Offers</div>
                            <div class="display-6 fw-800 text-dark mb-0"><?= $wishlistSaleCount ?></div>
                            <div class="small text-success mt-2">Potential savings: <?= formatPrice($wishlistDiscountValue) ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (empty($items)): ?>
                <div class="empty-state bg-white rounded-xl shadow-sm border border-light py-5">
                    <i class="fa-regular fa-heart empty-icon text-muted mb-3 d-block" style="font-size: 4rem;"></i>
                    <h4 class="fw-800 text-dark mb-2">Your wishlist is empty</h4>
                    <p class="text-secondary mb-4">Save items you love and buy them later.</p>
                    <a href="<?= url('products.php') ?>" class="btn btn-primary fw-700 px-4 rounded-pill">Explore Products</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($items as $p): 
                        $thumb = productThumb($p['images']);
                        $price = $p['sale_price'] > 0 ? $p['sale_price'] : $p['price'];
                        $hasDiscount = $p['sale_price'] > 0 && $p['sale_price'] < $p['price'];
                        $savedAmount = $hasDiscount ? ((float)$p['price'] - (float)$p['sale_price']) : 0;
                        $isInStock = (int)$p['stock'] > 0;
                    ?>
                        <div class="col-6 col-md-4">
                            <div class="product-card h-100 d-flex flex-column wishlist-product-card">
                                <!-- Remove btn -->
                                <a href="<?= url('wishlist.php?remove=' . $p['id']) ?>" class="btn-wishlist shadow-sm active" title="Remove from Wishlist" style="z-index: 10;">
                                    <i class="fa-solid fa-xmark text-dark"></i>
                                </a>
                                
                                <a href="<?= url('product.php?slug=' . $p['slug']) ?>" class="card-img-wrap d-block bg-white flex-shrink-0">
                                    <img src="<?= url($thumb) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                                </a>
                                
                                <div class="card-body d-flex flex-column flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                        <span class="wishlist-meta-badge"><?= e($p['category_name'] ?: 'Curated Pick') ?></span>
                                        <span class="wishlist-stock <?= $isInStock ? 'in-stock' : 'out-stock' ?>">
                                            <?= $isInStock ? 'In Stock' : 'Sold Out' ?>
                                        </span>
                                    </div>

                                    <a href="<?= url('product.php?slug=' . $p['slug']) ?>" class="product-name text-dark fw-700 fs-6 mb-2 flex-grow-1">
                                        <?= e($p['name']) ?>
                                    </a>

                                    <div class="rating-minimal mb-2">
                                        <?= starRating((float)($p['avg_rating'] ?? 0)) ?>
                                        <span class="review-count">(<?= (int)($p['review_count'] ?? 0) ?>)</span>
                                    </div>
                                    
                                    <div class="price-wrap d-flex align-items-baseline gap-2 mt-auto mb-3">
                                        <span class="sale-price fs-5 text-primary fw-800"><?= formatPrice($price) ?></span>
                                        <?php if ($hasDiscount): ?>
                                            <span class="original-price text-muted fw-600"><?= formatPrice($p['price']) ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="small text-muted mb-3 d-grid gap-1">
                                        <div><strong>Saved on offer:</strong> <?= $hasDiscount ? formatPrice($savedAmount) : 'No active discount' ?></div>
                                        <div><strong>Added to wishlist:</strong> <?= date('M j, Y', strtotime($p['added_at'])) ?></div>
                                        <?php if (!empty($p['sku'])): ?>
                                            <div><strong>SKU:</strong> <?= e($p['sku']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-outline-primary btn-sm w-100 fw-700 rounded-pill" data-add-cart="<?= $p['id'] ?>" <?= $isInStock ? '' : 'disabled' ?>>
                                            <?= $isInStock ? 'Move to Cart' : 'Currently Unavailable' ?>
                                        </button>
                                        <a href="<?= url('product.php?slug=' . $p['slug']) ?>" class="btn btn-light border btn-sm w-100 fw-700 rounded-pill">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<style>
.wishlist-product-card {
    border: 1px solid rgba(179, 135, 82, 0.14);
}
.wishlist-meta-badge,
.wishlist-stock {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .35rem .7rem;
    border-radius: 999px;
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
}
.wishlist-meta-badge {
    background: rgba(179, 135, 82, 0.12);
    color: var(--gold-dark);
}
.wishlist-stock.in-stock {
    background: rgba(38, 143, 93, 0.12);
    color: #1d7a4d;
}
.wishlist-stock.out-stock {
    background: rgba(165, 52, 59, 0.12);
    color: #a5343b;
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
