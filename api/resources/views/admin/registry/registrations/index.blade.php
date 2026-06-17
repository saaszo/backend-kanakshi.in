@extends('admin.layout')

@section('title', 'Guarantees & Warranties | Kanakshi.in Admin')

@section('content')
<div class="dashboard-shell">
    @include('admin.partials.sidebar')

    <main class="admin-main">
        <div class="admin-banner">
            <div>
                <span class="brand">Guarantee Registry</span>
                <h2>Guarantees & Warranties</h2>
            </div>
            <div class="toolbar-actions">
                <a href="{{ route('admin.registry.settings.edit') }}" class="button secondary">
                    <i class="bi bi-gear"></i>
                    <span>Registry Settings</span>
                </a>
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
            <h3 class="mb-3">Filter Registrations</h3>
            <form method="GET" action="{{ route('admin.registry.registrations.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="filter_source">Purchase Source</label>
                    <select id="filter_source" name="source" onchange="this.form.submit()">
                        <option value="">All Sources</option>
                        <option value="website" {{ $filters['source'] === 'website' ? 'selected' : '' }}>Website</option>
                        <option value="offline_store" {{ $filters['source'] === 'offline_store' ? 'selected' : '' }}>Offline Store</option>
                        <option value="amazon" {{ $filters['source'] === 'amazon' ? 'selected' : '' }}>Amazon</option>
                        <option value="other_marketplace" {{ $filters['source'] === 'other_marketplace' ? 'selected' : '' }}>Other Marketplace</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_status">Verification Status</label>
                    <select id="filter_status" name="status" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="pending_verification" {{ $filters['status'] === 'pending_verification' ? 'selected' : '' }}>Pending Verification</option>
                        <option value="verified" {{ $filters['status'] === 'verified' ? 'selected' : '' }}>Verified / Active</option>
                        <option value="rejected" {{ $filters['status'] === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="expired" {{ $filters['status'] === 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_buyback">Buyback Eligible</label>
                    <select id="filter_buyback" name="buyback_eligible" onchange="this.form.submit()">
                        <option value="">All Eligibility</option>
                        <option value="1" {{ $filters['buyback_eligible'] === '1' ? 'selected' : '' }}>Eligible</option>
                        <option value="0" {{ $filters['buyback_eligible'] === '0' ? 'selected' : '' }}>Ineligible</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="search">Search Keywords</label>
                    <div class="input-group">
                        <input type="text" id="search" name="q" placeholder="Code, name, email, bill no..." value="{{ $filters['q'] }}">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </form>
        </div>

        <div class="dashboard-table-card">
            <div class="dashboard-table-head">
                <h3>Guarantee Registrations Queue</h3>
                <span class="muted">Showing {{ $registrations->firstItem() ?? 0 }}-{{ $registrations->lastItem() ?? 0 }} of {{ $registrations->total() }} records</span>
            </div>

            <div class="table-wrap">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Reg Code</th>
                            <th>Customer</th>
                            <th>Source</th>
                            <th>Bill / Order #</th>
                            <th>Product Snapshot</th>
                            <th>Purchase Date</th>
                            <th>Guarantee Ends</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registrations as $reg)
                            <tr>
                                <td>
                                    <strong class="text-primary">{{ $reg->registration_code }}</strong>
                                </td>
                                <td>
                                    <div class="d-grid">
                                        <strong>{{ $reg->customer_name }}</strong>
                                        <small class="text-muted">{{ $reg->email }}</small>
                                        <small class="text-muted">{{ $reg->phone }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary text-capitalize">{{ str_replace('_', ' ', $reg->purchase_source) }}</span>
                                </td>
                                <td>
                                    <code>{{ $reg->order_or_bill_number }}</code>
                                </td>
                                <td>
                                    <div class="admin-product-meta">
                                        <strong>{{ $reg->product_name_snapshot }}</strong>
                                        @if($reg->serial_card_id)
                                            <span class="text-muted">Serial Card: {{ $reg->serial_card_id }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $reg->purchase_date->format('Y-m-d') }}</td>
                                <td>{{ $reg->warranty_end_date ? $reg->warranty_end_date->format('Y-m-d') : 'N/A' }}</td>
                                <td>
                                    @if($reg->verification_status === 'verified')
                                        <span class="admin-badge success">✓ Active</span>
                                    @elseif($reg->verification_status === 'pending_verification')
                                        <span class="admin-badge warning">⚠ Pending</span>
                                    @elseif($reg->verification_status === 'rejected')
                                        <span class="admin-badge danger">✗ Rejected</span>
                                    @else
                                        <span class="admin-badge muted">Expired</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.registry.registrations.show', $reg->id) }}" class="button small secondary py-1 px-2">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        @if($reg->verification_status === 'pending_verification')
                                            <a href="{{ route('admin.registry.registrations.show', $reg->id) }}#verify-section" class="button small py-1 px-2">
                                                <i class="bi bi-check-lg"></i> Check
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="dashboard-empty">
                                    <i class="bi bi-shield-slash fs-1 d-block mb-2 text-muted"></i>
                                    <span>No active guarantee or warranty registrations match the criteria.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($registrations->hasPages())
                <div class="p-3 border-top d-flex justify-content-center">
                    {{ $registrations->links() }}
                </div>
            @endif
        </div>
    </main>
</div>
@endsection
