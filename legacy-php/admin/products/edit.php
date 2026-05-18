<?php
/**
 * Admin Edit Product
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();

$editId = (int)inputStr('id', 0, 'GET');

// Fetch existing product
$stmtProd = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmtProd->execute([$editId]);
$product = $stmtProd->fetch();

if (!$product) {
    setFlash('error', 'Product not found.');
    redirect(url('admin/products/index.php'));
}

$images = json_decode($product['images'] ?? '[]', true) ?: [];

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    
    // Core Fields
    $name        = inputStr('name', '', 'POST');
    $category_id = (int)inputStr('category_id', 0, 'POST');
    $sku         = inputStr('sku', '', 'POST');
    $price       = (float)inputStr('price', 0, 'POST');
    $sale_price  = (float)inputStr('sale_price', 0, 'POST');
    $cost_price  = (float)inputStr('cost_price', 0, 'POST');
    $stock       = (int)inputStr('stock', 0, 'POST');
    $weight      = (float)inputStr('weight', 0, 'POST');
    $video_url   = inputStr('video_url', '', 'POST');
    
    // Content Fields
    $short_desc  = inputStr('short_desc', '', 'POST');
    $description = $_POST['description'] ?? ''; // rich text
    
    // SEO & Toggles
    $meta_title  = inputStr('meta_title', '', 'POST');
    $meta_desc   = inputStr('meta_desc', '', 'POST');
    $custom_schema = $_POST['custom_schema'] ?? '';
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active   = isset($_POST['is_active']) ? 1 : 0;
    
    // Logistics & Tax
    $hsn_code       = inputStr('hsn_code', '', 'POST');
    $gst_percent    = (float)inputStr('gst_percent', 0, 'POST');
    $shipping_type  = inputStr('shipping_type', 'default', 'POST');
    $shipping_fee   = (float)inputStr('shipping_fee', 0, 'POST');

    // Rich Content (JSON)
    $bullet_points  = json_encode(array_filter($_POST['bullet_points'] ?? []));
    $aplus_content  = json_encode($_POST['aplus'] ?? []);

    // Handle Image Removals
    $removeImages = $_POST['remove_images'] ?? [];
    $remainingImages = [];
    foreach ($images as $idx => $img) {
        if (in_array($idx, $removeImages)) {
            $filePath = __DIR__ . '/../../' . $img;
            if (file_exists($filePath)) @unlink($filePath);
        } else {
            $remainingImages[] = $img;
        }
    }
    
    // Handle New Image Uploads
    if (!empty($_FILES['images']['name'][0])) {
        $files = $_FILES['images'];
        for ($i=0; $i < count($files['name']); $i++) { 
            $singleFile = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];
            $res = uploadImage($singleFile, 'uploads/products/');
            if ($res) {
                $remainingImages[] = $res;
            }
        }
    }
    
    // VALIDATION: Minimum 4 Images required
    if (count($remainingImages) < 4) {
        setFlash('error', 'Minimum 4 images are required per product. Please upload more photos before saving.');
        // We do NOT redirect here because we want the user to see which images they have left in the form
    } else {
        $imagesJson = json_encode($remainingImages);
        
        // Check SKU Uniqueness (excluding self)
        $stmtSku = $db->prepare("SELECT id FROM products WHERE sku = ? AND id != ?");
        $stmtSku->execute([$sku, $editId]);
        if ($stmtSku->fetchColumn()) {
            setFlash('error', 'SKU must be unique. Another product uses this SKU.');
        } else {
            try {
                $stmt = $db->prepare("
                    UPDATE products SET 
                        category_id = ?, name = ?, description = ?, short_desc = ?, 
                        price = ?, sale_price = ?, cost_price = ?, stock = ?, sku = ?, weight = ?, 
                        images = ?, video_url = ?, is_featured = ?, is_active = ?, meta_title = ?, meta_desc = ?, custom_schema = ?,
                        bullet_points = ?, aplus_content = ?, hsn_code = ?, gst_percent = ?, shipping_type = ?, shipping_fee = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $category_id, $name, $description, $short_desc,
                    $price, $sale_price, $cost_price, $stock, $sku, $weight,
                    $imagesJson, $video_url, $is_featured, $is_active, $meta_title, $meta_desc, $custom_schema,
                    $bullet_points, $aplus_content, $hsn_code, $gst_percent, $shipping_type, $shipping_fee,
                    $editId
                ]);
                
                // Handle Variants update
                if (isset($_POST['variant_size'])) {
                    $db->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$editId]);
                    
                    $stmtVar = $db->prepare("INSERT INTO product_variants (product_id, size, color, price, stock, sku) VALUES (?, ?, ?, ?, ?, ?)");
                    $vSizes  = $_POST['variant_size'];
                    $vColors = $_POST['variant_color'] ?? [];
                    $vPrices = $_POST['variant_price'] ?? [];
                    $vStocks = $_POST['variant_stock'] ?? [];
                    $vSkus   = $_POST['variant_sku'] ?? [];
                    
                    for ($i=0; $i < count($vSizes); $i++) {
                        $s = trim($vSizes[$i] ?? '');
                        $c = trim($vColors[$i] ?? '');
                        $p = (float)($vPrices[$i] ?? $price);
                        $st = (int)($vStocks[$i] ?? 0);
                        $sk = trim($vSkus[$i] ?? '');
                        
                        if (($s || $c) && $p > 0) {
                            $stmtVar->execute([$editId, $s, $c, $p, $st, $sk]);
                        }
                    }
                }
                
                setFlash('success', 'Product updated successfully.');
                redirect(url('admin/products/index.php'));
                
            } catch (PDOException $e) {
                setFlash('error', 'Database error: ' . $e->getMessage());
            }
        }
    }
}

// Fetch categories for dropdown
$stmtCat = $db->query("SELECT id, name, parent_id FROM categories WHERE is_active = 1 ORDER BY parent_id ASC, sort_order ASC");
$allCats = $stmtCat->fetchAll();

$catTree = [];
foreach ($allCats as $c) {
    if (!$c['parent_id']) {
        $catTree[$c['id']] = $c;
        $catTree[$c['id']]['children'] = [];
    } else if (isset($catTree[$c['parent_id']])) {
        $catTree[$c['parent_id']]['children'][] = $c;
    }
}

// Fetch Variants
$stmtVar = $db->prepare("SELECT * FROM product_variants WHERE product_id = ?");
$stmtVar->execute([$editId]);
$variants = $stmtVar->fetchAll();

$pageTitle = 'Edit Product';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-900 text-dark mb-1">Refine Collection Item</h3>
        <p class="text-secondary small mb-0">Editing: <span class="fw-700 text-dark"><?= e($product['name']) ?></span></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('product.php?slug='.$product['slug']) ?>" target="_blank" class="btn btn-outline-primary fw-800 rounded-pill px-4 ls-1 fs-8 text-uppercase">
            <i class="fa-solid fa-eye me-2"></i> View Live
        </a>
        <a href="<?= url('admin/products/index.php') ?>" class="btn btn-light border fw-800 rounded-pill px-4 ls-1 fs-8 text-uppercase">
            <i class="fa-solid fa-arrow-left me-2"></i> Back
        </a>
    </div>
</div>

<form action="<?= url('admin/products/edit.php?id=' . $editId) ?>" method="POST" enctype="multipart/form-data" id="editProductForm">
    <?= csrfField() ?>
    
    <div class="admin-card mb-4 shadow-sm border-0 overflow-hidden">
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs nav-tabs-custom px-4 pt-3" id="productTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button">1. General Info</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button">2. Pricing & Stock</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="variants-tab" data-bs-toggle="tab" data-bs-target="#variants" type="button">3. Variants</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="aplus-tab" data-bs-toggle="tab" data-bs-target="#aplus" type="button">4. A+ Content</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button">5. SEO & Social</button>
            </li>
        </ul>

        <div class="tab-content p-4 p-lg-5" id="productTabsContent">
            
            <!-- Tab 1: General Info -->
            <div class="tab-pane fade show active" id="general" role="tabpanel">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="mb-4">
                            <label class="form-label fw-800 text-uppercase ls-1 fs-9 text-muted d-block mb-2">Product Title <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-lg shadow-none" value="<?= e($product['name']) ?>" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-800 text-uppercase ls-1 fs-9 text-muted d-block mb-2">Short Teaser</label>
                            <textarea name="short_desc" class="form-control shadow-none" rows="2"><?= e($product['short_desc']) ?></textarea>
                        </div>
                        
                        <div class="mb-0">
                            <label class="form-label fw-800 text-uppercase ls-1 fs-9 text-muted d-block mb-2">Full Narrative Description</label>
                            <textarea name="description" class="form-control rich-editor"><?= e($product['description']) ?></textarea>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="admin-card p-4 bg-light border-0 mb-4">
                            <label class="form-label fw-800 text-uppercase ls-1 fs-9 text-muted d-block mb-2">Video Showcase (YouTube)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-0"><i class="fa-brands fa-youtube text-danger"></i></span>
                                <input type="url" name="video_url" class="form-control border-0 shadow-sm" value="<?= e($product['video_url'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=...">
                            </div>
                            <div class="fs-9 text-muted mt-2">Paste the full YouTube URL to update the video preview.</div>
                        </div>

                        <div class="admin-card p-4 bg-light border-0 mb-4">
                            <label class="form-label fw-800 text-uppercase ls-1 fs-9 text-muted d-block mb-2">Category Placement <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select shadow-none border-0" required>
                                <option value="">Select Category</option>
                                <?php foreach($catTree as $parent): ?>
                                    <option value="<?= $parent['id'] ?>" class="fw-bold" <?= $parent['id'] == $product['category_id'] ? 'selected' : '' ?>><?= e($parent['name']) ?></option>
                                    <?php if(!empty($parent['children'])): ?>
                                        <?php foreach($parent['children'] as $child): ?>
                                            <option value="<?= $child['id'] ?>" <?= $child['id'] == $product['category_id'] ? 'selected' : '' ?>>&nbsp;&nbsp;&mdash; <?= e($child['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="admin-card p-4 bg-light border-0 mb-4">
                            <label class="form-label fw-800 text-uppercase ls-1 fs-9 text-muted d-block mb-3">Gallery Management</label>
                            
                            <?php if(!empty($images)): ?>
                                <div class="row g-2 mb-3">
                                    <?php foreach($images as $idx => $img): ?>
                                        <div class="col-4">
                                            <div class="position-relative border rounded p-1 bg-white">
                                                <img src="<?= url($img) ?>" class="img-fluid rounded" style="aspect-ratio:1/1; object-fit:cover;">
                                                <div class="form-check position-absolute top-0 end-0 m-1 bg-white rounded shadow-sm px-1 pt-1 opacity-75 hover-opacity-100">
                                                    <input class="form-check-input m-0 cursor-pointer" type="checkbox" name="remove_images[]" value="<?= $idx ?>" title="Remove">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="text-center border-2 border-dashed border-silver p-4 rounded-3 mb-3 bg-white position-relative">
                                <i class="fa-solid fa-cloud-arrow-up fa-2x text-muted mb-2"></i>
                                <div class="small fw-600">Add more images</div>
                                <input class="form-control position-absolute opacity-0 start-0 top-0 w-100 h-100 cursor-pointer" type="file" name="images[]" multiple accept="image/*">
                            </div>
                        </div>

                        <div class="admin-card p-4 border-0 bg-gold bg-opacity-10">
                            <h6 class="fw-800 ls-1 fs-9 text-uppercase mb-3">Visibility Settings</h6>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="isActive" value="1" <?= $product['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label fw-700 ms-2" for="isActive">Live on Storefront</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="is_featured" id="isFeatured" value="1" <?= $product['is_featured'] ? 'checked' : '' ?>>
                                <label class="form-check-label fw-700 ms-2" for="isFeatured">Featured Collection</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Pricing & Stock -->
            <div class="tab-pane fade" id="inventory" role="tabpanel">
                <div class="row g-4 justify-content-center">
                    <div class="col-lg-10">
                        <div class="row g-4 mb-5">
                            <div class="col-md-4">
                                <div class="admin-card p-4 border-start border-4 border-primary">
                                    <label class="form-label fw-800 text-uppercase ls-1 fs-9 text-muted mb-2">Retail Price (<?= getSetting('currency') ?>) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="price" class="form-control form-control-lg border-0 bg-light fw-800" value="<?= e($product['price']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="admin-card p-4 border-start border-4 border-success">
                                    <label class="form-label fw-800 text-uppercase ls-1 fs-9 text-muted mb-2">Sale Price (Offer)</label>
                                    <input type="number" step="0.01" name="sale_price" class="form-control form-control-lg border-0 bg-light fw-800 text-success" value="<?= e($product['sale_price']) ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="admin-card p-4 border-start border-4 border-secondary">
                                    <label class="form-label fw-800 text-uppercase ls-1 fs-9 text-muted mb-2">Cost (Internal Only)</label>
                                    <input type="number" step="0.01" name="cost_price" class="form-control form-control-lg border-0 bg-light fw-800 text-muted" value="<?= e($product['cost_price']) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="admin-card p-4 mb-4">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-800 text-uppercase ls-1 fs-9 text-muted mb-2">Unique SKU <span class="text-danger">*</span></label>
                                    <input type="text" name="sku" class="form-control shadow-none" value="<?= e($product['sku']) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-800 text-uppercase ls-1 fs-9 text-muted mb-2">Global Stock Count <span class="text-danger">*</span></label>
                                    <input type="number" name="stock" class="form-control shadow-none" value="<?= e($product['stock']) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-800 text-uppercase ls-1 fs-9 text-muted mb-2">Shipping Weight (KG)</label>
                                    <input type="number" step="0.01" name="weight" class="form-control shadow-none" value="<?= e($product['weight']) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="admin-card p-4 bg-light border-0">
                            <h6 class="fw-800 text-uppercase ls-1 fs-9 border-bottom pb-2 mb-3">Logistics & Tax Compliance</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-700 fs-9 mb-1">HSN Code</label>
                                    <input type="text" name="hsn_code" class="form-control form-control-sm border-0 shadow-sm" value="<?= e($product['hsn_code'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-700 fs-9 mb-1">GST (%)</label>
                                    <input type="number" step="0.1" name="gst_percent" class="form-control form-control-sm border-0 shadow-sm" value="<?= e($product['gst_percent'] ?? 0) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-700 fs-9 mb-1">Shipping Type</label>
                                    <select name="shipping_type" class="form-select form-select-sm border-0 shadow-sm" onchange="toggleShipFee(this.value)">
                                        <option value="default" <?= ($product['shipping_type'] ?? 'default') === 'default' ? 'selected' : '' ?>>Site Default</option>
                                        <option value="free" <?= ($product['shipping_type'] ?? '') === 'free' ? 'selected' : '' ?>>Always Free</option>
                                        <option value="custom" <?= ($product['shipping_type'] ?? '') === 'custom' ? 'selected' : '' ?>>Custom Flat Fee</option>
                                    </select>
                                </div>
                                <div class="col-md-3" id="customShipFee" style="display:<?= ($product['shipping_type'] ?? '') === 'custom' ? 'block' : 'none' ?>;">
                                    <label class="form-label fw-700 fs-9 mb-1">Flat Fee (<?= getSetting('currency') ?>)</label>
                                    <input type="number" step="0.01" name="shipping_fee" class="form-control form-control-sm border-0 shadow-sm" value="<?= e($product['shipping_fee'] ?? 0) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Variants -->
            <div class="tab-pane fade" id="variants" role="tabpanel">
                <div class="admin-card p-4 mb-4 bg-light border-0">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h6 class="fw-900 text-dark text-uppercase ls-1 fs-8 mb-1">Jewelry Specifications</h6>
                            <p class="small text-muted mb-0">Add variations like Ring Size or Material Color.</p>
                        </div>
                        <button type="button" class="btn btn-dark fw-800 rounded-pill px-4 fs-9 ls-1" id="addVariantBtn">
                            <i class="fa-solid fa-plus me-1"></i> ADD VARIANT
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="variantsTable">
                            <thead class="fs-9 text-uppercase ls-1 text-muted">
                                <tr>
                                    <th>Size / Metric</th>
                                    <th>Color / Finish</th>
                                    <th>Var. Price</th>
                                    <th>Stock</th>
                                    <th>SKU</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody id="variantsContainer">
                                <?php if(!empty($variants)): ?>
                                    <?php foreach($variants as $v): ?>
                                    <tr class="border-bottom border-light">
                                        <td>
                                            <input type="text" name="variant_size[]" class="form-control form-control-sm border-0 bg-white shadow-none" value="<?= e($v['size']) ?>">
                                        </td>
                                        <td>
                                            <input type="text" name="variant_color[]" class="form-control form-control-sm border-0 bg-white shadow-none" value="<?= e($v['color']) ?>">
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text border-0 bg-white">₹</span>
                                                <input type="number" step="0.01" name="variant_price[]" class="form-control border-0 bg-white shadow-none fw-700" value="<?= e($v['price']) ?>" required>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="number" name="variant_stock[]" class="form-control form-control-sm border-0 bg-white shadow-none text-center" value="<?= e($v['stock']) ?>" required>
                                        </td>
                                        <td>
                                            <input type="text" name="variant_sku[]" class="form-control form-control-sm border-0 bg-white shadow-none font-monospace fs-9" value="<?= e($v['sku']) ?>" required>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-link text-danger rem-var-btn"><i class="fa-solid fa-trash-can"></i></button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 4: A+ Content -->
            <div class="tab-pane fade" id="aplus" role="tabpanel">
                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="admin-card p-4 h-100 border-0 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="fw-900 text-dark text-uppercase ls-1 fs-8 mb-0">Key Highlights</h6>
                                <button type="button" class="btn btn-sm btn-outline-dark fw-700 rounded-pill px-3" onclick="addBullet()">+ Add Point</button>
                            </div>
                            <div id="bulletsContainer">
                                <?php 
                                $bullets = json_decode($product['bullet_points'] ?? '[]', true) ?: [];
                                if (empty($bullets)) $bullets = [''];
                                foreach($bullets as $bp): 
                                ?>
                                <div class="input-group mb-2">
                                    <span class="input-group-text bg-white border-0"><i class="fa-solid fa-gem text-gold fs-9"></i></span>
                                    <input type="text" name="bullet_points[]" class="form-control border-0 shadow-none border-bottom rounded-0" value="<?= e($bp) ?>">
                                    <button type="button" class="btn btn-link text-danger btn-sm" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="admin-card p-4 h-100 border-0 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="fw-900 text-dark text-uppercase ls-1 fs-8 mb-0">A+ Visual Story Blocks</h6>
                                <button type="button" class="btn btn-sm btn-dark fw-700 rounded-pill px-3" onclick="addAplusBlock()">+ New Block</button>
                            </div>
                            <div id="aplusContainer">
                                <?php 
                                $aplus = json_decode($product['aplus_content'] ?? '[]', true) ?: [];
                                foreach($aplus as $id => $block): 
                                ?>
                                <div class="admin-card p-4 mb-3 bg-white border shadow-sm position-relative rounded-3">
                                    <button type="button" class="btn btn-link text-danger position-absolute top-0 end-0 p-3" onclick="this.parentElement.remove()">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-800 text-uppercase ls-1 fs-9 text-muted mb-2">Block Image URL</label>
                                            <input type="text" name="aplus[<?= $id ?>][image]" class="form-control form-control-sm mb-2" value="<?= e($block['image'] ?? '') ?>">
                                            <div class="fs-9 text-muted fw-500">Recommended: 1200x600px</div>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label fw-800 text-uppercase ls-1 fs-9 text-muted mb-2">Block Heading</label>
                                            <input type="text" name="aplus[<?= $id ?>][title]" class="form-control form-control-sm mb-2" value="<?= e($block['title'] ?? '') ?>">
                                            <label class="form-label fw-800 text-uppercase ls-1 fs-9 text-muted mb-2">Description</label>
                                            <textarea name="aplus[<?= $id ?>][content]" class="form-control form-control-sm" rows="3"><?= e($block['content'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 5: SEO -->
            <div class="tab-pane fade" id="seo" role="tabpanel">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="admin-card p-4 mb-4 border-0 bg-light shadow-sm">
                            <h6 class="fw-900 text-uppercase ls-1 fs-9 mb-4 border-bottom pb-2">Google Search Preview</h6>
                            <div class="mb-4">
                                <label class="form-label fw-800 text-uppercase ls-1 fs-9 text-muted d-block mb-2">Meta Page Title</label>
                                <input type="text" name="meta_title" class="form-control border-0 shadow-sm" value="<?= e($product['meta_title']) ?>">
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-800 text-uppercase ls-1 fs-9 text-muted d-block mb-2">Meta Description</label>
                                <textarea name="meta_desc" class="form-control border-0 shadow-sm" rows="3"><?= e($product['meta_desc']) ?></textarea>
                            </div>
                        </div>

                        <div class="admin-card p-4 border-0 bg-dark text-white">
                            <h6 class="fw-800 text-uppercase ls-1 fs-9 mb-3 opacity-75">Advanced Data Schema (JSON-LD)</h6>
                            <textarea name="custom_schema" class="form-control bg-transparent border-silver text-silver font-monospace fs-8" rows="6"><?= e($product['custom_schema'] ?? '') ?></textarea>
                            <div class="small fw-500 mt-2 opacity-50">Custom script injected for high-end SEO control.</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="admin-footer p-4 bg-light border-top d-flex justify-content-between align-items-center">
            <div class="text-muted fw-600 small">Total Variants Active: <span id="varCount" class="text-dark fw-800"><?= count($variants) ?></span></div>
            <div class="d-flex gap-3">
                <button type="button" class="btn btn-link text-dark fw-800 text-uppercase ls-1 fs-8 text-decoration-none" onclick="window.history.back()">Discard Changes</button>
                <button type="submit" class="btn btn-primary px-5 py-3 fw-900 text-uppercase ls-2 fs-8 rounded-pill shadow-gold">
                    <i class="fa-solid fa-floppy-disk shadow-none me-2"></i> Save Masterpiece
                </button>
            </div>
        </div>
    </div>
</form>

<?php 
ob_start();
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dynamic Variant Rows (Table Based)
    const varBtn = document.getElementById('addVariantBtn');
    const varCont = document.getElementById('variantsContainer');
    const varCount = document.getElementById('varCount');
    
    function updateVarCount() {
        varCount.innerText = varCont.children.length;
    }

    // Bind existing remove buttons
    varCont.addEventListener('click', function(e) {
        if(e.target.closest('.rem-var-btn')) {
            e.target.closest('tr').remove();
            updateVarCount();
        }
    });

    varBtn.addEventListener('click', function() {
        const tr = document.createElement('tr');
        tr.className = 'border-bottom border-light';
        tr.innerHTML = `
            <td>
                <input type="text" name="variant_size[]" class="form-control form-control-sm border-0 bg-white shadow-none" placeholder="e.g. Size">
            </td>
            <td>
                <input type="text" name="variant_color[]" class="form-control form-control-sm border-0 bg-white shadow-none" placeholder="e.g. Finish">
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text border-0 bg-white">₹</span>
                    <input type="number" step="0.01" name="variant_price[]" class="form-control border-0 bg-white shadow-none fw-700" required>
                </div>
            </td>
            <td>
                <input type="number" name="variant_stock[]" class="form-control form-control-sm border-0 bg-white shadow-none text-center" value="0" required>
            </td>
            <td>
                <input type="text" name="variant_sku[]" class="form-control form-control-sm border-0 bg-white shadow-none font-monospace fs-9" required placeholder="SKU-VAR">
            </td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-link text-danger rem-var-btn"><i class="fa-solid fa-trash-can"></i></button>
            </td>
        `;
        varCont.appendChild(tr);
        updateVarCount();
    });
});

function addBullet() {
    const cont = document.getElementById('bulletsContainer');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <span class="input-group-text bg-white border-0"><i class="fa-solid fa-gem text-gold fs-9"></i></span>
        <input type="text" name="bullet_points[]" class="form-control border-0 shadow-none border-bottom rounded-0">
        <button type="button" class="btn btn-link text-danger btn-sm" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
    `;
    cont.appendChild(div);
}

function addAplusBlock() {
    const cont = document.getElementById('aplusContainer');
    const id = Date.now();
    const div = document.createElement('div');
    div.className = 'admin-card p-4 mb-3 bg-white border shadow-sm position-relative rounded-3';
    div.innerHTML = `
        <button type="button" class="btn btn-link text-danger position-absolute top-0 end-0 p-3" onclick="this.parentElement.remove()">
            <i class="fa-solid fa-trash-can"></i>
        </button>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-800 text-uppercase ls-1 fs-9 text-muted mb-2">Block Image URL</label>
                <input type="text" name="aplus[${id}][image]" class="form-control form-control-sm mb-2" placeholder="uploads/aplus/image.jpg">
                <div class="fs-9 text-muted fw-500">Recommended: 1200x600px</div>
            </div>
            <div class="col-md-8">
                <label class="form-label fw-800 text-uppercase ls-1 fs-9 text-muted mb-2">Block Heading</label>
                <input type="text" name="aplus[${id}][title]" class="form-control form-control-sm mb-2" placeholder="Headline">
                <label class="form-label fw-800 text-uppercase ls-1 fs-9 text-muted mb-2">Description</label>
                <textarea name="aplus[${id}][content]" class="form-control form-control-sm" rows="3" placeholder="Description"></textarea>
            </div>
        </div>
    `;
    cont.appendChild(div);
}

function toggleShipFee(val) {
    const el = document.getElementById('customShipFee');
    el.style.display = (val === 'custom') ? 'block' : 'none';
}
</script>
<?php
$extraAdminJs = ob_get_clean();
require_once __DIR__ . '/../includes/footer.php'; 
?>
