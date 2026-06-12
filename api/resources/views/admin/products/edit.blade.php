@extends('admin.layout')

@section('title', 'Edit Product')

@section('content')
    <style>
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

        /* Tabs CSS */
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .admin-subtab-btn:hover {
            color: var(--heading);
        }
        .admin-subtab-btn.active {
            background: #fff;
            color: var(--primary);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.05);
        }
        .admin-tab-panel {
            display: none;
        }
        .admin-tab-panel.active {
            display: block;
        }

        /* Photo Cards Grid */
        .media-slot-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }
        .media-slot-card {
            display: grid;
            gap: 8px;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #f8fafc;
        }
        .admin-upload-preview {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: #eef2f7;
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
                        <div class="brand">Catalog</div>
                        <h2>Edit Product</h2>
                        <p class="lead" style="margin-top:8px;">Update product details, media, pricing, stock, and storefront visibility.</p>
                    </div>
                    <a href="{{ route('admin.products.index') }}" class="button secondary small">Back</a>
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

                @php
                    $images = old('image_urls', $product->images ?? []);
                @endphp

                <form method="POST" action="{{ route('admin.products.update', $product) }}" data-auto-seo-form enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-split-grid">
                        
                        <!-- Left Column: Tabbed fields -->
                        <div class="admin-card" style="padding: 24px; background: var(--panel); border: 1px solid var(--border);">
                            
                            <div class="admin-subtabs-nav">
                                <button type="button" class="admin-subtab-btn active" data-tab-target="subpanel-edit-general" data-tab-group="edit-tabs">
                                    <i class="bi bi-info-circle"></i> General
                                </button>
                                <button type="button" class="admin-subtab-btn" data-tab-target="subpanel-edit-shipping" data-tab-group="edit-tabs">
                                    <i class="bi bi-truck"></i> Shipping
                                </button>
                                <button type="button" class="admin-subtab-btn" data-tab-target="subpanel-edit-content" data-tab-group="edit-tabs">
                                    <i class="bi bi-file-text"></i> Content & SEO
                                </button>
                                <button type="button" class="admin-subtab-btn" data-tab-target="subpanel-edit-photos" data-tab-group="edit-tabs">
                                    <i class="bi bi-images"></i> Photos
                                </button>
                            </div>

                            <!-- Subpanel 1: General Info -->
                            <div class="admin-tab-panel active" id="subpanel-edit-general" data-tab-panel-group="edit-tabs">
                                <div class="form-grid">
                                    <div class="field">
                                        <label>Category</label>
                                        <select name="category_id" id="edit-category-select">
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) === $category->id)>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label>Product Name</label>
                                        <input name="name" id="edit-name-input" value="{{ old('name', $product->name) }}" data-meta-title-source required />
                                    </div>
                                    <div class="field">
                                        <label>Slug</label>
                                        <input value="{{ $product->slug ?: 'Auto generated from product name' }}" disabled />
                                    </div>
                                    <div class="field">
                                        <label>SKU</label>
                                        <input name="sku" value="{{ old('sku', $product->sku) }}" />
                                    </div>
                                    <div class="field">
                                        <label>Material</label>
                                        <input name="material" value="{{ old('material', $product->material) }}" placeholder="e.g. Solid Brass" />
                                    </div>
                                    <div class="field">
                                        <label>Size Label</label>
                                        <input name="size_label" value="{{ old('size_label', $product->size_label) }}" placeholder="e.g. Medium / 12 x 8 x 16 cm" />
                                    </div>
                                    <div class="field">
                                        <label>Price (INR)</label>
                                        <input name="price" type="number" step="0.01" id="edit-price-input" value="{{ old('price', $product->price) }}" required />
                                    </div>
                                    <div class="field">
                                        <label>Sale Price (INR)</label>
                                        <input name="sale_price" type="number" step="0.01" id="edit-saleprice-input" value="{{ old('sale_price', $product->sale_price) }}" placeholder="Optional" />
                                    </div>
                                    <div class="field">
                                        <label>Stock Qty</label>
                                        <input name="stock" type="number" min="0" value="{{ old('stock', $product->stock) }}" />
                                    </div>
                                </div>
                            </div>

                            <!-- Subpanel 2: Shipping -->
                            <div class="admin-tab-panel" id="subpanel-edit-shipping" data-tab-panel-group="edit-tabs">
                                <div class="form-grid">
                                    <div class="field">
                                        <label>Weight</label>
                                        <input name="weight" type="number" step="0.001" value="{{ old('weight', $product->weight) }}" placeholder="e.g. 1.25" />
                                    </div>
                                    <div class="field">
                                        <label>Weight Unit</label>
                                        <select name="weight_unit">
                                            <option value="">Select unit</option>
                                            <option value="kg" @selected(old('weight_unit', $product->weight_unit ?? 'kg') === 'kg')>kg</option>
                                            <option value="g" @selected(old('weight_unit', $product->weight_unit) === 'g')>g</option>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label>Length</label>
                                        <input name="length" type="number" step="0.01" value="{{ old('length', $product->length) }}" placeholder="e.g. 12.50" />
                                    </div>
                                    <div class="field">
                                        <label>Width</label>
                                        <input name="width" type="number" step="0.01" value="{{ old('width', $product->width) }}" placeholder="e.g. 8.00" />
                                    </div>
                                    <div class="field">
                                        <label>Height</label>
                                        <input name="height" type="number" step="0.01" value="{{ old('height', $product->height) }}" placeholder="e.g. 16.20" />
                                    </div>
                                    <div class="field">
                                        <label>Dimension Unit</label>
                                        <select name="dimension_unit">
                                            <option value="">Select unit</option>
                                            <option value="cm" @selected(old('dimension_unit', $product->dimension_unit ?? 'cm') === 'cm')>cm</option>
                                            <option value="in" @selected(old('dimension_unit', $product->dimension_unit) === 'in')>in</option>
                                            <option value="mm" @selected(old('dimension_unit', $product->dimension_unit) === 'mm')>mm</option>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label>Delivery Rule</label>
                                        <select name="shipping_type">
                                            <option value="default" @selected(old('shipping_type', $product->shipping_type ?? 'default') === 'default')>Use store-wide shipping</option>
                                            <option value="custom" @selected(old('shipping_type', $product->shipping_type) === 'custom')>Set product-specific charge</option>
                                            <option value="free" @selected(old('shipping_type', $product->shipping_type) === 'free')>Always free delivery</option>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label>Delivery Charge (INR)</label>
                                        <input name="shipping_fee" type="number" step="0.01" value="{{ old('shipping_fee', $product->shipping_fee) }}" />
                                    </div>
                                </div>
                            </div>

                            <!-- Subpanel 3: Content & SEO -->
                            <div class="admin-tab-panel" id="subpanel-edit-content" data-tab-panel-group="edit-tabs">
                                <div class="field">
                                    <label>Short Description</label>
                                    <textarea name="short_desc" data-meta-desc-source placeholder="Brief summary of the product..." style="min-height: 80px;">{{ old('short_desc', $product->short_desc) }}</textarea>
                                </div>
                                <div class="field">
                                    <label>Description</label>
                                    <textarea name="description" placeholder="Detailed product specifications..." style="min-height: 120px;">{{ old('description', $product->description) }}</textarea>
                                </div>
                                <div class="form-grid" style="margin-top: 16px;">
                                    <div class="field">
                                        <label>Video URL</label>
                                        <input name="video_url" value="{{ old('video_url', $product->video_url) }}" placeholder="YouTube or video link" />
                                    </div>
                                    <div class="field">
                                        <label>Meta Title</label>
                                        <input name="meta_title" value="{{ old('meta_title', $product->meta_title) }}" data-meta-title-target placeholder="SEO title" />
                                    </div>
                                </div>
                                <div class="field">
                                    <label>Meta Description</label>
                                    <input name="meta_desc" value="{{ old('meta_desc', $product->meta_desc) }}" data-meta-desc-target placeholder="SEO description" />
                                </div>
                            </div>

                            <!-- Subpanel 4: Photos -->
                            <div class="admin-tab-panel" id="subpanel-edit-photos" data-tab-panel-group="edit-tabs">
                                <p class="muted" style="margin-bottom: 16px;">Each slot supports direct upload or URL. Upload replaces the URL for that slot.</p>
                                <div class="media-slot-grid">
                                    @for ($slot = 0; $slot < 8; $slot++)
                                        <div class="media-slot-card">
                                            <strong>Photo {{ $slot + 1 }}</strong>
                                            @if (!empty($images[$slot]))
                                                <img src="{{ $images[$slot] }}" alt="Product image {{ $slot + 1 }}" class="admin-upload-preview">
                                            @endif
                                            <input name="image_urls[]" value="{{ $images[$slot] ?? '' }}" class="edit-image-url-input" data-slot="{{ $slot }}" placeholder="Image URL" />
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
                                        <input type="checkbox" name="is_active" id="edit-active-checkbox" value="1" @checked(old('is_active', $product->is_active))>
                                        <div>
                                            <strong>Active on Store</strong>
                                            <p style="margin: 2px 0 0; font-size: 11px; color: var(--text-soft); font-weight: normal;">Visible to customers on frontend</p>
                                        </div>
                                    </label>
                                    <label class="checkbox-row" style="margin-top: 8px;">
                                        <input type="checkbox" name="is_featured" id="edit-featured-checkbox" value="1" @checked(old('is_featured', $product->is_featured))>
                                        <div>
                                            <strong>Featured Product</strong>
                                            <p style="margin: 2px 0 0; font-size: 11px; color: var(--text-soft); font-weight: normal;">Display in homepage featured collections</p>
                                        </div>
                                    </label>
                                </div>

                                <div style="display: grid; gap: 10px;">
                                    <button class="button" type="submit">
                                        <i class="bi bi-save"></i>
                                        <span>Save Product</span>
                                    </button>
                                    <a class="button secondary" href="{{ route('admin.products.index') }}">
                                        Cancel
                                    </a>
                                </div>
                            </div>

                            <!-- Card 2: Interactive Storefront Preview -->
                            <div class="admin-card" style="padding: 20px; background: #fafafa; border: 1px solid var(--border);">
                                <h4 style="margin: 0 0 12px; font-size: 13px; color: var(--text-soft); text-transform: uppercase; letter-spacing: 0.05em;">Storefront Preview</h4>
                                
                                <div class="preview-product-card">
                                    <div class="preview-img-container">
                                        <img id="edit-preview-image" src="" alt="Preview" style="display: none;">
                                        <div id="edit-preview-placeholder" style="display: grid; place-items: center; color: var(--text-soft); font-size: 24px;">
                                            <i class="bi bi-image"></i>
                                        </div>
                                        <span id="edit-preview-badge" class="preview-badge-featured" style="display: none;">Featured</span>
                                    </div>
                                    <div style="padding: 14px; background: #fff;">
                                        <small id="edit-preview-category" style="font-size: 10px; text-transform: uppercase; color: var(--primary); font-weight: 700;">Category</small>
                                        <h5 id="edit-preview-name" style="margin: 4px 0 8px; font-size: 14px; font-weight: 700; color: var(--heading); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Product Name</h5>
                                        <div style="display: flex; align-items: baseline; gap: 8px;">
                                            <strong id="edit-preview-price" style="font-size: 15px; color: var(--heading);">₹0.00</strong>
                                            <span id="edit-preview-old-price" style="font-size: 12px; text-decoration: line-through; color: var(--text-soft); display: none;"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </main>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            // --- Sub-tabs Switcher Logic ---
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
                });
            });

            // --- Live Storefront Preview Sync Logic ---
            const editNameInput = document.getElementById('edit-name-input');
            const editPreviewName = document.getElementById('edit-preview-name');
            if (editNameInput && editPreviewName) {
                const syncName = () => {
                    editPreviewName.textContent = editNameInput.value.trim() || 'Product Name';
                };
                editNameInput.addEventListener('input', syncName);
                syncName(); // Initial load
            }

            const editPriceInput = document.getElementById('edit-price-input');
            const editSalePriceInput = document.getElementById('edit-saleprice-input');
            const editPreviewPrice = document.getElementById('edit-preview-price');
            const editPreviewOldPrice = document.getElementById('edit-preview-old-price');

            function updateEditPreviewPrice() {
                const price = parseFloat(editPriceInput.value) || 0;
                const salePrice = parseFloat(editSalePriceInput.value) || 0;
                if (salePrice > 0 && salePrice < price) {
                    editPreviewPrice.textContent = '₹' + salePrice.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    editPreviewOldPrice.textContent = '₹' + price.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    editPreviewOldPrice.style.display = 'inline';
                } else {
                    editPreviewPrice.textContent = price > 0 
                        ? '₹' + price.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) 
                        : '₹0.00';
                    editPreviewOldPrice.style.display = 'none';
                }
            }
            if (editPriceInput) editPriceInput.addEventListener('input', updateEditPreviewPrice);
            if (editSalePriceInput) editSalePriceInput.addEventListener('input', updateEditPreviewPrice);
            updateEditPreviewPrice(); // Initial load

            const editCategorySelect = document.getElementById('edit-category-select');
            const editPreviewCategory = document.getElementById('edit-preview-category');
            if (editCategorySelect && editPreviewCategory) {
                const updateCategoryPreview = () => {
                    editPreviewCategory.textContent = editCategorySelect.options[editCategorySelect.selectedIndex].text;
                };
                editCategorySelect.addEventListener('change', updateCategoryPreview);
                updateCategoryPreview(); // Initial load
            }

            const editFeaturedCheckbox = document.getElementById('edit-featured-checkbox');
            const editPreviewBadge = document.getElementById('edit-preview-badge');
            if (editFeaturedCheckbox && editPreviewBadge) {
                const updateBadge = () => {
                    editPreviewBadge.style.display = editFeaturedCheckbox.checked ? 'inline-block' : 'none';
                };
                editFeaturedCheckbox.addEventListener('change', updateBadge);
                updateBadge(); // Initial load
            }

            // Sync first non-empty Image URL slot to preview
            const editPreviewImage = document.getElementById('edit-preview-image');
            const editPreviewPlaceholder = document.getElementById('edit-preview-placeholder');
            const imageUrlInputs = document.querySelectorAll('.edit-image-url-input');

            function updateEditPreviewImage() {
                let foundUrl = '';
                for (let i = 0; i < imageUrlInputs.length; i++) {
                    if (imageUrlInputs[i].value.trim() !== '') {
                        foundUrl = imageUrlInputs[i].value.trim();
                        break;
                    }
                }
                if (foundUrl) {
                    editPreviewImage.src = foundUrl;
                    editPreviewImage.style.display = 'block';
                    editPreviewPlaceholder.style.display = 'none';
                } else {
                    editPreviewImage.style.display = 'none';
                    editPreviewPlaceholder.style.display = 'grid';
                }
            }
            imageUrlInputs.forEach(input => {
                input.addEventListener('input', updateEditPreviewImage);
            });
            updateEditPreviewImage(); // Initial load

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
