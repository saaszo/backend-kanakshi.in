@extends('admin.layout')

@section('title', 'Create Auction')

@section('content')
@include('admin.partials.sidebar')

<div class="dashboard-shell">
    <div class="page-head">
        <div>
            <h1 class="page-title">Create Auction</h1>
            <p class="page-subtitle">Set up a new live auction event.</p>
        </div>
        <div class="page-head-actions">
            <a href="{{ route('admin.auctions.index') }}" class="button secondary">
                <i class="bi bi-arrow-left"></i> Back to Auctions
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="dashboard-card">
        <form method="POST" action="{{ route('admin.auctions.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold" for="title">Auction Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                           id="title" name="title" value="{{ old('title') }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="product_id">Linked Product</label>
                    <select class="form-select @error('product_id') is-invalid @enderror" id="product_id" name="product_id">
                        <option value="">— No linked product —</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="image_url">Image URL</label>
                    <input type="url" class="form-control @error('image_url') is-invalid @enderror"
                           id="image_url" name="image_url" value="{{ old('image_url') }}" placeholder="https://...">
                    @error('image_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="start_price">Start Price (₹) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" class="form-control @error('start_price') is-invalid @enderror"
                           id="start_price" name="start_price" value="{{ old('start_price', 0) }}" required>
                    @error('start_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="reserve_price">Reserve Price (₹)</label>
                    <input type="number" step="0.01" min="0" class="form-control @error('reserve_price') is-invalid @enderror"
                           id="reserve_price" name="reserve_price" value="{{ old('reserve_price') }}" placeholder="Optional">
                    @error('reserve_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="min_bid_increment">Min Bid Increment (₹) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="1" class="form-control @error('min_bid_increment') is-invalid @enderror"
                           id="min_bid_increment" name="min_bid_increment" value="{{ old('min_bid_increment', 50) }}" required>
                    @error('min_bid_increment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="start_at">Start Date & Time <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control @error('start_at') is-invalid @enderror"
                           id="start_at" name="start_at" value="{{ old('start_at') }}" required>
                    @error('start_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="end_at">End Date & Time <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control @error('end_at') is-invalid @enderror"
                           id="end_at" name="end_at" value="{{ old('end_at') }}" required>
                    @error('end_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold" for="description">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="4"
                              placeholder="Describe the auction item, condition, terms...">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="button">
                    <i class="bi bi-gavel"></i> Create Auction
                </button>
                <a href="{{ route('admin.auctions.index') }}" class="button secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
