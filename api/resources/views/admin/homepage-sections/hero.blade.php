@extends('admin.layout')

@section('title', 'Hero Slider & Promo Banners Studio')

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
            gap: 16px;
        }

        .hero-stage,
        .hero-promo-stack {
            min-width: 0;
        }

        .hero-stage {
            position: relative;
            min-height: 420px;
            overflow: hidden;
            background: #0f172a;
            border: 1px solid var(--border);
        }

        .hero-promo-stack {
            display: grid;
            gap: 16px;
            grid-template-rows: repeat(2, minmax(0, 1fr));
        }

        .hero-stage-copy,
        .hero-promo-copy {
            position: absolute;
            inset: auto auto 0 0;
            z-index: 2;
            padding: 24px;
            max-width: 440px;
            color: #ffffff;
            background: linear-gradient(180deg, rgba(9, 13, 22, 0) 0%, rgba(9, 13, 22, 0.85) 60%, rgba(9, 13, 22, 0.98) 100%);
        }

        .hero-stage-copy h4,
        .hero-promo-copy h4 {
            margin: 0 0 6px;
            font-size: 24px;
            font-weight: 800;
            color: #ffffff;
        }

        .hero-stage-copy p,
        .hero-promo-copy p {
            margin: 0;
            color: #cbd5e1;
            font-size: 13.5px;
            line-height: 1.5;
        }

        .hero-promo-card {
            position: relative;
            min-height: 200px;
            overflow: hidden;
            border: 1px solid var(--border);
            background: #0f172a;
        }

        .hero-empty-state {
            display: grid;
            place-items: center;
            min-height: 100%;
            padding: 24px;
            text-align: center;
            color: #64748b;
            font-size: 13.5px;
        }

        .hero-library-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
        }

        .hero-media-card {
            border: 1px solid var(--border);
            background: #ffffff;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: all 0.15s ease;
        }

        .hero-media-card:hover {
            border-color: #090d16;
        }

        .hero-media-card-top {
            position: relative;
            aspect-ratio: 1.1 / 1;
            background: #f1f5f9;
            border-bottom: 1px solid var(--border);
            overflow: hidden;
        }

        .hero-media-card-body {
            padding: 18px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .hero-card-meta {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .hero-card-meta h4 {
            margin: 0 0 4px;
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
        }

        .hero-card-meta p,
        .hero-media-note {
            margin: 0;
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
        }

        .hero-card-actions {
            margin-top: 4px;
        }

        .hero-media-tag-row {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .hero-media-filename {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 11px;
            color: #475569;
            background: #f1f5f9;
            padding: 4px 8px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
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
            padding: 12px 14px;
            background: linear-gradient(180deg, rgba(9, 13, 22, 0) 0%, rgba(9, 13, 22, 0.85) 100%);
            color: #fff;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 8px;
            z-index: 2;
        }

        .hero-media-overlay strong {
            display: block;
            font-size: 13px;
            color: #ffffff;
        }

        .hero-media-overlay span {
            font-size: 11px;
            color: #cbd5e1;
        }

        /* Modal Overlay & Dialog */
        .hero-editor-overlay {
            position: fixed;
            inset: 0;
            background: rgba(9, 13, 22, 0.75);
            backdrop-filter: blur(4px);
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
            overflow-y: auto;
            background: #ffffff;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
            border: 1px solid #1e293b;
        }

        .hero-editor-header {
            position: sticky;
            top: 0;
            z-index: 5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            background: #ffffff;
        }

        .hero-editor-header h4 {
            margin: 0 0 2px;
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        .hero-editor-header p {
            margin: 0;
            color: #64748b;
            font-size: 12.5px;
        }

        .hero-editor-body {
            padding: 24px;
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(340px, 0.9fr);
            gap: 24px;
            background: #ffffff;
        }

        .hero-crop-stage {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .hero-crop-preview {
            position: relative;
            min-height: 380px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
            background: #f1f5f9;
        }

        .hero-crop-preview.is-promo {
            min-height: 300px;
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
            background: rgba(0, 0, 0, 0.15);
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
            gap: 10px;
        }

        .hero-slider-control {
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 12px 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .hero-slider-control label {
            margin: 0;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
        }

        .hero-slider-control output {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
        }

        .hero-side-panel {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .hero-preview-note {
            padding: 12px 16px;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1e40af;
            font-size: 13px;
            line-height: 1.5;
        }

        .hero-editor-footer {
            position: sticky;
            bottom: 0;
            z-index: 4;
            padding: 16px 24px;
            background: #ffffff;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .hero-editor-footer p {
            margin: 0;
            color: #64748b;
            font-size: 12.5px;
        }

        .admin-sticky-savebar {
            position: sticky;
            bottom: 0;
            z-index: 90;
            background: #090d16;
            border-top: 1px solid #1e293b;
            padding: 16px 36px;
            margin: 32px -36px -32px -36px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 -10px 25px rgba(0,0,0,0.15);
        }

        @media (max-width: 991px) {
            .hero-preview-shell,
            .hero-editor-body {
                grid-template-columns: 1fr;
            }
            .admin-sticky-savebar {
                margin: 20px -16px -20px -16px;
                padding: 14px 16px;
            }
        }
    </style>

    <div class="dashboard-shell">
        @include('admin.partials.sidebar')

        <main class="admin-main">
            <div class="admin-shell-grid">
                <!-- Top Header Banner -->
                <div class="admin-banner">
                    <div>
                        <div class="brand">Storefront Media Studio</div>
                        <h2>Hero Carousel & Promo Banners</h2>
                        <p class="lead" style="margin-top: 4px;">Live preview, interactive crop controls, slide rotations, and side spotlights.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.homepage-sections.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back To Sections
                        </a>
                        <button type="submit" form="hero-editor-form" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Save All Changes
                        </button>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="p-3 mb-4" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; font-weight: 600;">
                        <strong>Please resolve the highlighted fields:</strong>
                        <ul class="mb-0 mt-2 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status'))
                    <div class="p-3 mb-4" style="background: #e8f7ee; border: 1px solid #c2ebd1; color: #0d532b; font-weight: 600;">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                    </div>
                @endif

                <!-- KPI Metric Tiles -->
                <div class="metrics-grid mb-4">
                    <div class="admin-stat">
                        <small>Main Slides Active</small>
                        <strong style="color: #2563eb;">{{ $heroEnabledCount }} <span style="color: #64748b; font-size: 16px;">/ {{ count($slides) }}</span></strong>
                        <span>Rotating hero slides</span>
                    </div>
                    <div class="admin-stat">
                        <small>Promo Spotlights</small>
                        <strong style="color: #16a34a;">{{ $promoEnabledCount }} <span style="color: #64748b; font-size: 16px;">/ {{ count($promos) }}</span></strong>
                        <span>Right side banners</span>
                    </div>
                    <div class="admin-stat">
                        <small>Autoplay Duration</small>
                        <strong>{{ old('autoplay_ms', $sliderSettings['autoplay_ms']) }} <span style="color: #64748b; font-size: 16px;">ms</span></strong>
                        <span>Slide transition interval</span>
                    </div>
                    <div class="admin-stat">
                        <small>Section Status</small>
                        <strong style="color: {{ $section->is_active ? '#16a34a' : '#dc2626' }};">
                            {{ $section->is_active ? 'Live Published' : 'Disabled' }}
                        </strong>
                        <span>Storefront visibility</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.homepage-sections.hero.update') }}" enctype="multipart/form-data" id="hero-editor-form">
                    @csrf
                    @method('PUT')

                    <!-- Live Storefront Simulation Stage -->
                    <section class="admin-section mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h3 class="mb-0">Live Storefront Simulation</h3>
                                <p class="muted mb-0" style="font-size: 13px;">Exact visual presentation rendered on the customer-facing storefront.</p>
                            </div>
                            <span class="badge bg-primary">Live View</span>
                        </div>

                        <div class="hero-preview-shell">
                            <!-- Main Hero Slide Preview -->
                            <div class="hero-stage">
                                @if (!empty($activeSlide['image'] ?? null))
                                    <img
                                        src="{{ $activeSlide['preview_image'] ?? $activeSlide['image'] }}"
                                        alt="{{ $activeSlide['alt'] ?: 'Active hero slide preview' }}"
                                        class="hero-media-preview"
                                        style="object-position: {{ $activeSlide['crop_x'] ?? 50 }}% {{ $activeSlide['crop_y'] ?? 50 }}%; transform: scale({{ $activeSlide['crop_zoom'] ?? 1 }});"
                                    >
                                    <div class="hero-stage-copy">
                                        <span class="badge bg-success mb-2">Active Main Slide</span>
                                        <h4>{{ $activeSlide['title'] ?: 'Fine Jewellery Collection' }}</h4>
                                        <p>{{ $activeSlide['href'] ?: '/shop' }}</p>
                                    </div>
                                @else
                                    <div class="hero-empty-state">
                                        <div>
                                            <i class="bi bi-image" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                                            No active main slide image yet.
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Right-Side Promo Cards Preview -->
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
                                                <span class="badge {{ !empty($previewPromo['is_active']) ? 'bg-success' : 'bg-secondary' }} mb-2">
                                                    {{ !empty($previewPromo['is_active']) ? 'Active Promo' : 'Inactive' }}
                                                </span>
                                                <h4 style="font-size: 18px;">{{ $previewPromo['title'] ?: 'Promo Banner '.($promoIndex + 1) }}</h4>
                                                <p>{{ $previewPromo['subtitle'] ?: ($previewPromo['href'] ?: 'Curated Fine Jewellery') }}</p>
                                            </div>
                                        @else
                                            <div class="hero-empty-state">
                                                <div>
                                                    <i class="bi bi-card-image" style="font-size: 24px; display: block; margin-bottom: 10px;"></i>
                                                    Promo banner {{ $promoIndex + 1 }} has no image yet.
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>

                    <!-- Core Hero Settings & Configuration -->
                    <section class="admin-section mb-4">
                        <h3 class="mb-1">Hero Section Parameters</h3>
                        <p class="muted mb-3" style="font-size: 13px;">Manage section titles, overlay CTA action buttons, and autoplay intervals.</p>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Admin Label</label>
                                <input id="label" name="label" class="form-control" value="{{ old('label', $section->label) }}" placeholder="e.g. Homepage Hero" />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Section Title</label>
                                <input id="title" name="title" class="form-control" value="{{ old('title', $section->title) }}" placeholder="Public section title" />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Section Subtitle</label>
                                <input id="subtitle" name="subtitle" class="form-control" value="{{ old('subtitle', $section->subtitle) }}" />
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Primary Button Text</label>
                                <input id="button_text" name="button_text" class="form-control" value="{{ old('button_text', $section->button_text) }}" placeholder="Shop the Collection">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Primary Button URL</label>
                                <input id="button_url" name="button_url" class="form-control" value="{{ old('button_url', $section->button_url) }}" placeholder="/shop">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Secondary Button Text</label>
                                <input id="secondary_button_text" name="secondary_button_text" class="form-control" value="{{ old('secondary_button_text', $secondaryButtonText) }}" placeholder="Explore Gifting Picks">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Secondary Button URL</label>
                                <input id="secondary_button_url" name="secondary_button_url" class="form-control" value="{{ old('secondary_button_url', $secondaryButtonUrl) }}" placeholder="/shop?category=rings">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Autoplay Speed (ms)</label>
                                <input id="autoplay_ms" name="autoplay_ms" type="number" min="1000" max="15000" step="100" class="form-control" value="{{ old('autoplay_ms', $sliderSettings['autoplay_ms']) }}" />
                                <small class="text-muted">Default is 4000ms (4 seconds per slide).</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Display Sort Order</label>
                                <input id="sort_order" name="sort_order" type="number" class="form-control" value="{{ old('sort_order', $section->sort_order) }}" />
                            </div>
                        </div>

                        <div class="d-flex gap-4 flex-wrap pt-2" style="border-top: 1px solid var(--border);">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $section->is_active))>
                                <label class="form-check-label" for="is_active" style="font-weight: 600; font-size: 13px;">
                                    Publish Entire Hero Section
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="show_text" value="1" id="show_text" @checked(old('show_text', $sliderSettings['show_text']))>
                                <label class="form-check-label" for="show_text" style="font-weight: 600; font-size: 13px;">
                                    Show Slide Text Overlays
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="show_dots" value="1" id="show_dots" @checked(old('show_dots', $sliderSettings['show_dots']))>
                                <label class="form-check-label" for="show_dots" style="font-weight: 600; font-size: 13px;">
                                    Enable Navigation Dots
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="show_arrows" value="1" id="show_arrows" @checked(old('show_arrows', $sliderSettings['show_arrows']))>
                                <label class="form-check-label" for="show_arrows" style="font-weight: 600; font-size: 13px;">
                                    Enable Navigation Arrows
                                </label>
                            </div>
                        </div>
                    </section>

                    <!-- Main Hero Carousel Slides Grid -->
                    <section class="admin-section mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h3 class="mb-0">Main Carousel Slides</h3>
                                <p class="muted mb-0" style="font-size: 13px;">Recommended dimensions: <strong>1600 × 1100 px</strong>. Click Edit to adjust crop and media.</p>
                            </div>
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
                                                    <span>{{ old("slides.$index.is_active", $slide['is_active']) ? 'Visible in Rotation' : 'Hidden' }}</span>
                                                </div>
                                                <span class="badge {{ old("slides.$index.is_active", $slide['is_active']) ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ old("slides.$index.is_active", $slide['is_active']) ? 'Active' : 'Hidden' }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="hero-empty-state">
                                                <div>
                                                    <i class="bi bi-image" style="font-size: 24px; display: block; margin-bottom: 8px;"></i>
                                                    No image uploaded
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="hero-media-card-body">
                                        <div class="hero-card-meta">
                                            <div>
                                                <h4>Slide {{ $index + 1 }}</h4>
                                                <p>{{ $slide['href'] ?: 'No target link' }}</p>
                                            </div>
                                            <span class="badge bg-primary">1600 × 1100</span>
                                        </div>
                                        <div class="hero-media-tag-row">
                                            <span class="hero-media-filename">{{ $slideImageName }}</span>
                                        </div>
                                        <div class="hero-card-actions">
                                            <button type="button" class="btn btn-outline-secondary btn-sm w-100" data-open-hero-editor="slide-{{ $index }}" onclick="openHeroEditor('slide-{{ $index }}')">
                                                <i class="bi bi-pencil-square me-1"></i> Edit Slide
                                            </button>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>

                    <!-- Right-Side Promo Spotlight Banners Grid -->
                    <section class="admin-section mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h3 class="mb-0">Right-Side Promo Spotlights</h3>
                                <p class="muted mb-0" style="font-size: 13px;">Recommended dimensions: <strong>900 × 620 px</strong>. Click Edit to customize banners.</p>
                            </div>
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
                                                    <strong>{{ $promo['title'] ?: 'Promo '.($index + 1) }}</strong>
                                                    <span>{{ old("promos.$index.is_active", $promo['is_active']) ? 'Active Spotlight' : 'Hidden' }}</span>
                                                </div>
                                                <span class="badge {{ old("promos.$index.is_active", $promo['is_active']) ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ old("promos.$index.is_active", $promo['is_active']) ? 'Active' : 'Hidden' }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="hero-empty-state">
                                                <div>
                                                    <i class="bi bi-card-image" style="font-size: 24px; display: block; margin-bottom: 8px;"></i>
                                                    No image uploaded
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="hero-media-card-body">
                                        <div class="hero-card-meta">
                                            <div>
                                                <h4>Promo {{ $index + 1 }}</h4>
                                                <p>{{ $promo['subtitle'] ?: ($promo['href'] ?: 'No target link') }}</p>
                                            </div>
                                            <span class="badge bg-primary">900 × 620</span>
                                        </div>
                                        <div class="hero-media-tag-row">
                                            <span class="hero-media-filename">{{ $promoImageName }}</span>
                                        </div>
                                        <div class="hero-card-actions">
                                            <button type="button" class="btn btn-outline-secondary btn-sm w-100" data-open-hero-editor="promo-{{ $index }}" onclick="openHeroEditor('promo-{{ $index }}')">
                                                <i class="bi bi-pencil-square me-1"></i> Edit Promo
                                            </button>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>

                    <!-- Slide Modal Editors (Popups) -->
                    @foreach ($slides as $index => $slide)
                        <div class="hero-editor-overlay" data-hero-editor="slide-{{ $index }}" aria-hidden="true">
                            <div class="hero-editor-dialog">
                                <div class="hero-editor-header">
                                    <div>
                                        <h4>Edit Slide {{ $index + 1 }}</h4>
                                        <p>Upload or replace media, adjust crop, and configure target URLs.</p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-close-hero-editor>Close</button>
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

                                        <div class="admin-field">
                                            <label>Slide Image URL</label>
                                            <input name="slide_urls[{{ $index }}]" value="{{ old("slide_urls.$index", '') }}" maxlength="255" placeholder="Paste new image URL" data-source-input>
                                            <div class="admin-help">Leave blank to retain current slide asset.</div>
                                        </div>
                                        <div class="admin-field">
                                            <label>Upload New Image File</label>
                                            <input type="file" name="slide_files[{{ $index }}]" accept="image/*" data-upload-input>
                                            <div class="admin-help">Supported formats: JPG, PNG, WebP.</div>
                                        </div>
                                        <div class="admin-field">
                                            <label>Display Title</label>
                                            <input name="slides[{{ $index }}][title]" value="{{ old("slides.$index.title", $slide['title']) }}" placeholder="e.g. Solitaire Diamond Edit">
                                        </div>
                                        <div class="admin-field">
                                            <label>Image Alt Text</label>
                                            <input name="slides[{{ $index }}][alt]" value="{{ old("slides.$index.alt", $slide['alt']) }}" placeholder="Descriptive accessibility label">
                                        </div>
                                        <div class="admin-field">
                                            <label>Target URL</label>
                                            <input name="slides[{{ $index }}][href]" value="{{ old("slides.$index.href", $slide['href']) }}" placeholder="/shop?category=rings">
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="slides[{{ $index }}][is_active]" value="1" id="slide_active_{{ $index }}" @checked(old("slides.$index.is_active", $slide['is_active']))>
                                            <label class="form-check-label" for="slide_active_{{ $index }}" style="font-weight: 600; font-size: 13px;">
                                                Publish this slide in rotation
                                            </label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="clear_slide_image[{{ $index }}]" value="1" id="clear_slide_{{ $index }}" data-clear-input>
                                            <label class="form-check-label text-danger" for="clear_slide_{{ $index }}" style="font-weight: 600; font-size: 13px;">
                                                Remove current slide image on save
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="hero-editor-footer">
                                    <p>Popup editor updates preview immediately. Click Save All Changes to persist to database.</p>
                                    <button type="button" class="btn btn-primary btn-sm" data-close-hero-editor>Done</button>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Promo Modal Editors (Popups) -->
                    @foreach ($promos as $index => $promo)
                        <div class="hero-editor-overlay" data-hero-editor="promo-{{ $index }}" aria-hidden="true">
                            <div class="hero-editor-dialog">
                                <div class="hero-editor-header">
                                    <div>
                                        <h4>Edit Promo Banner {{ $index + 1 }}</h4>
                                        <p>Upload or replace media, adjust crop, and configure headline text.</p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-close-hero-editor>Close</button>
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
                                                        Upload an image or paste a media URL to preview crop here.
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

                                        <div class="admin-field">
                                            <label>Promo Image URL</label>
                                            <input name="promo_urls[{{ $index }}]" value="{{ old("promo_urls.$index", '') }}" maxlength="255" placeholder="Paste replacement image URL" data-source-input>
                                        </div>
                                        <div class="admin-field">
                                            <label>Upload New Image File</label>
                                            <input type="file" name="promo_files[{{ $index }}]" accept="image/*" data-upload-input>
                                        </div>
                                        <div class="admin-field">
                                            <label>Display Title</label>
                                            <input name="promos[{{ $index }}][title]" value="{{ old("promos.$index.title", $promo['title']) }}" placeholder="e.g. Festive Gold Spotlight">
                                        </div>
                                        <div class="admin-field">
                                            <label>Subtitle / Caption</label>
                                            <input name="promos[{{ $index }}][subtitle]" value="{{ old("promos.$index.subtitle", $promo['subtitle']) }}" placeholder="e.g. Flat 10% OFF with code KANAKSHI10">
                                        </div>
                                        <div class="admin-field">
                                            <label>Target URL</label>
                                            <input name="promos[{{ $index }}][href]" value="{{ old("promos.$index.href", $promo['href']) }}" placeholder="/shop?category=earrings">
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="promos[{{ $index }}][is_active]" value="1" id="promo_active_{{ $index }}" @checked(old("promos.$index.is_active", $promo['is_active']))>
                                            <label class="form-check-label" for="promo_active_{{ $index }}" style="font-weight: 600; font-size: 13px;">
                                                Include in Right-Side Stack
                                            </label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="promos[{{ $index }}][show_text]" value="1" id="promo_show_text_{{ $index }}" @checked(old("promos.$index.show_text", $promo['show_text']))>
                                            <label class="form-check-label" for="promo_show_text_{{ $index }}" style="font-weight: 600; font-size: 13px;">
                                                Show Title & Subtitle Overlays
                                            </label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="clear_promo_image[{{ $index }}]" value="1" id="clear_promo_{{ $index }}" data-clear-input>
                                            <label class="form-check-label text-danger" for="clear_promo_{{ $index }}" style="font-weight: 600; font-size: 13px;">
                                                Remove current promo image on save
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="hero-editor-footer">
                                    <p>Popup editor updates preview immediately. Click Save All Changes to persist.</p>
                                    <button type="button" class="btn btn-primary btn-sm" data-close-hero-editor>Done</button>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Sticky Bottom Action Bar -->
                    <div class="admin-sticky-savebar">
                        <div style="color: #94a3b8; font-size: 13px;">
                            <i class="bi bi-info-circle me-1"></i> Changes will update live on the storefront immediately after saving.
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.homepage-sections.index') }}" class="btn btn-outline-secondary">Discard</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Save All Changes
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </main>
    </div>
@endsection

@push('scripts')
    <script>
        // Global open/close helper functions
        window.openHeroEditor = function(targetId) {
            const overlay = document.querySelector(`[data-hero-editor="${targetId}"]`);
            if (overlay) {
                overlay.classList.add('is-open');
                overlay.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }
        };

        window.closeHeroEditor = function(elementOrId) {
            let overlay;
            if (typeof elementOrId === 'string') {
                overlay = document.querySelector(`[data-hero-editor="${elementOrId}"]`);
            } else if (elementOrId && elementOrId.closest) {
                overlay = elementOrId.closest('.hero-editor-overlay');
            }
            if (overlay) {
                overlay.classList.remove('is-open');
                overlay.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            // Open editor popup via data attribute
            document.querySelectorAll('[data-open-hero-editor]').forEach(button => {
                button.addEventListener('click', () => {
                    const targetId = button.getAttribute('data-open-hero-editor');
                    window.openHeroEditor(targetId);
                });
            });

            // Close editor popup via data attribute
            document.querySelectorAll('[data-close-hero-editor]').forEach(button => {
                button.addEventListener('click', () => {
                    window.closeHeroEditor(button);
                });
            });

            // Close on backdrop click
            document.querySelectorAll('.hero-editor-overlay').forEach(overlay => {
                overlay.addEventListener('click', (e) => {
                    if (e.target === overlay) {
                        window.closeHeroEditor(overlay);
                    }
                });
            });

            // Live Slider Crop & Zoom Updates
            const updateTransform = (container) => {
                const img = container.querySelector('[data-preview-image]');
                if (!img) return;

                const cropX = container.querySelector('[data-crop-x]')?.value || 50;
                const cropY = container.querySelector('[data-crop-y]')?.value || 50;
                const zoom = container.querySelector('[data-crop-zoom]')?.value || 1;

                img.style.objectPosition = `${cropX}% ${cropY}%`;
                img.style.transform = `scale(${zoom})`;
            };

            document.querySelectorAll('.hero-editor-dialog').forEach(dialog => {
                dialog.querySelectorAll('input[type="range"]').forEach(input => {
                    input.addEventListener('input', () => {
                        const output = input.parentElement.querySelector('[data-range-output]');
                        if (output) {
                            output.textContent = input.name.includes('zoom') ? `${parseFloat(input.value).toFixed(2)}x` : `${input.value}%`;
                        }
                        updateTransform(dialog);
                    });
                });

                // Live File Upload Preview
                const fileInput = dialog.querySelector('[data-upload-input]');
                if (fileInput) {
                    fileInput.addEventListener('change', () => {
                        if (fileInput.files && fileInput.files[0]) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                let img = dialog.querySelector('[data-preview-image]');
                                const stage = dialog.querySelector('[data-preview-stage]');
                                const empty = dialog.querySelector('[data-preview-empty]');

                                if (empty) empty.style.display = 'none';

                                if (!img && stage) {
                                    img = document.createElement('img');
                                    img.className = 'hero-media-preview';
                                    img.setAttribute('data-preview-image', '');
                                    stage.prepend(img);
                                }

                                if (img) {
                                    img.src = e.target.result;
                                    updateTransform(dialog);
                                }
                            };
                            reader.readAsDataURL(fileInput.files[0]);
                        }
                    });
                }
            });
        });
    </script>
@endpush
