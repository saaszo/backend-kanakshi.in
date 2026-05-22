@extends('admin.layout')

@section('title', 'Order ' . $order->order_number)

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="dashboard-card">
                <!-- Page Head -->
                <div class="page-head mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <div class="brand">Fulfillment Center</div>
                        <div class="d-flex align-items-center gap-3">
                            <h2 class="mb-0">{{ $order->order_number }}</h2>
                            @php
                                $statusClass = match($order->status) {
                                    'pending' => 'warning',
                                    'confirmed', 'processing' => 'primary',
                                    'shipped' => 'purple',
                                    'delivered' => 'success',
                                    'cancelled', 'refunded' => 'danger',
                                    default => 'muted'
                                };
                            @endphp
                            <span class="admin-badge {{ $statusClass }}" style="font-size: 13px; padding: 4px 12px; border-radius: 99px;">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                        <p class="lead mb-0" style="margin-top: 8px;">Received on {{ $order->created_at->format('M d, Y') }} at {{ $order->created_at->format('h:i A') }}</p>
                    </div>
                    <div class="toolbar-actions">
                        <a href="{{ route('admin.orders.index') }}" class="button secondary small">
                            <i class="bi bi-arrow-left"></i>
                            <span>Back to Orders</span>
                        </a>
                    </div>
                </div>

                <!-- Session Feedback -->
                @if (session('status'))
                    <div class="message mb-4" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #34d399; padding: 15px; border-radius: var(--radius-md); font-weight: 500;">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                    </div>
                @endif

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="message mb-4" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #f87171; padding: 15px; border-radius: var(--radius-md); font-weight: 500;">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Main Columns Split -->
                <div class="row g-4">
                    <!-- Left Column: Receipts, Totals, Customer particulars -->
                    <div class="col-lg-8">
                        <div class="d-flex flex-column gap-4">
                            
                            <!-- Items List -->
                            <section class="panel">
                                <h3 class="mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-bag-check" style="color: var(--primary);"></i>
                                    <span>Product Receipt</span>
                                </h3>
                                <div class="table-wrap">
                                    <table class="admin-data-table align-middle">
                                        <thead>
                                            <tr>
                                                <th>Product Info</th>
                                                <th class="text-center">SKU / Code</th>
                                                <th class="text-center">Quantity</th>
                                                <th class="text-end">Item Price</th>
                                                <th class="text-end">Line Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($order->items as $item)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div style="width: 50px; height: 50px; border-radius: var(--radius-md); background: rgba(255,255,255,0.03); border: 1px solid var(--border); overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                                                @if ($item->image)
                                                                    <img src="{{ $item->image }}" alt="{{ $item->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                                @elseif ($item->product && count(is_array($item->product->images) ? $item->product->images : []) > 0)
                                                                    <img src="{{ $item->product->images[0] }}" alt="{{ $item->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                                @else
                                                                    <i class="bi bi-image muted" style="font-size: 18px;"></i>
                                                                @endif
                                                            </div>
                                                            <div>
                                                                <div style="font-weight: 700; color: #fff;">{{ $item->name }}</div>
                                                                
                                                                <!-- Details: variant, size, color -->
                                                                <div class="d-flex flex-wrap gap-2 mt-1">
                                                                    @if ($item->size)
                                                                        <span class="admin-badge muted compact" style="font-size: 9px; padding: 1px 5px;">Size: {{ $item->size }}</span>
                                                                    @endif
                                                                    @if ($item->color)
                                                                        <span class="admin-badge muted compact" style="font-size: 9px; padding: 1px 5px;">Color: {{ $item->color }}</span>
                                                                    @endif
                                                                    @if ($item->variant_details)
                                                                        <span class="admin-badge muted compact" style="font-size: 9px; padding: 1px 5px;">{{ $item->variant_details }}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center muted" style="font-size: 13px;">
                                                        <div>{{ $item->sku ?: 'N/A' }}</div>
                                                        @if($item->hsn_code)
                                                            <div style="font-size: 10px;">HSN: {{ $item->hsn_code }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="text-center" style="font-weight: 600;">
                                                        {{ $item->quantity }}
                                                    </td>
                                                    <td class="text-end font-monospace">
                                                        ₹{{ number_format($item->price, 2) }}
                                                        @if($item->gst_percent > 0)
                                                            <div style="font-size: 10px;" class="muted">Inc. {{ number_format($item->gst_percent, 1) }}% GST</div>
                                                        @endif
                                                    </td>
                                                    <td class="text-end font-monospace" style="font-weight: 700; color: #fff;">
                                                        ₹{{ number_format($item->line_total, 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </section>

                            <!-- Financial Breakdown -->
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <section class="panel h-100">
                                        <h3 class="mb-3 d-flex align-items-center gap-2">
                                            <i class="bi bi-cash-stack" style="color: var(--success);"></i>
                                            <span>Financial Receipt</span>
                                        </h3>
                                        <div class="d-flex flex-column gap-2" style="font-size: 14px;">
                                            <div class="d-flex justify-content-between">
                                                <span class="muted">Subtotal:</span>
                                                <span class="font-monospace">₹{{ number_format($order->subtotal, 2) }}</span>
                                            </div>
                                            @if($order->discount > 0)
                                                <div class="d-flex justify-content-between text-danger">
                                                    <span>Discount:</span>
                                                    <span class="font-monospace">-₹{{ number_format($order->discount, 2) }}</span>
                                                </div>
                                            @endif
                                            <div class="d-flex justify-content-between">
                                                <span class="muted">Tax & GST:</span>
                                                <span class="font-monospace">₹{{ number_format($order->tax, 2) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="muted">Shipping & Handling:</span>
                                                <span class="font-monospace">₹{{ number_format($order->shipping_cost, 2) }}</span>
                                            </div>
                                            <hr style="border-top: 1px solid var(--border); margin: 12px 0;">
                                            <div class="d-flex justify-content-between" style="font-size: 16px; font-weight: 800; color: #fff;">
                                                <span>Grand Total:</span>
                                                <span class="font-monospace" style="color: var(--success);">₹{{ number_format($order->total_amount, 2) }}</span>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                                <div class="col-md-6">
                                    <section class="panel h-100">
                                        <h3 class="mb-3 d-flex align-items-center gap-2">
                                            <i class="bi bi-credit-card-2-front" style="color: var(--purple);"></i>
                                            <span>Payment Coordinates</span>
                                        </h3>
                                        <div class="d-flex flex-column gap-2" style="font-size: 14px;">
                                            <div class="d-flex justify-content-between">
                                                <span class="muted">Payment Gateway:</span>
                                                <span style="font-weight: 700; text-transform: uppercase;">{{ $order->payment_method }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="muted">Payment Status:</span>
                                                <span class="admin-badge compact {{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'pending' ? 'warning' : 'danger') }}" style="font-size: 11px;">
                                                    {{ ucfirst($order->payment_status) }}
                                                </span>
                                            </div>
                                            @if($order->payment_id)
                                                <div class="d-flex justify-content-between">
                                                    <span class="muted">Transaction reference ID:</span>
                                                    <span class="font-monospace" style="font-size: 12px; word-break: break-all;">{{ $order->payment_id }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </section>
                                </div>
                            </div>

                            <!-- Customer Information -->
                            <section class="panel">
                                <h3 class="mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-person-bounding-box" style="color: var(--primary);"></i>
                                    <span>Customer & Shipping particulars</span>
                                </h3>
                                <div class="row g-3" style="font-size: 14px;">
                                    <div class="col-md-6">
                                        <div class="mb-2"><strong class="muted">Ship To Name:</strong> <span class="ms-2" style="color:#fff;">{{ $order->ship_name }}</span></div>
                                        <div class="mb-2"><strong class="muted">Ship Email:</strong> <span class="ms-2">{{ $order->ship_email }}</span></div>
                                        <div class="mb-2"><strong class="muted">Ship Phone:</strong> <span class="ms-2">{{ $order->ship_phone }}</span></div>
                                        @if($order->user_id)
                                            <div class="mt-3">
                                                <span class="admin-badge primary" style="font-size: 11px;">
                                                    <i class="bi bi-person-check-fill me-1"></i> Authenticated Customer (ID: {{ $order->user_id }})
                                                </span>
                                            </div>
                                        @else
                                            <div class="mt-3">
                                                <span class="admin-badge muted" style="font-size: 11px;">
                                                    <i class="bi bi-person-dash-fill me-1"></i> Guest Checkout Order
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <div style="background: rgba(0, 0, 0, 0.2); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 15px;">
                                            <div class="mb-2" style="font-weight: 700; color: #fff;"><i class="bi bi-geo-alt me-1"></i> Delivery Address</div>
                                            <div style="line-height: 1.6; color: var(--text-soft);">
                                                {!! nl2br(e($order->ship_address)) !!}<br>
                                                {{ $order->ship_city }}, {{ $order->ship_state }} - <strong>{{ $order->ship_pincode }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if ($order->notes)
                                        <div class="col-12 mt-2">
                                            <div style="background: rgba(245, 158, 11, 0.05); border: 1px solid rgba(245, 158, 11, 0.15); border-radius: var(--radius-md); padding: 15px;">
                                                <div class="mb-1" style="font-weight: 700; color: var(--warning);"><i class="bi bi-sticky me-1"></i> Customer Order Notes:</div>
                                                <div class="muted" style="font-style: italic;">"{{ $order->notes }}"</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </section>

                        </div>
                    </div>

                    <!-- Right Column: Operations controls, timeline tracker -->
                    <div class="col-lg-4">
                        <div class="d-flex flex-column gap-4">

                            <!-- Operational Action Box -->
                            <section class="panel">
                                <h3 class="mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-sliders" style="color: var(--primary);"></i>
                                    <span>Fulfillment & Payment controls</span>
                                </h3>
                                <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="field mb-3">
                                        <label for="status">Order Fulfillment Status</label>
                                        <select name="status" id="status" class="form-select">
                                            <option value="pending" @selected($order->status === 'pending')>Pending Verification</option>
                                            <option value="confirmed" @selected($order->status === 'confirmed')>Confirmed / Ready to Pack</option>
                                            <option value="processing" @selected($order->status === 'processing')>Processing / Picking</option>
                                            <option value="shipped" @selected($order->status === 'shipped')>Shipped / Dispatched</option>
                                            <option value="delivered" @selected($order->status === 'delivered')>Delivered Successfully</option>
                                            <option value="cancelled" @selected($order->status === 'cancelled')>Cancelled</option>
                                            <option value="refunded" @selected($order->status === 'refunded')>Refunded</option>
                                        </select>
                                    </div>

                                    <div class="field mb-3">
                                        <label for="payment_status">Payment Status</label>
                                        <select name="payment_status" id="payment_status" class="form-select">
                                            <option value="pending" @selected($order->payment_status === 'pending')>Pending</option>
                                            <option value="paid" @selected($order->payment_status === 'paid')>Paid / Captured</option>
                                            <option value="failed" @selected($order->payment_status === 'failed')>Failed</option>
                                            <option value="refunded" @selected($order->payment_status === 'refunded')>Refunded</option>
                                        </select>
                                    </div>

                                    <button class="button small w-100" type="submit">
                                        <i class="bi bi-save me-1"></i> Update Status Toggles
                                    </button>
                                </form>
                            </section>

                            <!-- Logistics & Courier Assigner -->
                            <section class="panel">
                                <h3 class="mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-truck" style="color: var(--purple);"></i>
                                    <span>Logistics & Courier details</span>
                                </h3>
                                
                                @if($order->tracking_number)
                                    <div style="background: rgba(99, 102, 241, 0.05); border: 1px solid rgba(99, 102, 241, 0.12); border-radius: var(--radius-md); padding: 15px; margin-bottom: 20px;">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <strong style="color: #fff;"><i class="bi bi-info-circle me-1"></i> Active Courier</strong>
                                            <span class="admin-badge primary compact" style="font-size: 10px;">Assigned</span>
                                        </div>
                                        <div class="mb-1" style="font-size: 13px;"><span class="muted">Tracking Code:</span> <code style="color: var(--primary); font-weight: 700;">{{ $order->tracking_number }}</code></div>
                                        @if($order->tracking_url)
                                            <a href="{{ $order->tracking_url }}" target="_blank" rel="noreferrer" class="button secondary small w-100 mt-2 text-center" style="font-size: 12px; display: inline-flex; justify-content: center; align-items: center; gap: 6px;">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                                <span>Track Package (Customer link)</span>
                                            </a>
                                        @endif
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('admin.orders.update-tracking', $order) }}">
                                    @csrf
                                    @method('PUT')

                                    <div class="field mb-3">
                                        <label for="courier_name">Courier Provider Name</label>
                                        <input type="text" name="courier_name" id="courier_name" placeholder="e.g. Delhivery, Blue Dart, FedEx" value="{{ str_contains(strtolower($order->tracking_number ?? ''), 'delhivery') ? 'Delhivery' : (str_contains(strtolower($order->tracking_number ?? ''), 'blue') ? 'Blue Dart' : '') }}" />
                                    </div>

                                    <div class="field mb-3">
                                        <label for="tracking_number">Logistics Tracking Number</label>
                                        <input type="text" name="tracking_number" id="tracking_number" placeholder="Enter tracking code number" value="{{ $order->tracking_number }}" required />
                                    </div>

                                    <div class="field mb-3">
                                        <label for="tracking_url">Custom Tracking URL (Optional)</label>
                                        <input type="url" name="tracking_url" id="tracking_url" placeholder="https://tracking.link/etc" value="{{ $order->tracking_url }}" />
                                        <p class="muted mt-1" style="font-size: 11px;">If left blank, Delhivery and Blue Dart tracking links resolve automatically.</p>
                                    </div>

                                    <button class="button small w-100" type="submit">
                                        <i class="bi bi-cursor me-1"></i> Assign Courier Logistics
                                    </button>
                                </form>
                            </section>

                            <!-- Milestone Tracker Stepper -->
                            <section class="panel">
                                <h3 class="mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-clock-history" style="color: var(--primary);"></i>
                                    <span>Milestones & Logs</span>
                                </h3>

                                <div class="admin-timeline mb-4" style="position: relative; padding-left: 24px; margin-top: 10px;">
                                    <div class="timeline-line" style="position: absolute; left: 7px; top: 0; bottom: 0; width: 2px; background: var(--border);"></div>
                                    
                                    @forelse($order->trackingUpdates as $index => $tracking)
                                        <div class="timeline-item mb-4" style="position: relative;">
                                            <div class="timeline-marker" style="position: absolute; left: -22px; top: 3px; width: 12px; height: 12px; border-radius: 50%; background: {{ $index === 0 ? 'var(--primary)' : 'var(--border-strong)' }}; box-shadow: {{ $index === 0 ? '0 0 10px var(--primary)' : 'none' }}; border: 2px solid var(--bg);"></div>
                                            <div>
                                                <div style="font-weight: 700; color: #fff; font-size: 14px;">
                                                    {{ $tracking->status }}
                                                </div>
                                                <div style="font-size: 11px; color: var(--text-soft);" class="d-flex align-items-center gap-2 mt-1">
                                                    <span class="d-flex align-items-center gap-1"><i class="bi bi-geo-alt"></i> {{ $tracking->location ?: 'In Transit' }}</span>
                                                    <span>•</span>
                                                    <span>{{ $tracking->created_at->format('M d, h:i A') }}</span>
                                                </div>
                                                @if($tracking->message)
                                                    <p class="muted mb-0 mt-2" style="font-size: 12px; line-height: 1.5; background: rgba(0,0,0,0.1); border-radius: var(--radius-md); padding: 8px 10px; border-left: 2px solid var(--border-strong);">
                                                        {{ $tracking->message }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <p class="muted py-2">No chronological tracking milestones registered yet.</p>
                                    @endforelse
                                </div>

                                <hr style="border-top: 1px solid var(--border); margin: 20px 0;">

                                <!-- Add Manual Milestone Log Form -->
                                <div class="milestone-form-container">
                                    <h4 class="mb-3" style="font-size: 14px; font-weight: 700; color: #fff;"><i class="bi bi-plus-circle me-1" style="color: var(--primary);"></i> Append Milestone Event</h4>
                                    
                                    <form method="POST" action="{{ route('admin.orders.add-tracking-log', $order) }}">
                                        @csrf
                                        <div class="field mb-3">
                                            <label for="timeline_status">Event Title *</label>
                                            <input type="text" name="status" id="timeline_status" placeholder="e.g. Package sorted, Out for Delivery" required />
                                        </div>

                                        <div class="field mb-3">
                                            <label for="timeline_location">Event Location</label>
                                            <input type="text" name="location" id="timeline_location" placeholder="e.g. Delhi sorting center, Mumbai Hub" />
                                        </div>

                                        <div class="field mb-3">
                                            <label for="timeline_message">Milestone Description</label>
                                            <textarea name="message" id="timeline_message" placeholder="Details of transit status..." style="min-height: 70px; font-size: 13px;"></textarea>
                                        </div>

                                        <button class="button secondary small w-100" type="submit">
                                            <i class="bi bi-plus-lg me-1"></i> Append Transit Log
                                        </button>
                                    </form>
                                </div>
                            </section>

                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
@endsection
