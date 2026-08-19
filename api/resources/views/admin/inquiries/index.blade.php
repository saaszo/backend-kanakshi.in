@extends('admin.layout')

@section('title', 'Customer Inquiries & Concierge')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <div class="admin-banner">
                    <div>
                        <div class="brand">Customer Care & Support</div>
                        <h2>Contact Inquiries & Concierge</h2>
                        <p class="lead" style="margin-top:8px;">View and respond to inquiries submitted via the Contact Us form, including ring size consultations, custom orders, and order assistance.</p>
                    </div>
                </div>

                @if (session('status'))
                    <div class="message mb-4" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #34d399; padding: 15px; border-radius: var(--radius-md); font-weight: 500;">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                    </div>
                @endif

                <div class="metrics-grid mb-4">
                    <div class="admin-stat">
                        <small>Total Messages</small>
                        <strong>{{ $counts['total'] }}</strong>
                        <span>All inquiries received</span>
                    </div>
                    <div class="admin-stat">
                        <small>Pending Response</small>
                        <strong style="color: var(--warning);">{{ $counts['pending'] }}</strong>
                        <span>Awaiting attention</span>
                    </div>
                    <div class="admin-stat">
                        <small>Contacted / In Progress</small>
                        <strong style="color: var(--primary);">{{ $counts['contacted'] }}</strong>
                        <span>Customer reached</span>
                    </div>
                    <div class="admin-stat">
                        <small>Resolved Inquiries</small>
                        <strong style="color: var(--success);">{{ $counts['resolved'] }}</strong>
                        <span>Closed inquiries</span>
                    </div>
                </div>

                <section class="admin-section">
                    <div class="admin-toolbar mb-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h3>Inquiry Messages</h3>
                            <p class="muted mb-0">Review questions sent by website visitors and customers.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.inquiries.index') }}" class="btn btn-sm {{ !$status ? 'btn-primary' : 'btn-outline-secondary' }}">All ({{ $counts['total'] }})</a>
                            <a href="{{ route('admin.inquiries.index', ['status' => 'pending']) }}" class="btn btn-sm {{ $status === 'pending' ? 'btn-warning' : 'btn-outline-secondary' }}">Pending ({{ $counts['pending'] }})</a>
                            <a href="{{ route('admin.inquiries.index', ['status' => 'contacted']) }}" class="btn btn-sm {{ $status === 'contacted' ? 'btn-info' : 'btn-outline-secondary' }}">Contacted ({{ $counts['contacted'] }})</a>
                            <a href="{{ route('admin.inquiries.index', ['status' => 'resolved']) }}" class="btn btn-sm {{ $status === 'resolved' ? 'btn-success' : 'btn-outline-secondary' }}">Resolved ({{ $counts['resolved'] }})</a>
                        </div>
                    </div>

                    @if ($inquiries->count())
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Contact</th>
                                        <th>Topic</th>
                                        <th>Message Snippet</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($inquiries as $inquiry)
                                        <tr>
                                            <td style="white-space: nowrap; font-size: 0.85rem;">
                                                <strong>{{ \Carbon\Carbon::parse($inquiry->created_at)->format('d M Y') }}</strong>
                                                <div class="muted">{{ \Carbon\Carbon::parse($inquiry->created_at)->format('h:i A') }}</div>
                                            </td>
                                            <td>
                                                <strong>{{ $inquiry->name }}</strong>
                                            </td>
                                            <td>
                                                <div><a href="tel:{{ $inquiry->phone }}" style="color: var(--text); text-decoration: none;"><i class="bi bi-telephone-fill me-1" style="color: var(--primary);"></i> {{ $inquiry->phone }}</a></div>
                                                <div class="muted" style="font-size: 0.82rem;"><a href="mailto:{{ $inquiry->email }}" style="color: var(--muted); text-decoration: none;"><i class="bi bi-envelope me-1"></i> {{ $inquiry->email }}</a></div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $inquiry->subject }}</span>
                                            </td>
                                            <td style="max-width: 260px;">
                                                <div style="font-size: 0.88rem; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    {{ $inquiry->message }}
                                                </div>
                                            </td>
                                            <td>
                                                @if ($inquiry->status === 'pending')
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                @elseif ($inquiry->status === 'contacted')
                                                    <span class="badge bg-info text-dark">Contacted</span>
                                                @elseif ($inquiry->status === 'resolved')
                                                    <span class="badge bg-success">Resolved</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-1">
                                                    <a href="{{ route('admin.inquiries.show', $inquiry->id) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 12px;">
                                                        <i class="bi bi-eye me-1"></i> View
                                                    </a>
                                                    <form method="POST" action="{{ route('admin.inquiries.destroy', $inquiry->id) }}" data-confirm="Are you sure you want to permanently delete inquiry #{{ $inquiry->id }} from {{ $inquiry->name }}?" data-confirm-title="Delete Inquiry" data-confirm-btn="Yes, Delete">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" style="font-size: 12px;">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $inquiries->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: var(--muted);"></i>
                            <h4 class="mt-3">No Inquiries Found</h4>
                            <p class="muted">Messages sent from the Contact Us page will automatically appear here.</p>
                        </div>
                    @endif
                </section>
            </div>
        </main>
    </div>
@endsection
