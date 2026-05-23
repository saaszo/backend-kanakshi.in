@extends('admin.layout')

@section('title', 'Returns & Refunds')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="dashboard-card">
                <div class="page-head mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <div class="brand">After-Sales Desk</div>
                        <h2 class="mb-0">Returns & Refunds</h2>
                        <p class="lead mb-0" style="margin-top: 8px;">Review customer return requests, approve resolutions, and reconcile refunds.</p>
                    </div>
                </div>

                @if (session('status'))
                    <div class="message mb-4" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #34d399; padding: 15px; border-radius: var(--radius-md); font-weight: 500;">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="GET" class="panel mb-4">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" name="q" value="{{ $filters['q'] }}" placeholder="Return no, order no, customer, reason">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="">All statuses</option>
                                @foreach (['requested', 'approved', 'rejected', 'received', 'refunded'] as $status)
                                    <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="button small w-100" type="submit">Filter Requests</button>
                        </div>
                    </div>
                </form>

                <div class="table-wrap">
                    <table class="admin-data-table align-middle">
                        <thead>
                            <tr>
                                <th>Return</th>
                                <th>Order</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Requested</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($returns as $return)
                                <tr>
                                    <td>
                                        <strong>{{ $return->return_number }}</strong>
                                        <div class="muted">{{ $return->user?->email ?: $return->order?->ship_email }}</div>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $return->order) }}">{{ $return->order?->order_number }}</a>
                                        <div class="muted">{{ $return->order?->ship_name }}</div>
                                    </td>
                                    <td>{{ $return->reason }}</td>
                                    <td><span class="admin-badge compact">{{ ucfirst($return->status) }}</span></td>
                                    <td>₹{{ number_format($return->approved_amount > 0 ? $return->approved_amount : $return->requested_amount, 2) }}</td>
                                    <td>{{ optional($return->requested_at ?: $return->created_at)->format('d M Y, h:i A') }}</td>
                                    <td class="text-end">
                                        <a class="button secondary small" href="{{ route('admin.returns.show', $return) }}">Review</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center muted">No return requests found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $returns->links() }}
                </div>
            </div>
        </main>
    </div>
@endsection
