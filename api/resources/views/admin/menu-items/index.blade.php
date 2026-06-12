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

        /* Viewport Optimized Scrollable List */
        .menu-list-wrap {
            max-height: calc(100vh - 280px);
            overflow-y: auto;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
            padding: 14px;
        }
        .menu-list {
            display: grid;
            gap: 12px;
        }
        .menu-list-item {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(220px, 1.2fr) 96px 110px 140px;
            gap: 16px;
            align-items: center;
            padding: 16px 18px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: #fff;
        }
        .menu-list-item + .menu-list-item {
            margin-top: 0;
        }
        .menu-list-meta-label {
            display: block;
            margin-bottom: 4px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--text-soft);
        }
        .menu-list-url {
            font-family: var(--font-mono, ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace);
            font-size: 12px;
            line-height: 1.6;
            color: var(--text-soft);
            word-break: break-word;
        }
        .menu-list-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            align-items: center;
        }
        .menu-list-empty {
            padding: 28px 16px;
            text-align: center;
            color: var(--text-soft);
            font-size: 13px;
        }
        @media (max-width: 1100px) {
            .menu-list-item {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .menu-list-actions {
                grid-column: 1 / -1;
                justify-content: flex-start;
            }
        }
        @media (max-width: 720px) {
            .menu-list-item {
                grid-template-columns: 1fr;
            }
        }

        /* Modal CSS */
        .admin-modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .admin-modal-overlay.active {
            display: flex;
            opacity: 1;
        }
        .admin-modal {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            transform: translateY(20px) scale(0.98);
            transition: all 0.2s ease;
        }
        .admin-modal-overlay.active .admin-modal {
            transform: translateY(0) scale(1);
        }
        .admin-modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 10;
        }
        .admin-modal-header h3 { margin: 0; font-size: 18px; }
        .admin-modal-close {
            background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-soft); padding: 0; line-height: 1;
        }
        .admin-modal-close:hover { color: var(--danger); }
        .admin-modal-body {
            padding: 24px;
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
                            <span>Dashboard</span>
                        </a>
                        <button type="button" class="button small" id="open-add-modal-btn">
                            <i class="bi bi-plus-lg"></i>
                            <span>Add Menu Link</span>
                        </button>
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
                    <div class="admin-errors" id="server-errors" data-has-errors="true">
                        <strong>Validation Error:</strong>
                        <p>{{ $errors->first() }}</p>
                    </div>
                @endif

                <div style="background: rgba(37, 99, 235, 0.05); color: var(--primary); padding: 12px 16px; border-radius: 12px; border: 1px solid rgba(37, 99, 235, 0.1); font-size: 13px; font-weight: 500; margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-info-circle-fill"></i>
                    <span>Dropdown arrow storefront par sirf unhi menu items par dikhega jinke andar actual submenu items honge.</span>
                </div>

                <!-- Tabbed Menu Items Lists -->
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
                            <div class="menu-list-wrap">
                                <div class="menu-list">
                                    @forelse(($groupedMenuItems->get($location) ?? collect()) as $menuItem)
                                        <article class="menu-list-item" id="row-menu-item-{{ $menuItem->id }}">
                                            <div>
                                                <span class="menu-list-meta-label">Title</span>
                                                <div style="font-weight: 700; color: var(--heading); display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                                    @if ($menuItem->icon)
                                                        <i class="{{ $menuItem->icon }}"></i>
                                                    @endif
                                                    <span>{{ $menuItem->title }}</span>
                                                    @if ($menuItem->children_count > 0)
                                                        <span class="admin-badge primary" style="font-size: 10px; padding: 2px 7px;">Dropdown {{ $menuItem->children_count }}</span>
                                                    @elseif ($menuItem->parent_id)
                                                        <span class="admin-badge muted" style="font-size: 10px; padding: 2px 7px;">Submenu</span>
                                                    @else
                                                        <span class="admin-badge success" style="font-size: 10px; padding: 2px 7px;">Link</span>
                                                    @endif
                                                </div>
                                                @if ($menuItem->css_class || ($menuItem->target && $menuItem->target !== '_self'))
                                                    <small class="muted" style="font-size: 11px; display: block; margin-top: 6px;">
                                                        Target: {{ $menuItem->target ?? '_self' }}{{ $menuItem->css_class ? ' · Class: '.$menuItem->css_class : '' }}
                                                    </small>
                                                @endif
                                            </div>

                                            <div>
                                                <span class="menu-list-meta-label">URL</span>
                                                <div class="menu-list-url">{{ $menuItem->url }}</div>
                                            </div>

                                            <div>
                                                <span class="menu-list-meta-label">Parent</span>
                                                <div style="font-size: 13px; font-weight: 600; color: var(--heading);">
                                                    {{ optional($menuItem->parent)->title ?: 'None' }}
                                                </div>
                                            </div>

                                            <div>
                                                <span class="menu-list-meta-label">Sort / Status</span>
                                                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                                    <strong>{{ $menuItem->sort_order }}</strong>
                                                    <span class="admin-badge {{ $menuItem->is_active ? 'success' : 'muted' }}">
                                                        {{ $menuItem->is_active ? 'Active' : 'Hidden' }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="menu-list-actions">
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
                                        </article>
                                    @empty
                                        <div class="menu-list-empty">No {{ $locationLabels[$location] }} links added yet.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endforeach
                </section>
            </div>
        </main>
    </div>

    <!-- Edit/Add Modal Overlay -->
    <div class="admin-modal-overlay" id="menu-item-modal">
        <div class="admin-modal">
            <div class="admin-modal-header">
                <div>
                    <h3 id="form-mode-title">Add Menu Item</h3>
                    <p id="form-mode-subtitle" class="muted" style="margin: 0; font-size: 12px; margin-top: 4px;">Create a new link for navigation.</p>
                </div>
                <button type="button" class="admin-modal-close" id="close-modal-btn">&times;</button>
            </div>
            <div class="admin-modal-body">
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
                            <button class="button small" type="submit" style="width: 100%; padding: 12px;">
                                <i class="bi bi-check2-circle"></i>
                                <span id="submit-btn-text">Save Menu Item</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const initMenuItemAdminPage = () => {
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
                createLocation.addEventListener('change', () => {
                    try {
                        syncParentOptions();
                    } catch (error) {
                        console.error('Unable to update parent menu options.', error);
                    }
                });
            }

            // --- Modal Logic ---
            const modal = document.getElementById('menu-item-modal');
            const btnOpenAdd = document.getElementById('open-add-modal-btn');
            const btnClose = document.getElementById('close-modal-btn');
            
            const form = document.getElementById('menu-item-form');
            const formModeTitle = document.getElementById('form-mode-title');
            const formModeSubtitle = document.getElementById('form-mode-subtitle');
            const submitBtnText = document.getElementById('submit-btn-text');
            const editUrlSavedInput = document.getElementById('edit-url-saved');
            const editTitleSavedInput = document.getElementById('edit-title-saved');

            const openModal = () => {
                if (!modal) return;
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            };

            const closeModal = () => {
                if (!modal) return;
                modal.classList.remove('active');
                document.body.style.overflow = '';
            };

            const resetFormToAddMode = () => {
                if (!form || !formModeTitle || !formModeSubtitle || !editUrlSavedInput || !editTitleSavedInput || !submitBtnText) {
                    return;
                }
                formModeTitle.textContent = 'Add Menu Item';
                formModeSubtitle.textContent = 'Create a new link for header, footer, or mobile navigation.';
                form.action = '{{ route("admin.menu-items.store") }}';
                editUrlSavedInput.value = '';
                editTitleSavedInput.value = '';
                
                const methodInput = form.querySelector('[name="_method"]');
                if (methodInput) methodInput.remove();
                
                form.reset();
                syncParentOptions();
                submitBtnText.textContent = 'Create Menu Item';
            };

            if (btnOpenAdd) {
                btnOpenAdd.addEventListener('click', () => {
                    resetFormToAddMode();
                    openModal();
                });
            }

            if (btnClose) {
                btnClose.addEventListener('click', closeModal);
            }

            if (modal) {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) closeModal();
                });
            }

            if (createLocation && createParent) {
                try {
                    syncParentOptions();
                } catch (error) {
                    console.error('Unable to update parent menu options.', error);
                }
            }

            // Click listener for row edit buttons
            document.querySelectorAll('.edit-menu-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (!form || !formModeTitle || !formModeSubtitle || !editUrlSavedInput || !editTitleSavedInput || !submitBtnText || !createLocation || !createParent) {
                        return;
                    }
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
                    
                    openModal();
                });
            });

            // Restore edit mode and open modal if validation fails on redirection back
            const serverErrorsDiv = document.getElementById('server-errors');
            const hasErrors = serverErrorsDiv && serverErrorsDiv.dataset.hasErrors === 'true';
            const savedUrl = editUrlSavedInput ? editUrlSavedInput.value : '';
            const savedTitle = editTitleSavedInput ? editTitleSavedInput.value : '';
            
            if (hasErrors || savedUrl) {
                if (savedUrl && form && formModeTitle && formModeSubtitle && submitBtnText) {
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
                }
                openModal();
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMenuItemAdminPage, { once: true });
        } else {
            initMenuItemAdminPage();
        }
    </script>
@endpush
