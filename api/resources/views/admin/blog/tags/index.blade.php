@extends('admin.layout')

@section('title', 'Blog Tags')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <div class="admin-banner">
                    <div>
                        <div class="brand">Editorial & Blog</div>
                        <h2>Blog Tags</h2>
                        <p class="lead" style="margin-top:8px;">Create, update, or remove editorial tags for descriptive article classification.</p>
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

                <div class="split-grid">
                    <!-- Left Panel: Create Tag -->
                    <section class="admin-section">
                        <h3>Create Tag</h3>
                        <form method="POST" action="{{ route('admin.blog.tags.store') }}" class="admin-fields" data-auto-slug-form>
                            @csrf
                            <div class="form-grid one">
                                <div class="field">
                                    <label>Tag Name</label>
                                    <input name="name" required placeholder="e.g. Brassware Care" data-slug-source />
                                </div>
                                <div class="field">
                                    <label>Slug (URL Segment)</label>
                                    <input name="slug" placeholder="e.g. brassware-care" data-slug-target />
                                </div>
                            </div>
                            <div class="button-row">
                                <button class="button small" type="submit">Create Tag</button>
                            </div>
                        </form>
                    </section>

                    <!-- Right Panel: Existing Tags -->
                    <section class="admin-section">
                        <h3>Existing Tags</h3>
                        <div class="admin-fields">
                            @forelse($tags as $tag)
                                <div class="admin-section" style="padding: 18px;">
                                    <form method="POST" action="{{ route('admin.blog.tags.update', $tag) }}" class="admin-fields" data-auto-slug-form>
                                        @csrf
                                        @method('PUT')
                                        <div class="form-grid one">
                                            <div class="field">
                                                <label>Tag Name</label>
                                                <input name="name" value="{{ $tag->name }}" required data-slug-source />
                                            </div>
                                            <div class="field">
                                                <label>Slug (URL Segment)</label>
                                                <input name="slug" value="{{ $tag->slug }}" data-slug-target />
                                            </div>
                                        </div>
                                        <div class="button-row" style="margin-top: 10px;">
                                            <span class="pill">Posts count: {{ $tag->posts_count }}</span>
                                            <button class="button small ms-auto" type="submit">Save Changes</button>
                                        </div>
                                    </form>
                                    <form method="POST" action="{{ route('admin.blog.tags.destroy', $tag) }}" onsubmit="return confirm('Delete this tag? Connections to posts will be severed.')" style="margin-top:10px;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button danger small" type="submit">Delete</button>
                                    </form>
                                </div>
                            @empty
                                <div class="dashboard-empty">
                                    <i class="bi bi-hash text-muted" style="font-size: 2.5rem; display:block; margin-bottom:10px;"></i>
                                    <span>No blog tags found. Create one on the left to start!</span>
                                </div>
                            @endforelse

                            <div style="margin-top: 15px;">
                                {{ $tags->links() }}
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
