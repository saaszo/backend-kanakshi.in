@extends('admin.layout')

@section('title', 'Product Reviews')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <div class="admin-banner">
                    <div>
                        <div class="brand">Customer Feedback Desk</div>
                        <h2>Product Reviews</h2>
                        <p class="lead" style="margin-top:8px;">Publish, hide, or remove verified customer feedback without editing customer words.</p>
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

                <div class="admin-overview">
                    <div class="admin-stat">
                        <small>Total Reviews</small>
                        <strong>{{ $stats['total'] }}</strong>
                        <span>All customer submissions</span>
                    </div>
                    <div class="admin-stat">
                        <small>Published</small>
                        <strong>{{ $stats['published'] }}</strong>
                        <span>Visible on storefront</span>
                    </div>
                    <div class="admin-stat">
                        <small>Pending</small>
                        <strong>{{ $stats['pending'] }}</strong>
                        <span>Awaiting moderation</span>
                    </div>
                    <div class="admin-stat">
                        <small>Hidden</small>
                        <strong>{{ $stats['hidden'] }}</strong>
                        <span>Moderated off storefront</span>
                    </div>
                </div>

                <form method="GET" class="admin-section mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" name="q" value="{{ $filters['q'] }}" placeholder="Product, order, customer, comment">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="">All reviews</option>
                                <option value="pending" @selected($filters['status'] === 'pending')>Pending</option>
                                <option value="published" @selected($filters['status'] === 'published')>Published</option>
                                <option value="hidden" @selected($filters['status'] === 'hidden')>Hidden</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="button small w-100" type="submit">Filter Reviews</button>
                        </div>
                    </div>
                </form>

                <div class="table-wrap">
                    <table class="admin-data-table align-middle">
                        <thead>
                            <tr>
                                <th>Review</th>
                                <th>Product</th>
                                <th>Customer</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th style="width: 250px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reviews as $review)
                                @php
                                    $status = $review->is_published ? 'Published' : ($review->moderated_at ? 'Hidden' : 'Pending');
                                    $badgeClass = $review->is_published ? 'success' : ($review->moderated_at ? 'muted' : 'primary');
                                @endphp
                                <tr>
                                    <td style="min-width: 280px;">
                                        <div class="d-grid gap-2">
                                            <strong>{{ str_repeat('★', (int) $review->rating) }}{{ str_repeat('☆', max(0, 5 - (int) $review->rating)) }}</strong>
                                            <div>{{ \Illuminate\Support\Str::limit($review->comment, 150) }}</div>
                                            @if (is_array($review->images) && count($review->images))
                                                <div class="d-flex gap-2 flex-wrap">
                                                    @foreach (array_slice($review->images, 0, 3) as $image)
                                                        <img src="{{ $image }}" alt="Review image" class="admin-upload-preview admin-upload-preview--small" style="margin-top:0;" />
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $review->product?->name ?: 'Unknown product' }}</strong>
                                    </td>
                                    <td>
                                        <div>{{ $review->user?->name ?: 'Customer' }}</div>
                                        <div class="muted">{{ $review->user?->email }}</div>
                                    </td>
                                    <td>
                                        <a href="{{ $review->order ? route('admin.orders.show', $review->order) : '#' }}">
                                            {{ $review->order?->order_number ?: 'N/A' }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="admin-badge compact {{ $badgeClass }}">{{ $status }}</span>
                                    </td>
                                    <td>{{ optional($review->created_at)->format('d M Y, h:i A') }}</td>
                                    <td>
                                        <div class="button-row admin-row-actions">
                                            <form method="POST" action="{{ route('admin.reviews.visibility', $review) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="action" value="{{ $review->is_published ? 'hide' : 'publish' }}">
                                                <button class="button secondary small" type="submit">
                                                    <i class="bi {{ $review->is_published ? 'bi-eye-slash' : 'bi-eye' }}"></i>
                                                    <span>{{ $review->is_published ? 'Hide' : 'Publish' }}</span>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('Delete this review permanently?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="button danger small" type="submit">
                                                    <i class="bi bi-trash3"></i>
                                                    <span>Delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center muted">No reviews found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $reviews->links() }}
                </div>
            </div>
        </main>
    </div>
@endsection
