@extends('admin.layout')

@section('title', 'Categories Management')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <div class="admin-banner">
                    <div>
                        <div class="brand">Catalog</div>
                        <h2>Categories Management</h2>
                        <p class="lead" style="margin-top: 4px;">Organize fine jewellery collections, rings, earrings, necklaces, and bridal sets.</p>
                    </div>
                </div>

                @if (session('status'))
                    <div class="p-3 mb-4" style="background: #e8f7ee; border: 1px solid #c2ebd1; color: #0d532b; font-weight: 600;">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                    </div>
                @endif

                <div class="row g-4">
                    <!-- Left: Create Category Form -->
                    <div class="col-lg-4">
                        <section class="admin-section h-100">
                            <h3 class="mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-plus-circle" style="color: #2563eb;"></i>
                                <span>Create Category</span>
                            </h3>
                            
                            <form method="POST" action="{{ route('admin.categories.store') }}" data-auto-slug-form enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600; font-size: 13px;">Category Name *</label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. Solitaire Rings" data-slug-source required />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600; font-size: 13px;">URL Slug *</label>
                                    <input type="text" name="slug" class="form-control" placeholder="solitaire-rings" data-slug-target required />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600; font-size: 13px;">Parent Collection</label>
                                    <select name="parent_id" class="form-select">
                                        <option value="">None (Top Level)</option>
                                        @foreach ($parents as $parent)
                                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600; font-size: 13px;">Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control" value="0" />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600; font-size: 13px;">Category Image</label>
                                    <input type="file" name="image_file" accept="image/*" class="form-control mb-2" />
                                    <input type="text" name="image" class="form-control" placeholder="Or paste image URL" />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600; font-size: 13px;">Description</label>
                                    <textarea name="description" class="form-control" rows="2" placeholder="Collection narrative..."></textarea>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" checked>
                                    <label class="form-check-label" for="is_active" style="font-size: 13px; font-weight: 600;">
                                        Publish on Storefront
                                    </label>
                                </div>

                                <button class="btn btn-primary w-100" type="submit">
                                    <i class="bi bi-check-lg me-1"></i> Save Category
                                </button>
                            </form>
                        </section>
                    </div>

                    <!-- Right: Existing Categories Table -->
                    <div class="col-lg-8">
                        <section class="admin-section h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h3 class="mb-0">Existing Categories</h3>
                                    <p class="muted mb-0" style="font-size: 13px;">{{ count($categories) }} total categories registered.</p>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Parent</th>
                                            <th class="text-center">Order</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($categories as $category)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div style="width: 44px; height: 44px; background: #f8fafc; border: 1px solid var(--border); overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                                            @if ($category->image)
                                                                <img src="{{ $category->image }}" alt="{{ $category->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                            @else
                                                                <i class="bi bi-tag" style="font-size: 18px; color: #94a3b8;"></i>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <div style="font-weight: 700; color: #0f172a;">{{ $category->name }}</div>
                                                            <small class="muted" style="font-size: 12px;">/{{ $category->slug }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if ($category->parent)
                                                        <span class="badge bg-secondary">{{ $category->parent->name }}</span>
                                                    @else
                                                        <span class="muted" style="font-size: 12px;">— Root</span>
                                                    @endif
                                                </td>
                                                <td class="text-center font-monospace" style="font-weight: 600;">
                                                    {{ $category->sort_order }}
                                                </td>
                                                <td class="text-center">
                                                    @if ($category->is_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary">Hidden</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="Are you sure you want to delete category '{{ $category->name }}'?" data-confirm-title="Delete Category" data-confirm-btn="Delete Category">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-sm btn-outline-danger py-1 px-2" style="font-size: 11px;" type="submit">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4" style="color: #64748b;">
                                                    No categories created yet.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
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

            document.querySelectorAll('[data-auto-slug-form]').forEach((form) => {
                const slugSource = form.querySelector('[data-slug-source]');
                const slugTarget = form.querySelector('[data-slug-target]');

                if (!slugSource || !slugTarget) {
                    return;
                }

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
            });
        })();
    </script>
@endpush
