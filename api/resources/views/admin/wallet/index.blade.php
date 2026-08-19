@extends('admin.layout')

@section('title', 'Customer Wallet & Loyalty Rewards')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <!-- Top Banner -->
                <div class="admin-banner">
                    <div>
                        <div class="brand">Customer Loyalty & Growth</div>
                        <h2>Customer Wallet & Loyalty Rewards</h2>
                        <p class="lead" style="margin-top: 4px;">Configure welcome sign-up credits, post-purchase non-return cashbacks (% or flat ₹), and manage customer wallet balances.</p>
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

                <!-- Metric Stat Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="p-3" style="background: #fff; border: 1px solid var(--border-color, #e5e7eb); border-radius: 8px;">
                            <span class="text-muted d-block" style="font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Active Wallet Circulation</span>
                            <div class="d-flex align-items-center justify-content-between mt-2">
                                <h3 class="mb-0" style="color: #0f766e; font-weight: 700;">₹{{ number_format($totalCirculation, 2) }}</h3>
                                <i class="bi bi-wallet2 fs-3 text-muted"></i>
                            </div>
                            <small class="text-muted">Total balance currently held across customer accounts</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3" style="background: #fff; border: 1px solid var(--border-color, #e5e7eb); border-radius: 8px;">
                            <span class="text-muted d-block" style="font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Rewards Issued</span>
                            <div class="d-flex align-items-center justify-content-between mt-2">
                                <h3 class="mb-0" style="color: #1d4ed8; font-weight: 700;">₹{{ number_format($totalEarnedAllTime, 2) }}</h3>
                                <i class="bi bi-gift fs-3 text-muted"></i>
                            </div>
                            <small class="text-muted">Cumulative sign-up bonuses and post-purchase cashbacks</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3" style="background: #fff; border: 1px solid var(--border-color, #e5e7eb); border-radius: 8px;">
                            <span class="text-muted d-block" style="font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Redeemed at Checkout</span>
                            <div class="d-flex align-items-center justify-content-between mt-2">
                                <h3 class="mb-0" style="color: #b45309; font-weight: 700;">₹{{ number_format($totalRedeemedAllTime, 2) }}</h3>
                                <i class="bi bi-bag-check fs-3 text-muted"></i>
                            </div>
                            <small class="text-muted">Total wallet cash spent on shopping orders</small>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Column 1: Settings Form -->
                    <div class="col-lg-5">
                        <section class="admin-section mb-4" style="background: #fff; padding: 1.5rem; border: 1px solid #e5e7eb; border-radius: 8px;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h3 class="mb-0 d-flex align-items-center gap-2">
                                        <i class="bi bi-gear-wide-connected" style="color: #6366f1;"></i>
                                        <span>Wallet & Cashback Rules</span>
                                    </h3>
                                    <p class="muted mb-0" style="font-size: 13px;">Manage bonuses and post-purchase reward percentages or fixed cash amounts.</p>
                                </div>
                                @if ($config['is_enabled'])
                                    <span class="badge bg-success">System Active</span>
                                @else
                                    <span class="badge bg-secondary">Disabled</span>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('admin.wallet.update') }}">
                                @csrf
                                @method('PUT')

                                <!-- Master Toggle -->
                                <div class="mb-3 form-check form-switch p-2 ps-5 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                    <input class="form-check-input" type="checkbox" role="switch" id="wallet_enabled" name="wallet_enabled" value="1" {{ $config['is_enabled'] ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="wallet_enabled">
                                        Enable Customer Wallet & Rewards System
                                    </label>
                                    <div class="form-text mt-0">Allows users to earn, accumulate, and redeem Kanakshi wallet credit at checkout.</div>
                                </div>

                                <hr class="my-3">

                                <!-- Welcome Signup Bonus -->
                                <h5 class="mb-2 fs-6 text-primary fw-bold">1. New Member Sign-up Welcome Bonus</h5>
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="wallet_signup_bonus_enabled" name="wallet_signup_bonus_enabled" value="1" {{ $config['signup_bonus_enabled'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="wallet_signup_bonus_enabled">
                                        Credit bonus automatically on new account registration
                                    </label>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Sign-up Welcome Credit (₹ Flat)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="1" min="0" class="form-control" name="wallet_signup_bonus_amount" value="{{ old('wallet_signup_bonus_amount', $config['signup_bonus_amount']) }}" required>
                                    </div>
                                    <div class="form-text">Credited immediately when customer creates & verifies their account.</div>
                                </div>

                                <hr class="my-3">

                                <!-- Post Purchase Cashback -->
                                <h5 class="mb-2 fs-6 text-primary fw-bold">2. Post-Purchase Non-Return Loyalty Cashback</h5>
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="wallet_order_cashback_enabled" name="wallet_order_cashback_enabled" value="1" {{ $config['order_cashback_enabled'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="wallet_order_cashback_enabled">
                                        Reward customers for keeping their delivered purchases
                                    </label>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold">Reward Calculation Mode</label>
                                        <select class="form-select" name="wallet_order_cashback_type" required>
                                            <option value="percent" {{ $config['order_cashback_type'] === 'percent' ? 'selected' : '' }}>Percentage (%) of Order</option>
                                            <option value="fix" {{ $config['order_cashback_type'] === 'fix' ? 'selected' : '' }}>Fixed Amount (₹ Flat)</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold">Reward Value (% or ₹)</label>
                                        <input type="number" step="0.01" min="0" class="form-control" name="wallet_order_cashback_value" value="{{ old('wallet_order_cashback_value', $config['order_cashback_value']) }}" required>
                                    </div>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold">Min. Order Amount (₹)</label>
                                        <input type="number" step="1" min="0" class="form-control" name="wallet_order_cashback_min_order" value="{{ old('wallet_order_cashback_min_order', $config['order_cashback_min_order']) }}" placeholder="1000">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold">Max Cashback Cap (₹)</label>
                                        <input type="number" step="1" min="0" class="form-control" name="wallet_order_cashback_max_amount" value="{{ old('wallet_order_cashback_max_amount', $config['order_cashback_max_amount']) }}" placeholder="1000">
                                        <div class="form-text" style="font-size: 11px;">Applies only to % mode (0 = no cap).</div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold">Return Window Cooldown</label>
                                        <div class="input-group">
                                            <input type="number" step="1" min="1" max="90" class="form-control" name="wallet_order_cashback_release_days" value="{{ old('wallet_order_cashback_release_days', $config['order_cashback_release_days']) }}" required>
                                            <span class="input-group-text">Days</span>
                                        </div>
                                        <div class="form-text" style="font-size: 11px;">Default 7 days (after return period).</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold">Max Order Wallet Usage</label>
                                        <div class="input-group">
                                            <input type="number" step="1" min="1" max="100" class="form-control" name="wallet_max_redemption_percent" value="{{ old('wallet_max_redemption_percent', $config['max_redemption_percent']) }}" required>
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                                    <i class="bi bi-check2-circle me-1"></i> Save Wallet & Rewards Rules
                                </button>
                            </form>
                        </section>
                    </div>

                    <!-- Column 2: Customer Balances & Adjustment -->
                    <div class="col-lg-7">
                        <section class="admin-section mb-4" style="background: #fff; padding: 1.5rem; border: 1px solid #e5e7eb; border-radius: 8px;">
                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                <div>
                                    <h3 class="mb-0 d-flex align-items-center gap-2">
                                        <i class="bi bi-people" style="color: #0f766e;"></i>
                                        <span>Customer Wallet Directory</span>
                                    </h3>
                                    <p class="muted mb-0" style="font-size: 13px;">Overview of registered customers and their available balances.</p>
                                </div>
                                <form method="GET" action="{{ route('admin.wallet.index') }}" class="d-flex gap-2">
                                    <input type="text" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search customer...">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Search</button>
                                    @if ($search !== '')
                                        <a href="{{ route('admin.wallet.index') }}" class="btn btn-sm btn-outline-danger">Clear</a>
                                    @endif
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light" style="font-size: 13px;">
                                        <tr>
                                            <th>Customer</th>
                                            <th>Contact</th>
                                            <th class="text-end">Balance</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody style="font-size: 13.5px;">
                                        @forelse ($customers as $cust)
                                            <tr>
                                                <td>
                                                    <strong>{{ $cust->name }}</strong>
                                                    <div class="text-muted" style="font-size: 12px;">ID: #{{ $cust->id }}</div>
                                                </td>
                                                <td>
                                                    <div>{{ $cust->email }}</div>
                                                    <small class="text-muted">{{ $cust->phone ?: 'No phone' }}</small>
                                                </td>
                                                <td class="text-end">
                                                    <strong style="color: #0f766e; font-size: 15px;">₹{{ number_format($cust->wallet_balance, 2) }}</strong>
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#adjustModal{{ $cust->id }}">
                                                        <i class="bi bi-arrow-left-right me-1"></i> Adjust
                                                    </button>

                                                    <!-- Adjustment Modal -->
                                                    <div class="modal fade text-start" id="adjustModal{{ $cust->id }}" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <form method="POST" action="{{ route('admin.wallet.adjust') }}">
                                                                    @csrf
                                                                    <input type="hidden" name="user_id" value="{{ $cust->id }}">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Adjust Wallet: {{ $cust->name }}</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="p-2 mb-3 rounded" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                                                                            Current Balance: <strong class="text-success fs-6">₹{{ number_format($cust->wallet_balance, 2) }}</strong>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-semibold">Action</label>
                                                                            <select class="form-select" name="action" required>
                                                                                <option value="credit">Credit Balance (+)</option>
                                                                                <option value="debit">Debit Balance (-)</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-semibold">Amount (₹)</label>
                                                                            <input type="number" step="0.01" min="1" class="form-control" name="amount" required placeholder="e.g. 500">
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-semibold">Reason / Audit Note</label>
                                                                            <input type="text" class="form-control" name="reason" required placeholder="e.g. Goodwill credit / Compensation">
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="btn btn-primary">Confirm Adjustment</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">No customers found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($customers->hasPages())
                                <div class="mt-3">
                                    {{ $customers->links() }}
                                </div>
                            @endif
                        </section>

                        <!-- Recent Transactions -->
                        <section class="admin-section" style="background: #fff; padding: 1.5rem; border: 1px solid #e5e7eb; border-radius: 8px;">
                            <h3 class="mb-2 fs-6 fw-bold">Recent Wallet Audit Activity</h3>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0" style="font-size: 12.5px;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Customer</th>
                                            <th>Source</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentTransactions as $tx)
                                            <tr>
                                                <td>{{ $tx->created_at ? $tx->created_at->format('d M, H:i') : 'N/A' }}</td>
                                                <td><strong>{{ $tx->user?->name ?: 'Customer #' . $tx->user_id }}</strong></td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">{{ str_replace('_', ' ', $tx->source) }}</span>
                                                </td>
                                                <td>
                                                    @if ($tx->type === 'credit')
                                                        <span class="text-success fw-bold">+₹{{ number_format($tx->amount, 2) }}</span>
                                                    @else
                                                        <span class="text-danger fw-bold">-₹{{ number_format($tx->amount, 2) }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($tx->status === 'completed')
                                                        <span class="badge bg-success">Completed</span>
                                                    @elseif ($tx->status === 'pending_clearance')
                                                        <span class="badge bg-warning text-dark">Pending (7d)</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ ucfirst($tx->status) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-3 text-muted">No wallet transactions logged yet.</td>
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
