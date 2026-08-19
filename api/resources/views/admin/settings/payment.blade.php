@extends('admin.layout')

@section('title', 'Payment Gateways & Prepaid Discount Offers')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <!-- Top Banner -->
                <div class="admin-banner">
                    <div>
                        <div class="brand">Checkout & Payments</div>
                        <h2>Payment Gateways & Prepaid Discount Offers</h2>
                        <p class="lead" style="margin-top: 4px;">Manage Cash on Delivery (COD), Razorpay & PhonePe credentials, and set instant prepaid online order discounts.</p>
                    </div>
                    <div>
                        <a href="{{ route('admin.settings.edit') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Store Settings
                        </a>
                    </div>
                </div>

                @if (session('status'))
                    <div class="p-3 mb-4" style="background: #e8f7ee; border: 1px solid #c2ebd1; color: #0d532b; font-weight: 600;">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="p-3 mb-4" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; font-weight: 600;">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row g-4">
                    <!-- Column 1: Prepaid Discount Offer & COD Settings -->
                    <div class="col-lg-6">
                        <!-- Card 1: Prepaid Order Discount Offer -->
                        <section class="admin-section mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h3 class="mb-0 d-flex align-items-center gap-2">
                                        <i class="bi bi-lightning-charge-fill" style="color: #eab308;"></i>
                                        <span>Prepaid Order Discount Offer</span>
                                    </h3>
                                    <p class="muted mb-0" style="font-size: 13px;">Incentivize customers to pay online via UPI / Cards to reduce RTO.</p>
                                </div>
                                @if (in_array((string)($settings['prepaid_discount_enabled'] ?? '1'), ['1', 'true', 'yes', 'on'], true))
                                    <span class="badge bg-success">Offer Active</span>
                                @else
                                    <span class="badge bg-secondary">Disabled</span>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('admin.payment-settings.prepaid.update') }}" data-confirm="Save prepaid order discount offer settings?" data-confirm-title="Confirm Prepaid Offer Settings" data-confirm-btn="Save Offer" data-confirm-class="btn-primary">
                                @csrf
                                @method('PUT')

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="prepaid_discount_enabled" id="prepaid_discount_enabled" value="1" @checked(in_array((string)($settings['prepaid_discount_enabled'] ?? '1'), ['1', 'true', 'yes', 'on'], true))>
                                    <label class="form-check-label" for="prepaid_discount_enabled" style="font-weight: 700; font-size: 13.5px; color: #0f172a;">
                                        Enable Prepaid / Online Payment Discount
                                    </label>
                                    <div class="form-text" style="font-size: 12px;">When enabled, customers who select Razorpay / PhonePe / UPI get an instant discount on checkout.</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600; font-size: 13px;">Offer Title / Badge Text</label>
                                    <input type="text" name="prepaid_discount_title" class="form-control" value="{{ old('prepaid_discount_title', $settings['prepaid_discount_title'] ?? 'Extra 5% OFF on Online Payment') }}" placeholder="e.g. Extra 5% OFF on Online Payment (UPI/Cards)" required />
                                    <div class="form-text" style="font-size: 11px;">Appears on checkout page near payment options.</div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label" style="font-weight: 600; font-size: 13px;">Discount Type</label>
                                        <select name="prepaid_discount_type" class="form-select">
                                            <option value="percent" @selected(($settings['prepaid_discount_type'] ?? 'percent') === 'percent')>Percentage (%)</option>
                                            <option value="fixed" @selected(($settings['prepaid_discount_type'] ?? '') === 'fixed')>Flat Amount (₹)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" style="font-weight: 600; font-size: 13px;">Discount Value</label>
                                        <input type="number" step="0.01" min="0" name="prepaid_discount_value" class="form-control font-monospace" value="{{ old('prepaid_discount_value', $settings['prepaid_discount_value'] ?? 5) }}" required />
                                        <div class="form-text" style="font-size: 11px;">e.g. 5 for 5%, or 150 for ₹150 flat discount.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" style="font-weight: 600; font-size: 13px;">Min Order Value (₹)</label>
                                        <input type="number" step="0.01" min="0" name="prepaid_discount_min_order" class="form-control font-monospace" value="{{ old('prepaid_discount_min_order', $settings['prepaid_discount_min_order'] ?? 0) }}" />
                                        <div class="form-text" style="font-size: 11px;">0 = Applicable on all orders.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" style="font-weight: 600; font-size: 13px;">Max Discount Cap (₹)</label>
                                        <input type="number" step="0.01" min="0" name="prepaid_discount_max_amount" class="form-control font-monospace" value="{{ old('prepaid_discount_max_amount', $settings['prepaid_discount_max_amount'] ?? 500) }}" />
                                        <div class="form-text" style="font-size: 11px;">0 = No limit cap for percentage discount.</div>
                                    </div>
                                </div>

                                <div class="p-3 mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                    <div style="font-size: 11px; text-transform: uppercase; font-weight: 800; color: #64748b; margin-bottom: 6px;">Customer Checkout Preview:</div>
                                    <div class="d-flex align-items-center gap-2" style="background: #fefce8; border: 1px solid #fef08a; padding: 10px; color: #854d0e; font-weight: 700; font-size: 13px;">
                                        <i class="bi bi-tag-fill" style="color: #ca8a04;"></i>
                                        <span>{{ $settings['prepaid_discount_title'] ?? 'Extra 5% OFF on Online Payment' }}</span>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-check-lg me-1"></i> Save Prepaid Offer Settings
                                </button>
                            </form>
                        </section>

                        <!-- Card 2: Cash on Delivery (COD) Settings -->
                        <section class="admin-section">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h3 class="mb-0 d-flex align-items-center gap-2">
                                        <i class="bi bi-cash-stack" style="color: #16a34a;"></i>
                                        <span>Cash on Delivery (COD)</span>
                                    </h3>
                                    <p class="muted mb-0" style="font-size: 13px;">Control doorstep cash collection and handling fees.</p>
                                </div>
                                @if ($cod->is_active && in_array((string)($settings['cod_enabled'] ?? '1'), ['1', 'true', 'yes', 'on'], true))
                                    <span class="badge bg-success">COD Active</span>
                                @else
                                    <span class="badge bg-secondary">COD Disabled</span>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('admin.payment-settings.cod.update') }}" data-confirm="Save Cash on Delivery settings?" data-confirm-title="Confirm COD Settings" data-confirm-btn="Save COD" data-confirm-class="btn-primary">
                                @csrf
                                @method('PUT')

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="cod_enabled" id="cod_enabled" value="1" @checked($cod->is_active && in_array((string)($settings['cod_enabled'] ?? '1'), ['1', 'true', 'yes', 'on'], true))>
                                    <label class="form-check-label" for="cod_enabled" style="font-weight: 700; font-size: 13.5px; color: #0f172a;">
                                        Accept Cash on Delivery Orders
                                    </label>
                                    <div class="form-text" style="font-size: 12px;">Uncheck to disable COD completely and make the store 100% prepaid.</div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label" style="font-weight: 600; font-size: 13px;">COD Convenience Fee (₹)</label>
                                        <input type="number" step="0.01" min="0" name="cod_fee" class="form-control font-monospace" value="{{ old('cod_fee', $settings['cod_fee'] ?? 0) }}" />
                                        <div class="form-text" style="font-size: 11px;">0 = Free COD. Set e.g. 49 or 99 to charge extra for COD.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" style="font-weight: 600; font-size: 13px;">Max COD Order Limit (₹)</label>
                                        <input type="number" step="0.01" min="0" name="cod_max_order_amount" class="form-control font-monospace" value="{{ old('cod_max_order_amount', $settings['cod_max_order_amount'] ?? 50000) }}" />
                                        <div class="form-text" style="font-size: 11px;">Orders above this limit will require online prepaid payment.</div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-check-lg me-1"></i> Save COD Configuration
                                </button>
                            </form>
                        </section>
                    </div>

                    <!-- Column 2: Payment Gateways (Razorpay & PhonePe) -->
                    <div class="col-lg-6">
                        <!-- Card 3: Razorpay Gateway -->
                        <section class="admin-section mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h3 class="mb-0 d-flex align-items-center gap-2">
                                        <i class="bi bi-credit-card-2-front-fill" style="color: #2563eb;"></i>
                                        <span>Razorpay Gateway</span>
                                    </h3>
                                    <p class="muted mb-0" style="font-size: 13px;">UPI (Google Pay, PhonePe, Paytm), Credit/Debit Cards, NetBanking.</p>
                                </div>
                                <div class="d-flex gap-1">
                                    @if ($razorpay->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Disabled</span>
                                    @endif
                                    @if ($razorpay->is_test_mode)
                                        <span class="badge bg-warning text-dark">Test Mode</span>
                                    @else
                                        <span class="badge bg-primary">Live Mode</span>
                                    @endif
                                </div>
                            </div>

                            <form method="POST" action="{{ route('admin.payment-settings.gateway.update', 'razorpay') }}" data-confirm="Update Razorpay gateway credentials?" data-confirm-title="Confirm Razorpay Update" data-confirm-btn="Save Razorpay" data-confirm-class="btn-primary">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600; font-size: 13px;">Display Name</label>
                                    <input type="text" name="display_name" class="form-control" value="{{ old('display_name', $razorpay->display_name ?: 'Razorpay Secure (UPI, Cards, NetBanking)') }}" required />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600; font-size: 13px;">Razorpay Key ID (Public Key)</label>
                                    <input type="text" name="public_key" class="form-control font-monospace" value="{{ old('public_key', $razorpay->public_key) }}" placeholder="rzp_live_xxxxxxxx or rzp_test_xxxxxxxx" />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600; font-size: 13px;">Razorpay Key Secret</label>
                                    <input type="password" name="secret_key" class="form-control font-monospace" value="{{ old('secret_key', $razorpay->secret_key) }}" placeholder="••••••••••••••••" />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600; font-size: 13px;">Webhook Secret (Optional)</label>
                                    <input type="password" name="webhook_secret" class="form-control font-monospace" value="{{ old('webhook_secret', $razorpay->webhook_secret) }}" placeholder="Webhook signing secret" />
                                    <div class="form-text" style="font-size: 11px;">Webhook endpoint: <code>{{ url('/api/v1/checkout/webhooks/razorpay') }}</code></div>
                                </div>

                                <div class="d-flex gap-4 mb-3 pt-2" style="border-top: 1px solid var(--border);">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="razorpay_active" value="1" @checked($razorpay->is_active)>
                                        <label class="form-check-label" for="razorpay_active" style="font-weight: 600; font-size: 13px;">
                                            Active on Storefront
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_test_mode" id="razorpay_test" value="1" @checked($razorpay->is_test_mode)>
                                        <label class="form-check-label" for="razorpay_test" style="font-weight: 600; font-size: 13px;">
                                            Test Mode / Sandbox
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-shield-check me-1"></i> Save Razorpay Credentials
                                </button>
                            </form>
                        </section>

                        <!-- Card 4: PhonePe Gateway -->
                        <section class="admin-section">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h3 class="mb-0 d-flex align-items-center gap-2">
                                        <i class="bi bi-phone" style="color: #7c3aed;"></i>
                                        <span>PhonePe Payment Gateway</span>
                                    </h3>
                                    <p class="muted mb-0" style="font-size: 13px;">Direct PhonePe checkout session integration.</p>
                                </div>
                                <div class="d-flex gap-1">
                                    @if ($phonepe->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Disabled</span>
                                    @endif
                                </div>
                            </div>

                            <form method="POST" action="{{ route('admin.payment-settings.gateway.update', 'phonepe') }}" data-confirm="Update PhonePe gateway credentials?" data-confirm-title="Confirm PhonePe Update" data-confirm-btn="Save PhonePe" data-confirm-class="btn-primary">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600; font-size: 13px;">Display Name</label>
                                    <input type="text" name="display_name" class="form-control" value="{{ old('display_name', $phonepe->display_name ?: 'PhonePe PG (UPI, QR, Cards)') }}" required />
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label" style="font-weight: 600; font-size: 13px;">Merchant ID</label>
                                        <input type="text" name="merchant_id" class="form-control font-monospace" value="{{ old('merchant_id', $phonepe->merchant_id) }}" placeholder="PGTESTPAYUAT" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" style="font-weight: 600; font-size: 13px;">Client ID (Public Key)</label>
                                        <input type="text" name="public_key" class="form-control font-monospace" value="{{ old('public_key', $phonepe->public_key) }}" />
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600; font-size: 13px;">Client Secret (Salt Key)</label>
                                    <input type="password" name="secret_key" class="form-control font-monospace" value="{{ old('secret_key', $phonepe->secret_key) }}" placeholder="••••••••••••••••" />
                                </div>

                                <div class="d-flex gap-4 mb-3 pt-2" style="border-top: 1px solid var(--border);">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="phonepe_active" value="1" @checked($phonepe->is_active)>
                                        <label class="form-check-label" for="phonepe_active" style="font-weight: 600; font-size: 13px;">
                                            Active on Storefront
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_test_mode" id="phonepe_test" value="1" @checked($phonepe->is_test_mode)>
                                        <label class="form-check-label" for="phonepe_test" style="font-weight: 600; font-size: 13px;">
                                            Test Mode / Sandbox
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-shield-check me-1"></i> Save PhonePe Credentials
                                </button>
                            </form>
                        </section>
                    </div>
                </div>
            </div>
        </main>
    </div>
@endsection
