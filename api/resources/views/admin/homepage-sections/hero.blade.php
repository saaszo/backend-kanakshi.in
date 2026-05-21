@extends('admin.layout')

@section('title', 'Hero Slider Editor')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="dashboard-card">
                <div class="page-head">
                    <div>
                        <div class="brand">Homepage Hero</div>
                        <h2>Hero Slider Editor</h2>
                        <p class="lead" style="margin-top:8px;">Manage hero slides, side banners, text visibility, slider speed, dots, links, and uploads from one dedicated screen.</p>
                    </div>
                    <a href="{{ route('admin.homepage-sections.index') }}" class="button secondary small">Back To Sections</a>
                </div>

                @if (session('status'))
                    <div class="message">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('admin.homepage-sections.hero.update') }}" class="section-grid" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <section class="panel">
                        <h3>Hero Section Controls</h3>
                        <div class="form-grid">
                            <div class="field">
                                <label for="label">Admin Label</label>
                                <input id="label" name="label" value="{{ old('label', $section->label) }}" />
                            </div>
                            <div class="field">
                                <label for="title">Section Title</label>
                                <input id="title" name="title" value="{{ old('title', $section->title) }}" />
                            </div>
                            <div class="field">
                                <label for="subtitle">Section Subtitle</label>
                                <input id="subtitle" name="subtitle" value="{{ old('subtitle', $section->subtitle) }}" />
                            </div>
                            <div class="field">
                                <label for="heading">Section Heading</label>
                                <input id="heading" name="heading" value="{{ old('heading', $section->heading) }}" />
                            </div>
                            <div class="field">
                                <label for="sort_order">Sort Order</label>
                                <input id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', $section->sort_order) }}" />
                            </div>
                            <div class="field">
                                <label for="autoplay_ms">Slider Speed (ms)</label>
                                <input id="autoplay_ms" name="autoplay_ms" type="number" min="1000" max="15000" step="100" value="{{ old('autoplay_ms', $sliderSettings['autoplay_ms']) }}" />
                                <small style="display:block;margin-top:8px;color:rgba(25,25,25,.58);">Recommended: 3000 to 4500 ms</small>
                            </div>
                            <div class="field">
                                <label for="nav_gap">Navigation Button Gap (px)</label>
                                <input id="nav_gap" name="nav_gap" type="number" min="0" max="240" value="{{ old('nav_gap', $sliderSettings['nav_gap']) }}" />
                                <small style="display:block;margin-top:8px;color:rgba(25,25,25,.58);">Controls space between left arrow, title, and right arrow.</small>
                            </div>
                        </div>

                        <div class="button-row" style="margin-top:16px;">
                            <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $section->is_active))> <span>Hero section active</span></label>
                            <label class="checkbox-row"><input type="checkbox" name="show_text" value="1" @checked(old('show_text', $sliderSettings['show_text']))> <span>Show slide title text</span></label>
                            <label class="checkbox-row"><input type="checkbox" name="show_dots" value="1" @checked(old('show_dots', $sliderSettings['show_dots']))> <span>Enable dots / points</span></label>
                            <label class="checkbox-row"><input type="checkbox" name="show_arrows" value="1" @checked(old('show_arrows', $sliderSettings['show_arrows']))> <span>Enable navigation arrows</span></label>
                        </div>
                    </section>

                    <section class="panel">
                        <h3>Hero Slider Images</h3>
                        <p>Upload up to 5 main hero slides. Recommended size: <strong>1600 x 1100 px</strong> or larger, same ratio for all images.</p>
                        <div class="section-grid">
                            @foreach ($slides as $index => $slide)
                                <div class="panel" style="background:#fff;">
                                    <h4 style="margin:0 0 16px;">Slide {{ $index + 1 }}</h4>
                                    <div class="form-grid one">
                                        <label class="checkbox-row"><input type="checkbox" name="slides[{{ $index }}][is_active]" value="1" @checked(old("slides.$index.is_active", $slide['is_active']))> <span>Enable this slide</span></label>
                                        <div class="field">
                                            <label>Slide Title</label>
                                            <input name="slides[{{ $index }}][title]" value="{{ old("slides.$index.title", $slide['title']) }}" placeholder="Wooden Collection" />
                                        </div>
                                        <div class="field">
                                            <label>Image Alt Text</label>
                                            <input name="slides[{{ $index }}][alt]" value="{{ old("slides.$index.alt", $slide['alt']) }}" placeholder="Hero slide alt text" />
                                        </div>
                                        <div class="field">
                                            <label>Button / Click Link</label>
                                            <input name="slides[{{ $index }}][href]" value="{{ old("slides.$index.href", $slide['href']) }}" placeholder="/shop?category=wooden-collection" />
                                        </div>
                                        <div class="field">
                                            <label>Image URL</label>
                                            <input name="slide_urls[{{ $index }}]" value="{{ old("slide_urls.$index", $slide['image']) }}" placeholder="/storage/homepage/hero-slide.jpg" />
                                            @if (!empty($slide['image']))
                                                <img src="{{ $slide['image'] }}" alt="Slide preview" class="admin-upload-preview" style="margin-top:10px;" />
                                            @endif
                                            <input type="file" name="slide_files[{{ $index }}]" accept="image/*" style="margin-top:10px;" />
                                            <small style="display:block;margin-top:8px;color:rgba(25,25,25,.58);">Recommended: 1600 x 1100 px. JPG/PNG/WebP under 5MB.</small>
                                        </div>
                                        <label class="checkbox-row"><input type="checkbox" name="clear_slide_image[{{ $index }}]" value="1"> <span>Remove current image</span></label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="panel">
                        <h3>Side Banner Images</h3>
                        <p>Manage the 2 stacked images shown on the right side of the hero. Recommended size: <strong>900 x 620 px</strong> for each banner.</p>
                        <div class="section-grid">
                            @foreach ($promos as $index => $promo)
                                <div class="panel" style="background:#fff;">
                                    <h4 style="margin:0 0 16px;">Side Banner {{ $index + 1 }}</h4>
                                    <div class="form-grid one">
                                        <label class="checkbox-row"><input type="checkbox" name="promos[{{ $index }}][is_active]" value="1" @checked(old("promos.$index.is_active", $promo['is_active']))> <span>Enable this side image</span></label>
                                        <label class="checkbox-row"><input type="checkbox" name="promos[{{ $index }}][show_text]" value="1" @checked(old("promos.$index.show_text", $promo['show_text']))> <span>Show text on image</span></label>
                                        <div class="field">
                                            <label>Title</label>
                                            <input name="promos[{{ $index }}][title]" value="{{ old("promos.$index.title", $promo['title']) }}" placeholder="Wall Decor Collection" />
                                        </div>
                                        <div class="field">
                                            <label>Subtitle</label>
                                            <input name="promos[{{ $index }}][subtitle]" value="{{ old("promos.$index.subtitle", $promo['subtitle']) }}" placeholder="Designed for thoughtful spaces" />
                                        </div>
                                        <div class="field">
                                            <label>Click Link</label>
                                            <input name="promos[{{ $index }}][href]" value="{{ old("promos.$index.href", $promo['href']) }}" placeholder="/shop?category=wall-decor" />
                                        </div>
                                        <div class="field">
                                            <label>Image URL</label>
                                            <input name="promo_urls[{{ $index }}]" value="{{ old("promo_urls.$index", $promo['image']) }}" placeholder="/storage/homepage/hero-promo.jpg" />
                                            @if (!empty($promo['image']))
                                                <img src="{{ $promo['image'] }}" alt="Promo preview" class="admin-upload-preview" style="margin-top:10px;" />
                                            @endif
                                            <input type="file" name="promo_files[{{ $index }}]" accept="image/*" style="margin-top:10px;" />
                                            <small style="display:block;margin-top:8px;color:rgba(25,25,25,.58);">Recommended: 900 x 620 px. Keep both banners same size for best layout.</small>
                                        </div>
                                        <label class="checkbox-row"><input type="checkbox" name="clear_promo_image[{{ $index }}]" value="1"> <span>Remove current image</span></label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <div class="button-row">
                        <button class="button small" type="submit">Save Hero Settings</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
@endsection
