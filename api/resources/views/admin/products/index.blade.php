@extends('admin.layout')

@section('title', 'Products')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="dashboard-card">
                <div class="page-head">
                    <div>
                        <div class="brand">Catalog</div>
                        <h2>Products</h2>
                        <p class="lead" style="margin-top:8px;">Add, update, remove, and feature products for the live storefront.</p>
                    </div>
                </div>
                @if (session('status'))
                    <div class="message">{{ session('status') }}</div>
                @endif
                <div class="section-grid">
                    <section class="panel">
                        <h3>Add Product</h3>
                        <form method="POST" action="{{ route('admin.products.store') }}" class="section-grid" data-auto-seo-form enctype="multipart/form-data">
                            @csrf
                            <div class="form-grid">
                                <div class="field">
                                    <label>Category</label>
                                    <select name="category_id">
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="field"><label>Name</label><input name="name" data-slug-source data-meta-title-source /></div>
                                <div class="field"><label>Slug</label><input name="slug" data-slug-target /></div>
                                <div class="field"><label>SKU</label><input name="sku" /></div>
                                <div class="field"><label>Price</label><input name="price" /></div>
                                <div class="field"><label>Sale Price</label><input name="sale_price" /></div>
                                <div class="field"><label>Stock</label><input name="stock" value="0" /></div>
                                <div class="field"><label>Video URL</label><input name="video_url" /></div>
                                <div class="field"><label>Meta Title</label><input name="meta_title" data-meta-title-target /></div>
                                <div class="field"><label>Meta Description</label><input name="meta_desc" data-meta-desc-target /></div>
                            </div>
                            <div class="field"><label>Short Description</label><textarea name="short_desc" data-meta-desc-source></textarea></div>
                            <div class="field"><label>Description</label><textarea name="description"></textarea></div>
                            <div class="field">
                                <label>Product Photos</label>
                                <div class="media-slot-grid">
                                    @for ($slot = 0; $slot < 8; $slot++)
                                        <div class="media-slot-card">
                                            <strong>Photo {{ $slot + 1 }}</strong>
                                            <input name="image_urls[]" placeholder="Image URL" />
                                            <input type="file" name="image_uploads[{{ $slot }}]" accept="image/*" />
                                        </div>
                                    @endfor
                                </div>
                                <p class="muted" style="margin-top:10px;">You can paste URL, upload image, or use both. Upload takes priority for that slot.</p>
                            </div>
                            <div class="button-row">
                                <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" checked> <span>Active</span></label>
                                <label class="checkbox-row"><input type="checkbox" name="is_featured" value="1"> <span>Featured</span></label>
                                <button class="button small" type="submit">Create Product</button>
                            </div>
                        </form>
                    </section>

                    <section class="panel">
                        <h3>Existing Products</h3>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Sale Price</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($products as $product)
                                        <tr>
                                            <td>
                                                <div class="admin-product-meta">
                                                    <strong>{{ $product->name }}</strong>
                                                    <span>{{ $product->sku ?: ($product->slug ?: 'No SKU / slug yet') }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <select name="category_id" form="product-update-{{ $product->id }}" class="table-input">
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}" @selected($product->category_id === $category->id)>{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input
                                                    name="stock"
                                                    type="number"
                                                    min="0"
                                                    value="{{ $product->stock }}"
                                                    form="product-update-{{ $product->id }}"
                                                    class="table-input"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    name="price"
                                                    type="number"
                                                    min="0"
                                                    step="0.01"
                                                    value="{{ $product->price }}"
                                                    form="product-update-{{ $product->id }}"
                                                    class="table-input"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    name="sale_price"
                                                    type="number"
                                                    min="0"
                                                    step="0.01"
                                                    value="{{ $product->sale_price }}"
                                                    form="product-update-{{ $product->id }}"
                                                    class="table-input"
                                                />
                                            </td>
                                            <td>
                                                <div class="admin-product-flags">
                                                    <label class="checkbox-row compact">
                                                        <input
                                                            type="checkbox"
                                                            name="is_active"
                                                            value="1"
                                                            form="product-update-{{ $product->id }}"
                                                            @checked($product->is_active)
                                                        >
                                                        <span>Active</span>
                                                    </label>
                                                    <label class="checkbox-row compact">
                                                        <input
                                                            type="checkbox"
                                                            name="is_featured"
                                                            value="1"
                                                            form="product-update-{{ $product->id }}"
                                                            @checked($product->is_featured)
                                                        >
                                                        <span>Featured</span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="button-row admin-row-actions">
                                                    <button class="button small" type="submit" form="product-update-{{ $product->id }}">Save</button>
                                                    <a class="button secondary small" href="{{ route('admin.products.edit', $product) }}">Edit</a>
                                                    <button class="button danger small" type="submit" form="product-delete-{{ $product->id }}">Delete</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="muted">No products added yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div style="display:none;">
                            @foreach ($products as $product)
                                <form method="POST" action="{{ route('admin.products.update', $product) }}" id="product-update-{{ $product->id }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="name" value="{{ $product->name }}">
                                    <input type="hidden" name="slug" value="{{ $product->slug }}">
                                    <input type="hidden" name="sku" value="{{ $product->sku }}">
                                    <input type="hidden" name="short_desc" value="{{ $product->short_desc }}">
                                    <input type="hidden" name="description" value="{{ $product->description }}">
                                    <input type="hidden" name="video_url" value="{{ $product->video_url }}">
                                    <input type="hidden" name="meta_title" value="{{ $product->meta_title }}">
                                    <input type="hidden" name="meta_desc" value="{{ $product->meta_desc }}">
                                    <textarea name="images_input">{{ is_array($product->images) ? implode("\n", $product->images) : $product->images }}</textarea>
                                </form>

                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" id="product-delete-{{ $product->id }}" onsubmit="return confirm('Remove this product?')">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endforeach
                        </div>
                    </section>
                </div>
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
