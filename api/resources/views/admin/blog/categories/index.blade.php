@extends('admin.layout')

@section('title', 'Blog Categories')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <div class="admin-banner">
                    <div>
                        <div class="brand">Editorial & Blog</div>
                        <h2>Blog Categories</h2>
                        <p class="lead" style="margin-top:8px;">Manage your blog clusters and topics to enhance SEO categorization.</p>
                    </div>
                </div>

                @if(session('success'))
                    <div class="admin-toast">
    <div>
        <strong>Success!</strong>
        <p>{{ session('success') }}</p>
    </div>
</div>
                @endif
                @if(session('error'))
                    <div class="admin-errors">{{ session('error') }}</div>
                @endif

                <div class="split-grid">
                    <!-- Left Panel: Create Category -->
                    <section class="admin-section">
                        <h3>Create Category</h3>
                        <form method="POST" action="{{ route('admin.blog.categories.store') }}" class="admin-fields" data-auto-slug-form>
                            @csrf
                            <div class="form-grid one">
                                <div class="field">
                                    <label>Category Name</label>
                                    <input name="name" required placeholder="e.g. Temple Traditions" data-slug-source />
                                </div>
                                <div class="field">
                                    <label>Slug (URL Segment)</label>
                                    <input name="slug" placeholder="e.g. temple-traditions" data-slug-target />
                                </div>
                                <div class="field">
                                    <label>SEO Meta Title</label>
                                    <input name="meta_title" placeholder="Target 45-65 characters" />
                                </div>
                                <div class="field">
                                    <label>SEO Meta Description</label>
                                    <textarea name="meta_description" placeholder="Target 140-160 characters"></textarea>
                                </div>
                                <div class="field">
                                    <label>Category Description</label>
                                    <textarea name="description" placeholder="Brief snippet describing this topic range"></textarea>
                                </div>
                            </div>
                            <div class="button-row">
                                <button class="button small" type="submit">Create Category</button>
                            </div>
                        </form>
                    </section>

                    <!-- Right Panel: Existing Categories List -->
                    <section class="admin-section">
                        <h3>Existing Blog Categories</h3>
                        <div class="admin-fields">
                            @forelse($categories as $category)
                                <div class="admin-section" style="padding: 18px;">
                                    <form method="POST" action="{{ route('admin.blog.categories.update', $category) }}" class="admin-fields" data-auto-slug-form>
                                        @csrf
                                        @method('PUT')
                                        <div class="form-grid one">
                                            <div class="field">
                                                <label>Category Name</label>
                                                <input name="name" value="{{ $category->name }}" required data-slug-source />
                                            </div>
                                            <div class="field">
                                                <label>Slug (URL Segment)</label>
                                                <input name="slug" value="{{ $category->slug }}" data-slug-target />
                                            </div>
                                            <div class="field">
                                                <label>SEO Meta Title</label>
                                                <input name="meta_title" value="{{ $category->meta_title }}" />
                                            </div>
                                            <div class="field">
                                                <label>SEO Meta Description</label>
                                                <textarea name="meta_description">{{ $category->meta_description }}</textarea>
                                            </div>
                                            <div class="field">
                                                <label>Category Description</label>
                                                <textarea name="description">{{ $category->description }}</textarea>
                                            </div>
                                        </div>
                                        <div class="button-row" style="margin-top: 10px;">
                                            <span class="pill">Posts count: {{ $category->posts_count }}</span>
                                            <button class="button small ms-auto" type="submit">Save Changes</button>
                                        </div>
                                    </form>
                                    <form method="POST" action="{{ route('admin.blog.categories.destroy', $category) }}" onsubmit="return confirm('Delete this blog category? All related posts will be set to uncategorized.')" style="margin-top:10px;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button danger small" type="submit">Delete</button>
                                    </form>
                                </div>
                            @empty
                                <div class="dashboard-empty">
                                    <i class="bi bi-collection-play text-muted" style="font-size: 2.5rem; display:block; margin-bottom:10px;"></i>
                                    <span>No blog categories found. Create one on the left to start!</span>
                                </div>
                            @endforelse

                            <div style="margin-top: 15px;">
                                {{ $categories->links() }}
                            </div>
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
