@extends('admin.layout')

@section('title', 'Menu Items')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="dashboard-card">
                <div class="page-head">
                    <div>
                        <div class="brand">Navigation</div>
                        <h2>Header & Footer Menu</h2>
                        <p class="lead" style="margin-top:8px;">Manage storefront navigation links, order, and optional submenu config from admin.</p>
                    </div>
                </div>
                @if (session('status'))
                    <div class="message">{{ session('status') }}</div>
                @endif
                <div class="split-grid">
                    <section class="panel">
                        <h3>Add Menu Item</h3>
                        <form method="POST" action="{{ route('admin.menu-items.store') }}" class="section-grid">
                            @csrf
                            <div class="form-grid">
                                <div class="field">
                                    <label>Location</label>
                                    <select name="location">
                                        <option value="header">Header</option>
                                        <option value="footer">Footer</option>
                                        <option value="mobile">Mobile</option>
                                    </select>
                                </div>
                                <div class="field"><label>Title</label><input name="title" /></div>
                                <div class="field"><label>URL</label><input name="url" /></div>
                                <div class="field">
                                    <label>Parent Item</label>
                                    <select name="parent_id">
                                        <option value="">None</option>
                                        @foreach ($parents as $parent)
                                            <option value="{{ $parent->id }}">{{ $parent->location }} · {{ $parent->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="field"><label>Target</label><input name="target" value="_self" /></div>
                                <div class="field"><label>CSS Class</label><input name="css_class" /></div>
                                <div class="field"><label>Icon</label><input name="icon" /></div>
                                <div class="field"><label>Sort Order</label><input name="sort_order" value="0" /></div>
                            </div>
                            <div class="field">
                                <label>Config JSON</label>
                                <textarea name="config_json" class="code">{}</textarea>
                            </div>
                            <div class="button-row">
                                <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" checked> <span>Active</span></label>
                                <button class="button small" type="submit">Create Menu Item</button>
                            </div>
                        </form>
                    </section>

                    <section class="panel">
                        <h3>Existing Menu Items</h3>
                        <div class="section-grid">
                            @foreach ($menuItems as $menuItem)
                                <form method="POST" action="{{ route('admin.menu-items.update', $menuItem) }}" class="panel" style="padding:18px;">
                                    @csrf
                                    @method('PUT')
                                    <div class="button-row" style="justify-content:space-between;">
                                        <strong>{{ $menuItem->title }}</strong>
                                        <span class="pill">{{ $menuItem->location }}</span>
                                    </div>
                                    <div class="form-grid" style="margin-top:16px;">
                                        <div class="field">
                                            <label>Location</label>
                                            <select name="location">
                                                <option value="header" @selected($menuItem->location === 'header')>Header</option>
                                                <option value="footer" @selected($menuItem->location === 'footer')>Footer</option>
                                                <option value="mobile" @selected($menuItem->location === 'mobile')>Mobile</option>
                                            </select>
                                        </div>
                                        <div class="field"><label>Title</label><input name="title" value="{{ $menuItem->title }}" /></div>
                                        <div class="field"><label>URL</label><input name="url" value="{{ $menuItem->url }}" /></div>
                                        <div class="field">
                                            <label>Parent Item</label>
                                            <select name="parent_id">
                                                <option value="">None</option>
                                                @foreach ($parents as $parent)
                                                    <option value="{{ $parent->id }}" @selected($menuItem->parent_id === $parent->id)>{{ $parent->location }} · {{ $parent->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="field"><label>Target</label><input name="target" value="{{ $menuItem->target }}" /></div>
                                        <div class="field"><label>CSS Class</label><input name="css_class" value="{{ $menuItem->css_class }}" /></div>
                                        <div class="field"><label>Icon</label><input name="icon" value="{{ $menuItem->icon }}" /></div>
                                        <div class="field"><label>Sort Order</label><input name="sort_order" value="{{ $menuItem->sort_order }}" /></div>
                                    </div>
                                    <div class="field">
                                        <label>Config JSON</label>
                                        <textarea name="config_json" class="code">{{ json_encode($menuItem->config ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</textarea>
                                    </div>
                                    <div class="button-row">
                                        <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked($menuItem->is_active)> <span>Active</span></label>
                                        <button class="button small" type="submit">Save</button>
                                    </div>
                                </form>
                                <form method="POST" action="{{ route('admin.menu-items.destroy', $menuItem) }}" onsubmit="return confirm('Delete this menu item?')" style="margin-top:10px;">
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
