<?php

namespace Tests\Feature;

use App\Models\PaymentGatewaySetting;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_settings_exposes_storefront_admin_configuration(): void
    {
        StoreSetting::query()->updateOrCreate(['id' => 1], [
            'site_name' => 'Little Divinity',
            'business_name' => 'Little Divinity LLP',
            'business_email' => 'biz@example.com',
            'business_phone' => '+91 9000000001',
            'support_email' => 'support@example.com',
            'support_phone' => '+91 9000000002',
            'whatsapp_number' => '+91 9876543210',
            'invoice_footer_note' => 'Keep this invoice for support.',
            'custom_domain' => 'littledivinity.com',
            'show_topbar' => true,
            'topbar_offers' => json_encode(['Offer one', 'Offer two']),
        ]);

        PaymentGatewaySetting::query()->create([
            'provider' => 'phonepe',
            'display_name' => 'PhonePe',
            'is_active' => true,
            'is_test_mode' => false,
            'sort_order' => 1,
        ]);

        SocialLink::query()->create([
            'platform' => 'instagram',
            'title' => 'Instagram',
            'url' => 'https://instagram.com/littledivinity',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Setting::query()->insert([
            ['key_name' => 'registry_allow_buyback', 'value' => '1'],
            ['key_name' => 'registry_warranty_duration_months', 'value' => '36'],
            ['key_name' => 'registry_allowed_sources', 'value' => '["website","amazon"]'],
            ['key_name' => 'registry_allowed_upload_size_mb', 'value' => '8'],
            ['key_name' => 'registry_allowed_file_types', 'value' => 'pdf,jpg,png'],
            ['key_name' => 'registry_auto_verify_website_orders', 'value' => '0'],
        ]);

        $response = $this->getJson('/api/v1/settings/public');

        $response->assertOk();
        $response->assertJsonPath('data.support_email', 'support@example.com');
        $response->assertJsonPath('data.support_phone', '+91 9000000002');
        $response->assertJsonPath('data.whatsapp_number', '+91 9876543210');
        $response->assertJsonPath('data.invoice_footer_note', 'Keep this invoice for support.');
        $response->assertJsonPath('data.payment_gateways.0.provider', 'phonepe');
        $response->assertJsonPath('data.registry_allow_buyback', true);
        $response->assertJsonPath('data.registry_warranty_duration_months', 36);
        $response->assertJsonPath('data.registry_allowed_sources.0', 'website');
        $response->assertJsonPath('data.registry_allowed_sources.1', 'amazon');
        $response->assertJsonPath('data.registry_allowed_upload_size_mb', 8);
        $response->assertJsonPath('data.registry_allowed_file_types.0', 'pdf');
        $response->assertJsonPath('data.registry_allowed_file_types.2', 'png');
        $response->assertJsonPath('data.registry_auto_verify_website_orders', false);
    }
}
