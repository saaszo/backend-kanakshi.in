@extends('admin.layout')

@section('title', 'Homepage Sections')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="dashboard-card">
                <div class="page-head">
                    <div>
                        <div class="brand">Homepage CMS</div>
                        <h2>Homepage Sections</h2>
                        <p class="lead" style="margin-top:8px;">Edit hero, slider text, side photos, product section headings, and homepage visibility from here.</p>
                    </div>
                    <a href="{{ route('admin.homepage-sections.hero.edit') }}" class="button small">Open Hero Editor</a>
                </div>
                @if (session('status'))
                    <div class="message">{{ session('status') }}</div>
                @endif
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Section</th>
                                <th>Key</th>
                                <th>Status</th>
                                <th>Sort</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sections as $section)
                                <tr>
                                    <td>
                                        <strong>{{ $section->label ?: $section->title ?: $section->section_key }}</strong>
                                        <div class="muted">{{ $section->heading }}</div>
                                    </td>
                                    <td><code>{{ $section->section_key }}</code></td>
                                    <td>{{ $section->is_active ? 'Active' : 'Hidden' }}</td>
                                    <td>{{ $section->sort_order }}</td>
                                    <td>
                                        @if ($section->section_key === 'hero')
                                            <a href="{{ route('admin.homepage-sections.hero.edit') }}" class="button secondary small">Open Hero Editor</a>
                                        @else
                                            <a href="{{ route('admin.homepage-sections.edit', $section) }}" class="button secondary small">Edit Section</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
@endsection
