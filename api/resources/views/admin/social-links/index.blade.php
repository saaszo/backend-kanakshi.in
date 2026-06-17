@extends('admin.layout')

@section('title', 'Social Links')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <div class="admin-banner">
                    <div>
                        <div class="brand">Brand Handles</div>
                        <h2>Social Links</h2>
                        <p class="lead" style="margin-top:8px;">Update footer social icons and brand URLs from here.</p>
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
                <div class="split-grid">
                    <section class="admin-section">
                        <h3>Add Social Link</h3>
                        <form method="POST" action="{{ route('admin.social-links.store') }}" class="admin-fields">
                            @csrf
                            <div class="form-grid">
                                <div class="field"><label>Platform</label><input name="platform" /></div>
                                <div class="field"><label>Title</label><input name="title" /></div>
                                <div class="field"><label>Handle</label><input name="handle" /></div>
                                <div class="field">
                                    <label>URL</label>
                                    <input name="url" placeholder="https://kanakshi.in" />
                                    <small class="muted">youtube.com/... bhi chalega, system automatically https:// add kar dega.</small>
                                </div>
                                <div class="field"><label>Icon</label><input name="icon" /></div>
                                <div class="field"><label>Sort Order</label><input name="sort_order" value="0" /></div>
                            </div>
                            <div class="button-row">
                                <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" checked> <span>Active</span></label>
                                <button class="button small" type="submit">Create Social Link</button>
                            </div>
                        </form>
                    </section>

                    <section class="admin-section">
                        <h3>Existing Social Links</h3>
                        <div class="admin-fields">
                            @foreach ($socialLinks as $socialLink)
                                <form method="POST" action="{{ route('admin.social-links.update', $socialLink) }}" class="admin-section" style="padding:18px;">
                                    @csrf
                                    @method('PUT')
                                    <div class="form-grid">
                                        <div class="field"><label>Platform</label><input name="platform" value="{{ $socialLink->platform }}" /></div>
                                        <div class="field"><label>Title</label><input name="title" value="{{ $socialLink->title }}" /></div>
                                        <div class="field"><label>Handle</label><input name="handle" value="{{ $socialLink->handle }}" /></div>
                                        <div class="field">
                                            <label>URL</label>
                                            <input name="url" value="{{ $socialLink->url }}" placeholder="https://kanakshi.in" />
                                        </div>
                                        <div class="field"><label>Icon</label><input name="icon" value="{{ $socialLink->icon }}" /></div>
                                        <div class="field"><label>Sort Order</label><input name="sort_order" value="{{ $socialLink->sort_order }}" /></div>
                                    </div>
                                    <div class="button-row">
                                        <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked($socialLink->is_active)> <span>Active</span></label>
                                        <button class="button small" type="submit">Save</button>
                                    </div>
                                </form>
                                <form method="POST" action="{{ route('admin.social-links.destroy', $socialLink) }}" onsubmit="return confirm('Delete this social link?')" style="margin-top:10px;">
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
