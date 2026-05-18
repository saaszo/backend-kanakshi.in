<?php
/**
 * Products Listing & Search Page
 */
require_once __DIR__ . '/config/config.php';

// Prepare Filters
$page     = (int)inputStr('page', 1, 'GET');
$category = inputStr('category', '', 'GET');
$search   = inputStr('q', '', 'GET');
$sort     = inputStr('sort', 'newest', 'GET');
$featured = (int)inputStr('featured', 0, 'GET');
$minPrice = (int)inputStr('min_price', 0, 'GET');
$maxPrice = (int)inputStr('max_price', 0, 'GET');

$filters = [
    'search'    => $search,
    'sort'      => $sort,
    'featured'  => $featured,
];
if ($category) $filters['category'] = $category;
if ($minPrice > 0) $filters['min_price'] = $minPrice;
if ($maxPrice > 0) $filters['max_price'] = $maxPrice;

// Fetch Products from View/Helper
$productData = getProducts($filters, $page, PRODUCTS_PER_PAGE);
$products    = $productData['items'];
$totalRows   = $productData['total'];
$totalPages  = $productData['total_pages'];

// Fetch Categories for Sidebar
$db = getDB();
$stmtCats = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY parent_id ASC, sort_order ASC");
$allCats = $stmtCats->fetchAll();

// Build Tree
$catTree = [];
foreach ($allCats as $c) {
    if (!$c['parent_id']) {
        $catTree[$c['id']] = $c;
        $catTree[$c['id']]['children'] = [];
    } else if (isset($catTree[$c['parent_id']])) {
        $catTree[$c['parent_id']]['children'][] = $c;
    }
}

// Check Category Name for Title
$catName = '';
if ($category) {
    foreach ($allCats as $c) {
        if ($c['slug'] === $category) {
            $catName = $c['name'];
            break;
        }
    }
}

