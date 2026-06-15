@extends('admin.layout')

@section('title', 'Full Homepage Editor')

@php
    $sectionToggles = [
        'collections_section_is_active' => ['label' => 'Collections', 'path' => 'collections.is_active'],
        'occasions_section_is_active' => ['label' => 'Festival Categories', 'path' => 'occasions.is_active'],
        'editorial_picks_section_is_active' => ['label' => 'Editorial Picks', 'path' => 'editorial_picks.is_active'],
        'about_brand_is_active' => ['label' => 'About Brand', 'path' => 'about_brand.is_active'],
        'founders_is_active' => ['label' => 'Founders Story', 'path' => 'founders.is_active'],
        'testimonials_is_active' => ['label' => 'Testimonials', 'path' => 'testimonials.is_active'],
        'newsletter_is_active' => ['label' => 'Newsletter Banner', 'path' => 'newsletter.is_active'],
        'instagram_is_active' => ['label' => 'Instagram Grid', 'path' => 'instagram.is_active'],
        'stats_is_active' => ['label' => 'Stats Strip', 'path' => 'stats.is_active'],
        'festive_edits_is_active' => ['label' => 'Festive Edits', 'path' => 'festive_edits.is_active'],
    ];
@endphp

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <div class="admin-banner">
                    <div>
                        <div class="brand">Homepage CMS</div>
                        <h2>Full Homepage Editor</h2>
                        <p class="lead" style="margin-top:8px;">Control the homepage copy, cards, social grid, newsletter block, stats, and festive story sections from one place.</p>
                    </div>
                    <div class="button-row">
                        <a href="{{ route('admin.homepage-sections.hero.edit') }}" class="button secondary small">Hero Slider</a>
                        <a href="{{ route('admin.homepage-products.index') }}" class="button secondary small">Homepage Products</a>
                        <a href="{{ route('admin.homepage-sections.index') }}" class="button secondary small">All Sections</a>
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

                <form method="POST" action="{{ route('admin.homepage-sections.full.update') }}" class="admin-fields">
                    @csrf
                    @method('PUT')

                    <div class="admin-section">
                        <h3>Section Settings</h3>
                        <div class="form-grid">
                            <div class="field"><label>Section Label</label><input name="label" value="{{ old('label', $section->label) }}" /></div>
                            <div class="field"><label>Title</label><input name="title" value="{{ old('title', $section->title) }}" /></div>
                            <div class="field"><label>Sort Order</label><input type="number" min="1" name="sort_order" value="{{ old('sort_order', $section->sort_order) }}" /></div>
                        </div>
                        <div class="form-grid one" style="margin-top: 16px;">
                            <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $section->is_active))> <span>Full homepage config active</span></label>
                        </div>
                        <div class="form-grid" style="margin-top: 16px;">
                            @foreach ($sectionToggles as $field => $meta)
                                <label class="checkbox-row"><input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, data_get($config, $meta['path'])))> <span>{{ $meta['label'] }}</span></label>
                            @endforeach
                        </div>
                    </div>

                    <div class="admin-section">
                        <h3>Collections Block</h3>
                        <div class="form-grid">
                            <div class="field"><label>Eyebrow</label><input name="collections_eyebrow" value="{{ old('collections_eyebrow', $config['collections']['eyebrow']) }}" /></div>
                            <div class="field"><label>Title</label><input name="collections_title" value="{{ old('collections_title', $config['collections']['title']) }}" /></div>
                            <div class="field"><label>Button Text</label><input name="collections_button_text" value="{{ old('collections_button_text', $config['collections']['button_text']) }}" /></div>
                            <div class="field"><label>Button URL</label><input name="collections_button_url" value="{{ old('collections_button_url', $config['collections']['button_url']) }}" /></div>
                        </div>
                        @foreach ($config['collections']['items'] as $index => $item)
                            <div class="admin-card" style="margin-top: 16px; padding: 16px;">
                                <h4 style="margin: 0 0 12px;">Collection Card {{ $index + 1 }}</h4>
                                <div class="form-grid">
                                    <div class="field"><label>Title</label><input name="collections[{{ $index }}][title]" value="{{ old("collections.$index.title", $item['title']) }}" /></div>
                                    <div class="field"><label>Subtitle</label><input name="collections[{{ $index }}][subtitle]" value="{{ old("collections.$index.subtitle", $item['subtitle']) }}" /></div>
                                    <div class="field"><label>Link</label><input name="collections[{{ $index }}][href]" value="{{ old("collections.$index.href", $item['href']) }}" /></div>
                                    <div class="field"><label>Image URL</label><input name="collections[{{ $index }}][image]" value="{{ old("collections.$index.image", $item['image']) }}" /></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="admin-section">
                        <h3>Festival Categories</h3>
                        <div class="form-grid">
                            <div class="field"><label>Eyebrow</label><input name="occasions_eyebrow" value="{{ old('occasions_eyebrow', $config['occasions']['eyebrow']) }}" /></div>
                            <div class="field"><label>Title</label><input name="occasions_title" value="{{ old('occasions_title', $config['occasions']['title']) }}" /></div>
                        </div>
                        @foreach ($config['occasions']['items'] as $index => $item)
                            <div class="admin-card" style="margin-top: 16px; padding: 16px;">
                                <h4 style="margin: 0 0 12px;">Occasion {{ $index + 1 }}</h4>
                                <div class="form-grid">
                                    <div class="field"><label>Title</label><input name="occasions[{{ $index }}][title]" value="{{ old("occasions.$index.title", $item['title']) }}" /></div>
                                    <div class="field"><label>Link</label><input name="occasions[{{ $index }}][href]" value="{{ old("occasions.$index.href", $item['href']) }}" /></div>
                                    <div class="field"><label>Image URL</label><input name="occasions[{{ $index }}][image]" value="{{ old("occasions.$index.image", $item['image']) }}" /></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="admin-section">
                        <h3>Editorial Picks</h3>
                        @foreach ($config['editorial_picks']['items'] as $index => $item)
                            <div class="admin-card" style="margin-top: 16px; padding: 16px;">
                                <h4 style="margin: 0 0 12px;">Editorial Card {{ $index + 1 }}</h4>
                                <div class="form-grid">
                                    <div class="field"><label>Badge</label><input name="editorial_picks[{{ $index }}][badge]" value="{{ old("editorial_picks.$index.badge", $item['badge']) }}" /></div>
                                    <div class="field"><label>Title</label><input name="editorial_picks[{{ $index }}][title]" value="{{ old("editorial_picks.$index.title", $item['title']) }}" /></div>
                                    <div class="field"><label>Link</label><input name="editorial_picks[{{ $index }}][href]" value="{{ old("editorial_picks.$index.href", $item['href']) }}" /></div>
                                    <div class="field"><label>Image URL</label><input name="editorial_picks[{{ $index }}][image]" value="{{ old("editorial_picks.$index.image", $item['image']) }}" /></div>
                                </div>
                                <div class="field" style="margin-top: 12px;">
                                    <label>Description</label>
                                    <textarea name="editorial_picks[{{ $index }}][description]" rows="3">{{ old("editorial_picks.$index.description", $item['description']) }}</textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="admin-section">
                        <h3>About Brand</h3>
                        <div class="form-grid">
                            <div class="field"><label>Eyebrow</label><input name="about_brand_eyebrow" value="{{ old('about_brand_eyebrow', $config['about_brand']['eyebrow']) }}" /></div>
                            <div class="field"><label>Title</label><input name="about_brand_title" value="{{ old('about_brand_title', $config['about_brand']['title']) }}" /></div>
                            <div class="field"><label>Button Text</label><input name="about_brand_button_text" value="{{ old('about_brand_button_text', $config['about_brand']['button_text']) }}" /></div>
                            <div class="field"><label>Button URL</label><input name="about_brand_button_url" value="{{ old('about_brand_button_url', $config['about_brand']['button_url']) }}" /></div>
                            <div class="field"><label>Image URL</label><input name="about_brand_image" value="{{ old('about_brand_image', $config['about_brand']['image']) }}" /></div>
                        </div>
                        <div class="form-grid one" style="margin-top: 16px;">
                            <div class="field"><label>Paragraph One</label><textarea name="about_brand_paragraph_one" rows="4">{{ old('about_brand_paragraph_one', $config['about_brand']['paragraph_one']) }}</textarea></div>
                            <div class="field"><label>Paragraph Two</label><textarea name="about_brand_paragraph_two" rows="4">{{ old('about_brand_paragraph_two', $config['about_brand']['paragraph_two']) }}</textarea></div>
                        </div>
                    </div>

                    <div class="admin-section">
                        <h3>Founders Story</h3>
                        <div class="form-grid">
                            <div class="field"><label>Eyebrow</label><input name="founders_eyebrow" value="{{ old('founders_eyebrow', $config['founders']['eyebrow']) }}" /></div>
                            <div class="field"><label>Title</label><input name="founders_title" value="{{ old('founders_title', $config['founders']['title']) }}" /></div>
                            <div class="field"><label>Button Text</label><input name="founders_button_text" value="{{ old('founders_button_text', $config['founders']['button_text']) }}" /></div>
                            <div class="field"><label>Button URL</label><input name="founders_button_url" value="{{ old('founders_button_url', $config['founders']['button_url']) }}" /></div>
                            <div class="field"><label>Main Image URL</label><input name="founders_main_image" value="{{ old('founders_main_image', $config['founders']['main_image']) }}" /></div>
                            <div class="field"><label>Side Image URL</label><input name="founders_side_image" value="{{ old('founders_side_image', $config['founders']['side_image']) }}" /></div>
                        </div>
                        <div class="form-grid one" style="margin-top: 16px;">
                            <div class="field"><label>Story Copy</label><textarea name="founders_content" rows="5">{{ old('founders_content', $config['founders']['content']) }}</textarea></div>
                        </div>
                    </div>

                    <div class="admin-section">
                        <h3>Testimonials</h3>
                        <div class="form-grid">
                            <div class="field"><label>Eyebrow</label><input name="testimonials_eyebrow" value="{{ old('testimonials_eyebrow', $config['testimonials']['eyebrow']) }}" /></div>
                            <div class="field"><label>Title</label><input name="testimonials_title" value="{{ old('testimonials_title', $config['testimonials']['title']) }}" /></div>
                        </div>
                        @foreach ($config['testimonials']['items'] as $index => $item)
                            <div class="admin-card" style="margin-top: 16px; padding: 16px;">
                                <h4 style="margin: 0 0 12px;">Testimonial {{ $index + 1 }}</h4>
                                <div class="form-grid">
                                    <div class="field"><label>Title</label><input name="testimonials[{{ $index }}][title]" value="{{ old("testimonials.$index.title", $item['title']) }}" /></div>
                                    <div class="field"><label>Author</label><input name="testimonials[{{ $index }}][author]" value="{{ old("testimonials.$index.author", $item['author']) }}" /></div>
                                    <div class="field"><label>Stars</label><input name="testimonials[{{ $index }}][stars]" value="{{ old("testimonials.$index.stars", $item['stars']) }}" /></div>
                                </div>
                                <div class="field" style="margin-top: 12px;"><label>Quote</label><textarea name="testimonials[{{ $index }}][quote]" rows="3">{{ old("testimonials.$index.quote", $item['quote']) }}</textarea></div>
                            </div>
                        @endforeach
                    </div>

                    <div class="admin-section">
                        <h3>Newsletter Banner</h3>
                        <div class="form-grid">
                            <div class="field"><label>Eyebrow</label><input name="newsletter_eyebrow" value="{{ old('newsletter_eyebrow', $config['newsletter']['eyebrow']) }}" /></div>
                            <div class="field"><label>Title</label><input name="newsletter_title" value="{{ old('newsletter_title', $config['newsletter']['title']) }}" /></div>
                            <div class="field"><label>Button Text</label><input name="newsletter_button_text" value="{{ old('newsletter_button_text', $config['newsletter']['button_text']) }}" /></div>
                            <div class="field"><label>Email Placeholder</label><input name="newsletter_placeholder" value="{{ old('newsletter_placeholder', $config['newsletter']['placeholder']) }}" /></div>
                        </div>
                        <div class="form-grid one" style="margin-top: 16px;">
                            <div class="field"><label>Description</label><textarea name="newsletter_description" rows="4">{{ old('newsletter_description', $config['newsletter']['description']) }}</textarea></div>
                            <div class="field"><label>Footnote</label><textarea name="newsletter_footnote" rows="3">{{ old('newsletter_footnote', $config['newsletter']['footnote']) }}</textarea></div>
                        </div>
                    </div>

                    <div class="admin-section">
                        <h3>Instagram Grid</h3>
                        <div class="form-grid">
                            <div class="field"><label>Eyebrow</label><input name="instagram_eyebrow" value="{{ old('instagram_eyebrow', $config['instagram']['eyebrow']) }}" /></div>
                            <div class="field"><label>Title</label><input name="instagram_title" value="{{ old('instagram_title', $config['instagram']['title']) }}" /></div>
                            <div class="field"><label>Profile Label</label><input name="instagram_profile_label" value="{{ old('instagram_profile_label', $config['instagram']['profile_label']) }}" /></div>
                            <div class="field"><label>Profile URL</label><input name="instagram_profile_url" value="{{ old('instagram_profile_url', $config['instagram']['profile_url']) }}" /></div>
                        </div>
                        @foreach ($config['instagram']['tiles'] as $index => $item)
                            <div class="admin-card" style="margin-top: 16px; padding: 16px;">
                                <h4 style="margin: 0 0 12px;">Instagram Tile {{ $index + 1 }}</h4>
                                <div class="form-grid">
                                    <div class="field"><label>Image URL</label><input name="instagram[tiles][{{ $index }}][image]" value="{{ old("instagram.tiles.$index.image", $item['image']) }}" /></div>
                                    <div class="field"><label>Alt Text</label><input name="instagram[tiles][{{ $index }}][alt]" value="{{ old("instagram.tiles.$index.alt", $item['alt']) }}" /></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="admin-section">
                        <h3>Stats Strip</h3>
                        <div class="form-grid">
                            <div class="field"><label>Eyebrow</label><input name="stats_eyebrow" value="{{ old('stats_eyebrow', $config['stats']['eyebrow']) }}" /></div>
                            <div class="field"><label>Title</label><input name="stats_title" value="{{ old('stats_title', $config['stats']['title']) }}" /></div>
                        </div>
                        @foreach ($config['stats']['items'] as $index => $item)
                            <div class="admin-card" style="margin-top: 16px; padding: 16px;">
                                <h4 style="margin: 0 0 12px;">Stat {{ $index + 1 }}</h4>
                                <div class="form-grid">
                                    <div class="field"><label>Value</label><input name="stats[{{ $index }}][value]" value="{{ old("stats.$index.value", $item['value']) }}" /></div>
                                    <div class="field"><label>Label</label><input name="stats[{{ $index }}][label]" value="{{ old("stats.$index.label", $item['label']) }}" /></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="admin-section">
                        <h3>Festive Edits</h3>
                        <div class="form-grid">
                            <div class="field"><label>Eyebrow</label><input name="festive_edits_eyebrow" value="{{ old('festive_edits_eyebrow', $config['festive_edits']['eyebrow']) }}" /></div>
                            <div class="field"><label>Title</label><input name="festive_edits_title" value="{{ old('festive_edits_title', $config['festive_edits']['title']) }}" /></div>
                            <div class="field"><label>Button Text</label><input name="festive_edits_button_text" value="{{ old('festive_edits_button_text', $config['festive_edits']['button_text']) }}" /></div>
                            <div class="field"><label>Button URL</label><input name="festive_edits_button_url" value="{{ old('festive_edits_button_url', $config['festive_edits']['button_url']) }}" /></div>
                        </div>
                        @foreach ($config['festive_edits']['items'] as $index => $item)
                            <div class="admin-card" style="margin-top: 16px; padding: 16px;">
                                <h4 style="margin: 0 0 12px;">Festive Card {{ $index + 1 }}</h4>
                                <div class="form-grid">
                                    <div class="field"><label>Badge</label><input name="festive_edits[{{ $index }}][badge]" value="{{ old("festive_edits.$index.badge", $item['badge']) }}" /></div>
                                    <div class="field"><label>Title</label><input name="festive_edits[{{ $index }}][title]" value="{{ old("festive_edits.$index.title", $item['title']) }}" /></div>
                                    <div class="field"><label>Link</label><input name="festive_edits[{{ $index }}][href]" value="{{ old("festive_edits.$index.href", $item['href']) }}" /></div>
                                    <div class="field"><label>Image URL</label><input name="festive_edits[{{ $index }}][image]" value="{{ old("festive_edits.$index.image", $item['image']) }}" /></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="button-row">
                        <button class="button small" type="submit">Save Full Homepage</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
@endsection
