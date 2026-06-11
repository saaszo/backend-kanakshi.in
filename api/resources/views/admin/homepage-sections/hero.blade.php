@extends('admin.layout')

@section('title', 'Hero Slider Editor')

@php
    $heroEnabledCount = collect($slides)->where('is_active', true)->count();
    $promoEnabledCount = collect($promos)->where('is_active', true)->count();
@endphp

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell">
                <div class="admin-banner">
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

                <form method="POST" action="{{ route('admin.homepage-sections.hero.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div style="display: grid; gap: 24px;">

                        <section class="admin-section">
                            <div class="admin-section-header">
                                <h3>Hero Settings & Options</h3>
                                <p>Control the core behavior, appearance, and speed of the main slider.</p>
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
                                <p>Manage individual slides. Upload high-quality imagery for the best visual impact. Recommended size: <strong>1600 × 1100 px</strong>.</p>
                            </div>

                            <div class="admin-cards">
                                @foreach ($slides as $index => $slide)
                                    <article class="admin-card">
                                        <div class="admin-card-head">
                                            <div>
                                                <h4>Slide {{ $index + 1 }}</h4>
                                                <p class="admin-help" style="margin: 0;">Primary rotating banner component.</p>
                                            </div>
                                            <span class="admin-badge {{ old("slides.$index.is_active", $slide['is_active']) ? 'active' : '' }}">
                                                {{ old("slides.$index.is_active", $slide['is_active']) ? 'Active' : 'Hidden' }}
                                            </span>
                                        </div>

                                        <label class="admin-toggle" style="margin-bottom: 16px; align-self: flex-start;">
                                            <input type="checkbox" name="slides[{{ $index }}][is_active]" value="1" @checked(old("slides.$index.is_active", $slide['is_active']))> 
                                            <span>Publish Slide</span>
                                        </label>

                                        <div class="admin-preview-wrap">
                                            @if (!empty($slide['image']))
                                                <img src="{{ $slide['image'] }}" alt="Slide preview" class="admin-preview" />
                                            @else
                                                <div class="admin-empty-preview">
                                                    <div>
                                                        <i class="bi bi-image" style="font-size: 24px; color: var(--border-strong); margin-bottom: 8px; display: block;"></i>
                                                        No Image Provided
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="admin-upload">
                                                <strong>Upload Media Asset</strong>
                                                <div class="admin-help" style="margin-bottom: 8px;">Supports JPG, PNG, and WebP formats.</div>
                                                <div class="admin-spec-grid" style="margin-bottom: 12px;">
                                                    <div class="admin-spec">
                                                        <strong>Target Size:</strong> <span>1600 × 1100 px</span>
                                                    </div>
                                                    <div class="admin-spec">
                                                        <strong>Max Size:</strong> <span>350 KB</span>
                                                    </div>
                                                </div>
                                                <input type="file" name="slide_files[{{ $index }}]" accept="image/*" class="js-admin-file-input">
                                            </div>
                                        </div>
                                        
                                        <div class="admin-fields" style="margin-top: 0; display: grid; gap: 16px;">
                                            <div class="admin-field">
                                                <label>Display Title</label>
                                                <input name="slides[{{ $index }}][title]" value="{{ old("slides.$index.title", $slide['title']) }}" placeholder="e.g. Summer Collection" />
                                            </div>
                                            <div class="admin-field">
                                                <label>Image Alt Text (SEO)</label>
                                                <input name="slides[{{ $index }}][alt]" value="{{ old("slides.$index.alt", $slide['alt']) }}" placeholder="Descriptive text for accessibility" />
                                            </div>
                                            <div class="admin-field" style="grid-column: 1 / -1;">
                                                <label>Target URL (Click Destination)</label>
                                                <input name="slides[{{ $index }}][href]" value="{{ old("slides.$index.href", $slide['href']) }}" placeholder="/shop?category=featured" />
                                            </div>
                                        </div>

                                        <label class="admin-toggle" style="margin-top: 16px; align-self: flex-start; background: transparent; border-color: transparent;">
                                            <input type="checkbox" name="clear_slide_image[{{ $index }}]" value="1"> 
                                            <span style="color: var(--danger);">Clear current image on save</span>
                                        </label>
                                    </article>
                                @endforeach
                            </div>
                        </section>

                        <section class="admin-section">
                            <div class="admin-section-header">
                                <h3>Right-Side Promo Banners</h3>
                                <p>Manage the secondary stacked promotional blocks alongside the main slider. Recommended size: <strong>900 × 620 px</strong>.</p>
                            </div>

                            <div class="admin-cards">
                                @foreach ($promos as $index => $promo)
                                    <article class="admin-card">
                                        <div class="admin-card-head">
                                            <div>
                                                <h4>Promo Banner {{ $index + 1 }}</h4>
                                                <p class="admin-help" style="margin: 0;">Supporting promotional graphic.</p>
                                            </div>
                                            <span class="admin-badge {{ old("promos.$index.is_active", $promo['is_active']) ? 'active' : '' }}">
                                                {{ old("promos.$index.is_active", $promo['is_active']) ? 'Active' : 'Hidden' }}
                                            </span>
                                        </div>

                                        <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
                                            <label class="admin-toggle">
                                                <input type="checkbox" name="promos[{{ $index }}][is_active]" value="1" @checked(old("promos.$index.is_active", $promo['is_active']))> 
                                                <span>Publish Banner</span>
                                            </label>
                                            <label class="admin-toggle">
                                                <input type="checkbox" name="promos[{{ $index }}][show_text]" value="1" @checked(old("promos.$index.show_text", $promo['show_text']))> 
                                                <span>Display Overlay Text</span>
                                            </label>
                                        </div>

                                        <div class="admin-preview-wrap">
                                            @if (!empty($promo['image']))
                                                <img src="{{ $promo['image'] }}" alt="Promo preview" class="admin-preview" />
                                            @else
                                                <div class="admin-empty-preview">
                                                    <div>
                                                        <i class="bi bi-image" style="font-size: 24px; color: var(--border-strong); margin-bottom: 8px; display: block;"></i>
                                                        No Image Provided
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="admin-upload">
                                                <strong>Upload Media Asset</strong>
                                                <div class="admin-help" style="margin-bottom: 8px;">Ensure consistent sizing for balanced layout.</div>
                                                <div class="admin-spec-grid" style="margin-bottom: 12px;">
                                                    <div class="admin-spec">
                                                        <strong>Target Size:</strong> <span>900 × 620 px</span>
                                                    </div>
                                                    <div class="admin-spec">
                                                        <strong>Max Size:</strong> <span>250 KB</span>
                                                    </div>
                                                </div>
                                                <input type="file" name="promo_files[{{ $index }}]" accept="image/*" class="js-admin-file-input">
                                            </div>
                                        </div>
                                        
                                        <div class="admin-fields" style="margin-top: 0; display: grid; gap: 16px;">
                                            <div class="admin-field">
                                                <label>Primary Title</label>
                                                <input name="promos[{{ $index }}][title]" value="{{ old("promos.$index.title", $promo['title']) }}" placeholder="e.g. Wall Decor" />
                                            </div>
                                            <div class="admin-field">
                                                <label>Secondary Subtitle</label>
                                                <input name="promos[{{ $index }}][subtitle]" value="{{ old("promos.$index.subtitle", $promo['subtitle']) }}" placeholder="e.g. Up to 40% Off" />
                                            </div>
                                            <div class="admin-field" style="grid-column: 1 / -1;">
                                                <label>Target URL (Click Destination)</label>
                                                <input name="promos[{{ $index }}][href]" value="{{ old("promos.$index.href", $promo['href']) }}" placeholder="/shop?category=decor" />
                                            </div>
                                        </div>

                                        <label class="admin-toggle" style="margin-top: 16px; align-self: flex-start; background: transparent; border-color: transparent;">
                                            <input type="checkbox" name="clear_promo_image[{{ $index }}]" value="1"> 
                                            <span style="color: var(--danger);">Clear current image on save</span>
                                        </label>
                                    </article>
                                @endforeach
                            </div>
                        </section>

                    </div>

                    <div class="admin-savebar">
                        <div class="admin-savebar-text">
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
        });
    </script>
@endsection
