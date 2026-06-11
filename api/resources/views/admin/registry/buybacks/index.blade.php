@extends('admin.layout')

@section('title', 'Buyback Evaluation Queue | Little Divinity Admin')

@section('content')
<div class="dashboard-shell">
    @include('admin.partials.sidebar')

    <main class="admin-main">
        <div class="admin-banner">
            <div>
                <span class="brand">Buyback Evaluation Queue</span>
                <h2>Return-to-Vault Buybacks</h2>
            </div>
        </div>

        @if(session('status'))
            <div class="admin-toast">
    <div>
        <strong>Success!</strong>
        <p>
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('status') }}</span>
            </p>
    </div>
</div>
        @endif

        <div class="admin-section mb-4">
            <h3 class="mb-3">Filter Buyback Requests</h3>
            <form method="GET" action="{{ route('admin.registry.buybacks.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="filter_status">Buyback Status</label>
                    <select id="filter_status" name="status" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="submitted" {{ $filters['status'] === 'submitted' ? 'selected' : '' }}>Submitted</option>
                        <option value="inspection_pending" {{ $filters['status'] === 'inspection_pending' ? 'selected' : '' }}>Inspection Pending</option>
                        <option value="valued" {{ $filters['status'] === 'valued' ? 'selected' : '' }}>Valued / Proposed</option>
                        <option value="approved" {{ $filters['status'] === 'approved' ? 'selected' : '' }}>Approved / In-Transit</option>
                        <option value="rejected" {{ $filters['status'] === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="completed" {{ $filters['status'] === 'completed' ? 'selected' : '' }}>Completed / Vault Restored</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label for="search">Search Keywords</label>
                    <div class="input-group">
                        <input type="text" id="search" name="q" placeholder="Request code, registration code, product, customer name..." value="{{ $filters['q'] }}">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </form>
        </div>

        <div class="dashboard-table-card">
            <div class="dashboard-table-head">
                <h3>Guarantee Return-to-Vault Queue</h3>
                <span class="muted">Showing {{ $buybacks->firstItem() ?? 0 }}-{{ $buybacks->lastItem() ?? 0 }} of {{ $buybacks->total() }} records</span>
            </div>

            <div class="table-wrap">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Request Code</th>
                            <th>Reg Code</th>
                            <th>Customer Name</th>
                            <th>Product Snapshot</th>
                            <th>Pickup City</th>
                            <th>Est. Value</th>
                            <th>Final Value</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($buybacks as $bb)
                            <tr>
                                <td>
                                    <strong class="text-primary font-monospace">{{ $bb->request_code }}</strong>
                                </td>
                                <td>
                                    <a href="{{ route('admin.registry.registrations.show', $bb->registration->id) }}" class="text-decoration-none font-monospace">
                                        {{ $bb->registration->registration_code }}
                                    </a>
                                </td>
                                <td>
                                    <div class="d-grid">
                                        <strong>{{ $bb->registration->customer_name }}</strong>
                                        <small class="text-muted">{{ $bb->registration->email }}</small>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $bb->registration->product_name_snapshot }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $bb->pickup_city ?: 'N/A' }}</span>
                                </td>
                                <td>
                                    <strong>{{ $bb->estimated_buyback_value ? '₹' . number_format($bb->estimated_buyback_value, 2) : 'Pending' }}</strong>
                                </td>
                                <td>
                                    <strong class="text-success">{{ $bb->final_buyback_value ? '₹' . number_format($bb->final_buyback_value, 2) : 'N/A' }}</strong>
                                </td>
                                <td>
                                    @if($bb->status === 'completed')
                                        <span class="admin-badge success">✓ Vaulted</span>
                                    @elseif($bb->status === 'submitted')
                                        <span class="admin-badge primary">⚠ Submitted</span>
                                    @elseif($bb->status === 'inspection_pending')
                                        <span class="admin-badge warning">✈ In-Transit</span>
                                    @elseif($bb->status === 'rejected')
                                        <span class="admin-badge danger">✗ Rejected</span>
                                    @else
                                        <span class="admin-badge success">{{ ucfirst($bb->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.registry.buybacks.show', $bb->id) }}" class="button small secondary">
                                        <i class="bi bi-pencil-square"></i> Inspect
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="dashboard-empty">
                                    <i class="bi bi-safe2 fs-1 d-block mb-2 text-muted"></i>
                                    <span>No guarantee buyback evaluation requests match criteria.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($buybacks->hasPages())
                <div class="p-3 border-top d-flex justify-content-center">
                    {{ $buybacks->links() }}
                </div>
            @endif
        </div>
    </main>
</div>
@endsection
