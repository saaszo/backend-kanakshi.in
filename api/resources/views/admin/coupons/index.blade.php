@extends('admin.layout')

@section('title', 'Coupons & Offers')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="dashboard-card">
                <div class="page-head">
                    <div>
                        <div class="brand">Marketing</div>
                        <h2>Coupons & Offers</h2>
                        <p class="lead" style="margin-top:8px;">Create visible cart offers, promo codes, and festive discount campaigns from one place.</p>
                    </div>
                </div>

                @if (session('status'))
                    <div class="message">{{ session('status') }}</div>
                @endif

                <div class="section-grid admin-split-layout">
                    <section class="panel">
                        <h3>Create Offer</h3>
                        <form method="POST" action="{{ route('admin.coupons.store') }}" class="section-grid">
                            @csrf
                            <div class="form-grid">
                                <div class="field"><label>Offer Title</label><input name="title" /></div>
                                <div class="field"><label>Coupon Code</label><input name="code" /></div>
                                <div class="field">
                                    <label>Type</label>
                                    <select name="type">
                                        <option value="percent">Percent</option>
                                        <option value="flat">Flat</option>
                                        <option value="free_shipping">Free Shipping</option>
                                    </select>
                                </div>
                                <div class="field"><label>Value</label><input name="value" type="number" step="0.01" min="0" /></div>
                                <div class="field"><label>Minimum Order</label><input name="min_order_amount" type="number" step="0.01" min="0" /></div>
                                <div class="field"><label>Badge Text</label><input name="badge_text" placeholder="10% OFF" /></div>
                                <div class="field"><label>Starts At</label><input name="starts_at" type="datetime-local" /></div>
                                <div class="field"><label>Ends At</label><input name="ends_at" type="datetime-local" /></div>
                                <div class="field"><label>Usage Limit</label><input name="usage_limit" type="number" min="1" /></div>
                                <div class="field"><label>Sort Order</label><input name="sort_order" type="number" value="0" /></div>
                            </div>
                            <div class="field"><label>Description</label><textarea name="description"></textarea></div>
                            <div class="button-row">
                                <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" checked> <span>Active</span></label>
                                <label class="checkbox-row"><input type="checkbox" name="show_on_cart" value="1" checked> <span>Show on cart page</span></label>
                                <button class="button small" type="submit">Create Offer</button>
                            </div>
                        </form>
                    </section>

                    <section class="panel">
                        <h3>Existing Offers</h3>
                        <div class="stack-list">
                            @forelse ($coupons as $coupon)
                                <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" class="stack-card">
                                    @csrf
                                    @method('PUT')
                                    <div class="form-grid">
                                        <div class="field"><label>Title</label><input name="title" value="{{ $coupon->title }}" /></div>
                                        <div class="field"><label>Code</label><input name="code" value="{{ $coupon->code }}" /></div>
                                        <div class="field">
                                            <label>Type</label>
                                            <select name="type">
                                                <option value="percent" @selected($coupon->type === 'percent')>Percent</option>
                                                <option value="flat" @selected($coupon->type === 'flat')>Flat</option>
                                                <option value="free_shipping" @selected($coupon->type === 'free_shipping')>Free Shipping</option>
                                            </select>
                                        </div>
                                        <div class="field"><label>Value</label><input name="value" type="number" step="0.01" min="0" value="{{ $coupon->value }}" /></div>
                                        <div class="field"><label>Minimum Order</label><input name="min_order_amount" type="number" step="0.01" min="0" value="{{ $coupon->min_order_amount }}" /></div>
                                        <div class="field"><label>Badge Text</label><input name="badge_text" value="{{ $coupon->badge_text }}" /></div>
                                        <div class="field"><label>Starts At</label><input name="starts_at" type="datetime-local" value="{{ optional($coupon->starts_at)->format('Y-m-d\TH:i') }}" /></div>
                                        <div class="field"><label>Ends At</label><input name="ends_at" type="datetime-local" value="{{ optional($coupon->ends_at)->format('Y-m-d\TH:i') }}" /></div>
                                        <div class="field"><label>Usage Limit</label><input name="usage_limit" type="number" min="1" value="{{ $coupon->usage_limit }}" /></div>
                                        <div class="field"><label>Used Count</label><input name="used_count" type="number" min="0" value="{{ $coupon->used_count }}" /></div>
                                        <div class="field"><label>Sort Order</label><input name="sort_order" type="number" value="{{ $coupon->sort_order }}" /></div>
                                    </div>
                                    <div class="field"><label>Description</label><textarea name="description">{{ $coupon->description }}</textarea></div>
                                    <div class="button-row">
                                        <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked($coupon->is_active)> <span>Active</span></label>
                                        <label class="checkbox-row"><input type="checkbox" name="show_on_cart" value="1" @checked($coupon->show_on_cart)> <span>Show on cart page</span></label>
                                        <button class="button small" type="submit">Save</button>
                                        <button class="button danger small" type="submit" form="coupon-delete-{{ $coupon->id }}">Delete</button>
                                    </div>
                                </form>
                                <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" id="coupon-delete-{{ $coupon->id }}" onsubmit="return confirm('Delete this offer?')">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @empty
                                <p class="muted">No offers created yet.</p>
                            @endforelse
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
@endsection
