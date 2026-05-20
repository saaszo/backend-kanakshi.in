<?php

namespace App\Http\Controllers\Api\Settings;

use App\Models\MenuItem;
use App\Models\SocialLink;
use App\Models\Setting;
use App\Models\StoreSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;

class PublicSettingsController
{
    public function __invoke(): JsonResponse
    {
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
            $socialLinks = Schema::hasTable('social_links')
                ? SocialLink::query()->where('is_active', true)->orderBy('sort_order')->get()
                : collect();

            return response()->json([
                'success' => true,
                'message' => 'Public storefront settings fetched successfully.',
                'data' => [
                    'site_name' => $store?->site_name,
                    'site_tagline' => $store?->site_tagline,
                    'site_email' => $store?->support_email ?: $store?->business_email,
                    'site_phone' => $store?->support_phone ?: $store?->business_phone,
                    'site_currency_symbol' => $store?->currency_symbol ?: '₹',
                    'site_currency' => $store?->currency ?: 'INR',
                    'address_line1' => $store?->address_line1,
                    'address_line2' => $store?->address_line2,
                    'city' => $store?->city,
                    'state' => $store?->state,
                    'pincode' => $store?->pincode,
                    'country' => $store?->country,
                    'custom_domain' => $store?->custom_domain,
                    'logo_url' => $store?->logo_url,
                    'favicon_url' => $store?->favicon_url,
                    'header_menu' => $headerMenu->values(),
                    'footer_menu' => $footerMenu->values(),
                    'social_links' => $socialLinks->values(),
                    'min_order_free_shipping' => '499',
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

        $settings = Setting::query()
            ->whereIn('key_name', $keys)
            ->pluck('value', 'key_name');

        return response()->json([
            'success' => true,
            'message' => 'Public settings fetched successfully.',
            'data' => $settings,
        ]);
    }
}
