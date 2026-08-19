@extends('admin.layout')

@section('title', 'Return Request ' . $returnRequest->return_number)

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <!-- Top Header Banner -->
                <div class="admin-banner">
                    <div>
                        <div class="brand">Returns Desk • Case {{ $returnRequest->return_number }}</div>
                        <div class="d-flex align-items-center gap-3 mt-1">
                            <h2 class="mb-0">{{ $returnRequest->return_number }}</h2>
                            @php
                                $badgeClass = match($returnRequest->status) {
                                    'requested' => 'bg-warning text-dark',
                                    'approved' => 'bg-primary',
                                    'received' => 'bg-info text-dark',
                                    'refunded' => 'bg-success',
                                    'rejected' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}" style="font-size: 13px; text-transform: uppercase;">
                                {{ ucfirst($returnRequest->status) }}
                            </span>
                        </div>
                        <p class="lead mb-0" style="margin-top: 4px;">
                            Linked Order: <a href="{{ route('admin.orders.show', $returnRequest->order) }}" style="color: #2563eb; font-weight: 700;">{{ $returnRequest->order->order_number }}</a>
                            • Created {{ optional($returnRequest->requested_at ?: $returnRequest->created_at)->format('d M Y, h:i A') }}
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('admin.returns.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Back to Returns
                        </a>
                    </div>
                </div>

                @if (session('status'))
                    <div class="p-3 mb-4" style="background: #e8f7ee; border: 1px solid #c2ebd1; color: #0d532b; font-weight: 600;">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                    </div>
                @endif

                <div class="row g-4">
                    <!-- Left Column: Items & Customer Details -->
                    <div class="col-lg-7">
                        <!-- Requested Return Items Card -->
                        <section class="admin-section mb-4">
                            <h3 class="mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-box-seam" style="color: #2563eb;"></i>
                                <span>Items in Return Request</span>
                            </h3>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Item Details</th>
                                            <th>SKU</th>
                                            <th class="text-center">Return Qty</th>
                                            <th class="text-end">Price</th>
                                            <th class="text-end">Line Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach (($returnRequest->requested_items ?? []) as $item)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        @if (!empty($item['image']))
                                                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" style="width: 48px; height: 48px; border: 1px solid var(--border); object-fit: cover;">
                                                        @endif
                                                        <div>
                                                            <strong style="color: #0f172a; display: block;">{{ $item['name'] }}</strong>
                                                            @if(!empty($item['size']))
                                                                <small class="text-muted">Size: {{ $item['size'] }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="font-monospace" style="font-size: 12px; color: #64748b;">
                                                    {{ $item['sku'] ?? 'N/A' }}
                                                </td>
                                                <td class="text-center font-monospace" style="font-weight: 700; color: #0f172a;">
                                                    {{ $item['quantity'] }}
                                                </td>
                                                <td class="text-end font-monospace" style="color: #0f172a;">
                                                    ₹{{ number_format((float) ($item['price'] ?? 0), 2) }}
                                                </td>
                                                <td class="text-end font-monospace" style="font-weight: 700; color: #16a34a;">
                                                    ₹{{ number_format(((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 1)), 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Customer Reason & Notes Card -->
                        <section class="admin-section mb-4">
                            <h3 class="mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-chat-left-quote" style="color: #f59e0b;"></i>
                                <span>Customer Return Reason &amp; Refund Choice</span>
                            </h3>
                            <div class="p-3 mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                                <div class="mb-2">
                                    <strong style="font-size: 13px; color: #64748b;">Primary Reason:</strong>
                                    <span class="badge bg-secondary ms-2" style="font-size: 13px;">{{ $returnRequest->reason }}</span>
                                </div>
                                @if (!empty($returnRequest->reason_detail))
                                    <div class="mb-2">
                                        <strong style="font-size: 13px; color: #64748b;">Specific Concern:</strong>
                                        <span class="text-dark ms-2" style="font-size: 13.5px; font-weight: 600;">{{ $returnRequest->reason_detail }}</span>
                                    </div>
                                @endif
                                <div class="mb-2">
                                    <strong style="font-size: 13px; color: #64748b;">Refund Destination Requested:</strong>
                                    @if (($returnRequest->refund_mode ?? 'wallet') === 'wallet')
                                        <span class="badge bg-warning text-dark ms-2" style="font-size: 12px; font-weight: 700;">
                                            <i class="bi bi-wallet2 me-1"></i> Kanakshi Cash Wallet (Instant Shopping Credit)
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark border ms-2" style="font-size: 12px;">
                                            <i class="bi bi-bank me-1"></i> Original Payment Method / Bank
                                        </span>
                                    @endif
                                </div>
                                <div style="font-size: 13.5px; color: #334155; line-height: 1.6; padding-top: 8px; border-top: 1px dashed #cbd5e1;">
                                    <strong>Customer Notes:</strong> {{ $returnRequest->customer_notes ?: 'No additional notes provided.' }}
                                </div>
                            </div>

                            @if (!empty($returnRequest->images) && is_array($returnRequest->images))
                                <div class="mt-3">
                                    <strong style="font-size: 13px; color: #64748b; display: block; margin-bottom: 8px;">Uploaded Photo Proof:</strong>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($returnRequest->images as $img)
                                            <a href="{{ $img }}" target="_blank" rel="noreferrer">
                                                <img src="{{ $img }}" alt="Return proof" style="width: 80px; height: 80px; object-fit: cover; border: 1px solid #cbd5e1; border-radius: 6px;">
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </section>

                        <!-- Linked Customer & Delivery Details -->
                        <section class="admin-section">
                            <h3 class="mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-person-badge" style="color: #0f172a;"></i>
                                <span>Customer & Pickup Address</span>
                            </h3>
                            <div class="row g-3" style="font-size: 13.5px;">
                                <div class="col-sm-6">
                                    <div class="text-muted" style="font-size: 11.5px; text-transform: uppercase; font-weight: 700;">Customer Contact</div>
                                    <div style="font-weight: 700; color: #0f172a; margin-top: 2px;">{{ $returnRequest->order->ship_name }}</div>
                                    <div>{{ $returnRequest->order->ship_email }}</div>
                                    <div>{{ $returnRequest->order->ship_phone }}</div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="text-muted" style="font-size: 11.5px; text-transform: uppercase; font-weight: 700;">Reverse Pickup Address</div>
                                    <div style="color: #0f172a; margin-top: 2px;">
                                        {!! nl2br(e($returnRequest->order->ship_address)) !!}<br>
                                        {{ $returnRequest->order->ship_city }}, {{ $returnRequest->order->ship_state }} - {{ $returnRequest->order->ship_pincode }}
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- Right Column: Resolution Controls -->
                    <div class="col-lg-5">
                        <section class="admin-section">
                            <h3 class="mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-sliders" style="color: #16a34a;"></i>
                                <span>Resolution Cockpit</span>
                            </h3>

                            <form method="POST" action="{{ route('admin.returns.update', $returnRequest) }}" data-confirm="Save resolution update for return request {{ $returnRequest->return_number }}?" data-confirm-title="Confirm Return Resolution" data-confirm-btn="Update Return" data-confirm-class="btn-primary">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600; font-size: 13px;">Return Status</label>
                                    <select class="form-select" name="status" id="return_status_select">
                                        <option value="requested" @selected($returnRequest->status === 'requested')>Requested (Under Verification)</option>
                                        <option value="approved" @selected($returnRequest->status === 'approved')>Approved (Pickup Authorized)</option>
                                        <option value="received" @selected($returnRequest->status === 'received')>Received & Inspected (Auto-Restock)</option>
                                        <option value="refunded" @selected($returnRequest->status === 'refunded')>Refunded (Closed & Settled)</option>
                                        <option value="rejected" @selected($returnRequest->status === 'rejected')>Rejected (Ineligible)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600; font-size: 13px;">Approved Refund Value (₹)</label>
                                    <input type="number" step="0.01" min="0" name="approved_amount" class="form-control font-monospace" value="{{ old('approved_amount', $returnRequest->approved_amount ?: $returnRequest->requested_amount) }}">
                                    <div class="form-text" style="font-size: 11px;">Requested by customer: ₹{{ number_format($returnRequest->requested_amount, 2) }}</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600; font-size: 13px;">Refund Processing Method</label>
                                    <select class="form-select" name="refund_mode">
                                        <option value="wallet" @selected(($returnRequest->refund_mode ?? 'wallet') === 'wallet')>
                                            Kanakshi Cash Wallet (Instant Shopping Balance Credit)
                                        </option>
                                        <option value="original_payment" @selected(($returnRequest->refund_mode ?? '') === 'original_payment')>
                                            Original Payment Method / Bank Transfer
                                        </option>
                                    </select>
                                    <div class="form-text" style="font-size: 11px;">
                                        When set to 'Refunded', choosing Wallet instantly credits ₹{{ number_format($returnRequest->approved_amount ?: $returnRequest->requested_amount, 2) }} to the customer's account so they can buy another piece immediately.
                                    </div>
                                </div>

                                @if ($returnRequest->refund_processed_at)
                                    <div class="p-2 mb-3" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; font-size: 12px; color: #166534;">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        <strong>Refund Settled:</strong> ₹{{ number_format($returnRequest->approved_amount, 2) }} was processed via <strong>{{ strtoupper($returnRequest->refund_mode) }}</strong> on {{ $returnRequest->refund_processed_at->format('d M Y, h:i A') }}.
                                    </div>
                                @endif

                                @php
                                    $linkedUser = $returnRequest->user ?? $returnRequest->order->user;
                                @endphp
                                @if ($linkedUser)
                                    <div class="p-2 mb-3" style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: 6px; font-size: 12px; color: #92400e;">
                                        <i class="bi bi-wallet2 me-1"></i>
                                        <strong>Customer Wallet Balance:</strong> ₹{{ number_format($linkedUser->wallet_balance, 2) }}
                                    </div>
                                @endif

                                <!-- Reverse Pickup Logistics -->
                                <div class="p-3 mb-3" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-truck text-success"></i>
                                        <strong style="font-size: 13px; color: #166534;">Reverse Pickup Logistics</strong>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <label class="form-label" style="font-size: 12px; font-weight: 600; color: #1e293b;">Pickup Courier Partner</label>
                                        <select name="pickup_courier_name" class="form-select form-select-sm mb-1" onchange="if (this.value) document.getElementById('pickup_courier_input').value = this.value;">
                                            <option value="">Choose Courier Partner...</option>
                                            <option value="Delhivery Reverse" @selected(stripos($returnRequest->pickup_courier_name ?? '', 'delhivery') !== false)>Delhivery Reverse Logistics</option>
                                            <option value="BlueDart Apex" @selected(stripos($returnRequest->pickup_courier_name ?? '', 'blue') !== false)>BlueDart Express</option>
                                            <option value="Shadowfax Reverse" @selected(stripos($returnRequest->pickup_courier_name ?? '', 'shadowfax') !== false)>Shadowfax Reverse</option>
                                            <option value="Ecom Express Reverse" @selected(stripos($returnRequest->pickup_courier_name ?? '', 'ecom') !== false)>Ecom Express Reverse</option>
                                            <option value="DTDC Courier" @selected(stripos($returnRequest->pickup_courier_name ?? '', 'dtdc') !== false)>DTDC Courier</option>
                                            <option value="Xpressbees" @selected(stripos($returnRequest->pickup_courier_name ?? '', 'xpressbee') !== false)>Xpressbees</option>
                                        </select>
                                        <input type="text" name="pickup_courier_name" id="pickup_courier_input" class="form-control form-control-sm" placeholder="e.g. Delhivery Reverse" value="{{ old('pickup_courier_name', $returnRequest->pickup_courier_name) }}">
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label" style="font-size: 12px; font-weight: 600; color: #1e293b;">Pickup Tracking / AWB No.</label>
                                        <input type="text" name="pickup_tracking_number" class="form-control form-control-sm font-monospace" placeholder="e.g. DELH-RET-987654" value="{{ old('pickup_tracking_number', $returnRequest->pickup_tracking_number) }}">
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label" style="font-size: 12px; font-weight: 600; color: #1e293b;">Pickup Scheduled Date</label>
                                        <input type="date" name="pickup_scheduled_date" class="form-control form-control-sm" value="{{ old('pickup_scheduled_date', optional($returnRequest->pickup_scheduled_date)->format('Y-m-d')) }}">
                                    </div>

                                    <div class="mb-1">
                                        <label class="form-label" style="font-size: 12px; font-weight: 600; color: #1e293b;">Custom Tracking URL (Optional)</label>
                                        <input type="url" name="pickup_tracking_url" class="form-control form-control-sm" placeholder="Auto-generated for standard couriers" value="{{ old('pickup_tracking_url', $returnRequest->pickup_tracking_url) }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" style="font-weight: 600; font-size: 13px;">Admin Internal Notes</label>
                                    <textarea name="admin_notes" rows="3" class="form-control" placeholder="Add inspection observations, pickup tracking #, or refund transaction details...">{{ old('admin_notes', $returnRequest->admin_notes) }}</textarea>
                                </div>

                                <!-- Status Summary Box -->
                                <div class="p-3 mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0; font-size: 13px;">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">Stock Restored:</span>
                                        <strong>
                                            @if ($returnRequest->stock_restored_at)
                                                <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Yes ({{ $returnRequest->stock_restored_at->format('d M Y, h:i A') }})</span>
                                            @else
                                                <span class="text-muted">No (Auto-restocks on 'Received' / 'Refunded')</span>
                                            @endif
                                        </strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">Original Payment:</span>
                                        <span class="font-monospace">{{ strtoupper($returnRequest->order->payment_method) }} ({{ ucfirst($returnRequest->order->payment_status) }})</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Case Resolved:</span>
                                        <span>{{ $returnRequest->resolved_at ? $returnRequest->resolved_at->format('d M Y, h:i A') : 'Pending' }}</span>
                                    </div>
                                </div>

                                <button class="btn btn-primary w-100" type="submit">
                                    <i class="bi bi-check-lg me-1"></i> Save Resolution
                                </button>
                            </form>
                        </section>
                    </div>
                </div>
            </div>
        </main>
    </div>
@endsection
