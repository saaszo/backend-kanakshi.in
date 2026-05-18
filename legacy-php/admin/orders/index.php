<?php
/**
 * Admin Orders List
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();

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

// Fetch Pagination & Search
$page   = (int)inputStr('page', 1, 'GET');
$search = inputStr('q', '', 'GET');
$statusFilter = inputStr('status', '', 'GET');
$limit  = 15;
$offset = ($page - 1) * $limit;

$where = "1=1";
$params = [];

if ($search) {
    $where .= " AND (o.order_number LIKE ? OR o.ship_name LIKE ? OR o.ship_phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($statusFilter) {
    $where .= " AND o.status = ?";
    $params[] = $statusFilter;
}

// Count Total
$stmtTotal = $db->prepare("SELECT COUNT(*) FROM orders o WHERE $where");
$stmtTotal->execute($params);
$totalRows = $stmtTotal->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Fetch Orders
$sql = "SELECT o.*,
               o.ship_name AS shipping_name,
               o.ship_phone AS shipping_phone,
               COALESCE(o.total_amount, o.total) AS total_amount,
               (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
        FROM orders o 
        WHERE $where 
        ORDER BY o.created_at DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$pageTitle = 'Manage Orders';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-900 text-dark mb-1 ls-1">Sales Ledger</h3>
        <p class="text-muted small mb-0 fw-700 text-uppercase ls-2">Fulfillment Tracking & Records</p>
    </div>
    <a href="<?= url('admin/orders/export.php') ?>" class="btn btn-outline-primary fw-900 rounded-pill px-5 py-3 ls-1 fs-8 text-uppercase shadow-sm">
        <i class="fa-solid fa-file-csv me-2"></i> Export Records
    </a>
</div>

<div class="admin-card mb-4 shadow-sm border-0 overflow-hidden">
    <!-- Search & Filter Bar -->
    <div class="p-4 border-bottom bg-white">
        <form action="" method="GET" class="row g-3">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="q" class="form-control border-light shadow-none" placeholder="Search by Order #, Name or Phone..." value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select border-light shadow-none fw-700" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= $statusFilter == 'pending' ? 'selected' : '' ?>>Pending Review</option>
                    <option value="confirmed" <?= $statusFilter == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="processing" <?= $statusFilter == 'processing' ? 'selected' : '' ?>>In Production</option>
                    <option value="shipped" <?= $statusFilter == 'shipped' ? 'selected' : '' ?>>Dispatched</option>
                    <option value="delivered" <?= $statusFilter == 'delivered' ? 'selected' : '' ?>>Handed Over</option>
                    <option value="cancelled" <?= $statusFilter == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100 fw-900 rounded-pill text-uppercase ls-1 fs-9">Refresh Ledger</button>
                <?php if($search || $statusFilter): ?>
                    <a href="<?= url('admin/orders/index.php') ?>" class="btn btn-outline-danger px-3 rounded-pill" title="Clear Filters"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted fs-9 text-uppercase ls-2">
                <tr>
                    <th class="py-3 px-4">Order Essence</th>
                    <th class="py-3 px-3">Valued Client</th>
                    <th class="py-3 px-3">Date</th>
                    <th class="py-3 px-3">Valuation</th>
                    <th class="py-3 px-3 text-center">Fulfillment</th>
                    <th class="py-3 px-4 text-end">Control</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($orders)): ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted fw-600">No records found for this ledger entry.</td></tr>
                <?php else: ?>
                    <?php foreach($orders as $o): 
                        $statusClass = '';
                        switch($o['status']) {
                            case 'pending': $statusClass = 'badge-soft-warning'; break;
                            case 'confirmed': 
                            case 'processing': $statusClass = 'badge-soft-info'; break;
                            case 'shipped': $statusClass = 'badge-soft-primary'; break;
                            case 'delivered': $statusClass = 'badge-soft-success'; break;
                            default: $statusClass = 'badge-soft-danger';
                        }
                    ?>
                        <tr>
                            <td class="px-4 py-3">
                                <a href="<?= url('admin/orders/view.php?id=' . $o['id']) ?>" class="fw-900 text-dark text-decoration-none ls-1 fs-7 d-block mb-1">
                                    #<?= e($o['order_number']) ?>
                                </a>
                                <div class="text-muted small fw-700 text-uppercase ls-1" style="font-size: 0.65rem;">
                                    <?= $o['item_count'] ?> Masterpiece(s)
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <div class="fw-800 text-dark mb-1"><?= e($o['shipping_name']) ?></div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted small fw-600"><i class="fa-solid fa-phone fs-9 me-1"></i><?= e($o['shipping_phone']) ?></span>
                                    <a href="https://wa.me/91<?= preg_replace('/[^0-9]/', '', $o['shipping_phone']) ?>?text=Hi%20<?= urlencode($o['shipping_name']) ?>" target="_blank" class="text-success" title="Instant Direct Line">
                                        <i class="fa-brands fa-whatsapp"></i>
                                    </a>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-muted small fw-700 text-uppercase ls-1" style="font-size: 0.7rem;">
                                <?= date('d M Y', strtotime($o['created_at'])) ?>
                                <div class="opacity-50 mt-1"><?= date('h:i A', strtotime($o['created_at'])) ?></div>
                            </td>
                            <td class="px-3 py-3">
                                <div class="fw-900 text-dark fs-7"><?= formatPrice($o['total_amount']) ?></div>
                                <div class="badge <?= $o['payment_status'] == 'paid' ? 'text-success' : 'text-warning' ?> p-0 mt-1 fw-800 text-uppercase ls-1" style="font-size: 0.6rem;">
                                    <i class="fa-solid fa-circle me-1" style="font-size: 6px;"></i> <?= $o['payment_status'] ?>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="badge-soft <?= $statusClass ?> rounded-pill px-3 py-1 fw-900 fs-9 ls-1">
                                    <?= ucfirst($o['status']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <a href="<?= url('admin/orders/view.php?id=' . $o['id']) ?>" class="btn btn-primary fw-900 rounded-pill px-4 fs-9 text-uppercase ls-1 py-2 shadow-gold">
                                    Manage
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Professional Pagination -->
    <?php if($totalPages > 1): ?>
        <div class="px-4 py-3 border-top d-flex justify-content-between align-items-center bg-white">
            <div class="text-muted fw-800 fs-9 text-uppercase ls-1">Record <?= ($offset + 1) ?> - <?= ($offset + count($orders)) ?> of <?= $totalRows ?></div>
            <div>
                <?php 
                    $qs = '';
                    if($search) $qs .= '&q='.urlencode($search);
                    if($statusFilter) $qs .= '&status='.urlencode($statusFilter);
                ?>
                <?= paginationLinks($page, $totalPages, url('admin/orders/index.php') . '?1=1'.$qs.'&') ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
