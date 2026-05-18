<?php
/**
 * Admin Dashboard
 */
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$db = getDB();

// 1. Quick Stats
$totalUsers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$totalProds = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrds  = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// Total Revenue (only paid or delivered depending on logic, let's take all non-cancelled for general view)
$revenue    = $db->query("SELECT SUM(total) FROM orders WHERE status NOT IN ('cancelled', 'refunded')")->fetchColumn();
$revenue    = $revenue ?: 0;

// KPI: Average Order Value (AOV)
$aov = $totalOrds > 0 ? $revenue / $totalOrds : 0;

// KPI: Recent Customers (Last 30 Days)
$recentCustomersCount = $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();

// 2. Top Selling Products
$stmtTopProds = $db->query("
    SELECT p.id, p.name, p.slug, p.price, p.sale_price, p.images, SUM(oi.quantity) as total_qty
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status NOT IN ('cancelled', 'refunded')
    GROUP BY p.id
    ORDER BY total_qty DESC
    LIMIT 5
");
$topProducts = $stmtTopProds->fetchAll();

// 3. Recent Orders
$stmtOrders = $db->query("
    SELECT o.*, u.name as customer_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC LIMIT 5
");
$recentOrders = $stmtOrders->fetchAll();

$stmtStock = $db->query("SELECT id, name, sku, stock FROM products WHERE stock <= 5 AND is_active = 1 ORDER BY stock ASC LIMIT 5");
$lowStock = $stmtStock->fetchAll();

// 4. Fetch Last 7 Days Revenue for Chart
$chartLabels = [];
$chartData   = [];

for ($i = 6; $i >= 0; $i--) {
    $dateLabel = date('Y-m-d', strtotime("-$i days"));
    $displayLabel = date('D, M j', strtotime("-$i days"));
    
    $stmtDaily = $db->prepare("SELECT SUM(total) FROM orders WHERE DATE(created_at) = ? AND status NOT IN ('cancelled', 'refunded')");
    $stmtDaily->execute([$dateLabel]);
    $dailyTotal = $stmtDaily->fetchColumn();
    
    $chartLabels[] = $displayLabel;
    $chartData[]   = $dailyTotal ? (float)$dailyTotal : 0;
}

// 5. Monthly Revenue Chart (Last 6 Months)
$monthLabels = [];
$monthData   = [];
for ($i = 5; $i >= 0; $i--) {
    $monthStart = date('Y-m-01', strtotime("-$i months"));
    $monthDisplay = date('M Y', strtotime("-$i months"));
    
    $stmtMonthly = $db->prepare("SELECT SUM(total) FROM orders WHERE created_at >= ? AND created_at < DATE_ADD(?, INTERVAL 1 MONTH) AND status NOT IN ('cancelled', 'refunded')");
    $stmtMonthly->execute([$monthStart, $monthStart]);
    $monthlyTotal = $stmtMonthly->fetchColumn();
    
    $monthLabels[] = $monthDisplay;
    $monthData[]   = $monthlyTotal ? (float)$monthlyTotal : 0;
}

$chartLabelsJson = json_encode($chartLabels);
$chartDataJson   = json_encode($chartData);
$monthLabelsJson = json_encode($monthLabels);
$monthDataJson   = json_encode($monthData);

$pageTitle = 'Executive Overview';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Include Chart.js for revenue chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="page-header">
    <div class="page-header-left">
        <h3>Dashboard</h3>
        <p>Store overview &amp; performance snapshot</p>
    </div>
    <a href="<?= url('admin/products/add.php') ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Add Product
    </a>
</div>


<div class="row g-4 mb-4">
    <!-- Revenue -->
    <div class="col-sm-6 col-xl-3">
        <div class="admin-card p-4 h-100 shadow-sm border-0 position-relative overflow-hidden">
            <div class="position-absolute top-0 end-0 p-3 opacity-10">
                <i class="fa-solid fa-indian-rupee-sign fa-4x text-primary"></i>
            </div>
            <h6 class="text-muted small fw-800 text-uppercase ls-2 mb-3">Gross Revenue</h6>
            <h2 class="fw-900 text-dark mb-1"><?= formatPrice($revenue) ?></h2>
            <div class="d-flex align-items-center gap-2 mt-2">
                <span class="badge-soft badge-soft-success px-2 py-1 rounded fs-9 fw-700">+12.5%</span>
                <span class="text-muted fs-9 fw-600">vs last month</span>
            </div>
        </div>
    </div>
    <!-- Orders -->
    <div class="col-sm-6 col-xl-3">
        <div class="admin-card p-4 h-100 shadow-sm border-0 position-relative overflow-hidden">
            <div class="position-absolute top-0 end-0 p-3 opacity-10">
                <i class="fa-solid fa-cart-shopping fa-4x text-success"></i>
            </div>
            <h6 class="text-muted small fw-800 text-uppercase ls-2 mb-3">Total Shipments</h6>
            <h2 class="fw-900 text-dark mb-1"><?= number_format($totalOrds) ?></h2>
            <div class="d-flex align-items-center gap-2 mt-2">
                <span class="badge-soft badge-soft-primary px-2 py-1 rounded fs-9 fw-700">AOV: <?= formatPrice($aov) ?></span>
            </div>
        </div>
    </div>
    <!-- Customers -->
    <div class="col-sm-6 col-xl-3">
        <div class="admin-card p-4 h-100 shadow-sm border-0 position-relative overflow-hidden">
            <div class="position-absolute top-0 end-0 p-3 opacity-10">
                <i class="fa-solid fa-users-viewfinder fa-4x text-info"></i>
            </div>
            <h6 class="text-muted small fw-800 text-uppercase ls-2 mb-3">Valued Clients</h6>
            <h2 class="fw-900 text-dark mb-1"><?= number_format($totalUsers) ?></h2>
            <div class="d-flex align-items-center gap-2 mt-2">
                <span class="badge-soft badge-soft-info px-2 py-1 rounded fs-9 fw-700">+<?= $recentCustomersCount ?> New</span>
                <span class="text-muted fs-9 fw-600">this month</span>
            </div>
        </div>
    </div>
    <!-- Inventory -->
    <div class="col-sm-6 col-xl-3">
        <div class="admin-card p-4 h-100 shadow-sm border-0 position-relative overflow-hidden">
            <div class="position-absolute top-0 end-0 p-3 opacity-10">
                <i class="fa-solid fa-gem fa-4x text-warning"></i>
            </div>
            <h6 class="text-muted small fw-800 text-uppercase ls-2 mb-3">Vault Items</h6>
            <h2 class="fw-900 text-dark mb-1"><?= number_format($totalProds) ?></h2>
            <div class="d-flex align-items-center gap-2 mt-2">
                <span class="badge-soft badge-soft-danger px-2 py-1 rounded fs-9 fw-700"><?= count($lowStock) ?> Low Stock</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Analytics Chart -->
    <div class="col-lg-8">
        <div class="admin-card p-4 h-100 shadow-sm border-0">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-900 text-dark mb-1 ls-1">Revenue Stream</h5>
                    <p class="text-muted small mb-0 fw-600">Performance across defined intervals</p>
                </div>
                <div class="btn-group btn-group-sm shadow-sm rounded-pill overflow-hidden border">
                    <button type="button" class="btn btn-white fw-700 active" id="btn7Days">7 Days</button>
                    <button type="button" class="btn btn-white fw-700" id="btn6Months">6 Months</button>
                </div>
            </div>
            <div style="height: 320px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Top Selling Masterpieces -->
    <div class="col-lg-4">
        <div class="admin-card h-100 d-flex flex-column shadow-sm border-0">
            <div class="p-4 border-bottom">
                <h5 class="fw-900 text-dark mb-0 ls-1">🔥 Top Sellers</h5>
            </div>
            
            <div class="p-4 flex-grow-1">
                <?php if(empty($topProducts)): ?>
                    <div class="text-center text-muted py-5 mt-4">
                        <i class="fa-solid fa-chart-line fa-3x mb-3 opacity-25"></i>
                        <p class="fw-600 ls-1">Awaiting data...</p>
                    </div>
                <?php else: ?>
                    <?php foreach($topProducts as $tp): 
                        $img = productThumb($tp['images']);
                    ?>
                        <div class="d-flex align-items-center gap-3 mb-4 last-child-mb-0">
                            <img src="<?= url($img) ?>" class="rounded bg-light border" style="width: 54px; height: 54px; object-fit: cover;">
                            <div class="flex-grow-1 text-truncate">
                                <a href="<?= url('product.php?slug=' . $tp['slug']) ?>" target="_blank" class="fw-800 text-dark text-decoration-none fs-8 text-truncate d-block ls-1 mb-1"><?= e($tp['name']) ?></a>
                                <div class="fs-9 text-muted fw-700 text-uppercase ls-1"><?= $tp['total_qty'] ?> Units</div>
                            </div>
                            <div class="fw-900 text-primary fs-8 ls-1"><?= formatPrice($tp['sale_price'] ?: $tp['price']) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="p-4 pt-0 mt-auto">
                <a href="<?= url('admin/products/index.php') ?>" class="btn btn-light w-100 fw-800 text-muted fs-8 text-uppercase ls-1 py-3 rounded-pill border">View All Products</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Low Stock Alert -->
    <div class="col-lg-4">
        <div class="admin-card h-100 shadow-sm border-0 bg-gold bg-opacity-10 shadow-gold">
            <div class="p-4 border-bottom border-gold border-opacity-25 d-flex justify-content-between align-items-center">
                <h5 class="fw-900 text-dark mb-0 ls-1">⚠️ Urgent Action</h5>
                <span class="badge-soft badge-soft-danger px-3 py-1 rounded-pill fw-800 fs-9"><?= count($lowStock) ?> items</span>
            </div>
            
            <div class="p-4">
                <?php if(empty($lowStock)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="fa-solid fa-circle-check fa-3x mb-3 text-success opacity-50"></i>
                        <p class="mb-0 fw-800 ls-1">Vault Is Full</p>
                    </div>
                <?php else: ?>
                    <?php foreach($lowStock as $ls): ?>
                        <div class="mb-3 p-3 bg-white rounded-3 shadow-none border border-gold border-opacity-25 d-flex justify-content-between align-items-center">
                            <div class="me-3 text-truncate">
                                <div class="fw-800 text-dark text-truncate fs-8 ls-1"><?= e($ls['name']) ?></div>
                                <div class="fs-9 text-muted fw-600 uppercase ls-1">SKU: <?= $ls['sku'] ?: 'N/A' ?></div>
                            </div>
                            <span class="badge-soft <?= $ls['stock'] <= 0 ? 'badge-soft-danger' : 'badge-soft-warning' ?> px-3 py-2 rounded-pill fw-900 fs-8">
                                <?= $ls['stock'] ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                    <a href="<?= url('admin/products/inventory.php') ?>" class="btn btn-dark w-100 fw-900 fs-8 text-uppercase ls-1 py-3 mt-2 rounded-pill shadow-gold">Restock Now</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="col-lg-8">
        <div class="admin-card h-100 shadow-sm border-0 overflow-hidden">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white">
                <h5 class="fw-900 text-dark mb-0 ls-1">📦 Order Velocity</h5>
                <a href="<?= url('admin/orders/index.php') ?>" class="text-decoration-none small fw-900 text-primary ls-1 text-uppercase fs-9">Full Ledger &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted fs-9 text-uppercase ls-2">
                        <tr>
                            <th class="py-3 px-4">Ledger Ref</th>
                            <th class="py-3 px-3">Client</th>
                            <th class="py-3 px-3">Timestamp</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-4 text-end">Grand Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentOrders)): ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted fw-600">No transactions recorded.</td></tr>
                        <?php else: ?>
                            <?php foreach($recentOrders as $o): ?>
                                <tr>
                                    <td class="px-4 py-3 fw-900 text-dark">#<?= e($o['order_number']) ?></td>
                                    <td class="px-3 py-3">
                                        <div class="fw-700 text-dark fs-8 text-truncate" style="max-width: 140px;"><?= e($o['ship_name']) ?></div>
                                    </td>
                                    <td class="px-3 py-3 text-muted fs-9 fw-600"><?= date('d M, h:i A', strtotime($o['created_at'])) ?></td>
                                    <td class="px-3 py-3">
                                        <?php
                                            $s = strtolower($o['status']);
                                            $b = 'badge-soft-secondary';
                                            if($s == 'pending') $b='badge-soft-warning';
                                            if($s == 'confirmed') $b='badge-soft-info';
                                            if($s == 'shipped') $b='badge-soft-primary';
                                            if($s == 'delivered') $b='badge-soft-success';
                                            if($s == 'cancelled') $b='badge-soft-danger';
                                        ?>
                                        <span class="badge-soft <?= $b ?> px-3 py-1 rounded-pill fw-800 text-uppercase ls-1" style="font-size: 0.65rem;"><?= $s ?></span>
                                    </td>
                                    <td class="px-4 py-3 fw-900 text-primary text-end fs-8 ls-1"><?= formatPrice($o['total']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
// Example Chart JS logic
// In real app, fetch this via AJAX or PHP arrays from DB for the last 7 days
ob_start();
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    const labels7Days = <?= $chartLabelsJson ?>;
    const data7Days   = <?= $chartDataJson ?>;
    const labels6Months = <?= $monthLabelsJson ?>;
    const data6Months   = <?= $monthDataJson ?>;
    
    let currentChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels7Days,
            datasets: [{
                label: 'Revenue (₹)',
                data: data7Days,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#0d6efd',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: ₹' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: '#64748b', font: { size: 11 } },
                    grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false }
                },
                x: {
                    ticks: { color: '#64748b', font: { size: 11 } },
                    grid: { display: false, drawBorder: false }
                }
            }
        }
    });

    // Toggle Logic
    const btn7 = document.getElementById('btn7Days');
    const btn6 = document.getElementById('btn6Months');

    btn7.addEventListener('click', () => {
        btn7.classList.add('active');
        btn6.classList.remove('active');
        currentChart.data.labels = labels7Days;
        currentChart.data.datasets[0].data = data7Days;
        currentChart.update();
    });

    btn6.addEventListener('click', () => {
        btn6.classList.add('active');
        btn7.classList.remove('active');
        currentChart.data.labels = labels6Months;
        currentChart.data.datasets[0].data = data6Months;
        currentChart.update();
    });
});
</script>
<?php
$extraAdminJs = ob_get_clean();
require_once __DIR__ . '/includes/footer.php'; 
?>
