<?php

namespace App\Http\Controllers\Api\Settings;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;

class PublicSettingsController
{
    public function __invoke(): JsonResponse
    {
        if (!Schema::hasTable('settings')) {
            return response()->json([
                'success' => true,
                'message' => 'Settings table is not available yet.',
                'data' => [],
            ]);
        }

        $keys = [
            'site_name',
            'site_tagline',
            'site_email',
            'site_phone',
            'site_currency',
            'site_currency_symbol',
            'theme_primary_color',
            'home_style',
            'default_shipping_cost',
            'min_order_free_shipping',
            'gst_percent',
        ];

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
