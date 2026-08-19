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
                        <p class="lead" style="margin-top: 4px;">Track, manage, and process customer orders. Review items, assign courier logistics, and update live milestones.</p>
                    </div>
                </div>

                @if (session('status'))
                    <div class="p-3 mb-4" style="background: #e8f7ee; border: 1px solid #c2ebd1; color: #0d532b; font-weight: 600;">
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
                        <small>Pending Orders</small>
                        <strong style="color: #d97706;">{{ $stats['pending'] }}</strong>
                        <span>Awaiting verification</span>
                    </div>
                    <div class="admin-stat">
                        <small>Processing</small>
                        <strong style="color: #2563eb;">{{ $stats['processing'] }}</strong>
                        <span>Being picked & packed</span>
                    </div>
                    <div class="admin-stat">
                        <small>Dispatched Route</small>
                        <strong style="color: #7c3aed;">{{ $stats['shipped'] }}</strong>
                        <span>In-transit to customer</span>
                    </div>
                    <div class="admin-stat">
                        <small>Deliveries Completed</small>
                        <strong style="color: #16a34a;">{{ $stats['delivered'] }}</strong>
                        <span>Delivered successfully</span>
                    </div>
                </div>

                <section class="admin-section">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                        <div>
                            <h3 class="mb-1">Customer Orders</h3>
                            <p class="muted mb-0" style="font-size: 13px;">Filter by order ID, customer name, status or payment.</p>
                        </div>
                        <form method="GET" action="{{ route('admin.orders.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
                            <input type="search" name="q" placeholder="Order ID, customer name..." value="{{ $filters['q'] }}" style="width: 220px;" />
                            
                            <select name="status" style="width: 150px;">
                                <option value="">All Statuses</option>
                                <option value="pending" @selected($filters['status'] === 'pending')>Pending</option>
                                <option value="confirmed" @selected($filters['status'] === 'confirmed')>Confirmed</option>
                                <option value="processing" @selected($filters['status'] === 'processing')>Processing</option>
                                <option value="shipped" @selected($filters['status'] === 'shipped')>Shipped</option>
                                <option value="delivered" @selected($filters['status'] === 'delivered')>Delivered</option>
                                <option value="cancelled" @selected($filters['status'] === 'cancelled')>Cancelled</option>
                                <option value="refunded" @selected($filters['status'] === 'refunded')>Refunded</option>
                            </select>

                            <select name="payment_status" style="width: 150px;">
                                <option value="">All Payment States</option>
                                <option value="pending" @selected($filters['payment_status'] === 'pending')>Pending</option>
                                <option value="paid" @selected($filters['payment_status'] === 'paid')>Paid</option>
                                <option value="failed" @selected($filters['payment_status'] === 'failed')>Failed</option>
                                <option value="refunded" @selected($filters['payment_status'] === 'refunded')>Refunded</option>
                            </select>

                            <button class="btn btn-primary" type="submit">Filter</button>
                            @if($filters['q'] || $filters['status'] || $filters['payment_status'])
                                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Reset</a>
                            @endif
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Order Details</th>
                                    <th>Customer Info</th>
                                    <th>Method & Payment</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th class="text-end">Total Amount</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr>
                                        <td>
                                            <div style="font-weight: 700; color: #0f172a;">
                                                {{ $order->order_number }}
                                            </div>
                                            <small class="muted" style="font-size: 12px;">{{ $order->items->count() }} {{ Str::plural('piece', $order->items->count()) }}</small>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: #0f172a;">{{ $order->ship_name }}</div>
                                            <div style="font-size: 12px; color: #64748b;">
                                                <i class="bi bi-envelope me-1"></i> {{ $order->ship_email }}
                                            </div>
                                            <div style="font-size: 12px; color: #64748b;">
                                                <i class="bi bi-telephone me-1"></i> {{ $order->ship_phone }}
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; text-transform: uppercase; font-size: 12.5px; color: #0f172a;">
                                                {{ $order->payment_method }}
                                            </div>
                                            <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success' : ($order->payment_status === 'pending' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                                {{ ucfirst($order->payment_status) }}
                                            </span>
                                        </td>
                                        <td style="color: #64748b; font-size: 12.5px;">
                                            <div>{{ $order->created_at->format('M d, Y') }}</div>
                                            <div style="font-size: 11px;">{{ $order->created_at->format('h:i A') }}</div>
                                        </td>
                                        <td>
                                            @php
                                                $statusBadge = match($order->status) {
                                                    'pending' => 'bg-warning text-dark',
                                                    'confirmed', 'processing' => 'bg-primary',
                                                    'shipped' => 'bg-info text-dark',
                                                    'delivered' => 'bg-success',
                                                    'cancelled', 'refunded' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                            @endphp
                                            <span class="badge {{ $statusBadge }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end font-monospace" style="font-weight: 700; color: #16a34a; font-size: 14px;">
                                            ₹{{ number_format($order->total_amount, 2) }}
                                        </td>
                                        <td class="text-center">
                                            <a class="btn btn-sm btn-primary py-1 px-3" href="{{ route('admin.orders.show', $order) }}" style="font-size: 12px;">
                                                <i class="bi bi-box-seam me-1"></i> Fulfill
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5" style="color: #64748b;">
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
                            <div>
                                {{ $orders->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                </section>
            </div>
        </main>
    </div>
@endsection
