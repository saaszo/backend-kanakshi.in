<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key_name' => 'site_name', 'value' => 'Luxury Jewelry Store', 'label' => 'Site Name', 'group_name' => 'general'],
            ['key_name' => 'site_tagline', 'value' => 'Timeless Elegance.', 'label' => 'Site Tagline', 'group_name' => 'general'],
            ['key_name' => 'site_email', 'value' => 'admin@saaszo.in', 'label' => 'Site Email', 'group_name' => 'general'],
            ['key_name' => 'site_currency', 'value' => 'INR', 'label' => 'Currency', 'group_name' => 'general'],
            ['key_name' => 'site_currency_symbol', 'value' => '₹', 'label' => 'Currency Symbol', 'group_name' => 'general'],
            ['key_name' => 'theme_primary_color', 'value' => '#c5a059', 'label' => 'Primary Color', 'group_name' => 'general'],
            ['key_name' => 'home_style', 'value' => 'marketplace', 'label' => 'Homepage Style', 'group_name' => 'appearance'],
            ['key_name' => 'default_shipping_cost', 'value' => '49', 'label' => 'Default Shipping', 'group_name' => 'checkout'],
            ['key_name' => 'min_order_free_shipping', 'value' => '499', 'label' => 'Free Shipping Threshold', 'group_name' => 'checkout'],
            ['key_name' => 'gst_percent', 'value' => '18', 'label' => 'GST Percent', 'group_name' => 'checkout'],
        ];

        foreach ($defaults as $row) {
            DB::table('settings')->updateOrInsert(
                ['key_name' => $row['key_name']],
                $row
            );
        }
    }
}
