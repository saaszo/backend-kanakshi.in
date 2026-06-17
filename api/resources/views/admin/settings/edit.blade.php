@extends('admin.layout')

@section('title', 'Store Settings')

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')

        <main class="admin-main">
            <div class="admin-shell-grid">
                <style>
                    .settings-grid {
                        display: grid;
                        gap: 18px;
                    }

                    .settings-actions {
                        display: flex;
                        justify-content: flex-end;
                        margin-top: 12px;
                    }

                    .field.has-error input,
                    .field.has-error textarea,
                    .field.has-error select {
                        border-color: #dc2626;
                        box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.12);
                    }

                    .field-error {
                        margin-top: 8px;
                        font-size: 12px;
                        color: #b91c1c;
                    }

                    .setting-note {
                        margin: -4px 0 10px;
                        color: rgba(25, 25, 25, 0.62);
                        font-size: 13px;
                    }
                </style>
                <div class="admin-banner">
                    <div>
                        <div class="brand">Configuration</div>
                        <h2>Store Settings</h2>
                        <p class="lead" style="margin-top:8px;">Manage brand details, storefront identity, delivery setup, and payment gateways from one place.</p>
                    </div>
                </div>

                @if (session('status'))
                    <div class="admin-toast">
    <div>
        <strong>Success!</strong>
        <p>{{ session('status') }}</p>
    </div>
