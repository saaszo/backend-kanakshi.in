<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesAdminUploads;
use App\Http\Controllers\Controller;
use App\Models\DeliveryPartnerSetting;
use App\Models\PaymentGatewaySetting;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreSettingsController extends Controller
{
    use HandlesAdminUploads;

    public function edit(): View
    {
        $store = StoreSetting::query()->first();

        return view('admin.settings.edit', [
            'store' => $store,
            'topbarOffersText' => collect(json_decode($store?->topbar_offers ?? '[]', true) ?: [])
                ->filter(fn ($offer) => is_string($offer) && trim($offer) !== '')
                ->implode("\n"),
            'paymentGateways' => PaymentGatewaySetting::query()->orderBy('sort_order')->get(),
            'deliveryPartners' => DeliveryPartnerSetting::query()->orderByDesc('is_default')->orderBy('name')->get(),
        ]);
    }

    public function updateStore(Request $request): RedirectResponse
    {
        $store = StoreSetting::query()->first();

        $rules = [
            'site_name' => ['required', 'string', 'max:150'],
            'site_tagline' => ['nullable', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:150'],
            'business_email' => ['nullable', 'email', 'max:150'],
            'business_phone' => ['nullable', 'string', 'max:30'],
            'support_email' => ['nullable', 'email', 'max:150'],
            'support_phone' => ['nullable', 'string', 'max:30'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'logo_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg,webp,ico', 'max:5120'],
            'favicon_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg,webp,ico', 'max:5120'],
            'custom_domain' => ['nullable', 'string', 'max:180'],
            'google_tag_manager_id' => ['nullable', 'string', 'max:80'],
            'facebook_pixel_id' => ['nullable', 'string', 'max:80'],
            'seasonal_campaign_name' => ['nullable', 'string', 'max:150'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string'],
            'og_image' => ['nullable', 'string', 'max:255'],
            'og_image_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg,webp,ico', 'max:5120'],
            'twitter_title' => ['nullable', 'string', 'max:255'],
            'twitter_description' => ['nullable', 'string'],
            'twitter_image' => ['nullable', 'string', 'max:255'],
            'twitter_image_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg,webp,ico', 'max:5120'],
            'twitter_handle' => ['nullable', 'string', 'max:100'],
            'show_topbar' => ['nullable', 'boolean'],
            'topbar_bg_color' => ['nullable', 'string', 'max:20'],
            'topbar_text_color' => ['nullable', 'string', 'max:20'],
            'topbar_offers_text' => ['nullable', 'string'],
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
            'footer_copyright_text' => ['nullable', 'string', 'max:255'],
            'show_logo_on_invoice' => ['nullable', 'boolean'],
            'return_policy' => ['nullable', 'string'],
            'privacy_policy' => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],
            'custom_header_scripts' => ['nullable', 'string'],
            'custom_footer_scripts' => ['nullable', 'string'],
        ];

        $currentValues = $store?->toArray() ?? [];
        $mergedInput = array_replace($currentValues, $request->except(['_token', '_method']));

        foreach ([
            'site_name',
            'currency',
            'currency_symbol',
            'timezone',
            'language',
            'country',
            'invoice_prefix',
        ] as $requiredField) {
            if (! array_key_exists($requiredField, $mergedInput)) {
                $mergedInput[$requiredField] = $currentValues[$requiredField] ?? '';
            }
        }

        foreach (['show_logo_on_invoice', 'show_topbar'] as $booleanField) {
            if ($request->has($booleanField)) {
                $mergedInput[$booleanField] = $request->boolean($booleanField);
            } else {
                $mergedInput[$booleanField] = (bool) ($currentValues[$booleanField] ?? false);
            }
        }

        if (! array_key_exists('topbar_offers_text', $mergedInput)) {
            $mergedInput['topbar_offers_text'] = collect(json_decode($currentValues['topbar_offers'] ?? '[]', true) ?: [])
                ->filter(fn ($offer) => is_string($offer) && trim($offer) !== '')
                ->implode("\n");
        }

        $validated = validator($mergedInput, $rules)->validate();

        $validated['show_logo_on_invoice'] = (bool) ($mergedInput['show_logo_on_invoice'] ?? false);
        $validated['show_topbar'] = (bool) ($mergedInput['show_topbar'] ?? false);
        $validated['topbar_offers'] = json_encode(
            collect(preg_split('/\r\n|\r|\n/', (string) ($mergedInput['topbar_offers_text'] ?? '')) ?: [])
                ->map(fn ($offer) => trim((string) $offer))
                ->filter()
                ->values()
                ->all(),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        unset($validated['topbar_offers_text']);
        if ($request->hasFile('logo_file')) {
            $validated['logo_url'] = $this->storeAdminUpload($request->file('logo_file'), 'branding', 'Store logo', 'logo_file');
        }
        if ($request->hasFile('favicon_file')) {
            $validated['favicon_url'] = $this->storeAdminUpload($request->file('favicon_file'), 'branding', 'Store favicon', 'favicon_file');
        }
        if ($request->hasFile('og_image_file')) {
            $validated['og_image'] = $this->storeAdminUpload($request->file('og_image_file'), 'branding', 'Open Graph image', 'og_image_file');
        }
        if ($request->hasFile('twitter_image_file')) {
            $validated['twitter_image'] = $this->storeAdminUpload($request->file('twitter_image_file'), 'branding', 'Twitter card image', 'twitter_image_file');
        }

        StoreSetting::query()->updateOrCreate(['id' => 1], $validated);

        return back()->with('status', 'Store settings updated successfully.');
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
            'extra_config_text' => ['nullable', 'string'],
        ]);
        $isActive = $request->boolean('is_active');
        $isTestMode = $request->boolean('is_test_mode');

        $extraConfig = trim((string) ($validated['extra_config_text'] ?? ''));
        unset($validated['extra_config_text']);

        if ($extraConfig !== '') {
            $decoded = json_decode($extraConfig, true);
            $validated['extra_config'] = is_array($decoded)
                ? $decoded
                : ['raw' => $extraConfig];
        } else {
            $validated['extra_config'] = $gateway->extra_config;
        }

        foreach (['secret_key', 'secret_key_secondary', 'webhook_secret'] as $secretField) {
            if (($validated[$secretField] ?? null) === null || $validated[$secretField] === '') {
                $validated[$secretField] = $gateway->{$secretField};
            }
        }

        if ($gateway->provider === 'razorpay' && $isActive && ! $isTestMode) {
            if (blank($validated['public_key']) || blank($validated['secret_key']) || blank($validated['webhook_secret'])) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'public_key' => 'Razorpay live mode requires Key ID, Key Secret, and Webhook Secret.',
                    ]);
            }
        }

        $gateway->update($validated + [
            'is_active' => $isActive,
            'is_test_mode' => $isTestMode,
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
