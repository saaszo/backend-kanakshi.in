<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerEmailSetting;
use App\Models\OtpProviderSetting;
use App\Models\OtpVerificationSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailOtpVerificationController extends Controller
{
    public function edit(): View
    {
        return view('admin.email-otp-verification.edit', [
            'emailSettings' => CustomerEmailSetting::query()->first(),
            'verificationSettings' => OtpVerificationSetting::query()->first(),
            'providers' => OtpProviderSetting::query()->orderByDesc('is_default')->orderBy('display_name')->get(),
        ]);
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $existing = CustomerEmailSetting::query()->first();
        $validated = $request->validate([
            'from_name' => ['nullable', 'string', 'max:150'],
            'from_email' => ['nullable', 'email', 'max:150'],
            'reply_to_email' => ['nullable', 'email', 'max:150'],
            'order_from_name' => ['nullable', 'string', 'max:150'],
            'order_from_email' => ['nullable', 'email', 'max:150'],
            'order_reply_to_email' => ['nullable', 'email', 'max:150'],
            'smtp_host' => ['nullable', 'string', 'max:150'],
            'smtp_port' => ['nullable', 'integer'],
            'smtp_encryption' => ['nullable', 'string', 'max:20'],
            'smtp_username' => ['nullable', 'string', 'max:150'],
            'smtp_password' => ['nullable', 'string'],
            'order_smtp_username' => ['nullable', 'string', 'max:150'],
            'order_smtp_password' => ['nullable', 'string'],
        ]);

        if (($validated['smtp_password'] ?? null) === null || $validated['smtp_password'] === '') {
            $validated['smtp_password'] = $existing?->smtp_password;
        }

        if (($validated['order_smtp_password'] ?? null) === null || $validated['order_smtp_password'] === '') {
            $validated['order_smtp_password'] = $existing?->order_smtp_password;
        }

        $validated['send_account_creation_emails'] = $request->boolean('send_account_creation_emails');
        $validated['send_email_verification_emails'] = $request->boolean('send_email_verification_emails');
        $validated['send_password_reset_emails'] = $request->boolean('send_password_reset_emails');
        $validated['send_order_emails'] = $request->boolean('send_order_emails');
        $validated['is_active'] = $request->boolean('is_active');

        CustomerEmailSetting::query()->updateOrCreate(['id' => 1], $validated);

        return back()->with('status', 'Customer email settings updated successfully.');
    }

    public function updateVerification(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'default_otp_channel' => ['required', 'in:email,sms,whatsapp'],
            'otp_length' => ['required', 'integer', 'min:4', 'max:8'],
            'otp_expiry_minutes' => ['required', 'integer', 'min:1', 'max:60'],
            'resend_wait_seconds' => ['required', 'integer', 'min:15', 'max:600'],
        ]);

        $hasMobileProvider = OtpProviderSetting::query()
            ->where('is_active', true)
            ->whereIn('channel', ['sms', 'whatsapp'])
            ->exists();

        $mobileVerificationEnabled = $hasMobileProvider && $request->boolean('mobile_verification_enabled');
        $smsOtpEnabled = $hasMobileProvider && $request->boolean('sms_otp_enabled');
        $whatsappOtpEnabled = $hasMobileProvider && $request->boolean('whatsapp_otp_enabled');

        if (! $hasMobileProvider && in_array($validated['default_otp_channel'], ['sms', 'whatsapp'], true)) {
            $validated['default_otp_channel'] = 'email';
        }

        OtpVerificationSetting::query()->updateOrCreate(
            ['id' => 1],
            $validated + [
                'email_verification_enabled' => $request->boolean('email_verification_enabled'),
                'mobile_verification_enabled' => $mobileVerificationEnabled,
                'email_otp_enabled' => $request->boolean('email_otp_enabled', true),
                'sms_otp_enabled' => $smsOtpEnabled,
                'whatsapp_otp_enabled' => $whatsappOtpEnabled,
            ]
        );

        return back()->with(
            'status',
            $hasMobileProvider
                ? 'OTP verification settings updated successfully.'
                : 'OTP verification settings updated. Mobile verification remains off because no active mobile OTP provider is configured.'
        );
    }

    public function updateProvider(Request $request, OtpProviderSetting $provider): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:100'],
            'channel' => ['required', 'in:sms,whatsapp,voice'],
            'api_key' => ['nullable', 'string', 'max:255'],
            'api_secret' => ['nullable', 'string'],
            'sender_id' => ['nullable', 'string', 'max:100'],
            'template_id' => ['nullable', 'string', 'max:120'],
            'base_url' => ['nullable', 'string', 'max:255'],
            'extra_config_text' => ['nullable', 'string'],
        ]);

        if (($validated['api_secret'] ?? null) === null || $validated['api_secret'] === '') {
            $validated['api_secret'] = $provider->api_secret;
        }

        $extraConfig = trim((string) ($validated['extra_config_text'] ?? ''));
        unset($validated['extra_config_text']);

        if ($request->boolean('is_default')) {
            OtpProviderSetting::query()->update(['is_default' => false]);
        }

        $provider->update($validated + [
            'is_active' => $request->boolean('is_active'),
            'is_default' => $request->boolean('is_default'),
            'extra_config' => $extraConfig !== '' ? ['raw' => $extraConfig] : null,
        ]);

        return back()->with('status', "{$provider->display_name} OTP provider updated successfully.");
    }
}
