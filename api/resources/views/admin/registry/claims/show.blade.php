@extends('admin.layout')

@section('title', 'Guarantee Claim Case | Little Divinity Admin')

@section('content')
<div class="dashboard-shell">
    @include('admin.partials.sidebar')

    <main class="admin-main">
        <div class="topbar">
            <div>
                <a href="{{ route('admin.registry.claims.index') }}" class="text-link text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Back to Claims Queue
                </a>
                <h2 class="mt-2">Guarantee Claim Review</h2>
            </div>
            <div class="toolbar-actions">
                @if($claim->status === 'completed')
                    <span class="admin-badge success px-3 py-2 fs-6">✓ Resolved Case</span>
                @elseif($claim->status === 'submitted')
                    <span class="admin-badge primary px-3 py-2 fs-6">⚠ New Case Submission</span>
                @elseif($claim->status === 'rejected')
                    <span class="admin-badge danger px-3 py-2 fs-6">✗ Rejected Case</span>
                @else
                    <span class="admin-badge warning px-3 py-2 fs-6">{{ ucfirst(str_replace('_', ' ', $claim->status)) }}</span>
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
            <!-- Left Side: Claim Details -->
            <div class="d-grid gap-4">
                <div class="panel">
                    <h3 class="border-bottom pb-2 mb-3">Claim Case File</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <strong>Claim ID Code</strong>
                            <p class="text-primary font-monospace fs-5">{{ $claim->claim_code }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Submitted Date</strong>
                            <p>{{ $claim->created_at->format('F d, Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Issue Category</strong>
                            <p><span class="badge bg-light text-dark border fs-6">{{ $claim->issue_type }}</span></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Guarantee Registration Link</strong>
                            <p>
                                <a href="{{ route('admin.registry.registrations.show', $claim->registration->id) }}" class="font-monospace text-primary text-decoration-none">
                                    {{ $claim->registration->registration_code }} <i class="bi bi-box-arrow-up-right small"></i>
                                </a>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <strong>Registered Product</strong>
                            <p class="fs-6 font-weight-bold">{{ $claim->registration->product_name_snapshot }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Guarantee Ends On</strong>
                            <p>{{ $claim->registration->warranty_end_date ? $claim->registration->warranty_end_date->format('F d, Y') : 'N/A' }}</p>
                        </div>
                        <div class="col-12">
                            <strong>Detailed Issue Description</strong>
                            <p class="bg-light p-3 rounded-3 border fs-6" style="line-height: 1.6;">{{ $claim->description }}</p>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <h3 class="border-bottom pb-2 mb-3">Uploaded Issue Photos</h3>
                    <div class="d-flex flex-wrap gap-3">
                        @if(is_array($claim->image_paths) && count($claim->image_paths) > 0)
                            @foreach($claim->image_paths as $img)
                                <div class="media-slot-card p-2 text-center" style="max-width: 200px;">
                                    <a href="{{ $img }}" target="_blank">
                                        <img src="{{ $img }}" alt="Issue detail photo" class="admin-upload-preview mb-2">
                                    </a>
                                    <a href="{{ $img }}" target="_blank" class="button secondary small">
                                        <i class="bi bi-download"></i> Open Photo
                                    </a>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted small w-100 text-center py-4">No issue photos uploaded by customer.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Side: Actions & Guarantee context -->
            <div class="d-grid gap-4">
                <div class="panel">
                    <h3 class="mb-3">Update Case Status</h3>
                    <form method="POST" action="{{ route('admin.registry.claims.update', $claim->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="field">
                            <label for="claim_status">Guarantee Service Status</label>
                            <select id="claim_status" name="status">
                                <option value="submitted" {{ $claim->status === 'submitted' ? 'selected' : '' }}>Submitted (New)</option>
                                <option value="under_review" {{ $claim->status === 'under_review' ? 'selected' : '' }}>Under Review</option>
                                <option value="approved" {{ $claim->status === 'approved' ? 'selected' : '' }}>Approved / Order Replacement</option>
                                <option value="rejected" {{ $claim->status === 'rejected' ? 'selected' : '' }}>Rejected (No Guarantee Scope)</option>
                                <option value="in_service" {{ $claim->status === 'in_service' ? 'selected' : '' }}>In Service / Polish & Repair</option>
                                <option value="completed" {{ $claim->status === 'completed' ? 'selected' : '' }}>Completed & Returned</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="claim_admin_notes">Administrative / Service Notes</label>
                            <textarea id="claim_admin_notes" name="admin_notes" placeholder="Polish team working on it / Dispatching a new idol replacement...">{{ $claim->admin_notes }}</textarea>
                        </div>
                        <button type="submit" class="button"><i class="bi bi-save"></i> Save Claim Case Details</button>
                    </form>
                </div>

                <div class="panel bg-light">
                    <h3 class="mb-2">Guarantee Verification Context</h3>
                    <p class="small text-muted">Verification parameters for registration: <strong>{{ $claim->registration->registration_code }}</strong></p>
                    <div class="row g-2 small">
                        <div class="col-6"><strong>Customer Name:</strong></div>
                        <div class="col-6">{{ $claim->registration->customer_name }}</div>
                        <div class="col-6"><strong>Customer Email:</strong></div>
                        <div class="col-6">{{ $claim->registration->email }}</div>
                        <div class="col-6"><strong>Phone Contact:</strong></div>
                        <div class="col-6">{{ $claim->registration->phone }}</div>
                        <div class="col-6"><strong>Source Channel:</strong></div>
                        <div class="col-6 text-capitalize">{{ str_replace('_', ' ', $claim->registration->purchase_source) }}</div>
                        <div class="col-6"><strong>Order/Bill Number:</strong></div>
                        <div class="col-6 font-monospace">{{ $claim->registration->order_or_bill_number }}</div>
                    </div>
                    <a href="{{ route('admin.registry.registrations.show', $claim->registration->id) }}" class="button small secondary mt-3 w-100">
                        <i class="bi bi-file-earmark-check"></i> View Guarantee Record
                    </a>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
