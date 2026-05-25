@extends('admin.layout')

@section('title', 'Live Auctions')

@section('content')
@include('admin.partials.sidebar')

<div class="dashboard-shell">
    <div class="page-head">
        <div>
            <h1 class="page-title">Live Auctions</h1>
            <p class="page-subtitle">Manage all auctions — create, monitor bids, end or cancel.</p>
        </div>
        <div class="page-head-actions">
            <a href="{{ route('admin.auctions.create') }}" class="button">
                <i class="bi bi-plus-lg"></i>
                <span>Create Auction</span>
            </a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="dashboard-card">
        <div class="table-responsive">
            <table class="admin-table table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Start Price</th>
                        <th>Current Bid</th>
                        <th>Bids</th>
                        <th>Participants</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auctions as $auction)
                        <tr>
                            <td class="text-muted small">{{ $auction->id }}</td>
                            <td>
                                <strong>{{ $auction->title }}</strong>
                                @if($auction->product)
                                    <br><span class="text-muted small">{{ $auction->product->name ?? '' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($auction->status === 'live')
                                    <span class="pill pill-success">Live</span>
                                @elseif($auction->status === 'draft')
                                    <span class="pill pill-warning">Draft</span>
                                @elseif($auction->status === 'ended')
                                    <span class="pill pill-secondary">Ended</span>
                                @elseif($auction->status === 'cancelled')
                                    <span class="pill pill-danger">Cancelled</span>
                                @endif
                            </td>
                            <td>₹{{ number_format($auction->start_price, 2) }}</td>
                            <td>₹{{ number_format($auction->currentHighestBid(), 2) }}</td>
                            <td>{{ $auction->total_bids }}</td>
                            <td>{{ $auction->total_participants }}</td>
                            <td class="small">{{ $auction->start_at?->format('d M Y, h:i A') }}</td>
                            <td class="small">{{ $auction->end_at?->format('d M Y, h:i A') }}</td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <a href="{{ route('admin.auctions.bids', $auction) }}" class="button small">
                                        <i class="bi bi-list-ol"></i> Bids
                                    </a>
                                    @if(!in_array($auction->status, ['ended','cancelled']))
                                        <a href="{{ route('admin.auctions.edit', $auction) }}" class="button small secondary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.auctions.end', $auction) }}"
                                              onsubmit="return confirm('Are you sure you want to end this auction now?')">
                                            @csrf
                                            <button type="submit" class="button small secondary">
                                                <i class="bi bi-stop-circle"></i> End Now
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.auctions.cancel', $auction) }}"
                                              onsubmit="return confirm('Are you sure you want to cancel this auction?')">
                                            @csrf
                                            <button type="submit" class="button small" style="background:var(--color-danger,#dc3545);border-color:var(--color-danger,#dc3545)">
                                                <i class="bi bi-x-circle"></i> Cancel
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">No auctions found. <a href="{{ route('admin.auctions.create') }}">Create your first auction.</a></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
