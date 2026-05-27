@extends('admin.layout')

@section('title', 'Live Auctions')

@section('content')
    @php
        $liveCount = $auctions->where('status', 'live')->count();
        $draftCount = $auctions->where('status', 'draft')->count();
        $endedCount = $auctions->where('status', 'ended')->count();
        $totalBids = $auctions->sum('total_bids');
    @endphp

    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="dashboard-card">
                <div class="page-head">
                    <div>
                        <div class="brand">Auction Control</div>
                        <h2>Live Auctions</h2>
                        <p class="lead" style="margin-top:8px;">Manage upcoming drops, watch bidding activity, and close auctions without leaving the main dashboard flow.</p>
                    </div>
                    <div class="toolbar-actions">
                        <a href="{{ route('admin.products.index') }}" class="button secondary small">
                            <i class="bi bi-box-seam"></i>
                            <span>Products</span>
                        </a>
                        <a href="{{ route('admin.auctions.create') }}" class="button small">
                            <i class="bi bi-plus-lg"></i>
                            <span>Create Auction</span>
                        </a>
                    </div>
                </div>

                @if(session('status'))
                    <div class="message">{{ session('status') }}</div>
                @endif

                @if(session('error'))
                    <div class="errors">{{ session('error') }}</div>
                @endif

                <div class="metrics-grid">
                    <article class="metric-card">
                        <small>Live Now</small>
                        <strong>{{ $liveCount }}</strong>
                        <span>Auctions currently accepting bids</span>
                    </article>
                    <article class="metric-card">
                        <small>Scheduled</small>
                        <strong>{{ $draftCount }}</strong>
                        <span>Draft or upcoming lots</span>
                    </article>
                    <article class="metric-card">
                        <small>Closed</small>
                        <strong>{{ $endedCount }}</strong>
                        <span>Ended events with winners locked</span>
                    </article>
                    <article class="metric-card">
                        <small>Total Bids</small>
                        <strong>{{ $totalBids }}</strong>
                        <span>All participant activity across auctions</span>
                    </article>
                </div>

                <section class="panel">
                    <div class="admin-toolbar">
                        <div>
                            <h3>Auction List</h3>
                            <p class="muted">Each row keeps product context, bid momentum, and the controls you need for quick moderation.</p>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table class="admin-data-table">
                            <thead>
                                <tr>
                                    <th>Auction</th>
                                    <th>Status</th>
                                    <th>Price Snapshot</th>
                                    <th>Activity</th>
                                    <th>Window</th>
                                    <th style="width: 250px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($auctions as $auction)
                                    @php
                                        $currentBid = $auction->currentHighestBid();
                                        $statusTone = match ($auction->status) {
                                            'live' => 'success',
                                            'draft' => 'warning',
                                            'ended' => 'muted',
                                            'cancelled' => 'danger',
                                            default => 'muted',
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="admin-product-meta" style="min-width: 240px;">
                                                <strong>#{{ $auction->id }} {{ $auction->title }}</strong>
                                                <span>{{ $auction->product?->name ?: 'Standalone auction item' }}</span>
                                                <span>
                                                    @if($auction->winner)
                                                        Winner: {{ $auction->winner->name }}
                                                    @else
                                                        Created {{ $auction->created_at?->format('d M Y') }}
                                                    @endif
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="admin-status-stack">
                                                <span class="admin-badge {{ $statusTone }}">{{ ucfirst($auction->status) }}</span>
                                                <span class="muted">
                                                    @if($auction->status === 'live')
                                                        Next bid: ₹{{ number_format($auction->minimumNextBid(), 2) }}
                                                    @elseif($auction->status === 'ended' && $auction->winning_bid)
                                                        Winning bid: ₹{{ number_format($auction->winning_bid, 2) }}
                                                    @else
                                                        Increment: ₹{{ number_format($auction->min_bid_increment, 2) }}
                                                    @endif
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="admin-status-stack">
                                                <span>Start: ₹{{ number_format($auction->start_price, 2) }}</span>
                                                <span>Current: ₹{{ number_format($currentBid, 2) }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="admin-status-stack">
                                                <span>{{ $auction->total_bids }} bid(s)</span>
                                                <span>{{ $auction->total_participants }} participant(s)</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="admin-status-stack">
                                                <span>{{ $auction->start_at?->format('d M Y, h:i A') }}</span>
                                                <span>to {{ $auction->end_at?->format('d M Y, h:i A') }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="button-row admin-row-actions">
                                                <a href="{{ route('admin.auctions.bids', $auction) }}" class="button secondary small">
                                                    <i class="bi bi-list-ol"></i>
                                                    <span>Bids</span>
                                                </a>
                                                @if(!in_array($auction->status, ['ended','cancelled']))
                                                    <a href="{{ route('admin.auctions.edit', $auction) }}" class="button secondary small">
                                                        <i class="bi bi-pencil"></i>
                                                        <span>Edit</span>
                                                    </a>
                                                    <form method="POST" action="{{ route('admin.auctions.end', $auction) }}" onsubmit="return confirm('Are you sure you want to end this auction now?')">
                                                        @csrf
                                                        <button type="submit" class="button small">
                                                            <i class="bi bi-stop-circle"></i>
                                                            <span>End</span>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.auctions.cancel', $auction) }}" onsubmit="return confirm('Are you sure you want to cancel this auction?')">
                                                        @csrf
                                                        <button type="submit" class="button danger small">
                                                            <i class="bi bi-x-circle"></i>
                                                            <span>Cancel</span>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="dashboard-empty">No auctions found yet. Start with your first auction and it will appear here.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
@endsection
