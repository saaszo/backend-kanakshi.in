@php
    $siteUrl = env('FRONTEND_SITE_URL', env('APP_FRONTEND_URL', 'http://localhost:3000'));
    $pendingInquiries = \Illuminate\Support\Facades\DB::table('contact_inquiries')->where('status', 'pending')->count();
    $pendingOrders = \App\Models\Order::where('status', 'pending')->count();
    $pendingReturns = \App\Models\OrderReturn::where('status', 'requested')->count();
@endphp

<aside class="admin-sidebar" id="adminSidebar">
    <!-- Brand Header -->
    <div class="sidebar-brand">
        <div class="brand-logo-square">
            <i class="bi bi-gem"></i>
        </div>
        <div class="brand-info">
            <span class="brand-title">KANAKSHI</span>
            <span class="brand-badge">ADMIN CONSOLE</span>
        </div>
    </div>

    <!-- Navigation Scroll Container -->
    <div class="sidebar-scroll">
        <!-- Quick Dashboard Link -->
        <div class="sidebar-section">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-direct-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Executive Dashboard</span>
            </a>
        </div>

        <!-- Group: Commerce & Orders -->
        <div class="sidebar-menu-group">
            <div class="menu-item-parent {{ request()->routeIs('admin.orders.*', 'admin.returns.*', 'admin.inquiries.*', 'admin.reviews.*', 'admin.reports.*') ? 'active open' : '' }}" onclick="toggleSubmenu(this)">
                <div class="parent-label">
                    <i class="bi bi-cart3"></i>
                    <span>Orders & Sales</span>
                </div>
                <div class="parent-meta">
                    @if ($pendingOrders > 0)
                        <span class="badge-count">{{ $pendingOrders }}</span>
                    @endif
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </div>
            </div>
            <div class="submenu-list {{ request()->routeIs('admin.orders.*', 'admin.returns.*', 'admin.inquiries.*', 'admin.reviews.*', 'admin.reports.*') ? 'expanded' : '' }}">
                <a href="{{ route('admin.orders.index') }}" class="submenu-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                    <i class="bi bi-bag-check"></i>
                    <span>Orders Management</span>
                    @if ($pendingOrders > 0)
                        <span class="sub-badge">{{ $pendingOrders }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.inquiries.index') }}" class="submenu-link {{ request()->routeIs('admin.inquiries.*') ? 'active' : '' }}">
                    <i class="bi bi-envelope-paper"></i>
                    <span>Contact Inquiries</span>
                    @if ($pendingInquiries > 0)
                        <span class="sub-badge warn">{{ $pendingInquiries }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.returns.index') }}" class="submenu-link {{ request()->routeIs('admin.returns.*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-return-left"></i>
                    <span>Returns & Refunds</span>
                    @if ($pendingReturns > 0)
                        <span class="sub-badge warn">{{ $pendingReturns }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.reviews.index') }}" class="submenu-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                    <i class="bi bi-star"></i>
                    <span>Product Reviews</span>
                </a>
                <a href="{{ route('admin.reports.index') }}" class="submenu-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Sales Analytics</span>
                </a>
            </div>
        </div>

        <!-- Group: Catalog & Inventory -->
        <div class="sidebar-menu-group">
            <div class="menu-item-parent {{ request()->routeIs('admin.products.*', 'admin.categories.*', 'admin.inventory.*', 'admin.coupons.*') ? 'active open' : '' }}" onclick="toggleSubmenu(this)">
                <div class="parent-label">
                    <i class="bi bi-box-seam"></i>
                    <span>Catalog & Stock</span>
                </div>
                <div class="parent-meta">
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </div>
            </div>
            <div class="submenu-list {{ request()->routeIs('admin.products.*', 'admin.categories.*', 'admin.inventory.*', 'admin.coupons.*') ? 'expanded' : '' }}">
                <a href="{{ route('admin.products.index') }}" class="submenu-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    <i class="bi bi-grid-3x3-gap"></i>
                    <span>All Products</span>
                </a>
                <a href="{{ route('admin.categories.index') }}" class="submenu-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i>
                    <span>Categories</span>
                </a>
                <a href="{{ route('admin.inventory.index') }}" class="submenu-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
                    <i class="bi bi-boxes"></i>
                    <span>Inventory Levels</span>
                </a>
                <a href="{{ route('admin.coupons.index') }}" class="submenu-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
                    <i class="bi bi-percent"></i>
                    <span>Coupons & Offers</span>
                </a>
            </div>
        </div>

        <!-- Group: Guarantees & Registry -->
        <div class="sidebar-menu-group">
            <div class="menu-item-parent {{ request()->routeIs('admin.registry.*') ? 'active open' : '' }}" onclick="toggleSubmenu(this)">
                <div class="parent-label">
                    <i class="bi bi-shield-check"></i>
                    <span>Warranty & Registry</span>
                </div>
                <div class="parent-meta">
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </div>
            </div>
            <div class="submenu-list {{ request()->routeIs('admin.registry.*') ? 'expanded' : '' }}">
                <a href="{{ route('admin.registry.registrations.index') }}" class="submenu-link {{ request()->routeIs('admin.registry.registrations.*') ? 'active' : '' }}">
                    <i class="bi bi-award"></i>
                    <span>Registrations</span>
                </a>
                <a href="{{ route('admin.registry.claims.index') }}" class="submenu-link {{ request()->routeIs('admin.registry.claims.*') ? 'active' : '' }}">
                    <i class="bi bi-tools"></i>
                    <span>Warranty Claims</span>
                </a>
                <a href="{{ route('admin.registry.buybacks.index') }}" class="submenu-link {{ request()->routeIs('admin.registry.buybacks.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-stack"></i>
                    <span>Buyback Queue</span>
                </a>
                <a href="{{ route('admin.registry.settings.edit') }}" class="submenu-link {{ request()->routeIs('admin.registry.settings.*') ? 'active' : '' }}">
                    <i class="bi bi-sliders"></i>
                    <span>Registry Config</span>
                </a>
            </div>
        </div>

        <!-- Group: Live Auctions -->
        <div class="sidebar-menu-group">
            <div class="menu-item-parent {{ request()->routeIs('admin.auctions.*') ? 'active open' : '' }}" onclick="toggleSubmenu(this)">
                <div class="parent-label">
                    <i class="bi bi-hammer"></i>
                    <span>Live Auctions</span>
                </div>
                <div class="parent-meta">
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </div>
            </div>
            <div class="submenu-list {{ request()->routeIs('admin.auctions.*') ? 'expanded' : '' }}">
                <a href="{{ route('admin.auctions.index') }}" class="submenu-link {{ request()->routeIs('admin.auctions.index') ? 'active' : '' }}">
                    <i class="bi bi-list-ul"></i>
                    <span>All Auctions</span>
                </a>
                <a href="{{ route('admin.auctions.create') }}" class="submenu-link {{ request()->routeIs('admin.auctions.create') ? 'active' : '' }}">
                    <i class="bi bi-plus-square"></i>
                    <span>Create Auction</span>
                </a>
            </div>
        </div>

        <!-- Group: Editorial CMS -->
        <div class="sidebar-menu-group">
            <div class="menu-item-parent {{ request()->routeIs('admin.blog.*') ? 'active open' : '' }}" onclick="toggleSubmenu(this)">
                <div class="parent-label">
                    <i class="bi bi-journal-richtext"></i>
                    <span>Editorial & Blog</span>
                </div>
                <div class="parent-meta">
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </div>
            </div>
            <div class="submenu-list {{ request()->routeIs('admin.blog.*') ? 'expanded' : '' }}">
                <a href="{{ route('admin.blog.posts.index') }}" class="submenu-link {{ request()->routeIs('admin.blog.posts.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Articles & Posts</span>
                </a>
                <a href="{{ route('admin.blog.categories.index') }}" class="submenu-link {{ request()->routeIs('admin.blog.categories.*') ? 'active' : '' }}">
                    <i class="bi bi-folder"></i>
                    <span>Blog Categories</span>
                </a>
                <a href="{{ route('admin.blog.tags.index') }}" class="submenu-link {{ request()->routeIs('admin.blog.tags.*') ? 'active' : '' }}">
                    <i class="bi bi-hash"></i>
                    <span>Tags</span>
                </a>
                <a href="{{ route('admin.blog.authors.index') }}" class="submenu-link {{ request()->routeIs('admin.blog.authors.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Authors</span>
                </a>
            </div>
        </div>

        <!-- Group: Storefront & Design -->
        <div class="sidebar-menu-group">
            <div class="menu-item-parent {{ request()->routeIs('admin.homepage-sections.*', 'admin.homepage-products.*', 'admin.menu-items.*', 'admin.social-links.*') ? 'active open' : '' }}" onclick="toggleSubmenu(this)">
                <div class="parent-label">
                    <i class="bi bi-palette"></i>
                    <span>Storefront & Layout</span>
                </div>
                <div class="parent-meta">
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </div>
            </div>
            <div class="submenu-list {{ request()->routeIs('admin.homepage-sections.*', 'admin.homepage-products.*', 'admin.menu-items.*', 'admin.social-links.*') ? 'expanded' : '' }}">
                <a href="{{ route('admin.homepage-sections.hero.edit') }}" class="submenu-link {{ request()->routeIs('admin.homepage-sections.hero.*') ? 'active' : '' }}">
                    <i class="bi bi-display"></i>
                    <span>Hero Banner Slider</span>
                </a>
                <a href="{{ route('admin.homepage-sections.full.edit') }}" class="submenu-link {{ request()->routeIs('admin.homepage-sections.full.*') ? 'active' : '' }}">
                    <i class="bi bi-layout-wtf"></i>
                    <span>Full Homepage Builder</span>
                </a>
                <a href="{{ route('admin.homepage-products.index') }}" class="submenu-link {{ request()->routeIs('admin.homepage-products.*') ? 'active' : '' }}">
                    <i class="bi bi-grid"></i>
                    <span>Featured Collections</span>
                </a>
                <a href="{{ route('admin.menu-items.index') }}" class="submenu-link {{ request()->routeIs('admin.menu-items.*') ? 'active' : '' }}">
                    <i class="bi bi-menu-app"></i>
                    <span>Header & Footer Nav</span>
                </a>
                <a href="{{ route('admin.social-links.index') }}" class="submenu-link {{ request()->routeIs('admin.social-links.*') ? 'active' : '' }}">
                    <i class="bi bi-share"></i>
                    <span>Social Media Channels</span>
                </a>
            </div>
        </div>

        <!-- Group: Settings & Integrations -->
        <div class="sidebar-menu-group">
            <div class="menu-item-parent {{ request()->routeIs('admin.settings.*', 'admin.email-otp.*') ? 'active open' : '' }}" onclick="toggleSubmenu(this)">
                <div class="parent-label">
                    <i class="bi bi-gear"></i>
                    <span>Settings & System</span>
                </div>
                <div class="parent-meta">
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </div>
            </div>
            <div class="submenu-list {{ request()->routeIs('admin.settings.*', 'admin.payment-settings.*', 'admin.email-otp.*') ? 'expanded' : '' }}">
                <a href="{{ route('admin.settings.edit') }}" class="submenu-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="bi bi-sliders2"></i>
                    <span>General Store Info</span>
                </a>
                <a href="{{ route('admin.payment-settings.index') }}" class="submenu-link {{ request()->routeIs('admin.payment-settings.*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card-2-front"></i>
                    <span>Payment & Prepaid Offers</span>
                </a>
                <a href="{{ route('admin.wallet.index') }}" class="submenu-link {{ request()->routeIs('admin.wallet.*') ? 'active' : '' }}">
                    <i class="bi bi-wallet2"></i>
                    <span>Customer Wallet & Rewards</span>
                </a>
                <a href="{{ route('admin.email-otp.edit') }}" class="submenu-link {{ request()->routeIs('admin.email-otp.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-lock"></i>
                    <span>Email & OTP Security</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Sidebar Footer with Quick Actions -->
    <div class="sidebar-footer">
        <a href="{{ $siteUrl }}" target="_blank" rel="noreferrer" class="footer-action-btn store-btn">
            <i class="bi bi-arrow-up-right-square"></i>
            <span>View Live Store</span>
        </a>
        <form method="POST" action="{{ route('admin.logout') }}" style="margin: 0;">
            @csrf
            <button type="submit" class="footer-action-btn logout-btn">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>
