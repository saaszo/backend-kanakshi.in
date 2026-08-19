@extends('admin.layout')

@section('title', 'Returns & Refunds Management')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <!-- Top Header Banner -->
                <div class="admin-banner">
                    <div>
                        <div class="brand">After-Sales & Returns Desk</div>
                        <h2>Returns & Refunds Management</h2>
                        <p class="lead" style="margin-top: 4px;">Review customer return requests, authorize reverse pickups, inspect items, and issue refunds.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-bag me-1"></i> View Orders
                        </a>
                    </div>
                </div>

                @if (session('status'))
                    <div class="p-3 mb-4" style="background: #e8f7ee; border: 1px solid #c2ebd1; color: #0d532b; font-weight: 600;">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                    </div>
                @endif

                <!-- KPI Metric Tiles -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-lg-3">
                        <div class="p-3" style="background: #ffffff; border: 1px solid var(--border); border-left: 4px solid #f59e0b;">
                            <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b;">Pending Review</span>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <h3 class="mb-0 font-monospace" style="color: #d97706; font-size: 1.6rem;">{{ $stats['requested'] ?? 0 }}</h3>
                                <i class="bi bi-clock-history" style="font-size: 1.4rem; color: #f59e0b;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="p-3" style="background: #ffffff; border: 1px solid var(--border); border-left: 4px solid #2563eb;">
                            <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b;">Approved & In-Transit</span>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <h3 class="mb-0 font-monospace" style="color: #2563eb; font-size: 1.6rem;">{{ ($stats['approved'] ?? 0) + ($stats['received'] ?? 0) }}</h3>
                                <i class="bi bi-truck" style="font-size: 1.4rem; color: #2563eb;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="p-3" style="background: #ffffff; border: 1px solid var(--border); border-left: 4px solid #16a34a;">
                            <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b;">Refunded & Restocked</span>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <h3 class="mb-0 font-monospace" style="color: #16a34a; font-size: 1.6rem;">{{ $stats['refunded'] ?? 0 }}</h3>
                                <i class="bi bi-check2-circle" style="font-size: 1.4rem; color: #16a34a;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="p-3" style="background: #ffffff; border: 1px solid var(--border); border-left: 4px solid #7c3aed;">
                            <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b;">Total Value Refunded</span>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <h3 class="mb-0 font-monospace" style="color: #7c3aed; font-size: 1.4rem;">₹{{ number_format($stats['total_refunded_amount'] ?? 0, 2) }}</h3>
                                <i class="bi bi-currency-rupee" style="font-size: 1.4rem; color: #7c3aed;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters & Search Bar -->
                <section class="admin-section mb-4">
                    <form method="GET" action="{{ route('admin.returns.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Search Request</label>
                            <input type="text" class="form-control" name="q" value="{{ $filters['q'] }}" placeholder="Search by Return #, Order #, Email, Name..." />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Status Filter</label>
                            <select class="form-select" name="status">
                                <option value="">All Statuses ({{ $stats['total'] ?? 0 }})</option>
                                <option value="requested" @selected($filters['status'] === 'requested')>Pending Review ({{ $stats['requested'] ?? 0 }})</option>
                                <option value="approved" @selected($filters['status'] === 'approved')>Approved for Pickup ({{ $stats['approved'] ?? 0 }})</option>
                                <option value="received" @selected($filters['status'] === 'received')>Item Received & Inspected ({{ $stats['received'] ?? 0 }})</option>
                                <option value="refunded" @selected($filters['status'] === 'refunded')>Refunded & Resolved ({{ $stats['refunded'] ?? 0 }})</option>
                                <option value="rejected" @selected($filters['status'] === 'rejected')>Rejected ({{ $stats['rejected'] ?? 0 }})</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button class="btn btn-primary w-100" type="submit">
                                <i class="bi bi-funnel me-1"></i> Filter
                            </button>
                            @if ($filters['status'] !== '' || $filters['q'] !== '')
                                <a href="{{ route('admin.returns.index') }}" class="btn btn-outline-secondary">Reset</a>
                            @endif
                        </div>
                    </form>
                </section>

                <!-- Returns List Table -->
                <section class="admin-section">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="mb-0">Return Requests ({{ $returns->total() }})</h3>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Return ID</th>
                                    <th>Linked Order</th>
                                    <th>Customer</th>
                                    <th>Reason</th>
                                    <th>Items</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Requested On</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($returns as $return)
                                    <tr>
                                        <td>
                                            <div style="font-weight: 700; color: #0f172a;">{{ $return->return_number }}</div>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $return->order) }}" style="font-weight: 600; color: #2563eb;">
                                                {{ $return->order?->order_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: #0f172a;">{{ $return->order?->ship_name ?: ($return->user?->name ?? 'Customer') }}</div>
                                            <small class="text-muted">{{ $return->order?->ship_phone ?: ($return->user?->phone ?? $return->user?->email) }}</small>
                                        </td>
                                        <td style="font-size: 13px; max-width: 180px;">
                                            <div class="text-truncate" title="{{ $return->reason }}">{{ $return->reason }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark" style="border: 1px solid var(--border);">
                                                {{ count($return->requested_items ?? []) }} item(s)
                                            </span>
                                        </td>
                                        <td class="font-monospace" style="font-weight: 700; color: #16a34a;">
                                            ₹{{ number_format($return->approved_amount > 0 ? $return->approved_amount : $return->requested_amount, 2) }}
                                        </td>
                                        <td>
                                            @php
                                                $badgeClass = match($return->status) {
                                                    'requested' => 'bg-warning text-dark',
                                                    'approved' => 'bg-primary',
                                                    'received' => 'bg-info text-dark',
                                                    'refunded' => 'bg-success',
                                                    'rejected' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ ucfirst($return->status) }}</span>
                                        </td>
                                        <td style="font-size: 12px; color: #64748b;">
                                            {{ optional($return->requested_at ?: $return->created_at)->format('d M Y, h:i A') }}
                                        </td>
                                        <td class="text-end">
                                            <a class="btn btn-sm btn-primary py-1 px-3" href="{{ route('admin.returns.show', $return) }}" style="font-size: 12px;">
                                                <i class="bi bi-eye me-1"></i> Review
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5" style="color: #64748b;">
                                            <i class="bi bi-inbox" style="font-size: 2rem; display: block; margin-bottom: 8px;"></i>
                                            No return requests found matching criteria.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($returns->hasPages())
                        <div class="mt-4">
                            {{ $returns->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </section>
            </div>
        </main>
    </div>
@endsection
