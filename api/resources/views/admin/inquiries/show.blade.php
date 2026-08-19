@extends('admin.layout')

@section('title', 'Inquiry Details #' . $inquiry->id)

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <div class="admin-banner">
                    <div>
                        <div class="brand">Customer Care</div>
                        <h2>Inquiry Details #{{ $inquiry->id }}</h2>
                        <p class="lead" style="margin-top:8px;">Received on {{ \Carbon\Carbon::parse($inquiry->created_at)->format('d M Y, h:i A') }}</p>
                    </div>
                    <div>
                        <a href="{{ route('admin.inquiries.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Back to Inquiries
                        </a>
                    </div>
                </div>

                @if (session('status'))
                    <div class="message mb-4" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #34d399; padding: 15px; border-radius: var(--radius-md); font-weight: 500;">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                    </div>
                @endif

                <div class="row g-4">
                    <div class="col-lg-8">
                        <section class="admin-section mb-4">
                            <h3 class="mb-3">Message Content</h3>
                            <div class="p-3 mb-3" style="background: var(--bg-alt); border-radius: var(--radius-md); border: 1px solid var(--border);">
                                <div class="mb-2"><strong>Topic / Subject:</strong> <span class="badge bg-primary ms-1">{{ $inquiry->subject }}</span></div>
                                <hr style="opacity: 0.15;" />
                                <div style="font-size: 1rem; line-height: 1.7; white-space: pre-wrap; color: var(--text);">{{ $inquiry->message }}</div>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $inquiry->phone) }}?text=Hello%20{{ urlencode($inquiry->name) }}%2C%20thank%20you%20for%20reaching%20out%20to%20Kanakshi%20Fine%20Jewellery%20regarding%20{{ urlencode($inquiry->subject) }}." target="_blank" class="btn btn-success">
                                    <i class="bi bi-whatsapp me-1"></i> Reply on WhatsApp
                                </a>
                                <a href="tel:{{ $inquiry->phone }}" class="btn btn-outline-primary">
                                    <i class="bi bi-telephone me-1"></i> Call Customer
                                </a>
                                <a href="mailto:{{ $inquiry->email }}?subject={{ urlencode('Re: ' . $inquiry->subject . ' - Kanakshi Fine Jewellery') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-envelope me-1"></i> Send Email
                                </a>
                            </div>
                        </section>
                    </div>

                    <div class="col-lg-4">
                        <section class="admin-section mb-4">
                            <h3 class="mb-3">Customer Information</h3>
                            <div class="d-flex flex-column gap-2" style="font-size: 0.9rem;">
                                <div><strong>Name:</strong> {{ $inquiry->name }}</div>
                                <div><strong>Email:</strong> <a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></div>
                                <div><strong>Phone:</strong> <a href="tel:{{ $inquiry->phone }}">{{ $inquiry->phone }}</a></div>
                                <div><strong>IP Address:</strong> {{ $inquiry->ip_address ?? 'N/A' }}</div>
                            </div>
                        </section>

                        <section class="admin-section">
                            <h3 class="mb-3">Update Status</h3>
                            <form method="POST" action="{{ route('admin.inquiries.update-status', $inquiry->id) }}">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600;">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="pending" {{ $inquiry->status === 'pending' ? 'selected' : '' }}>Pending Response</option>
                                        <option value="contacted" {{ $inquiry->status === 'contacted' ? 'selected' : '' }}>Contacted / In Progress</option>
                                        <option value="resolved" {{ $inquiry->status === 'resolved' ? 'selected' : '' }}>Resolved / Closed</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600;">Internal Admin Notes</label>
                                    <textarea name="admin_notes" rows="3" class="form-control" placeholder="Add private notes regarding this customer inquiry...">{{ $inquiry->admin_notes }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    Save Status & Notes
                                </button>
                            </form>
                        </section>
                    </div>
                </div>
            </div>
        </main>
    </div>
@endsection
