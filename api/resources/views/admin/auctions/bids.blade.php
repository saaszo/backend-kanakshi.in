@extends('admin.layout')

@section('title', 'Bids — ' . $auction->title)

@section('content')
@include('admin.partials.sidebar')

<div class="dashboard-shell">
    <div class="page-head">
        <div>
            <h1 class="page-title">Bids — {{ $auction->title }}</h1>
            <p class="page-subtitle">
                Total {{ $auction->total_bids }} bid(s) &bull;
                {{ $auction->total_participants }} participant(s) &bull;
                Status:
                @if($auction->status === 'live')
                    <span class="pill pill-success">Live</span>
                @elseif($auction->status === 'draft')
                    <span class="pill pill-warning">Draft</span>
                @elseif($auction->status === 'ended')
                    <span class="pill pill-secondary">Ended</span>
                @else
                    <span class="pill pill-danger">Cancelled</span>
                @endif
            </p>
        </div>
        <div class="page-head-actions">
            <a href="{{ route('admin.auctions.index') }}" class="button secondary">
                <i class="bi bi-arrow-left"></i> Back to Auctions
            </a>
        </div>
    </div>

    <div class="dashboard-card">
        @if($bids->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-gavel fs-1 d-block mb-2"></i>
                No bids placed yet.
            </div>
        @else
            <div class="table-responsive">
                <table class="admin-table table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Bidder Name</th>
                            <th>Email</th>
                            <th>Bid Amount</th>
                            <th>Status</th>
                            <th>Placed At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bids as $index => $bid)
                            <tr @if($bid->is_winning) style="background:#f0fdf4;" @endif>
                                <td class="fw-bold">#{{ $index + 1 }}</td>
                                <td>{{ $bid->user?->name ?? '—' }}</td>
                                <td class="text-muted small">{{ $bid->user?->email ?? '—' }}</td>
                                <td class="fw-semibold">₹{{ number_format($bid->amount, 2) }}</td>
                                <td>
                                    @if($bid->is_winning)
                                        <span class="pill pill-success">🏆 Winning</span>
                                    @else
                                        <span class="pill pill-secondary">Outbid</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $bid->created_at->format('d M Y, h:i:s A') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
