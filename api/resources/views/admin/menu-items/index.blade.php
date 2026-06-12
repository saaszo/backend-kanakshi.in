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
    <style>
        /* Modern Tabs CSS */
        .admin-tabs-nav {
            display: flex;
            gap: 8px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
        .admin-tab-btn {
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-soft);
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .admin-tab-btn:hover {
            color: var(--heading);
            background: rgba(15, 23, 42, 0.04);
        }
        .admin-tab-btn.active {
            color: var(--primary);
            background: var(--primary-glow);
        }
        .admin-tab-panel {
            display: none;
        }
        .admin-tab-panel.active {
            display: block;
        }

        /* Viewport Optimized Scrollable Table */
        .menu-table-wrap {
            max-height: calc(100vh - 340px);
            overflow-y: auto;
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
        }
        .menu-table-wrap thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f8fafc;
            border-bottom: 1px solid var(--border-strong);
        }

        /* Layout Grid */
        .menu-split-layout {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 24px;
            align-items: start;
        }
        @media (max-width: 1200px) {
            .menu-split-layout {
                grid-template-columns: 1fr;
            }
        }
        .sticky-editor {
            position: sticky;
            top: 24px;
        }
    </style>

    <div class="dashboard-shell">
        @include('admin.partials.sidebar')

        <main class="admin-main">
            <div class="admin-shell-grid">
                <div class="admin-banner">
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
                    <div class="admin-toast">
                        <div>
                            <strong>Success!</strong>
                            <p>{{ session('status') }}</p>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="admin-errors">
                        <strong>Validation Error:</strong>
                        <p>{{ $errors->first() }}</p>
                    </div>
                @endif

                <div style="background: rgba(37, 99, 235, 0.05); color: var(--primary); padding: 12px 16px; border-radius: 12px; border: 1px solid rgba(37, 99, 235, 0.1); font-size: 13px; font-weight: 500; margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-info-circle-fill"></i>
                    <span>Dropdown arrow storefront par sirf unhi menu items par dikhega jinke andar actual submenu items honge.</span>
                </div>

                <div class="menu-split-layout">
                    <!-- Left Column: Unified Add/Edit Link Form -->
                    <div class="sticky-editor">
                        <section class="admin-section" style="padding: 24px;">
                            <div class="mb-4">
                                <h3 id="form-mode-title" style="margin: 0 0 4px; font-size: 18px;">Add Menu Item</h3>
                                <p id="form-mode-subtitle" class="muted" style="margin: 0; font-size: 12px;">Create a new link for header, footer, or mobile navigation.</p>
                            </div>

                            <form method="POST" action="{{ route('admin.menu-items.store') }}" id="menu-item-form" class="admin-fields">
                                @csrf
                                <!-- Hidden inputs to save edit state on redirect validation failure -->
                                <input type="hidden" name="edit_url_saved" id="edit-url-saved" value="{{ old('edit_url_saved') }}">
                                <input type="hidden" name="edit_title_saved" id="edit-title-saved" value="{{ old('edit_title_saved') }}">

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
                                    <input id="title" name="title" value="{{ old('title') }}" placeholder="e.g. Shop All" required />
                                </div>

                                <div class="field">
                                    <label for="url">URL</label>
                                    <input id="url" name="url" value="{{ old('url') }}" placeholder="e.g. /pages/about-us" required />
                                </div>

                                <div class="field">
                                    <label for="parent_id">Parent Item</label>
                                    <select id="parent_id" name="parent_id">
                                        <option value="">None (Top Level)</option>
                                        @foreach ($parents as $parent)
                                            <option value="{{ $parent->id }}" data-location="{{ $parent->location }}" @selected((string) old('parent_id') === (string) $parent->id)>
                                                {{ ucfirst($parent->location) }} · {{ $parent->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="muted" style="font-size:11px; margin-top: 4px; display:block;">Parent and child same location me hone chahiye. Sirf top-level item ko parent banao.</small>
                                </div>

                                <div class="form-grid" style="grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 16px;">
                                    <div class="field" style="margin-bottom: 0;">
                                        <label for="sort_order">Sort Order</label>
                                        <input id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', 0) }}" />
                                    </div>

                                    <div class="field" style="margin-bottom: 0;">
                                        <label for="target">Target</label>
                                        <select id="target" name="target">
                                            <option value="_self" @selected(old('target', '_self') === '_self')>Same tab</option>
                                            <option value="_blank" @selected(old('target') === '_blank')>New tab</option>
                                        </select>
                                    </div>

                                    <div class="field" style="margin-bottom: 0;">
                                        <label for="css_class">CSS Class</label>
                                        <input id="css_class" name="css_class" value="{{ old('css_class') }}" placeholder="optional" />
                                    </div>

                                    <div class="field" style="margin-bottom: 0;">
                                        <label for="icon">Icon Class</label>
                                        <input id="icon" name="icon" value="{{ old('icon') }}" placeholder="e.g. bi bi-star" />
                                    </div>
                                </div>

                                <div class="field">
                                    <label for="config_json">Config JSON</label>
                                    <textarea id="config_json" name="config_json" class="code" rows="3" style="min-height: 80px; font-size:12px;">{{ old('config_json', '{}') }}</textarea>
                                </div>

                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 20px;">
                                    <label class="checkbox-row">
                                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1') === '1')>
                                        <span>Active</span>
                                    </label>
                                    <div style="display: flex; gap: 8px;">
                                        <button type="button" class="button secondary small" id="cancel-edit-btn" style="display: none;">Cancel</button>
                                        <button class="button small" type="submit">
                                            <i class="bi bi-check2-circle"></i>
                                            <span id="submit-btn-text">Create Menu Item</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </section>
                    </div>

                    <!-- Right Column: Tabbed Menu Items Lists -->
                    <div>
                        <section class="admin-section h-100" style="padding: 24px;">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h3 style="margin: 0 0 4px; font-size: 18px;">Navigation Links</h3>
                                    <p class="muted" style="margin: 0; font-size: 12px;">Reorder, remove, or trigger editor state for items.</p>
                                </div>
                                <span class="pill">{{ $menuItems->count() }} total links</span>
                            </div>

                            <!-- Tabs navigation for locations -->
                            <div class="admin-tabs-nav">
                                @foreach (['header', 'footer', 'mobile'] as $index => $location)
                                    <button type="button" class="admin-tab-btn {{ $index === 0 ? 'active' : '' }}" data-tab-target="panel-menu-{{ $location }}" data-tab-group="menu-locations">
                                        <span>{{ $locationLabels[$location] }}</span>
                                        <span class="admin-badge" style="margin-left: 6px; padding: 2px 6px; font-size: 10px;">
                                            {{ $groupedMenuItems->get($location)?->count() ?? 0 }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>

                            <!-- Tab Panels -->
                            @foreach (['header', 'footer', 'mobile'] as $index => $location)
                                <div class="admin-tab-panel {{ $index === 0 ? 'active' : '' }}" id="panel-menu-{{ $location }}" data-tab-panel-group="menu-locations">
                                    <div class="table-wrap menu-table-wrap">
                                        <table class="admin-data-table align-middle">
                                            <thead>
                                                <tr>
                                                    <th style="min-width: 140px;">Title</th>
                                                    <th style="min-width: 140px;">URL</th>
                                                    <th style="width: 100px;">Parent</th>
                                                    <th style="width: 70px;">Sort</th>
                                                    <th style="width: 90px;">Status</th>
                                                    <th style="width: 150px;" class="text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse(($groupedMenuItems->get($location) ?? collect()) as $menuItem)
                                                    <tr id="row-menu-item-{{ $menuItem->id }}">
                                                        <td>
                                                            <div style="font-weight: 700; color: var(--heading); display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                                                @if ($menuItem->icon)
                                                                    <i class="{{ $menuItem->icon }}"></i>
                                                                @endif
                                                                <span>{{ $menuItem->title }}</span>
                                                                @if ($menuItem->children_count > 0)
                                                                    <span class="admin-badge primary" style="font-size: 9px; padding: 1px 5px;">Dropdown ({{ $menuItem->children_count }})</span>
                                                                @elseif ($menuItem->parent_id)
                                                                    <span class="admin-badge muted" style="font-size: 9px; padding: 1px 5px;">Submenu</span>
                                                                @else
                                                                    <span class="admin-badge success" style="font-size: 9px; padding: 1px 5px;">Link</span>
                                                                @endif
                                                            </div>
                                                            @if ($menuItem->css_class || ($menuItem->target && $menuItem->target !== '_self'))
                                                                <small class="muted" style="font-size: 11px;">
                                                                    Target: {{ $menuItem->target ?? '_self' }} {{ $menuItem->css_class ? '· Class: '.$menuItem->css_class : '' }}
                                                                </small>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="font-monospace" style="font-size: 12px; color: var(--text-soft);">{{ $menuItem->url }}</span>
                                                        </td>
                                                        <td>
                                                            <span style="font-size: 13px;">{{ optional($menuItem->parent)->title ?: 'None' }}</span>
                                                        </td>
                                                        <td>
                                                            <strong>{{ $menuItem->sort_order }}</strong>
                                                        </td>
                                                        <td>
                                                            <span class="admin-badge {{ $menuItem->is_active ? 'success' : 'muted' }}">
                                                                {{ $menuItem->is_active ? 'Active' : 'Hidden' }}
                                                            </span>
                                                        </td>
                                                        <td class="text-end">
                                                            <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                                                <button
                                                                    class="button secondary small edit-menu-btn"
                                                                    type="button"
                                                                    data-id="{{ $menuItem->id }}"
                                                                    data-location="{{ $menuItem->location }}"
                                                                    data-title="{{ $menuItem->title }}"
                                                                    data-url="{{ $menuItem->url }}"
                                                                    data-sort-order="{{ $menuItem->sort_order }}"
                                                                    data-parent-id="{{ $menuItem->parent_id ?? '' }}"
                                                                    data-target="{{ $menuItem->target ?? '_self' }}"
                                                                    data-css-class="{{ $menuItem->css_class ?? '' }}"
                                                                    data-icon="{{ $menuItem->icon ?? '' }}"
                                                                    data-config-json="{{ json_encode($menuItem->config ?? [], JSON_UNESCAPED_SLASHES) }}"
                                                                    data-is-active="{{ $menuItem->is_active ? '1' : '0' }}"
                                                                    data-update-url="{{ route('admin.menu-items.update', $menuItem) }}"
                                                                >
                                                                    <i class="bi bi-pencil-square"></i>
                                                                    <span>Edit</span>
                                                                </button>
                                                                <form method="POST" action="{{ route('admin.menu-items.destroy', $menuItem) }}" style="display: inline;" onsubmit="return confirm('Delete this menu item? Child items will stay but become top-level.');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button class="button danger small" type="submit">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center py-4 muted" style="font-size: 13px;">No {{ $locationLabels[$location] }} links added yet.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </section>
                    </div>
                </div>
            </div>
        </main>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- Tabs Nav Switcher Logic ---
            document.querySelectorAll('[data-tab-target]').forEach(tabBtn => {
                tabBtn.addEventListener('click', () => {
                    const group = tabBtn.getAttribute('data-tab-group');
                    const target = tabBtn.getAttribute('data-tab-target');

                    document.querySelectorAll(`[data-tab-group="${group}"]`).forEach(btn => {
                        btn.classList.remove('active');
                    });

                    document.querySelectorAll(`[data-tab-panel-group="${group}"]`).forEach(panel => {
                        panel.classList.remove('active');
                    });

                    tabBtn.classList.add('active');
                    const targetPanel = document.getElementById(target);
                    if (targetPanel) {
                        targetPanel.classList.add('active');
                    }
                });
            });

            // --- Sync parent dropdown options based on location ---
            const createLocation = document.getElementById('location');
            const createParent = document.getElementById('parent_id');

            const syncParentOptions = () => {
                if (!createLocation || !createParent) return;
                const selectedLocation = createLocation.value;

                Array.from(createParent.options).forEach((option) => {
                    if (!option.value) {
                        option.hidden = false;
                        return;
                    }

                    option.hidden = option.dataset.location !== selectedLocation;

                    if (option.hidden && option.selected) {
                        createParent.value = '';
                    }
                });
            };

            if (createLocation && createParent) {
                createLocation.addEventListener('change', syncParentOptions);
                syncParentOptions(); // Initial call
            }

            // --- Dynamic Unified Edit State Logic ---
            const form = document.getElementById('menu-item-form');
            const formModeTitle = document.getElementById('form-mode-title');
            const formModeSubtitle = document.getElementById('form-mode-subtitle');
            const submitBtnText = document.getElementById('submit-btn-text');
            const cancelEditBtn = document.getElementById('cancel-edit-btn');
            const editUrlSavedInput = document.getElementById('edit-url-saved');
            const editTitleSavedInput = document.getElementById('edit-title-saved');

            // Click listener for row edit buttons
            document.querySelectorAll('.edit-menu-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    // Update titles
                    formModeTitle.textContent = 'Edit Menu Item';
                    formModeSubtitle.textContent = 'Modify details for the link: ' + btn.dataset.title;

                    // Set action and hidden fields
                    form.action = btn.dataset.updateUrl;
                    editUrlSavedInput.value = btn.dataset.updateUrl;
                    editTitleSavedInput.value = btn.dataset.title;

                    // Inject method input for PUT requests
                    let methodInput = form.querySelector('[name="_method"]');
                    if (!methodInput) {
                        methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'PUT';
                        form.appendChild(methodInput);
                    }

                    // Populate fields
                    form.querySelector('[name="title"]').value = btn.dataset.title;
                    form.querySelector('[name="url"]').value = btn.dataset.url;
                    form.querySelector('[name="sort_order"]').value = btn.dataset.sortOrder;
                    form.querySelector('[name="css_class"]').value = btn.dataset.cssClass;
                    form.querySelector('[name="icon"]').value = btn.dataset.icon;
                    form.querySelector('[name="config_json"]').value = btn.dataset.configJson;
                    form.querySelector('[name="target"]').value = btn.dataset.target;

                    // Set location and trigger change to sync parent options
                    createLocation.value = btn.dataset.location;
                    syncParentOptions();

                    // Set parent ID (options are synced now)
                    createParent.value = btn.dataset.parentId;

                    // Set active checkbox
                    form.querySelector('[name="is_active"]').checked = btn.dataset.isActive === '1';

                    // Update buttons
                    submitBtnText.textContent = 'Save Changes';
                    cancelEditBtn.style.display = 'inline-flex';

                    // Smooth scroll to form on small viewports
                    if (window.innerWidth < 1200) {
                        form.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });

            // Click listener for Cancel Edit button
            if (cancelEditBtn) {
                cancelEditBtn.addEventListener('click', () => {
                    // Reset titles
                    formModeTitle.textContent = 'Add Menu Item';
                    formModeSubtitle.textContent = 'Create a new link for header, footer, or mobile navigation.';

                    // Reset action and hidden values
                    form.action = '{{ route("admin.menu-items.store") }}';
                    editUrlSavedInput.value = '';
                    editTitleSavedInput.value = '';

                    // Remove PUT method injection
                    const methodInput = form.querySelector('[name="_method"]');
                    if (methodInput) methodInput.remove();

                    // Reset fields
                    form.reset();
                    syncParentOptions();

                    // Update buttons
                    submitBtnText.textContent = 'Create Menu Item';
                    cancelEditBtn.style.display = 'none';
                });
            }

            // Restore edit mode if validation fails on redirection back
            const savedUrl = editUrlSavedInput.value;
            const savedTitle = editTitleSavedInput.value;
            if (savedUrl) {
                formModeTitle.textContent = 'Edit Menu Item';
                formModeSubtitle.textContent = 'Modify details for the link: ' + savedTitle;
                form.action = savedUrl;

                let methodInput = form.querySelector('[name="_method"]');
                if (!methodInput) {
                    methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'PUT';
                    form.appendChild(methodInput);
                }

                submitBtnText.textContent = 'Save Changes';
                cancelEditBtn.style.display = 'inline-flex';
            }
        });
    </script>
@endpush
