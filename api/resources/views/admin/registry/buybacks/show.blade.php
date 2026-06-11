@extends('admin.layout')

@section('title', 'Buyback Request appraisal | Little Divinity Admin')

@section('content')
<div class="dashboard-shell">
    @include('admin.partials.sidebar')

    <main class="admin-main">
        <div class="admin-banner">
            <div>
                <a href="{{ route('admin.registry.buybacks.index') }}" class="text-link text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Back to Buybacks Queue
                </a>
                <h2 class="mt-2">Buyback Return-to-Vault Appraisal</h2>
            </div>
            <div class="toolbar-actions">
                @if($buyback->status === 'completed')
                    <span class="admin-badge success px-3 py-2 fs-6">✓ Vaulted & Completed</span>
                @elseif($buyback->status === 'submitted')
                    <span class="admin-badge primary px-3 py-2 fs-6">⚠ New Buyback Case</span>
                @elseif($buyback->status === 'rejected')
                    <span class="admin-badge danger px-3 py-2 fs-6">✗ Rejected Case</span>
                @else
                    <span class="admin-badge warning px-3 py-2 fs-6">{{ ucfirst(str_replace('_', ' ', $buyback->status)) }}</span>
                @endif
            </div>
        </div>

        @if(session('status'))
            <div class="admin-toast">
    <div>
        <strong>Success!</strong>
        <p>
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('status') }}</span>
            </p>
    </div>
</div>
        @endif

        <div class="admin-split-layout d-grid gap-4" style="grid-template-columns: 1.4fr 1fr;">
            <!-- Left Side: Request Details -->
            <div class="d-grid gap-4">
                <div class="admin-section">
                    <h3 class="border-bottom pb-2 mb-3">Buyback Request Case File</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <strong>Buyback ID Code</strong>
                            <p class="text-primary font-monospace fs-5">{{ $buyback->request_code }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Submitted Date</strong>
                            <p>{{ $buyback->created_at->format('F d, Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Pickup City</strong>
                            <p><span class="badge bg-light text-dark border fs-6">{{ $buyback->pickup_city ?: 'N/A' }}</span></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Preferred Contact</strong>
                            <p class="text-capitalize"><i class="bi bi-chat-left-text"></i> {{ $buyback->preferred_contact_method }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Guarantee Registration Link</strong>
                            <p>
                                <a href="{{ route('admin.registry.registrations.show', $buyback->registration->id) }}" class="font-monospace text-primary text-decoration-none">
                                    {{ $buyback->registration->registration_code }} <i class="bi bi-box-arrow-up-right small"></i>
                                </a>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <strong>Registered Product</strong>
                            <p class="fs-6 font-weight-bold">{{ $buyback->registration->product_name_snapshot }}</p>
                        </div>
                        <div class="col-12">
                            <strong>Detailed Product Condition Notes</strong>
                            <p class="bg-light p-3 rounded-3 border fs-6" style="line-height: 1.6;">{{ $buyback->condition_notes }}</p>
                        </div>
                    </div>
                </div>

                <div class="admin-section">
                    <h3 class="border-bottom pb-2 mb-3">Uploaded Condition Photos</h3>
                    <div class="d-flex flex-wrap gap-3">
                        @if(is_array($buyback->image_paths) && count($buyback->image_paths) > 0)
                            @foreach($buyback->image_paths as $img)
                                <div class="media-slot-card p-2 text-center" style="max-width: 200px;">
                                    <a href="{{ $img }}" target="_blank">
                                        <img src="{{ $img }}" alt="Condition photo" class="admin-upload-preview mb-2">
                                    </a>
                                    <a href="{{ $img }}" target="_blank" class="button secondary small">
                                        <i class="bi bi-download"></i> Open Photo
                                    </a>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted small w-100 text-center py-4">No condition photos uploaded by customer.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Side: Valuation Appraisal & Guarantee Context -->
            <div class="d-grid gap-4">
                <div class="admin-section border-3">
                    <h3 class="text-primary mb-3"><i class="bi bi-safe2"></i> Appraise & Value Asset</h3>
                    <form method="POST" action="{{ route('admin.registry.buybacks.update', $buyback->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="field">
                            <label for="buyback_status">Buyback / Return status</label>
                            <select id="buyback_status" name="status">
                                <option value="submitted" {{ $buyback->status === 'submitted' ? 'selected' : '' }}>Submitted (New)</option>
                                <option value="inspection_pending" {{ $buyback->status === 'inspection_pending' ? 'selected' : '' }}>Inspection Pending / In-Transit</option>
                                <option value="valued" {{ $buyback->status === 'valued' ? 'selected' : '' }}>Valued / Proposed Value</option>
                                <option value="approved" {{ $buyback->status === 'approved' ? 'selected' : '' }}>Approved / Value Accepted</option>
                                <option value="rejected" {{ $buyback->status === 'rejected' ? 'selected' : '' }}>Rejected (No Value)</option>
                                <option value="completed" {{ $buyback->status === 'completed' ? 'selected' : '' }}>Completed / Vault Restored</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="est_value">Estimated Buyback Value (₹)</label>
                            <input type="number" id="est_value" name="estimated_buyback_value" placeholder="₹ Value based on photos" step="0.01" value="{{ $buyback->estimated_buyback_value }}">
                        </div>
                        <div class="field">
                            <label for="final_value">Final Buyback Value (₹)</label>
                            <input type="number" id="final_value" name="final_buyback_value" placeholder="₹ Final check value" step="0.01" value="{{ $buyback->final_buyback_value }}">
                        </div>
                        <div class="field">
                            <label for="buyback_admin_notes">Administrative Appraisal Remarks</label>
                            <textarea id="buyback_admin_notes" name="admin_notes" placeholder="Brass shows mild oxidation, polish will restore it. Proposing 60% buyback..." style="min-height: 120px;">{{ $buyback->admin_notes }}</textarea>
                        </div>
                        <button type="submit" class="button"><i class="bi bi-patch-check-fill"></i> Save Appraisal Details</button>
                    </form>
                </div>

                <div class="admin-section bg-light">
                    <h3 class="mb-2">Guarantee Verification Context</h3>
                    <p class="small text-muted">Verification parameters for registration: <strong>{{ $buyback->registration->registration_code }}</strong></p>
                    <div class="row g-2 small">
                        <div class="col-6"><strong>Customer Name:</strong></div>
                        <div class="col-6">{{ $buyback->registration->customer_name }}</div>
                        <div class="col-6"><strong>Customer Email:</strong></div>
                        <div class="col-6">{{ $buyback->registration->email }}</div>
                        <div class="col-6"><strong>Phone Contact:</strong></div>
                        <div class="col-6">{{ $buyback->registration->phone }}</div>
                        <div class="col-6"><strong>Source Channel:</strong></div>
                        <div class="col-6 text-capitalize">{{ str_replace('_', ' ', $buyback->registration->purchase_source) }}</div>
                        <div class="col-6"><strong>Order/Bill Number:</strong></div>
                        <div class="col-6 font-monospace">{{ $buyback->registration->order_or_bill_number }}</div>
                    </div>
                    <a href="{{ route('admin.registry.registrations.show', $buyback->registration->id) }}" class="button small secondary mt-3 w-100">
                        <i class="bi bi-file-earmark-check"></i> View Guarantee Record
                    </a>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
