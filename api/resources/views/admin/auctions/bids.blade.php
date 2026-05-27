@extends('admin.layout')

@section('title', 'Bids — ' . $auction->title)

@section('content')
    @php
        $statusTone = match ($auction->status) {
            'live' => 'success',
            'draft' => 'warning',
            'ended' => 'muted',
            'cancelled' => 'danger',
            default => 'muted',
        };
    @endphp

    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="dashboard-card">
                <div class="page-head">
                    <div>
                        <div class="brand">Auction Activity</div>
                        <h2>Bids — {{ $auction->title }}</h2>
                        <p class="lead" style="margin-top:8px;">Total {{ $auction->total_bids }} bid(s), {{ $auction->total_participants }} participant(s), current status <span class="admin-badge {{ $statusTone }}">{{ ucfirst($auction->status) }}</span>.</p>
                    </div>
                    <a href="{{ route('admin.auctions.index') }}" class="button secondary small">
                        <i class="bi bi-arrow-left"></i>
                        <span>Back</span>
                    </a>
                </div>

                <section class="panel">
                    @if($bids->isEmpty())
                        <div class="dashboard-empty">No bids placed yet for this auction.</div>
                    @else
                        <div class="table-wrap">
                            <table class="admin-data-table">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Bidder</th>
                                        <th>Bid Amount</th>
                                        <th>Status</th>
                                        <th>Placed At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bids as $index => $bid)
                                        <tr>
                                            <td>#{{ $index + 1 }}</td>
                                            <td>
                                                <div class="admin-product-meta" style="min-width: 220px;">
                                                    <strong>{{ $bid->user?->name ?? 'Guest User' }}</strong>
                                                    <span>{{ $bid->user?->email ?? 'No email available' }}</span>
                                                </div>
                                            </td>
                                            <td>₹{{ number_format($bid->amount, 2) }}</td>
                                            <td>
                                                <span class="admin-badge {{ $bid->is_winning ? 'success' : 'muted' }}">
                                                    {{ $bid->is_winning ? 'Winning' : 'Outbid' }}
                                                </span>
                                            </td>
                                            <td>{{ $bid->created_at->format('d M Y, h:i:s A') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            </div>
        </main>
    </div>
@endsection
