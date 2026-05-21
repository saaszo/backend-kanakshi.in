@extends('admin.layout')

@section('title', 'Store Settings')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')

        <main class="admin-main">
            <div class="dashboard-card">
                <div class="page-head">
                    <div>
                        <div class="brand">Configuration</div>
                        <h2>Store Settings</h2>
                        <p class="lead" style="margin-top:8px;">Manage brand details, storefront identity, delivery setup, and payment gateways from one place.</p>
                    </div>
                </div>

                @if (session('status'))
                    <div class="message">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="errors">
                        <strong>Please fix the highlighted fields.</strong>
                    </div>
                @endif

                <div class="section-grid">
                    <section class="panel">
                        <h3>Store Identity</h3>
                        <p>These values power storefront branding, footer details, invoice branding, and general business information.</p>
                        <form method="POST" action="{{ route('admin.settings.store.update') }}" class="section-grid" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="form-grid">
                                <div class="field">
                                    <label for="site_name">Site Name</label>
                                    <input id="site_name" name="site_name" value="{{ old('site_name', $store?->site_name) }}" />
                                </div>
                                <div class="field">
                                    <label for="site_tagline">Tagline</label>
                                    <input id="site_tagline" name="site_tagline" value="{{ old('site_tagline', $store?->site_tagline) }}" />
                                </div>
                                <div class="field">
                                    <label for="business_name">Business Name</label>
                                    <input id="business_name" name="business_name" value="{{ old('business_name', $store?->business_name) }}" />
                                </div>
                                <div class="field">
                                    <label for="custom_domain">Custom Domain</label>
                                    <input id="custom_domain" name="custom_domain" value="{{ old('custom_domain', $store?->custom_domain) }}" />
                                </div>
                                <div class="checkbox-row">
                                    <input id="show_topbar" type="checkbox" name="show_topbar" value="1" @checked(old('show_topbar', $store?->show_topbar))>
                                    <label for="show_topbar" style="margin:0;">Enable top offer bar</label>
                                </div>
                                <div class="field">
                                    <label for="topbar_bg_color">Topbar Background Color</label>
                                    <input id="topbar_bg_color" name="topbar_bg_color" value="{{ old('topbar_bg_color', $store?->topbar_bg_color) }}" placeholder="#0f0f0f" />
                                </div>
                                <div class="field">
                                    <label for="topbar_text_color">Topbar Text Color</label>
                                    <input id="topbar_text_color" name="topbar_text_color" value="{{ old('topbar_text_color', $store?->topbar_text_color) }}" placeholder="#ffffff" />
                                </div>
                                <div class="field">
                                    <label for="business_email">Business Email</label>
                                    <input id="business_email" name="business_email" type="email" value="{{ old('business_email', $store?->business_email) }}" />
                                </div>
                                <div class="field">
                                    <label for="support_email">Support Email</label>
                                    <input id="support_email" name="support_email" type="email" value="{{ old('support_email', $store?->support_email) }}" />
                                </div>
                                <div class="field">
                                    <label for="business_phone">Business Phone</label>
                                    <input id="business_phone" name="business_phone" value="{{ old('business_phone', $store?->business_phone) }}" />
                                </div>
                                <div class="field">
                                    <label for="support_phone">Support Phone</label>
                                    <input id="support_phone" name="support_phone" value="{{ old('support_phone', $store?->support_phone) }}" />
                                </div>
                                <div class="field">
                                    <label for="whatsapp_number">WhatsApp Number</label>
                                    <input id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number', $store?->whatsapp_number) }}" />
                                </div>
                                <div class="field">
                                    <label for="logo_url">Logo URL</label>
                                    <input id="logo_url" name="logo_url" value="{{ old('logo_url', $store?->logo_url) }}" />
                                    @if ($store?->logo_url)
                                        <img src="{{ $store->logo_url }}" alt="Logo" class="admin-upload-preview" style="margin-top:10px;" />
                                    @endif
                                    <input type="file" id="logo_file" name="logo_file" accept="image/*" style="margin-top:10px;" />
                                </div>
                                <div class="field">
                                    <label for="favicon_url">Favicon URL</label>
                                    <input id="favicon_url" name="favicon_url" value="{{ old('favicon_url', $store?->favicon_url) }}" />
                                    @if ($store?->favicon_url)
                                        <img src="{{ $store->favicon_url }}" alt="Favicon" class="admin-upload-preview admin-upload-preview--small" style="margin-top:10px;" />
                                    @endif
                                    <input type="file" id="favicon_file" name="favicon_file" accept="image/*" style="margin-top:10px;" />
                                </div>
                                <div class="field">
                                    <label for="invoice_prefix">Invoice Prefix</label>
                                    <input id="invoice_prefix" name="invoice_prefix" value="{{ old('invoice_prefix', $store?->invoice_prefix) }}" />
                                </div>
                                <div class="field">
                                    <label for="currency">Currency</label>
                                    <input id="currency" name="currency" value="{{ old('currency', $store?->currency) }}" />
                                </div>
                                <div class="field">
                                    <label for="currency_symbol">Currency Symbol</label>
                                    <input id="currency_symbol" name="currency_symbol" value="{{ old('currency_symbol', $store?->currency_symbol) }}" />
                                </div>
                                <div class="field">
                                    <label for="timezone">Timezone</label>
                                    <input id="timezone" name="timezone" value="{{ old('timezone', $store?->timezone) }}" />
                                </div>
                                <div class="field">
                                    <label for="language">Language</label>
                                    <input id="language" name="language" value="{{ old('language', $store?->language) }}" />
                                </div>
                                <div class="field">
                                    <label for="address_line1">Address Line 1</label>
                                    <input id="address_line1" name="address_line1" value="{{ old('address_line1', $store?->address_line1) }}" />
                                </div>
                                <div class="field">
                                    <label for="address_line2">Address Line 2</label>
                                    <input id="address_line2" name="address_line2" value="{{ old('address_line2', $store?->address_line2) }}" />
                                </div>
                                <div class="field">
                                    <label for="city">City</label>
                                    <input id="city" name="city" value="{{ old('city', $store?->city) }}" />
                                </div>
                                <div class="field">
                                    <label for="state">State</label>
                                    <input id="state" name="state" value="{{ old('state', $store?->state) }}" />
                                </div>
                                <div class="field">
                                    <label for="pincode">Pincode</label>
                                    <input id="pincode" name="pincode" value="{{ old('pincode', $store?->pincode) }}" />
                                </div>
                                <div class="field">
                                    <label for="country">Country</label>
                                    <input id="country" name="country" value="{{ old('country', $store?->country) }}" />
                                </div>
                            </div>
                            <div class="form-grid one">
                                <div class="field">
                                    <label for="invoice_footer_note">Invoice Footer Note</label>
                                    <textarea id="invoice_footer_note" name="invoice_footer_note">{{ old('invoice_footer_note', $store?->invoice_footer_note) }}</textarea>
                                </div>
                                <div class="field">
                                    <label for="topbar_offers_text">Topbar Offers</label>
                                    <textarea id="topbar_offers_text" name="topbar_offers_text" placeholder="One offer per line">{{ old('topbar_offers_text', $topbarOffersText ?? '') }}</textarea>
                                    <small style="display:block;margin-top:8px;color:rgba(25,25,25,.58);">Add one offer per line. The storefront will rotate them like a slider.</small>
                                </div>
                                <div class="field">
                                    <label for="footer_copyright_text">Footer Copyright Text</label>
                                    <input id="footer_copyright_text" name="footer_copyright_text" value="{{ old('footer_copyright_text', $store?->footer_copyright_text) }}" placeholder="© Little Divinity. All rights reserved to Tadpole Story LLP." />
                                </div>
                                <div class="checkbox-row">
                                    <input id="show_logo_on_invoice" type="checkbox" name="show_logo_on_invoice" value="1" @checked(old('show_logo_on_invoice', $store?->show_logo_on_invoice))>
                                    <label for="show_logo_on_invoice" style="margin:0;">Show logo on invoices</label>
                                </div>
                                <div class="field">
                                    <label for="return_policy">Return Policy</label>
                                    <textarea id="return_policy" name="return_policy">{{ old('return_policy', $store?->return_policy) }}</textarea>
                                </div>
                                <div class="field">
                                    <label for="privacy_policy">Privacy Policy</label>
                                    <textarea id="privacy_policy" name="privacy_policy">{{ old('privacy_policy', $store?->privacy_policy) }}</textarea>
                                </div>
                                <div class="field">
                                    <label for="terms_conditions">Terms & Conditions</label>
                                    <textarea id="terms_conditions" name="terms_conditions">{{ old('terms_conditions', $store?->terms_conditions) }}</textarea>
                                </div>
                            </div>
                            <div class="button-row">
                                <button type="submit" class="button small">Save Store Settings</button>
                            </div>
                        </form>
                    </section>

                    <div class="split-grid">
                        <section class="panel">
                            <h3>Payment Gateways</h3>
                            <p>Enable, test, and configure COD, Razorpay, Paytm, and PhonePe directly from admin.</p>
                            @foreach ($paymentGateways as $gateway)
                                <form method="POST" action="{{ route('admin.settings.gateway.update', $gateway) }}" class="panel" style="margin-top: 16px; padding: 18px;">
                                    @csrf
                                    @method('PUT')
                                    <div class="button-row" style="justify-content: space-between; align-items: center;">
                                        <h4 style="margin:0;">{{ $gateway->display_name }}</h4>
                                        <span class="pill">{{ $gateway->provider }}</span>
                                    </div>
                                    <div class="form-grid" style="margin-top: 16px;">
                                        <div class="field"><label>Display Name</label><input name="display_name" value="{{ old('display_name', $gateway->display_name) }}" /></div>
                                        <div class="field"><label>Merchant ID</label><input name="merchant_id" value="{{ old('merchant_id', $gateway->merchant_id) }}" /></div>
                                        <div class="field"><label>Public Key</label><input name="public_key" value="{{ old('public_key', $gateway->public_key) }}" /></div>
                                        <div class="field"><label>Secret Key</label><input name="secret_key" type="password" value="{{ old('secret_key', $gateway->secret_key) }}" /></div>
                                        <div class="field"><label>Secondary Secret</label><input name="secret_key_secondary" type="password" value="{{ old('secret_key_secondary', $gateway->secret_key_secondary) }}" /></div>
                                        <div class="field"><label>Webhook Secret</label><input name="webhook_secret" type="password" value="{{ old('webhook_secret', $gateway->webhook_secret) }}" /></div>
                                        <div class="field"><label>Sort Order</label><input name="sort_order" value="{{ old('sort_order', $gateway->sort_order) }}" /></div>
                                    </div>
                                    <div class="button-row">
                                        <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $gateway->is_active))> <span>Active</span></label>
                                        <label class="checkbox-row"><input type="checkbox" name="is_test_mode" value="1" @checked(old('is_test_mode', $gateway->is_test_mode))> <span>Test mode</span></label>
                                        <button type="submit" class="button small">Save Gateway</button>
                                    </div>
                                </form>
                            @endforeach
                        </section>

                        <section class="panel">
                            <h3>Delivery Partners</h3>
                            <p>Manage shipment partners, pickup details, and tracking templates from here.</p>
                            @foreach ($deliveryPartners as $partner)
                                <form method="POST" action="{{ route('admin.settings.delivery.update', $partner) }}" class="panel" style="margin-top: 16px; padding: 18px;">
                                    @csrf
                                    @method('PUT')
                                    <div class="button-row" style="justify-content: space-between; align-items: center;">
                                        <h4 style="margin:0;">{{ $partner->name }}</h4>
                                        @if ($partner->is_default)
                                            <span class="pill">Default</span>
                                        @endif
                                    </div>
                                    <div class="form-grid" style="margin-top: 16px;">
                                        <div class="field"><label>Name</label><input name="name" value="{{ old('name', $partner->name) }}" /></div>
                                        <div class="field"><label>Contact Person</label><input name="contact_person" value="{{ old('contact_person', $partner->contact_person) }}" /></div>
                                        <div class="field"><label>Contact Phone</label><input name="contact_phone" value="{{ old('contact_phone', $partner->contact_phone) }}" /></div>
                                        <div class="field"><label>Contact Email</label><input name="contact_email" value="{{ old('contact_email', $partner->contact_email) }}" /></div>
                                        <div class="field"><label>API Key</label><input name="api_key" value="{{ old('api_key', $partner->api_key) }}" /></div>
                                        <div class="field"><label>API Secret</label><input type="password" name="api_secret" value="{{ old('api_secret', $partner->api_secret) }}" /></div>
                                        <div class="field"><label>Account Number</label><input name="account_number" value="{{ old('account_number', $partner->account_number) }}" /></div>
                                        <div class="field"><label>Pickup Location</label><input name="pickup_location" value="{{ old('pickup_location', $partner->pickup_location) }}" /></div>
                                        <div class="field"><label>Tracking URL Template</label><input name="tracking_url_template" value="{{ old('tracking_url_template', $partner->tracking_url_template) }}" /></div>
                                    </div>
                                    <div class="button-row">
                                        <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $partner->is_active))> <span>Active</span></label>
                                        <label class="checkbox-row"><input type="checkbox" name="is_default" value="1" @checked(old('is_default', $partner->is_default))> <span>Default partner</span></label>
                                        <button type="submit" class="button small">Save Delivery Partner</button>
                                    </div>
                                </form>
                            @endforeach
                        </section>
                    </div>
                </div>
            </div>
        </main>
    </div>
@endsection
