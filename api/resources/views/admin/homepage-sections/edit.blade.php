@extends('admin.layout')

@section('title', 'Edit Homepage Section')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="dashboard-card">
                <div class="page-head">
                    <div>
                        <div class="brand">Homepage Section</div>
                        <h2>{{ $section->label ?: $section->section_key }}</h2>
                        <p class="lead" style="margin-top:8px;">Use the config JSON box for hero slides, side image arrays, or product selection details when needed.</p>
                    </div>
                    <a href="{{ route('admin.homepage-sections.index') }}" class="button secondary small">Back</a>
                </div>

                @if (session('status'))
                    <div class="message">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('admin.homepage-sections.update', $section) }}" class="section-grid">
                    @csrf
                    @method('PUT')

                    <div class="panel">
                        <h3>Core Content</h3>
                        <div class="form-grid">
                            <div class="field"><label>Label</label><input name="label" value="{{ old('label', $section->label) }}" /></div>
                            <div class="field"><label>Sort Order</label><input name="sort_order" value="{{ old('sort_order', $section->sort_order) }}" /></div>
                            <div class="field"><label>Title</label><input name="title" value="{{ old('title', $section->title) }}" /></div>
                            <div class="field"><label>Subtitle</label><input name="subtitle" value="{{ old('subtitle', $section->subtitle) }}" /></div>
                            <div class="field"><label>Heading</label><input name="heading" value="{{ old('heading', $section->heading) }}" /></div>
                            <div class="field"><label>Button Text</label><input name="button_text" value="{{ old('button_text', $section->button_text) }}" /></div>
                            <div class="field"><label>Button URL</label><input name="button_url" value="{{ old('button_url', $section->button_url) }}" /></div>
                            <div class="field"><label>Main Image URL</label><input name="image_url" value="{{ old('image_url', $section->image_url) }}" /></div>
                            <div class="field"><label>Mobile Image URL</label><input name="mobile_image_url" value="{{ old('mobile_image_url', $section->mobile_image_url) }}" /></div>
                            <div class="field"><label>Side Image URL</label><input name="side_image_url" value="{{ old('side_image_url', $section->side_image_url) }}" /></div>
                            <div class="field"><label>Side Secondary Image URL</label><input name="side_secondary_image_url" value="{{ old('side_secondary_image_url', $section->side_secondary_image_url) }}" /></div>
                        </div>
                        <div class="form-grid one" style="margin-top: 16px;">
                            <div class="field">
                                <label>Content</label>
                                <textarea name="content">{{ old('content', $section->content) }}</textarea>
                            </div>
                            <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $section->is_active))> <span>Section active on storefront</span></label>
                        </div>
                    </div>

                    <div class="panel">
                        <h3>Advanced Config JSON</h3>
                        <p>For hero section, use JSON like <code>{"slides":[{"title":"...","image":"..."},{"title":"...","image":"..."}],"promos":[...]}</code>.</p>
                        <div class="field">
                            <label>Config JSON</label>
                            <textarea name="config_json" class="code">{{ old('config_json', json_encode($section->config ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) }}</textarea>
                        </div>
                        <div class="button-row">
                            <button class="button small" type="submit">Save Homepage Section</button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
@endsection
