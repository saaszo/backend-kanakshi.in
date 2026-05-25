@extends('admin.layout')

@section('title', 'Guarantees & Warranties Details | Little Divinity Admin')

@section('content')
<div class="dashboard-shell">
    @include('admin.partials.sidebar')

    <main class="admin-main">
        <div class="topbar">
            <div>
                <a href="{{ route('admin.registry.registrations.index') }}" class="text-link text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Back to Registrations
                </a>
                <h2 class="mt-2">Guarantee Guarantee Card Details</h2>
            </div>
            <div class="toolbar-actions">
                @if($reg->verification_status === 'verified')
                    <span class="admin-badge success px-3 py-2 fs-6">✓ Verified Guarantee</span>
                @elseif($reg->verification_status === 'pending_verification')
                    <span class="admin-badge warning px-3 py-2 fs-6">⚠ Pending Verification</span>
                @elseif($reg->verification_status === 'rejected')
                    <span class="admin-badge danger px-3 py-2 fs-6">✗ Rejected Registration</span>
                @else
                    <span class="admin-badge muted px-3 py-2 fs-6">Expired Guarantee</span>
                @endif
            </div>
        </div>

        @if(session('status'))
            <div class="message">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <div class="admin-split-layout d-grid gap-4" style="grid-template-columns: 1.4fr 1fr;">
            <!-- Left Side: Registration Details -->
            <div class="d-grid gap-4">
                <div class="panel">
                    <h3 class="border-bottom pb-2 mb-3">Guarantee Card Registry</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <strong>Registration Code</strong>
                            <p class="text-primary fs-5 font-monospace">{{ $reg->registration_code }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Purchase Date</strong>
                            <p>{{ $reg->purchase_date->format('F d, Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Guarantee Start Date</strong>
                            <p>{{ $reg->warranty_start_date ? $reg->warranty_start_date->format('F d, Y') : 'Not Activated' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Guarantee End Date</strong>
                            <p>{{ $reg->warranty_end_date ? $reg->warranty_end_date->format('F d, Y') : 'Not Activated' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Product Name Registered</strong>
                            <p class="fs-6 font-weight-bold">{{ $reg->product_name_snapshot }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Serial / Engraved ID</strong>
                            <p>{{ $reg->serial_card_id ?: 'No Serial Engraved' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Purchase Source</strong>
                            <p class="text-capitalize">{{ str_replace('_', ' ', $reg->purchase_source) }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Bill / Order / Receipt #</strong>
                            <p class="font-monospace">{{ $reg->order_or_bill_number }}</p>
                        </div>
                        @if($reg->source_store_name)
                            <div class="col-md-6">
                                <strong>Store / Seller Name</strong>
                                <p>{{ $reg->source_store_name }}</p>
                            </div>
                        @endif
                        @if($reg->source_city)
                            <div class="col-md-6">
                                <strong>City Location</strong>
                                <p>{{ $reg->source_city }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="panel">
                    <h3 class="border-bottom pb-2 mb-3">Customer Profile</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <strong>Full Name</strong>
                            <p>{{ $reg->customer_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Email Address</strong>
                            <p>{{ $reg->email }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Phone Number</strong>
                            <p>{{ $reg->phone }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>WhatsApp Contact</strong>
                            <p>{{ $reg->whatsapp_number ?: 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>WhatsApp Alert Status</strong>
                            <p>
                                @if($reg->whatsapp_opt_in)
                                    <span class="text-success"><i class="bi bi-chat-left-dots-fill"></i> Enabled</span>
                                @else
                                    <span class="text-muted"><i class="bi bi-chat-left-dots"></i> Disabled</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-12">
                            <strong>Customer Notes</strong>
                            <p class="bg-light p-3 rounded-3 border">{{ $reg->notes ?: 'No additional notes added by customer.' }}</p>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <h3 class="border-bottom pb-2 mb-3">Uploaded Verification Proofs</h3>
                    <div class="media-slot-grid">
                        <div class="media-slot-card">
                            <strong>Original Invoice</strong>
                            @if($reg->invoice_file_path)
                                @php $ext = strtolower(pathinfo($reg->invoice_file_path, PATHINFO_EXTENSION)); @endphp
                                @if(in_array($ext, ['jpg','jpeg','png','webp']))
                                    <a href="{{ $reg->invoice_file_path }}" target="_blank">
                                        <img src="{{ $reg->invoice_file_path }}" alt="Invoice upload" class="admin-upload-preview">
                                    </a>
                                @else
                                    <div class="bg-white border rounded-3 p-3 text-center d-grid gap-2">
                                        <i class="bi bi-file-earmark-pdf fs-1 text-danger"></i>
                                        <span class="small text-truncate">{{ basename($reg->invoice_file_path) }}</span>
                                    </div>
                                @endif
                                <a href="{{ $reg->invoice_file_path }}" target="_blank" class="button secondary small mt-2">
                                    <i class="bi bi-download"></i> View Full File
                                </a>
                            @else
                                <p class="text-muted small">No invoice document uploaded (Auto-verified Website Order)</p>
                            @endif
                        </div>

                        <div class="media-slot-card">
                            <strong>Product Image Cover</strong>
                            @if($reg->product_image_path)
                                <a href="{{ $reg->product_image_path }}" target="_blank">
                                    <img src="{{ $reg->product_image_path }}" alt="Product snapshot" class="admin-upload-preview">
                                </a>
                                <a href="{{ $reg->product_image_path }}" target="_blank" class="button secondary small mt-2">
                                    <i class="bi bi-download"></i> View Full Photo
                                </a>
                            @else
                                <p class="text-muted small">No product photo uploaded by customer.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Decisions & History -->
            <div class="d-grid gap-4">
                @if($reg->verification_status === 'pending_verification')
                    <div class="panel border-warning border-3" id="verify-section">
                        <h3 class="text-warning mb-3"><i class="bi bi-patch-question"></i> Verify Guarantee Claim</h3>
                        <form method="POST" action="{{ route('admin.registry.registrations.verify', $reg->id) }}">
                            @csrf
                            <div class="field">
                                <label for="warranty_start">Active Start Date</label>
                                <input type="date" id="warranty_start" name="warranty_start_date" value="{{ $reg->warranty_start_date ? $reg->warranty_start_date->format('Y-m-d') : $reg->purchase_date->format('Y-m-d') }}" required>
                            </div>
                            <div class="field">
                                <label for="warranty_end">Active End Date</label>
                                <input type="date" id="warranty_end" name="warranty_end_date" value="{{ $reg->warranty_end_date ? $reg->warranty_end_date->format('Y-m-d') : $reg->purchase_date->addMonths(24)->format('Y-m-d') }}" required>
                            </div>
                            <div class="field">
                                <label for="admin_notes">Administrative Remarks</label>
                                <textarea id="admin_notes" name="admin_notes" placeholder="Verify store/amazon transaction match notes..."></textarea>
                            </div>
                            <button type="submit" class="button"><i class="bi bi-patch-check"></i> Approve & Activate Guarantee</button>
                        </form>

                        <hr class="my-4">

                        <h3 class="text-danger mb-3">Reject Registration</h3>
                        <form method="POST" action="{{ route('admin.registry.registrations.reject', $reg->id) }}">
                            @csrf
                            <div class="field">
                                <label for="reject_notes">Rejection Reason</label>
                                <textarea id="reject_notes" name="admin_notes" placeholder="Invoice details are blurry / Order bill number could not be found..." required></textarea>
                            </div>
                            <button type="submit" class="button danger"><i class="bi bi-patch-exclamation"></i> Reject Guarantee</button>
                        </form>
                    </div>
                @else
                    <div class="panel">
                        <h3>Guarantee Management</h3>
                        <form method="POST" action="{{ route('admin.registry.registrations.update-notes', $reg->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="field">
                                <label for="buyback_eligibility">Buyback Eligibility</label>
                                <select id="buyback_eligibility" name="buyback_eligible">
                                    <option value="1" {{ $reg->buyback_eligible ? 'selected' : '' }}>Eligible for Return-to-Vault</option>
                                    <option value="0" {{ !$reg->buyback_eligible ? 'selected' : '' }}>Ineligible for Buyback</option>
                                </select>
                            </div>
                            <div class="field">
                                <label for="active_admin_notes">Administrative Notes</label>
                                <textarea id="active_admin_notes" name="admin_notes">{{ $reg->admin_notes }}</textarea>
                            </div>
                            <button type="submit" class="button"><i class="bi bi-save"></i> Save Registry Details</button>
                        </form>
                    </div>
                @endif

                <div class="panel">
                    <h3 class="mb-3">Audit Logs & History</h3>
                    <div class="stack-list">
                        @forelse($reg->activityLogs as $log)
                            <div class="stack-card p-3">
                                <div class="d-flex justify-content-between">
                                    <strong class="text-capitalize">{{ str_replace('_', ' ', $log->action) }}</strong>
                                    <small class="text-muted">{{ $log->created_at->format('Y-m-d H:i') }}</small>
                                </div>
                                <span class="small text-muted">Authorized by: {{ $log->created_by }}</span>
                                @if($log->new_data)
                                    <pre class="small bg-white p-2 rounded border mt-2 mb-0 font-monospace" style="font-size: 11px;">{{ json_encode($log->new_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted small text-center mb-0">No audits recorded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
