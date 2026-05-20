@extends('admin.layout')

@section('title', 'Categories')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="dashboard-card">
                <div class="page-head">
                    <div>
                        <div class="brand">Catalog</div>
                        <h2>Categories</h2>
                        <p class="lead" style="margin-top:8px;">Create, update, hide, or remove storefront categories from here.</p>
                    </div>
                </div>
                @if (session('status'))
                    <div class="message">{{ session('status') }}</div>
                @endif
                <div class="split-grid">
                    <section class="panel">
                        <h3>Add Category</h3>
                        <form method="POST" action="{{ route('admin.categories.store') }}" class="section-grid">
                            @csrf
                            <div class="form-grid">
                                <div class="field"><label>Name</label><input name="name" /></div>
                                <div class="field"><label>Slug</label><input name="slug" /></div>
                                <div class="field"><label>Image URL</label><input name="image" /></div>
                                <div class="field"><label>Parent</label>
                                    <select name="parent_id">
                                        <option value="">None</option>
                                        @foreach ($parents as $parent)
                                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="field"><label>Sort Order</label><input name="sort_order" value="0" /></div>
                            </div>
                            <div class="field">
                                <label>Description</label>
                                <textarea name="description"></textarea>
                            </div>
                            <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" checked> <span>Active category</span></label>
                            <div class="button-row"><button class="button small" type="submit">Create Category</button></div>
                        </form>
                    </section>

                    <section class="panel">
                        <h3>Existing Categories</h3>
                        <div class="section-grid">
                            @foreach ($categories as $category)
                                <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="panel" style="padding:18px;">
                                    @csrf
                                    @method('PUT')
                                    <div class="form-grid">
                                        <div class="field"><label>Name</label><input name="name" value="{{ $category->name }}" /></div>
                                        <div class="field"><label>Slug</label><input name="slug" value="{{ $category->slug }}" /></div>
                                        <div class="field"><label>Image URL</label><input name="image" value="{{ $category->image }}" /></div>
                                        <div class="field"><label>Parent</label>
                                            <select name="parent_id">
                                                <option value="">None</option>
                                                @foreach ($parents as $parent)
                                                    <option value="{{ $parent->id }}" @selected($category->parent_id === $parent->id)>{{ $parent->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="field"><label>Sort Order</label><input name="sort_order" value="{{ $category->sort_order }}" /></div>
                                    </div>
                                    <div class="field">
                                        <label>Description</label>
                                        <textarea name="description">{{ $category->description }}</textarea>
                                    </div>
                                    <div class="button-row">
                                        <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked($category->is_active)> <span>Active</span></label>
                                        <button class="button small" type="submit">Save</button>
                                    </div>
                                </form>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Remove this category?')" style="margin-top:10px;">
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
