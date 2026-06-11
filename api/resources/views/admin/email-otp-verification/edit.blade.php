@extends('admin.layout')

@section('title', 'Email & OTP Verification')

@php
    $providers = $providers ?? collect();
    $hasMobileProvider = $providers->where('is_active', true)->whereIn('channel', ['sms', 'whatsapp'])->isNotEmpty();
    $lockedMailRoutes = $lockedMailRoutes ?? [];
@endphp

@section('content')
    <div class="dashboard-shell">
        @include('admin.partials.sidebar')

        <main class="admin-main">
            <div class="admin-shell-grid">
                <div class="admin-banner">
                    <div>
                        <div class="brand">Customer Verification</div>
                        <h2>Email & OTP Verification</h2>
                        <p class="lead" style="margin-top:8px;">Manage only Little Divinity customer email delivery and verification rules here. Admin OTP and admin reset email settings stay hidden and backend-managed by Saaszo.</p>
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
                    </div>
                @endif

                <div class="admin-fields">
                    <section class="admin-section">
                        <h3>Customer Email Delivery</h3>
                        <p>Customer-facing mailboxes are fixed for Little Divinity. Admin OTP and admin reset mail stay on the separate Saaszo-managed mailbox and are not editable here.</p>
                        <form method="POST" action="{{ route('admin.email-otp.email.update') }}" class="admin-fields">
                            @csrf
                            @method('PUT')
                            <div class="form-grid">
                                <div class="field">
                                    <label>Customer Auth Sender</label>
                                    <input value="{{ ($lockedMailRoutes['auth']['name'] ?? 'Little Divinity') . ' <' . ($lockedMailRoutes['auth']['email'] ?? 'noreply@littledivinity.com') . '>' }}" readonly />
                                </div>
                                <div class="field">
                                    <label>Order Sender</label>
                                    <input value="{{ ($lockedMailRoutes['order']['name'] ?? 'Little Divinity Orders') . ' <' . ($lockedMailRoutes['order']['email'] ?? 'order@littledivinity.com') . '>' }}" readonly />
                                </div>
                                <div class="field">
                                    <label>Admin OTP Sender</label>
                                    <input value="Saaszo managed mailbox only" readonly />
                                </div>
                            </div>
                            <p class="muted" style="margin:0;">This screen now controls only whether customer account emails and order updates are active. Mailbox identities and transport credentials remain backend-managed so the page can stay simple and safe.</p>
                            <div class="button-row" style="flex-wrap:wrap;">
                                <label class="checkbox-row"><input type="checkbox" name="send_account_creation_emails" value="1" @checked(old('send_account_creation_emails', $emailSettings?->send_account_creation_emails))> <span>Account creation emails</span></label>
                                <label class="checkbox-row"><input type="checkbox" name="send_email_verification_emails" value="1" @checked(old('send_email_verification_emails', $emailSettings?->send_email_verification_emails))> <span>Email verification emails</span></label>
                                <label class="checkbox-row"><input type="checkbox" name="send_password_reset_emails" value="1" @checked(old('send_password_reset_emails', $emailSettings?->send_password_reset_emails))> <span>Customer forgot password emails</span></label>
                                <label class="checkbox-row"><input type="checkbox" name="send_order_emails" value="1" @checked(old('send_order_emails', $emailSettings?->send_order_emails))> <span>Order emails</span></label>
                                <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $emailSettings?->is_active))> <span>Email delivery active</span></label>
                            </div>
                            <div class="button-row">
                                <button type="submit" class="button small">Save Customer Mail Toggles</button>
                            </div>
                        </form>
                    </section>

                    <section class="admin-section">
                        <h3>Verification Rules</h3>
                        <p>If no mobile OTP provider is active, mobile verification automatically stays off and customer verification falls back to email only.</p>
                        <form method="POST" action="{{ route('admin.email-otp.verification.update') }}" class="admin-fields">
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

                    <section class="admin-section">
                        <h3>Mobile OTP Providers</h3>
                        <p>Add API credentials for 2-3 providers so admin can enable SMS or WhatsApp OTP whenever Little Divinity wants mobile verification.</p>
                        @foreach ($providers as $provider)
                            <form method="POST" action="{{ route('admin.email-otp.provider.update', $provider) }}" class="admin-section" style="margin-top: 16px; padding: 18px;">
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
