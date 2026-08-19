@extends('admin.layout')

@section('title', 'Inventory & Stock Management')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <div class="admin-banner">
                    <div>
                        <div class="brand">Stock Control</div>
                        <h2>Inventory & Stock Management</h2>
                        <p class="lead" style="margin-top: 4px;">Monitor catalog quantities, update stock units in bulk, and spot out-of-stock items.</p>
                    </div>
                </div>

                @if (session('status'))
                    <div class="p-3 mb-4" style="background: #e8f7ee; border: 1px solid #c2ebd1; color: #0d532b; font-weight: 600;">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                    </div>
                @endif

                <div class="metrics-grid mb-4">
                    <div class="admin-stat">
                        <small>Total Products</small>
                        <strong>{{ $stats['total_products'] }}</strong>
                        <span>Catalog items</span>
                    </div>
                    <div class="admin-stat">
                        <small>Total Units</small>
                        <strong style="color: #2563eb;">{{ $stats['total_units'] }}</strong>
                        <span>Available pieces</span>
                    </div>
                    <div class="admin-stat">
                        <small>Low Stock</small>
                        <strong style="color: #d97706;">{{ $stats['low_stock'] }}</strong>
                        <span>1 to 5 pieces remaining</span>
                    </div>
                    <div class="admin-stat">
                        <small>Out of Stock</small>
                        <strong style="color: #dc2626;">{{ $stats['out_of_stock'] }}</strong>
                        <span>0 pieces remaining</span>
                    </div>
                </div>

                <section class="admin-section">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                        <div>
                            <h3 class="mb-0">Stock Allocation Table</h3>
                            <p class="muted mb-0" style="font-size: 13px;">Quickly edit units in real-time without leaving this screen.</p>
                        </div>
                        <form method="GET" action="{{ route('admin.inventory.index') }}" class="d-flex align-items-center gap-2">
                            <input type="search" name="q" placeholder="Search product or SKU..." value="{{ $search }}" style="width: 240px;" />
                            <button class="btn btn-primary" type="submit">Search</button>
                            @if($search)
                                <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary">Reset</a>
                            @endif
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th class="text-center">Current Stock</th>
                                    <th class="text-center">Health Status</th>
                                    <th class="text-center" style="width: 200px;">Update Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $product)
                                    <tr>
                                        <td>
                                            <div style="font-weight: 700; color: #0f172a;">{{ $product->name }}</div>
                                            <small class="muted" style="font-size: 12px;">SKU: {{ $product->sku ?: 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $product->category?->name ?: 'Fine Jewellery' }}</span>
                                        </td>
                                        <td class="text-center font-monospace" style="font-weight: 800; font-size: 14px; color: {{ $product->stock <= 0 ? '#dc2626' : ($product->stock <= 5 ? '#d97706' : '#16a34a') }};">
                                            {{ $product->stock }}
                                        </td>
                                        <td class="text-center">
                                            @if ($product->stock <= 0)
                                                <span class="badge bg-danger">Out of Stock</span>
                                            @elseif ($product->stock <= 5)
                                                <span class="badge bg-warning text-dark">Low Stock</span>
                                            @else
                                                <span class="badge bg-success">In Stock</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('admin.inventory.update', $product) }}" class="d-flex gap-2 justify-content-center">
                                                @csrf
                                                @method('PUT')
                                                <input type="number" min="0" name="stock" value="{{ $product->stock }}" class="form-control text-center" style="width: 80px; padding: 4px 8px; font-weight: 700;" />
                                                <button class="btn btn-sm btn-primary py-1 px-3" type="submit">Save</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4" style="color: #64748b;">
                                            No products match the search query.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
@endsection
