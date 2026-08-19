@extends('admin.layout')

@section('title', 'Order ' . $order->order_number)

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')
        <main class="admin-main">
            <div class="admin-shell-grid">
                <!-- Page Head -->
                <div class="admin-banner">
                    <div>
                        <div class="brand">Fulfillment Center</div>
                        <div class="d-flex align-items-center gap-3">
                            <h2 class="mb-0">{{ $order->order_number }}</h2>
                            @php
                                $statusBadge = match($order->status) {
                                    'pending' => 'bg-warning text-dark',
                                    'confirmed', 'processing' => 'bg-primary',
                                    'shipped' => 'bg-info text-dark',
                                    'delivered' => 'bg-success',
                                    'cancelled', 'refunded' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $statusBadge }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                        <p class="lead mb-0" style="margin-top: 6px;">Received on {{ $order->created_at->format('M d, Y') }} at {{ $order->created_at->format('h:i A') }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" class="btn btn-primary">
                            <i class="bi bi-printer"></i>
                            <span>Print Invoice</span>
                        </a>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i>
                            <span>Back to Orders</span>
                        </a>
                    </div>
                </div>

                <!-- Session Feedback -->
                @if (session('status'))
                    <div class="p-3 mb-4" style="background: #e8f7ee; border: 1px solid #c2ebd1; color: #0d532b; font-weight: 600;">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                    </div>
                @endif

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="p-3 mb-4" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; font-weight: 600;">
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
                            <section class="admin-section">
                                <h3 class="mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-bag-check" style="color: #2563eb;"></i>
                                    <span>Product Receipt</span>
                                </h3>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
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
                                                            <div style="width: 50px; height: 50px; background: #f8fafc; border: 1px solid var(--border); overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                                                @if ($item->image)
                                                                    <img src="{{ $item->image }}" alt="{{ $item->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                                @elseif ($item->product && count(is_array($item->product->images) ? $item->product->images : []) > 0)
                                                                    <img src="{{ $item->product->images[0] }}" alt="{{ $item->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                                @else
                                                                    <i class="bi bi-image" style="font-size: 18px; color: #94a3b8;"></i>
                                                                @endif
                                                            </div>
                                                            <div>
                                                                <div style="font-weight: 700; color: #0f172a;">{{ $item->name }}</div>
                                                                
                                                                <!-- Details: variant, size, color -->
                                                                <div class="d-flex flex-wrap gap-2 mt-1">
                                                                    @if ($item->size)
                                                                        <span class="badge bg-secondary">Size: {{ $item->size }}</span>
                                                                    @endif
                                                                    @if ($item->color)
                                                                        <span class="badge bg-secondary">Color: {{ $item->color }}</span>
                                                                    @endif
                                                                    @if ($item->variant_details)
                                                                        <span class="badge bg-secondary">{{ $item->variant_details }}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center" style="font-size: 13px; color: #64748b;">
                                                        <div>{{ $item->sku ?: 'N/A' }}</div>
                                                        @if($item->hsn_code)
                                                            <div style="font-size: 10px;">HSN: {{ $item->hsn_code }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="text-center" style="font-weight: 700; color: #0f172a;">
                                                        {{ $item->quantity }}
                                                    </td>
                                                    <td class="text-end font-monospace" style="color: #0f172a;">
                                                        ₹{{ number_format($item->price, 2) }}
                                                        @if($item->gst_percent > 0)
                                                            <div style="font-size: 11px; color: #64748b;">Inc. {{ number_format($item->gst_percent, 1) }}% GST</div>
                                                        @endif
                                                    </td>
                                                    <td class="text-end font-monospace" style="font-weight: 700; color: #16a34a;">
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
                                    <section class="admin-section h-100">
                                        <h3 class="mb-3 d-flex align-items-center gap-2">
                                            <i class="bi bi-cash-stack" style="color: #16a34a;"></i>
                                            <span>Financial Summary</span>
                                        </h3>
                                        <div class="d-flex flex-column gap-2" style="font-size: 14px;">
                                            <div class="d-flex justify-content-between">
                                                <span class="muted">Subtotal:</span>
                                                <span class="font-monospace">₹{{ number_format($order->subtotal, 2) }}</span>
                                            </div>
                                            @if($order->discount > 0)
                                                <div class="d-flex justify-content-between text-danger">
                                                    <span>Coupon Discount:</span>
                                                    <span class="font-monospace">-₹{{ number_format($order->discount, 2) }}</span>
                                                </div>
                                            @endif
                                            @if(($order->prepaid_discount ?? 0) > 0)
                                                <div class="d-flex justify-content-between" style="color: #16a34a; font-weight: 600;">
                                                    <span>Prepaid Online Discount:</span>
                                                    <span class="font-monospace">-₹{{ number_format($order->prepaid_discount, 2) }}</span>
                                                </div>
                                            @endif
                                            <div class="d-flex justify-content-between">
                                                <span class="muted">Tax & GST:</span>
                                                <span class="font-monospace">₹{{ number_format($order->tax, 2) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="muted">Shipping:</span>
                                                <span class="font-monospace">₹{{ number_format($order->shipping_cost, 2) }}</span>
                                            </div>
                                            @if(($order->cod_fee ?? 0) > 0)
                                                <div class="d-flex justify-content-between text-warning" style="color: #c2410c !important;">
                                                    <span>COD Convenience Fee:</span>
                                                    <span class="font-monospace">+₹{{ number_format($order->cod_fee, 2) }}</span>
                                                </div>
                                            @endif
                                            <hr style="border-top: 1px solid var(--border); margin: 12px 0;">
                                            <div class="d-flex justify-content-between" style="font-size: 16px; font-weight: 800; color: #0f172a;">
                                                <span>Grand Total:</span>
                                                <span class="font-monospace" style="color: #16a34a;">₹{{ number_format($order->total_amount, 2) }}</span>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                                <div class="col-md-6">
                                    <section class="admin-section h-100">
                                        <h3 class="mb-3 d-flex align-items-center gap-2">
                                            <i class="bi bi-credit-card-2-front" style="color: #7c3aed;"></i>
                                            <span>Payment Details</span>
                                        </h3>
                                        <div class="d-flex flex-column gap-2" style="font-size: 14px;">
                                            <div class="d-flex justify-content-between">
                                                <span class="muted">Payment Gateway:</span>
                                                <span style="font-weight: 700; text-transform: uppercase;">{{ $order->payment_method }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="muted">Payment Status:</span>
                                                <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success' : ($order->payment_status === 'pending' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                                    {{ ucfirst($order->payment_status) }}
                                                </span>
                                            </div>
                                            @if($order->payment_id)
                                                <div class="d-flex justify-content-between">
                                                    <span class="muted">Transaction Reference:</span>
                                                    <span class="font-monospace" style="font-size: 12px; word-break: break-all;">{{ $order->payment_id }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </section>
                                </div>
                            </div>

                            <!-- Customer Information -->
                            <section class="admin-section">
                                <h3 class="mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-person-bounding-box" style="color: #2563eb;"></i>
                                    <span>Customer & Shipping Details</span>
                                </h3>
                                <div class="row g-3" style="font-size: 14px;">
                                    <div class="col-md-6">
                                        <div class="mb-2"><strong class="muted">Ship To Name:</strong> <span class="ms-2" style="font-weight: 600; color: #0f172a;">{{ $order->ship_name }}</span></div>
                                        <div class="mb-2"><strong class="muted">Ship Email:</strong> <span class="ms-2" style="color: #0f172a;">{{ $order->ship_email }}</span></div>
                                        <div class="mb-2"><strong class="muted">Ship Phone:</strong> <span class="ms-2" style="color: #0f172a;">{{ $order->ship_phone }}</span></div>
                                        @if($order->user_id)
                                            <div class="mt-3">
                                                <span class="badge bg-primary">
                                                    <i class="bi bi-person-check-fill me-1"></i> Registered Customer (ID: {{ $order->user_id }})
                                                </span>
                                            </div>
                                        @else
                                            <div class="mt-3">
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-person-dash-fill me-1"></i> Guest Checkout
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <div style="background: #f8fafc; border: 1px solid var(--border); padding: 15px;">
                                            <div class="mb-2" style="font-weight: 700; color: #0f172a;"><i class="bi bi-geo-alt me-1"></i> Delivery Address</div>
                                            <div style="line-height: 1.6; color: #334155;">
                                                {!! nl2br(e($order->ship_address)) !!}<br>
                                                {{ $order->ship_city }}, {{ $order->ship_state }} - <strong>{{ $order->ship_pincode }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if ($order->notes)
                                        <div class="col-12 mt-2">
                                            <div style="background: #fffbeb; border: 1px solid #fde68a; padding: 15px;">
                                                <div class="mb-1" style="font-weight: 700; color: #b45309;"><i class="bi bi-sticky me-1"></i> Customer Order Notes:</div>
                                                <div style="font-style: italic; color: #78350f;">"{{ $order->notes }}"</div>
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
                            <section class="admin-section">
                                <h3 class="mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-sliders" style="color: #2563eb;"></i>
                                    <span>Status Controls</span>
                                </h3>
                                <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" data-confirm="Update order #{{ $order->order_number }} fulfillment and payment status?" data-confirm-title="Confirm Order Update" data-confirm-btn="Update Status" data-confirm-class="btn-primary">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="mb-3">
                                        <label for="status" class="form-label" style="font-weight: 600; font-size: 13px;">Order Fulfillment Status</label>
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

                                    <div class="mb-3">
                                        <label for="payment_status" class="form-label" style="font-weight: 600; font-size: 13px;">Payment Status</label>
                                        <select name="payment_status" id="payment_status" class="form-select">
                                            <option value="pending" @selected($order->payment_status === 'pending')>Pending</option>
                                            <option value="paid" @selected($order->payment_status === 'paid')>Paid / Captured</option>
                                            <option value="failed" @selected($order->payment_status === 'failed')>Failed</option>
                                            <option value="refunded" @selected($order->payment_status === 'refunded')>Refunded</option>
                                        </select>
                                    </div>

                                    <button class="btn btn-primary w-100" type="submit">
                                        <i class="bi bi-save me-1"></i> Update Status
                                    </button>
                                </form>
                            </section>

                            <!-- Logistics & Courier Assigner -->
                            <section class="admin-section">
                                <h3 class="mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-truck" style="color: #7c3aed;"></i>
                                    <span>Courier & Logistics Dispatch</span>
                                </h3>
                                
                                @if($order->tracking_number)
                                    <div style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 15px; margin-bottom: 20px;">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <strong style="color: #1e3a8a;"><i class="bi bi-box-seam me-1"></i> Active Shipment</strong>
                                            <span class="badge bg-primary">Dispatched</span>
                                        </div>
                                        <div class="mb-1" style="font-size: 13px; color: #1e293b;">
                                            <span class="muted">Carrier:</span> <strong>{{ $order->courier_name ?: 'Courier Partner' }}</strong>
                                        </div>
                                        <div class="mb-1" style="font-size: 13px; color: #1e293b;">
                                            <span class="muted">AWB Code:</span> <code style="color: #2563eb; font-weight: 700; font-size: 13px;">{{ $order->tracking_number }}</code>
                                        </div>
                                        @if($order->estimated_delivery_date)
                                            <div class="mb-1" style="font-size: 13px; color: #1e293b;">
                                                <span class="muted">Est. Delivery:</span> <strong>{{ $order->estimated_delivery_date->format('d M Y') }}</strong>
                                            </div>
                                        @endif
                                        @if($order->tracking_url)
                                            <a href="{{ $order->tracking_url }}" target="_blank" rel="noreferrer" class="btn btn-outline-primary btn-sm w-100 mt-2 text-center" style="font-weight: 600;">
                                                <i class="bi bi-box-arrow-up-right me-1"></i> Live Courier Tracking
                                            </a>
                                        @endif
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('admin.orders.update-tracking', $order) }}">
                                    @csrf
                                    @method('PUT')

                                    <div class="mb-3">
                                        <label for="courier_name" class="form-label" style="font-weight: 600; font-size: 13px;">Courier Partner Company *</label>
                                        <select name="courier_name" id="courier_name_select" class="form-select mb-2" onchange="document.getElementById('courier_name_input').value = this.value; updateTrackingUrlPlaceholder(this.value);">
                                            <option value="">Select or Type Courier...</option>
                                            <option value="BlueDart Express" @selected(stripos($order->courier_name ?? '', 'blue') !== false)>BlueDart Express</option>
                                            <option value="Delhivery" @selected(stripos($order->courier_name ?? '', 'delhivery') !== false)>Delhivery Surface / Express</option>
                                            <option value="DTDC Courier" @selected(stripos($order->courier_name ?? '', 'dtdc') !== false)>DTDC Courier</option>
                                            <option value="Shiprocket" @selected(stripos($order->courier_name ?? '', 'shiprocket') !== false)>Shiprocket</option>
                                            <option value="Xpressbees" @selected(stripos($order->courier_name ?? '', 'xpressbee') !== false)>Xpressbees Logistics</option>
                                            <option value="Shadowfax" @selected(stripos($order->courier_name ?? '', 'shadowfax') !== false)>Shadowfax</option>
                                            <option value="India Post / Speed Post" @selected(stripos($order->courier_name ?? '', 'post') !== false)>India Post / Speed Post</option>
                                            <option value="Ekart Logistics" @selected(stripos($order->courier_name ?? '', 'ekart') !== false)>Ekart Logistics</option>
                                            <option value="Ecom Express" @selected(stripos($order->courier_name ?? '', 'ecom') !== false)>Ecom Express</option>
                                            <option value="Other Courier">Other / Custom</option>
                                        </select>
                                        <input type="text" name="courier_name" id="courier_name_input" class="form-control" placeholder="Type Courier Company Name" value="{{ $order->courier_name ?: 'Delhivery' }}" required />
                                    </div>

                                    <div class="mb-3">
                                        <label for="tracking_number" class="form-label" style="font-weight: 600; font-size: 13px;">Tracking Number / AWB *</label>
                                        <input type="text" name="tracking_number" id="tracking_number" class="form-control font-monospace" placeholder="e.g. DELH123456789 or 84729104" value="{{ $order->tracking_number }}" required />
                                    </div>

                                    <div class="mb-3">
                                        <label for="tracking_url" class="form-label" style="font-weight: 600; font-size: 13px;">Live Tracking Link (Optional)</label>
                                        <input type="url" name="tracking_url" id="tracking_url" class="form-control" placeholder="Auto-generated if left blank" value="{{ $order->tracking_url }}" />
                                        <small class="text-muted" style="font-size: 11px;">Leave blank to automatically generate tracking link for selected courier.</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="estimated_delivery_date" class="form-label" style="font-weight: 600; font-size: 13px;">Estimated Delivery Date (Optional)</label>
                                        <input type="date" name="estimated_delivery_date" id="estimated_delivery_date" class="form-control" value="{{ optional($order->estimated_delivery_date)->format('Y-m-d') }}" />
                                    </div>

                                    <div class="mb-3">
                                        <label for="location" class="form-label" style="font-weight: 600; font-size: 13px;">Initial Dispatch Location</label>
                                        <input type="text" name="location" id="location" class="form-control" placeholder="e.g. Central Warehouse, Delhi" value="Central Fulfillment Hub" />
                                    </div>

                                    <button class="btn btn-primary w-100" type="submit">
                                        <i class="bi bi-send-check me-1"></i> Save &amp; Dispatch Package
                                    </button>
                                </form>
                            </section>

                            <!-- Milestone Tracker Stepper -->
                            <section class="admin-section">
                                <h3 class="mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-clock-history" style="color: #2563eb;"></i>
                                    <span>Milestones & Timeline</span>
                                </h3>

                                <div class="admin-timeline mb-4" style="position: relative; padding-left: 24px; margin-top: 10px;">
                                    <div class="timeline-line" style="position: absolute; left: 7px; top: 0; bottom: 0; width: 2px; background: var(--border);"></div>
                                    
                                    @forelse($order->trackingUpdates as $index => $tracking)
                                        <div class="timeline-item mb-4" style="position: relative;">
                                            <div class="timeline-marker" style="position: absolute; left: -22px; top: 3px; width: 12px; height: 12px; background: {{ $index === 0 ? '#2563eb' : '#94a3b8' }};"></div>
                                            <div>
                                                <div style="font-weight: 700; color: #0f172a; font-size: 14px;">
                                                    {{ $tracking->status }}
                                                </div>
                                                <div style="font-size: 11px; color: #64748b;" class="d-flex align-items-center gap-2 mt-1">
                                                    <span class="d-flex align-items-center gap-1"><i class="bi bi-geo-alt"></i> {{ $tracking->location ?: 'In Transit' }}</span>
                                                    <span>•</span>
                                                    <span>{{ $tracking->created_at->format('M d, h:i A') }}</span>
                                                </div>
                                                @if($tracking->message)
                                                    <p class="mb-0 mt-2" style="font-size: 12px; line-height: 1.5; background: #f8fafc; padding: 8px 10px; border-left: 2px solid #cbd5e1; color: #334155;">
                                                        {{ $tracking->message }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <p class="muted py-2" style="font-size: 13px;">No chronological tracking milestones registered yet.</p>
                                    @endforelse
                                </div>

                                <hr style="border-top: 1px solid var(--border); margin: 20px 0;">

                                <!-- Add Manual Milestone Log Form -->
                                <div>
                                    <h4 class="mb-3" style="font-size: 14px; font-weight: 700; color: #0f172a;"><i class="bi bi-plus-circle me-1" style="color: #2563eb;"></i> Add Milestone Event</h4>
                                    
                                    <form method="POST" action="{{ route('admin.orders.add-tracking-log', $order) }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="timeline_status" class="form-label" style="font-size: 13px; font-weight: 600;">Event Title *</label>
                                            <input type="text" name="status" id="timeline_status" class="form-control" placeholder="e.g. Package Sorted, Out for Delivery" required />
                                        </div>

                                        <div class="mb-3">
                                            <label for="timeline_location" class="form-label" style="font-size: 13px; font-weight: 600;">Location</label>
                                            <input type="text" name="location" id="timeline_location" class="form-control" placeholder="e.g. Mumbai Hub" />
                                        </div>

                                        <div class="mb-3">
                                            <label for="timeline_message" class="form-label" style="font-size: 13px; font-weight: 600;">Description</label>
                                            <textarea name="message" id="timeline_message" class="form-control" placeholder="Milestone details..." style="min-height: 60px; font-size: 13px;"></textarea>
                                        </div>

                                        <button class="btn btn-outline-secondary w-100" type="submit">
                                            <i class="bi bi-plus-lg me-1"></i> Add Milestone
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