</div>
                @endif
                @if ($errors->any())
                    <div class="admin-errors">
                        <strong>Please fix the highlighted fields.</strong>
                        <div style="margin-top:8px;font-size:13px;">
                            {{ $errors->first() }}
                        </div>
                    </div>
                @endif

                <div class="admin-fields">
                    <div class="settings-grid">
                        <section class="admin-section">
                            <h3>Brand Basics</h3>
                            <p>Site name and core brand details.</p>
                            <form method="POST" action="{{ route('admin.settings.store.update') }}">
                                @csrf
                                @method('PUT')
                                <div class="form-grid">
                                    <div @class(['field', 'has-error' => $errors->has('site_name')])>
                                        <label for="site_name">Site Name</label>
                                        <input id="site_name" name="site_name" value="{{ old('site_name', $store?->site_name) }}" />
                                        @error('site_name')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                    <div @class(['field', 'has-error' => $errors->has('site_tagline')])>
                                        <label for="site_tagline">Tagline</label>
                                        <input id="site_tagline" name="site_tagline" value="{{ old('site_tagline', $store?->site_tagline) }}" />
                                        @error('site_tagline')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                    <div @class(['field', 'has-error' => $errors->has('business_name')])>
                                        <label for="business_name">Business Name</label>
                                        <input id="business_name" name="business_name" value="{{ old('business_name', $store?->business_name) }}" />
                                        @error('business_name')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="settings-actions">
                                    <button type="submit" class="button small">Update Brand Basics</button>
                                </div>
                            </form>
                        </section>

                        <section class="admin-section">
                            <h3>Brand Media</h3>
                            <p>Logo aur favicon ko alag se update karo.</p>
                            <div class="form-grid">
                                <form method="POST" action="{{ route('admin.settings.store.update') }}" enctype="multipart/form-data" class="admin-section" style="padding:18px;">
                                    @csrf
                                    @method('PUT')
                                    <div @class(['field', 'has-error' => $errors->has('logo_file')])>
                                        <label for="logo_file">Upload Logo</label>
                                        @if ($store?->logo_url)
                                            <img src="{{ $store->logo_url }}" alt="Logo" class="admin-upload-preview" style="margin-top:10px;" />
                                        @endif
                                        <div class="setting-note">Supported files: PNG, JPG, JPEG, SVG, WEBP, ICO.</div>
                                        <input type="file" id="logo_file" name="logo_file" accept=".png,.jpg,.jpeg,.svg,.webp,.ico,image/png,image/jpeg,image/svg+xml,image/webp,image/x-icon" style="margin-top:10px;" />
                                        @error('logo_file')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="settings-actions">
                                        <button type="submit" class="button small">Update Logo</button>
                                    </div>
                                </form>

                                <form method="POST" action="{{ route('admin.settings.store.update') }}" enctype="multipart/form-data" class="admin-section" style="padding:18px;">
                                    @csrf
                                    @method('PUT')
                                    <div @class(['field', 'has-error' => $errors->has('favicon_file')])>
                                        <label for="favicon_file">Upload Favicon</label>
                                        @if ($store?->favicon_url)
                                            <img src="{{ $store->favicon_url }}" alt="Favicon" class="admin-upload-preview admin-upload-preview--small" style="margin-top:10px;" />
                                        @endif
                                        <div class="setting-note">Supported files: PNG, JPG, JPEG, SVG, WEBP, ICO.</div>
                                        <input type="file" id="favicon_file" name="favicon_file" accept=".png,.jpg,.jpeg,.svg,.webp,.ico,image/png,image/jpeg,image/svg+xml,image/webp,image/x-icon" style="margin-top:10px;" />
                                        @error('favicon_file')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="settings-actions">
                                        <button type="submit" class="button small">Update Favicon</button>
                                    </div>
                                </form>
                            </div>
                        </section>

                        <section class="admin-section">
                            <h3>Tracking & Campaign</h3>
                            <p>Domain, campaign and marketing snippets.</p>
                            <form method="POST" action="{{ route('admin.settings.store.update') }}">
                                @csrf
                                @method('PUT')
                                <div class="form-grid">
                                    <div @class(['field', 'has-error' => $errors->has('custom_domain')])>
                                        <label for="custom_domain">Custom Domain</label>
                                        <input id="custom_domain" name="custom_domain" value="{{ old('custom_domain', $store?->custom_domain) }}" />
                                        @error('custom_domain')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                    <div @class(['field', 'has-error' => $errors->has('google_tag_manager_id')])>
                                        <label for="google_tag_manager_id">Google Tag Manager ID</label>
                                        <input id="google_tag_manager_id" name="google_tag_manager_id" value="{{ old('google_tag_manager_id', $store?->google_tag_manager_id) }}" placeholder="GTM-XXXXXXX" />
                                        @error('google_tag_manager_id')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                    <div @class(['field', 'has-error' => $errors->has('facebook_pixel_id')])>
                                        <label for="facebook_pixel_id">Facebook Pixel ID</label>
                                        <input id="facebook_pixel_id" name="facebook_pixel_id" value="{{ old('facebook_pixel_id', $store?->facebook_pixel_id) }}" placeholder="123456789012345" />
                                        @error('facebook_pixel_id')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                    <div @class(['field', 'has-error' => $errors->has('seasonal_campaign_name')])>
                                        <label for="seasonal_campaign_name">Occasion / Campaign Name</label>
                                        <input id="seasonal_campaign_name" name="seasonal_campaign_name" value="{{ old('seasonal_campaign_name', $store?->seasonal_campaign_name) }}" placeholder="Diwali 2026, Wedding Edit, Navratri Collection" />
                                        @error('seasonal_campaign_name')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="settings-actions">
                                    <button type="submit" class="button small">Update Tracking</button>
                                </div>
                            </form>
                        </section>

                        <section class="admin-section">
                            <h3>Contact Details</h3>
                            <p>Customer-facing email, phone and WhatsApp.</p>
                            <form method="POST" action="{{ route('admin.settings.store.update') }}">
                                @csrf
                                @method('PUT')
                                <div class="form-grid">
                                    <div @class(['field', 'has-error' => $errors->has('business_email')])><label for="business_email">Business Email</label><input id="business_email" name="business_email" type="email" value="{{ old('business_email', $store?->business_email) }}" />@error('business_email')<div class="field-error">{{ $message }}</div>@enderror</div>
                                    <div @class(['field', 'has-error' => $errors->has('support_email')])><label for="support_email">Support Email</label><input id="support_email" name="support_email" type="email" value="{{ old('support_email', $store?->support_email) }}" />@error('support_email')<div class="field-error">{{ $message }}</div>@enderror</div>
                                    <div @class(['field', 'has-error' => $errors->has('business_phone')])><label for="business_phone">Business Phone</label><input id="business_phone" name="business_phone" value="{{ old('business_phone', $store?->business_phone) }}" />@error('business_phone')<div class="field-error">{{ $message }}</div>@enderror</div>
                                    <div @class(['field', 'has-error' => $errors->has('support_phone')])><label for="support_phone">Support Phone</label><input id="support_phone" name="support_phone" value="{{ old('support_phone', $store?->support_phone) }}" />@error('support_phone')<div class="field-error">{{ $message }}</div>@enderror</div>
                                    <div @class(['field', 'has-error' => $errors->has('whatsapp_number')])><label for="whatsapp_number">WhatsApp Number</label><input id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number', $store?->whatsapp_number) }}" />@error('whatsapp_number')<div class="field-error">{{ $message }}</div>@enderror</div>
                                </div>
                                <div class="settings-actions">
                                    <button type="submit" class="button small">Update Contact</button>
                                </div>
                            </form>
                        </section>

                        <section class="admin-section">
                            <h3>Top Offer Bar</h3>
                            <p>Top bar ko on/off aur offers ko alag se manage karo.</p>
                            <form method="POST" action="{{ route('admin.settings.store.update') }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="show_topbar" value="0">
                                <div class="checkbox-row" style="margin-bottom:16px;">
                                    <input id="show_topbar" type="checkbox" name="show_topbar" value="1" @checked(old('show_topbar', $store?->show_topbar))>
                                    <label for="show_topbar" style="margin:0;">Enable top offer bar</label>
                                </div>
                                <div class="form-grid">
                                    <div @class(['field', 'has-error' => $errors->has('topbar_bg_color')])><label for="topbar_bg_color">Topbar Background Color</label><input id="topbar_bg_color" name="topbar_bg_color" value="{{ old('topbar_bg_color', $store?->topbar_bg_color) }}" placeholder="#0f0f0f" />@error('topbar_bg_color')<div class="field-error">{{ $message }}</div>@enderror</div>
                                    <div @class(['field', 'has-error' => $errors->has('topbar_text_color')])><label for="topbar_text_color">Topbar Text Color</label><input id="topbar_text_color" name="topbar_text_color" value="{{ old('topbar_text_color', $store?->topbar_text_color) }}" placeholder="#ffffff" />@error('topbar_text_color')<div class="field-error">{{ $message }}</div>@enderror</div>
                                </div>
                                <div @class(['field', 'has-error' => $errors->has('topbar_offers_text')])>
                                    <label for="topbar_offers_text">Topbar Offers</label>
                                    <textarea id="topbar_offers_text" name="topbar_offers_text" placeholder="One offer per line">{{ old('topbar_offers_text', $topbarOffersText ?? '') }}</textarea>
                                    <div class="setting-note">Add one offer per line. The storefront will rotate them like a slider.</div>
                                    @error('topbar_offers_text')<div class="field-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="settings-actions">
                                    <button type="submit" class="button small">Update Top Bar</button>
                                </div>
                            </form>
                        </section>

                        <section class="admin-section">
                            <h3>Locale & Billing</h3>
                            <p>Currency, invoice prefix and store locale.</p>
                            <form method="POST" action="{{ route('admin.settings.store.update') }}">
                                @csrf
                                @method('PUT')
                                <div class="form-grid">
                                    <div @class(['field', 'has-error' => $errors->has('invoice_prefix')])><label for="invoice_prefix">Invoice Prefix</label><input id="invoice_prefix" name="invoice_prefix" value="{{ old('invoice_prefix', $store?->invoice_prefix) }}" />@error('invoice_prefix')<div class="field-error">{{ $message }}</div>@enderror</div>
                                    <div @class(['field', 'has-error' => $errors->has('currency')])><label for="currency">Currency</label><input id="currency" name="currency" value="{{ old('currency', $store?->currency) }}" />@error('currency')<div class="field-error">{{ $message }}</div>@enderror</div>
                                    <div @class(['field', 'has-error' => $errors->has('currency_symbol')])><label for="currency_symbol">Currency Symbol</label><input id="currency_symbol" name="currency_symbol" value="{{ old('currency_symbol', $store?->currency_symbol) }}" />@error('currency_symbol')<div class="field-error">{{ $message }}</div>@enderror</div>
                                    <div @class(['field', 'has-error' => $errors->has('timezone')])><label for="timezone">Timezone</label><input id="timezone" name="timezone" value="{{ old('timezone', $store?->timezone) }}" />@error('timezone')<div class="field-error">{{ $message }}</div>@enderror</div>
                                    <div @class(['field', 'has-error' => $errors->has('language')])><label for="language">Language</label><input id="language" name="language" value="{{ old('language', $store?->language) }}" />@error('language')<div class="field-error">{{ $message }}</div>@enderror</div>
                                    <div @class(['field', 'has-error' => $errors->has('country')])><label for="country">Country</label><input id="country" name="country" value="{{ old('country', $store?->country) }}" />@error('country')<div class="field-error">{{ $message }}</div>@enderror</div>
                                </div>
                                <div class="settings-actions">
                                    <button type="submit" class="button small">Update Locale & Billing</button>
                                </div>
                            </form>
                        </section>

                        <section class="admin-section">
                            <h3>Address</h3>
                            <p>Office or billing address details.</p>
                            <form method="POST" action="{{ route('admin.settings.store.update') }}">
                                @csrf
                                @method('PUT')
                                <div class="form-grid">
                                    <div @class(['field', 'has-error' => $errors->has('address_line1')])><label for="address_line1">Address Line 1</label><input id="address_line1" name="address_line1" value="{{ old('address_line1', $store?->address_line1) }}" />@error('address_line1')<div class="field-error">{{ $message }}</div>@enderror</div>
                                    <div @class(['field', 'has-error' => $errors->has('address_line2')])><label for="address_line2">Address Line 2</label><input id="address_line2" name="address_line2" value="{{ old('address_line2', $store?->address_line2) }}" />@error('address_line2')<div class="field-error">{{ $message }}</div>@enderror</div>
                                    <div @class(['field', 'has-error' => $errors->has('city')])><label for="city">City</label><input id="city" name="city" value="{{ old('city', $store?->city) }}" />@error('city')<div class="field-error">{{ $message }}</div>@enderror</div>
                                    <div @class(['field', 'has-error' => $errors->has('state')])><label for="state">State</label><input id="state" name="state" value="{{ old('state', $store?->state) }}" />@error('state')<div class="field-error">{{ $message }}</div>@enderror</div>
                                    <div @class(['field', 'has-error' => $errors->has('pincode')])><label for="pincode">Pincode</label><input id="pincode" name="pincode" value="{{ old('pincode', $store?->pincode) }}" />@error('pincode')<div class="field-error">{{ $message }}</div>@enderror</div>
                                </div>
                                <div class="settings-actions">
                                    <button type="submit" class="button small">Update Address</button>
                                </div>
                            </form>
                        </section>

                        <section class="admin-section">
                            <h3>Invoice & Footer</h3>
                            <p>Invoice note, footer text and invoice logo visibility.</p>
                            <form method="POST" action="{{ route('admin.settings.store.update') }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="show_logo_on_invoice" value="0">
                                <div class="checkbox-row" style="margin-bottom:16px;">
                                    <input id="show_logo_on_invoice" type="checkbox" name="show_logo_on_invoice" value="1" @checked(old('show_logo_on_invoice', $store?->show_logo_on_invoice))>
                                    <label for="show_logo_on_invoice" style="margin:0;">Show logo on invoices</label>
                                </div>
                                <div class="form-grid one">
                                    <div @class(['field', 'has-error' => $errors->has('invoice_footer_note')])><label for="invoice_footer_note">Invoice Footer Note</label><textarea id="invoice_footer_note" name="invoice_footer_note">{{ old('invoice_footer_note', $store?->invoice_footer_note) }}</textarea>@error('invoice_footer_note')<div class="field-error">{{ $message }}</div>@enderror</div>
                                    <div @class(['field', 'has-error' => $errors->has('footer_copyright_text')])><label for="footer_copyright_text">Footer Copyright Text</label><input id="footer_copyright_text" name="footer_copyright_text" value="{{ old('footer_copyright_text', $store?->footer_copyright_text) }}" placeholder="© Kanakshi.in. All rights reserved to Tadpole Story LLP." />@error('footer_copyright_text')<div class="field-error">{{ $message }}</div>@enderror</div>
                                </div>
                                <div class="settings-actions">
                                    <button type="submit" class="button small">Update Invoice & Footer</button>
                                </div>
                            </form>
                        </section>

                        <section class="admin-section">
                            <h3>SEO Defaults</h3>
                            <p>Homepage and general search metadata.</p>
                            <form method="POST" action="{{ route('admin.settings.store.update') }}">
                                @csrf
                                @method('PUT')
                                <div class="form-grid one">
                                    <div @class(['field', 'has-error' => $errors->has('meta_title')])><label for="meta_title">Default Meta Title</label><input id="meta_title" name="meta_title" value="{{ old('meta_title', $store?->meta_title) }}" placeholder="Kanakshi.in | Handcrafted Brass Decor & Gifting" />@error('meta_title')<div class="field-error">{{ $message }}</div>@enderror</div>
                                    <div @class(['field', 'has-error' => $errors->has('meta_description')])><label for="meta_description">Default Meta Description</label><textarea id="meta_description" name="meta_description" placeholder="Store-wide SEO description for homepage and main social previews.">{{ old('meta_description', $store?->meta_description) }}</textarea>@error('meta_description')<div class="field-error">{{ $message }}</div>@enderror</div>
                                </div>
                                <div class="settings-actions">
                                    <button type="submit" class="button small">Update SEO Defaults</button>
                                </div>
                            </form>
                        </section>

                        <section class="admin-section">
                            <h3>Open Graph</h3>
                            <p>WhatsApp, Facebook and LinkedIn share preview.</p>
                            <form method="POST" action="{{ route('admin.settings.store.update') }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="form-grid">
                                    <div @class(['field', 'has-error' => $errors->has('og_title')])><label for="og_title">Open Graph Title</label><input id="og_title" name="og_title" value="{{ old('og_title', $store?->og_title) }}" placeholder="Share preview title for Facebook / WhatsApp / LinkedIn" />@error('og_title')<div class="field-error">{{ $message }}</div>@enderror</div>
                                    <div @class(['field', 'has-error' => $errors->has('og_image') || $errors->has('og_image_file')])>
                                        <label for="og_image">Open Graph Image URL</label>
                                        <input id="og_image" name="og_image" value="{{ old('og_image', $store?->og_image) }}" placeholder="/branding/og-diwali.jpg" />
                                        @if ($store?->og_image)
                                            <img src="{{ $store->og_image }}" alt="Open Graph image" class="admin-upload-preview" style="margin-top:10px;" />
                                        @endif
                                        <div class="setting-note">Supported files: PNG, JPG, JPEG, SVG, WEBP, ICO.</div>
                                        <input type="file" id="og_image_file" name="og_image_file" accept=".png,.jpg,.jpeg,.svg,.webp,.ico,image/png,image/jpeg,image/svg+xml,image/webp,image/x-icon" style="margin-top:10px;" />
                                        @error('og_image')<div class="field-error">{{ $message }}</div>@enderror
                                        @error('og_image_file')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                    <div @class(['field', 'has-error' => $errors->has('og_description')]) style="grid-column:1 / -1;"><label for="og_description">Open Graph Description</label><textarea id="og_description" name="og_description" placeholder="Occasion-specific share description for social apps.">{{ old('og_description', $store?->og_description) }}</textarea>@error('og_description')<div class="field-error">{{ $message }}</div>@enderror</div>
                                </div>
                                <div class="settings-actions">
                                    <button type="submit" class="button small">Update Open Graph</button>
                                </div>
                            </form>
                        </section>

                        <section class="admin-section">
                            <h3>Twitter / X</h3>
                            <p>Twitter card content and image.</p>
                            <form method="POST" action="{{ route('admin.settings.store.update') }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="form-grid">
                                    <div @class(['field', 'has-error' => $errors->has('twitter_title')])><label for="twitter_title">Twitter / X Title</label><input id="twitter_title" name="twitter_title" value="{{ old('twitter_title', $store?->twitter_title) }}" placeholder="Card title for Twitter / X shares" />@error('twitter_title')<div class="field-error">{{ $message }}</div>@enderror</div>
                                    <div @class(['field', 'has-error' => $errors->has('twitter_handle')])><label for="twitter_handle">Twitter / X Handle</label><input id="twitter_handle" name="twitter_handle" value="{{ old('twitter_handle', $store?->twitter_handle) }}" placeholder="@kanakshi.in" />@error('twitter_handle')<div class="field-error">{{ $message }}</div>@enderror</div>
                                    <div @class(['field', 'has-error' => $errors->has('twitter_image') || $errors->has('twitter_image_file')])>
                                        <label for="twitter_image">Twitter / X Image URL</label>
                                        <input id="twitter_image" name="twitter_image" value="{{ old('twitter_image', $store?->twitter_image) }}" placeholder="/branding/twitter-festive-card.jpg" />
                                        @if ($store?->twitter_image)
                                            <img src="{{ $store->twitter_image }}" alt="Twitter image" class="admin-upload-preview" style="margin-top:10px;" />
                                        @endif
                                        <div class="setting-note">Supported files: PNG, JPG, JPEG, SVG, WEBP, ICO.</div>
                                        <input type="file" id="twitter_image_file" name="twitter_image_file" accept=".png,.jpg,.jpeg,.svg,.webp,.ico,image/png,image/jpeg,image/svg+xml,image/webp,image/x-icon" style="margin-top:10px;" />
                                        @error('twitter_image')<div class="field-error">{{ $message }}</div>@enderror
                                        @error('twitter_image_file')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                    <div @class(['field', 'has-error' => $errors->has('twitter_description')]) style="grid-column:1 / -1;"><label for="twitter_description">Twitter / X Description</label><textarea id="twitter_description" name="twitter_description" placeholder="Dedicated description for Twitter / X card previews.">{{ old('twitter_description', $store?->twitter_description) }}</textarea>@error('twitter_description')<div class="field-error">{{ $message }}</div>@enderror</div>
                                </div>
                                <div class="settings-actions">
                                    <button type="submit" class="button small">Update Twitter / X</button>
                                </div>
                            </form>
                        </section>

                        <section class="admin-section">
                            <h3>Policies</h3>
                            <p>Return, privacy and terms text.</p>
                            <form method="POST" action="{{ route('admin.settings.store.update') }}">
                                @csrf
                                @method('PUT')
                                <div class="form-grid one">
                                    <div @class(['field', 'has-error' => $errors->has('return_policy')])><label for="return_policy">Return Policy</label><textarea id="return_policy" name="return_policy">{{ old('return_policy', $store?->return_policy) }}</textarea>@error('return_policy')<div class="field-error">{{ $message }}</div>@enderror</div>
                                    <div @class(['field', 'has-error' => $errors->has('privacy_policy')])><label for="privacy_policy">Privacy Policy</label><textarea id="privacy_policy" name="privacy_policy">{{ old('privacy_policy', $store?->privacy_policy) }}</textarea>@error('privacy_policy')<div class="field-error">{{ $message }}</div>@enderror</div>
                                    <div @class(['field', 'has-error' => $errors->has('terms_conditions')])><label for="terms_conditions">Terms & Conditions</label><textarea id="terms_conditions" name="terms_conditions">{{ old('terms_conditions', $store?->terms_conditions) }}</textarea>@error('terms_conditions')<div class="field-error">{{ $message }}</div>@enderror</div>
                                </div>
                                <div class="settings-actions">
                                    <button type="submit" class="button small">Update Policies</button>
                                </div>
                            </form>
                        </section>

                        <section class="admin-section">
                            <h3>Custom Scripts</h3>
                            <p>Header and footer scripts ko independently save karo.</p>
                            <form method="POST" action="{{ route('admin.settings.store.update') }}">
                                @csrf
                                @method('PUT')
                                <div class="form-grid one">
                                    <div @class(['field', 'has-error' => $errors->has('custom_header_scripts')])>
                                        <label for="custom_header_scripts">Header Scripts</label>
                                        <textarea id="custom_header_scripts" name="custom_header_scripts" placeholder="<script>/* custom script */</script>">{{ old('custom_header_scripts', $store?->custom_header_scripts) }}</textarea>
                                        <div class="setting-note">Use this for snippets that should load near the top of the storefront body.</div>
                                        @error('custom_header_scripts')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                    <div @class(['field', 'has-error' => $errors->has('custom_footer_scripts')])>
                                        <label for="custom_footer_scripts">Footer Scripts</label>
                                        <textarea id="custom_footer_scripts" name="custom_footer_scripts" placeholder="<script>/* footer script */</script>">{{ old('custom_footer_scripts', $store?->custom_footer_scripts) }}</textarea>
                                        <div class="setting-note">Use this for chat widgets, tracking snippets, or code that should render near the end of the page.</div>
                                        @error('custom_footer_scripts')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="settings-actions">
                                    <button type="submit" class="button small">Update Scripts</button>
                                </div>
                            </form>
                        </section>
                    </div>

                    <div class="split-grid">
                        <section class="admin-section">
                            <h3>Payment Gateways</h3>
                            <p>Enable, test, and configure COD, Razorpay, Paytm, and PhonePe directly from admin.</p>
                            @foreach ($paymentGateways as $gateway)
                                <form method="POST" action="{{ route('admin.settings.gateway.update', $gateway) }}" class="admin-section" style="margin-top: 16px; padding: 18px;">
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
                                    <div class="field" style="margin-top:16px;">
                                        <label>Extra Config (JSON)</label>
                                        <textarea name="extra_config_text" placeholder='{"api_base_url":"https://api.phonepe.com/apis/pg","auth_base_url":"https://api.phonepe.com/apis/identity-manager"}'>{{ old('extra_config_text', is_array($gateway->extra_config) ? json_encode($gateway->extra_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</textarea>
                                        @if ($gateway->provider === 'phonepe')
                                            <small style="display:block;margin-top:8px;color:rgba(25,25,25,.58);">
                                                For PhonePe live mode use: <code>merchant_id</code> as Merchant ID, <code>public_key</code> as Client ID, <code>secret_key</code> as Client Secret, and <code>secret_key_secondary</code> as Client Version. Optional JSON can override <code>api_base_url</code> and <code>auth_base_url</code>.
                                            </small>
                                        @elseif ($gateway->provider === 'razorpay')
                                            <small style="display:block;margin-top:8px;color:rgba(25,25,25,.58);">
                                                Razorpay live mode needs Key ID in <code>public_key</code>, Key Secret in <code>secret_key</code>, and Webhook Secret in <code>webhook_secret</code>. Webhook URL: <code>{{ url('/api/v1/checkout/webhooks/razorpay') }}</code>. Orders from this storefront are sent with receipts like <code>KAN-20260525-ABCDE</code>.
                                            </small>
                                        @endif
                                    </div>
                                    <div class="button-row">
                                        <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $gateway->is_active))> <span>Active</span></label>
                                        <label class="checkbox-row"><input type="checkbox" name="is_test_mode" value="1" @checked(old('is_test_mode', $gateway->is_test_mode))> <span>Test mode</span></label>
                                        <button type="submit" class="button small">Save Gateway</button>
                                    </div>
                                </form>
                            @endforeach
                        </section>

                        <section class="admin-section">
                            <h3>Delivery Partners</h3>
                            <p>Manage shipment partners, pickup details, and tracking templates from here.</p>
                            @foreach ($deliveryPartners as $partner)
                                <form method="POST" action="{{ route('admin.settings.delivery.update', $partner) }}" class="admin-section" style="margin-top: 16px; padding: 18px;">
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
