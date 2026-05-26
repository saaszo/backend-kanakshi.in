@extends('admin.layout')

@section('title', 'Email & OTP Verification')

@php
    $providers = $providers ?? collect();
    $hasMobileProvider = $providers->where('is_active', true)->whereIn('channel', ['sms', 'whatsapp'])->isNotEmpty();
@endphp

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')

        <main class="admin-main">
            <div class="dashboard-card">
                <div class="page-head">
                    <div>
                        <div class="brand">Customer Verification</div>
                        <h2>Email & OTP Verification</h2>
                        <p class="lead" style="margin-top:8px;">Manage only Little Divinity customer email delivery and verification rules here. Admin OTP and admin reset email settings stay hidden and backend-managed by Saaszo.</p>
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
                        <h3>Customer Email Delivery</h3>
                        <p>Use the customer auth sender for signup, verification, and forgot password. Use the order sender for invoice, order confirmation, shipping, and delivery updates. Admin OTP stays on the separate Saaszo-managed mailbox.</p>
                        <form method="POST" action="{{ route('admin.email-otp.email.update') }}" class="section-grid">
                            @csrf
                            @method('PUT')
                            <h4 style="margin:0;">Customer Auth Sender</h4>
                            <div class="form-grid">
                                <div class="field"><label>From Name</label><input name="from_name" value="{{ old('from_name', $emailSettings?->from_name) }}" placeholder="Little Divinity" /></div>
                                <div class="field"><label>From Email</label><input type="email" name="from_email" value="{{ old('from_email', $emailSettings?->from_email) }}" placeholder="noreply@littledivinity.com" /></div>
                                <div class="field"><label>Reply To Email</label><input type="email" name="reply_to_email" value="{{ old('reply_to_email', $emailSettings?->reply_to_email) }}" placeholder="noreply@littledivinity.com" /></div>
                            </div>
                            <h4 style="margin:8px 0 0;">Shared SMTP</h4>
                            <div class="form-grid">
                                <div class="field"><label>SMTP Host</label><input name="smtp_host" value="{{ old('smtp_host', $emailSettings?->smtp_host) }}" placeholder="smtp.example.com" /></div>
                                <div class="field"><label>SMTP Port</label><input name="smtp_port" value="{{ old('smtp_port', $emailSettings?->smtp_port) }}" placeholder="465" /></div>
                                <div class="field"><label>SMTP Encryption</label><input name="smtp_encryption" value="{{ old('smtp_encryption', $emailSettings?->smtp_encryption) }}" placeholder="ssl / tls" /></div>
                            </div>
                            <h4 style="margin:8px 0 0;">Auth SMTP Login</h4>
                            <div class="form-grid">
                                <div class="field"><label>SMTP Username</label><input name="smtp_username" value="{{ old('smtp_username', $emailSettings?->smtp_username) }}" placeholder="noreply@littledivinity.com" /></div>
                                <div class="field"><label>SMTP Password</label><input type="password" name="smtp_password" value="{{ old('smtp_password') }}" placeholder="Saved password stays unchanged if left blank" /></div>
                            </div>
                            <h4 style="margin:8px 0 0;">Order Sender</h4>
                            <div class="form-grid">
                                <div class="field"><label>From Name</label><input name="order_from_name" value="{{ old('order_from_name', $emailSettings?->order_from_name) }}" placeholder="Little Divinity Orders" /></div>
                                <div class="field"><label>From Email</label><input type="email" name="order_from_email" value="{{ old('order_from_email', $emailSettings?->order_from_email) }}" placeholder="order@littledivinity.com" /></div>
                                <div class="field"><label>Reply To Email</label><input type="email" name="order_reply_to_email" value="{{ old('order_reply_to_email', $emailSettings?->order_reply_to_email) }}" placeholder="order@littledivinity.com" /></div>
                            </div>
                            <h4 style="margin:8px 0 0;">Order SMTP Login</h4>
                            <div class="form-grid">
                                <div class="field"><label>SMTP Username</label><input name="order_smtp_username" value="{{ old('order_smtp_username', $emailSettings?->order_smtp_username) }}" placeholder="order@littledivinity.com" /></div>
                                <div class="field"><label>SMTP Password</label><input type="password" name="order_smtp_password" value="{{ old('order_smtp_password') }}" placeholder="Leave blank to keep saved password" /></div>
                            </div>
                            <div class="button-row" style="flex-wrap:wrap;">
                                <label class="checkbox-row"><input type="checkbox" name="send_account_creation_emails" value="1" @checked(old('send_account_creation_emails', $emailSettings?->send_account_creation_emails))> <span>Account creation emails</span></label>
                                <label class="checkbox-row"><input type="checkbox" name="send_email_verification_emails" value="1" @checked(old('send_email_verification_emails', $emailSettings?->send_email_verification_emails))> <span>Email verification emails</span></label>
                                <label class="checkbox-row"><input type="checkbox" name="send_password_reset_emails" value="1" @checked(old('send_password_reset_emails', $emailSettings?->send_password_reset_emails))> <span>Customer forgot password emails</span></label>
                                <label class="checkbox-row"><input type="checkbox" name="send_order_emails" value="1" @checked(old('send_order_emails', $emailSettings?->send_order_emails))> <span>Order emails</span></label>
                                <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $emailSettings?->is_active))> <span>Email delivery active</span></label>
                            </div>
                            <div class="button-row">
                                <button type="submit" class="button small">Save Customer Mail Settings</button>
                            </div>
                        </form>
                    </section>

                    <section class="panel">
                        <h3>Verification Rules</h3>
                        <p>If no mobile OTP provider is active, mobile verification automatically stays off and customer verification falls back to email only.</p>
                        <form method="POST" action="{{ route('admin.email-otp.verification.update') }}" class="section-grid">
                            @csrf
                            @method('PUT')
                            <div class="form-grid">
                                <div class="field">
                                    <label>Default OTP Channel</label>
                                    <select name="default_otp_channel">
                                        <option value="email" @selected(old('default_otp_channel', $verificationSettings?->default_otp_channel) === 'email')>Email</option>
                                        <option value="sms" @selected(old('default_otp_channel', $verificationSettings?->default_otp_channel) === 'sms')>SMS</option>
                                        <option value="whatsapp" @selected(old('default_otp_channel', $verificationSettings?->default_otp_channel) === 'whatsapp')>WhatsApp</option>
                                    </select>
                                </div>
                                <div class="field"><label>OTP Length</label><input type="number" name="otp_length" min="4" max="8" value="{{ old('otp_length', $verificationSettings?->otp_length ?? 6) }}" /></div>
                                <div class="field"><label>OTP Expiry (minutes)</label><input type="number" name="otp_expiry_minutes" min="1" max="60" value="{{ old('otp_expiry_minutes', $verificationSettings?->otp_expiry_minutes ?? 10) }}" /></div>
                                <div class="field"><label>Resend Wait (seconds)</label><input type="number" name="resend_wait_seconds" min="15" max="600" value="{{ old('resend_wait_seconds', $verificationSettings?->resend_wait_seconds ?? 60) }}" /></div>
                            </div>
                            <div class="button-row" style="flex-wrap:wrap;">
                                <label class="checkbox-row"><input type="checkbox" name="email_verification_enabled" value="1" @checked(old('email_verification_enabled', $verificationSettings?->email_verification_enabled ?? true))> <span>Email verification enabled</span></label>
                                <label class="checkbox-row"><input type="checkbox" name="email_otp_enabled" value="1" @checked(old('email_otp_enabled', $verificationSettings?->email_otp_enabled ?? true))> <span>Email OTP enabled</span></label>
                                <label class="checkbox-row"><input type="checkbox" name="mobile_verification_enabled" value="1" @checked(old('mobile_verification_enabled', $verificationSettings?->mobile_verification_enabled))> <span>Mobile verification enabled</span></label>
                                <label class="checkbox-row"><input type="checkbox" name="sms_otp_enabled" value="1" @checked(old('sms_otp_enabled', $verificationSettings?->sms_otp_enabled))> <span>SMS OTP enabled</span></label>
                                <label class="checkbox-row"><input type="checkbox" name="whatsapp_otp_enabled" value="1" @checked(old('whatsapp_otp_enabled', $verificationSettings?->whatsapp_otp_enabled))> <span>WhatsApp OTP enabled</span></label>
                            </div>
                            @unless($hasMobileProvider)
                                <p class="muted" style="margin:0;">No active mobile OTP provider is configured yet, so mobile verification will stay off even if these toggles are checked.</p>
                            @endunless
                            <div class="button-row">
                                <button type="submit" class="button small">Save Verification Rules</button>
                            </div>
                        </form>
                    </section>

                    <section class="panel">
                        <h3>Mobile OTP Providers</h3>
                        <p>Add API credentials for 2-3 providers so admin can enable SMS or WhatsApp OTP whenever Little Divinity wants mobile verification.</p>
                        @foreach ($providers as $provider)
                            <form method="POST" action="{{ route('admin.email-otp.provider.update', $provider) }}" class="panel" style="margin-top: 16px; padding: 18px;">
                                @csrf
                                @method('PUT')
                                <div class="button-row" style="justify-content: space-between; align-items: center;">
                                    <h4 style="margin:0;">{{ $provider->display_name }}</h4>
                                    <span class="pill">{{ strtoupper($provider->channel) }}</span>
                                </div>
                                <div class="form-grid" style="margin-top: 16px;">
                                    <div class="field"><label>Display Name</label><input name="display_name" value="{{ old('display_name', $provider->display_name) }}" /></div>
                                    <div class="field">
                                        <label>Channel</label>
                                        <select name="channel">
                                            <option value="sms" @selected(old('channel', $provider->channel) === 'sms')>SMS</option>
                                            <option value="whatsapp" @selected(old('channel', $provider->channel) === 'whatsapp')>WhatsApp</option>
                                            <option value="voice" @selected(old('channel', $provider->channel) === 'voice')>Voice</option>
                                        </select>
                                    </div>
                                    <div class="field"><label>API Key</label><input name="api_key" value="{{ old('api_key', $provider->api_key) }}" /></div>
                                    <div class="field"><label>API Secret</label><input type="password" name="api_secret" value="{{ old('api_secret') }}" placeholder="Leave blank to keep saved secret" /></div>
                                    <div class="field"><label>Sender ID</label><input name="sender_id" value="{{ old('sender_id', $provider->sender_id) }}" /></div>
                                    <div class="field"><label>Template ID</label><input name="template_id" value="{{ old('template_id', $provider->template_id) }}" /></div>
                                    <div class="field"><label>Base URL</label><input name="base_url" value="{{ old('base_url', $provider->base_url) }}" /></div>
                                </div>
                                <div class="field">
                                    <label>Extra Config</label>
                                    <textarea name="extra_config_text" placeholder="Optional notes, JSON, callback params, route names, etc.">{{ old('extra_config_text', is_array($provider->extra_config) ? ($provider->extra_config['raw'] ?? '') : '') }}</textarea>
                                </div>
                                <div class="button-row">
                                    <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $provider->is_active))> <span>Provider active</span></label>
                                    <label class="checkbox-row"><input type="checkbox" name="is_default" value="1" @checked(old('is_default', $provider->is_default))> <span>Default provider</span></label>
                                    <button type="submit" class="button small">Save Provider</button>
                                </div>
                            </form>
                        @endforeach
                    </section>
                </div>
            </div>
        </main>
    </div>
@endsection
