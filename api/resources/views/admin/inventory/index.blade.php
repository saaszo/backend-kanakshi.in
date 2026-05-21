@extends('admin.layout')

@section('title', 'Inventory')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="dashboard-card">
                <div class="page-head">
                    <div>
                        <div class="brand">Stock Control</div>
                        <h2>Inventory</h2>
                        <p class="lead" style="margin-top:8px;">Track total stock, identify low inventory, and update product quantities from one focused inventory screen.</p>
                    </div>
                </div>

                @if (session('status'))
                    <div class="message">{{ session('status') }}</div>
                @endif

                <div class="metrics-grid">
                    <article class="metric-card">
                        <small>Total Products</small>
                        <strong>{{ $stats['total_products'] }}</strong>
                        <span>Catalog items tracked</span>
                    </article>
                    <article class="metric-card">
                        <small>Total Inventory</small>
                        <strong>{{ $stats['total_units'] }}</strong>
                        <span>Units available</span>
                    </article>
                    <article class="metric-card warning">
                        <small>Low Stock</small>
                        <strong>{{ $stats['low_stock'] }}</strong>
                        <span>Products between 1 and 5 units</span>
                    </article>
                    <article class="metric-card danger">
                        <small>Out of Stock</small>
                        <strong>{{ $stats['out_of_stock'] }}</strong>
                        <span>Products that need replenishment</span>
                    </article>
                </div>

                <section class="panel">
                    <div class="admin-toolbar">
                        <div>
                            <h3>Inventory Table</h3>
                            <p class="muted">Update stock without opening each product separately.</p>
                        </div>
                        <form method="GET" action="{{ route('admin.inventory.index') }}" class="admin-toolbar-filters">
                            <input type="search" name="q" placeholder="Search name, sku, slug" value="{{ $search }}" />
                            <button class="button small" type="submit">Search</button>
                        </form>
                    </div>

                    <div class="table-wrap admin-product-table-wrap">
                        <table class="admin-data-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Current Stock</th>
                                    <th>Status</th>
                                    <th>Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $product)
                                    <tr>
                                        <td>
                                            <div class="admin-product-meta">
                                                <strong>{{ $product->name }}</strong>
                                                <span>{{ $product->sku ?: ($product->slug ?: 'No SKU / slug yet') }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $product->category?->name ?: 'Uncategorized' }}</td>
                                        <td>
                                            <span class="inventory-count {{ $product->stock <= 0 ? 'danger' : ($product->stock <= 5 ? 'warning' : 'success') }}">
                                                {{ $product->stock }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($product->stock <= 0)
                                                <span class="admin-badge danger">Out of Stock</span>
                                            @elseif ($product->stock <= 5)
                                                <span class="admin-badge warning">Low Stock</span>
                                            @else
                                                <span class="admin-badge success">Healthy</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.inventory.update', $product) }}" class="inventory-inline-form">
                                                @csrf
                                                @method('PUT')
                                                <input type="number" min="0" name="stock" value="{{ $product->stock }}" class="table-input" />
                                                <button class="button small" type="submit">Save</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="muted">No products found.</td>
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
