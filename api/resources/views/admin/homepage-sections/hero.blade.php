@extends('admin.layout')

@section('title', 'Hero Slider Editor')

@php
    $heroEnabledCount = collect($slides)->where('is_active', true)->count();
    $promoEnabledCount = collect($promos)->where('is_active', true)->count();
    $activeSlide = collect($slides)->firstWhere('is_active', true) ?? ($slides[0] ?? null);
    $activePromos = collect($promos)->where('is_active', true)->values();
@endphp

@section('content')
    <style>
        .hero-preview-shell {
            display: grid;
            grid-template-columns: minmax(0, 1.7fr) minmax(280px, 0.9fr);
            gap: 18px;
        }

        .hero-stage,
        .hero-promo-stack {
            min-width: 0;
        }

        .hero-stage {
            position: relative;
            min-height: 420px;
            border-radius: 18px;
            overflow: hidden;
            background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
            border: 1px solid var(--border);
        }

        .hero-promo-stack {
            display: grid;
            gap: 18px;
            grid-template-rows: repeat(2, minmax(0, 1fr));
        }

        .hero-stage-copy,
        .hero-promo-copy {
            position: absolute;
            inset: auto auto 0 0;
            z-index: 2;
            padding: 28px;
            max-width: 440px;
            color: #fff;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.02) 0%, rgba(15, 23, 42, 0.72) 72%, rgba(15, 23, 42, 0.92) 100%);
        }

        .hero-stage-copy h4,
        .hero-promo-copy h4 {
            margin: 0 0 8px;
            font-size: 28px;
            color: #fff;
        }

        .hero-stage-copy p,
        .hero-promo-copy p {
            margin: 0;
            color: rgba(255, 255, 255, 0.82);
            font-size: 14px;
            line-height: 1.5;
        }

        .hero-promo-card {
            position: relative;
            min-height: 200px;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid var(--border);
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        }

        .hero-empty-state {
            display: grid;
            place-items: center;
            min-height: 100%;
            padding: 24px;
            text-align: center;
            color: var(--text-soft);
            font-size: 14px;
        }

        .hero-library-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(255px, 1fr));
            gap: 20px;
        }

        .hero-media-card {
            border: 1px solid var(--border);
            border-radius: 18px;
            background: #fff;
            overflow: hidden;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
            display: flex;
            flex-direction: column;
        }

        .hero-media-card-top {
            position: relative;
            aspect-ratio: 1.1 / 1;
            background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
            border-bottom: 1px solid var(--border);
            overflow: hidden;
        }

        .hero-media-card-body {
            padding: 18px;
            display: grid;
            gap: 14px;
        }

        .hero-card-meta {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .hero-card-meta h4 {
            margin: 0 0 4px;
            font-size: 17px;
        }

        .hero-card-meta p,
        .hero-media-note {
            margin: 0;
            font-size: 12px;
            color: var(--text-soft);
            line-height: 1.5;
        }

        .hero-card-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
        }

        .hero-card-actions .button {
            flex: 1 1 auto;
        }

        .hero-media-tag-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .hero-media-filename {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 11px;
            color: var(--text-soft);
            background: var(--bg-soft);
            border-radius: 999px;
            padding: 6px 10px;
            border: 1px solid var(--border);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .hero-media-preview {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.18s ease, object-position 0.18s ease;
            transform-origin: center center;
        }

        .hero-media-overlay {
            position: absolute;
            inset: auto 0 0 0;
            padding: 14px 16px;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0) 0%, rgba(15, 23, 42, 0.72) 100%);
            color: #fff;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 12px;
            z-index: 2;
        }

        .hero-media-overlay strong {
            display: block;
            font-size: 13px;
            color: #fff;
        }

        .hero-media-overlay span {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.76);
        }

        .hero-editor-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.58);
            backdrop-filter: blur(8px);
            padding: 24px;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1200;
        }

        .hero-editor-overlay.is-open {
            display: flex;
        }

        .hero-editor-dialog {
            width: min(1120px, 100%);
            max-height: calc(100vh - 48px);
            overflow: auto;
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 30px 80px rgba(15, 23, 42, 0.24);
            border: 1px solid rgba(15, 23, 42, 0.08);
        }

        .hero-editor-header {
            position: sticky;
            top: 0;
            z-index: 5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 22px 24px;
            border-bottom: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
        }

        .hero-editor-header h4 {
            margin: 0 0 6px;
            font-size: 22px;
        }

        .hero-editor-header p {
            margin: 0;
            color: var(--text-soft);
            font-size: 13px;
        }

        .hero-editor-body {
            padding: 24px;
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.9fr);
            gap: 24px;
        }

        .hero-crop-stage {
            display: grid;
            gap: 18px;
        }

        .hero-crop-preview {
            position: relative;
            min-height: 420px;
            border-radius: 22px;
            overflow: hidden;
            border: 1px solid var(--border);
            background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
        }

        .hero-crop-preview.is-promo {
            min-height: 320px;
        }

        .hero-crop-guides {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 2;
        }

        .hero-crop-guides::before,
        .hero-crop-guides::after {
            content: '';
            position: absolute;
            background: rgba(255, 255, 255, 0.48);
        }

        .hero-crop-guides::before {
            top: 0;
            bottom: 0;
            left: 50%;
            width: 1px;
            transform: translateX(-0.5px);
        }

        .hero-crop-guides::after {
            left: 0;
            right: 0;
            top: 50%;
            height: 1px;
            transform: translateY(-0.5px);
        }

        .hero-crop-toolbar {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .hero-slider-control {
            display: grid;
            gap: 6px;
            padding: 14px 16px;
            background: var(--bg-soft);
            border: 1px solid var(--border);
            border-radius: 16px;
        }

        .hero-slider-control label {
            margin: 0;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-soft);
        }

        .hero-slider-control output {
            font-size: 20px;
            font-weight: 800;
            color: var(--heading);
        }

        .hero-side-panel {
            display: grid;
            gap: 18px;
            align-content: start;
        }

        .hero-side-panel .admin-field,
        .hero-side-panel .admin-fields {
            gap: 14px;
        }

        .hero-preview-note {
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid rgba(37, 99, 235, 0.14);
            background: rgba(37, 99, 235, 0.06);
            color: var(--primary-dark);
            font-size: 13px;
            line-height: 1.5;
        }

        .hero-editor-footer {
            position: sticky;
            bottom: 0;
            z-index: 4;
            padding: 18px 24px 24px;
            background: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.96) 24%, rgba(255,255,255,0.98) 100%);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .hero-editor-footer p {
            margin: 0;
            color: var(--text-soft);
            font-size: 13px;
        }

        .hero-modal-button-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .hero-hidden-url {
            display: none;
        }

        @media (max-width: 1100px) {
            .hero-preview-shell,
            .hero-editor-body {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .hero-library-grid {
                grid-template-columns: 1fr;
            }

            .hero-editor-overlay {
                padding: 12px;
            }

            .hero-editor-header,
            .hero-editor-body,
            .hero-editor-footer {
                padding-left: 16px;
                padding-right: 16px;
            }

            .hero-editor-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .hero-modal-button-row {
                width: 100%;
            }

            .hero-modal-button-row .button {
                width: 100%;
            }

            .hero-crop-toolbar {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell">
                <div class="admin-banner">
                    <div>
                        <div class="brand">Homepage Settings</div>
                        <h2>Hero Slider Configuration</h2>
                        <p>Manage hero media like a small asset studio: clear active previews, popup editing, and crop tuning before you save.</p>
                    </div>
                    <a href="{{ route('admin.homepage-sections.index') }}" class="button secondary small">
                        <i class="bi bi-arrow-left"></i> Back To Sections
                    </a>
                </div>

                @if ($errors->any())
                    <div class="admin-errors">
                        <strong>Please fix the highlighted fields to continue.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status'))
                    <div class="admin-toast" id="admin-success">
                        <div>
                            <strong>Changes Saved Successfully</strong>
                            <p>{{ session('status') }}</p>
                        </div>
                        <button type="button" class="button secondary small" id="admin-toast-close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                @endif

                <div class="admin-overview">
                    <div class="admin-stat">
                        <span>Main Slides Active</span>
                        <strong>{{ $heroEnabledCount }} <span style="color: var(--text-soft); font-size: 18px;">/ {{ count($slides) }}</span></strong>
                    </div>
                    <div class="admin-stat">
                        <span>Promo Banners Active</span>
                        <strong>{{ $promoEnabledCount }} <span style="color: var(--text-soft); font-size: 18px;">/ {{ count($promos) }}</span></strong>
                    </div>
                    <div class="admin-stat">
                        <span>Autoplay Speed</span>
                        <strong>{{ old('autoplay_ms', $sliderSettings['autoplay_ms']) }} <span style="color: var(--text-soft); font-size: 18px;">ms</span></strong>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.homepage-sections.hero.update') }}" enctype="multipart/form-data" id="hero-editor-form">
                    @csrf
                    @method('PUT')

                    <section class="admin-section">
                        <div class="admin-section-header">
                            <h3>Storefront Preview</h3>
                            <p>This shows which media is currently active for the hero area before you open any popup editor.</p>
                        </div>

                        <div class="hero-preview-shell">
                            <div class="hero-stage">
                                @if (!empty($activeSlide['image'] ?? null))
                                    <img
                                        src="{{ $activeSlide['preview_image'] ?? $activeSlide['image'] }}"
                                        alt="{{ $activeSlide['alt'] ?: 'Active hero slide preview' }}"
                                        class="hero-media-preview"
                                        style="object-position: {{ $activeSlide['crop_x'] ?? 50 }}% {{ $activeSlide['crop_y'] ?? 50 }}%; transform: scale({{ $activeSlide['crop_zoom'] ?? 1 }});"
                                    >
                                    <div class="hero-stage-copy">
                                        <span class="admin-badge success" style="margin-bottom: 12px;">Active main slide</span>
                                        <h4>{{ $activeSlide['title'] ?: 'No slide title set yet' }}</h4>
                                        <p>{{ $activeSlide['href'] ?: 'No target URL set for this slide yet.' }}</p>
                                    </div>
                                @else
                                    <div class="hero-empty-state">
                                        <div>
                                            <i class="bi bi-image" style="font-size: 28px; display: block; margin-bottom: 10px;"></i>
                                            No active main slide image yet.
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="hero-promo-stack">
                                @foreach ([0, 1] as $promoIndex)
                                    @php
                                        $previewPromo = $activePromos[$promoIndex] ?? ($promos[$promoIndex] ?? null);
                                    @endphp
                                    <div class="hero-promo-card">
                                        @if (!empty($previewPromo['image'] ?? null))
                                            <img
                                                src="{{ $previewPromo['preview_image'] ?? $previewPromo['image'] }}"
                                                alt="{{ $previewPromo['title'] ?: 'Promo preview' }}"
                                                class="hero-media-preview"
                                                style="object-position: {{ $previewPromo['crop_x'] ?? 50 }}% {{ $previewPromo['crop_y'] ?? 50 }}%; transform: scale({{ $previewPromo['crop_zoom'] ?? 1 }});"
                                            >
                                            <div class="hero-promo-copy">
                                                <span class="admin-badge {{ !empty($previewPromo['is_active']) ? 'success' : 'muted' }}" style="margin-bottom: 10px;">
                                                    {{ !empty($previewPromo['is_active']) ? 'Active promo' : 'Inactive promo' }}
                                                </span>
                                                <h4 style="font-size: 18px;">{{ $previewPromo['title'] ?: 'Promo banner '.($promoIndex + 1) }}</h4>
                                                <p>{{ $previewPromo['subtitle'] ?: ($previewPromo['href'] ?: 'No promo copy yet.') }}</p>
                                            </div>
                                        @else
                                            <div class="hero-empty-state">
                                                <div>
                                                    <i class="bi bi-card-image" style="font-size: 22px; display: block; margin-bottom: 10px;"></i>
                                                    Promo banner {{ $promoIndex + 1 }} has no image yet.
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>

                    <section class="admin-section">
                        <div class="admin-section-header">
                            <h3>Hero Settings & Options</h3>
                            <p>Core section behavior and visibility settings stay on the main page. Media details open in popups.</p>
                        </div>

                        <div class="admin-fields">
                            <div class="admin-field">
                                <label for="label">Admin Label</label>
                                <input id="label" name="label" value="{{ old('label', $section->label) }}" placeholder="e.g. Homepage Hero" />
                                @error('label') <div class="admin-inline-error">{{ $message }}</div> @enderror
                            </div>
                            <div class="admin-field">
                                <label for="title">Section Title</label>
                                <input id="title" name="title" value="{{ old('title', $section->title) }}" placeholder="Public section title" />
                                @error('title') <div class="admin-inline-error">{{ $message }}</div> @enderror
                            </div>
                            <div class="admin-field">
                                <label for="subtitle">Section Subtitle</label>
                                <input id="subtitle" name="subtitle" value="{{ old('subtitle', $section->subtitle) }}" />
                                @error('subtitle') <div class="admin-inline-error">{{ $message }}</div> @enderror
                            </div>
                            <div class="admin-field">
                                <label for="heading">Section Heading</label>
                                <input id="heading" name="heading" value="{{ old('heading', $section->heading) }}" />
                                @error('heading') <div class="admin-inline-error">{{ $message }}</div> @enderror
                            </div>
                            <div class="admin-field" style="grid-column: 1 / -1;">
                                <label for="content">Overlay Description</label>
                                <textarea id="content" name="content" rows="3" placeholder="Short supporting copy shown on the homepage hero">{{ old('content', $section->content) }}</textarea>
                                @error('content') <div class="admin-inline-error">{{ $message }}</div> @enderror
                            </div>
                            <div class="admin-field">
                                <label for="button_text">Primary Button Text</label>
                                <input id="button_text" name="button_text" value="{{ old('button_text', $section->button_text) }}" placeholder="e.g. Shop the Collection">
                                @error('button_text') <div class="admin-inline-error">{{ $message }}</div> @enderror
                            </div>
                            <div class="admin-field">
                                <label for="button_url">Primary Button URL</label>
                                <input id="button_url" name="button_url" value="{{ old('button_url', $section->button_url) }}" placeholder="/shop">
                                @error('button_url') <div class="admin-inline-error">{{ $message }}</div> @enderror
                            </div>
                            <div class="admin-field">
                                <label for="secondary_button_text">Secondary Button Text</label>
                                <input id="secondary_button_text" name="secondary_button_text" value="{{ old('secondary_button_text', $secondaryButtonText) }}" placeholder="e.g. Explore Gifting Picks">
                                @error('secondary_button_text') <div class="admin-inline-error">{{ $message }}</div> @enderror
                            </div>
                            <div class="admin-field">
                                <label for="secondary_button_url">Secondary Button URL</label>
                                <input id="secondary_button_url" name="secondary_button_url" value="{{ old('secondary_button_url', $secondaryButtonUrl) }}" placeholder="/shop?category=gifting-edit">
                                @error('secondary_button_url') <div class="admin-inline-error">{{ $message }}</div> @enderror
                            </div>
                            <div class="admin-field">
                                <label for="sort_order">Display Sort Order</label>
                                <input id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', $section->sort_order) }}" />
                                @error('sort_order') <div class="admin-inline-error">{{ $message }}</div> @enderror
                            </div>
                            <div class="admin-field">
                                <label for="autoplay_ms">Slider Transition Speed (ms)</label>
                                <input id="autoplay_ms" name="autoplay_ms" type="number" min="1000" max="15000" step="100" value="{{ old('autoplay_ms', $sliderSettings['autoplay_ms']) }}" />
                                <div class="admin-help">Recommended speed is between 3000ms and 4500ms.</div>
                                @error('autoplay_ms') <div class="admin-inline-error">{{ $message }}</div> @enderror
                            </div>
                            <div class="admin-field">
                                <label for="nav_gap">Navigation Gap (px)</label>
                                <input id="nav_gap" name="nav_gap" type="number" min="0" max="240" value="{{ old('nav_gap', $sliderSettings['nav_gap']) }}" />
                                <div class="admin-help">Spacing between slider navigation components.</div>
                                @error('nav_gap') <div class="admin-inline-error">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="admin-toggle-row">
                            <label class="admin-toggle">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $section->is_active))>
                                <span>Entire Hero Section Active</span>
                            </label>
                            <label class="admin-toggle">
                                <input type="checkbox" name="show_text" value="1" @checked(old('show_text', $sliderSettings['show_text']))>
                                <span>Show Slide Title Text</span>
                            </label>
                            <label class="admin-toggle">
                                <input type="checkbox" name="show_dots" value="1" @checked(old('show_dots', $sliderSettings['show_dots']))>
                                <span>Enable Slider Dots</span>
                            </label>
                            <label class="admin-toggle">
                                <input type="checkbox" name="show_arrows" value="1" @checked(old('show_arrows', $sliderSettings['show_arrows']))>
                                <span>Enable Navigation Arrows</span>
                            </label>
                        </div>
                    </section>

                    <section class="admin-section">
                        <div class="admin-section-header">
                            <h3>Main Hero Slides</h3>
                            <p>Each slide now opens inside a popup editor with media preview and crop controls. Recommended size: <strong>1600 × 1100 px</strong>.</p>
                        </div>

                        <div class="hero-library-grid">
                            @foreach ($slides as $index => $slide)
                                @php
                                    $slideImageName = $slide['image'] ? basename((string) parse_url($slide['image'], PHP_URL_PATH)) : 'No image uploaded';
                                @endphp
                                <article class="hero-media-card">
                                    <div class="hero-media-card-top">
                                        @if (!empty($slide['image']))
                                            <img
                                                src="{{ $slide['preview_image'] ?? $slide['image'] }}"
                                                alt="{{ $slide['alt'] ?: 'Slide preview' }}"
                                                class="hero-media-preview"
                                                data-media-preview
                                                style="object-position: {{ old("slides.$index.crop_x", $slide['crop_x']) }}% {{ old("slides.$index.crop_y", $slide['crop_y']) }}%; transform: scale({{ old("slides.$index.crop_zoom", $slide['crop_zoom']) }});"
                                            >
                                            <div class="hero-media-overlay">
                                                <div>
                                                    <strong>{{ $slide['title'] ?: 'Slide '.($index + 1) }}</strong>
                                                    <span>{{ old("slides.$index.is_active", $slide['is_active']) ? 'Currently visible on storefront rotation' : 'Saved but hidden' }}</span>
                                                </div>
                                                <span class="admin-badge {{ old("slides.$index.is_active", $slide['is_active']) ? 'success' : 'muted' }}">
                                                    {{ old("slides.$index.is_active", $slide['is_active']) ? 'Active' : 'Hidden' }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="hero-empty-state">
                                                <div>
                                                    <i class="bi bi-image" style="font-size: 24px; display: block; margin-bottom: 10px;"></i>
                                                    No image uploaded yet
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="hero-media-card-body">
                                        <div class="hero-card-meta">
                                            <div>
                                                <h4>Slide {{ $index + 1 }}</h4>
                                                <p>{{ $slide['href'] ?: 'No click URL set yet' }}</p>
                                            </div>
                                            <span class="admin-badge primary">1600 x 1100</span>
                                        </div>
                                        <div class="hero-media-tag-row">
                                            <span class="hero-media-filename">{{ $slideImageName }}</span>
                                        </div>
                                        <p class="hero-media-note">Edit title, upload a new image, fine-tune crop, and control publish state from the popup.</p>
                                        <div class="hero-card-actions">
                                            <button type="button" class="button small" data-open-hero-editor="slide-{{ $index }}">Edit Slide</button>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>

                    <section class="admin-section">
                        <div class="admin-section-header">
                            <h3>Right-Side Promo Banners</h3>
                            <p>Supporting banners also open in a popup editor, with their own crop and preview controls. Recommended size: <strong>900 × 620 px</strong>.</p>
                        </div>

                        <div class="hero-library-grid">
                            @foreach ($promos as $index => $promo)
                                @php
                                    $promoImageName = $promo['image'] ? basename((string) parse_url($promo['image'], PHP_URL_PATH)) : 'No image uploaded';
                                @endphp
                                <article class="hero-media-card">
                                    <div class="hero-media-card-top" style="aspect-ratio: 1.15 / 0.82;">
                                        @if (!empty($promo['image']))
                                            <img
                                                src="{{ $promo['preview_image'] ?? $promo['image'] }}"
                                                alt="{{ $promo['title'] ?: 'Promo preview' }}"
                                                class="hero-media-preview"
                                                style="object-position: {{ old("promos.$index.crop_x", $promo['crop_x']) }}% {{ old("promos.$index.crop_y", $promo['crop_y']) }}%; transform: scale({{ old("promos.$index.crop_zoom", $promo['crop_zoom']) }});"
                                            >
                                            <div class="hero-media-overlay">
                                                <div>
                                                    <strong>{{ $promo['title'] ?: 'Promo banner '.($index + 1) }}</strong>
                                                    <span>{{ old("promos.$index.is_active", $promo['is_active']) ? 'Included in the right-side stack' : 'Saved but hidden' }}</span>
                                                </div>
                                                <span class="admin-badge {{ old("promos.$index.is_active", $promo['is_active']) ? 'success' : 'muted' }}">
                                                    {{ old("promos.$index.is_active", $promo['is_active']) ? 'Active' : 'Hidden' }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="hero-empty-state">
                                                <div>
                                                    <i class="bi bi-card-image" style="font-size: 22px; display: block; margin-bottom: 10px;"></i>
                                                    No promo image uploaded yet
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="hero-media-card-body">
                                        <div class="hero-card-meta">
                                            <div>
                                                <h4>Promo {{ $index + 1 }}</h4>
                                                <p>{{ $promo['subtitle'] ?: ($promo['href'] ?: 'No promo destination set yet') }}</p>
                                            </div>
                                            <span class="admin-badge primary">900 x 620</span>
                                        </div>
                                        <div class="hero-media-tag-row">
                                            <span class="hero-media-filename">{{ $promoImageName }}</span>
                                            @if (old("promos.$index.show_text", $promo['show_text']))
                                                <span class="admin-badge warning">Overlay text on</span>
                                            @endif
                                        </div>
                                        <p class="hero-media-note">Open popup editor to change image, crop, overlay copy, and publish state.</p>
                                        <div class="hero-card-actions">
                                            <button type="button" class="button small" data-open-hero-editor="promo-{{ $index }}">Edit Promo</button>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>

                    @foreach ($slides as $index => $slide)
                        <div class="hero-editor-overlay" data-hero-editor="slide-{{ $index }}" aria-hidden="true">
                            <div class="hero-editor-dialog">
                                <div class="hero-editor-header">
                                    <div>
                                        <h4>Edit Slide {{ $index + 1 }}</h4>
                                        <p>Upload or replace media, adjust crop, and decide whether this slide should stay active in the rotation.</p>
                                    </div>
                                    <button type="button" class="button secondary small" data-close-hero-editor>Close</button>
                                </div>
                                <div class="hero-editor-body">
                                    <div class="hero-crop-stage">
                                        <div class="hero-crop-preview" data-preview-stage>
                                            @if (!empty($slide['image']))
                                                <img
                                                    src="{{ $slide['preview_image'] ?? $slide['image'] }}"
                                                    alt="{{ $slide['alt'] ?: 'Slide preview' }}"
                                                    class="hero-media-preview"
                                                    data-preview-image
                                                    style="object-position: {{ old("slides.$index.crop_x", $slide['crop_x']) }}% {{ old("slides.$index.crop_y", $slide['crop_y']) }}%; transform: scale({{ old("slides.$index.crop_zoom", $slide['crop_zoom']) }});"
                                                >
                                            @else
                                                <div class="hero-empty-state" data-preview-empty>
                                                    <div>
                                                        <i class="bi bi-image" style="font-size: 28px; display: block; margin-bottom: 10px;"></i>
                                                        Upload an image or paste a media URL to preview crop here.
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="hero-crop-guides"></div>
                                        </div>

                                        <div class="hero-crop-toolbar">
                                            <div class="hero-slider-control">
                                                <label for="slide-{{ $index }}-crop-x">Horizontal focus</label>
                                                <output data-range-output>{{ old("slides.$index.crop_x", $slide['crop_x']) }}%</output>
                                                <input id="slide-{{ $index }}-crop-x" type="range" min="0" max="100" value="{{ old("slides.$index.crop_x", $slide['crop_x']) }}" name="slides[{{ $index }}][crop_x]" data-crop-x>
                                            </div>
                                            <div class="hero-slider-control">
                                                <label for="slide-{{ $index }}-crop-y">Vertical focus</label>
                                                <output data-range-output>{{ old("slides.$index.crop_y", $slide['crop_y']) }}%</output>
                                                <input id="slide-{{ $index }}-crop-y" type="range" min="0" max="100" value="{{ old("slides.$index.crop_y", $slide['crop_y']) }}" name="slides[{{ $index }}][crop_y]" data-crop-y>
                                            </div>
                                            <div class="hero-slider-control">
                                                <label for="slide-{{ $index }}-crop-zoom">Zoom</label>
                                                <output data-range-output>{{ number_format((float) old("slides.$index.crop_zoom", $slide['crop_zoom']), 2) }}x</output>
                                                <input id="slide-{{ $index }}-crop-zoom" type="range" min="1" max="2.5" step="0.05" value="{{ old("slides.$index.crop_zoom", $slide['crop_zoom']) }}" name="slides[{{ $index }}][crop_zoom]" data-crop-zoom>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="hero-side-panel">
                                        <div class="hero-preview-note">
                                            Current file:
                                            <strong>{{ $slide['image'] ? basename((string) parse_url($slide['image'], PHP_URL_PATH)) : 'No image uploaded yet' }}</strong>
                                        </div>

                                        <div class="admin-fields" style="grid-template-columns: 1fr;">
                                            <div class="admin-field">
                                                <label>Slide Image URL</label>
                                                <input name="slide_urls[{{ $index }}]" value="{{ old("slide_urls.$index", $slide['image']) }}" placeholder="https://... or /storage/..." data-source-input>
                                            </div>
                                            <div class="admin-field">
                                                <label>Upload New Image</label>
                                                <input type="file" name="slide_files[{{ $index }}]" accept="image/*" data-upload-input>
                                                <div class="admin-help">JPG, PNG, and WebP supported. File preview updates immediately.</div>
                                            </div>
                                            <div class="admin-field">
                                                <label>Display Title</label>
                                                <input name="slides[{{ $index }}][title]" value="{{ old("slides.$index.title", $slide['title']) }}" placeholder="e.g. Summer Collection">
                                            </div>
                                            <div class="admin-field">
                                                <label>Image Alt Text</label>
                                                <input name="slides[{{ $index }}][alt]" value="{{ old("slides.$index.alt", $slide['alt']) }}" placeholder="Descriptive accessibility text">
                                            </div>
                                            <div class="admin-field">
                                                <label>Target URL</label>
                                                <input name="slides[{{ $index }}][href]" value="{{ old("slides.$index.href", $slide['href']) }}" placeholder="/shop?category=featured">
                                            </div>
                                        </div>

                                        <label class="admin-toggle">
                                            <input type="checkbox" name="slides[{{ $index }}][is_active]" value="1" @checked(old("slides.$index.is_active", $slide['is_active']))>
                                            <span>Publish this slide</span>
                                        </label>

                                        <label class="admin-toggle">
                                            <input type="checkbox" name="clear_slide_image[{{ $index }}]" value="1" data-clear-input>
                                            <span style="color: var(--danger);">Remove current image on save</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="hero-editor-footer">
                                    <p>Popup editor keeps the main screen clean, but all fields still save together when you submit the page.</p>
                                    <div class="hero-modal-button-row">
                                        <button type="button" class="button secondary small" data-close-hero-editor>Done</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @foreach ($promos as $index => $promo)
                        <div class="hero-editor-overlay" data-hero-editor="promo-{{ $index }}" aria-hidden="true">
                            <div class="hero-editor-dialog">
                                <div class="hero-editor-header">
                                    <div>
                                        <h4>Edit Promo Banner {{ $index + 1 }}</h4>
                                        <p>Set promo image, crop focus, overlay copy, and visibility without cluttering the main page.</p>
                                    </div>
                                    <button type="button" class="button secondary small" data-close-hero-editor>Close</button>
                                </div>
                                <div class="hero-editor-body">
                                    <div class="hero-crop-stage">
                                        <div class="hero-crop-preview is-promo" data-preview-stage>
                                            @if (!empty($promo['image']))
                                                <img
                                                    src="{{ $promo['preview_image'] ?? $promo['image'] }}"
                                                    alt="{{ $promo['title'] ?: 'Promo preview' }}"
                                                    class="hero-media-preview"
                                                    data-preview-image
                                                    style="object-position: {{ old("promos.$index.crop_x", $promo['crop_x']) }}% {{ old("promos.$index.crop_y", $promo['crop_y']) }}%; transform: scale({{ old("promos.$index.crop_zoom", $promo['crop_zoom']) }});"
                                                >
                                            @else
                                                <div class="hero-empty-state" data-preview-empty>
                                                    <div>
                                                        <i class="bi bi-card-image" style="font-size: 28px; display: block; margin-bottom: 10px;"></i>
                                                        Upload a promo image or paste its URL to preview the crop.
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="hero-crop-guides"></div>
                                        </div>

                                        <div class="hero-crop-toolbar">
                                            <div class="hero-slider-control">
                                                <label for="promo-{{ $index }}-crop-x">Horizontal focus</label>
                                                <output data-range-output>{{ old("promos.$index.crop_x", $promo['crop_x']) }}%</output>
                                                <input id="promo-{{ $index }}-crop-x" type="range" min="0" max="100" value="{{ old("promos.$index.crop_x", $promo['crop_x']) }}" name="promos[{{ $index }}][crop_x]" data-crop-x>
                                            </div>
                                            <div class="hero-slider-control">
                                                <label for="promo-{{ $index }}-crop-y">Vertical focus</label>
                                                <output data-range-output>{{ old("promos.$index.crop_y", $promo['crop_y']) }}%</output>
                                                <input id="promo-{{ $index }}-crop-y" type="range" min="0" max="100" value="{{ old("promos.$index.crop_y", $promo['crop_y']) }}" name="promos[{{ $index }}][crop_y]" data-crop-y>
                                            </div>
                                            <div class="hero-slider-control">
                                                <label for="promo-{{ $index }}-crop-zoom">Zoom</label>
                                                <output data-range-output>{{ number_format((float) old("promos.$index.crop_zoom", $promo['crop_zoom']), 2) }}x</output>
                                                <input id="promo-{{ $index }}-crop-zoom" type="range" min="1" max="2.5" step="0.05" value="{{ old("promos.$index.crop_zoom", $promo['crop_zoom']) }}" name="promos[{{ $index }}][crop_zoom]" data-crop-zoom>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="hero-side-panel">
                                        <div class="hero-preview-note">
                                            Current file:
                                            <strong>{{ $promo['image'] ? basename((string) parse_url($promo['image'], PHP_URL_PATH)) : 'No image uploaded yet' }}</strong>
                                        </div>

                                        <div class="admin-fields" style="grid-template-columns: 1fr;">
                                            <div class="admin-field">
                                                <label>Promo Image URL</label>
                                                <input name="promo_urls[{{ $index }}]" value="{{ old("promo_urls.$index", $promo['image']) }}" placeholder="https://... or /storage/..." data-source-input>
                                            </div>
                                            <div class="admin-field">
                                                <label>Upload New Image</label>
                                                <input type="file" name="promo_files[{{ $index }}]" accept="image/*" data-upload-input>
                                                <div class="admin-help">Promo preview refreshes immediately when you pick a file.</div>
                                            </div>
                                            <div class="admin-field">
                                                <label>Primary Title</label>
                                                <input name="promos[{{ $index }}][title]" value="{{ old("promos.$index.title", $promo['title']) }}" placeholder="e.g. Wall Decor">
                                            </div>
                                            <div class="admin-field">
                                                <label>Secondary Subtitle</label>
                                                <input name="promos[{{ $index }}][subtitle]" value="{{ old("promos.$index.subtitle", $promo['subtitle']) }}" placeholder="e.g. Up to 40% Off">
                                            </div>
                                            <div class="admin-field">
                                                <label>Target URL</label>
                                                <input name="promos[{{ $index }}][href]" value="{{ old("promos.$index.href", $promo['href']) }}" placeholder="/shop?category=decor">
                                            </div>
                                        </div>

                                        <label class="admin-toggle">
                                            <input type="checkbox" name="promos[{{ $index }}][is_active]" value="1" @checked(old("promos.$index.is_active", $promo['is_active']))>
                                            <span>Publish this promo</span>
                                        </label>

                                        <label class="admin-toggle">
                                            <input type="checkbox" name="promos[{{ $index }}][show_text]" value="1" @checked(old("promos.$index.show_text", $promo['show_text']))>
                                            <span>Show overlay text on image</span>
                                        </label>

                                        <label class="admin-toggle">
                                            <input type="checkbox" name="clear_promo_image[{{ $index }}]" value="1" data-clear-input>
                                            <span style="color: var(--danger);">Remove current image on save</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="hero-editor-footer">
                                    <p>You can tune promo framing here without losing the overview on the main editor page.</p>
                                    <div class="hero-modal-button-row">
                                        <button type="button" class="button secondary small" data-close-hero-editor>Done</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="admin-savebar">
                        <div class="admin-savebar-text">
                            <strong>Ready to Apply Changes?</strong>
                            <p>Popup editors keep the UI lighter. Save once here to publish all hero, promo, and crop updates together.</p>
                        </div>
                        <div class="button-row">
                            <a href="{{ route('admin.homepage-sections.index') }}" class="button secondary" style="min-width: 120px;">Cancel</a>
                            <button class="button" type="submit" style="min-width: 160px; padding-left: 24px; padding-right: 24px;">
                                <i class="bi bi-cloud-upload"></i> Save Dashboard Configuration
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const successBox = document.getElementById('admin-success');
            if (successBox) {
                successBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            const closeToastButton = document.getElementById('admin-toast-close');
            if (closeToastButton && successBox) {
                closeToastButton.addEventListener('click', function () {
                    successBox.style.opacity = '0';
                    setTimeout(() => successBox.remove(), 300);
                });
            }

            const editorOverlays = Array.from(document.querySelectorAll('[data-hero-editor]'));
            const openButtons = Array.from(document.querySelectorAll('[data-open-hero-editor]'));
            const closeButtons = Array.from(document.querySelectorAll('[data-close-hero-editor]'));

            const openEditor = (editorId) => {
                const overlay = document.querySelector(`[data-hero-editor="${editorId}"]`);
                if (!overlay) return;
                overlay.classList.add('is-open');
                overlay.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            };

            const closeEditor = (overlay) => {
                if (!overlay) return;
                overlay.classList.remove('is-open');
                overlay.setAttribute('aria-hidden', 'true');
                if (!document.querySelector('.hero-editor-overlay.is-open')) {
                    document.body.style.overflow = '';
                }
            };

            openButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    openEditor(button.getAttribute('data-open-hero-editor'));
                });
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    closeEditor(button.closest('.hero-editor-overlay'));
                });
            });

            editorOverlays.forEach((overlay) => {
                overlay.addEventListener('click', function (event) {
                    if (event.target === overlay) {
                        closeEditor(overlay);
                    }
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key !== 'Escape') return;
                const visibleOverlay = document.querySelector('.hero-editor-overlay.is-open');
                if (visibleOverlay) {
                    closeEditor(visibleOverlay);
                }
            });

            const updatePreviewPresentation = (overlay) => {
                const image = overlay.querySelector('[data-preview-image]');
                const emptyState = overlay.querySelector('[data-preview-empty]');
                const sourceInput = overlay.querySelector('[data-source-input]');
                const cropXInput = overlay.querySelector('[data-crop-x]');
                const cropYInput = overlay.querySelector('[data-crop-y]');
                const cropZoomInput = overlay.querySelector('[data-crop-zoom]');
                const clearInput = overlay.querySelector('[data-clear-input]');

                if (cropXInput) {
                    const output = cropXInput.closest('.hero-slider-control')?.querySelector('[data-range-output]');
                    if (output) output.textContent = `${cropXInput.value}%`;
                }
                if (cropYInput) {
                    const output = cropYInput.closest('.hero-slider-control')?.querySelector('[data-range-output]');
                    if (output) output.textContent = `${cropYInput.value}%`;
                }
                if (cropZoomInput) {
                    const output = cropZoomInput.closest('.hero-slider-control')?.querySelector('[data-range-output]');
                    if (output) output.textContent = `${Number(cropZoomInput.value).toFixed(2)}x`;
                }

                if (!image) {
                    return;
                }

                const shouldClear = clearInput && clearInput.checked;
                if (shouldClear) {
                    image.style.display = 'none';
                    if (emptyState) emptyState.style.display = 'grid';
                    return;
                }

                const source = sourceInput && sourceInput.value.trim() !== '' ? sourceInput.value.trim() : image.getAttribute('src');
                if (source) {
                    image.setAttribute('src', source);
                    image.style.display = 'block';
                    image.style.objectPosition = `${cropXInput ? cropXInput.value : 50}% ${cropYInput ? cropYInput.value : 50}%`;
                    image.style.transform = `scale(${cropZoomInput ? cropZoomInput.value : 1})`;
                    if (emptyState) emptyState.style.display = 'none';
                } else {
                    image.style.display = 'none';
                    if (emptyState) emptyState.style.display = 'grid';
                }
            };

            editorOverlays.forEach((overlay) => {
                const sourceInput = overlay.querySelector('[data-source-input]');
                const uploadInput = overlay.querySelector('[data-upload-input]');
                const cropInputs = overlay.querySelectorAll('[data-crop-x], [data-crop-y], [data-crop-zoom]');
                const clearInput = overlay.querySelector('[data-clear-input]');

                if (sourceInput) {
                    sourceInput.addEventListener('input', function () {
                        if (clearInput) clearInput.checked = false;
                        updatePreviewPresentation(overlay);
                    });
                }

                if (uploadInput) {
                    uploadInput.addEventListener('change', function () {
                        const [file] = uploadInput.files || [];
                        if (!file) return;
                        const reader = new FileReader();
                        reader.onload = function (event) {
                            if (sourceInput && typeof event.target?.result === 'string') {
                                sourceInput.value = event.target.result;
                            }
                            if (clearInput) clearInput.checked = false;
                            updatePreviewPresentation(overlay);
                        };
                        reader.readAsDataURL(file);
                    });
                }

                cropInputs.forEach((input) => {
                    input.addEventListener('input', function () {
                        updatePreviewPresentation(overlay);
                    });
                });

                if (clearInput) {
                    clearInput.addEventListener('change', function () {
                        updatePreviewPresentation(overlay);
                    });
                }

                updatePreviewPresentation(overlay);
            });

            @if ($errors->any())
                const firstOpenOverlay = document.querySelector('[data-hero-editor]');
                if (firstOpenOverlay) {
                    firstOpenOverlay.classList.add('is-open');
                    firstOpenOverlay.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                }
            @endif
        });
    </script>
@endsection
