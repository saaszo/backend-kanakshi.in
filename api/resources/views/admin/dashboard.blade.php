@extends('admin.layout')

@section('title', 'Executive Analytics Dashboard')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')

        <main class="admin-main">
            <div class="admin-shell-grid">
                <!-- Top Header Banner -->
                <div class="admin-banner">
                    <div>
                        <div class="brand">Executive Workspace</div>
                        <h2>Store Analytics & Control Center</h2>
                        <p class="lead" style="margin-top: 4px;">Real-time performance indicators, transaction records, shipping bottlenecks, and catalog metrics.</p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-primary">
                            <i class="bi bi-cart-check"></i>
                            <span>Fulfillment Center</span>
                        </a>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-box-seam"></i>
                            <span>Catalog Manager</span>
                        </a>
                    </div>
                </div>

                @if (session('status'))
                    <div class="p-3 mb-4" style="background: #e8f7ee; border: 1px solid #c2ebd1; color: #0d532b; font-weight: 600;">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                    </div>
                @endif

                @if ($errors->has('backup_file'))
                    <div class="p-3 mb-4" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; font-weight: 600;">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i> {{ $errors->first('backup_file') }}
                    </div>
                @endif

                <!-- KPI Metric Stat Tiles -->
                <div class="metrics-grid mb-4">
                    <div class="admin-stat">
                        <small>Total Revenue</small>
                        <strong style="color: #16a34a;">₹{{ number_format($stats['total_sales'], 2) }}</strong>
                        <span>Total paid orders</span>
                    </div>
                    <div class="admin-stat">
                        <small>Transactions</small>
                        <strong style="color: #0f172a;">{{ $stats['orders_count'] }}</strong>
                        <span>Total checkouts placed</span>
                    </div>
                    <div class="admin-stat">
                        <small>Pending Orders</small>
                        <strong style="color: #d97706;">{{ $stats['pending_orders'] }}</strong>
                        <span>Awaiting processing</span>
                    </div>
                    <div class="admin-stat">
                        <small>Active Shipments</small>
                        <strong style="color: #2563eb;">{{ $stats['shipped_orders'] }}</strong>
                        <span>In-transit with courier</span>
                    </div>
                    <div class="admin-stat">
                        <small>Delivered Orders</small>
                        <strong style="color: #16a34a;">{{ $stats['completed_orders'] }}</strong>
                        <span>Completed successfully</span>
                    </div>
                </div>

                <!-- Visual Charts Section -->
                <div class="row g-4 mb-4">
                    <!-- Left: Revenue Trend -->
                    <div class="col-lg-8">
                        <section class="admin-section h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h3 class="mb-0">Revenue Stream Trend</h3>
                                    <p class="muted mb-0" style="font-size: 13px;">Daily sales totals collected over the past 30 days.</p>
                                </div>
                                <span class="badge bg-success">
                                    <i class="bi bi-graph-up-arrow me-1"></i> Live Metric
                                </span>
                            </div>
                            <div style="position: relative; height: 300px; width: 100%;">
                                <canvas id="revenueTrendChart"></canvas>
                            </div>
                        </section>
                    </div>

                    <!-- Right: Category Shares -->
                    <div class="col-lg-4">
                        <section class="admin-section h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h3 class="mb-0">Category Breakdown</h3>
                                    <p class="muted mb-0" style="font-size: 13px;">Proportional share of item units sold.</p>
                                </div>
                            </div>
                            <div style="position: relative; height: 300px; width: 100%; display: flex; align-items: center; justify-content: center;">
                                <canvas id="categorySharesChart"></canvas>
                            </div>
                        </section>
                    </div>
                </div>

                <!-- Fulfillment Alerts & Best Sellers Grid -->
                <div class="row g-4 mb-4">
                    <!-- Left: Needs Attention / Pending Orders -->
                    <div class="col-lg-7">
                        <section class="admin-section h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h3 class="mb-0 text-warning d-flex align-items-center gap-2">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        <span>Fulfillment Queue</span>
                                    </h3>
                                    <p class="muted mb-0" style="font-size: 13px;">Incoming customer orders awaiting packing and shipping assignment.</p>
                                </div>
                                <span class="badge bg-warning text-dark">
                                    {{ $needsAttention->count() }} Orders Pending
                                </span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer Name</th>
                                            <th>Items</th>
                                            <th class="text-end">Grand Total</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($needsAttention as $order)
                                            <tr>
                                                <td style="font-weight: 700; color: #0f172a;">{{ $order->order_number }}</td>
                                                <td>
                                                    <strong>{{ $order->ship_name }}</strong>
                                                    <div style="font-size: 12px; color: #64748b;">{{ $order->ship_city }}, {{ $order->ship_state }}</div>
                                                </td>
                                                <td style="color: #64748b;">{{ $order->items->count() }} {{ Str::plural('piece', $order->items->count()) }}</td>
                                                <td class="text-end font-monospace" style="color: #16a34a; font-weight: 700;">
                                                    ₹{{ number_format($order->total_amount, 2) }}
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-primary py-1 px-3" style="font-size: 12px;">
                                                        Fulfill →
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4" style="color: #64748b;">
                                                    <i class="bi bi-shield-check text-success" style="font-size: 28px; display: block; margin-bottom: 6px;"></i>
                                                    Fulfillment Queue Clear. All orders are up to date!
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>

                    <!-- Right: Leaderboard Top Products -->
                    <div class="col-lg-5">
                        <section class="admin-section h-100">
                            <h3 class="mb-1 d-flex align-items-center gap-2">
                                <i class="bi bi-trophy-fill" style="color: #c59b27;"></i>
                                <span>Leaderboard: Top Jewellery</span>
                            </h3>
                            <p class="muted mb-3" style="font-size: 13px;">Products generating the highest transaction and shipping volume.</p>
                            
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Item Name</th>
                                            <th class="text-center">Qty Sold</th>
                                            <th class="text-end">Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($topProducts as $top)
                                            <tr>
                                                <td style="font-weight: 600; color: #0f172a; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    {{ $top->name }}
                                                </td>
                                                <td class="text-center font-monospace" style="font-weight: 700;">{{ $top->total_qty }}</td>
                                                <td class="text-end font-monospace" style="color: #16a34a; font-weight: 700;">
                                                    ₹{{ number_format($top->sales_amount, 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-4" style="color: #64748b;">
                                                    No sales records registered yet.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                </div>

                <!-- Quick Utilities Cards Grid -->
                <section class="admin-section mb-4">
                    <h3 class="mb-1">Quick Administration Utilities</h3>
                    <p class="muted mb-3" style="font-size: 13px;">Jump directly into administrative portal tools and configurations.</p>

                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px;">
                        <a href="{{ route('admin.settings.edit') }}" class="btn btn-outline-secondary d-flex flex-column align-items-center justify-content-center p-3 text-center" style="gap: 8px;">
                            <i class="bi bi-gear" style="font-size: 20px; color: #2563eb;"></i>
                            <span style="font-size: 13px;">Store Settings</span>
                        </a>
                        <a href="{{ route('admin.homepage-sections.index') }}" class="btn btn-outline-secondary d-flex flex-column align-items-center justify-content-center p-3 text-center" style="gap: 8px;">
                            <i class="bi bi-images" style="font-size: 20px; color: #2563eb;"></i>
                            <span style="font-size: 13px;">Homepage Layout</span>
                        </a>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary d-flex flex-column align-items-center justify-content-center p-3 text-center" style="gap: 8px;">
                            <i class="bi bi-tags" style="font-size: 20px; color: #2563eb;"></i>
                            <span style="font-size: 13px;">Categories</span>
                        </a>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary d-flex flex-column align-items-center justify-content-center p-3 text-center" style="gap: 8px;">
                            <i class="bi bi-box-seam" style="font-size: 20px; color: #2563eb;"></i>
                            <span style="font-size: 13px;">Products</span>
                        </a>
                        <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary d-flex flex-column align-items-center justify-content-center p-3 text-center" style="gap: 8px;">
                            <i class="bi bi-percent" style="font-size: 20px; color: #2563eb;"></i>
                            <span style="font-size: 13px;">Coupons & Offers</span>
                        </a>
                        <a href="{{ route('admin.social-links.index') }}" class="btn btn-outline-secondary d-flex flex-column align-items-center justify-content-center p-3 text-center" style="gap: 8px;">
                            <i class="bi bi-share" style="font-size: 20px; color: #2563eb;"></i>
                            <span style="font-size: 13px;">Social Links</span>
                        </a>
                    </div>
                </section>

                <!-- Bottom Row: Recovery Vault & Active Operator Profile -->
                <div class="row g-4 mb-4">
                    <!-- Recovery Vault -->
                    <div class="col-lg-6">
                        <section class="admin-section h-100">
                            <h3 class="mb-1 d-flex align-items-center gap-2">
                                <i class="bi bi-shield-lock" style="color: #2563eb;"></i>
                                <span>System Backup & Recovery Vault</span>
                            </h3>
                            <p class="muted mb-3" style="font-size: 13px;">Download full database snapshot, product assets, and layout configurations or restore an archive.</p>

                            <div class="d-flex flex-column gap-3">
                                <div>
                                    <a href="{{ route('admin.backups.download') }}" class="btn btn-primary">
                                        <i class="bi bi-cloud-arrow-down me-1"></i> Download Full Backup (.ZIP)
                                    </a>
                                </div>

                                <hr style="border-color: var(--border); margin: 6px 0;" />

                                <form method="POST" action="{{ route('admin.backups.restore') }}" enctype="multipart/form-data" data-confirm="Restoring a backup archive will restore all database records and product configurations. An automatic safety snapshot will be created first. Do you want to proceed?" data-confirm-title="Confirm System Restore" data-confirm-btn="Yes, Restore System" data-confirm-class="btn-primary">
                                    @csrf
                                    <label class="form-label" style="font-weight: 600; font-size: 13px;">Restore System State</label>
                                    <div class="d-flex gap-2">
                                        <input type="file" name="backup_file" accept=".zip,application/zip" class="form-control" required style="font-size: 13px;" />
                                        <button type="submit" class="btn btn-outline-secondary" style="white-space: nowrap;">
                                            <i class="bi bi-arrow-clockwise"></i> Restore
                                        </button>
                                    </div>
                                    <small class="muted" style="font-size: 12px; display: block; margin-top: 6px;">System creates an automatic safety restore point before applying the archive.</small>
                                </form>
                            </div>
                        </section>
                    </div>

                    <!-- Active Operator Profile -->
                    <div class="col-lg-6">
                        <section class="admin-section h-100">
                            <h3 class="mb-1 d-flex align-items-center gap-2">
                                <i class="bi bi-person-badge" style="color: #2563eb;"></i>
                                <span>Active Operator Session</span>
                            </h3>
                            <p class="muted mb-3" style="font-size: 13px;">Verified administrator credentials and active security level.</p>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <tbody>
                                        <tr>
                                            <th style="width: 140px; background: #f8fafc; color: #475569; font-size: 12.5px;">Operator Name</th>
                                            <td style="font-weight: 700; color: #0f172a;">{{ auth()->user()->name }}</td>
                                        </tr>
                                        <tr>
                                            <th style="background: #f8fafc; color: #475569; font-size: 12.5px;">Email Address</th>
                                            <td style="color: #0f172a;">{{ auth()->user()->email }}</td>
                                        </tr>
                                        <tr>
                                            <th style="background: #f8fafc; color: #475569; font-size: 12.5px;">Access Role</th>
                                            <td>
                                                <span class="badge bg-primary" style="text-transform: capitalize;">
                                                    {{ str_replace('_', ' ', auth()->user()->role) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="background: #f8fafc; color: #475569; font-size: 12.5px;">Account Status</th>
                                            <td>
                                                <span class="badge bg-success">
                                                    {{ strtoupper(auth()->user()->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
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
            const canvasLine = document.getElementById('revenueTrendChart');
            if (canvasLine) {
                const ctxLine = canvasLine.getContext('2d');
                const gradient = ctxLine.createLinearGradient(0, 0, 0, 280);
                gradient.addColorStop(0, 'rgba(37, 99, 235, 0.25)');
                gradient.addColorStop(1, 'rgba(37, 99, 235, 0.0)');

                new Chart(ctxLine, {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Revenue (₹)',
                            data: chartData.revenue,
                            borderColor: '#2563eb',
                            borderWidth: 2.5,
                            pointBackgroundColor: '#2563eb',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            tension: 0.3,
                            fill: true,
                            backgroundColor: gradient
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#090d16',
                                titleColor: '#ffffff',
                                bodyColor: '#4ade80',
                                borderWidth: 1,
                                borderColor: '#1e293b',
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
                                grid: { color: '#e2e8f0' },
                                ticks: { color: '#64748b', font: { size: 11, family: 'Inter' } }
                            },
                            y: {
                                grid: { color: '#e2e8f0' },
                                ticks: {
                                    color: '#64748b',
                                    font: { size: 11, family: 'Inter' },
                                    callback: function(value) { return '₹' + value; }
                                }
                            }
                        }
                    }
                });
            }

            // --- 2. Doughnut Chart: Category Shares ---
            const canvasDoughnut = document.getElementById('categorySharesChart');
            if (canvasDoughnut) {
                const ctxDoughnut = canvasDoughnut.getContext('2d');
                new Chart(ctxDoughnut, {
                    type: 'doughnut',
                    data: {
                        labels: chartData.catLabels,
                        datasets: [{
                            data: chartData.catValues,
                            backgroundColor: [
                                '#2563eb', '#7c3aed', '#16a34a', '#d97706', '#dc2626', '#64748b'
                            ],
                            borderColor: '#ffffff',
                            borderWidth: 2,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: '#334155',
                                    padding: 12,
                                    font: { size: 11, family: 'Inter', weight: '600' }
                                }
                            },
                            tooltip: {
                                backgroundColor: '#090d16',
                                titleColor: '#ffffff',
                                bodyColor: '#93c5fd',
                                borderWidth: 1,
                                borderColor: '#1e293b',
                                padding: 10
                            }
                        },
                        cutout: '65%'
                    }
                });
            }
        });
    </script>
@endpush
