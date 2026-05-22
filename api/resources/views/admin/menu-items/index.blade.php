@extends('admin.layout')

@section('title', 'Menu Items')

@php
    $locationLabels = [
        'header' => 'Header Menu',
        'footer' => 'Footer Menu',
        'mobile' => 'Mobile Menu',
    ];
@endphp

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')

        <main class="admin-main">
            <div class="dashboard-card">
                <div class="topbar">
                    <div>
                        <div class="brand">Navigation Control</div>
                        <h2>Header, Footer & Mobile Menus</h2>
                        <p class="lead" style="margin-top:8px;">Add, remove, reorder, and update storefront menu links directly from admin.</p>
                    </div>
                    <div class="toolbar-actions">
                        <a href="{{ route('admin.dashboard') }}" class="button secondary small">
                            <i class="bi bi-grid-1x2"></i>
                            <span>Back to Dashboard</span>
                        </a>
                    </div>
                </div>

                @if (session('status'))
                    <div class="message">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="message" style="background: rgba(220, 38, 38, 0.08); color: #b91c1c; border-color: rgba(220, 38, 38, 0.18);">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="row g-4">
                    <div class="col-12 col-xl-4">
                        <section class="panel h-100">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h3 class="mb-1">Add Menu Item</h3>
                                    <p class="muted mb-0" style="font-size:13px;">Create a new link for header, footer, or mobile navigation.</p>
                                </div>
                                <span class="pill">Admin only</span>
                            </div>

                            <form method="POST" action="{{ route('admin.menu-items.store') }}" class="section-grid">
                                @csrf
                                <div class="form-grid">
                                    <div class="field">
                                        <label for="location">Location</label>
                                        <select id="location" name="location">
                                            <option value="header" @selected(old('location') === 'header')>Header</option>
                                            <option value="footer" @selected(old('location') === 'footer')>Footer</option>
                                            <option value="mobile" @selected(old('location') === 'mobile')>Mobile</option>
                                        </select>
                                    </div>

                                    <div class="field">
                                        <label for="title">Title</label>
                                        <input id="title" name="title" value="{{ old('title') }}" placeholder="Menu title" />
                                    </div>

                                    <div class="field">
                                        <label for="url">URL</label>
                                        <input id="url" name="url" value="{{ old('url') }}" placeholder="/pages/about-us" />
                                    </div>

                                    <div class="field">
                                        <label for="sort_order">Sort Order</label>
                                        <input id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" />
                                    </div>

                                    <div class="field">
                                        <label for="parent_id">Parent Item</label>
                                        <select id="parent_id" name="parent_id">
                                            <option value="">None</option>
                                            @foreach ($parents as $parent)
                                                <option value="{{ $parent->id }}" data-location="{{ $parent->location }}" @selected((string) old('parent_id') === (string) $parent->id)>
                                                    {{ ucfirst($parent->location) }} · {{ $parent->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="muted">Parent and child should stay in the same location.</small>
                                    </div>

                                    <div class="field">
                                        <label for="target">Target</label>
                                        <select id="target" name="target">
                                            <option value="_self" @selected(old('target', '_self') === '_self')>Same tab</option>
                                            <option value="_blank" @selected(old('target') === '_blank')>New tab</option>
                                        </select>
                                    </div>

                                    <div class="field">
                                        <label for="css_class">CSS Class</label>
                                        <input id="css_class" name="css_class" value="{{ old('css_class') }}" placeholder="optional-class" />
                                    </div>

                                    <div class="field">
                                        <label for="icon">Icon</label>
                                        <input id="icon" name="icon" value="{{ old('icon') }}" placeholder="bi bi-star" />
                                    </div>
                                </div>

                                <div class="field">
                                    <label for="config_json">Config JSON</label>
                                    <textarea id="config_json" name="config_json" class="code" rows="5">{{ old('config_json', '{}') }}</textarea>
                                    <small class="muted">Use this only if you need extra config for custom menu behavior.</small>
                                </div>

                                <div class="button-row justify-content-between">
                                    <label class="checkbox-row">
                                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1') === '1')>
                                        <span>Active</span>
                                    </label>
                                    <button class="button small" type="submit">
                                        <i class="bi bi-plus-circle"></i>
                                        <span>Create Menu Item</span>
                                    </button>
                                </div>
                            </form>
                        </section>
                    </div>

                    <div class="col-12 col-xl-8">
                        <section class="panel h-100">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h3 class="mb-1">Existing Menu Items</h3>
                                    <p class="muted mb-0" style="font-size:13px;">Edit labels, URLs, sort order, and remove links from the storefront menus.</p>
                                </div>
                                <span class="pill">{{ $menuItems->count() }} items</span>
                            </div>

                            <div class="d-grid gap-4">
                                @foreach (['header', 'footer', 'mobile'] as $location)
                                    <section class="panel" style="padding:18px;">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h4 class="mb-0">{{ $locationLabels[$location] }}</h4>
                                            <span class="admin-badge">{{ $groupedMenuItems->get($location)?->count() ?? 0 }} links</span>
                                        </div>

                                        <div class="table-wrap">
                                            <table class="admin-data-table align-middle">
                                                <thead>
                                                    <tr>
                                                        <th style="min-width: 180px;">Title</th>
                                                        <th style="min-width: 200px;">URL</th>
                                                        <th style="width: 110px;">Parent</th>
                                                        <th style="width: 90px;">Sort</th>
                                                        <th style="width: 100px;">Status</th>
                                                        <th style="width: 180px;" class="text-end">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse(($groupedMenuItems->get($location) ?? collect()) as $menuItem)
                                                        <tr>
                                                            <td>
                                                                <div style="font-weight:700; color:#111827;">{{ $menuItem->title }}</div>
                                                                <div class="muted" style="font-size:12px;">
                                                                    {{ $menuItem->target ?: '_self' }}
                                                                    @if ($menuItem->css_class)
                                                                        · {{ $menuItem->css_class }}
                                                                    @endif
                                                                </div>
                                                            </td>
                                                            <td><span class="font-monospace" style="font-size:12px;">{{ $menuItem->url }}</span></td>
                                                            <td>{{ optional($menuItem->parent)->title ?: 'None' }}</td>
                                                            <td>{{ $menuItem->sort_order }}</td>
                                                            <td>
                                                                <span class="pill {{ $menuItem->is_active ? '' : 'bg-light text-muted' }}">
                                                                    {{ $menuItem->is_active ? 'Active' : 'Inactive' }}
                                                                </span>
                                                            </td>
                                                            <td class="text-end">
                                                                <button
                                                                    class="button secondary small"
                                                                    type="button"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#menu-item-{{ $menuItem->id }}"
                                                                    aria-expanded="false"
                                                                    aria-controls="menu-item-{{ $menuItem->id }}"
                                                                >
                                                                    <i class="bi bi-pencil-square"></i>
                                                                    <span>Edit</span>
                                                                </button>
                                                                <form method="POST" action="{{ route('admin.menu-items.destroy', $menuItem) }}" class="d-inline" onsubmit="return confirm('Delete this menu item? Child items will stay but become top-level.');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button class="button danger small" type="submit">
                                                                        <i class="bi bi-trash"></i>
                                                                        <span>Delete</span>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                        <tr class="collapse" id="menu-item-{{ $menuItem->id }}">
                                                            <td colspan="6" style="background: #faf7f2;">
                                                                <form method="POST" action="{{ route('admin.menu-items.update', $menuItem) }}" class="section-grid" style="padding:18px 8px 8px;">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <div class="form-grid">
                                                                        <div class="field">
                                                                            <label>Location</label>
                                                                            <select name="location">
                                                                                <option value="header" @selected($menuItem->location === 'header')>Header</option>
                                                                                <option value="footer" @selected($menuItem->location === 'footer')>Footer</option>
                                                                                <option value="mobile" @selected($menuItem->location === 'mobile')>Mobile</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="field">
                                                                            <label>Title</label>
                                                                            <input name="title" value="{{ $menuItem->title }}" />
                                                                        </div>
                                                                        <div class="field">
                                                                            <label>URL</label>
                                                                            <input name="url" value="{{ $menuItem->url }}" />
                                                                        </div>
                                                                        <div class="field">
                                                                            <label>Parent</label>
                                                                            <select name="parent_id">
                                                                                <option value="">None</option>
                                                                                @foreach ($parents as $parent)
                                                                                    @if ($parent->id !== $menuItem->id)
                                                                                        <option value="{{ $parent->id }}" @selected($menuItem->parent_id === $parent->id)>{{ ucfirst($parent->location) }} · {{ $parent->title }}</option>
                                                                                    @endif
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="field">
                                                                            <label>Target</label>
                                                                            <select name="target">
                                                                                <option value="_self" @selected(($menuItem->target ?: '_self') === '_self')>Same tab</option>
                                                                                <option value="_blank" @selected($menuItem->target === '_blank')>New tab</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="field">
                                                                            <label>CSS Class</label>
                                                                            <input name="css_class" value="{{ $menuItem->css_class }}" />
                                                                        </div>
                                                                        <div class="field">
                                                                            <label>Icon</label>
                                                                            <input name="icon" value="{{ $menuItem->icon }}" />
                                                                        </div>
                                                                        <div class="field">
                                                                            <label>Sort Order</label>
                                                                            <input name="sort_order" value="{{ $menuItem->sort_order }}" />
                                                                        </div>
                                                                    </div>
                                                                    <div class="field">
                                                                        <label>Config JSON</label>
                                                                        <textarea name="config_json" class="code" rows="5">{{ json_encode($menuItem->config ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</textarea>
                                                                    </div>
                                                                    <div class="button-row justify-content-between">
                                                                        <label class="checkbox-row">
                                                                            <input type="checkbox" name="is_active" value="1" @checked($menuItem->is_active)>
                                                                            <span>Active</span>
                                                                        </label>
                                                                        <button class="button small" type="submit">
                                                                            <i class="bi bi-check2-circle"></i>
                                                                            <span>Save Changes</span>
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="text-center py-4 muted">No {{ $locationLabels[$location] }} items added yet.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </section>
                                @endforeach
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </main>
    </div>
@endsection
