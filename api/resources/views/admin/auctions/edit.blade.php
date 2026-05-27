@extends('admin.layout')

@section('title', 'Edit Auction — ' . $auction->title)

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="dashboard-card">
                <div class="page-head">
                    <div>
                        <div class="brand">Auction Setup</div>
                        <h2>Edit Auction</h2>
                        <p class="lead" style="margin-top:8px;">Update the schedule, bidding rules, or linked product for {{ $auction->title }}.</p>
                    </div>
                    <a href="{{ route('admin.auctions.index') }}" class="button secondary small">
                        <i class="bi bi-arrow-left"></i>
                        <span>Back</span>
                    </a>
                </div>

                @if($errors->any())
                    <div class="errors">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <section class="panel">
                    <form method="POST" action="{{ route('admin.auctions.update', $auction) }}" class="section-grid">
                        @csrf
                        @method('PUT')

                        <div class="form-grid">
                            <div class="field" style="grid-column: 1 / -1;">
                                <label for="title">Auction Title</label>
                                <input type="text" id="title" name="title" value="{{ old('title', $auction->title) }}" required>
                            </div>
                            <div class="field">
                                <label for="product_id">Linked Product</label>
                                <select id="product_id" name="product_id">
                                    <option value="">No linked product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ old('product_id', $auction->product_id) == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field">
                                <label for="image_url">Image URL</label>
                                <input type="url" id="image_url" name="image_url" value="{{ old('image_url', $auction->image_url) }}" placeholder="https://...">
                            </div>
                            <div class="field">
                                <label for="start_price">Start Price (₹)</label>
                                <input type="number" step="0.01" min="0" id="start_price" name="start_price" value="{{ old('start_price', $auction->start_price) }}" required>
                            </div>
                            <div class="field">
                                <label for="reserve_price">Reserve Price (₹)</label>
                                <input type="number" step="0.01" min="0" id="reserve_price" name="reserve_price" value="{{ old('reserve_price', $auction->reserve_price) }}" placeholder="Optional">
                            </div>
                            <div class="field">
                                <label for="min_bid_increment">Min Bid Increment (₹)</label>
                                <input type="number" step="0.01" min="1" id="min_bid_increment" name="min_bid_increment" value="{{ old('min_bid_increment', $auction->min_bid_increment) }}" required>
                            </div>
                            <div class="field">
                                <label for="start_at">Start Date & Time</label>
                                <input type="datetime-local" id="start_at" name="start_at" value="{{ old('start_at', $auction->start_at?->format('Y-m-d\TH:i')) }}" required>
                            </div>
                            <div class="field">
                                <label for="end_at">End Date & Time</label>
                                <input type="datetime-local" id="end_at" name="end_at" value="{{ old('end_at', $auction->end_at?->format('Y-m-d\TH:i')) }}" required>
                            </div>
                        </div>

                        <div class="field">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="4">{{ old('description', $auction->description) }}</textarea>
                        </div>

                        <div class="button-row">
                            <button type="submit" class="button small">
                                <i class="bi bi-check-lg"></i>
                                <span>Update Auction</span>
                            </button>
                            <a href="{{ route('admin.auctions.index') }}" class="button secondary small">Cancel</a>
                        </div>
                    </form>
                </section>
            </div>
        </main>
    </div>
@endsection
