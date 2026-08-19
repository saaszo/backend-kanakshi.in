@extends('admin.layout')

@section('title', 'Product Reviews & Ratings')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <div class="admin-banner">
                    <div>
                        <div class="brand">Customer Feedback Desk</div>
                        <h2>Product Reviews & Ratings</h2>
                        <p class="lead" style="margin-top: 4px;">Moderate verified customer feedback, manage star ratings, and approve testimonials.</p>
                    </div>
                </div>

                @if (session('status'))
                    <div class="p-3 mb-4" style="background: #e8f7ee; border: 1px solid #c2ebd1; color: #0d532b; font-weight: 600;">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                    </div>
                @endif

                <div class="metrics-grid mb-4">
                    <div class="admin-stat">
                        <small>Total Reviews</small>
                        <strong>{{ $stats['total'] }}</strong>
                        <span>Customer submissions</span>
                    </div>
                    <div class="admin-stat">
                        <small>Published</small>
                        <strong style="color: #16a34a;">{{ $stats['published'] }}</strong>
                        <span>Live on product pages</span>
                    </div>
                    <div class="admin-stat">
                        <small>Pending Moderation</small>
                        <strong style="color: #d97706;">{{ $stats['pending'] }}</strong>
                        <span>Awaiting approval</span>
                    </div>
                    <div class="admin-stat">
                        <small>Hidden / Rejected</small>
                        <strong style="color: #dc2626;">{{ $stats['hidden'] }}</strong>
                        <span>Removed from store</span>
                    </div>
                </div>

                <section class="admin-section mb-4">
                    <form method="GET" action="{{ route('admin.reviews.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Search Reviews</label>
                            <input type="text" class="form-control" name="q" value="{{ $filters['q'] }}" placeholder="Product, customer name, review text..." />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Filter Status</label>
                            <select class="form-select" name="status">
                                <option value="">All Reviews</option>
                                <option value="pending" @selected($filters['status'] === 'pending')>Pending Moderation</option>
                                <option value="published" @selected($filters['status'] === 'published')>Published</option>
                                <option value="hidden" @selected($filters['status'] === 'hidden')>Hidden</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary w-100" type="submit">Filter Feedback</button>
                        </div>
                    </form>
                </section>

                <section class="admin-section">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Product & Customer</th>
                                    <th class="text-center">Rating</th>
                                    <th>Review Content</th>
                                    <th class="text-center">Status</th>
                                    <th>Date</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($reviews as $review)
                                    <tr>
                                        <td>
                                            <div style="font-weight: 700; color: #0f172a;">{{ $review->product?->name ?: 'Fine Jewellery Piece' }}</div>
                                            <div style="font-size: 12px; color: #64748b;">
                                                <i class="bi bi-person me-1"></i> {{ $review->customer_name }} ({{ $review->customer_email }})
                                            </div>
                                        </td>
                                        <td class="text-center" style="white-space: nowrap;">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i class="bi {{ $i <= $review->rating ? 'bi-star-fill text-warning' : 'bi-star text-muted' }}" style="font-size: 12px;"></i>
                                            @endfor
                                        </td>
                                        <td style="max-width: 320px; font-size: 13px;">
                                            @if ($review->title)
                                                <div style="font-weight: 700; color: #0f172a;">{{ $review->title }}</div>
                                            @endif
                                            <div style="color: #334155;">{{ $review->body }}</div>
                                        </td>
                                        <td class="text-center">
                                            @if ($review->is_published)
                                                <span class="badge bg-success">Published</span>
                                            @elseif ($review->status === 'hidden')
                                                <span class="badge bg-danger">Hidden</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @endif
                                        </td>
                                        <td style="font-size: 12px; color: #64748b;">
                                            {{ $review->created_at->format('M d, Y') }}
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                @if (!$review->is_published)
                                                    <form method="POST" action="{{ route('admin.reviews.update', $review) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="is_published" value="1" />
                                                        <button class="btn btn-sm btn-success py-1 px-2" style="font-size: 11px;" type="submit" title="Publish">
                                                            <i class="bi bi-check-lg"></i> Approve
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('admin.reviews.update', $review) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="is_published" value="0" />
                                                        <button class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 11px;" type="submit" title="Hide">
                                                            Hide
                                                        </button>
                                                    </form>
                                                @endif

                                                <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" data-confirm="Are you sure you want to permanently delete this customer review for '{{ $review->product->title ?? 'Product' }}'?" data-confirm-title="Delete Review" data-confirm-btn="Delete Review">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger py-1 px-2" style="font-size: 11px;" type="submit" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4" style="color: #64748b;">
                                            No product reviews found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($reviews->hasPages())
                        <div class="mt-4">
                            {{ $reviews->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </section>
            </div>
        </main>
    </div>
@endsection
