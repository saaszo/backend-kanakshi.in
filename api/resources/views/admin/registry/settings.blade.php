@extends('admin.layout')

@section('title', 'Registry Settings | Kanakshi.in Admin')

@section('content')
<div class="dashboard-shell">
    @include('admin.partials.sidebar')

    <main class="admin-main">
        <div class="admin-banner">
            <div>
                <span class="brand">Configuration</span>
                <h2>Registry Settings</h2>
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

        <div class="admin-section" style="max-width: 800px;">
            <h3 class="border-bottom pb-2 mb-4">Guarantee & Buyback Configurations</h3>
            <form method="POST" action="{{ route('admin.registry.settings.update') }}">
                @csrf
                @method('PUT')

                <div class="form-grid g-3 mb-4">
                    <div class="field">
                        <label for="warranty_months">Default Guarantee Duration (Months)</label>
                        <input type="number" id="warranty_months" name="registry_warranty_duration_months" value="{{ $warrantyDuration }}" required min="1">
                        <small class="text-muted">Guarantee period applied automatically on verification.</small>
                    </div>

                    <div class="field">
                        <label for="allow_buyback">Allow Buyback Programs</label>
                        <select id="allow_buyback" name="registry_allow_buyback" required>
                            <option value="1" {{ $allowBuyback ? 'selected' : '' }}>Yes, active</option>
                            <option value="0" {{ !$allowBuyback ? 'selected' : '' }}>No, suspended</option>
                        </select>
                        <small class="text-muted">Toggles the Return-to-Vault buyback appraisal submission form on storefront.</small>
                    </div>
                </div>

                <div class="field mb-4">
                    <label>Allowed Purchase Sources</label>
                    <div class="d-flex gap-3 flex-wrap bg-light p-3 rounded-3 border">
                        <label class="checkbox-row compact mb-0">
                            <input type="checkbox" name="registry_allowed_sources[]" value="website" {{ in_array('website', $allowedSources) ? 'checked' : '' }}>
                            <span>Kanakshi.in Website</span>
                        </label>
                        <label class="checkbox-row compact mb-0">
                            <input type="checkbox" name="registry_allowed_sources[]" value="offline_store" {{ in_array('offline_store', $allowedSources) ? 'checked' : '' }}>
                            <span>Offline Store</span>
                        </label>
                        <label class="checkbox-row compact mb-0">
                            <input type="checkbox" name="registry_allowed_sources[]" value="amazon" {{ in_array('amazon', $allowedSources) ? 'checked' : '' }}>
                            <span>Amazon India</span>
                        </label>
                        <label class="checkbox-row compact mb-0">
                            <input type="checkbox" name="registry_allowed_sources[]" value="other_marketplace" {{ in_array('other_marketplace', $allowedSources) ? 'checked' : '' }}>
                            <span>Other Marketplaces (Flipkart, etc.)</span>
                        </label>
                    </div>
                </div>

                <div class="form-grid g-3 mb-4">
                    <div class="field">
                        <label for="upload_size">Maximum Upload Size (MB)</label>
                        <input type="number" id="upload_size" name="registry_allowed_upload_size_mb" value="{{ $allowedUploadSize }}" required min="1" max="50">
                        <small class="text-muted">Limits customer uploads for invoices and condition photos.</small>
                    </div>

                    <div class="field">
                        <label for="file_types">Allowed File Extensions</label>
                        <input type="text" id="file_types" name="registry_allowed_file_types" value="{{ $allowedFileTypes }}" required>
                        <small class="text-muted">Comma-separated list (e.g. pdf,jpg,jpeg,png,webp).</small>
                    </div>
                </div>

                <div class="field mb-4">
                    <label for="auto_verify">Auto-Verify Internal Website Orders</label>
                    <select id="auto_verify" name="registry_auto_verify_website_orders" required>
                        <option value="1" {{ $autoVerifyWebsiteOrders ? 'selected' : '' }}>Enabled (Matches order email/phone)</option>
                        <option value="0" {{ !$autoVerifyWebsiteOrders ? 'selected' : '' }}>Disabled (Hold all for manual review)</option>
                    </select>
                    <small class="text-muted">If enabled, registrations with source "website" matching internal orders are approved automatically.</small>
                </div>

                <button type="submit" class="button px-4 py-2 mt-2 w-auto">
                    <i class="bi bi-save-fill"></i> Save Registry Configurations
                </button>
            </form>
        </div>
    </main>
</div>
@endsection
