<aside class="sidebar">
    <div class="brand">Little Divinity</div>
    <h3 style="margin:0; font-size:28px;">Admin</h3>
    <p class="muted" style="margin-top:8px;">Single-store control panel for storefront content, products, and settings.</p>

    <nav class="sidebar-nav">
        <a class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}">Store Settings</a>
        <a class="sidebar-link {{ request()->routeIs('admin.homepage-sections.*') ? 'active' : '' }}" href="{{ route('admin.homepage-sections.index') }}">Homepage Sections</a>
        <a class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">Categories</a>
        <a class="sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">Products</a>
        <a class="sidebar-link {{ request()->routeIs('admin.menu-items.*') ? 'active' : '' }}" href="{{ route('admin.menu-items.index') }}">Header/Footer Menu</a>
        <a class="sidebar-link {{ request()->routeIs('admin.social-links.*') ? 'active' : '' }}" href="{{ route('admin.social-links.index') }}">Social Links</a>
    </nav>
</aside>
