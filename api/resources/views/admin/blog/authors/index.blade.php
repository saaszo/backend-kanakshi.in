@extends('admin.layout')

@section('title', 'Blog Authors')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <div class="admin-banner">
                    <div>
                        <div class="brand">Editorial & Blog</div>
                        <h2>Blog Authors</h2>
                        <p class="lead" style="margin-top:8px;">Manage your content creation team profiles, avatars, and bios.</p>
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
                    <!-- Left Panel: Create Author -->
                    <section class="admin-section">
                        <h3>Create Author</h3>
                        <form method="POST" action="{{ route('admin.blog.authors.store') }}" class="admin-fields" data-auto-slug-form enctype="multipart/form-data">
                            @csrf
                            <div class="form-grid one">
                                <div class="field">
                                    <label>Author Name</label>
                                    <input name="name" required placeholder="e.g. Pt. Ramesh Shastri" data-slug-source />
                                </div>
                                <div class="field">
                                    <label>Slug (URL Segment)</label>
                                    <input name="slug" placeholder="e.g. pt-ramesh-shastri" data-slug-target />
                                </div>
                                <div class="field">
                                    <label>Twitter Handle</label>
                                    <input name="twitter_handle" placeholder="e.g. ramesh_shastri" />
                                </div>
                                <div class="field">
                                    <label>Profile Avatar Image</label>
                                    <input type="file" name="avatar_upload" accept="image/*" />
                                </div>
                                <div class="field">
                                    <label>Avatar Image Alt Description</label>
                                    <input name="avatar_alt" placeholder="e.g. Portrait of Ramesh Shastri" />
                                </div>
                                <div class="field">
                                    <label>Author Biography</label>
                                    <textarea name="bio" placeholder="Brief expert credentials and bio description..."></textarea>
                                </div>
                            </div>
                            <div class="button-row">
                                <button class="button small" type="submit">Create Author</button>
                            </div>
                        </form>
                    </section>

                    <!-- Right Panel: Existing Authors -->
                    <section class="admin-section">
                        <h3>Existing Authors</h3>
                        <div class="admin-fields">
                            @forelse($authors as $author)
                                <div class="admin-section" style="padding: 18px;">
                                    <form method="POST" action="{{ route('admin.blog.authors.update', $author) }}" class="admin-fields" data-auto-slug-form enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <div class="form-grid one">
                                            <div class="field">
                                                <label>Author Name</label>
                                                <input name="name" value="{{ $author->name }}" required data-slug-source />
                                            </div>
                                            <div class="field">
                                                <label>Slug (URL Segment)</label>
                                                <input name="slug" value="{{ $author->slug }}" data-slug-target />
                                            </div>
                                            <div class="field">
                                                <label>Twitter Handle</label>
                                                <input name="twitter_handle" value="{{ $author->twitter_handle }}" />
                                            </div>
                                            <div class="field d-flex align-items-center gap-3">
                                                @if($author->avatar)
                                                    <img src="{{ $author->avatar }}" alt="{{ $author->avatar_alt }}" class="admin-upload-preview admin-upload-preview--small" style="border-radius:50%;" />
                                                @else
                                                    <span class="admin-upload-preview admin-upload-preview--small d-grid place-items-center bg-light text-muted" style="border-radius:50%; font-size: 20px;">
                                                        <i class="bi bi-person"></i>
                                                    </span>
                                                @endif
                                                <div class="flex-grow-1">
                                                    <label class="mb-1">Replace Profile Avatar</label>
                                                    <input type="file" name="avatar_upload" accept="image/*" />
                                                </div>
                                            </div>
                                            <div class="field">
                                                <label>Avatar Alt Description</label>
                                                <input name="avatar_alt" value="{{ $author->avatar_alt }}" />
                                            </div>
                                            <div class="field">
                                                <label>Author Biography</label>
                                                <textarea name="bio">{{ $author->bio }}</textarea>
                                            </div>
                                        </div>
                                        <div class="button-row" style="margin-top: 10px;">
                                            <span class="pill">Articles count: {{ $author->posts_count }}</span>
                                            <button class="button small ms-auto" type="submit">Save Changes</button>
                                        </div>
                                    </form>
                                    <form method="POST" action="{{ route('admin.blog.authors.destroy', $author) }}" onsubmit="return confirm('Delete this author? Active articles will be left authorless.')" style="margin-top:10px;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button danger small" type="submit">Delete</button>
                                    </form>
                                </div>
                            @empty
                                <div class="dashboard-empty">
                                    <i class="bi bi-people text-muted" style="font-size: 2.5rem; display:block; margin-bottom:10px;"></i>
                                    <span>No blog authors found. Create one on the left to start!</span>
                                </div>
                            @endforelse

                            <div style="margin-top: 15px;">
                                {{ $authors->links() }}
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
