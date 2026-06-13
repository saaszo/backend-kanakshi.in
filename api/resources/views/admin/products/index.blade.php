@extends('admin.layout')

@section('title', 'Products')

@section('content')
    <style>
        /* Modern Tabs CSS */
        .admin-tabs-nav {
            display: flex;
            gap: 8px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 8px;
            margin-bottom: 24px;
        }
        .admin-tab-btn {
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-soft);
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .admin-tab-btn:hover {
            color: var(--heading);
            background: rgba(15, 23, 42, 0.04);
        }
        .admin-tab-btn.active {
            color: var(--primary);
            background: var(--primary-glow);
        }
        .admin-tab-panel {
            display: none;
        }
        .admin-tab-panel.active {
            display: block;
        }

        /* Sub-tabs for Form sections */
        .admin-subtabs-nav {
            display: flex;
            gap: 4px;
            background: rgba(15, 23, 42, 0.03);
            padding: 4px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .admin-subtab-btn {
            flex: 1;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-soft);
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
        }
        .admin-subtab-btn:hover {
            color: var(--heading);
        }
        .admin-subtab-btn.active {
            background: #fff;
            color: var(--primary);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.05);
        }

        /* Viewport Optimized Scrollable Table */
        .admin-product-table-wrap {
            max-height: calc(100vh - 380px);
            overflow-y: auto;
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
        }
        .admin-product-table-wrap thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f8fafc;
            border-bottom: 1px solid var(--border-strong);
        }
        .admin-data-table .table-input {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 13px;
            background: transparent;
            transition: all 0.2s;
        }
        .admin-data-table .table-input:focus {
            background: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        /* Form Split Layout */
        .form-split-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 24px;
            align-items: start;
        }
        @media (max-width: 1024px) {
            .form-split-grid {
                grid-template-columns: 1fr;
            }
        }
        .sticky-sidebar {
            position: sticky;
            top: 24px;
            display: grid;
            gap: 20px;
        }

        /* Photo Cards Grid */
        .media-slot-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }

        /* Live Storefront Preview Card Styles */
        .preview-product-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        }
        .preview-img-container {
            aspect-ratio: 4/3;
            background: #eef2f7;
            display: grid;
            place-items: center;
            overflow: hidden;
            position: relative;
        }
        .preview-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .preview-badge-featured {
            position: absolute;
            top: 8px;
            left: 8px;
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
            border: 1px solid rgba(37, 99, 235, 0.14);
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 999px;
            font-weight: 700;
            text-transform: uppercase;
        }

        /* Quick Edit Modal CSS */
        .admin-modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .admin-modal-overlay.active {
            display: flex;
            opacity: 1;
        }
        .admin-modal-content {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }
        .admin-modal-overlay.active .admin-modal-content {
            transform: translateY(0);
        }
        .admin-modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .admin-modal-header h3 { margin: 0; font-size: 18px; color: var(--heading); }
        .admin-modal-close {
            background: none; border: none; font-size: 20px; color: var(--text-soft); cursor: pointer;
        }
        .admin-modal-body {
            padding: 24px;
            overflow-y: auto;
        }
        .admin-modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background: #f8fafc;
            border-radius: 0 0 16px 16px;
        }
    </style>

    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <div class="admin-banner">
                    <div>
                        <div class="brand">Catalog Control</div>
                        <h2>Products</h2>
                        <p class="lead" style="margin-top:8px;">Manage live products, pricing, stock, featured visibility, and category placement from one professional list view.</p>
                    </div>
                    <div class="toolbar-actions">
                        <a class="button secondary small" href="{{ route('admin.inventory.index') }}">
                            <i class="bi bi-boxes"></i>
                            <span>Inventory</span>
                        </a>
                        <button type="button" class="button small" data-tab-target="panel-add" data-tab-group="main-tabs" data-tab-value="add">
                            <i class="bi bi-plus-lg"></i>
                            <span>Add Product</span>
                        </button>
                    </div>
                </div>

                @if (session('status'))
                    <div class="admin-toast">
                        <div>
                            <strong>Success!</strong>
                            <p>{{ session('status') }}</p>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="admin-errors">
                        <strong>Please resolve the validation errors below:</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="admin-overview">
                    <div class="admin-stat">
                        <small>Total Products</small>
                        <strong>{{ $stats['total_products'] }}</strong>
                        <span>All catalog items</span>
                    </div>
                    <div class="admin-stat">
                        <small>Active Products</small>
                        <strong>{{ $stats['active_products'] }}</strong>
                        <span>Visible on storefront</span>
                    </div>
                    <div class="admin-stat">
                        <small>Featured Products</small>
                        <strong>{{ $stats['featured_products'] }}</strong>
                        <span>Used in homepage rails</span>
                    </div>
                    <div class="admin-stat">
                        <small>Total Inventory</small>
                        <strong>{{ $stats['total_stock'] }}</strong>
                        <span>Units available right now</span>
                    </div>
                </div>

                <!-- Main tab buttons -->
                <div class="admin-tabs-nav">
                    <button type="button" class="admin-tab-btn active" data-tab-target="panel-list" data-tab-group="main-tabs" data-tab-value="list">
                        <i class="bi bi-list-ul"></i>
                        <span>Product Catalog ({{ $stats['total_products'] }})</span>
                    </button>
                    <button type="button" class="admin-tab-btn" data-tab-target="panel-add" data-tab-group="main-tabs" data-tab-value="add">
                        <i class="bi bi-plus-circle"></i>
                        <span>Add New Product</span>
                    </button>
                </div>

                <!-- Tab 1: Product List -->
                <div class="admin-tab-panel active" id="panel-list" data-tab-panel-group="main-tabs">
                    <section class="admin-section">
                        <div class="admin-toolbar">
                            <div>
                                <h3>Product List</h3>
                                <p class="muted">Quick update category, stock, pricing, visibility, and open the full editor when you need media or SEO changes.</p>
                            </div>
                            <form method="GET" action="{{ route('admin.products.index') }}" class="admin-toolbar-filters">
                                <input type="search" name="q" placeholder="Search name, sku, slug" value="{{ $filters['q'] }}" />
                                <select name="category_id">
                                    <option value="0">All categories</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected($filters['category_id'] === $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <select name="status">
                                    <option value="">All status</option>
                                    <option value="active" @selected($filters['status'] === 'active')>Active</option>
                                    <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
                                    <option value="featured" @selected($filters['status'] === 'featured')>Featured</option>
                                </select>
                                <button class="button small" type="submit">Filter</button>
                            </form>
                        </div>

                        <div class="table-wrap admin-product-table-wrap">
                            <table class="admin-data-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Sale Price</th>
                                        <th>Delivery</th>
                                        <th>Marketplace</th>
                                        <th>Status</th>
                                        <th style="width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($products as $product)
                                        @php
                                            $image = is_array($product->images) ? ($product->images[0] ?? null) : null;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="admin-product-line">
                                                    <div class="admin-product-thumb">
                                                        @if ($image)
                                                            <img src="{{ $image }}" alt="{{ $product->name }}">
                                                        @else
                                                            <span><i class="bi bi-image"></i></span>
                                                        @endif
                                                    </div>
                                                    <div class="admin-product-meta">
                                                        <strong>{{ $product->name }}</strong>
                                                        <span>{{ $product->sku ?: ($product->slug ?: 'No SKU / slug yet') }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="muted" style="white-space: nowrap;">{{ $product->category->name ?? 'Uncategorized' }}</span>
                                            </td>
                                            <td>
                                                <span style="font-weight: 600; color: {{ $product->stock > 0 ? 'inherit' : 'var(--danger)' }}">{{ $product->stock }}</span>
                                            </td>
                                            <td>
                                                ₹{{ number_format((float) $product->price, 2) }}
                                            </td>
                                            <td>
                                                @if ($product->sale_price)
                                                    <span style="color: var(--success); font-weight: 600;">₹{{ number_format((float) $product->sale_price, 2) }}</span>
                                                @else
                                                    <span class="muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($product->shipping_type === 'free')
                                                    <span class="admin-badge success">Free</span>
                                                @elseif($product->shipping_type === 'custom')
                                                    <span class="admin-badge">₹{{ number_format((float) $product->shipping_fee, 2) }}</span>
                                                @else
                                                    <span class="admin-badge muted">Global</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                                    <span class="admin-badge {{ $product->amazon_link ? 'primary' : 'muted' }}">{{ $product->amazon_link ? 'Linked' : 'No link' }}</span>
                                                    @if($product->amazon_link)
                                                        <span class="admin-badge {{ $product->amazon_button_enabled ? 'success' : 'muted' }}">{{ $product->amazon_button_enabled ? 'Btn On' : 'Btn Off' }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                                    <span class="admin-badge {{ $product->is_active ? 'success' : 'muted' }}">{{ $product->is_active ? 'Active' : 'Hidden' }}</span>
                                                    <span class="admin-badge {{ $product->is_featured ? 'primary' : 'muted' }}">{{ $product->is_featured ? 'Featured' : 'Std' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="button-row admin-row-actions">
                                                    <button class="button small secondary quick-edit-btn" 
                                                        data-product="{{ json_encode([
                                                            'id' => $product->id,
                                                            'name' => $product->name,
                                                            'category_id' => $product->category_id,
                                                            'stock' => $product->stock,
                                                            'price' => $product->price,
                                                            'sale_price' => $product->sale_price,
                                                            'shipping_type' => $product->shipping_type,
                                                            'shipping_fee' => $product->shipping_fee,
                                                            'is_active' => $product->is_active,
                                                            'is_featured' => $product->is_featured,
                                                            'amazon_button_enabled' => $product->amazon_button_enabled
                                                        ]) }}">
                                                        <i class="bi bi-lightning"></i>
                                                        <span>Quick Edit</span>
                                                    </button>
                                                    <a class="button small secondary" href="{{ route('admin.products.edit', $product) }}" title="Full Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Remove this product?')" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="button danger small" type="submit" title="Delete">
                                                            <i class="bi bi-trash3"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="muted text-center" style="padding: 30px;">No products found for this filter.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <!-- Tab 2: Add Product Form -->
                <div class="admin-tab-panel" id="panel-add" data-tab-panel-group="main-tabs">
                    <form method="POST" action="{{ route('admin.products.store') }}" data-auto-seo-form enctype="multipart/form-data">
                        @csrf
                        <div class="form-split-grid">
                            
                            <!-- Left Column: Tabbed fields -->
                            <div class="admin-card" style="padding: 24px; background: var(--panel); border: 1px solid var(--border);">
                                
                                <div class="admin-subtabs-nav">
                                    <button type="button" class="admin-subtab-btn active" data-tab-target="subpanel-add-general" data-tab-group="add-tabs">General</button>
                                    <button type="button" class="admin-subtab-btn" data-tab-target="subpanel-add-shipping" data-tab-group="add-tabs">Shipping</button>
                                    <button type="button" class="admin-subtab-btn" data-tab-target="subpanel-add-content" data-tab-group="add-tabs">Content & SEO</button>
                                    <button type="button" class="admin-subtab-btn" data-tab-target="subpanel-add-photos" data-tab-group="add-tabs">Photos</button>
                                </div>

                                <!-- Subpanel 1: General Info -->
                                <div class="admin-tab-panel active" id="subpanel-add-general" data-tab-panel-group="add-tabs">
                                    <div class="form-grid">
                                        <div class="field">
                                            <label>Category</label>
                                            <select name="category_id" id="add-category-select">
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="field">
                                            <label>Product Name</label>
                                            <input name="name" id="add-name-input" placeholder="e.g. Handmade Brass Ganesha" data-meta-title-source required />
                                        </div>
                                        <div class="field">
                                            <label>SKU</label>
                                            <input name="sku" placeholder="e.g. BR-GAN-01" />
                                        </div>
                                        <div class="field">
                                            <label>Material</label>
                                            <input name="material" placeholder="e.g. Solid Brass" />
                                        </div>
                                        <div class="field">
                                            <label>Size Label</label>
                                            <input name="size_label" placeholder="e.g. Medium / 12 x 8 x 16 cm" />
                                        </div>
                                        <div class="field">
                                            <label>Price (INR)</label>
                                            <input name="price" type="number" step="0.01" id="add-price-input" placeholder="0.00" required />
                                        </div>
                                        <div class="field">
                                            <label>Sale Price (INR)</label>
                                            <input name="sale_price" type="number" step="0.01" id="add-saleprice-input" placeholder="0.00 (Optional)" />
                                        </div>
                                        <div class="field">
                                            <label>Stock Qty</label>
                                            <input name="stock" type="number" min="0" value="0" />
                                        </div>
                                        <div class="field" style="grid-column: 1 / -1;">
                                            <label>Amazon Link</label>
                                            <input name="amazon_link" type="url" placeholder="https://www.amazon.in/dp/ASIN" />
                                            <div class="admin-help">Optional marketplace destination. The button stays off until you enable it.</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Subpanel 2: Shipping -->
                                <div class="admin-tab-panel" id="subpanel-add-shipping" data-tab-panel-group="add-tabs">
                                    <div class="form-grid">
                                        <div class="field">
                                            <label>Weight</label>
                                            <input name="weight" type="number" step="0.001" value="0" />
                                        </div>
                                        <div class="field">
                                            <label>Weight Unit</label>
                                            <select name="weight_unit">
                                                <option value="kg" selected>kg</option>
                                                <option value="g">g</option>
                                            </select>
                                        </div>
                                        <div class="field">
                                            <label>Length</label>
                                            <input name="length" type="number" step="0.01" placeholder="0.00" />
                                        </div>
                                        <div class="field">
                                            <label>Width</label>
                                            <input name="width" type="number" step="0.01" placeholder="0.00" />
                                        </div>
                                        <div class="field">
                                            <label>Height</label>
                                            <input name="height" type="number" step="0.01" placeholder="0.00" />
                                        </div>
                                        <div class="field">
                                            <label>Dimension Unit</label>
                                            <select name="dimension_unit">
                                                <option value="cm" selected>cm</option>
                                                <option value="in">in</option>
                                                <option value="mm">mm</option>
                                            </select>
                                        </div>
                                        <div class="field">
                                            <label>Delivery Rule</label>
                                            <select name="shipping_type">
                                                <option value="default" selected>Use store-wide shipping</option>
                                                <option value="custom">Set product-specific charge</option>
                                                <option value="free">Always free delivery</option>
                                            </select>
                                        </div>
                                        <div class="field">
                                            <label>Delivery Charge (INR)</label>
                                            <input name="shipping_fee" type="number" step="0.01" value="0" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Subpanel 3: Content & SEO -->
                                <div class="admin-tab-panel" id="subpanel-add-content" data-tab-panel-group="add-tabs">
                                    <div class="field">
                                        <label>Short Description</label>
                                        <textarea name="short_desc" data-meta-desc-source placeholder="Brief summary of the product..." style="min-height: 80px;"></textarea>
                                    </div>
                                    <div class="field">
                                        <label>Description</label>
                                        <textarea name="description" placeholder="Detailed product specifications and history..." style="min-height: 120px;"></textarea>
                                    </div>
                                    <div class="form-grid" style="margin-top: 16px;">
                                        <div class="field">
                                            <label>Video URL</label>
                                            <input name="video_url" placeholder="YouTube or video direct link" />
                                        </div>
                                        <div class="field">
                                            <label>Meta Title</label>
                                            <input name="meta_title" data-meta-title-target placeholder="SEO title (Optional)" />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label>Meta Description</label>
                                        <input name="meta_desc" data-meta-desc-target placeholder="SEO description (Optional)" />
                                    </div>
                                </div>

                                <!-- Subpanel 4: Photos -->
                                <div class="admin-tab-panel" id="subpanel-add-photos" data-tab-panel-group="add-tabs">
                                    <p class="muted" style="margin-bottom: 16px;">Add up to 8 photos. You can paste image URLs or upload files. Uploaded files take priority.</p>
                                    <div class="media-slot-grid">
                                        @for ($slot = 0; $slot < 8; $slot++)
                                            <div class="media-slot-card">
                                                <strong>Photo {{ $slot + 1 }}</strong>
                                                <input name="image_urls[]" class="add-image-url-input" data-slot="{{ $slot }}" placeholder="Image URL" />
                                                <input type="file" name="image_uploads[{{ $slot }}]" accept="image/*" />
                                            </div>
                                        @endfor
                                    </div>
                                </div>

                            </div>

                            <!-- Right Column: Sticky actions & storefront preview -->
                            <div class="sticky-sidebar">
                                <div class="admin-card" style="padding: 24px; border: 1px solid var(--border);">
                                    <h4 style="margin: 0 0 16px; font-size: 16px;">Publish Status</h4>
                                    
                                    <div style="display: grid; gap: 14px; margin-bottom: 20px;">
                                        <label class="checkbox-row">
                                            <input type="checkbox" name="is_active" id="add-active-checkbox" value="1" checked>
                                            <div>
                                                <strong>Active on Store</strong>
                                                <p style="margin: 2px 0 0; font-size: 11px; color: var(--text-soft); font-weight: normal;">Visible to customers on frontend</p>
                                            </div>
                                        </label>
                                    <label class="checkbox-row" style="margin-top: 8px;">
                                        <input type="checkbox" name="is_featured" id="add-featured-checkbox" value="1">
                                        <div>
                                            <strong>Featured Product</strong>
                                            <p style="margin: 2px 0 0; font-size: 11px; color: var(--text-soft); font-weight: normal;">Display in homepage featured collections</p>
                                        </div>
                                    </label>
                                    <label class="checkbox-row" style="margin-top: 8px;">
                                        <input type="checkbox" name="amazon_button_enabled" id="add-amazon-button-checkbox" value="1">
                                        <div>
                                            <strong>Enable Amazon Button</strong>
                                            <p style="margin: 2px 0 0; font-size: 11px; color: var(--text-soft); font-weight: normal;">Off by default. Turns on Buy on Amazon only when a link exists.</p>
                                        </div>
                                    </label>
                                </div>

                                    <div style="display: grid; gap: 10px;">
                                        <button class="button" type="submit">
                                            <i class="bi bi-plus-circle"></i>
                                            <span>Create Product</span>
                                        </button>
                                        <button class="button secondary" type="button" data-tab-target="panel-list" data-tab-group="main-tabs" data-tab-value="list">
                                            Cancel
                                        </button>
                                    </div>
                                </div>

                                <!-- Card 2: Interactive Storefront Preview -->
                                <div class="admin-card" style="padding: 20px; background: #fafafa; border: 1px solid var(--border);">
                                    <h4 style="margin: 0 0 12px; font-size: 13px; color: var(--text-soft); text-transform: uppercase; letter-spacing: 0.05em;">Storefront Preview</h4>
                                    
                                    <div class="preview-product-card">
                                        <div class="preview-img-container">
                                            <img id="add-preview-image" src="" alt="Preview" style="display: none;">
                                            <div id="add-preview-placeholder" style="display: grid; place-items: center; color: var(--text-soft); font-size: 24px;">
                                                <i class="bi bi-image"></i>
                                            </div>
                                            <span id="add-preview-badge" class="preview-badge-featured" style="display: none;">Featured</span>
                                        </div>
                                        <div style="padding: 14px; background: #fff;">
                                            <small id="add-preview-category" style="font-size: 10px; text-transform: uppercase; color: var(--primary); font-weight: 700;">Category</small>
                                            <h5 id="add-preview-name" style="margin: 4px 0 8px; font-size: 14px; font-weight: 700; color: var(--heading); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Product Name</h5>
                                            <div style="display: flex; align-items: baseline; gap: 8px;">
                                                <strong id="add-preview-price" style="font-size: 15px; color: var(--heading);">₹0.00</strong>
                                                <span id="add-preview-old-price" style="font-size: 12px; text-decoration: line-through; color: var(--text-soft); display: none;"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>

            </div>
        </main>
    </div>

    <!-- Quick Edit Modal -->
    <div class="admin-modal-overlay" id="quick-edit-modal">
        <div class="admin-modal-content">
            <div class="admin-modal-header">
                <h3 id="qe-title">Quick Edit</h3>
                <button type="button" class="admin-modal-close" onclick="closeQuickEdit()">&times;</button>
            </div>
            <form id="qe-form" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="admin-modal-body">
                    <div class="form-grid">
                        <div class="field">
                            <label>Category</label>
                            <select name="category_id" id="qe-category_id">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Stock Qty</label>
                            <input type="number" name="stock" id="qe-stock" min="0" required />
                        </div>
                        <div class="field">
                            <label>Price (INR)</label>
                            <input type="number" name="price" id="qe-price" step="0.01" min="0" required />
                        </div>
                        <div class="field">
                            <label>Sale Price (INR)</label>
                            <input type="number" name="sale_price" id="qe-sale_price" step="0.01" min="0" />
                        </div>
                        <div class="field">
                            <label>Delivery Rule</label>
                            <select name="shipping_type" id="qe-shipping_type">
                                <option value="default">Global rule</option>
                                <option value="custom">Custom charge</option>
                                <option value="free">Free delivery</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Delivery Charge (INR)</label>
                            <input type="number" name="shipping_fee" id="qe-shipping_fee" step="0.01" min="0" />
                        </div>
                    </div>
                    
                    <h4 style="margin: 24px 0 12px; font-size: 14px;">Publish Status</h4>
                    <div style="display: grid; gap: 12px;">
                        <label class="checkbox-row compact">
                            <input type="checkbox" name="is_active" id="qe-is_active" value="1">
                            <span>Active on Store</span>
                        </label>
                        <label class="checkbox-row compact">
                            <input type="checkbox" name="is_featured" id="qe-is_featured" value="1">
                            <span>Featured Product</span>
                        </label>
                        <label class="checkbox-row compact">
                            <input type="checkbox" name="amazon_button_enabled" id="qe-amazon_button_enabled" value="1">
                            <span>Enable Amazon Button</span>
                        </label>
                    </div>
                </div>
                <div class="admin-modal-footer">
                    <button type="button" class="button secondary" onclick="closeQuickEdit()">Cancel</button>
                    <button type="submit" class="button">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        (() => {
            // --- Quick Edit Modal Logic ---
            const quickEditModal = document.getElementById('quick-edit-modal');
            const qeForm = document.getElementById('qe-form');
            const qeTitle = document.getElementById('qe-title');
            
            document.querySelectorAll('.quick-edit-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const product = JSON.parse(btn.getAttribute('data-product'));
                    
                    qeTitle.textContent = `Quick Edit: ${product.name}`;
                    qeForm.action = `/admin/products/${product.id}`;
                    
                    document.getElementById('qe-category_id').value = product.category_id || '';
                    document.getElementById('qe-stock').value = product.stock || 0;
                    document.getElementById('qe-price').value = product.price || '';
                    document.getElementById('qe-sale_price').value = product.sale_price || '';
                    document.getElementById('qe-shipping_type').value = product.shipping_type || 'default';
                    document.getElementById('qe-shipping_fee').value = product.shipping_fee || 0;
                    
                    document.getElementById('qe-is_active').checked = Boolean(product.is_active);
                    document.getElementById('qe-is_featured').checked = Boolean(product.is_featured);
                    document.getElementById('qe-amazon_button_enabled').checked = Boolean(product.amazon_button_enabled);
                    
                    quickEditModal.classList.add('active');
                });
            });

            window.closeQuickEdit = function() {
                quickEditModal.classList.remove('active');
            };

            // Close modal on outside click
            quickEditModal.addEventListener('click', (e) => {
                if (e.target === quickEditModal) {
                    closeQuickEdit();
                }
            });

            // --- Tabs switcher logic ---
            document.querySelectorAll('[data-tab-target]').forEach(tabBtn => {
                tabBtn.addEventListener('click', () => {
                    const group = tabBtn.getAttribute('data-tab-group');
                    const target = tabBtn.getAttribute('data-tab-target');

                    // Deactivate all buttons in this group
                    document.querySelectorAll(`[data-tab-group="${group}"]`).forEach(btn => {
                        btn.classList.remove('active');
                    });

                    // Deactivate all panels in this group
                    document.querySelectorAll(`[data-tab-panel-group="${group}"]`).forEach(panel => {
                        panel.classList.remove('active');
                    });

                    // Activate current
                    tabBtn.classList.add('active');
                    const targetPanel = document.getElementById(target);
                    if (targetPanel) {
                        targetPanel.classList.add('active');
                    }

                    // Update URL hash for main tabs
                    if (group === 'main-tabs') {
                        const val = tabBtn.getAttribute('data-tab-value');
                        history.replaceState(null, null, '#' + val);
                    }
                });
            });

            // Auto-activate tab based on Hash or validation Errors
            const hash = window.location.hash;
            const hasErrors = @json($errors->any());
            if (hash === '#add' || hash === '#create-product' || hasErrors) {
                const addBtn = document.querySelector('[data-tab-group="main-tabs"][data-tab-value="add"]');
                if (addBtn) addBtn.click();
            }

            // Listen to hash changes in case button is clicked
            window.addEventListener('hashchange', () => {
                if (window.location.hash === '#add' || window.location.hash === '#create-product') {
                    const addBtn = document.querySelector('[data-tab-group="main-tabs"][data-tab-value="add"]');
                    if (addBtn) addBtn.click();
                } else if (window.location.hash === '#list') {
                    const listBtn = document.querySelector('[data-tab-group="main-tabs"][data-tab-value="list"]');
                    if (listBtn) listBtn.click();
                }
            });

            // --- Live storefront preview logic ---
            const addNameInput = document.getElementById('add-name-input');
            const addPreviewName = document.getElementById('add-preview-name');
            if (addNameInput && addPreviewName) {
                addNameInput.addEventListener('input', (e) => {
                    addPreviewName.textContent = e.target.value.trim() || 'Product Name';
                });
            }

            const addPriceInput = document.getElementById('add-price-input');
            const addSalePriceInput = document.getElementById('add-saleprice-input');
            const addPreviewPrice = document.getElementById('add-preview-price');
            const addPreviewOldPrice = document.getElementById('add-preview-old-price');

            function updateAddPreviewPrice() {
                const price = parseFloat(addPriceInput.value) || 0;
                const salePrice = parseFloat(addSalePriceInput.value) || 0;
                if (salePrice > 0 && salePrice < price) {
                    addPreviewPrice.textContent = '₹' + salePrice.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    addPreviewOldPrice.textContent = '₹' + price.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    addPreviewOldPrice.style.display = 'inline';
                } else {
                    addPreviewPrice.textContent = price > 0 
                        ? '₹' + price.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) 
                        : '₹0.00';
                    addPreviewOldPrice.style.display = 'none';
                }
            }
            if (addPriceInput) addPriceInput.addEventListener('input', updateAddPreviewPrice);
            if (addSalePriceInput) addSalePriceInput.addEventListener('input', updateAddPreviewPrice);

            const addCategorySelect = document.getElementById('add-category-select');
            const addPreviewCategory = document.getElementById('add-preview-category');
            if (addCategorySelect && addPreviewCategory) {
                const updateCategoryPreview = () => {
                    addPreviewCategory.textContent = addCategorySelect.options[addCategorySelect.selectedIndex].text;
                };
                addCategorySelect.addEventListener('change', updateCategoryPreview);
                updateCategoryPreview(); // Initial load
            }

            const addFeaturedCheckbox = document.getElementById('add-featured-checkbox');
            const addPreviewBadge = document.getElementById('add-preview-badge');
            if (addFeaturedCheckbox && addPreviewBadge) {
                const updateBadge = () => {
                    addPreviewBadge.style.display = addFeaturedCheckbox.checked ? 'inline-block' : 'none';
                };
                addFeaturedCheckbox.addEventListener('change', updateBadge);
                updateBadge(); // Initial load
            }

            // Sync first non-empty Image URL slot to preview
            const addPreviewImage = document.getElementById('add-preview-image');
            const addPreviewPlaceholder = document.getElementById('add-preview-placeholder');
            const imageUrlInputs = document.querySelectorAll('.add-image-url-input');

            function updateAddPreviewImage() {
                let foundUrl = '';
                for (let i = 0; i < imageUrlInputs.length; i++) {
                    if (imageUrlInputs[i].value.trim() !== '') {
                        foundUrl = imageUrlInputs[i].value.trim();
                        break;
                    }
                }
                if (foundUrl) {
                    addPreviewImage.src = foundUrl;
                    addPreviewImage.style.display = 'block';
                    addPreviewPlaceholder.style.display = 'none';
                } else {
                    addPreviewImage.style.display = 'none';
                    addPreviewPlaceholder.style.display = 'grid';
                }
            }
            imageUrlInputs.forEach(input => {
                input.addEventListener('input', updateAddPreviewImage);
            });

            // --- SEO Helpers Logic ---
            const limitText = (value, max) => value.trim().replace(/\s+/g, ' ').slice(0, max);

            document.querySelectorAll('[data-auto-seo-form]').forEach((form) => {
                const metaTitleSource = form.querySelector('[data-meta-title-source]');
                const metaTitleTarget = form.querySelector('[data-meta-title-target]');
                const metaDescSource = form.querySelector('[data-meta-desc-source]');
                const metaDescTarget = form.querySelector('[data-meta-desc-target]');

                if (metaTitleSource && metaTitleTarget) {
                    const initialAutoTitle = limitText(metaTitleSource.value || '', 200);
                    let titleManual = Boolean(metaTitleTarget.value) && metaTitleTarget.value !== initialAutoTitle;

                    metaTitleTarget.addEventListener('input', () => {
                        titleManual = true;
                    });

                    metaTitleSource.addEventListener('input', () => {
                        if (!titleManual) {
                            metaTitleTarget.value = limitText(metaTitleSource.value || '', 200);
                        }
                    });
                }

                if (metaDescSource && metaDescTarget) {
                    const initialAutoDescription = limitText(metaDescSource.value || '', 320);
                    let descriptionManual =
                        Boolean(metaDescTarget.value) && metaDescTarget.value !== initialAutoDescription;

                    metaDescTarget.addEventListener('input', () => {
                        descriptionManual = true;
                    });

                    metaDescSource.addEventListener('input', () => {
                        if (!descriptionManual) {
                            metaDescTarget.value = limitText(metaDescSource.value || '', 320);
                        }
                    });
                }
            });
        })();
    </script>
@endpush
