@extends('admin.layout')

@section('title', 'Reports')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <div class="admin-banner">
                    <div>
                        <div class="brand">Performance Desk</div>
                        <h2 class="mb-0">Sales & Order Reports</h2>
                        <p class="lead mb-0" style="margin-top: 8px;">Track gross revenue, paid revenue, payment mix, and monthly order movement.</p>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-4 col-xl-2">
                        <div class="admin-section h-100">
                            <div class="muted">Gross Revenue</div>
                            <h3 class="mt-2 mb-0">₹{{ number_format($totals['gross_revenue'], 2) }}</h3>
                        </div>
                    </div>
                    <div class="col-md-4 col-xl-2">
                        <div class="admin-section h-100">
                            <div class="muted">Paid Revenue</div>
                            <h3 class="mt-2 mb-0">₹{{ number_format($totals['paid_revenue'], 2) }}</h3>
                        </div>
                    </div>
                    <div class="col-md-4 col-xl-2">
                        <div class="admin-section h-100">
                            <div class="muted">Refunded</div>
                            <h3 class="mt-2 mb-0">₹{{ number_format($totals['refunded_total'], 2) }}</h3>
                        </div>
                    </div>
                    <div class="col-md-4 col-xl-2">
                        <div class="admin-section h-100">
                            <div class="muted">Total Orders</div>
                            <h3 class="mt-2 mb-0">{{ $totals['total_orders'] }}</h3>
                        </div>
                    </div>
                    <div class="col-md-4 col-xl-2">
                        <div class="admin-section h-100">
                            <div class="muted">Prepaid</div>
                            <h3 class="mt-2 mb-0">{{ $totals['prepaid_orders'] }}</h3>
                        </div>
                    </div>
                    <div class="col-md-4 col-xl-2">
                        <div class="admin-section h-100">
                            <div class="muted">COD</div>
                            <h3 class="mt-2 mb-0">{{ $totals['cod_orders'] }}</h3>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <section class="admin-section">
                            <h3 class="mb-3">Monthly Sales</h3>
                            <div class="table-wrap">
                                <table class="admin-data-table">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th>Orders</th>
                                            <th>Paid Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($monthlySales as $row)
                                            <tr>
                                                <td>{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $row->month_key)->format('M Y') }}</td>
                                                <td>{{ $row->orders_count }}</td>
                                                <td>₹{{ number_format((float) $row->paid_revenue, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                    <div class="col-lg-6">
                        <section class="admin-section mb-4">
                            <h3 class="mb-3">Payment Mix</h3>
                            <div class="table-wrap">
                                <table class="admin-data-table">
                                    <thead>
                                        <tr>
                                            <th>Method</th>
                                            <th>Orders</th>
                                            <th>Gross</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($paymentBreakdown as $row)
                                            <tr>
                                                <td>{{ strtoupper($row->payment_method) }}</td>
                                                <td>{{ $row->orders_count }}</td>
                                                <td>₹{{ number_format((float) $row->gross_total, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </section>
                        <section class="admin-section">
                            <h3 class="mb-3">Order Status Split</h3>
                            <div class="table-wrap">
                                <table class="admin-data-table">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th>Orders</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($statusBreakdown as $row)
                                            <tr>
                                                <td>{{ ucfirst($row->status) }}</td>
                                                <td>{{ $row->orders_count }}</td>
                                            </tr>
                                        @endforeach
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
