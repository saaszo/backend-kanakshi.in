@extends('admin.layout')

@section('title', 'Analytics Dashboard')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')

        <main class="admin-main">
            <div class="admin-shell-grid">
                <!-- Top Header -->
                <div class="admin-banner">
                    <div>
                        <div class="brand">Executive Workspace</div>
                        <h2>Store Analytics & Control</h2>
                        <p class="lead" style="margin-top:8px;">Real-time performance indicators, transaction records, shipping bottlenecks, and catalog performance.</p>
                    </div>
                    <div class="toolbar-actions">
                        <a href="{{ route('admin.orders.index') }}" class="button small">
                            <i class="bi bi-cart-check"></i>
                            <span>Fulfillment Center</span>
                        </a>
                        <a href="{{ route('admin.products.index') }}" class="button secondary small">
                            <i class="bi bi-box-seam"></i>
                            <span>Catalog Manager</span>
                        </a>
                    </div>
                </div>

                <!-- Metrics Grid -->
                <div class="metrics-grid mb-4">
                    <article class="metric-card" style="position: relative; overflow: hidden; border-color: rgba(16, 185, 129, 0.2); background: radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent);">
                        <small>Total Revenue</small>
                        <strong style="color: var(--success);">₹{{ number_format($stats['total_sales'], 2) }}</strong>
                        <span>Sum of paid invoices</span>
                    </div>
                    <article class="metric-card" style="position: relative; overflow: hidden;">
                        <small>Transactions</small>
                        <strong>{{ $stats['orders_count'] }}</strong>
                        <span>Total customer checkouts</span>
                    </div>
                    <article class="metric-card" style="position: relative; overflow: hidden; border-color: rgba(245, 158, 11, 0.2); background: radial-gradient(circle at top right, rgba(245, 158, 11, 0.08), transparent);">
                        <small>Pending Validation</small>
                        <strong style="color: var(--warning);">{{ $stats['pending_orders'] }}</strong>
                        <span>Orders awaiting approval</span>
                    </div>
                    <article class="metric-card" style="position: relative; overflow: hidden; border-color: rgba(99, 102, 241, 0.2); background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.08), transparent);">
                        <small>Active Shipments</small>
                        <strong style="color: var(--primary);">{{ $stats['shipped_orders'] }}</strong>
                        <span>Orders currently in transit</span>
                    </div>
                    <article class="metric-card" style="position: relative; overflow: hidden; border-color: rgba(16, 185, 129, 0.2);">
                        <small>Deliveries Completed</small>
                        <strong style="color: var(--success);">{{ $stats['completed_orders'] }}</strong>
                        <span>Delivered and archived</span>
                    </div>
                </div>

                <!-- High-Fidelity Charts Splitting Row -->
                <div class="row g-4 mb-4">
                    <!-- Left: Revenue Trend -->
                    <div class="col-lg-8">
                        <section class="admin-section h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h3 class="mb-0">Revenue Stream Trend</h3>
                                    <p class="muted mb-0" style="font-size: 13px;">Daily sales totals collected over the past 30 days.</p>
                                </div>
                                <div class="admin-badge success" style="font-size: 11px;">
                                    <i class="bi bi-graph-up-arrow me-1"></i> Live
                                </div>
                            </div>
                            <div style="position: relative; height: 320px; width: 100%;">
                                <canvas id="revenueTrendChart"></canvas>
                            </div>
                        </section>
                    </div>

                    <!-- Right: Category Shares -->
                    <div class="col-lg-4">
                        <section class="admin-section h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h3 class="mb-0">Product Category split</h3>
                                    <p class="muted mb-0" style="font-size: 13px;">Proportional share of item units sold.</p>
                                </div>
                            </div>
                            <div style="position: relative; height: 320px; width: 100%; display: flex; align-items: center; justify-content: center;">
                                <canvas id="categorySharesChart"></canvas>
                            </div>
                        </section>
                    </div>
                </div>

                <!-- Actionable Alert & Snapshot Splitting Row -->
                <div class="row g-4 mb-4">
                    <!-- Needs Attention: Pending Shipments -->
                    <div class="col-lg-7">
                        <section class="admin-section h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h3 class="mb-0 text-warning d-flex align-items-center gap-2">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        <span>Fulfillment Alerts</span>
                                    </h3>
                                    <p class="muted mb-0" style="font-size: 13px;">Incoming customer orders awaiting invoice verification and shipping partners assignment.</p>
                                </div>
                                <span class="admin-badge warning" style="font-size: 11px;">
                                    {{ $needsAttention->count() }} Urgent
                                </span>
                            </div>

                            <div class="table-wrap" style="margin-top: 15px;">
                                <table class="admin-data-table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer Name</th>
                                            <th>Items</th>
                                            <th class="text-end">Grand Total</th>
                                            <th style="width: 100px;" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($needsAttention as $order)
                                            <tr>
                                                <td style="font-weight: 700; color: #fff;">{{ $order->order_number }}</td>
                                                <td>{{ $order->ship_name }}</td>
                                                <td class="muted">{{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}</td>
                                                <td class="text-end font-monospace" style="color: var(--success); font-weight: 700;">
                                                    ₹{{ number_format($order->total_amount, 2) }}
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.orders.show', $order) }}" class="button primary small py-1 px-3" style="font-size: 11px;">
                                                        Fulfill
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 muted">
                                                    <i class="bi bi-shield-check-fill text-success" style="font-size: 24px; display: block; margin-bottom: 6px;"></i>
                                                    Fulfillment Queue Clear. Excellent work!
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>

                    <!-- Top Selling Products -->
                    <div class="col-lg-5">
                        <section class="admin-section h-100">
                            <h3 class="mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-trophy" style="color: var(--warning);"></i>
                                <span>Leaderboard: Top Products</span>
                            </h3>
                            <p class="muted" style="font-size: 13px; margin-bottom: 15px;">Products generating the highest transaction and shipping volume.</p>
                            
                            <div class="table-wrap">
                                <table class="admin-data-table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Item Name</th>
                                            <th class="text-center">Qty Sold</th>
                                            <th class="text-end">Revenue Generated</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($topProducts as $top)
                                            <tr>
                                                <td style="font-weight: 600; color: #fff; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    {{ $top->name }}
                                                </td>
                                                <td class="text-center font-monospace" style="font-weight: 700;">{{ $top->total_qty }}</td>
                                                <td class="text-end font-monospace" style="color: var(--success); font-weight: 700;">
                                                    ₹{{ number_format($top->sales_amount, 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-4 muted">
                                                    No transaction history available yet.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                </div>

                <!-- Admin Snapshot & Modules Split -->
                <div class="split-grid" style="margin-top: 24px;">
                    <section class="dashboard-table-card">
                        <div class="dashboard-table-head">
                            <div>
                                <h3>Quick Utilities</h3>
                                <p class="muted" style="margin:8px 0 0;">Jump directly into administrative portal tools.</p>
                            </div>
                        </div>
                        <div style="padding: 0 22px 22px;">
                            <div class="button-row">
                                <a href="{{ route('admin.settings.edit') }}" class="button secondary small"><i class="bi bi-gear"></i><span>Store Settings</span></a>
                                <a href="{{ route('admin.homepage-sections.index') }}" class="button secondary small"><i class="bi bi-images"></i><span>Homepage Sections</span></a>
                                <a href="{{ route('admin.categories.index') }}" class="button secondary small"><i class="bi bi-tags"></i><span>Categories</span></a>
                                <a href="{{ route('admin.products.index') }}" class="button secondary small"><i class="bi bi-box-seam"></i><span>Products</span></a>
                                <a href="{{ route('admin.menu-items.index') }}" class="button secondary small"><i class="bi bi-menu-button-wide"></i><span>Menus</span></a>
                                <a href="{{ route('admin.social-links.index') }}" class="button secondary small"><i class="bi bi-share"></i><span>Social Links</span></a>
                            </div>
                        </div>
                    </section>

                    <section class="dashboard-table-card">
                        <div class="dashboard-table-head">
                            <div>
                                <h3>Active Operator Profile</h3>
                                <p class="muted" style="margin:8px 0 0;">Current verified session particulars.</p>
                            </div>
                        </div>
                        <div class="table-wrap" style="border:none; border-top:1px solid var(--border); border-radius:0;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Operator</th>
                                        <th>Email Address</th>
                                        <th>Access Role</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="color:#fff; font-weight:700;">{{ auth()->user()->name }}</td>
                                        <td>{{ auth()->user()->email }}</td>
                                        <td><span class="pill" style="text-transform: capitalize;">{{ str_replace('_', ' ', auth()->user()->role) }}</span></td>
                                        <td><span class="pill" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.25);">{{ auth()->user()->status }}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

            </div>
        </main>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const chartData = @json($chartData);
            
            // --- 1. Line Chart: Revenue Streams ---
            const ctxLine = document.getElementById('revenueTrendChart').getContext('2d');
            
            // Build linear gradient for Line chart fill
            const purpleGradient = ctxLine.createLinearGradient(0, 0, 0, 300);
            purpleGradient.addColorStop(0, 'rgba(99, 102, 241, 0.35)');
            purpleGradient.addColorStop(1, 'rgba(99, 102, 241, 0.0)');

            new Chart(ctxLine, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Revenue (₹)',
                            data: chartData.revenue,
                            borderColor: '#6366f1',
                            borderWidth: 3,
                            pointBackgroundColor: '#6366f1',
                            pointBorderColor: 'rgba(255, 255, 255, 0.8)',
                            pointBorderWidth: 1.5,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            tension: 0.35,
                            fill: true,
                            backgroundColor: purpleGradient
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(11, 15, 25, 0.85)',
                            titleColor: '#fff',
                            bodyColor: '#34d399',
                            borderColor: 'rgba(255, 255, 255, 0.08)',
                            borderWidth: 1,
                            padding: 10,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return 'Revenue: ₹' + context.formattedValue;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.03)'
                            },
                            ticks: {
                                color: '#94a3b8',
                                font: {
                                    size: 11,
                                    family: 'Inter'
                                }
                            }
                        },
                        y: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.04)'
                            },
                            ticks: {
                                color: '#94a3b8',
                                font: {
                                    size: 11,
                                    family: 'Inter'
                                },
                                callback: function(value) {
                                    return '₹' + value;
                                }
                            }
                        }
                    }
                }
            });

            // --- 2. Doughnut Chart: Category Shares ---
            const ctxDoughnut = document.getElementById('categorySharesChart').getContext('2d');
            
            new Chart(ctxDoughnut, {
                type: 'doughnut',
                data: {
                    labels: chartData.catLabels,
                    datasets: [{
                        data: chartData.catValues,
                        backgroundColor: [
                            'rgba(99, 102, 241, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(148, 163, 184, 0.8)'
                        ],
                        borderColor: '#0f1624',
                        borderWidth: 2,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#e2e8f0',
                                padding: 15,
                                font: {
                                    size: 11,
                                    family: 'Inter',
                                    weight: '500'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(11, 15, 25, 0.85)',
                            titleColor: '#fff',
                            bodyColor: '#a5b4fc',
                            borderColor: 'rgba(255, 255, 255, 0.08)',
                            borderWidth: 1,
                            padding: 10
                        }
                    },
                    cutout: '68%'
                }
            });
        });
    </script>
@endpush
