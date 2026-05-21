@extends('admin.layout')

@section('title', 'Edit Product')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="dashboard-card">
                <div class="page-head">
                    <div>
                        <div class="brand">Catalog</div>
                        <h2>Edit Product</h2>
                        <p class="lead" style="margin-top:8px;">Update product details, media, pricing, stock, and storefront visibility.</p>
                    </div>
                    <a href="{{ route('admin.products.index') }}" class="button secondary small">Back</a>
                </div>

                @if (session('status'))
                    <div class="message">{{ session('status') }}</div>
                @endif

                @php
                    $images = old('image_urls', $product->images ?? []);
                @endphp

                <section class="panel">
                    <form method="POST" action="{{ route('admin.products.update', $product) }}" class="section-grid" data-auto-seo-form enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="form-grid">
                            <div class="field">
                                <label>Category</label>
                                <select name="category_id">
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) === $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field"><label>Name</label><input name="name" value="{{ old('name', $product->name) }}" data-slug-source data-meta-title-source /></div>
                            <div class="field"><label>Slug</label><input name="slug" value="{{ old('slug', $product->slug) }}" data-slug-target /></div>
                            <div class="field"><label>SKU</label><input name="sku" value="{{ old('sku', $product->sku) }}" /></div>
                            <div class="field"><label>Price</label><input name="price" value="{{ old('price', $product->price) }}" /></div>
                            <div class="field"><label>Sale Price</label><input name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" /></div>
                            <div class="field"><label>Stock</label><input name="stock" value="{{ old('stock', $product->stock) }}" /></div>
                            <div class="field"><label>Video URL</label><input name="video_url" value="{{ old('video_url', $product->video_url) }}" /></div>
                            <div class="field"><label>Meta Title</label><input name="meta_title" value="{{ old('meta_title', $product->meta_title) }}" data-meta-title-target /></div>
                            <div class="field"><label>Meta Description</label><input name="meta_desc" value="{{ old('meta_desc', $product->meta_desc) }}" data-meta-desc-target /></div>
                        </div>

                        <div class="field"><label>Short Description</label><textarea name="short_desc" data-meta-desc-source>{{ old('short_desc', $product->short_desc) }}</textarea></div>
                        <div class="field"><label>Description</label><textarea name="description">{{ old('description', $product->description) }}</textarea></div>

                        <div class="field">
                            <label>Product Photos</label>
                            <div class="media-slot-grid">
                                @for ($slot = 0; $slot < 8; $slot++)
                                    <div class="media-slot-card">
                                        <strong>Photo {{ $slot + 1 }}</strong>
                                        @if (!empty($images[$slot]))
                                            <img src="{{ $images[$slot] }}" alt="Product image {{ $slot + 1 }}" class="admin-upload-preview">
                                        @endif
                                        <input name="image_urls[]" value="{{ $images[$slot] ?? '' }}" placeholder="Image URL" />
                                        <input type="file" name="image_uploads[{{ $slot }}]" accept="image/*" />
                                    </div>
                                @endfor
                            </div>
                            <p class="muted" style="margin-top:10px;">Each slot supports direct upload or URL. Upload replaces the URL for that slot.</p>
                        </div>

                        <div class="button-row">
                            <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))> <span>Active</span></label>
                            <label class="checkbox-row"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))> <span>Featured</span></label>
                            <button class="button small" type="submit">Save Product</button>
                        </div>
                    </form>
                </section>
            </div>
        </main>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const slugify = (value) =>
                value
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');

            const limitText = (value, max) => value.trim().replace(/\s+/g, ' ').slice(0, max);

            document.querySelectorAll('[data-auto-seo-form]').forEach((form) => {
                const slugSource = form.querySelector('[data-slug-source]');
                const slugTarget = form.querySelector('[data-slug-target]');
                const metaTitleSource = form.querySelector('[data-meta-title-source]');
                const metaTitleTarget = form.querySelector('[data-meta-title-target]');
                const metaDescSource = form.querySelector('[data-meta-desc-source]');
                const metaDescTarget = form.querySelector('[data-meta-desc-target]');

                if (slugSource && slugTarget) {
                    const initialAutoSlug = slugify(slugSource.value || '');
                    let slugManual = Boolean(slugTarget.value) && slugTarget.value !== initialAutoSlug;

                    slugTarget.addEventListener('input', () => {
                        slugManual = true;
                    });

                    slugSource.addEventListener('input', () => {
                        if (!slugManual) {
                            slugTarget.value = slugify(slugSource.value || '');
                        }
                    });
                }

                if (metaTitleSource && metaTitleTarget) {
                    const initialAutoTitle = limitText(metaTitleSource.value || '', 200);
                    let titleManual = Boolean(metaTitleTarget.value) && metaTitleTarget.value !== initialAutoTitle;

                    metaTitleTarget.addEventListener('input', () => {
                        titleManual = true;
                    });

                    metaTitleSource.addEventListener('input', () => {
                        if (!titleManual) {
                            metaTitleTarget.value = limitText(metaTitleSource.value || '', 200);
                        }
                    });
                }

                if (metaDescSource && metaDescTarget) {
                    const initialAutoDescription = limitText(metaDescSource.value || '', 320);
                    let descriptionManual =
                        Boolean(metaDescTarget.value) && metaDescTarget.value !== initialAutoDescription;

                    metaDescTarget.addEventListener('input', () => {
                        descriptionManual = true;
                    });

                    metaDescSource.addEventListener('input', () => {
                        if (!descriptionManual) {
                            metaDescTarget.value = limitText(metaDescSource.value || '', 320);
                        }
                    });
                }
            });
        })();
    </script>
@endpush
