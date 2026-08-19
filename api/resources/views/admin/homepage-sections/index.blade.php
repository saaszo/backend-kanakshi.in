@extends('admin.layout')

@section('title', 'Homepage Sections Management')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <div class="admin-banner">
                    <div>
                        <div class="brand">Homepage CMS</div>
                        <h2>Homepage Sections Management</h2>
                        <p class="lead" style="margin-top: 4px;">Control banner sliders, promotional cards, product spotlights, and section ordering.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.homepage-sections.full.edit') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-layout-text-window-reverse me-1"></i> Full Homepage Builder
                        </a>
                        <a href="{{ route('admin.homepage-sections.hero.edit') }}" class="btn btn-primary">
                            <i class="bi bi-sliders me-1"></i> Hero Carousel Studio
                        </a>
                    </div>
                </div>

                @if (session('status'))
                    <div class="p-3 mb-4" style="background: #e8f7ee; border: 1px solid #c2ebd1; color: #0d532b; font-weight: 600;">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                    </div>
                @endif

                <section class="admin-section">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Section Name</th>
                                    <th>Section Key</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Sort Order</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sections as $section)
                                    <tr>
                                        <td>
                                            <div style="font-weight: 700; color: #0f172a;">
                                                {{ $section->label ?: $section->title ?: $section->section_key }}
                                            </div>
                                            <small class="muted" style="font-size: 12px;">{{ $section->heading }}</small>
                                        </td>
                                        <td>
                                            <code style="color: #2563eb; font-weight: 600;">{{ $section->section_key }}</code>
                                        </td>
                                        <td class="text-center">
                                            @if ($section->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Hidden</span>
                                            @endif
                                        </td>
                                        <td class="text-center font-monospace" style="font-weight: 600;">
                                            {{ $section->sort_order }}
                                        </td>
                                        <td class="text-center">
                                            @if ($section->section_key === 'hero')
                                                <a href="{{ route('admin.homepage-sections.hero.edit') }}" class="btn btn-sm btn-primary py-1 px-3" style="font-size: 12px;">
                                                    <i class="bi bi-sliders me-1"></i> Open Studio
                                                </a>
                                            @elseif ($section->section_key === 'full-homepage')
                                                <a href="{{ route('admin.homepage-sections.full.edit') }}" class="btn btn-sm btn-outline-secondary py-1 px-3" style="font-size: 12px;">
                                                    <i class="bi bi-layout-text-window-reverse me-1"></i> Full Builder
                                                </a>
                                            @else
                                                <a href="{{ route('admin.homepage-sections.edit', $section) }}" class="btn btn-sm btn-outline-secondary py-1 px-3" style="font-size: 12px;">
                                                    <i class="bi bi-pencil-square me-1"></i> Edit Section
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
@endsection
