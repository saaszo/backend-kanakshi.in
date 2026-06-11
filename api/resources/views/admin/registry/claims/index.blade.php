@extends('admin.layout')

@section('title', 'Warranty Claims Queue | Little Divinity Admin')

@section('content')
<div class="dashboard-shell">
    @include('admin.partials.sidebar')

    <main class="admin-main">
        <div class="admin-banner">
            <div>
                <span class="brand">Warranty Claims Queue</span>
                <h2>Warranty Service Claims</h2>
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
            <h3 class="mb-3">Filter Claims</h3>
            <form method="GET" action="{{ route('admin.registry.claims.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="filter_status">Claim Status</label>
                    <select id="filter_status" name="status" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="submitted" {{ $filters['status'] === 'submitted' ? 'selected' : '' }}>Submitted</option>
                        <option value="under_review" {{ $filters['status'] === 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="approved" {{ $filters['status'] === 'approved' ? 'selected' : '' }}>Approved / In Service</option>
                        <option value="rejected" {{ $filters['status'] === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="in_service" {{ $filters['status'] === 'in_service' ? 'selected' : '' }}>In Service</option>
                        <option value="completed" {{ $filters['status'] === 'completed' ? 'selected' : '' }}>Completed / Resolved</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label for="search">Search Keywords</label>
                    <div class="input-group">
                        <input type="text" id="search" name="q" placeholder="Claim code, registration code, product, customer name..." value="{{ $filters['q'] }}">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </form>
        </div>

        <div class="dashboard-table-card">
            <div class="dashboard-table-head">
                <h3>Guarantee Claims Queue</h3>
                <span class="muted">Showing {{ $claims->firstItem() ?? 0 }}-{{ $claims->lastItem() ?? 0 }} of {{ $claims->total() }} records</span>
            </div>

            <div class="table-wrap">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Claim Code</th>
                            <th>Reg Code</th>
                            <th>Customer Name</th>
                            <th>Product Snapshot</th>
                            <th>Issue Type</th>
                            <th>Submitted Date</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($claims as $claim)
                            <tr>
                                <td>
                                    <strong class="text-primary font-monospace">{{ $claim->claim_code }}</strong>
                                </td>
                                <td>
                                    <a href="{{ route('admin.registry.registrations.show', $claim->registration->id) }}" class="text-decoration-none font-monospace">
                                        {{ $claim->registration->registration_code }}
                                    </a>
                                </td>
                                <td>
                                    <div class="d-grid">
                                        <strong>{{ $claim->registration->customer_name }}</strong>
                                        <small class="text-muted">{{ $claim->registration->email }}</small>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $claim->registration->product_name_snapshot }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $claim->issue_type }}</span>
                                </td>
                                <td>{{ $claim->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    @if($claim->status === 'completed')
                                        <span class="admin-badge success">✓ Resolved</span>
                                    @elseif($claim->status === 'submitted')
                                        <span class="admin-badge primary">⚠ Submitted</span>
                                    @elseif($claim->status === 'under_review')
                                        <span class="admin-badge warning">✎ Reviewing</span>
                                    @elseif($claim->status === 'rejected')
                                        <span class="admin-badge danger">✗ Rejected</span>
                                    @else
                                        <span class="admin-badge success">{{ ucfirst($claim->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.registry.claims.show', $claim->id) }}" class="button small secondary">
                                        <i class="bi bi-pencil-square"></i> Inspect
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="dashboard-empty">
                                    <i class="bi bi-wrench-adjustable fs-1 d-block mb-2 text-muted"></i>
                                    <span>No guarantee claims match the selected criteria.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($claims->hasPages())
                <div class="p-3 border-top d-flex justify-content-center">
                    {{ $claims->links() }}
                </div>
            @endif
        </div>
    </main>
</div>
@endsection
