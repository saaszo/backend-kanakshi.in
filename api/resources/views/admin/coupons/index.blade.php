@extends('admin.layout')

@section('title', 'Coupons & Promotional Offers')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <div class="admin-banner">
                    <div>
                        <div class="brand">Marketing</div>
                        <h2>Coupons & Promotional Offers</h2>
                        <p class="lead" style="margin-top: 4px;">Create checkout coupon codes, flat festive discounts, and minimum cart tier vouchers.</p>
                    </div>
                </div>

                @if (session('status'))
                    <div class="p-3 mb-4" style="background: #e8f7ee; border: 1px solid #c2ebd1; color: #0d532b; font-weight: 600;">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                    </div>
                @endif

                <div class="row g-4">
                    <!-- Left: Create Offer Form -->
                    <div class="col-lg-4">
                        <section class="admin-section h-100">
                            <h3 class="mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-tag" style="color: #2563eb;"></i>
                                <span>Create New Coupon</span>
                            </h3>

                            <form method="POST" action="{{ route('admin.coupons.store') }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600; font-size: 13px;">Offer Title *</label>
                                    <input type="text" name="title" class="form-control" placeholder="e.g. Festive Sparkle ₹500 OFF" required />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600; font-size: 13px;">Coupon Code *</label>
                                    <input type="text" name="code" class="form-control" placeholder="SPARKLE500" style="text-transform: uppercase; font-weight: 700;" required />
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label" style="font-weight: 600; font-size: 13px;">Type</label>
                                        <select name="type" class="form-select">
                                            <option value="flat">Flat Amount (₹)</option>
                                            <option value="percent">Percentage (%)</option>
                                            <option value="free_shipping">Free Shipping</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" style="font-weight: 600; font-size: 13px;">Value *</label>
                                        <input type="number" name="value" step="0.01" min="0" class="form-control" placeholder="500" required />
                                    </div>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label" style="font-weight: 600; font-size: 13px;">Min Order (₹)</label>
                                        <input type="number" name="min_order_amount" step="0.01" min="0" class="form-control" placeholder="2499" />
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" style="font-weight: 600; font-size: 13px;">Badge Text</label>
                                        <input type="text" name="badge_text" class="form-control" placeholder="₹500 OFF" />
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600; font-size: 13px;">Description</label>
                                    <textarea name="description" class="form-control" rows="2" placeholder="Terms and conditions..."></textarea>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" checked>
                                    <label class="form-check-label" for="is_active" style="font-size: 13px; font-weight: 600;">
                                        Active & Redeemable
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="show_on_cart" value="1" id="show_on_cart" checked>
                                    <label class="form-check-label" for="show_on_cart" style="font-size: 13px; font-weight: 600;">
                                        Show on Cart Page Sheet
                                    </label>
                                </div>

                                <button class="btn btn-primary w-100" type="submit">
                                    <i class="bi bi-plus-lg me-1"></i> Create Coupon
                                </button>
                            </form>
                        </section>
                    </div>

                    <!-- Right: Existing Coupons Table -->
                    <div class="col-lg-8">
                        <section class="admin-section h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h3 class="mb-0">Active Coupon Codes</h3>
                                    <p class="muted mb-0" style="font-size: 13px;">{{ count($coupons) }} coupons available in database.</p>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Code & Offer</th>
                                            <th>Benefit</th>
                                            <th>Min Order</th>
                                            <th class="text-center">Redeemed</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($coupons as $coupon)
                                            <tr>
                                                <td>
                                                    <div style="font-weight: 800; font-family: monospace; font-size: 14px; color: #2563eb;">
                                                        {{ $coupon->code }}
                                                    </div>
                                                    <div style="font-weight: 600; color: #0f172a; font-size: 12.5px;">{{ $coupon->title }}</div>
                                                    @if($coupon->badge_text)
                                                        <span class="badge bg-secondary" style="font-size: 9.5px;">{{ $coupon->badge_text }}</span>
                                                    @endif
                                                </td>
                                                <td style="font-weight: 700; color: #16a34a;">
                                                    @if($coupon->type === 'percent')
                                                        {{ (int)$coupon->value }}% OFF
                                                    @elseif($coupon->type === 'flat')
                                                        ₹{{ number_format($coupon->value, 0) }} OFF
                                                    @else
                                                        Free Shipping
                                                    @endif
                                                </td>
                                                <td style="color: #64748b; font-size: 13px;">
                                                    @if($coupon->min_order_amount > 0)
                                                        ₹{{ number_format($coupon->min_order_amount, 0) }}
                                                    @else
                                                        No Minimum
                                                    @endif
                                                </td>
                                                <td class="text-center font-monospace" style="font-weight: 600;">
                                                    {{ $coupon->used_count }} / {{ $coupon->usage_limit ?: '∞' }}
                                                </td>
                                                <td class="text-center">
                                                    @if($coupon->is_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary">Disabled</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" data-confirm="Are you sure you want to delete coupon code {{ $coupon->code }}?" data-confirm-title="Delete Coupon" data-confirm-btn="Delete Coupon">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" style="font-size: 12px;">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4" style="color: #64748b;">
                                                    No coupon codes created yet.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                </div>

            </div>
        </main>
    </div>
@endsection
