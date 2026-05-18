<?php
/**
 * Admin View & Manage Order
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();
$orderId = (int)inputStr('id', 0, 'GET');

// Fetch Order
$stmt = $db->prepare("
    SELECT o.*,
           u.email as user_account_email,
           o.ship_name AS shipping_name,
           o.ship_email AS shipping_email,
           o.ship_phone AS shipping_phone,
           o.ship_address AS shipping_address,
           o.ship_city AS shipping_city,
           o.ship_state AS shipping_state,
           o.ship_pincode AS shipping_pincode,
           COALESCE(o.total_amount, o.total) AS total_amount,
           COALESCE(o.shipping, o.shipping_cost) AS shipping
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    setFlash('error', 'Order not found.');
    redirect(url('admin/orders/index.php'));
}

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    validateCsrf();
    
    $newStatus = inputStr('status', '', 'POST');
    $payStatus = inputStr('payment_status', '', 'POST');
    $trackingNo = inputStr('tracking_number', '', 'POST');
    $trackingUrl = inputStr('tracking_url', '', 'POST');
    
    $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
    $validPaySts   = ['pending', 'paid', 'failed', 'refunded'];
    
    if (in_array($newStatus, $validStatuses) && in_array($payStatus, $validPaySts)) {
        
        $msg = "Order status updated.";
        
        // Stock Restoration if cancelled
        if ($newStatus === 'cancelled' && $order['status'] !== 'cancelled') {
            // Restore stock
            $items = $db->prepare("SELECT product_id, variant_id, quantity FROM order_items WHERE order_id = ?");
            $items->execute([$orderId]);
            foreach ($items->fetchAll() as $item) {
                if ($item['variant_id']) {
                    $db->prepare("UPDATE product_variants SET stock = stock + ? WHERE id = ?")->execute([$item['quantity'], $item['variant_id']]);
                } else {
                    $db->prepare("UPDATE products SET stock = stock + ? WHERE id = ?")->execute([$item['quantity'], $item['product_id']]);
                }
            }
            $msg .= " Stock restored.";
        }
        
        $db->prepare("UPDATE orders SET status = ?, payment_status = ?, tracking_number = ?, tracking_url = ? WHERE id = ?")
           ->execute([$newStatus, $payStatus, $trackingNo ?: null, $trackingUrl ?: null, $orderId]);

        if ($newStatus !== $order['status'] || $trackingNo !== ($order['tracking_number'] ?? '')) {
            $statusMessage = match ($newStatus) {
                'pending' => 'Order received and awaiting confirmation.',
                'confirmed' => 'Order confirmed by the store team.',
                'processing' => 'Order is being prepared for dispatch.',
                'shipped' => 'Shipment has been dispatched.',
                'delivered' => 'Order marked as delivered.',
                'cancelled' => 'Order has been cancelled.',
                'refunded' => 'Refund recorded for this order.',
                default => 'Order updated by admin.'
            };

            $db->prepare("
                INSERT INTO order_tracking (order_id, tracking_number, courier_name, status, location, message, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ")->execute([
                $orderId,
                $trackingNo ?: null,
                'Store Update',
                ucfirst($newStatus),
                $order['ship_city'] ?: null,
                $statusMessage
            ]);
        }
        
        // ── SHIPPING NOTIFICATION ──────────────────────────
        // Send email if status changed to shipped OR tracking number was added/updated
        if (($newStatus === 'shipped' && $order['status'] !== 'shipped') || (!empty($trackingNo) && $trackingNo !== $order['tracking_number'])) {
            sendShippingUpdateEmail($orderId);
            $msg .= " Shipping notification sent.";
        }
        
        setFlash('success', $msg);
        redirect(url('admin/orders/view.php?id=' . $orderId));
    } else {
        setFlash('error', 'Invalid status provided.');
    }
}

// Fetch Order Items
$stmtItems = $db->prepare("
    SELECT oi.*, p.slug as product_slug,
           COALESCE(oi.line_total, oi.price * oi.quantity) AS line_total,
           COALESCE(NULLIF(oi.variant_details, ''), TRIM(CONCAT(COALESCE(oi.size, ''), ' ', COALESCE(oi.color, '')))) AS variant_details
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmtItems->execute([$orderId]);
$items = $stmtItems->fetchAll();

// Quick helper to fetch status color
function getStatusBadge($status) {
    switch ($status) {
        case 'pending': return 'bg-warning text-dark';
        case 'confirmed': 
        case 'processing': return 'bg-info text-dark';
        case 'shipped': return 'bg-primary';
        case 'delivered': return 'bg-success';
        case 'cancelled': 
        case 'refunded': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

$pageTitle = 'Order #' . $order['order_number'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-900 text-dark mb-1 ls-1">Fulfillment Workspace</h3>
        <p class="text-muted small mb-0 fw-700 text-uppercase ls-2">Processing Order <span class="text-primary">#<?= e($order['order_number']) ?></span></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('admin/orders/shipping-label.php?id=' . $orderId) ?>" target="_blank" class="btn btn-dark fw-900 rounded-pill px-4 fs-9 text-uppercase ls-1 py-3 shadow-sm">
            <i class="fa-solid fa-print me-2"></i> Print Label
        </a>
        <a href="<?= url('invoice.php?id=' . urlencode($order['order_number'])) ?>" target="_blank" class="btn btn-primary fw-900 rounded-pill px-4 fs-9 text-uppercase ls-1 py-3 shadow-gold">
            <i class="fa-solid fa-file-invoice me-2"></i> View Invoice
        </a>
        <a href="<?= url('admin/orders/index.php') ?>" class="btn btn-light bg-white border fw-900 rounded-pill px-4 fs-9 text-uppercase ls-1 py-3">
            <i class="fa-solid fa-arrow-left me-2"></i> Back
        </a>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Left Column: Order Content -->
    <div class="col-lg-8">
        
        <?php if($order['status'] === 'cancelled' || $order['status'] === 'refunded'): ?>
            <div class="alert badge-soft-danger border d-flex align-items-center mb-4 p-3 rounded-4 animate-in">
                <i class="fa-solid fa-circle-exclamation me-3 fa-lg"></i>
                <div>
                    <strong class="text-uppercase ls-1 fs-9 d-block">System Alert</strong>
                    <span class="fw-600">This order is marked as <strong><?= strtoupper($order['status']) ?></strong>. No further fulfillment actions are required.</span>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Client Identity Card -->
        <div class="admin-card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-900 text-muted text-uppercase ls-2 fs-9 border-bottom pb-3 mb-4">
                <i class="fa-solid fa-id-card-clip text-primary me-2"></i> Client Identity
            </h6>
            <div class="row g-4">
                <div class="col-md-6 border-end">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-900" style="width:50px; height:50px; font-size: 1.2rem;">
                            <?= strtoupper(substr($order['shipping_name'] ?: 'C', 0, 1)) ?>
                        </div>
                        <div>
                            <div class="fw-900 text-dark fs-5 mb-0"><?= e($order['shipping_name']) ?></div>
                            <div class="text-muted small fw-700 text-uppercase ls-1">Valued Member</div>
                        </div>
                    </div>
                    <div class="text-secondary fw-600 mb-2">
                        <i class="fa-solid fa-envelope me-2 text-muted"></i> <?= e($order['shipping_email']) ?>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-secondary fw-600"><i class="fa-solid fa-mobile-screen-button me-2 text-muted"></i> <?= e($order['shipping_phone']) ?></span>
                        <a href="https://wa.me/91<?= preg_replace('/[^0-9]/', '', $order['shipping_phone']) ?>?text=Hi%20<?= urlencode($order['shipping_name']) ?>!" target="_blank" class="badge-soft-success text-decoration-none px-2 py-1 fs-9 fw-900 rounded-pill">
                            <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp
                        </a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small fw-900 text-uppercase ls-1 mb-2">Delivery Destination</div>
                    <div class="text-dark fw-600 fs-7 lh-base">
                        <?= nl2br(e($order['shipping_address'])) ?><br>
                        <?= e($order['shipping_city']) ?>, <?= e($order['shipping_state']) ?> - <span class="fw-900"><?= e($order['shipping_pincode']) ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Masterpiece Inventory Card -->
        <div class="admin-card border-0 shadow-sm overflow-hidden mb-4">
            <div class="px-4 py-3 border-bottom bg-light bg-opacity-50">
                <h6 class="fw-900 text-dark text-uppercase ls-2 fs-9 mb-0">Masterpiece Selection</h6>
            </div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="bg-light text-muted fs-9 text-uppercase ls-2">
                        <tr>
                            <th class="py-3 px-4 w-50">Masterpiece Essence</th>
                            <th class="py-3 px-3 text-center">Valuation</th>
                            <th class="py-3 px-3 text-center">Qty</th>
                            <th class="py-3 px-4 text-end">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($items as $item): ?>
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-light rounded p-1 border shadow-sm" style="width:60px; height:60px; overflow:hidden;">
                                            <?php if($item['image']): ?>
                                                <img src="<?= url($item['image']) ?>" style="width:100%; height:100%; object-fit:cover; border-radius:4px;">
                                            <?php else: ?>
                                                <i class="fa-solid fa-gem text-muted fs-4 d-flex align-items-center justify-content-center h-100"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <?php if($item['product_slug']): ?>
                                                <a href="<?= url('product.php?slug='.$item['product_slug']) ?>" target="_blank" class="fw-900 text-dark text-decoration-none ls-1 fs-7"><?= e($item['name']) ?></a>
                                            <?php else: ?>
                                                <span class="fw-900 text-dark ls-1"><?= e($item['name']) ?></span>
                                            <?php endif; ?>
                                            
                                            <?php if($item['variant_details']): ?>
                                                <div class="text-primary small fw-800 text-uppercase ls-1 mt-1" style="font-size: 0.65rem;"><?= e($item['variant_details']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center text-muted fw-700 fs-8"><?= formatPrice($item['price']) ?></td>
                                <td class="px-3 py-3 text-center fw-900 text-dark fs-7">&times; <?= $item['quantity'] ?></td>
                                <td class="px-4 py-3 text-end fw-900 text-dark fs-6"><?= formatPrice($item['line_total']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Professional Summary Totals -->
            <div class="p-4 bg-light bg-opacity-25 border-top d-flex justify-content-end">
                <div style="width: 320px;">
                    <div class="d-flex justify-content-between mb-2 fs-8 fw-700">
                        <span class="text-muted text-uppercase ls-1">Subtotal</span>
                        <span class="text-dark"><?= formatPrice($order['subtotal']) ?></span>
                    </div>
                    <?php if($order['discount'] > 0): ?>
                        <div class="d-flex justify-content-between mb-2 fs-8 fw-700">
                            <span class="text-muted text-uppercase ls-1">Loyalty Discount</span>
                            <span class="text-success">-<?= formatPrice($order['discount']) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-2 fs-8 fw-700">
                        <span class="text-muted text-uppercase ls-1">Taxes (GST/VAT)</span>
                        <span class="text-dark">+<?= formatPrice($order['tax']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom border-dark border-opacity-10 fs-8 fw-700">
                        <span class="text-muted text-uppercase ls-1">Secured Logistics</span>
                        <span class="<?= $order['shipping'] == 0 ? 'text-success' : 'text-dark' ?>">
                            <?= $order['shipping'] == 0 ? 'COMPLIMENTARY' : '+' . formatPrice($order['shipping']) ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-900 text-dark text-uppercase ls-1 fs-8">Total Valuation</span>
                        <span class="fw-900 text-primary fs-3 shadow-text"><?= formatPrice($order['total_amount']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right Column: Controls & Protocol -->
    <div class="col-lg-4">
        
        <!-- Fulfillment Controls -->
        <div class="admin-card border-0 shadow-sm p-4 mb-4 bg-dark text-white card-hover-gold">
            <h6 class="fw-900 text-white text-uppercase ls-2 fs-9 border-bottom border-white border-opacity-10 pb-3 mb-4">Fulfillment Suite</h6>
            
            <form action="<?= url('admin/orders/view.php?id='.$orderId) ?>" method="POST" id="fulfillmentForm">
                <?= csrfField() ?>
                <input type="hidden" name="update_status" value="1">
                
                <div class="mb-3">
                    <label class="form-label text-white opacity-50 fs-9 ls-1">Lifecycle Status</label>
                    <select name="status" class="form-select bg-dark text-white border-secondary border-opacity-50 fw-900 fs-7 py-3">
                        <option value="pending" <?= $order['status']=='pending'?'selected':'' ?>>Pending Review</option>
                        <option value="confirmed" <?= $order['status']=='confirmed'?'selected':'' ?>>Confirmed</option>
                        <option value="processing" <?= $order['status']=='processing'?'selected':'' ?>>In Production</option>
                        <option value="shipped" <?= $order['status']=='shipped'?'selected':'' ?>>Dispatched</option>
                        <option value="delivered" <?= $order['status']=='delivered'?'selected':'' ?>>Handed Over</option>
                        <option value="cancelled" <?= $order['status']=='cancelled'?'selected':'' ?>>Annulled / Refund</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-white opacity-50 fs-9 ls-1">Payment State</label>
                    <select name="payment_status" class="form-select bg-dark text-white border-secondary border-opacity-50 fw-900 fs-7 py-3">
                        <option value="pending" <?= $order['payment_status']=='pending'?'selected':'' ?>>Funds Pending</option>
                        <option value="paid" <?= $order['payment_status']=='paid'?'selected':'' ?>>Funds Secured</option>
                        <option value="failed" <?= $order['payment_status']=='failed'?'selected':'' ?>>Payment Failure</option>
                        <option value="refunded" <?= $order['payment_status']=='refunded'?'selected':'' ?>>Refund Issued</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="form-label text-white opacity-50 fs-9 ls-1">Logistics / AWB Number</label>
                    <div class="input-group">
                        <input type="text" name="tracking_number" id="tracking_number_input" class="form-control bg-dark text-white border-secondary border-opacity-50 fw-900 py-3" placeholder="Tracking ID..." value="<?= e($order['tracking_number'] ?? '') ?>">
                        <?php if(empty($order['tracking_number'])): ?>
                            <button type="button" id="btnShiprocket" data-order="<?= $orderId ?>" class="btn btn-outline-light border-secondary border-opacity-50 fw-900 px-4 ls-1" title="Generate AWB via Shiprocket">
                                <i class="fa-solid fa-truck-fast text-primary"></i> <span class="d-none d-xl-inline ms-2">Shiprocket</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 fw-900 rounded-pill py-3 text-uppercase ls-1 fs-8 shadow-gold">
                    <i class="fa-solid fa-check-double me-2"></i> Update Lifecycle
                </button>
            </form>
        </div>
        
        <!-- Financial Protocol -->
        <div class="admin-card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-900 text-muted text-uppercase ls-2 fs-9 border-bottom pb-3 mb-4">Financial Protocol</h6>
            <div class="mb-4">
                <div class="text-muted small fw-900 text-uppercase ls-1 mb-2">Acquisition Method</div>
                <div class="fw-900 text-dark fs-5">
                    <?php 
                        if($order['payment_method'] === 'cod') echo '<i class="fa-solid fa-hand-holding-dollar text-success me-2"></i> Cash on Delivery';
                        else echo '<i class="fa-solid fa-credit-card text-primary me-2"></i> ' . strtoupper($order['payment_method']);
                    ?>
                </div>
            </div>
            
            <?php if($order['payment_id']): ?>
                <div class="mb-0">
                    <div class="text-muted small fw-900 text-uppercase ls-1 mb-2">Verification ID</div>
                    <div class="fw-900 text-dark fs-8 font-monospace bg-light p-3 rounded-4 border border-dashed border-dark border-opacity-10"><?= e($order['payment_id']) ?></div>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if($order['notes']): ?>
            <div class="admin-card border-0 shadow-sm p-4 mb-4 bg-primary bg-opacity-5 border-start border-primary border-4">
                <h6 class="fw-900 text-primary text-uppercase ls-2 fs-9 mb-3"><i class="fa-solid fa-quote-left me-1"></i> Client Annotations</h6>
                <div class="text-dark fw-500 fst-italic fs-7">
                    "<?= nl2br(e($order['notes'])) ?>"
                </div>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<script>
// Phase 17 SweetAlert Implementation
document.getElementById('fulfillmentForm').addEventListener('submit', function(e) {
    const newStatus = this.querySelector('select[name="status"]').value;
    if (newStatus === 'cancelled') {
        e.preventDefault();
        Swal.fire({
            title: 'Annul Order?',
            text: "This will restore inventory stock and stop fulfillment. This action is definitive.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Annul Order',
            cancelButtonText: 'Retain Order',
            customClass: {
                confirmButton: 'btn btn-primary px-4 py-2 rounded-pill fw-900 ls-1',
                cancelButton: 'btn btn-light px-4 py-2 rounded-pill fw-900 ls-1 border ms-2'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    }
});

// Shiprocket AJAX Handler
const btnShiprocket = document.getElementById('btnShiprocket');
if (btnShiprocket) {
    btnShiprocket.addEventListener('click', async function() {
        if (!confirm('This will push the order to Shiprocket and generate a live AWB. Proceed?')) return;
        
        try {
            this.disabled = true;
            this.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Processing';
            
            const res = await postAjax(BASE_URL + 'admin/ajax/shiprocket-order.php', { order_id: this.dataset.order });
            
            if (res.success) {
                showToast('success', res.message);
                document.getElementById('tracking_number_input').value = res.awb ?? res.data?.awb ?? '';
                setTimeout(() => location.reload(), 2000);
            } else {
                showToast('error', res.message);
                this.innerHTML = '<i class="fa-solid fa-truck-fast text-primary"></i> <span class="d-none d-xl-inline ms-2">Shiprocket Error</span>';
            }
        } catch (e) {
            showToast('error', 'API Connection Failed');
            this.innerHTML = '<i class="fa-solid fa-truck-fast text-primary"></i> <span class="d-none d-xl-inline ms-2">Shiprocket</span>';
        } finally {
            this.disabled = false;
        }
    });
}
</script>

<!-- Print Styles -->
<style>
@media print {
    body { background: white !important; font-size: 12pt; }
    .admin-sidebar, .admin-header, #orderActions, .btn, .dropdown, #sidebarOverlay { display: none !important; }
    .admin-wrapper { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
    .admin-card { border: none !important; box-shadow: none !important; }
    .container, .col-lg-8, .col-lg-4, .row { width: 100% !important; max-width: 100% !important; display: block !important; margin: 0 !important;}
    h3 { font-size: 24pt !important; }
    .table th { background-color: #f8f9fa !important; color: #000 !important; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
