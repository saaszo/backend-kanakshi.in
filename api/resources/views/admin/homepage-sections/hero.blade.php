@extends('admin.layout')

@section('title', 'Hero Slider Editor')

@php
    $heroEnabledCount = collect($slides)->where('is_active', true)->count();
    $promoEnabledCount = collect($promos)->where('is_active', true)->count();
@endphp

@section('content')
    <style>
        .hero-editor-shell {
            display: grid;
            gap: 24px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .hero-editor-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 32px;
            border-radius: var(--radius-xl);
            background: linear-gradient(135deg, #1e3a8a 0%, var(--primary) 100%);
            color: #fff;
            box-shadow: 0 12px 32px rgba(37, 99, 235, 0.25);
            position: relative;
            overflow: hidden;
        }
        
        .hero-editor-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .hero-editor-banner h2 {
            margin: 0 0 8px;
            font-size: 28px;
            color: #fff;
            position: relative;
            z-index: 2;
        }

        .hero-editor-banner p {
            margin: 0;
            color: rgba(255, 255, 255, 0.85);
            font-size: 15px;
            max-width: 600px;
            line-height: 1.6;
            position: relative;
            z-index: 2;
        }

        .hero-editor-banner .brand {
            color: rgba(255,255,255,0.9);
            position: relative;
            z-index: 2;
        }
        
        .hero-editor-banner .button.secondary {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            color: #fff;
            position: relative;
            z-index: 2;
            backdrop-filter: blur(8px);
        }
        
        .hero-editor-banner .button.secondary:hover {
            background: #fff;
            color: var(--primary-dark);
        }

        .hero-editor-toast {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 20px;
            border-radius: var(--radius-lg);
            border: 1px solid rgba(5, 150, 105, 0.2);
            background: #ecfdf5;
            color: var(--success);
            box-shadow: var(--shadow-soft);
        }

        .hero-editor-toast strong {
            display: block;
            font-size: 15px;
            margin-bottom: 2px;
        }
        
        .hero-editor-toast p {
            margin: 0;
            font-size: 13px;
            opacity: 0.9;
        }

        .hero-editor-errors {
            padding: 16px 20px;
            border-radius: var(--radius-lg);
            border: 1px solid rgba(220, 38, 38, 0.2);
            background: #fef2f2;
            color: var(--danger);
        }

        .hero-editor-errors ul {
            margin: 8px 0 0;
            padding-left: 20px;
            font-size: 14px;
        }

        .hero-editor-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .hero-editor-stat {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            padding: 24px;
            box-shadow: var(--shadow-soft);
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }
        
        .hero-editor-stat::after {
            content: '';
            position: absolute;
            left: 24px;
            right: 24px;
            bottom: 0;
            height: 3px;
            border-radius: 4px 4px 0 0;
            background: linear-gradient(90deg, var(--primary), #60a5fa);
        }

        .hero-editor-stat span {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-soft);
            margin-bottom: 8px;
        }

        .hero-editor-stat strong {
            font-size: 32px;
            line-height: 1;
            color: var(--heading);
        }

        .hero-editor-section {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            padding: 32px;
            box-shadow: var(--shadow-soft);
        }

        .hero-editor-section-header {
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }

        .hero-editor-section-header h3 {
            margin: 0 0 8px;
            font-size: 22px;
            color: var(--heading);
        }

        .hero-editor-section-header p {
            margin: 0;
            color: var(--text-soft);
            font-size: 14px;
            line-height: 1.6;
        }

        .hero-editor-fields {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
        }

        .hero-editor-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .hero-editor-field label {
            margin: 0;
        }
        
        .hero-editor-help {
            color: var(--text-soft);
            font-size: 12px;
            line-height: 1.5;
            margin-top: -2px;
        }

        .hero-editor-toggle-row {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
        }

        .hero-editor-toggle {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 18px;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            background: var(--bg-soft);
            font-size: 14px;
            font-weight: 600;
            color: var(--heading);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .hero-editor-toggle:hover {
            border-color: var(--primary);
            background: #fff;
        }
        
        .hero-editor-toggle input {
            width: 18px;
            height: 18px;
            margin: 0;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .hero-editor-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 24px;
        }

        .hero-editor-card {
            background: #fff;
            border: 1px solid var(--border-strong);
            border-radius: var(--radius-lg);
            padding: 24px;
            transition: box-shadow 0.2s ease;
            display: flex;
            flex-direction: column;
        }
        
        .hero-editor-card:hover {
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
            border-color: rgba(37, 99, 235, 0.3);
        }

        .hero-editor-card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }

        .hero-editor-card-head h4 {
            margin: 0 0 4px;
            font-size: 18px;
            color: var(--heading);
        }

        .hero-editor-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            background: rgba(15, 23, 42, 0.06);
            color: var(--text-soft);
        }
        
        .hero-editor-badge.active {
            background: rgba(5, 150, 105, 0.1);
            color: var(--success);
        }

        .hero-editor-preview-wrap {
            display: grid;
            grid-template-columns: 140px minmax(0, 1fr);
            gap: 20px;
            margin: 20px 0;
            align-items: stretch;
            flex-grow: 1;
        }

        .hero-editor-preview {
            width: 100%;
            height: 100%;
            min-height: 100px;
            object-fit: cover;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            background: #f8fafc;
        }

        .hero-editor-empty-preview {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100px;
            border-radius: var(--radius-md);
            border: 1px dashed var(--border-strong);
            background: #f8fafc;
            color: var(--text-soft);
            font-size: 13px;
            text-align: center;
            padding: 16px;
        }

        .hero-editor-upload {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 12px;
            padding: 16px;
            border-radius: var(--radius-md);
            border: 1px dashed rgba(37, 99, 235, 0.3);
            background: var(--bg-soft);
        }

        .hero-editor-upload strong {
            font-size: 14px;
            color: var(--heading);
        }

        .hero-editor-upload input[type="file"] {
            font-size: 13px;
            padding: 8px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #fff;
            cursor: pointer;
        }

        .hero-editor-spec-grid {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .hero-editor-spec {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 6px;
            background: #fff;
            border: 1px solid var(--border);
            font-size: 12px;
        }
        
        .hero-editor-spec strong {
            color: var(--heading);
            font-size: 12px;
        }
        
        .hero-editor-spec span {
            color: var(--text-soft);
        }

        .hero-editor-inline-error {
            color: var(--danger);
            font-size: 13px;
            font-weight: 600;
            margin-top: 4px;
        }

        .hero-editor-savebar {
            position: sticky;
            bottom: 24px;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 20px 32px;
            border-radius: var(--radius-xl);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.1);
        }
        
        .hero-editor-savebar-text strong {
            display: block;
            font-size: 16px;
            color: var(--heading);
            margin-bottom: 4px;
        }
        
        .hero-editor-savebar-text p {
            margin: 0;
            font-size: 13px;
            color: var(--text-soft);
        }

        @media (max-width: 1024px) {
            .hero-editor-cards {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .hero-editor-banner {
                flex-direction: column;
                align-items: flex-start;
                padding: 24px;
            }
            .hero-editor-savebar {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }
            .hero-editor-savebar .button-row {
                justify-content: center;
            }
            .hero-editor-preview-wrap {
                grid-template-columns: 1fr;
            }
            .hero-editor-preview, .hero-editor-empty-preview {
                height: 180px;
            }
        }
    </style>

    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="hero-editor-shell">
                <div class="hero-editor-banner">
                    <div>
                        <div class="brand">Homepage Settings</div>
                        <h2>Hero Slider Configuration</h2>
                        <p>Manage the main hero slider and accompanying right-side promotional banners. Professional dashboard tools to fine-tune your homepage engagement.</p>
                    </div>
                    <a href="{{ route('admin.homepage-sections.index') }}" class="button secondary small">
                        <i class="bi bi-arrow-left"></i> Back To Sections
                    </a>
                </div>

                @if ($errors->any())
                    <div class="hero-editor-errors">
                        <strong>Please fix the highlighted fields to continue.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status'))
                    <div class="hero-editor-toast" id="hero-editor-success">
                        <div>
                            <strong>Changes Saved Successfully</strong>
                            <p>{{ session('status') }}</p>
                        </div>
                        <button type="button" class="button secondary small" id="hero-editor-toast-close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                @endif

                <div class="hero-editor-overview">
                    <div class="hero-editor-stat">
                        <span>Main Slides Active</span>
                        <strong>{{ $heroEnabledCount }} <span style="color: var(--text-soft); font-size: 18px;">/ {{ count($slides) }}</span></strong>
                    </div>
                    <div class="hero-editor-stat">
                        <span>Promo Banners Active</span>
                        <strong>{{ $promoEnabledCount }} <span style="color: var(--text-soft); font-size: 18px;">/ {{ count($promos) }}</span></strong>
                    </div>
                    <div class="hero-editor-stat">
                        <span>Autoplay Speed</span>
                        <strong>{{ old('autoplay_ms', $sliderSettings['autoplay_ms']) }} <span style="color: var(--text-soft); font-size: 18px;">ms</span></strong>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.homepage-sections.hero.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div style="display: grid; gap: 24px;">

                        <section class="hero-editor-section">
                            <div class="hero-editor-section-header">
                                <h3>Hero Settings & Options</h3>
                                <p>Control the core behavior, appearance, and speed of the main slider.</p>
                            </div>

                            <div class="hero-editor-fields">
                                <div class="hero-editor-field">
                                    <label for="label">Admin Label</label>
                                    <input id="label" name="label" value="{{ old('label', $section->label) }}" placeholder="e.g. Homepage Hero" />
                                    @error('label') <div class="hero-editor-inline-error">{{ $message }}</div> @enderror
                                </div>
                                <div class="hero-editor-field">
                                    <label for="title">Section Title</label>
                                    <input id="title" name="title" value="{{ old('title', $section->title) }}" placeholder="Public section title" />
                                    @error('title') <div class="hero-editor-inline-error">{{ $message }}</div> @enderror
                                </div>
                                <div class="hero-editor-field">
                                    <label for="subtitle">Section Subtitle</label>
                                    <input id="subtitle" name="subtitle" value="{{ old('subtitle', $section->subtitle) }}" />
                                    @error('subtitle') <div class="hero-editor-inline-error">{{ $message }}</div> @enderror
                                </div>
                                <div class="hero-editor-field">
                                    <label for="heading">Section Heading</label>
                                    <input id="heading" name="heading" value="{{ old('heading', $section->heading) }}" />
                                    @error('heading') <div class="hero-editor-inline-error">{{ $message }}</div> @enderror
                                </div>
                                <div class="hero-editor-field">
                                    <label for="sort_order">Display Sort Order</label>
                                    <input id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', $section->sort_order) }}" />
                                    @error('sort_order') <div class="hero-editor-inline-error">{{ $message }}</div> @enderror
                                </div>
                                <div class="hero-editor-field">
                                    <label for="autoplay_ms">Slider Transition Speed (ms)</label>
                                    <input id="autoplay_ms" name="autoplay_ms" type="number" min="1000" max="15000" step="100" value="{{ old('autoplay_ms', $sliderSettings['autoplay_ms']) }}" />
                                    <div class="hero-editor-help">Recommended speed is between 3000ms and 4500ms.</div>
                                    @error('autoplay_ms') <div class="hero-editor-inline-error">{{ $message }}</div> @enderror
                                </div>
                                <div class="hero-editor-field">
                                    <label for="nav_gap">Navigation Gap (px)</label>
                                    <input id="nav_gap" name="nav_gap" type="number" min="0" max="240" value="{{ old('nav_gap', $sliderSettings['nav_gap']) }}" />
                                    <div class="hero-editor-help">Spacing between slider navigation components.</div>
                                    @error('nav_gap') <div class="hero-editor-inline-error">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="hero-editor-toggle-row">
                                <label class="hero-editor-toggle">
                                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $section->is_active))> 
                                    <span>Entire Hero Section Active</span>
                                </label>
                                <label class="hero-editor-toggle">
                                    <input type="checkbox" name="show_text" value="1" @checked(old('show_text', $sliderSettings['show_text']))> 
                                    <span>Show Slide Title Text</span>
                                </label>
                                <label class="hero-editor-toggle">
                                    <input type="checkbox" name="show_dots" value="1" @checked(old('show_dots', $sliderSettings['show_dots']))> 
                                    <span>Enable Slider Dots</span>
                                </label>
                                <label class="hero-editor-toggle">
                                    <input type="checkbox" name="show_arrows" value="1" @checked(old('show_arrows', $sliderSettings['show_arrows']))> 
                                    <span>Enable Navigation Arrows</span>
                                </label>
                            </div>
                        </section>

                        <section class="hero-editor-section">
                            <div class="hero-editor-section-header">
                                <h3>Main Hero Slides</h3>
                                <p>Manage individual slides. Upload high-quality imagery for the best visual impact. Recommended size: <strong>1600 × 1100 px</strong>.</p>
                            </div>

                            <div class="hero-editor-cards">
                                @foreach ($slides as $index => $slide)
                                    <article class="hero-editor-card">
                                        <div class="hero-editor-card-head">
                                            <div>
                                                <h4>Slide {{ $index + 1 }}</h4>
                                                <p class="hero-editor-help" style="margin: 0;">Primary rotating banner component.</p>
                                            </div>
                                            <span class="hero-editor-badge {{ old("slides.$index.is_active", $slide['is_active']) ? 'active' : '' }}">
                                                {{ old("slides.$index.is_active", $slide['is_active']) ? 'Active' : 'Hidden' }}
                                            </span>
                                        </div>

                                        <label class="hero-editor-toggle" style="margin-bottom: 16px; align-self: flex-start;">
                                            <input type="checkbox" name="slides[{{ $index }}][is_active]" value="1" @checked(old("slides.$index.is_active", $slide['is_active']))> 
                                            <span>Publish Slide</span>
                                        </label>

                                        <div class="hero-editor-preview-wrap">
                                            @if (!empty($slide['image']))
                                                <img src="{{ $slide['image'] }}" alt="Slide preview" class="hero-editor-preview" />
                                            @else
                                                <div class="hero-editor-empty-preview">
                                                    <div>
                                                        <i class="bi bi-image" style="font-size: 24px; color: var(--border-strong); margin-bottom: 8px; display: block;"></i>
                                                        No Image Provided
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="hero-editor-upload">
                                                <strong>Upload Media Asset</strong>
                                                <div class="hero-editor-help" style="margin-bottom: 8px;">Supports JPG, PNG, and WebP formats.</div>
                                                <div class="hero-editor-spec-grid" style="margin-bottom: 12px;">
                                                    <div class="hero-editor-spec">
                                                        <strong>Target Size:</strong> <span>1600 × 1100 px</span>
                                                    </div>
                                                    <div class="hero-editor-spec">
                                                        <strong>Max Size:</strong> <span>350 KB</span>
                                                    </div>
                                                </div>
                                                <input type="file" name="slide_files[{{ $index }}]" accept="image/*" class="js-admin-file-input">
                                            </div>
                                        </div>
                                        
                                        <div class="hero-editor-fields" style="margin-top: 0; display: grid; gap: 16px;">
                                            <div class="hero-editor-field">
                                                <label>Display Title</label>
                                                <input name="slides[{{ $index }}][title]" value="{{ old("slides.$index.title", $slide['title']) }}" placeholder="e.g. Summer Collection" />
                                            </div>
                                            <div class="hero-editor-field">
                                                <label>Image Alt Text (SEO)</label>
                                                <input name="slides[{{ $index }}][alt]" value="{{ old("slides.$index.alt", $slide['alt']) }}" placeholder="Descriptive text for accessibility" />
                                            </div>
                                            <div class="hero-editor-field" style="grid-column: 1 / -1;">
                                                <label>Target URL (Click Destination)</label>
                                                <input name="slides[{{ $index }}][href]" value="{{ old("slides.$index.href", $slide['href']) }}" placeholder="/shop?category=featured" />
                                            </div>
                                        </div>

                                        <label class="hero-editor-toggle" style="margin-top: 16px; align-self: flex-start; background: transparent; border-color: transparent;">
                                            <input type="checkbox" name="clear_slide_image[{{ $index }}]" value="1"> 
                                            <span style="color: var(--danger);">Clear current image on save</span>
                                        </label>
                                    </article>
                                @endforeach
                            </div>
                        </section>

                        <section class="hero-editor-section">
                            <div class="hero-editor-section-header">
                                <h3>Right-Side Promo Banners</h3>
                                <p>Manage the secondary stacked promotional blocks alongside the main slider. Recommended size: <strong>900 × 620 px</strong>.</p>
                            </div>

                            <div class="hero-editor-cards">
                                @foreach ($promos as $index => $promo)
                                    <article class="hero-editor-card">
                                        <div class="hero-editor-card-head">
                                            <div>
                                                <h4>Promo Banner {{ $index + 1 }}</h4>
                                                <p class="hero-editor-help" style="margin: 0;">Supporting promotional graphic.</p>
                                            </div>
                                            <span class="hero-editor-badge {{ old("promos.$index.is_active", $promo['is_active']) ? 'active' : '' }}">
                                                {{ old("promos.$index.is_active", $promo['is_active']) ? 'Active' : 'Hidden' }}
                                            </span>
                                        </div>

                                        <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
                                            <label class="hero-editor-toggle">
                                                <input type="checkbox" name="promos[{{ $index }}][is_active]" value="1" @checked(old("promos.$index.is_active", $promo['is_active']))> 
                                                <span>Publish Banner</span>
                                            </label>
                                            <label class="hero-editor-toggle">
                                                <input type="checkbox" name="promos[{{ $index }}][show_text]" value="1" @checked(old("promos.$index.show_text", $promo['show_text']))> 
                                                <span>Display Overlay Text</span>
                                            </label>
                                        </div>

                                        <div class="hero-editor-preview-wrap">
                                            @if (!empty($promo['image']))
                                                <img src="{{ $promo['image'] }}" alt="Promo preview" class="hero-editor-preview" />
                                            @else
                                                <div class="hero-editor-empty-preview">
                                                    <div>
                                                        <i class="bi bi-image" style="font-size: 24px; color: var(--border-strong); margin-bottom: 8px; display: block;"></i>
                                                        No Image Provided
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="hero-editor-upload">
                                                <strong>Upload Media Asset</strong>
                                                <div class="hero-editor-help" style="margin-bottom: 8px;">Ensure consistent sizing for balanced layout.</div>
                                                <div class="hero-editor-spec-grid" style="margin-bottom: 12px;">
                                                    <div class="hero-editor-spec">
                                                        <strong>Target Size:</strong> <span>900 × 620 px</span>
                                                    </div>
                                                    <div class="hero-editor-spec">
                                                        <strong>Max Size:</strong> <span>250 KB</span>
                                                    </div>
                                                </div>
                                                <input type="file" name="promo_files[{{ $index }}]" accept="image/*" class="js-admin-file-input">
                                            </div>
                                        </div>
                                        
                                        <div class="hero-editor-fields" style="margin-top: 0; display: grid; gap: 16px;">
                                            <div class="hero-editor-field">
                                                <label>Primary Title</label>
                                                <input name="promos[{{ $index }}][title]" value="{{ old("promos.$index.title", $promo['title']) }}" placeholder="e.g. Wall Decor" />
                                            </div>
                                            <div class="hero-editor-field">
                                                <label>Secondary Subtitle</label>
                                                <input name="promos[{{ $index }}][subtitle]" value="{{ old("promos.$index.subtitle", $promo['subtitle']) }}" placeholder="e.g. Up to 40% Off" />
                                            </div>
                                            <div class="hero-editor-field" style="grid-column: 1 / -1;">
                                                <label>Target URL (Click Destination)</label>
                                                <input name="promos[{{ $index }}][href]" value="{{ old("promos.$index.href", $promo['href']) }}" placeholder="/shop?category=decor" />
                                            </div>
                                        </div>

                                        <label class="hero-editor-toggle" style="margin-top: 16px; align-self: flex-start; background: transparent; border-color: transparent;">
                                            <input type="checkbox" name="clear_promo_image[{{ $index }}]" value="1"> 
                                            <span style="color: var(--danger);">Clear current image on save</span>
                                        </label>
                                    </article>
                                @endforeach
                            </div>
                        </section>

                    </div>

                    <div class="hero-editor-savebar">
                        <div class="hero-editor-savebar-text">
                            <strong>Ready to Apply Changes?</strong>
                            <p>Review your configurations. Your changes will be reflected live on the storefront immediately.</p>
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
            // Toast notification dismissal
            const successBox = document.getElementById('hero-editor-success');
            if (successBox) {
                successBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            const closeToastButton = document.getElementById('hero-editor-toast-close');
            if (closeToastButton && successBox) {
                closeToastButton.addEventListener('click', function () {
                    successBox.style.opacity = '0';
                    setTimeout(() => successBox.remove(), 300);
                });
            }
        });
    </script>
@endsection
