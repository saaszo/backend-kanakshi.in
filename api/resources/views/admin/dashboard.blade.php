@extends('admin.layout')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')

        <main class="admin-main">
            <div class="dashboard-card">
                <div class="topbar">
                    <div>
                        <div class="brand">Little Divinity</div>
                        <h2>Admin Dashboard</h2>
                        <p class="lead" style="margin-top:8px;">Admin panel is active at <strong>/admin</strong>. Signup is disabled. Use this panel for homepage content, products, categories, menus, and storefront settings.</p>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="button secondary small" type="submit">Logout</button>
                    </form>
                </div>

                <div class="stats">
                    <div class="stat">
                        <small>Admin Email</small>
                        <strong>{{ auth()->user()->email }}</strong>
                    </div>
                    <div class="stat">
                        <small>Products</small>
                        <strong>{{ $stats['products'] }}</strong>
                    </div>
                    <div class="stat">
                        <small>Categories</small>
                        <strong>{{ $stats['categories'] }}</strong>
                    </div>
                    <div class="stat">
                        <small>Homepage Sections</small>
                        <strong>{{ $stats['homepage_sections'] }}</strong>
                    </div>
                </div>

                <div class="section-grid" style="margin-top: 24px;">
                    <div class="panel">
                        <h3>What you can manage now</h3>
                        <p>This admin pass is focused on the live storefront controls you asked for before push.</p>
                        <div class="button-row">
                            <a href="{{ route('admin.settings.edit') }}" class="button secondary small">Store Settings</a>
                            <a href="{{ route('admin.homepage-sections.index') }}" class="button secondary small">Homepage Sections</a>
                            <a href="{{ route('admin.categories.index') }}" class="button secondary small">Categories</a>
                            <a href="{{ route('admin.products.index') }}" class="button secondary small">Products</a>
                            <a href="{{ route('admin.menu-items.index') }}" class="button secondary small">Header/Footer Menu</a>
                            <a href="{{ route('admin.social-links.index') }}" class="button secondary small">Social Links</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
@endsection
