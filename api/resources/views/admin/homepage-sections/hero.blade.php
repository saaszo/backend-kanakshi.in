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
            gap: 1.25rem;
        }

        .hero-editor-banner,
        .hero-editor-errors,
        .hero-editor-savebar,
        .hero-editor-section,
        .hero-editor-overview,
        .hero-editor-toast {
            border: 1px solid rgba(25, 25, 25, 0.08);
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 14px 34px rgba(16, 24, 40, 0.04);
        }

        .hero-editor-banner,
        .hero-editor-errors,
        .hero-editor-overview,
        .hero-editor-section,
        .hero-editor-savebar,
        .hero-editor-toast {
            padding: 1.1rem 1.2rem;
        }

        .hero-editor-toast {
            position: sticky;
            top: 1rem;
            z-index: 8;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            border-color: rgba(22, 163, 74, 0.16);
            background: #f3fff7;
        }

        .hero-editor-banner {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            background: linear-gradient(135deg, #fffdf9 0%, #f7f2ea 100%);
        }

        .hero-editor-banner h2,
        .hero-editor-section h3,
        .hero-editor-card h4 {
            margin: 0;
        }

        .hero-editor-banner p,
        .hero-editor-section > p,
        .hero-editor-overview p,
        .hero-editor-savebar p {
            margin: 0.35rem 0 0;
            color: rgba(25, 25, 25, 0.68);
            line-height: 1.55;
        }

        .hero-editor-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.9rem;
            margin-top: 1rem;
        }

        .hero-editor-stat {
            border-radius: 16px;
            padding: 0.9rem 1rem;
            background: rgba(247, 242, 234, 0.72);
            border: 1px solid rgba(25, 25, 25, 0.06);
        }

        .hero-editor-stat strong {
            display: block;
            font-size: 1.15rem;
            margin-top: 0.2rem;
        }

        .hero-editor-stat span {
            color: rgba(25, 25, 25, 0.66);
            font-size: 0.88rem;
        }

        .hero-editor-errors {
            border-color: rgba(211, 47, 47, 0.18);
            background: #fff7f7;
        }

        .hero-editor-errors ul {
            margin: 0.75rem 0 0;
            padding-left: 1rem;
        }

        .hero-editor-success {
            border-color: rgba(22, 163, 74, 0.16);
            background: #f3fff7;
        }

        .hero-editor-form {
            display: grid;
            gap: 1.2rem;
        }

        .hero-editor-fields {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.95rem;
            margin-top: 1rem;
        }

        .hero-editor-field {
            display: grid;
            gap: 0.45rem;
        }

        .hero-editor-field label {
            font-size: 0.88rem;
            font-weight: 700;
            color: rgba(25, 25, 25, 0.82);
        }

        .hero-editor-field input[type="text"],
        .hero-editor-field input[type="number"],
        .hero-editor-field input:not([type]),
        .hero-editor-field textarea {
            width: 100%;
            min-height: 46px;
            padding: 0.78rem 0.9rem;
            border-radius: 14px;
            border: 1px solid rgba(25, 25, 25, 0.11);
            background: #fff;
        }

        .hero-editor-help {
            color: rgba(25, 25, 25, 0.58);
            font-size: 0.8rem;
            line-height: 1.45;
        }

        .hero-editor-toggle-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.9rem 1rem;
            margin-top: 1rem;
        }

        .hero-editor-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-height: 44px;
            padding: 0.7rem 0.95rem;
            border-radius: 999px;
            border: 1px solid rgba(25, 25, 25, 0.08);
            background: #fcfaf6;
            font-size: 0.88rem;
            font-weight: 600;
        }

        .hero-editor-cards {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .hero-editor-card {
            display: grid;
            gap: 0.95rem;
            padding: 1rem;
            border-radius: 18px;
            border: 1px solid rgba(25, 25, 25, 0.08);
            background: #fffdfa;
        }

        .hero-editor-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .hero-editor-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 28px;
            padding: 0.2rem 0.7rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            background: rgba(25, 25, 25, 0.07);
            color: rgba(25, 25, 25, 0.68);
        }

        .hero-editor-upload {
            display: grid;
            gap: 0.75rem;
            padding: 0.95rem;
            border-radius: 16px;
            border: 1px dashed rgba(25, 25, 25, 0.16);
            background: #fff;
        }

        .hero-editor-upload strong {
            font-size: 0.92rem;
        }

        .hero-editor-upload input[type="file"] {
            width: 100%;
        }

        .hero-editor-file-chip {
            display: none;
            align-items: center;
            gap: 0.45rem;
            padding: 0.55rem 0.7rem;
            border-radius: 12px;
            background: #f6f8fb;
            color: rgba(25, 25, 25, 0.74);
            font-size: 0.82rem;
            font-weight: 600;
        }

        .hero-editor-file-chip.is-visible {
            display: inline-flex;
        }

        .hero-editor-spec-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.65rem;
        }

        .hero-editor-spec {
            padding: 0.7rem 0.8rem;
            border-radius: 12px;
            background: #faf7f2;
            border: 1px solid rgba(25, 25, 25, 0.06);
        }

        .hero-editor-spec strong,
        .hero-editor-spec span {
            display: block;
        }

        .hero-editor-spec span {
            margin-top: 0.22rem;
            color: rgba(25, 25, 25, 0.62);
            font-size: 0.8rem;
        }

        .hero-editor-preview-wrap {
            display: grid;
            grid-template-columns: 124px minmax(0, 1fr);
            gap: 0.9rem;
            align-items: start;
        }

        .hero-editor-preview {
            width: 124px;
            height: 96px;
            object-fit: cover;
            border-radius: 14px;
            border: 1px solid rgba(25, 25, 25, 0.08);
            background: #f5f5f5;
        }

        .hero-editor-empty-preview {
            display: grid;
            place-items: center;
            width: 124px;
            height: 96px;
            border-radius: 14px;
            border: 1px dashed rgba(25, 25, 25, 0.14);
            background: #faf7f2;
            color: rgba(25, 25, 25, 0.48);
            font-size: 0.8rem;
            text-align: center;
            padding: 0.7rem;
        }

        .hero-editor-inline-error {
            color: #c62828;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .hero-editor-savebar {
            position: sticky;
            bottom: 1rem;
            z-index: 5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
        }

        .hero-editor-savebar .button-row {
            margin: 0;
        }

        @media (max-width: 1100px) {
            .hero-editor-grid,
            .hero-editor-fields,
            .hero-editor-cards,
            .hero-editor-spec-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            .hero-editor-banner,
            .hero-editor-card-head,
            .hero-editor-savebar,
            .hero-editor-preview-wrap {
                grid-template-columns: 1fr;
                display: grid;
            }

            .hero-editor-preview,
            .hero-editor-empty-preview {
                width: 100%;
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
                        <div class="brand">Homepage Hero</div>
                        <h2>Hero Slider Editor</h2>
                        <p>Upload slider images, update links, manage side banners, and control slider settings from one clean page. Each card below shows the current image and the new file you select before saving.</p>
                    </div>
                    <a href="{{ route('admin.homepage-sections.index') }}" class="button secondary small">Back To Sections</a>
                </div>

                @if ($errors->any())
                    <div class="hero-editor-errors">
                        <strong>Please fix the highlighted fields.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status'))
                    <div class="hero-editor-toast hero-editor-success" id="hero-editor-success">
                        <div>
                            <strong>Saved successfully</strong>
                            <p>{{ session('status') }}</p>
                        </div>
                        <button type="button" class="button secondary small" id="hero-editor-toast-close">Close</button>
                    </div>
                @endif

                <div class="hero-editor-overview">
                    <h3 style="margin:0;">Quick overview</h3>
                    <p>This helps you confirm how many slider items are active before you save.</p>
                    <div class="hero-editor-grid">
                        <div class="hero-editor-stat">
                            <span>Main slides active</span>
                            <strong>{{ $heroEnabledCount }} / {{ count($slides) }}</strong>
                        </div>
                        <div class="hero-editor-stat">
                            <span>Side banners active</span>
                            <strong>{{ $promoEnabledCount }} / {{ count($promos) }}</strong>
                        </div>
                        <div class="hero-editor-stat">
                            <span>Autoplay speed</span>
                            <strong>{{ old('autoplay_ms', $sliderSettings['autoplay_ms']) }} ms</strong>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.homepage-sections.hero.update') }}" class="hero-editor-form" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <section class="hero-editor-section">
                        <h3>Hero settings</h3>
                        <p>These controls affect the full hero area and slider behavior.</p>

                        <div class="hero-editor-fields">
                            <div class="hero-editor-field">
                                <label for="label">Admin label</label>
                                <input id="label" name="label" value="{{ old('label', $section->label) }}" />
                                @error('label') <div class="hero-editor-inline-error">{{ $message }}</div> @enderror
                            </div>
                            <div class="hero-editor-field">
                                <label for="title">Section title</label>
                                <input id="title" name="title" value="{{ old('title', $section->title) }}" />
                                @error('title') <div class="hero-editor-inline-error">{{ $message }}</div> @enderror
                            </div>
                            <div class="hero-editor-field">
                                <label for="subtitle">Section subtitle</label>
                                <input id="subtitle" name="subtitle" value="{{ old('subtitle', $section->subtitle) }}" />
                                @error('subtitle') <div class="hero-editor-inline-error">{{ $message }}</div> @enderror
                            </div>
                            <div class="hero-editor-field">
                                <label for="heading">Section heading</label>
                                <input id="heading" name="heading" value="{{ old('heading', $section->heading) }}" />
                                @error('heading') <div class="hero-editor-inline-error">{{ $message }}</div> @enderror
                            </div>
                            <div class="hero-editor-field">
                                <label for="sort_order">Sort order</label>
                                <input id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', $section->sort_order) }}" />
                                @error('sort_order') <div class="hero-editor-inline-error">{{ $message }}</div> @enderror
                            </div>
                            <div class="hero-editor-field">
                                <label for="autoplay_ms">Slider speed (ms)</label>
                                <input id="autoplay_ms" name="autoplay_ms" type="number" min="1000" max="15000" step="100" value="{{ old('autoplay_ms', $sliderSettings['autoplay_ms']) }}" />
                                <div class="hero-editor-help">Recommended range: 3000 to 4500 ms.</div>
                                @error('autoplay_ms') <div class="hero-editor-inline-error">{{ $message }}</div> @enderror
                            </div>
                            <div class="hero-editor-field">
                                <label for="nav_gap">Navigation gap (px)</label>
                                <input id="nav_gap" name="nav_gap" type="number" min="0" max="240" value="{{ old('nav_gap', $sliderSettings['nav_gap']) }}" />
                                <div class="hero-editor-help">Space between left arrow, center label, and right arrow.</div>
                                @error('nav_gap') <div class="hero-editor-inline-error">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="hero-editor-toggle-row">
                            <label class="hero-editor-toggle"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $section->is_active))> <span>Hero section active</span></label>
                            <label class="hero-editor-toggle"><input type="checkbox" name="show_text" value="1" @checked(old('show_text', $sliderSettings['show_text']))> <span>Show slide title text</span></label>
                            <label class="hero-editor-toggle"><input type="checkbox" name="show_dots" value="1" @checked(old('show_dots', $sliderSettings['show_dots']))> <span>Enable slider dots</span></label>
                            <label class="hero-editor-toggle"><input type="checkbox" name="show_arrows" value="1" @checked(old('show_arrows', $sliderSettings['show_arrows']))> <span>Enable arrows</span></label>
                        </div>
                    </section>

                    <section class="hero-editor-section">
                        <h3>Main hero slides</h3>
                        <p>Use the upload box for a new image, or paste an existing storage URL if you already uploaded it earlier. Recommended size: <strong>1600 x 1100 px</strong>.</p>

                        <div class="hero-editor-cards">
                            @foreach ($slides as $index => $slide)
                                <article class="hero-editor-card">
                                    <div class="hero-editor-card-head">
                                        <div>
                                            <h4>Slide {{ $index + 1 }}</h4>
                                            <p class="hero-editor-help">Large left-side slider image.</p>
                                        </div>
                                        <span class="hero-editor-badge">{{ old("slides.$index.is_active", $slide['is_active']) ? 'Active' : 'Hidden' }}</span>
                                    </div>

                                    <label class="hero-editor-toggle"><input type="checkbox" name="slides[{{ $index }}][is_active]" value="1" @checked(old("slides.$index.is_active", $slide['is_active']))> <span>Show this slide on website</span></label>

                                    <div class="hero-editor-preview-wrap">
                                        @if (!empty($slide['image']))
                                            <img src="{{ $slide['image'] }}" alt="Slide preview" class="hero-editor-preview" />
                                        @else
                                            <div class="hero-editor-empty-preview">No image uploaded yet</div>
                                        @endif

                                        <div class="hero-editor-upload">
                                            <strong>Upload new slide image</strong>
                                            <div class="hero-editor-help">Choose JPG, PNG, or WebP. The new image will replace the current one after save.</div>
                                            <div class="hero-editor-spec-grid">
                                                <div class="hero-editor-spec">
                                                    <strong>Best size</strong>
                                                    <span>1600 x 1100 px</span>
                                                </div>
                                                <div class="hero-editor-spec">
                                                    <strong>Best file size</strong>
                                                    <span>Under 350 KB if possible</span>
                                                </div>
                                            </div>
                                            <input type="file" name="slide_files[{{ $index }}]" accept="image/*" class="js-admin-file-input" data-target="slide-file-name-{{ $index }}">
                                            <div class="hero-editor-file-chip" id="slide-file-name-{{ $index }}">No new file selected</div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="slide_urls[{{ $index }}]" value="{{ old("slide_urls.$index", $slide['image']) }}">

                                    <div class="hero-editor-fields">
                                        <div class="hero-editor-field">
                                            <label>Slide title</label>
                                            <input name="slides[{{ $index }}][title]" value="{{ old("slides.$index.title", $slide['title']) }}" placeholder="Wooden Collection" />
                                        </div>
                                        <div class="hero-editor-field">
                                            <label>Alt text</label>
                                            <input name="slides[{{ $index }}][alt]" value="{{ old("slides.$index.alt", $slide['alt']) }}" placeholder="Hero image alt text" />
                                        </div>
                                        <div class="hero-editor-field" style="grid-column: 1 / -1;">
                                            <label>Click link</label>
                                            <input name="slides[{{ $index }}][href]" value="{{ old("slides.$index.href", $slide['href']) }}" placeholder="/shop?category=wooden-collection" />
                                        </div>
                                    </div>

                                    <label class="hero-editor-toggle"><input type="checkbox" name="clear_slide_image[{{ $index }}]" value="1"> <span>Remove current image when saving</span></label>
                                </article>
                            @endforeach
                        </div>
                    </section>

                    <section class="hero-editor-section">
                        <h3>Right-side banners</h3>
                        <p>These are the two stacked side images shown next to the main hero slider. Recommended size: <strong>900 x 620 px</strong>.</p>

                        <div class="hero-editor-cards">
                            @foreach ($promos as $index => $promo)
                                <article class="hero-editor-card">
                                    <div class="hero-editor-card-head">
                                        <div>
                                            <h4>Side banner {{ $index + 1 }}</h4>
                                            <p class="hero-editor-help">Smaller supporting hero image.</p>
                                        </div>
                                        <span class="hero-editor-badge">{{ old("promos.$index.is_active", $promo['is_active']) ? 'Active' : 'Hidden' }}</span>
                                    </div>

                                    <label class="hero-editor-toggle"><input type="checkbox" name="promos[{{ $index }}][is_active]" value="1" @checked(old("promos.$index.is_active", $promo['is_active']))> <span>Show this side banner</span></label>
                                    <label class="hero-editor-toggle"><input type="checkbox" name="promos[{{ $index }}][show_text]" value="1" @checked(old("promos.$index.show_text", $promo['show_text']))> <span>Show title text on banner</span></label>

                                    <div class="hero-editor-preview-wrap">
                                        @if (!empty($promo['image']))
                                            <img src="{{ $promo['image'] }}" alt="Promo preview" class="hero-editor-preview" />
                                        @else
                                            <div class="hero-editor-empty-preview">No image uploaded yet</div>
                                        @endif

                                        <div class="hero-editor-upload">
                                            <strong>Upload new banner image</strong>
                                            <div class="hero-editor-help">Choose JPG, PNG, or WebP. Keep both banners same size for a cleaner layout.</div>
                                            <div class="hero-editor-spec-grid">
                                                <div class="hero-editor-spec">
                                                    <strong>Best size</strong>
                                                    <span>900 x 620 px</span>
                                                </div>
                                                <div class="hero-editor-spec">
                                                    <strong>Best file size</strong>
                                                    <span>Under 250 KB if possible</span>
                                                </div>
                                            </div>
                                            <input type="file" name="promo_files[{{ $index }}]" accept="image/*" class="js-admin-file-input" data-target="promo-file-name-{{ $index }}">
                                            <div class="hero-editor-file-chip" id="promo-file-name-{{ $index }}">No new file selected</div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="promo_urls[{{ $index }}]" value="{{ old("promo_urls.$index", $promo['image']) }}">

                                    <div class="hero-editor-fields">
                                        <div class="hero-editor-field">
                                            <label>Title</label>
                                            <input name="promos[{{ $index }}][title]" value="{{ old("promos.$index.title", $promo['title']) }}" placeholder="Wall Decor Collection" />
                                        </div>
                                        <div class="hero-editor-field">
                                            <label>Subtitle</label>
                                            <input name="promos[{{ $index }}][subtitle]" value="{{ old("promos.$index.subtitle", $promo['subtitle']) }}" placeholder="Designed for thoughtful spaces" />
                                        </div>
                                        <div class="hero-editor-field" style="grid-column: 1 / -1;">
                                            <label>Click link</label>
                                            <input name="promos[{{ $index }}][href]" value="{{ old("promos.$index.href", $promo['href']) }}" placeholder="/shop?category=wall-decor" />
                                        </div>
                                    </div>

                                    <label class="hero-editor-toggle"><input type="checkbox" name="clear_promo_image[{{ $index }}]" value="1"> <span>Remove current banner image when saving</span></label>
                                </article>
                            @endforeach
                        </div>
                    </section>

                    <div class="hero-editor-savebar">
                        <div>
                            <strong>Ready to save?</strong>
                            <p>After clicking save, this page will stay open and show a success message at the top.</p>
                        </div>
                        <div class="button-row">
                            <a href="{{ route('admin.homepage-sections.index') }}" class="button secondary small">Cancel</a>
                            <button class="button small" type="submit">Save Hero Settings</button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.js-admin-file-input').forEach(function (input) {
                input.addEventListener('change', function () {
                    const targetId = input.getAttribute('data-target');
                    const target = targetId ? document.getElementById(targetId) : null;
                    if (!target) {
                        return;
                    }

                    const fileName = input.files && input.files[0] ? input.files[0].name : 'No new file selected';
                    target.textContent = fileName;
                    target.classList.toggle('is-visible', !!(input.files && input.files.length));
                });
            });

            const successBox = document.getElementById('hero-editor-success');
            if (successBox) {
                successBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            const closeToastButton = document.getElementById('hero-editor-toast-close');
            if (closeToastButton && successBox) {
                closeToastButton.addEventListener('click', function () {
                    successBox.remove();
                });
            }
        });
    </script>
@endsection
