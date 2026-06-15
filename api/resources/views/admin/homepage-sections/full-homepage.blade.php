@extends('admin.layout')

@section('title', 'Full Homepage Editor')

@php
    $sectionToggles = [
        'collections_section_is_active' => ['label' => 'Collections', 'path' => 'collections.is_active', 'icon' => 'bi-grid-3x3-gap'],
        'occasions_section_is_active' => ['label' => 'Festival Categories', 'path' => 'occasions.is_active', 'icon' => 'bi-calendar-event'],
        'editorial_picks_section_is_active' => ['label' => 'Editorial Picks', 'path' => 'editorial_picks.is_active', 'icon' => 'bi-stars'],
        'about_brand_is_active' => ['label' => 'About Brand', 'path' => 'about_brand.is_active', 'icon' => 'bi-shop'],
        'founders_is_active' => ['label' => 'Founders Story', 'path' => 'founders.is_active', 'icon' => 'bi-people'],
        'testimonials_is_active' => ['label' => 'Testimonials', 'path' => 'testimonials.is_active', 'icon' => 'bi-chat-quote'],
        'newsletter_is_active' => ['label' => 'Newsletter Banner', 'path' => 'newsletter.is_active', 'icon' => 'bi-envelope-paper'],
        'instagram_is_active' => ['label' => 'Instagram Grid', 'path' => 'instagram.is_active', 'icon' => 'bi-instagram'],
        'stats_is_active' => ['label' => 'Stats Strip', 'path' => 'stats.is_active', 'icon' => 'bi-bar-chart-line'],
        'festive_edits_is_active' => ['label' => 'Festive Edits', 'path' => 'festive_edits.is_active', 'icon' => 'bi-gift'],
    ];
@endphp

