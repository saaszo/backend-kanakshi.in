<?php

namespace App\Http\Controllers\Api\Settings;

use App\Models\MenuItem;
use App\Models\PaymentGatewaySetting;
use App\Models\SocialLink;
use App\Models\Setting;
use App\Models\StoreSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;

class PublicSettingsController
{
    public function __invoke(): JsonResponse
    {
        $legacySettings = Schema::hasTable('settings')
            ? Setting::query()->pluck('value', 'key_name')
            : collect();

        if (Schema::hasTable('store_settings')) {
            $store = StoreSetting::query()->first();
            $headerMenu = Schema::hasTable('menu_items')
                ? MenuItem::query()
                    ->where('location', 'header')
                    ->where('is_active', true)
                    ->with(['children' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
                    ->whereNull('parent_id')
                    ->orderBy('sort_order')
                    ->get()
                : collect();
            $footerMenu = Schema::hasTable('menu_items')
                ? MenuItem::query()
                    ->where('location', 'footer')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get()
                : collect();
            $mobileMenu = Schema::hasTable('menu_items')
                ? MenuItem::query()
                    ->where('location', 'mobile')
                    ->where('is_active', true)
                    ->with(['children' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
                    ->whereNull('parent_id')
                    ->orderBy('sort_order')
                    ->get()
                : collect();
            $socialLinks = Schema::hasTable('social_links')
                ? SocialLink::query()->where('is_active', true)->orderBy('sort_order')->get()
                : collect();
            $paymentGateways = Schema::hasTable('payment_gateway_settings')
                ? PaymentGatewaySetting::query()
                    ->orderBy('sort_order')
                    ->get()
                    ->filter(fn (PaymentGatewaySetting $gateway) => $this->gatewayVisibleOnStorefront($gateway))
                    ->map(fn (PaymentGatewaySetting $gateway) => [
                        'provider' => $gateway->provider,
                        'display_name' => $gateway->display_name,
                        'is_test_mode' => (bool) $gateway->is_test_mode,
                    ])
                    ->values()
                : collect();
            $topbarOffers = collect(json_decode($store?->topbar_offers ?? '[]', true) ?: [])
                ->filter(fn ($offer) => is_string($offer) && trim($offer) !== '')
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Public storefront settings fetched successfully.',
                'data' => [
                    'site_name' => $store?->site_name,
                    'site_tagline' => $store?->site_tagline,
                    'site_email' => $store?->support_email ?: $store?->business_email,
                    'site_phone' => $store?->support_phone ?: $store?->business_phone,
                    'privacy_policy' => $store?->privacy_policy,
                    'terms_conditions' => $store?->terms_conditions,
                    'return_policy' => $store?->return_policy,
                    'site_currency_symbol' => $store?->currency_symbol ?: '₹',
                    'site_currency' => $store?->currency ?: 'INR',
                    'address_line1' => $store?->address_line1,
                    'address_line2' => $store?->address_line2,
                    'city' => $store?->city,
                    'state' => $store?->state,
                    'pincode' => $store?->pincode,
                    'country' => $store?->country,
                    'custom_domain' => $store?->custom_domain,
                    'google_tag_manager_id' => $store?->google_tag_manager_id,
                    'facebook_pixel_id' => $store?->facebook_pixel_id,
                    'seasonal_campaign_name' => $store?->seasonal_campaign_name,
                    'meta_title' => $store?->meta_title,
                    'meta_description' => $store?->meta_description,
                    'og_title' => $store?->og_title,
                    'og_description' => $store?->og_description,
                    'og_image' => $store?->og_image,
                    'twitter_title' => $store?->twitter_title,
                    'twitter_description' => $store?->twitter_description,
                    'twitter_image' => $store?->twitter_image,
                    'twitter_handle' => $store?->twitter_handle,
                    'logo_url' => $store?->logo_url,
                    'favicon_url' => $store?->favicon_url,
                    'footer_copyright_text' => $store?->footer_copyright_text,
                    'custom_header_scripts' => $store?->custom_header_scripts,
                    'custom_footer_scripts' => $store?->custom_footer_scripts,
                    'show_topbar' => (bool) ($store?->show_topbar ?? false),
                    'topbar_bg_color' => $store?->topbar_bg_color ?: '#0f0f0f',
                    'topbar_text_color' => $store?->topbar_text_color ?: '#ffffff',
                    'topbar_offers' => $topbarOffers,
                    'header_menu' => $headerMenu->values(),
                    'footer_menu' => $footerMenu->values(),
                    'mobile_menu' => $mobileMenu->values(),
                    'social_links' => $socialLinks->values(),
                    'payment_gateways' => $paymentGateways->values(),
                    'registry_allow_buyback' => $this->readRegistryBooleanSetting('registry_allow_buyback', true),
                    'default_shipping_cost' => $legacySettings->get('default_shipping_cost', '99'),
                    'min_order_free_shipping' => $legacySettings->get('min_order_free_shipping', '499'),
                    'gst_percent' => $legacySettings->get('gst_percent', '18'),
                ],
            ]);
        }

        if (! Schema::hasTable('settings')) {
            return response()->json([
                'success' => true,
                'message' => 'Settings table is not available yet.',
                'data' => [],
            ]);
        }

        $keys = ['site_name', 'site_tagline', 'site_email', 'site_phone', 'site_currency', 'site_currency_symbol', 'theme_primary_color', 'home_style', 'default_shipping_cost', 'min_order_free_shipping', 'gst_percent'];

        $settings = $legacySettings->only($keys);

        return response()->json([
            'success' => true,
            'message' => 'Public settings fetched successfully.',
            'data' => $settings,
        ]);
    }

    private function gatewayVisibleOnStorefront(PaymentGatewaySetting $gateway): bool
    {
        return (bool) $gateway->is_active;
    }

    private function readRegistryBooleanSetting(string $key, bool $default): bool
    {
        if (! Schema::hasTable('settings')) {
            return $default;
        }

        $value = Setting::query()->where('key_name', $key)->value('value');

        if ($value === null) {
            return $default;
        }

        return in_array((string) $value, ['1', 'true', 'yes', 'on'], true);
    }
}
