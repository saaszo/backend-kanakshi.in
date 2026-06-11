@extends('admin.layout')

@section('title', 'Orders Management')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <div class="admin-banner">
                    <div>
                        <div class="brand">Sales & Fulfillments</div>
                        <h2>Orders Management</h2>
                        <p class="lead" style="margin-top:8px;">Track, manage, and process customer and guest orders. Review details, assign courier logistics, and publish live milestones.</p>
                    </div>
                </div>

                @if (session('status'))
                    <div class="message mb-4" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #34d399; padding: 15px; border-radius: var(--radius-md); font-weight: 500;">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                    </div>
                @endif

                <div class="metrics-grid mb-4">
                    <div class="admin-stat">
                        <small>Total Volume</small>
                        <strong>{{ $stats['total'] }}</strong>
                        <span>All orders received</span>
                    </div>
                    <div class="admin-stat">
                        <small>Pending Validation</small>
                        <strong style="color: var(--warning);">{{ $stats['pending'] }}</strong>
                        <span>Awaiting verification</span>
                    </div>
                    <div class="admin-stat">
                        <small>Processing Items</small>
                        <strong style="color: var(--primary);">{{ $stats['processing'] }}</strong>
                        <span>Being picked & packed</span>
                    </div>
                    <div class="admin-stat">
                        <small>Dispatched Route</small>
                        <strong style="color: var(--purple);">{{ $stats['shipped'] }}</strong>
                        <span>En route to destinations</span>
                    </div>
                    <div class="admin-stat">
                        <small>Completed Deliveries</small>
                        <strong style="color: var(--success);">{{ $stats['delivered'] }}</strong>
                        <span>Delivered successfully</span>
                    </div>
                </div>

                <section class="admin-section">
                    <div class="admin-toolbar mb-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h3>Customer Orders</h3>
                            <p class="muted mb-0">Search by ID, name, email, or telephone. Filter by order status or payment state.</p>
                        </div>
                        <form method="GET" action="{{ route('admin.orders.index') }}" class="admin-toolbar-filters d-flex align-items-center gap-2 flex-wrap">
                            <input type="search" name="q" placeholder="Order ID, customer name..." value="{{ $filters['q'] }}" style="width: 240px;" />
                            
                            <select name="status">
                                <option value="">All Statuses</option>
                                <option value="pending" @selected($filters['status'] === 'pending')>Pending</option>
                                <option value="confirmed" @selected($filters['status'] === 'confirmed')>Confirmed</option>
                                <option value="processing" @selected($filters['status'] === 'processing')>Processing</option>
                                <option value="shipped" @selected($filters['status'] === 'shipped')>Shipped</option>
                                <option value="delivered" @selected($filters['status'] === 'delivered')>Delivered</option>
                                <option value="cancelled" @selected($filters['status'] === 'cancelled')>Cancelled</option>
                                <option value="refunded" @selected($filters['status'] === 'refunded')>Refunded</option>
                            </select>

                            <select name="payment_status">
                                <option value="">All Payment States</option>
                                <option value="pending" @selected($filters['payment_status'] === 'pending')>Pending</option>
                                <option value="paid" @selected($filters['payment_status'] === 'paid')>Paid</option>
                                <option value="failed" @selected($filters['payment_status'] === 'failed')>Failed</option>
                                <option value="refunded" @selected($filters['payment_status'] === 'refunded')>Refunded</option>
                            </select>

                            <button class="button small" type="submit">Filter</button>
                            @if($filters['q'] || $filters['status'] || $filters['payment_status'])
                                <a href="{{ route('admin.orders.index') }}" class="button secondary small">Reset</a>
                            @endif
                        </form>
                    </div>

                    <div class="table-wrap">
                        <table class="admin-data-table">
                            <thead>
                                <tr>
                                    <th>Order Details</th>
                                    <th>Customer Information</th>
                                    <th>Method & Payment</th>
                                    <th>Ordered On</th>
                                    <th>Status</th>
                                    <th class="text-end">Total Amount</th>
                                    <th style="width: 150px;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr>
                                        <td>
                                            <div style="font-weight: 700; color: #fff; letter-spacing: -0.01em;">
                                                {{ $order->order_number }}
                                            </div>
                                            <small class="muted">{{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}</small>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: var(--text);">{{ $order->ship_name }}</div>
                                            <div style="font-size: 12px; color: var(--text-soft);" class="d-flex align-items-center gap-1">
                                                <i class="bi bi-envelope" style="font-size: 11px;"></i> {{ $order->ship_email }}
                                            </div>
                                            <div style="font-size: 12px; color: var(--text-soft);" class="d-flex align-items-center gap-1">
                                                <i class="bi bi-telephone" style="font-size: 11px;"></i> {{ $order->ship_phone }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                <span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--text);">
                                                    {{ $order->payment_method }}
                                                </span>
                                                <span class="admin-badge compact {{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'pending' ? 'warning' : 'danger') }}" style="font-size: 10px; width: fit-content; padding: 2px 6px;">
                                                    {{ ucfirst($order->payment_status) }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="muted">
                                            {{ $order->created_at->format('M d, Y') }}
                                            <div style="font-size: 11px;">{{ $order->created_at->format('h:i A') }}</div>
                                        </td>
                                        <td>
                                            @php
                                                $statusClass = match($order->status) {
                                                    'pending' => 'warning',
                                                    'confirmed', 'processing' => 'primary',
                                                    'shipped' => 'purple',
                                                    'delivered' => 'success',
                                                    'cancelled', 'refunded' => 'danger',
                                                    default => 'muted'
                                                };
                                            @endphp
                                            <span class="admin-badge {{ $statusClass }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end" style="font-weight: 700; color: #fff;">
                                            ₹{{ number_format($order->total_amount, 2) }}
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center">
                                                <a class="button secondary small" href="{{ route('admin.orders.show', $order) }}" style="padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px; font-size: 12px;">
                                                    <i class="bi bi-box-seam" style="font-size: 13px;"></i>
                                                    <span>Fulfill</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 muted">
                                            <i class="bi bi-inbox" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                                            No orders found matching the filter coordinates.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($orders->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <span class="muted" style="font-size: 13px;">
                                Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} Orders
                            </span>
                            <div class="pagination-wrapper">
                                {{ $orders->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                </section>
            </div>
        </main>
    </div>
@endsection