@section('content')
    <style>
        .editor-container {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            gap: 24px;
            margin-top: 24px;
            align-items: start;
        }

        .tab-menu {
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            position: sticky;
            top: 24px;
            box-shadow: var(--shadow-soft);
        }

        .tab-menu-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--primary);
            padding: 4px 12px 10px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 8px;
        }

        .tab-btn {
            background: transparent;
            border: none;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            text-align: left;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-soft);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
        }

        .tab-btn:hover {
            background: var(--bg-soft);
            color: var(--heading);
        }

        .tab-btn.active {
            background: rgba(37, 99, 235, 0.08);
            color: var(--primary);
        }

        .tab-btn i {
            font-size: 16px;
            opacity: 0.8;
        }

        .tab-content-panel {
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            padding: 28px;
            box-shadow: var(--shadow-soft);
            min-height: 520px;
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
            animation: paneFadeIn 0.2s ease-out;
        }

        @keyframes paneFadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .section-header-block {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 24px;
        }

        .section-header-block h3 {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        /* Image Studio Custom Styles */
        .image-studio {
            display: flex;
            align-items: center;
            gap: 20px;
            background: var(--bg-soft);
            border: 1px dashed var(--border-strong);
            padding: 16px;
            border-radius: var(--radius-md);
            margin-top: 8px;
            transition: all 0.2s ease;
            width: 100%;
        }

        .image-studio:hover {
            border-color: var(--primary);
            background: #fff;
        }

        .studio-preview,
        .studio-preview-box {
            width: 130px;
            height: 130px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border);
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: inset 0 2px 8px rgba(0,0,0,0.03);
            flex-shrink: 0;
        }

        .studio-preview img,
        .studio-preview-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .studio-placeholder {
            color: var(--text-soft);
            font-size: 26px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .studio-placeholder span {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .studio-action-stack {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex-grow: 1;
            min-width: 0;
        }

        .studio-btn-row {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .studio-upload-label {
            background: #fff;
            border: 1px solid var(--border-strong);
            border-radius: 10px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 700;
            color: var(--heading);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin: 0;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(15,23,42,0.02);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .studio-upload-label:hover {
            background: var(--bg-soft);
            border-color: var(--primary);
            color: var(--primary-dark);
        }

        .studio-picker-btn {
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-shadow: 0 4px 12px var(--primary-glow);
            transition: all 0.2s ease;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .studio-picker-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            color: #fff;
        }

        /* Enforce vertical stack layout inside narrow cards to prevent layout squishing */
        .admin-card-styled .image-studio {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
            padding: 12px;
        }

        .admin-card-styled .studio-preview,
        .admin-card-styled .studio-preview-box {
            width: 100%;
            height: 140px;
        }

        .admin-card-styled .studio-btn-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            width: 100%;
        }

        .admin-card-styled .studio-upload-label,
        .admin-card-styled .studio-picker-btn {
            width: 100%;
            padding: 8px 4px;
            font-size: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 16px;
        }

        .admin-card-styled {
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            background: #fff;
            padding: 20px;
            box-shadow: 0 6px 18px rgba(15,23,42,0.02);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .admin-card-styled:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(15,23,42,0.05);
        }

        /* Media Modal Selection Overlay */
        .picker-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(6px);
            z-index: 3000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .picker-modal-backdrop.is-open {
            display: flex;
        }

        .picker-modal {
            width: min(880px, 100%);
            max-height: calc(100vh - 60px);
            background: #fff;
            border-radius: var(--radius-xl);
            box-shadow: 0 30px 70px rgba(15, 23, 42, 0.25);
            border: 1px solid rgba(15,23,42,0.08);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: pickerModalUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes pickerModalUp {
            from { transform: translateY(16px) scale(0.97); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }

        .picker-modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--bg-soft);
        }

        .picker-modal-header h3 {
            margin: 0;
            font-size: 19px;
            font-weight: 800;
        }

        .picker-modal-body {
            padding: 24px;
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .picker-modal-toolbar {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .picker-search-input {
            flex: 1;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid var(--border-strong);
        }

        .picker-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(118px, 1fr));
            gap: 16px;
            min-height: 260px;
        }

        .picker-tile {
            aspect-ratio: 1;
            border: 2px solid transparent;
            border-radius: var(--radius-md);
            overflow: hidden;
            background: var(--bg-soft);
            cursor: pointer;
            position: relative;
            box-shadow: 0 4px 10px rgba(0,0,0,0.02);
            transition: all 0.2s ease;
        }

        .picker-tile:hover {
            border-color: var(--primary);
            transform: scale(1.03);
            box-shadow: 0 8px 20px rgba(37,99,235,0.15);
        }

        .picker-tile img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .picker-tile-filename {
            position: absolute;
            inset: auto 0 0 0;
            background: rgba(15, 23, 42, 0.82);
            color: #fff;
            font-size: 10px;
            padding: 5px 8px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .picker-tile:hover .picker-tile-filename {
            opacity: 1;
        }

        .picker-modal-footer {
            padding: 18px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--bg-soft);
        }

        .picker-pagination {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .picker-pagination button {
            width: auto;
            min-height: 36px;
            padding: 0 12px;
            font-size: 13px;
        }

        .admin-savebar-sticky {
            position: fixed;
            bottom: 0;
            left: 300px;
            right: 0;
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(10px);
            border-top: 1px solid var(--border);
            padding: 16px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 1000;
            box-shadow: 0 -10px 30px rgba(15, 23, 42, 0.04);
        }

        @media (max-width: 991px) {
            .editor-container {
                grid-template-columns: 1fr;
            }
            .tab-menu {
                position: static;
                flex-direction: row;
                overflow-x: auto;
                padding: 10px;
            }
            .tab-menu-title {
                display: none;
            }
            .tab-btn {
                white-space: nowrap;
                padding: 10px 14px;
            }
            .admin-savebar-sticky {
                left: 0;
            }
        }

        @media (max-width: 600px) {
            .image-studio {
                flex-direction: column;
                align-items: stretch;
                gap: 14px;
            }

            .studio-preview,
            .studio-preview-box {
                width: 100%;
                max-width: none;
            }
        }
    </style>

    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main" style="padding-bottom: 100px;">
            <div class="admin-shell-grid">
                <div class="admin-banner">
                    <div>
                        <div class="brand">Homepage Configuration</div>
                        <h2>Full Homepage Editor</h2>
                        <p class="lead" style="margin-top:8px;">Design, enable, and upload media files for every block on the homepage circular menu, banners, editorial grid, and testimonial sliders.</p>
                    </div>
                    <div class="button-row">
                        <a href="{{ route('admin.homepage-sections.hero.edit') }}" class="button secondary small"><i class="bi bi-sliders"></i> Hero Slider</a>
                        <a href="{{ route('admin.homepage-products.index') }}" class="button secondary small"><i class="bi bi-grid-3x3"></i> Product Rails</a>
                        <a href="{{ route('admin.homepage-sections.index') }}" class="button secondary small"><i class="bi bi-list-task"></i> View All Sections</a>
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
                    <div class="admin-errors" style="margin-bottom: 24px; border-radius: var(--radius-md); padding: 16px; background: rgba(220, 38, 38, 0.08); border: 1px solid rgba(220, 38, 38, 0.15); color: var(--danger);">
                        <strong>Please fix the errors below to continue:</strong>
                        <ul style="margin: 8px 0 0; padding-left: 20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.homepage-sections.full.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="editor-container">
                        <!-- Navigation Sidebar -->
                        <aside class="tab-menu">
                            <div class="tab-menu-title">Homepage Sections</div>
                            <button type="button" class="tab-btn active" data-tab-target="general-pane"><i class="bi bi-gear-fill"></i> General Settings</button>
                            <button type="button" class="tab-btn" data-tab-target="collections-pane"><i class="bi bi-grid-3x3-gap-fill"></i> Collections</button>
                            <button type="button" class="tab-btn" data-tab-target="occasions-pane"><i class="bi bi-calendar-heart-fill"></i> Occasions</button>
                            <button type="button" class="tab-btn" data-tab-target="editorial-pane"><i class="bi bi-stars"></i> Editorial Picks</button>
                            <button type="button" class="tab-btn" data-tab-target="about-pane"><i class="bi bi-shop-window"></i> About Brand</button>
                            <button type="button" class="tab-btn" data-tab-target="founders-pane"><i class="bi bi-people-fill"></i> Founders Story</button>
                            <button type="button" class="tab-btn" data-tab-target="testimonials-pane"><i class="bi bi-chat-left-quote-fill"></i> Testimonials</button>
                            <button type="button" class="tab-btn" data-tab-target="newsletter-pane"><i class="bi bi-envelope-paper-fill"></i> Newsletter</button>
                            <button type="button" class="tab-btn" data-tab-target="instagram-pane"><i class="bi bi-instagram"></i> Instagram Grid</button>
                            <button type="button" class="tab-btn" data-tab-target="stats-pane"><i class="bi bi-bar-chart-line-fill"></i> Stats Strip</button>
                            <button type="button" class="tab-btn" data-tab-target="festive-pane"><i class="bi bi-gift-fill"></i> Festive Edits</button>
                        </aside>

                        <!-- Content Panels -->
                        <div class="tab-content-panel">
                            
                            <!-- 1. GENERAL SETTINGS -->
                            <div class="tab-pane active" id="general-pane">
                                <div class="section-header-block">
                                    <div>
                                        <h3>General Settings</h3>
                                        <p class="lead" style="margin: 4px 0 0;">Metadata and status toggles for homepage section blocks.</p>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div class="field"><label>Section Label</label><input name="label" value="{{ old('label', $section->label) }}" /></div>
                                    <div class="field"><label>Title</label><input name="title" value="{{ old('title', $section->title) }}" /></div>
                                    <div class="field"><label>Sort Order</label><input type="number" min="1" name="sort_order" value="{{ old('sort_order', $section->sort_order) }}" /></div>
                                </div>
                                <div class="form-grid one" style="margin-top: 16px;">
                                    <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $section->is_active))> <span>Full homepage settings enabled</span></label>
                                </div>
                                <hr style="margin: 24px 0; border-color: var(--border);">
                                <h4>Enable/Disable Individual Block Sections</h4>
                                <div class="form-grid" style="margin-top: 16px; gap: 14px;">
                                    @foreach ($sectionToggles as $field => $meta)
                                        <label class="checkbox-row" style="padding: 10px; background: var(--bg-soft); border: 1px solid var(--border); border-radius: 10px;">
                                            <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, data_get($config, $meta['path'])))> 
                                            <i class="bi {{ $meta['icon'] }}" style="margin-left: 6px; color: var(--primary);"></i>
                                            <span style="font-weight: 600; margin-left: 6px;">{{ $meta['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- 2. COLLECTIONS BLOCK -->
                            <div class="tab-pane" id="collections-pane">
                                <div class="section-header-block">
                                    <div>
                                        <h3>Collections Block</h3>
                                        <p class="lead" style="margin: 4px 0 0;">Manage category collection cards shown on the homepage.</p>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div class="field"><label>Eyebrow</label><input name="collections_eyebrow" value="{{ old('collections_eyebrow', $config['collections']['eyebrow']) }}" /></div>
                                    <div class="field"><label>Title</label><input name="collections_title" value="{{ old('collections_title', $config['collections']['title']) }}" /></div>
                                    <div class="field"><label>Button Text</label><input name="collections_button_text" value="{{ old('collections_button_text', $config['collections']['button_text']) }}" /></div>
                                    <div class="field"><label>Button URL</label><input name="collections_button_url" value="{{ old('collections_button_url', $config['collections']['button_url']) }}" /></div>
                                </div>
                                
                                <div class="card-grid">
                                    @foreach ($config['collections']['items'] as $index => $item)
                                        <div class="admin-card-styled">
                                            <h4 style="margin: 0 0 16px; font-weight: 700; color: var(--heading);">Collection Card {{ $index + 1 }}</h4>
                                            <div class="form-grid one" style="gap: 12px;">
                                                <div class="field" style="margin-bottom:0;"><label>Title</label><input name="collections[{{ $index }}][title]" value="{{ old("collections.$index.title", $item['title']) }}" /></div>
                                                <div class="field" style="margin-bottom:0;"><label>Subtitle</label><input name="collections[{{ $index }}][subtitle]" value="{{ old("collections.$index.subtitle", $item['subtitle']) }}" /></div>
                                                <div class="field" style="margin-bottom:0;"><label>Link URL</label><input name="collections[{{ $index }}][href]" value="{{ old("collections.$index.href", $item['href']) }}" /></div>
                                                
                                                <div class="field" style="margin-bottom:0;">
                                                    <label>Card Image</label>
                                                    <div class="image-studio">
                                                        <div class="studio-preview-box">
                                                            <img src="{{ $item['preview_image'] ?? $item['image'] }}" style="{{ empty($item['image']) ? 'display:none;' : '' }}" data-studio-preview>
                                                            <div class="studio-placeholder" style="{{ !empty($item['image']) ? 'display:none;' : '' }}" data-studio-placeholder>
                                                                <i class="bi bi-image"></i>
                                                                <span>No Image</span>
                                                            </div>
                                                        </div>
                                                        <div class="studio-action-stack">
                                                            <div class="studio-btn-row">
                                                                <label class="studio-upload-label">
                                                                    <i class="bi bi-upload"></i> Upload File
                                                                    <input type="file" name="collections_files[{{ $index }}]" accept="image/*" data-studio-file>
                                                                </label>
                                                                <button type="button" class="studio-picker-btn" data-trigger-picker><i class="bi bi-images"></i> Media Library</button>
                                                            </div>
                                                            <input type="text" name="collections[{{ $index }}][image]" value="{{ old("collections.$index.image", $item['image']) }}" placeholder="Or paste image URL" data-studio-url />
                                                            <label class="checkbox-row compact" style="margin-top: 4px; color: var(--danger);">
                                                                <input type="checkbox" name="clear_collections_image[{{ $index }}]" value="1" data-studio-clear> <span>Remove image</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- 3. FESTIVAL CATEGORIES (OCCASIONS) -->
                            <div class="tab-pane" id="occasions-pane">
                                <div class="section-header-block">
                                    <div>
                                        <h3>Festival Categories</h3>
                                        <p class="lead" style="margin: 4px 0 0;">Circular navigation options for festival shopping routes.</p>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div class="field"><label>Eyebrow</label><input name="occasions_eyebrow" value="{{ old('occasions_eyebrow', $config['occasions']['eyebrow']) }}" /></div>
                                    <div class="field"><label>Title</label><input name="occasions_title" value="{{ old('occasions_title', $config['occasions']['title']) }}" /></div>
                                </div>

                                <div class="card-grid">
                                    @foreach ($config['occasions']['items'] as $index => $item)
                                        <div class="admin-card-styled">
                                            <h4 style="margin: 0 0 16px; font-weight:700;">Occasion {{ $index + 1 }}</h4>
                                            <div class="form-grid one" style="gap: 12px;">
                                                <div class="field" style="margin-bottom:0;"><label>Title</label><input name="occasions[{{ $index }}][title]" value="{{ old("occasions.$index.title", $item['title']) }}" /></div>
                                                <div class="field" style="margin-bottom:0;"><label>Link URL</label><input name="occasions[{{ $index }}][href]" value="{{ old("occasions.$index.href", $item['href']) }}" /></div>
                                                
                                                <div class="field" style="margin-bottom:0;">
                                                    <label>Occasion Image</label>
                                                    <div class="image-studio">
                                                        <div class="studio-preview-box">
                                                            <img src="{{ $item['preview_image'] ?? $item['image'] }}" style="{{ empty($item['image']) ? 'display:none;' : '' }}" data-studio-preview>
                                                            <div class="studio-placeholder" style="{{ !empty($item['image']) ? 'display:none;' : '' }}" data-studio-placeholder>
                                                                <i class="bi bi-image"></i>
                                                                <span>No Image</span>
                                                            </div>
                                                        </div>
                                                        <div class="studio-action-stack">
                                                            <div class="studio-btn-row">
                                                                <label class="studio-upload-label">
                                                                    <i class="bi bi-upload"></i> Upload
                                                                    <input type="file" name="occasions_files[{{ $index }}]" accept="image/*" data-studio-file>
                                                                </label>
                                                                <button type="button" class="studio-picker-btn" data-trigger-picker><i class="bi bi-images"></i> Picker</button>
                                                            </div>
                                                            <input type="text" name="occasions[{{ $index }}][image]" value="{{ old("occasions.$index.image", $item['image']) }}" placeholder="Or paste image URL" data-studio-url />
                                                            <label class="checkbox-row compact" style="margin-top: 4px; color: var(--danger);">
                                                                <input type="checkbox" name="clear_occasions_image[{{ $index }}]" value="1" data-studio-clear> <span>Remove image</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- 4. EDITORIAL PICKS -->
                            <div class="tab-pane" id="editorial-pane">
                                <div class="section-header-block">
                                    <div>
                                        <h3>Editorial Picks</h3>
                                        <p class="lead" style="margin: 4px 0 0;">Highlight special curated edits or banners in the catalog grid.</p>
                                    </div>
                                </div>

                                <div class="card-grid">
                                    @foreach ($config['editorial_picks']['items'] as $index => $item)
                                        <div class="admin-card-styled">
                                            <h4 style="margin: 0 0 16px; font-weight:700;">Editorial Card {{ $index + 1 }}</h4>
                                            <div class="form-grid one" style="gap: 12px;">
                                                <div class="field" style="margin-bottom:0;"><label>Badge Text</label><input name="editorial_picks[{{ $index }}][badge]" value="{{ old("editorial_picks.$index.badge", $item['badge']) }}" /></div>
                                                <div class="field" style="margin-bottom:0;"><label>Title</label><input name="editorial_picks[{{ $index }}][title]" value="{{ old("editorial_picks.$index.title", $item['title']) }}" /></div>
                                                <div class="field" style="margin-bottom:0;"><label>Link URL</label><input name="editorial_picks[{{ $index }}][href]" value="{{ old("editorial_picks.$index.href", $item['href']) }}" /></div>
                                                <div class="field" style="margin-bottom:0;">
                                                    <label>Description Copy</label>
                                                    <textarea name="editorial_picks[{{ $index }}][description]" rows="2">{{ old("editorial_picks.$index.description", $item['description']) }}</textarea>
                                                </div>
                                                
                                                <div class="field" style="margin-bottom:0;">
                                                    <label>Card Image</label>
                                                    <div class="image-studio">
                                                        <div class="studio-preview-box">
                                                            <img src="{{ $item['preview_image'] ?? $item['image'] }}" style="{{ empty($item['image']) ? 'display:none;' : '' }}" data-studio-preview>
                                                            <div class="studio-placeholder" style="{{ !empty($item['image']) ? 'display:none;' : '' }}" data-studio-placeholder>
                                                                <i class="bi bi-image"></i>
                                                                <span>No Image</span>
                                                            </div>
                                                        </div>
                                                        <div class="studio-action-stack">
                                                            <div class="studio-btn-row">
                                                                <label class="studio-upload-label">
                                                                    <i class="bi bi-upload"></i> Upload File
                                                                    <input type="file" name="editorial_picks_files[{{ $index }}]" accept="image/*" data-studio-file>
                                                                </label>
                                                                <button type="button" class="studio-picker-btn" data-trigger-picker><i class="bi bi-images"></i> Pick Image</button>
                                                            </div>
                                                            <input type="text" name="editorial_picks[{{ $index }}][image]" value="{{ old("editorial_picks.$index.image", $item['image']) }}" placeholder="Or paste image URL" data-studio-url />
                                                            <label class="checkbox-row compact" style="margin-top: 4px; color: var(--danger);">
                                                                <input type="checkbox" name="clear_editorial_picks_image[{{ $index }}]" value="1" data-studio-clear> <span>Remove image</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- 5. ABOUT THE BRAND -->
                            <div class="tab-pane" id="about-pane">
                                <div class="section-header-block">
                                    <div>
                                        <h3>About Brand</h3>
                                        <p class="lead" style="margin: 4px 0 0;">Heritage story, supporting copy, and brand marketing banner image.</p>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div class="field"><label>Eyebrow</label><input name="about_brand_eyebrow" value="{{ old('about_brand_eyebrow', $config['about_brand']['eyebrow']) }}" /></div>
                                    <div class="field"><label>Title</label><input name="about_brand_title" value="{{ old('about_brand_title', $config['about_brand']['title']) }}" /></div>
                                    <div class="field"><label>Button Text</label><input name="about_brand_button_text" value="{{ old('about_brand_button_text', $config['about_brand']['button_text']) }}" /></div>
                                    <div class="field"><label>Button URL</label><input name="about_brand_button_url" value="{{ old('about_brand_button_url', $config['about_brand']['button_url']) }}" /></div>
                                </div>
                                <div class="form-grid one" style="margin-top: 16px;">
                                    <div class="field"><label>Paragraph One</label><textarea name="about_brand_paragraph_one" rows="3">{{ old('about_brand_paragraph_one', $config['about_brand']['paragraph_one']) }}</textarea></div>
                                    <div class="field"><label>Paragraph Two</label><textarea name="about_brand_paragraph_two" rows="3">{{ old('about_brand_paragraph_two', $config['about_brand']['paragraph_two']) }}</textarea></div>
                                </div>
                                <hr style="margin: 20px 0; border-color: var(--border);">
                                <div class="field">
                                    <label>About Brand Banner Image</label>
                                    <div class="image-studio" style="max-width: 680px;">
                                        <div class="studio-preview-box" style="width: 150px; height: 150px;">
                                            <img src="{{ $config['about_brand']['preview_image'] ?? $config['about_brand']['image'] }}" style="{{ empty($config['about_brand']['image']) ? 'display:none;' : '' }}" data-studio-preview>
                                            <div class="studio-placeholder" style="{{ !empty($config['about_brand']['image']) ? 'display:none;' : '' }}" data-studio-placeholder>
                                                <i class="bi bi-image"></i>
                                                <span>No Image</span>
                                            </div>
                                        </div>
                                        <div class="studio-action-stack">
                                            <div class="studio-btn-row">
                                                <label class="studio-upload-label">
                                                    <i class="bi bi-upload"></i> Upload Banner File
                                                    <input type="file" name="about_brand_file" accept="image/*" data-studio-file>
                                                </label>
                                                <button type="button" class="studio-picker-btn" data-trigger-picker><i class="bi bi-images"></i> Media Selector</button>
                                            </div>
                                            <input type="text" name="about_brand_image" value="{{ old('about_brand_image', $config['about_brand']['image']) }}" placeholder="Or paste image URL" data-studio-url />
                                            <label class="checkbox-row compact" style="margin-top: 4px; color: var(--danger);">
                                                <input type="checkbox" name="clear_about_brand_image" value="1" data-studio-clear> <span>Remove current banner</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 6. FOUNDERS STORY -->
                            <div class="tab-pane" id="founders-pane">
                                <div class="section-header-block">
                                    <div>
                                        <h3>Founders Story</h3>
                                        <p class="lead" style="margin: 4px 0 0;">Brand building blocks, artisan focus, main portrait, and side highlights.</p>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div class="field"><label>Eyebrow</label><input name="founders_eyebrow" value="{{ old('founders_eyebrow', $config['founders']['eyebrow']) }}" /></div>
                                    <div class="field"><label>Title</label><input name="founders_title" value="{{ old('founders_title', $config['founders']['title']) }}" /></div>
                                    <div class="field"><label>Button Text</label><input name="founders_button_text" value="{{ old('founders_button_text', $config['founders']['button_text']) }}" /></div>
                                    <div class="field"><label>Button URL</label><input name="founders_button_url" value="{{ old('founders_button_url', $config['founders']['button_url']) }}" /></div>
                                </div>
                                <div class="form-grid one" style="margin-top: 16px;">
                                    <div class="field"><label>Story Copy</label><textarea name="founders_content" rows="4">{{ old('founders_content', $config['founders']['content']) }}</textarea></div>
                                </div>
                                
                                <div class="form-grid" style="margin-top: 16px;">
                                    <!-- Main Image -->
                                    <div class="field">
                                        <label>Main Portrait Image</label>
                                        <div class="image-studio">
                                            <div class="studio-preview-box">
                                                <img src="{{ $config['founders']['preview_main_image'] ?? $config['founders']['main_image'] }}" style="{{ empty($config['founders']['main_image']) ? 'display:none;' : '' }}" data-studio-preview>
                                                <div class="studio-placeholder" style="{{ !empty($config['founders']['main_image']) ? 'display:none;' : '' }}" data-studio-placeholder>
                                                    <i class="bi bi-image"></i>
                                                    <span>No Image</span>
                                                </div>
                                            </div>
                                            <div class="studio-action-stack">
                                                <div class="studio-btn-row">
                                                    <label class="studio-upload-label">
                                                        <i class="bi bi-upload"></i> Upload
                                                        <input type="file" name="founders_main_file" accept="image/*" data-studio-file>
                                                    </label>
                                                    <button type="button" class="studio-picker-btn" data-trigger-picker><i class="bi bi-images"></i> Pick</button>
                                                </div>
                                                <input type="text" name="founders_main_image" value="{{ old('founders_main_image', $config['founders']['main_image']) }}" placeholder="Image URL" data-studio-url />
                                                <label class="checkbox-row compact" style="margin-top: 4px; color: var(--danger);">
                                                    <input type="checkbox" name="clear_founders_main_image" value="1" data-studio-clear> <span>Clear image</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Side Image -->
                                    <div class="field">
                                        <label>Side Feature Image</label>
                                        <div class="image-studio">
                                            <div class="studio-preview-box">
                                                <img src="{{ $config['founders']['preview_side_image'] ?? $config['founders']['side_image'] }}" style="{{ empty($config['founders']['side_image']) ? 'display:none;' : '' }}" data-studio-preview>
                                                <div class="studio-placeholder" style="{{ !empty($config['founders']['side_image']) ? 'display:none;' : '' }}" data-studio-placeholder>
                                                    <i class="bi bi-image"></i>
                                                    <span>No Image</span>
                                                </div>
                                            </div>
                                            <div class="studio-action-stack">
                                                <div class="studio-btn-row">
                                                    <label class="studio-upload-label">
                                                        <i class="bi bi-upload"></i> Upload
                                                        <input type="file" name="founders_side_file" accept="image/*" data-studio-file>
                                                    </label>
                                                    <button type="button" class="studio-picker-btn" data-trigger-picker><i class="bi bi-images"></i> Pick</button>
                                                </div>
                                                <input type="text" name="founders_side_image" value="{{ old('founders_side_image', $config['founders']['side_image']) }}" placeholder="Image URL" data-studio-url />
                                                <label class="checkbox-row compact" style="margin-top: 4px; color: var(--danger);">
                                                    <input type="checkbox" name="clear_founders_side_image" value="1" data-studio-clear> <span>Clear image</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 7. TESTIMONIALS (TEXT ONLY) -->
                            <div class="tab-pane" id="testimonials-pane">
                                <div class="section-header-block">
                                    <div>
                                        <h3>Testimonials</h3>
                                        <p class="lead" style="margin: 4px 0 0;">Customer quote slides and review summaries shown on the homepage.</p>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div class="field"><label>Eyebrow</label><input name="testimonials_eyebrow" value="{{ old('testimonials_eyebrow', $config['testimonials']['eyebrow']) }}" /></div>
                                    <div class="field"><label>Title</label><input name="testimonials_title" value="{{ old('testimonials_title', $config['testimonials']['title']) }}" /></div>
                                </div>

                                <div class="card-grid">
                                    @foreach ($config['testimonials']['items'] as $index => $item)
                                        <div class="admin-card-styled">
                                            <h4 style="margin: 0 0 14px; font-weight:700;">Testimonial {{ $index + 1 }}</h4>
                                            <div class="form-grid one" style="gap: 10px;">
                                                <div class="field" style="margin-bottom:0;"><label>Heading / Title</label><input name="testimonials[{{ $index }}][title]" value="{{ old("testimonials.$index.title", $item['title']) }}" /></div>
                                                <div class="field" style="margin-bottom:0;"><label>Author Name</label><input name="testimonials[{{ $index }}][author]" value="{{ old("testimonials.$index.author", $item['author']) }}" /></div>
                                                <div class="field" style="margin-bottom:0;"><label>Stars Rating</label><input name="testimonials[{{ $index }}][stars]" value="{{ old("testimonials.$index.stars", $item['stars']) }}" placeholder="★★★★★" /></div>
                                                <div class="field" style="margin-bottom:0;">
                                                    <label>Quote Content</label>
                                                    <textarea name="testimonials[{{ $index }}][quote]" rows="3">{{ old("testimonials.$index.quote", $item['quote']) }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- 8. NEWSLETTER BANNER (TEXT ONLY) -->
                            <div class="tab-pane" id="newsletter-pane">
                                <div class="section-header-block">
                                    <div>
                                        <h3>Newsletter Banner</h3>
                                        <p class="lead" style="margin: 4px 0 0;">Email subscription strip, description details, and coupon discount promo.</p>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div class="field"><label>Eyebrow</label><input name="newsletter_eyebrow" value="{{ old('newsletter_eyebrow', $config['newsletter']['eyebrow']) }}" /></div>
                                    <div class="field"><label>Title</label><input name="newsletter_title" value="{{ old('newsletter_title', $config['newsletter']['title']) }}" /></div>
                                    <div class="field"><label>Button Text</label><input name="newsletter_button_text" value="{{ old('newsletter_button_text', $config['newsletter']['button_text']) }}" /></div>
                                    <div class="field"><label>Email Input Placeholder</label><input name="newsletter_placeholder" value="{{ old('newsletter_placeholder', $config['newsletter']['placeholder']) }}" /></div>
                                </div>
                                <div class="form-grid one" style="margin-top: 16px;">
                                    <div class="field"><label>Description Text</label><textarea name="newsletter_description" rows="3">{{ old('newsletter_description', $config['newsletter']['description']) }}</textarea></div>
                                    <div class="field"><label>Footnote / Trust Badges Copy</label><textarea name="newsletter_footnote" rows="2">{{ old('newsletter_footnote', $config['newsletter']['footnote']) }}</textarea></div>
                                </div>
                            </div>

                            <!-- 9. INSTAGRAM GRID -->
                            <div class="tab-pane" id="instagram-pane">
                                <div class="section-header-block">
                                    <div>
                                        <h3>Instagram Grid</h3>
                                        <p class="lead" style="margin: 4px 0 0;">Social grid tiles linking to your official instagram page.</p>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div class="field"><label>Eyebrow</label><input name="instagram_eyebrow" value="{{ old('instagram_eyebrow', $config['instagram']['eyebrow']) }}" /></div>
                                    <div class="field"><label>Title</label><input name="instagram_title" value="{{ old('instagram_title', $config['instagram']['title']) }}" /></div>
                                    <div class="field"><label>Profile Label</label><input name="instagram_profile_label" value="{{ old('instagram_profile_label', $config['instagram']['profile_label']) }}" /></div>
                                    <div class="field"><label>Profile Handle Link URL</label><input name="instagram_profile_url" value="{{ old('instagram_profile_url', $config['instagram']['profile_url']) }}" /></div>
                                </div>

                                <div class="card-grid">
                                    @foreach ($config['instagram']['tiles'] as $index => $item)
                                        <div class="admin-card-styled">
                                            <h4 style="margin: 0 0 14px; font-weight:700;">Instagram Tile {{ $index + 1 }}</h4>
                                            <div class="form-grid one" style="gap: 12px;">
                                                <div class="field" style="margin-bottom:0;"><label>Alt Accessibility Text</label><input name="instagram[tiles][{{ $index }}][alt]" value="{{ old("instagram.tiles.$index.alt", $item['alt']) }}" /></div>
                                                
                                                <div class="field" style="margin-bottom:0;">
                                                    <label>Instagram Photo</label>
                                                    <div class="image-studio">
                                                        <div class="studio-preview-box">
                                                            <img src="{{ $item['preview_image'] ?? $item['image'] }}" style="{{ empty($item['image']) ? 'display:none;' : '' }}" data-studio-preview>
                                                            <div class="studio-placeholder" style="{{ !empty($item['image']) ? 'display:none;' : '' }}" data-studio-placeholder>
                                                                <i class="bi bi-image"></i>
                                                                <span>No Image</span>
                                                            </div>
                                                        </div>
                                                        <div class="studio-action-stack">
                                                            <div class="studio-btn-row">
                                                                <label class="studio-upload-label">
                                                                    <i class="bi bi-upload"></i> Upload
                                                                    <input type="file" name="instagram_tiles_files[{{ $index }}]" accept="image/*" data-studio-file>
                                                                </label>
                                                                <button type="button" class="studio-picker-btn" data-trigger-picker><i class="bi bi-images"></i> picker</button>
                                                            </div>
                                                            <input type="text" name="instagram[tiles][{{ $index }}][image]" value="{{ old("instagram.tiles.$index.image", $item['image']) }}" placeholder="Or paste image URL" data-studio-url />
                                                            <label class="checkbox-row compact" style="margin-top: 4px; color: var(--danger);">
                                                                <input type="checkbox" name="clear_instagram_tiles_image[{{ $index }}]" value="1" data-studio-clear> <span>Remove image</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- 10. STATS STRIP -->
                            <div class="tab-pane" id="stats-pane">
                                <div class="section-header-block">
                                    <div>
                                        <h3>Stats Strip</h3>
                                        <p class="lead" style="margin: 4px 0 0;">Numbers showing customer trust, years active, and products available.</p>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div class="field"><label>Eyebrow</label><input name="stats_eyebrow" value="{{ old('stats_eyebrow', $config['stats']['eyebrow']) }}" /></div>
                                    <div class="field"><label>Title</label><input name="stats_title" value="{{ old('stats_title', $config['stats']['title']) }}" /></div>
                                </div>

                                <div class="card-grid">
                                    @foreach ($config['stats']['items'] as $index => $item)
                                        <div class="admin-card-styled">
                                            <h4 style="margin: 0 0 14px; font-weight:700;">Stat {{ $index + 1 }}</h4>
                                            <div class="form-grid one" style="gap: 10px;">
                                                <div class="field" style="margin-bottom:0;"><label>Value</label><input name="stats[{{ $index }}][value]" value="{{ old("stats.$index.value", $item['value']) }}" placeholder="e.g. 45000+" /></div>
                                                <div class="field" style="margin-bottom:0;"><label>Label</label><input name="stats[{{ $index }}][label]" value="{{ old("stats.$index.label", $item['label']) }}" placeholder="e.g. Happy Customers" /></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- 11. FESTIVE EDITS -->
                            <div class="tab-pane" id="festive-pane">
                                <div class="section-header-block">
                                    <div>
                                        <h3>Festive Edits</h3>
                                        <p class="lead" style="margin: 4px 0 0;">Curated seasonal banner stories displayed at the bottom of the homepage.</p>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div class="field"><label>Eyebrow</label><input name="festive_edits_eyebrow" value="{{ old('festive_edits_eyebrow', $config['festive_edits']['eyebrow']) }}" /></div>
                                    <div class="field"><label>Title</label><input name="festive_edits_title" value="{{ old('festive_edits_title', $config['festive_edits']['title']) }}" /></div>
                                    <div class="field"><label>Button Text</label><input name="festive_edits_button_text" value="{{ old('festive_edits_button_text', $config['festive_edits']['button_text']) }}" /></div>
                                    <div class="field"><label>Button URL</label><input name="festive_edits_button_url" value="{{ old('festive_edits_button_url', $config['festive_edits']['button_url']) }}" /></div>
                                </div>

                                <div class="card-grid">
                                    @foreach ($config['festive_edits']['items'] as $index => $item)
                                        <div class="admin-card-styled">
                                            <h4 style="margin: 0 0 16px; font-weight:700;">Festive Card {{ $index + 1 }}</h4>
                                            <div class="form-grid one" style="gap: 12px;">
                                                <div class="field" style="margin-bottom:0;"><label>Badge Info</label><input name="festive_edits[{{ $index }}][badge]" value="{{ old("festive_edits.$index.badge", $item['badge']) }}" /></div>
                                                <div class="field" style="margin-bottom:0;"><label>Title</label><input name="festive_edits[{{ $index }}][title]" value="{{ old("festive_edits.$index.title", $item['title']) }}" /></div>
                                                <div class="field" style="margin-bottom:0;"><label>Link URL</label><input name="festive_edits[{{ $index }}][href]" value="{{ old("festive_edits.$index.href", $item['href']) }}" /></div>
                                                
                                                <div class="field" style="margin-bottom:0;">
                                                    <label>Card Image</label>
                                                    <div class="image-studio">
                                                        <div class="studio-preview-box">
                                                            <img src="{{ $item['preview_image'] ?? $item['image'] }}" style="{{ empty($item['image']) ? 'display:none;' : '' }}" data-studio-preview>
                                                            <div class="studio-placeholder" style="{{ !empty($item['image']) ? 'display:none;' : '' }}" data-studio-placeholder>
                                                                <i class="bi bi-image"></i>
                                                                <span>No Image</span>
                                                            </div>
                                                        </div>
                                                        <div class="studio-action-stack">
                                                            <div class="studio-btn-row">
                                                                <label class="studio-upload-label">
                                                                    <i class="bi bi-upload"></i> Upload File
                                                                    <input type="file" name="festive_edits_files[{{ $index }}]" accept="image/*" data-studio-file>
                                                                </label>
                                                                <button type="button" class="studio-picker-btn" data-trigger-picker><i class="bi bi-images"></i> Media Selector</button>
                                                            </div>
                                                            <input type="text" name="festive_edits[{{ $index }}][image]" value="{{ old("festive_edits.$index.image", $item['image']) }}" placeholder="Or paste image URL" data-studio-url />
                                                            <label class="checkbox-row compact" style="margin-top: 4px; color: var(--danger);">
                                                                <input type="checkbox" name="clear_festive_edits_image[{{ $index }}]" value="1" data-studio-clear> <span>Remove image</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Sticky Save Bar -->
                    <div class="admin-savebar-sticky">
                        <div>
                            <strong style="color: var(--heading);">Homepage Section Configuration</strong>
                            <p style="margin: 2px 0 0; font-size: 13px; color: var(--text-soft);">Toggle sections on/off, edit details, and upload images. All edits are applied together upon saving.</p>
                        </div>
                        <div class="button-row">
                            <a href="{{ route('admin.homepage-sections.index') }}" class="button secondary" style="width: auto; padding: 10px 20px;">Cancel</a>
                            <button type="submit" class="button" style="width: auto; padding: 10px 24px;"><i class="bi bi-cloud-upload"></i> Save Full Homepage</button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- Media Library Selection Modal -->
    <div class="picker-modal-backdrop" id="media-picker-modal" aria-hidden="true">
        <div class="picker-modal">
            <div class="picker-modal-header">
                <h3>Select Image From Media Library</h3>
                <button type="button" class="button secondary small" style="width: auto;" id="media-picker-close"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="picker-modal-body">
                <div class="picker-modal-toolbar">
                    <input type="text" class="picker-search-input" id="picker-search" placeholder="Search uploaded images by filename or original name..." />
                    <button type="button" class="button small" style="width: auto;" id="picker-search-btn"><i class="bi bi-search"></i> Search</button>
                </div>
                
                <!-- Loader -->
                <div id="picker-loader" style="display: none; text-align: center; padding: 40px 0; color: var(--text-soft);">
                    <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem; margin-bottom: 12px;"></div>
                    <p style="margin: 0; font-weight: 600;">Fetching Media Items...</p>
                </div>

                <!-- Empty State -->
                <div id="picker-empty" style="display: none; text-align: center; padding: 40px 20px; color: var(--text-soft);">
                    <i class="bi bi-images" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                    <p style="margin: 0; font-weight: 600;">No images found in your Media Library.</p>
                </div>

                <!-- Grid -->
                <div class="picker-grid" id="picker-image-grid"></div>
            </div>
            <div class="picker-modal-footer">
                <span id="picker-pagination-info" style="font-size: 13px; font-weight: 600; color: var(--text-soft);">Showing 0 of 0 images</span>
                <div class="picker-pagination" id="picker-pagination-buttons"></div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const frontendBaseUrl = @json(rtrim((string) config('app.frontend_url', config('app.url')), '/'));

            // --- Tabs Switching Logic ---
            const tabButtons = Array.from(document.querySelectorAll('.tab-btn'));
            const tabPanes = Array.from(document.querySelectorAll('.tab-pane'));

            tabButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    const targetId = btn.getAttribute('data-tab-target');
                    
                    // Update active button
                    tabButtons.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');

                    // Update active panel
                    tabPanes.forEach(p => p.classList.remove('active'));
                    const targetPane = document.getElementById(targetId);
                    if (targetPane) {
                        targetPane.classList.add('active');
                    }
                });
            });

            // --- Image Studio Live Previews ---
            const studios = Array.from(document.querySelectorAll('.image-studio'));

            studios.forEach(studio => {
                const urlInput = studio.querySelector('[data-studio-url]');
                const fileInput = studio.querySelector('[data-studio-file]');
                const clearInput = studio.querySelector('[data-studio-clear]');
                const previewImg = studio.querySelector('[data-studio-preview]');
                const placeholder = studio.querySelector('[data-studio-placeholder]');
                let objectUrl = null;

                const revokeObjectUrl = () => {
                    if (objectUrl) {
                        URL.revokeObjectURL(objectUrl);
                        objectUrl = null;
                    }
                };

                const resolvePreviewUrl = (value) => {
                    const path = value.trim();

                    if (path === '') {
                        return '';
                    }

                    if (
                        path.startsWith('http://') ||
                        path.startsWith('https://') ||
                        path.startsWith('blob:') ||
                        path.startsWith('data:')
                    ) {
                        return path;
                    }

                    if (path.startsWith('/reference-assets/') || path.startsWith('reference-assets/')) {
                        return `${frontendBaseUrl}/${path.replace(/^\/+/, '')}`;
                    }

                    if (path.startsWith('/storage/')) {
                        return `${window.location.origin}${path}`;
                    }

                    if (path.startsWith('storage/')) {
                        return `${window.location.origin}/${path}`;
                    }

                    if (path.startsWith('/')) {
                        return `${window.location.origin}${path}`;
                    }

                    return `${window.location.origin}/${path}`;
                };

                const updatePreview = () => {
                    if (clearInput && clearInput.checked) {
                        revokeObjectUrl();
                        if (previewImg) previewImg.style.display = 'none';
                        if (placeholder) placeholder.style.display = 'flex';
                        return;
                    }

                    // Priority 1: File Upload (object URL preview)
                    if (fileInput && fileInput.files && fileInput.files[0]) {
                        const file = fileInput.files[0];
                        revokeObjectUrl();
                        objectUrl = URL.createObjectURL(file);
                        if (previewImg) {
                            previewImg.src = objectUrl;
                            previewImg.style.display = 'block';
                        }
                        if (placeholder) placeholder.style.display = 'none';
                        return;
                    }

                    // Priority 2: Manual URL
                    if (urlInput && urlInput.value.trim() !== '') {
                        revokeObjectUrl();
                        const path = resolvePreviewUrl(urlInput.value);
                        if (previewImg) {
                            previewImg.src = path;
                            previewImg.style.display = 'block';
                        }
                        if (placeholder) placeholder.style.display = 'none';
                        return;
                    }

                    // Default: Hide
                    revokeObjectUrl();
                    if (previewImg) previewImg.style.display = 'none';
                    if (placeholder) placeholder.style.display = 'flex';
                };

                if (urlInput) {
                    urlInput.addEventListener('input', function () {
                        if (clearInput && urlInput.value.trim() !== '') {
                            clearInput.checked = false;
                        }
                        if (fileInput && urlInput.value.trim() !== '') {
                            fileInput.value = '';
                        }
                        updatePreview();
                    });
                }
                if (fileInput) {
                    fileInput.addEventListener('change', function () {
                        if (clearInput) clearInput.checked = false;
                        updatePreview();
                    });
                }
                if (clearInput) {
                    clearInput.addEventListener('change', function () {
                        if (clearInput.checked) {
                            if (fileInput) fileInput.value = '';
                        }
                        updatePreview();
                    });
                }

                // Initialize preview
                updatePreview();
            });

            // --- Media Library Picker Dialog ---
            const pickerModal = document.getElementById('media-picker-modal');
            const pickerClose = document.getElementById('media-picker-close');
            const searchInput = document.getElementById('picker-search');
            const searchBtn = document.getElementById('picker-search-btn');
            const imageGrid = document.getElementById('picker-image-grid');
            const loader = document.getElementById('picker-loader');
            const emptyState = document.getElementById('picker-empty');
            const paginationInfo = document.getElementById('picker-pagination-info');
            const paginationButtons = document.getElementById('picker-pagination-buttons');

            let activeStudio = null;
            let currentSearch = '';
            let currentPage = 1;

            const openPicker = (studioElement) => {
                activeStudio = studioElement;
                pickerModal.classList.add('is-open');
                pickerModal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                searchInput.value = '';
                currentSearch = '';
                currentPage = 1;
                fetchMedia(1);
            };

            const closePicker = () => {
                pickerModal.classList.remove('is-open');
                pickerModal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
                activeStudio = null;
            };

            document.querySelectorAll('[data-trigger-picker]').forEach(button => {
                button.addEventListener('click', function () {
                    const studio = button.closest('.image-studio');
                    openPicker(studio);
                });
            });

            pickerClose.addEventListener('click', closePicker);
            pickerModal.addEventListener('click', function (e) {
                if (e.target === pickerModal) closePicker();
            });

            const selectImage = (imageUrl) => {
                if (!activeStudio) return;
                const urlInput = activeStudio.querySelector('[data-studio-url]');
                const clearInput = activeStudio.querySelector('[data-studio-clear]');
                const fileInput = activeStudio.querySelector('[data-studio-file]');

                if (urlInput) {
                    urlInput.value = imageUrl;
                }
                if (clearInput) {
                    clearInput.checked = false;
                }
                if (fileInput) {
                    fileInput.value = ''; // Clear file uploads if selecting from library
                }

                // Trigger input event to update preview
                if (urlInput) {
                    urlInput.dispatchEvent(new Event('input', { bubbles: true }));
                }

                closePicker();
            };

            const fetchMedia = (page = 1) => {
                loader.style.display = 'block';
                imageGrid.style.display = 'none';
                emptyState.style.display = 'none';
                paginationButtons.innerHTML = '';
                paginationInfo.textContent = 'Loading...';

                const url = new URL('/admin/media-library/list', window.location.origin);
                url.searchParams.append('page', page);
                if (currentSearch !== '') {
                    url.searchParams.append('search', currentSearch);
                }

                fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    loader.style.display = 'none';
                    currentPage = data.current_page || 1;

                    if (!data.data || data.data.length === 0) {
                        emptyState.style.display = 'block';
                        paginationInfo.textContent = 'No images';
                        return;
                    }

                    imageGrid.style.display = 'grid';
                    imageGrid.innerHTML = '';

                    data.data.forEach(item => {
                        const tile = document.createElement('div');
                        tile.className = 'picker-tile';
                        tile.title = item.original_name || item.file_name;
                        
                        const img = document.createElement('img');
                        img.src = item.file_url;
                        img.loading = 'lazy';
                        
                        const filename = document.createElement('div');
                        filename.className = 'picker-tile-filename';
                        filename.textContent = item.original_name || item.file_name;

                        tile.appendChild(img);
                        tile.appendChild(filename);
                        
                        tile.addEventListener('click', () => {
                            selectImage(item.file_url);
                        });

                        imageGrid.appendChild(tile);
                    });

                    // Pagination Info
                    paginationInfo.textContent = `Showing ${data.from || 0}-${data.to || 0} of ${data.total || 0} images`;

                    // Pagination Buttons
                    if (data.prev_page_url) {
                        const prevBtn = document.createElement('button');
                        prevBtn.type = 'button';
                        prevBtn.className = 'button secondary small';
                        prevBtn.innerHTML = '<i class="bi bi-chevron-left"></i> Prev';
                        prevBtn.addEventListener('click', () => fetchMedia(currentPage - 1));
                        paginationButtons.appendChild(prevBtn);
                    }

                    if (data.next_page_url) {
                        const nextBtn = document.createElement('button');
                        nextBtn.type = 'button';
                        nextBtn.className = 'button secondary small';
                        nextBtn.innerHTML = 'Next <i class="bi bi-chevron-right"></i>';
                        nextBtn.addEventListener('click', () => fetchMedia(currentPage + 1));
                        paginationButtons.appendChild(nextBtn);
                    }
                })
                .catch(err => {
                    console.error('Error fetching media library:', err);
                    loader.style.display = 'none';
                    paginationInfo.textContent = 'Error loading media library.';
                });
            };

            const triggerSearch = () => {
                currentSearch = searchInput.value.trim();
                currentPage = 1;
                fetchMedia(1);
            };

            searchBtn.addEventListener('click', triggerSearch);
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    triggerSearch();
                }
            });
        });
    </script>
@endsection
