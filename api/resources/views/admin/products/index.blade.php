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
                        <form method="POST" action="{{ route('admin.products.store') }}" class="section-grid">
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
                                <div class="field"><label>Name</label><input name="name" /></div>
                                <div class="field"><label>Slug</label><input name="slug" /></div>
                                <div class="field"><label>SKU</label><input name="sku" /></div>
                                <div class="field"><label>Price</label><input name="price" /></div>
                                <div class="field"><label>Sale Price</label><input name="sale_price" /></div>
                                <div class="field"><label>Stock</label><input name="stock" value="0" /></div>
                                <div class="field"><label>Video URL</label><input name="video_url" /></div>
                                <div class="field"><label>Meta Title</label><input name="meta_title" /></div>
                                <div class="field"><label>Meta Description</label><input name="meta_desc" /></div>
                            </div>
                            <div class="field"><label>Short Description</label><textarea name="short_desc"></textarea></div>
                            <div class="field"><label>Description</label><textarea name="description"></textarea></div>
                            <div class="field"><label>Image URLs (comma or new line separated)</label><textarea name="images_input" class="code"></textarea></div>
                            <div class="button-row">
                                <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" checked> <span>Active</span></label>
                                <label class="checkbox-row"><input type="checkbox" name="is_featured" value="1"> <span>Featured</span></label>
                                <button class="button small" type="submit">Create Product</button>
                            </div>
                        </form>
                    </section>

                    <section class="panel">
                        <h3>Existing Products</h3>
                        <div class="section-grid">
                            @foreach ($products as $product)
                                <form method="POST" action="{{ route('admin.products.update', $product) }}" class="panel" style="padding:18px;">
                                    @csrf
                                    @method('PUT')
                                    <div class="button-row" style="justify-content:space-between;">
                                        <strong>{{ $product->name }}</strong>
                                        <span class="pill">{{ $product->category?->name ?? 'Uncategorised' }}</span>
                                    </div>
                                    <div class="form-grid" style="margin-top:16px;">
                                        <div class="field">
                                            <label>Category</label>
                                            <select name="category_id">
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}" @selected($product->category_id === $category->id)>{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="field"><label>Name</label><input name="name" value="{{ $product->name }}" /></div>
                                        <div class="field"><label>Slug</label><input name="slug" value="{{ $product->slug }}" /></div>
                                        <div class="field"><label>SKU</label><input name="sku" value="{{ $product->sku }}" /></div>
                                        <div class="field"><label>Price</label><input name="price" value="{{ $product->price }}" /></div>
                                        <div class="field"><label>Sale Price</label><input name="sale_price" value="{{ $product->sale_price }}" /></div>
                                        <div class="field"><label>Stock</label><input name="stock" value="{{ $product->stock }}" /></div>
                                        <div class="field"><label>Video URL</label><input name="video_url" value="{{ $product->video_url }}" /></div>
                                        <div class="field"><label>Meta Title</label><input name="meta_title" value="{{ $product->meta_title }}" /></div>
                                        <div class="field"><label>Meta Description</label><input name="meta_desc" value="{{ $product->meta_desc }}" /></div>
                                    </div>
                                    <div class="field"><label>Short Description</label><textarea name="short_desc">{{ $product->short_desc }}</textarea></div>
                                    <div class="field"><label>Description</label><textarea name="description">{{ $product->description }}</textarea></div>
                                    <div class="field"><label>Image URLs</label><textarea name="images_input" class="code">{{ is_array($product->images) ? implode("\n", $product->images) : $product->images }}</textarea></div>
                                    <div class="button-row">
                                        <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked($product->is_active)> <span>Active</span></label>
                                        <label class="checkbox-row"><input type="checkbox" name="is_featured" value="1" @checked($product->is_featured)> <span>Featured</span></label>
                                        <button class="button small" type="submit">Save</button>
                                    </div>
                                </form>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Remove this product?')" style="margin-top:10px;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="button danger small" type="submit">Delete</button>
                                </form>
                            @endforeach
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
@endsection
