<?php
/**
 * Shopping Cart Page
 */
require_once __DIR__ . '/config/config.php';
$pageTitle = 'Shopping Cart';
require_once __DIR__ . '/includes/header.php';

$cartItems = getCartItems();
$totals    = cartTotals();

// Flash messages for coupons
$couponMsg   = '';
$couponClass = '';
if (isset($_SESSION['coupon'])) {
    $couponMsg   = "Coupon applied: " . $_SESSION['coupon']['code'];
    $couponClass = "text-success";
}

// Fetch Upsell Products (e.g. 4 random products under 1000 INR, not in cart)
$cartProductIds = array_column($cartItems, 'product_id');
$inClauseObj = empty($cartProductIds) ? '0' : implode(',', array_map('intval', $cartProductIds));
$db = getDB();
$upsellStmt = $db->query("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1 AND p.price < 2000 AND p.id NOT IN ($inClauseObj)
    ORDER BY RAND() LIMIT 4
");
$upsellProducts = $upsellStmt->fetchAll();

?>

<div class="container py-5 cart-page">
    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom border-light">
        <div class="bg-primary-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
            <i class="fa-solid fa-cart-shopping fa-lg"></i>
        </div>
        <h2 class="fw-800 text-dark mb-0">Your Cart</h2>
    </div>

    <?php if (empty($cartItems)): ?>
        <div class="empty-state bg-white rounded-xl shadow-sm border border-light py-5">
            <i class="fa-brands fa-shopify empty-icon text-muted mb-3 d-block" style="font-size: 5rem;"></i>
            <h4 class="fw-800 text-dark mb-2">Your cart is empty</h4>
            <p class="text-secondary mb-4">Looks like you haven't added anything to your cart yet.</p>
            <a href="<?= url('products.php') ?>" class="btn btn-primary btn-lg fw-700 px-5 rounded-pill">Start Shopping</a>
        </div>
    <?php else: 
        // Cart Reservation Logic
        if (!isset($_SESSION['cart_reserved_time'])) {
            $_SESSION['cart_reserved_time'] = time() + (10 * 60); // 10 minutes from now
        }
        $timeLeft = max(0, $_SESSION['cart_reserved_time'] - time());
    ?>
        <div class="alert alert-warning border-warning bg-warning bg-opacity-10 d-flex align-items-center gap-3 mb-4 py-3 border border-1 border-opacity-50" role="alert">
            <i class="fa-solid fa-clock fs-4 text-warning heartbeat-icon"></i>
            <div>
                <strong class="text-dark d-block mb-1">High Demand!</strong>
                <span class="text-dark" style="font-size: 0.95rem;">Your cart is reserved for <strong id="cartTimer" class="text-danger" data-time="<?= $timeLeft ?>">10:00</strong> minutes. Checkout now before items sell out!</span>
            </div>
        </div>
        <style>
            @keyframes heartbeat {
                0% { transform: scale(1); }
                20% { transform: scale(1.2); }
                40% { transform: scale(1); }
                60% { transform: scale(1.2); }
                80% { transform: scale(1); }
            }
            .heartbeat-icon { animation: heartbeat 2s infinite; display: inline-block; }
        </style>

        <div class="row g-5">
            <!-- Left: Cart Items -->
            <div class="col-lg-8">
                <div class="d-flex justify-content-between text-secondary small fw-700 text-uppercase ls-1 px-3 mb-3 d-none d-md-flex">
                    <div style="width: 50%;">Product</div>
                    <div style="width: 20%; text-align: center;">Quantity</div>
                    <div style="width: 20%; text-align: right;">Total</div>
                    <div style="width: 10%;"></div>
                </div>
                
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($cartItems as $item): 
                        $thumb = $item['thumb'];
                        $stock = (int)($item['variant_id'] ? $item['variant_stock'] : $item['total_stock']);
                        $price = (float)$item['unit_price'];
                        $qty   = (int)$item['quantity'];
                        $lineTotal = $price * $qty;
                    ?>
                        <div class="cart-item position-relative shadow-sm border-light" data-cart-row="<?= $item['id'] ?>">
                            <!-- Mobile layout -->
                            <div class="d-md-none d-flex gap-3">
                                <a href="<?= url('product.php?slug=' . $item['slug']) ?>" class="flex-shrink-0">
                                    <img src="<?= url($thumb) ?>" alt="<?= e($item['name']) ?>" class="rounded bg-light border" style="width: 80px; height: 80px; object-fit: cover;">
                                </a>
                                <div class="flex-grow-1">
                                    <a href="<?= url('product.php?slug=' . $item['slug']) ?>" class="text-dark fw-700 text-decoration-none d-block mb-1" style="font-size: .95rem; line-height: 1.3;">
                                        <?= e($item['name']) ?>
                                    </a>
                                    
                                    <?php if ($item['size'] || $item['color']): ?>
                                        <div class="text-secondary small fw-500 mb-1">
                                            <?= e($item['size']) ?><?= ($item['size'] && $item['color']) ? ' / ' : '' ?><?= e($item['color']) ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="text-primary fw-800 mb-2"><?= formatPrice($price) ?></div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="qty-control border bg-light rounded px-1">
                                            <button type="button" class="btn btn-sm shadow-none" data-qty-action="dec" data-cart-id="<?= $item['id'] ?>"><i class="fa-solid fa-minus"></i></button>
                                            <input type="text" class="form-control form-control-sm border-0 bg-transparent px-0 text-center fw-600" value="<?= $qty ?>" data-cart-id="<?= $item['id'] ?>" readonly style="width: 30px;">
                                            <button type="button" class="btn btn-sm shadow-none" data-qty-action="inc" data-cart-id="<?= $item['id'] ?>"><i class="fa-solid fa-plus"></i></button>
                                        </div>
                                        <button class="btn btn-sm text-danger" data-remove-cart="<?= $item['id'] ?>"><i class="fa-regular fa-trash-can"></i></button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Desktop layout -->
                            <div class="d-none d-md-flex align-items-center">
                                <div class="d-flex gap-3 align-items-center" style="width: 50%;">
                                    <a href="<?= url('product.php?slug=' . $item['slug']) ?>">
                                        <img src="<?= url($thumb) ?>" alt="<?= e($item['name']) ?>" class="rounded bg-light border" style="width: 80px; height: 80px; object-fit: cover;">
                                    </a>
                                    <div>
                                        <a href="<?= url('product.php?slug=' . $item['slug']) ?>" class="text-dark fw-700 text-decoration-none hover-primary">
                                            <?= e($item['name']) ?>
                                        </a>
                                        <?php if ($item['size'] || $item['color']): ?>
                                            <div class="text-secondary fs-8 fw-600 mt-1 bg-light d-inline-block px-2 py-1 rounded">
                                                <?= e($item['size']) ?><?= ($item['size'] && $item['color']) ? ' / ' : '' ?><?= e($item['color']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="text-muted small fw-600 mt-1"><?= formatPrice($price) ?></div>
                                    </div>
                                </div>
                                
                                <div style="width: 20%;" class="d-flex justify-content-center">
                                    <div class="qty-control border bg-light rounded px-1">
                                        <button type="button" class="btn btn-sm shadow-none text-secondary hover-primary" data-qty-action="dec" data-cart-id="<?= $item['id'] ?>"><i class="fa-solid fa-minus"></i></button>
                                        <input type="text" class="form-control form-control-sm border-0 bg-transparent px-0 text-center fw-700 text-dark" value="<?= $qty ?>" data-cart-id="<?= $item['id'] ?>" readonly style="width: 40px;">
                                        <button type="button" class="btn btn-sm shadow-none text-secondary hover-primary" data-qty-action="inc" data-cart-id="<?= $item['id'] ?>"><i class="fa-solid fa-plus"></i></button>
                                    </div>
                                </div>
                                
                                <div style="width: 20%; text-align: right;" class="text-dark fw-800" data-line-total="<?= $item['id'] ?>">
                                    <?= formatPrice($lineTotal) ?>
                                </div>
                                
                                <div style="width: 10%; text-align: right;">
                                    <button class="btn btn-light text-danger bg-white border shadow-sm rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width:35px;height:35px;" data-remove-cart="<?= $item['id'] ?>" title="Remove Item">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right: Order Summary -->
            <div class="col-lg-4">
                <div class="order-summary-card shadow-lg border-0 rounded-4 overflow-hidden" style="background: var(--bg-surface); border: 1px solid var(--border-gold) !important;">
                    <div class="p-4 border-bottom border-light bg-light bg-opacity-50">
                        <h5 class="fw-700 text-dark mb-0 ls-1">Order Summary</h5>
                    </div>
                    
                    <div class="p-4">
                        <input type="hidden" id="cartSubtotalRaw" value="<?= $totals['subtotal'] ?>">
                        
                        <div class="summary-details d-flex flex-column gap-3 mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-secondary fw-500">Subtotal</span>
                                <span id="cartSubtotal" class="text-dark fw-700 font-inter"><?= formatPrice($totals['subtotal']) ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-secondary fw-500">Discount</span>
                                <span id="cartDiscount" class="text-success fw-700 font-inter">-<?= formatPrice($totals['discount']) ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-secondary fw-500">GST (<?= GST_PERCENT ?>%)</span>
                                <span id="cartTax" class="text-dark fw-700 font-inter">+<?= formatPrice($totals['tax']) ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center pb-3 border-bottom border-light">
                                <span class="text-secondary fw-500">Shipping</span>
                                <span id="cartShipping" class="fw-700 font-inter <?= $totals['shipping'] == 0 ? 'text-success' : 'text-dark' ?>">
                                    <?= $totals['shipping'] == 0 ? 'FREE' : '+' . formatPrice($totals['shipping']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="fs-5 fw-800 text-dark text-uppercase ls-1">Total</span>
                            <span id="cartTotal" class="fs-3 fw-800 text-primary font-inter"><?= formatPrice($totals['total']) ?></span>
                        </div>
                        
                        <!-- Coupon Apply -->
                        <div class="promo-section mb-4 p-3 rounded-3 bg-light border border-dashed">
                            <label class="form-label fs-8 text-uppercase ls-2 fw-700 text-secondary mb-2">Promo Code</label>
                            <div class="input-group">
                                <input type="text" class="form-control border-end-0 bg-white" id="couponInput" placeholder="Enter code" value="<?= isset($_SESSION['coupon']) ? e($_SESSION['coupon']['code']) : '' ?>" <?= isset($_SESSION['coupon']) ? 'readonly' : '' ?>>
                                <button class="btn <?= isset($_SESSION['coupon']) ? 'btn-success' : 'btn-outline-primary' ?> fw-700 px-3 border-start-0" type="button" id="applyCouponBtn" <?= isset($_SESSION['coupon']) ? 'disabled' : '' ?>>
                                    <?= isset($_SESSION['coupon']) ? 'Applied' : 'Apply' ?>
                                </button>
                            </div>
                            <div id="couponMessage" class="small mt-2 <?= $couponClass ?> fw-600"><?= $couponMsg ?></div>
                        </div>
                        
                        <a href="<?= url('checkout.php') ?>" class="btn btn-lux-primary w-100 py-3 rounded-pill shadow-gold d-flex align-items-center justify-content-center gap-2">
                            Secure Checkout <i class="fa-solid fa-lock fs-7"></i>
                        </a>
                        
                        <div class="trust-footer mt-4 pt-3 border-top border-light text-center">
                            <div class="d-flex justify-content-center gap-3 text-muted mb-2">
                                <i class="fa-brands fa-cc-visa fa-xl hover-primary"></i>
                                <i class="fa-brands fa-cc-mastercard fa-xl hover-primary"></i>
                                <i class="fa-brands fa-cc-stripe fa-xl hover-primary"></i>
                                <i class="fa-brands fa-cc-amazon-pay fa-xl hover-primary"></i>
                            </div>
                            <p class="small text-uppercase ls-2 text-muted fw-700 mb-0" style="font-size: 0.65rem;">100% Secure Transaction</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Upsell / Cross-sell Section -->
    <?php if (!empty($upsellProducts)): ?>
        <div class="mt-5 pt-4 border-top border-light">
            <h4 class="fw-800 text-dark mb-4">Frequently Bought Together</h4>
            <div class="row g-4">
                <?php foreach ($upsellProducts as $up): 
                    $upThumb = productThumb($up['images']);
                    $upPrice = $up['sale_price'] > 0 ? $up['sale_price'] : $up['price'];
                ?>
                    <div class="col-6 col-md-3">
                        <div class="product-card border border-light rounded-3 bg-white shadow-sm h-100 d-flex flex-column">
                            <a href="<?= url('product.php?slug=' . $up['slug']) ?>" class="card-img-wrap d-block p-2 text-center">
                                <img src="<?= url($upThumb) ?>" alt="<?= e($up['name']) ?>" loading="lazy" style="max-height: 120px; object-fit: contain;">
                            </a>
                            <div class="card-body p-3 text-center d-flex flex-column flex-grow-1 border-top border-light">
                                <a href="<?= url('product.php?slug=' . $up['slug']) ?>" class="product-name text-dark fw-600 mb-2 fs-7 text-truncate" style="max-height: unset; line-height:1.2; -webkit-line-clamp: 2; display: -webkit-box; -webkit-box-orient: vertical; white-space: normal;"><?= e($up['name']) ?></a>
                                <div class="sale-price fs-6 fw-800 text-primary mt-auto mb-2"><?= formatPrice($upPrice) ?></div>
                                <button type="button" class="btn btn-sm btn-outline-primary fw-600 rounded-pill w-100" onclick="window.location.href='<?= url('product.php?slug=' . $up['slug']) ?>'">View Item</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

<?php
$extraJs = <<<JS
<script>
document.addEventListener('DOMContentLoaded', function() {
    const timerElem = document.getElementById('cartTimer');
    if (timerElem) {
        let timeLeft = parseInt(timerElem.getAttribute('data-time'));
        
        const tick = setInterval(() => {
            if (timeLeft <= 0) {
                clearInterval(tick);
                timerElem.innerHTML = "00:00";
                timerElem.closest('.alert').classList.replace('alert-warning', 'alert-danger');
                timerElem.closest('.alert').classList.replace('border-warning', 'border-danger');
                timerElem.closest('.alert').querySelector('strong').innerText = "Reservation Expired!";
                return;
            }
            
            timeLeft--;
        const mins = Math.floor(timeLeft / 60);
        const secs = timeLeft % 60;
        timerElem.innerHTML = (mins < 10 ? '0' : '') + mins + ":" + (secs < 10 ? "0" : "") + secs;
      }, 1000);
    }

    // --- Coupon Logic ---
    const couponBtn = document.getElementById("applyCouponBtn");
    const couponInput = document.getElementById("couponInput");
    const couponMsg = document.getElementById("couponMessage");

    if (couponBtn) {
      couponBtn.addEventListener("click", async function () {
        const code = couponInput.value.trim();
        if (!code) return;

        couponBtn.disabled = true;
        couponBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        try {
          const res = await postAjax(BASE_URL + "ajax/apply-coupon.php", {
            code
          });
          if (res.success) {
            couponMsg.innerHTML = res.message;
            couponMsg.className = "small mt-1 text-success fw-600";
            couponInput.readOnly = true;
            couponBtn.innerHTML = "Applied ✓";
            couponBtn.classList.replace("btn-outline-primary", "btn-success");

            // Update UI Totals
            if (res.data) {
              document.getElementById("cartDiscount").innerText = "-" + formatPrice(res.data.discount);
              document.getElementById("cartTotal").innerText = formatPrice(res.data.total);
            }
          } else {
            couponMsg.innerHTML = res.message;
            couponMsg.className = "small mt-1 text-danger fw-600";
            couponBtn.disabled = false;
            couponBtn.innerHTML = "Apply";
          }
        } catch (e) {
          couponMsg.innerHTML = "Error applying coupon.";
          couponBtn.disabled = false;
          couponBtn.innerHTML = "Apply";
        }
      });
    }
  });
</script>
JS;
?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
