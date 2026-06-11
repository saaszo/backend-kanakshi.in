@extends('admin.layout')

@section('title', 'Homepage Products')

@php
    $renderSectionConfig = function ($section) {
        return [
            'source_type' => data_get($section->config, 'source_type', 'featured'),
            'product_count' => data_get($section->config, 'product_count', 8),
            'category_slug' => data_get($section->config, 'category_slug'),
            'product_ids' => data_get($section->config, 'product_ids', []),
        ];
    };
    $bestConfig = $renderSectionConfig($bestSellers);
    $newConfig = $renderSectionConfig($newArrivalsProducts);
@endphp

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <div class="admin-banner">
                    <div>
                        <div class="brand">Homepage CMS</div>
                        <h2>Homepage Product Control</h2>
                        <p class="lead" style="margin-top:8px;">Choose which products show on homepage, how many appear, and whether each rail should use featured, newest, category-based, or manually selected products.</p>
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

                <div class="admin-fields">
                    @foreach ([['section' => $bestSellers, 'config' => $bestConfig], ['section' => $newArrivalsProducts, 'config' => $newConfig]] as $item)
                        @php
                            $section = $item['section'];
                            $config = $item['config'];
                        @endphp
                        <section class="admin-section">
                            <h3>{{ $section->label ?: $section->title }}</h3>
                            <form method="POST" action="{{ route('admin.homepage-products.update', $section->section_key) }}" class="admin-fields">
                                @csrf
                                @method('PUT')
                                <div class="form-grid">
                                    <div class="field"><label>Subtitle</label><input name="subtitle" value="{{ old('subtitle', $section->subtitle) }}" /></div>
                                    <div class="field"><label>Title</label><input name="title" value="{{ old('title', $section->title) }}" /></div>
                                    <div class="field"><label>Button Text</label><input name="button_text" value="{{ old('button_text', $section->button_text) }}" /></div>
                                    <div class="field"><label>Button URL</label><input name="button_url" value="{{ old('button_url', $section->button_url) }}" /></div>
                                    <div class="field">
                                        <label>Source Type</label>
                                        <select name="source_type">
                                            <option value="featured" @selected($config['source_type'] === 'featured')>Featured Products</option>
                                            <option value="newest" @selected($config['source_type'] === 'newest')>Newest Products</option>
                                            <option value="manual" @selected($config['source_type'] === 'manual')>Manual Product Selection</option>
                                            <option value="category" @selected($config['source_type'] === 'category')>Category Based</option>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label>Products To Show</label>
                                        <input type="number" min="1" max="24" name="product_count" value="{{ old('product_count', $config['product_count']) }}" />
                                    </div>
                                    <div class="field">
                                        <label>Category Source</label>
                                        <select name="category_slug">
                                            <option value="">Not selected</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->slug }}" @selected($config['category_slug'] === $category->slug)>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="field">
                                    <label>Manual Product Selection</label>
                                    <select name="product_ids[]" multiple size="10">
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" @selected(in_array($product->id, $config['product_ids'], true))>{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                    <small style="display:block;margin-top:8px;color:rgba(25,25,25,.58);">Use this when source type is set to manual.</small>
                                </div>

                                <div class="button-row">
                                    <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked($section->is_active)> <span>Section active</span></label>
                                    <button class="button small" type="submit">Save Homepage Rail</button>
                                </div>
                            </form>
                        </section>
                    @endforeach
                </div>
            </div>
        </main>
    </div>
@endsection
