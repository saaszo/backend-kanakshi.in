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
                            <table class="admin-data-table" style="min-width: 1160px;">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Sale Price</th>
                                        <th>Delivery</th>
                                        <th>Status</th>
                                        <th style="width: 220px;">Actions</th>
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
                                                <select name="category_id" form="product-update-{{ $product->id }}" class="table-input">
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}" @selected($product->category_id === $category->id)>{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input name="stock" type="number" min="0" value="{{ $product->stock }}" form="product-update-{{ $product->id }}" class="table-input" style="width: 70px;" />
                                            </td>
                                            <td>
                                                <input name="price" type="number" min="0" step="0.01" value="{{ $product->price }}" form="product-update-{{ $product->id }}" class="table-input" style="width: 90px;" />
                                            </td>
                                            <td>
                                                <input name="sale_price" type="number" min="0" step="0.01" value="{{ $product->sale_price }}" form="product-update-{{ $product->id }}" class="table-input" style="width: 90px;" />
                                            </td>
                                            <td>
                                                <div class="admin-status-stack">
                                                    <select name="shipping_type" form="product-update-{{ $product->id }}" class="table-input" style="margin-bottom: 4px;">
                                                        <option value="default" @selected(($product->shipping_type ?? 'default') === 'default')>Global rule</option>
                                                        <option value="custom" @selected($product->shipping_type === 'custom')>Custom charge</option>
                                                        <option value="free" @selected($product->shipping_type === 'free')>Free delivery</option>
                                                    </select>
                                                    <input
                                                        name="shipping_fee"
                                                        type="number"
                                                        min="0"
                                                        step="0.01"
                                                        value="{{ old('shipping_fee', $product->shipping_fee) }}"
                                                        form="product-update-{{ $product->id }}"
                                                        class="table-input"
                                                        placeholder="Charge"
                                                    />
                                                </div>
                                            </td>
                                            <td>
                                                <div class="admin-status-stack">
                                                    <div style="display: flex; gap: 4px; margin-bottom: 4px;">
                                                        <span class="admin-badge {{ $product->is_active ? 'success' : 'muted' }}">{{ $product->is_active ? 'Active' : 'Hidden' }}</span>
                                                        <span class="admin-badge {{ $product->is_featured ? 'primary' : 'muted' }}">{{ $product->is_featured ? 'Featured' : 'Std' }}</span>
                                                    </div>
                                                    <div class="admin-product-flags">
                                                        <label class="checkbox-row compact">
                                                            <input type="checkbox" name="is_active" value="1" form="product-update-{{ $product->id }}" @checked($product->is_active)>
                                                            <span>Active</span>
                                                        </label>
                                                        <label class="checkbox-row compact">
                                                            <input type="checkbox" name="is_featured" value="1" form="product-update-{{ $product->id }}" @checked($product->is_featured)>
                                                            <span>Featured</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="button-row admin-row-actions">
                                                    <a class="button secondary small" href="{{ route('admin.products.edit', $product) }}">
                                                        <i class="bi bi-pencil-square"></i>
                                                        <span>Edit</span>
                                                    </a>
                                                    <button class="button small" type="submit" form="product-update-{{ $product->id }}">
                                                        <i class="bi bi-check2-circle"></i>
                                                        <span>Save</span>
                                                    </button>
                                                    <button class="button danger small" type="submit" form="product-delete-{{ $product->id }}">
                                                        <i class="bi bi-trash3"></i>
                                                        <span>Delete</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="muted text-center" style="padding: 30px;">No products found for this filter.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div style="display:none;">
                            @foreach ($products as $product)
                                <form method="POST" action="{{ route('admin.products.update', $product) }}" id="product-update-{{ $product->id }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="name" value="{{ $product->name }}">
                                    <input type="hidden" name="sku" value="{{ $product->sku }}">
                                    <input type="hidden" name="short_desc" value="{{ $product->short_desc }}">
                                    <input type="hidden" name="description" value="{{ $product->description }}">
                                    <input type="hidden" name="video_url" value="{{ $product->video_url }}">
                                    <input type="hidden" name="meta_title" value="{{ $product->meta_title }}">
                                    <input type="hidden" name="meta_desc" value="{{ $product->meta_desc }}">
                                    <textarea name="images_input">{{ is_array($product->images) ? implode("\n", $product->images) : $product->images }}</textarea>
                                </form>

                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" id="product-delete-{{ $product->id }}" onsubmit="return confirm('Remove this product?')">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endforeach
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
@endsection

@push('scripts')
    <script>
        (() => {
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
