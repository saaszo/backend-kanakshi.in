@php
    $siteUrl = env('FRONTEND_SITE_URL', env('APP_FRONTEND_URL', 'https://littledivinity.com'));
@endphp

<aside class="sidebar">
    <div class="sidebar-logo">
        <span class="sidebar-logo-mark">
            <i class="bi bi-shop-window"></i>
        </span>
        <div class="sidebar-logo-text">
            <strong>Little Divinity</strong>
            <span>Admin Panel</span>
        </div>
    </div>

    <div class="sidebar-group">
        <div class="sidebar-label">Overview</div>
        <nav class="sidebar-nav">
            <a class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                <i class="bi bi-grid-1x2"></i>
                <span>Dashboard</span>
            </a>
        </nav>
    </div>

    <div class="sidebar-group">
        <div class="sidebar-label">Storefront Content</div>
        <nav class="sidebar-nav">
            <a class="sidebar-link {{ request()->routeIs('admin.homepage-sections.*') ? 'active' : '' }}" href="{{ route('admin.homepage-sections.index') }}">
                <i class="bi bi-images"></i>
                <span>Homepage Sections</span>
            </a>
            <a class="sidebar-link {{ request()->routeIs('admin.menu-items.*') ? 'active' : '' }}" href="{{ route('admin.menu-items.index') }}">
                <i class="bi bi-menu-button-wide"></i>
                <span>Header / Footer Menu</span>
            </a>
            <a class="sidebar-link {{ request()->routeIs('admin.social-links.*') ? 'active' : '' }}" href="{{ route('admin.social-links.index') }}">
                <i class="bi bi-share"></i>
                <span>Social Links</span>
            </a>
        </nav>
    </div>

    <div class="sidebar-group">
        <div class="sidebar-label">Catalog</div>
        <nav class="sidebar-nav">
            <a class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
                <i class="bi bi-tags"></i>
                <span>Categories</span>
            </a>
            <a class="sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
                <i class="bi bi-box-seam"></i>
                <span>Products</span>
            </a>
        </nav>
    </div>

    <div class="sidebar-group">
        <div class="sidebar-label">Configuration</div>
        <nav class="sidebar-nav">
            <a class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}">
                <i class="bi bi-gear"></i>
                <span>Store Settings</span>
            </a>
        </nav>
    </div>

    <div class="sidebar-footer">
        <div class="button-row">
            <a href="{{ $siteUrl }}" target="_blank" rel="noreferrer" class="button small">
                <i class="bi bi-box-arrow-up-right"></i>
                <span>View Site</span>
            </a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="button secondary small" type="submit">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>
