@extends('admin.layout')

@section('title', 'Marketing, Pixels & Tracking Scripts')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')

        <main class="admin-main">
            <div class="admin-shell-grid">
                <style>
                    .pixel-card {
                        background: #ffffff;
                        border: 1px solid #e2e8f0;
                        border-radius: 12px;
                        padding: 24px;
                        margin-bottom: 20px;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
                    }
                    .pixel-header {
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        margin-bottom: 16px;
                        padding-bottom: 12px;
                        border-bottom: 1px solid #f1f5f9;
                    }
                    .pixel-icon {
                        width: 42px;
                        height: 42px;
                        border-radius: 10px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 20px;
                        color: #ffffff;
                    }
                    .meta-icon { background: #1877f2; }
                    .gtm-icon { background: #4285f4; }
                    .code-icon { background: #1e293b; }
                    .script-textarea {
                        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
                        font-size: 13px;
                        background: #0f172a;
                        color: #38bdf8;
                        border-radius: 8px;
                        padding: 14px;
                        width: 100%;
                        min-height: 120px;
                        border: 1px solid #334155;
                    }
                    .helper-box {
                        background: #f8fafc;
                        border-left: 4px solid var(--kanakshi-pink, #e9718b);
                        padding: 12px 16px;
                        border-radius: 0 8px 8px 0;
                        margin-top: 8px;
                        font-size: 12px;
                        color: #475569;
                    }
                </style>

                <div class="admin-banner">
                    <div>
                        <div class="brand">Integrations & Growth</div>
                        <h2>Marketing, Pixels & Tracking Studio</h2>
                        <p class="lead" style="margin-top:8px;">
                            Connect Meta (Facebook) Pixel, Google Tag Manager (GTM), Google Analytics, and custom conversion tracking scripts to your storefront with zero code deployment.
                        </p>
                    </div>
                </div>

                @if (session('status'))
                    <div class="admin-toast" style="background:#16a34a;color:#ffffff;padding:16px 20px;border-radius:8px;margin-bottom:20px;display:flex;align-items:center;gap:12px;">
                        <i class="bi bi-check-circle-fill" style="font-size:20px;"></i>
                        <div>
                            <strong>Success!</strong>
                            <p style="margin:0;font-size:14px;">{{ session('status') }}</p>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="admin-errors" style="background:#fef2f2;border:1px solid #f87171;padding:16px 20px;border-radius:8px;margin-bottom:20px;">
                        <strong style="color:#b91c1c;">Please review the highlighted errors:</strong>
                        <div style="margin-top:6px;font-size:13px;color:#dc2626;">
                            {{ $errors->first() }}
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.settings.marketing-pixels.update') }}">
                    @csrf
                    @method('PUT')

                    <!-- 1. Meta / Facebook Pixel -->
                    <div class="pixel-card">
                        <div class="pixel-header">
                            <div class="pixel-icon meta-icon">
                                <i class="bi bi-meta"></i>
                            </div>
                            <div>
                                <h3 style="margin:0;font-size:1.15rem;font-weight:700;">Meta / Facebook Pixel</h3>
                                <p style="margin:0;font-size:0.85rem;color:#64748b;">Tracks PageView, ViewContent, AddToCart, and Purchase events across Meta Ads.</p>
                            </div>
                        </div>

                        <div class="field">
                            <label for="facebook_pixel_id" style="font-weight:600;font-size:14px;margin-bottom:6px;display:block;">Facebook Pixel ID</label>
                            <input 
                                id="facebook_pixel_id" 
                                name="facebook_pixel_id" 
                                value="{{ old('facebook_pixel_id', $store?->facebook_pixel_id) }}" 
                                placeholder="e.g. 123456789012345" 
                                style="width:100%;max-width:480px;padding:10px 14px;border:1px solid #cbd5e1;border-radius:6px;font-size:14px;"
                            />
                            <div class="helper-box">
                                <strong>How it works:</strong> Entering your Pixel ID automatically activates the official Facebook Pixel runtime (<code>fbevents.js</code>) and fires <code>PageView</code> across all product and checkout pages.
                            </div>
                        </div>
                    </div>

                    <!-- 2. Google Tag Manager (GTM) -->
                    <div class="pixel-card">
                        <div class="pixel-header">
                            <div class="pixel-icon gtm-icon">
                                <i class="bi bi-google"></i>
                            </div>
                            <div>
                                <h3 style="margin:0;font-size:1.15rem;font-weight:700;">Google Tag Manager (GTM) & GA4</h3>
                                <p style="margin:0;font-size:0.85rem;color:#64748b;">Manage Google Analytics 4, Google Ads conversions, and third-party tags via GTM container.</p>
                            </div>
                        </div>

                        <div class="field">
                            <label for="google_tag_manager_id" style="font-weight:600;font-size:14px;margin-bottom:6px;display:block;">GTM Container ID</label>
                            <input 
                                id="google_tag_manager_id" 
                                name="google_tag_manager_id" 
                                value="{{ old('google_tag_manager_id', $store?->google_tag_manager_id) }}" 
                                placeholder="e.g. GTM-XXXXXXX" 
                                style="width:100%;max-width:480px;padding:10px 14px;border:1px solid #cbd5e1;border-radius:6px;font-size:14px;"
                            />
                            <div class="helper-box">
                                <strong>How it works:</strong> Both head snippet and noscript iframe are automatically mounted on the live storefront in real-time.
                            </div>
                        </div>
                    </div>

                    <!-- 3. Custom Header Scripts -->
                    <div class="pixel-card">
                        <div class="pixel-header">
                            <div class="pixel-icon code-icon">
                                <i class="bi bi-code-square"></i>
                            </div>
                            <div>
                                <h3 style="margin:0;font-size:1.15rem;font-weight:700;">Custom Header Scripts</h3>
                                <p style="margin:0;font-size:0.85rem;color:#64748b;">Injected directly into the <code>&lt;head&gt;</code> tag (e.g. Clarity, Hotjar, Microsoft Ads, Pinterest Tag).</p>
                            </div>
                        </div>

                        <div class="field">
                            <label for="custom_header_scripts" style="font-weight:600;font-size:14px;margin-bottom:6px;display:block;">Header Snippet (HTML / &lt;script&gt;)</label>
                            <textarea 
                                id="custom_header_scripts" 
                                name="custom_header_scripts" 
                                class="script-textarea" 
                                placeholder="<script>\n  // Paste your custom verification or tracking tag here\n</script>"
                            >{{ old('custom_header_scripts', $store?->custom_header_scripts) }}</textarea>
                        </div>
                    </div>

                    <!-- 4. Custom Footer Scripts -->
                    <div class="pixel-card">
                        <div class="pixel-header">
                            <div class="pixel-icon code-icon">
                                <i class="bi bi-layout-text-window"></i>
                            </div>
                            <div>
                                <h3 style="margin:0;font-size:1.15rem;font-weight:700;">Custom Footer Scripts</h3>
                                <p style="margin:0;font-size:0.85rem;color:#64748b;">Injected before <code>&lt;/body&gt;</code> closing tag (e.g. Live Chat widget, WhatsApp bot snippet, Tawk.to).</p>
                            </div>
                        </div>

                        <div class="field">
                            <label for="custom_footer_scripts" style="font-weight:600;font-size:14px;margin-bottom:6px;display:block;">Footer Snippet (HTML / &lt;script&gt;)</label>
                            <textarea 
                                id="custom_footer_scripts" 
                                name="custom_footer_scripts" 
                                class="script-textarea" 
                                placeholder="<script>\n  // Paste live chat or footer script here\n</script>"
                            >{{ old('custom_footer_scripts', $store?->custom_footer_scripts) }}</textarea>
                        </div>
                    </div>

                    <div style="display:flex;justify-content:flex-end;margin-top:20px;">
                        <button type="submit" class="button" style="background:var(--kanakshi-pink, #e9718b);color:#ffffff;padding:12px 28px;font-size:15px;font-weight:700;border-radius:8px;border:none;cursor:pointer;">
                            Save & Sync Tracking Settings
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
@endsection
