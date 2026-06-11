@extends('admin.layout')

@section('title', 'Blog Posts')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <!-- Page Header -->
                <div class="admin-banner">
                    <div>
                        <div class="brand">Editorial & Content</div>
                        <h2>Blog Articles Management</h2>
                        <p class="lead" style="margin-top:8px;">Author, schedule, search, or edit your content marketing blog posts from here.</p>
                    </div>
                    <div class="toolbar-actions">
                        <a href="{{ route('admin.blog.posts.create') }}" class="button small">
                            <i class="bi bi-journal-plus"></i>
                            <span>Create Article</span>
                        </a>
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

                <!-- Search and Filters Dashboard -->
                <section class="admin-section mb-4" style="padding:15px;">
                    <form method="GET" action="{{ route('admin.blog.posts.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-4 col-sm-12">
                            <label class="mb-1" style="font-size:12px; font-weight:700;">Search Keyword</label>
                            <input name="search" value="{{ request('search') }}" placeholder="Title, slug, or primary keyword..." style="padding: 10px 12px; height: auto;" />
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <label class="mb-1" style="font-size:12px; font-weight:700;">Status</label>
                            <select name="status" style="padding: 10px 12px; height: auto;">
                                <option value="">All Statuses</option>
                               <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                                <option value="scheduled" @selected(request('status') === 'scheduled')>Scheduled</option>
                                <option value="published" @selected(request('status') === 'published')>Published</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <label class="mb-1" style="font-size:12px; font-weight:700;">Category</label>
                            <select name="category_id" style="padding: 10px 12px; height: auto;">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <label class="mb-1" style="font-size:12px; font-weight:700;">Author</label>
                            <select name="author_id" style="padding: 10px 12px; height: auto;">
                                <option value="">All Authors</option>
                                @foreach($authors as $author)
                                    <option value="{{ $author->id }}" @selected(request('author_id') == $author->id)>{{ $author->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 col-sm-6 d-flex gap-2">
                            <button type="submit" class="button small py-2 px-3 w-100" title="Filter"><i class="bi bi-funnel"></i></button>
                            <a href="{{ route('admin.blog.posts.index') }}" class="button secondary small py-2 px-3 w-100" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                        </div>
                    </form>
                </section>

                <!-- Post Table -->
                <div class="table-wrap">
                    <table class="admin-data-table align-middle">
                        <thead>
                            <tr>
                                <th>Featured Image</th>
                                <th>Article Title</th>
                                <th>Category</th>
                                <th>Author</th>
                                <th>Reading time</th>
                                <th>Publish Date</th>
                                <th>Status</th>
                                <th style="width: 180px;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($posts as $post)
                                <tr>
                                    <td>
                                        @if($post->featured_image)
                                            <img src="{{ $post->featured_image }}" alt="{{ $post->featured_image_alt }}" style="width: 72px; height: 48px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border);" />
                                        @else
                                            <div style="width: 72px; height: 48px; border-radius: 8px; background:#f1f5f9; display:grid; place-items:center;" class="text-muted">
                                                <i class="bi bi-image" style="font-size:16px;"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="admin-product-meta" style="min-width: 250px;">
                                            <strong>{{ $post->title }}</strong>
                                            <span>slug: <code>{{ $post->slug }}</code></span>
                                            <span>keyword: <code>{{ $post->primary_keyword ?? 'N/A' }}</code></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="pill">{{ $post->category->name ?? 'Uncategorized' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-soft font-monospace" style="font-size: 13px;">{{ $post->author->name ?? 'Unknown' }}</span>
                                    </td>
                                    <td class="text-center font-monospace" style="font-weight: 700;">
                                        {{ $post->reading_time ?? 1 }}m
                                    </td>
                                    <td>
                                        <span class="text-soft" style="font-size: 13px;">
                                            {{ $post->published_at ? $post->published_at->format('M d, Y H:i') : 'Draft state' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($post->status === 'published')
                                            <span class="admin-badge success">Published</span>
                                        @elseif($post->status === 'scheduled')
                                            <span class="admin-badge primary">Scheduled</span>
                                        @else
                                            <span class="admin-badge warning">Draft</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a href="{{ route('admin.blog.posts.preview', $post) }}" target="_blank" class="button secondary small py-1 px-2" title="Preview Mode">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.blog.posts.edit', $post) }}" class="button small py-1 px-2" title="Edit Article">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.blog.posts.destroy', $post) }}" onsubmit="return confirm('Permanently delete this blog article?')" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="button danger small py-1 px-2" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 muted">
                                        <i class="bi bi-journal-x text-muted" style="font-size: 3rem; display: block; margin-bottom: 12px;"></i>
                                        No blog posts matching criteria. Create an article to launch your content strategy!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $posts->links() }}
                </div>
            </div>
        </main>
    </div>
@endsection