// Determine Page Title
$pageTitle = 'All Products';
$heading   = 'Our Products';
if ($search) {
    $pageTitle = 'Search: ' . htmlspecialchars($search);
    $heading   = 'Search Results for "' . htmlspecialchars($search) . '"';
} elseif ($catName) {
    $pageTitle = $catName;
    $heading   = $catName;
} elseif ($featured) {
    $pageTitle = 'Featured Deals';
    $heading   = 'Featured Deals';
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4 catalog-page">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url() ?>"><i class="fa-solid fa-house mb-1"></i> Home</a></li>
            <?php if ($catName): ?>
                <li class="breadcrumb-item"><a href="<?= url('products.php') ?>">Products</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($catName) ?></li>
            <?php else: ?>
                <li class="breadcrumb-item active" aria-current="page"><?= e($pageTitle) ?></li>
            <?php endif; ?>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- LEFT SIDEBAR: FILTERS -->
        <div class="col-lg-3">
            <!-- Mobile Toggle -->
            <button class="btn btn-outline-primary w-100 d-lg-none mb-3 fw-600" type="button" data-bs-toggle="collapse" data-bs-target="#filterSidebar">
                <i class="fa-solid fa-filter me-2"></i> Show Filters
            </button>
            
            <div class="collapse d-lg-block" id="filterSidebar">
                <div class="filter-sidebar mb-4 shadow-sm border-0 bg-white">
                    <form action="<?= url('products.php') ?>" method="GET" id="filterForm">
                        <!-- Preserve some keys -->
                        <?php if ($search): ?><input type="hidden" name="q" value="<?= e($search) ?>"><?php endif; ?>
                        <?php if ($category): ?><input type="hidden" name="category" value="<?= e($category) ?>"><?php endif; ?>
                        <?php if ($featured): ?><input type="hidden" name="featured" value="1"><?php endif; ?>

                        <!-- Categories -->
                        <div class="mb-4 pb-4 border-bottom border-light">
                            <h6 class="fw-800 text-dark text-uppercase fs-8 ls-1 mb-3">Categories</h6>
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                                <li>
                                    <a href="<?= url('products.php') ?>" class="text-decoration-none fw-600 <?= !$category ? 'text-primary' : 'text-secondary hover-primary' ?>">
                                        <i class="fa-solid fa-angle-right small me-1"></i> All Categories
                                    </a>
                                </li>
                                <?php foreach ($catTree as $parent): ?>
                                    <li>
                                        <a href="<?= url('products.php?category=' . $parent['slug']) ?>" class="text-decoration-none fw-600 <?= ($category === $parent['slug']) ? 'text-primary' : 'text-secondary hover-primary' ?>">
                                            <i class="fa-solid fa-angle-right small me-1"></i> <?= e($parent['name']) ?>
                                        </a>
                                        <?php if (!empty($parent['children'])): ?>
                                            <ul class="list-unstyled ps-3 mt-1 d-flex flex-column gap-1">
                                                <?php foreach ($parent['children'] as $child): ?>
                                                    <li>
                                                        <a href="<?= url('products.php?category=' . $child['slug']) ?>" class="text-decoration-none small fs-7 fw-500 <?= ($category === $child['slug']) ? 'text-primary fw-600' : 'text-muted hover-primary' ?>">
                                                            - <?= e($child['name']) ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-4 pb-4 border-bottom border-light">
                            <h6 class="fw-800 text-dark text-uppercase fs-8 ls-1 mb-3">Price Range</h6>
                            <div class="row g-2 align-items-center">
                                <div class="col-5">
                                    <input type="number" name="min_price" class="form-control form-control-sm text-center" placeholder="Min" value="<?= $minPrice ?: '' ?>" min="0">
                                </div>
                                <div class="col-2 text-center text-muted small">to</div>
                                <div class="col-5">
                                    <input type="number" name="max_price" class="form-control form-control-sm text-center" placeholder="Max" value="<?= $maxPrice ?: '' ?>" min="0">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-outline-primary btn-sm w-100 mt-3 fw-600">Apply Filter</button>
                        </div>
                    </form>
                </div> <!-- /.filter-sidebar -->
            </div>
        </div>

        <!-- RIGHT SIDE: PRODUCTS LIST -->
        <div class="col-lg-9">
            
            <!-- Top Bar: Title & Sort -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 bg-white p-3 rounded-xl shadow-sm border border-light">
                <div class="mb-3 mb-sm-0">
                    <h1 class="h4 fw-800 text-dark mb-1"><?= e($heading) ?></h1>
                    <p class="text-muted small fw-600 mb-0">Showing <?= count($products) ?> of <?= $totalRows ?> product(s)</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="text-secondary small fw-600 text-nowrap mb-0 d-none d-sm-block">Sort by:</label>
                    <select class="form-select form-select-sm shadow-none border-light bg-light fw-600" style="width: auto; cursor: pointer;" onchange="updateSort(this.value)">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name: A to Z</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            <?php if (empty($products)): ?>
                <div class="empty-state bg-white rounded-xl shadow-sm border border-light py-5">
                    <i class="fa-regular fa-face-frown empty-icon text-muted mb-3 d-block"></i>
                    <h4 class="fw-800 text-dark mb-2">No Products Found</h4>
                    <p class="text-secondary">We couldn't find any products matching your current filters.</p>
                    <a href="<?= url('products.php') ?>" class="btn btn-primary fw-600 px-4 mt-3 rounded-pill">Clear All Filters</a>
                </div>
            <?php else: ?>
                <div class="row g-4 mb-5">
                    <?php foreach ($products as $p): 
                        $thumb = productThumb($p['images']);
                        $price = $p['effective_price'];
                        $hasDiscount = $p['sale_price'] > 0 && $p['sale_price'] < $p['price'];
                    ?>
                        <div class="col-6 col-md-4 col-xl-3">
                            <div class="product-card">
                                <a href="<?= url('product.php?slug=' . $p['slug']) ?>" class="card-img-wrap d-block">
                                    <!-- Badges -->
                                    <?php 
                                        $isNew = (strtotime($p['created_at']) > strtotime('-7 days'));
                                    ?>
                                    <?php if ($p['stock'] <= 0): ?>
                                        <span class="card-badge out">Out of Stock</span>
                                    <?php elseif ($hasDiscount): ?>
                                        <span class="card-badge sale"><?= discountPercent($p['price'], $p['sale_price']) ?>% OFF</span>
                                    <?php elseif ($isNew): ?>
                                        <span class="card-badge new">New In</span>
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
                                    <div class="product-cat"><?= e($p['category_name']) ?></div>
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
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="d-flex justify-content-center">
                        <?= paginationLinks($page, $totalPages, url('products.php') . '?' . http_build_query($filters)) ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php 
$extraJs = <<<JS
<script>
function updateSort(val) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', val);
    url.searchParams.delete('page'); // reset to page 1 on sort change
    window.location.href = url.toString();
}
</script>
JS;
require_once __DIR__ . '/includes/footer.php'; 
?>
