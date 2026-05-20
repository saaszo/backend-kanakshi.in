<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryPartnerSetting;
use App\Models\EmailSetting;
use App\Models\PaymentGatewaySetting;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'store' => StoreSetting::query()->first(),
            'emailSettings' => EmailSetting::query()->first(),
            'paymentGateways' => PaymentGatewaySetting::query()->orderBy('sort_order')->get(),
            'deliveryPartners' => DeliveryPartnerSetting::query()->orderByDesc('is_default')->orderBy('name')->get(),
        ]);
    }

    public function updateStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:150'],
            'site_tagline' => ['nullable', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:150'],
            'business_email' => ['nullable', 'email', 'max:150'],
            'business_phone' => ['nullable', 'string', 'max:30'],
            'support_email' => ['nullable', 'email', 'max:150'],
            'support_phone' => ['nullable', 'string', 'max:30'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'logo_url' => ['nullable', 'string', 'max:255'],
            'favicon_url' => ['nullable', 'string', 'max:255'],
            'custom_domain' => ['nullable', 'string', 'max:180'],
            'currency' => ['required', 'string', 'max:12'],
            'currency_symbol' => ['required', 'string', 'max:12'],
            'timezone' => ['required', 'string', 'max:80'],
            'language' => ['required', 'string', 'max:12'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'invoice_prefix' => ['required', 'string', 'max:25'],
            'invoice_footer_note' => ['nullable', 'string'],
            'show_logo_on_invoice' => ['nullable', 'boolean'],
            'return_policy' => ['nullable', 'string'],
            'privacy_policy' => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],
        ]);

        $validated['show_logo_on_invoice'] = $request->boolean('show_logo_on_invoice');

        StoreSetting::query()->updateOrCreate(['id' => 1], $validated);

        return back()->with('status', 'Store settings updated successfully.');
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $existing = EmailSetting::query()->first();
        $validated = $request->validate([
            'from_name' => ['required', 'string', 'max:150'],
            'from_email' => ['required', 'email', 'max:150'],
            'reply_to_email' => ['nullable', 'email', 'max:150'],
            'smtp_host' => ['nullable', 'string', 'max:150'],
            'smtp_port' => ['nullable', 'integer'],
            'smtp_encryption' => ['nullable', 'string', 'max:20'],
            'smtp_username' => ['nullable', 'string', 'max:150'],
            'smtp_password' => ['nullable', 'string'],
            'imap_host' => ['nullable', 'string', 'max:150'],
            'imap_port' => ['nullable', 'integer'],
            'imap_encryption' => ['nullable', 'string', 'max:20'],
            'pop_host' => ['nullable', 'string', 'max:150'],
            'pop_port' => ['nullable', 'integer'],
            'pop_encryption' => ['nullable', 'string', 'max:20'],
        ]);

        $validated['mailer'] = 'smtp';
        $validated['send_otp_emails'] = $request->boolean('send_otp_emails');
        $validated['send_password_reset_emails'] = $request->boolean('send_password_reset_emails');
        $validated['send_account_creation_emails'] = $request->boolean('send_account_creation_emails');
        $validated['send_order_emails'] = $request->boolean('send_order_emails');
        $validated['is_active'] = $request->boolean('is_active');
        if (($validated['smtp_password'] ?? null) === null || $validated['smtp_password'] === '') {
            $validated['smtp_password'] = $existing?->smtp_password;
        }

        EmailSetting::query()->updateOrCreate(['id' => 1], $validated);

        return back()->with('status', 'Email settings updated successfully.');
    }

    public function updateGateway(Request $request, PaymentGatewaySetting $gateway): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:100'],
            'merchant_id' => ['nullable', 'string', 'max:150'],
            'public_key' => ['nullable', 'string', 'max:255'],
            'secret_key' => ['nullable', 'string'],
            'secret_key_secondary' => ['nullable', 'string'],
            'webhook_secret' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        foreach (['secret_key', 'secret_key_secondary', 'webhook_secret'] as $secretField) {
            if (($validated[$secretField] ?? null) === null || $validated[$secretField] === '') {
                $validated[$secretField] = $gateway->{$secretField};
            }
        }

        $gateway->update($validated + [
            'is_active' => $request->boolean('is_active'),
            'is_test_mode' => $request->boolean('is_test_mode'),
        ]);

        return back()->with('status', "{$gateway->display_name} settings updated.");
    }

    public function updateDeliveryPartner(Request $request, DeliveryPartnerSetting $partner): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'api_key' => ['nullable', 'string'],
            'api_secret' => ['nullable', 'string'],
            'account_number' => ['nullable', 'string', 'max:120'],
            'pickup_location' => ['nullable', 'string', 'max:180'],
            'tracking_url_template' => ['nullable', 'string', 'max:255'],
        ]);

        foreach (['api_key', 'api_secret'] as $secretField) {
            if (($validated[$secretField] ?? null) === null || $validated[$secretField] === '') {
                $validated[$secretField] = $partner->{$secretField};
            }
        }

        if ($request->boolean('is_default')) {
            DeliveryPartnerSetting::query()->update(['is_default' => false]);
        }

        $partner->update($validated + [
            'is_active' => $request->boolean('is_active'),
            'is_default' => $request->boolean('is_default'),
        ]);

        return back()->with('status', "{$partner->name} delivery settings updated.");
    }
}
