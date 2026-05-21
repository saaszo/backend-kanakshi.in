@extends('admin.layout')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')

        <main class="admin-main">
            <div class="dashboard-card">
                <div class="topbar">
                    <div>
                        <div class="brand">Dashboard</div>
                        <h2>Store Control Center</h2>
                        <p class="lead" style="margin-top:8px;">Manage products, homepage content, menus, branding, and storefront configuration from one clean workspace.</p>
                    </div>
                    <div class="toolbar-actions">
                        <a href="{{ route('admin.products.index') }}" class="button secondary small">
                            <i class="bi bi-box-seam"></i>
                            <span>Manage Products</span>
                        </a>
                        <a href="{{ route('admin.homepage-sections.index') }}" class="button small">
                            <i class="bi bi-stars"></i>
                            <span>Edit Homepage</span>
                        </a>
                    </div>
                </div>

                <div class="stats">
                    <div class="stat"><small>Total Products</small><strong>{{ $stats['products'] }}</strong><p>Live catalog items available on the storefront.</p></div>
                    <div class="stat"><small>Categories</small><strong>{{ $stats['categories'] }}</strong><p>Organised collections for shopping and filtering.</p></div>
                    <div class="stat"><small>Homepage Blocks</small><strong>{{ $stats['homepage_sections'] }}</strong><p>Editable content sections powering the home page.</p></div>
                    <div class="stat"><small>Admin Users</small><strong>{{ $stats['admins'] }}</strong><p>Authorized dashboard users with protected access.</p></div>
                </div>

                <div class="split-grid" style="margin-top: 24px;">
                    <section class="dashboard-table-card">
                        <div class="dashboard-table-head">
                            <div>
                                <h3>Quick Actions</h3>
                                <p class="muted" style="margin:8px 0 0;">Jump directly into the areas you update the most.</p>
                            </div>
                        </div>
                        <div style="padding: 0 22px 22px;">
                            <div class="button-row">
                                <a href="{{ route('admin.settings.edit') }}" class="button secondary small"><i class="bi bi-gear"></i><span>Store Settings</span></a>
                                <a href="{{ route('admin.homepage-sections.index') }}" class="button secondary small"><i class="bi bi-images"></i><span>Homepage Sections</span></a>
                                <a href="{{ route('admin.categories.index') }}" class="button secondary small"><i class="bi bi-tags"></i><span>Categories</span></a>
                                <a href="{{ route('admin.products.index') }}" class="button secondary small"><i class="bi bi-box-seam"></i><span>Products</span></a>
                                <a href="{{ route('admin.menu-items.index') }}" class="button secondary small"><i class="bi bi-menu-button-wide"></i><span>Menus</span></a>
                                <a href="{{ route('admin.social-links.index') }}" class="button secondary small"><i class="bi bi-share"></i><span>Social Links</span></a>
                            </div>
                        </div>
                    </section>

                    <section class="dashboard-table-card">
                        <div class="dashboard-table-head">
                            <div>
                                <h3>Admin Snapshot</h3>
                                <p class="muted" style="margin:8px 0 0;">Current protected admin access details for the live store.</p>
                            </div>
                        </div>
                        <div class="table-wrap" style="border:none; border-top:1px solid var(--border); border-radius:0;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ auth()->user()->name }}</td>
                                        <td>{{ auth()->user()->email }}</td>
                                        <td><span class="pill">{{ str_replace('_', ' ', auth()->user()->role) }}</span></td>
                                        <td><span class="pill">{{ auth()->user()->status }}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <div class="section-grid" style="margin-top: 24px;">
                    <section class="dashboard-table-card">
                        <div class="dashboard-table-head">
                            <div>
                                <h3>Recent Store Modules</h3>
                                <p class="muted" style="margin:8px 0 0;">A quick map of the main control areas currently wired into this admin panel.</p>
                            </div>
                        </div>
                        <div class="table-wrap" style="border:none; border-top:1px solid var(--border); border-radius:0;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Module</th>
                                        <th>Purpose</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Homepage Sections</td>
                                        <td>Hero slider, promo blocks, section headings, and image-driven content.</td>
                                        <td><span class="pill">Active</span></td>
                                        <td><a href="{{ route('admin.homepage-sections.index') }}" class="button secondary small">Open</a></td>
                                    </tr>
                                    <tr>
                                        <td>Product Catalog</td>
                                        <td>Products, pricing, stock quantity, media uploads, and storefront visibility.</td>
                                        <td><span class="pill">Active</span></td>
                                        <td><a href="{{ route('admin.products.index') }}" class="button secondary small">Open</a></td>
                                    </tr>
                                    <tr>
                                        <td>Store Settings</td>
                                        <td>Brand identity, logo, favicon, email transport, payment, and delivery setup.</td>
                                        <td><span class="pill">Active</span></td>
                                        <td><a href="{{ route('admin.settings.edit') }}" class="button secondary small">Open</a></td>
                                    </tr>
                                    <tr>
                                        <td>Navigation</td>
                                        <td>Header and footer menu links, social handles, and storefront discovery flow.</td>
                                        <td><span class="pill">Active</span></td>
                                        <td><a href="{{ route('admin.menu-items.index') }}" class="button secondary small">Open</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
@endsection
