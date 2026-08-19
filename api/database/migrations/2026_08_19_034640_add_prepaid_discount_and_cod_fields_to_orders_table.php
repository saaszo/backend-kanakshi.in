<?php

use App\Models\PaymentGatewaySetting;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'prepaid_discount')) {
                $table->decimal('prepaid_discount', 10, 2)->default(0);
            }
            if (! Schema::hasColumn('orders', 'cod_fee')) {
                $table->decimal('cod_fee', 10, 2)->default(0);
            }
        });

        // Seed default payment gateways if empty
        if (Schema::hasTable('payment_gateway_settings')) {
            PaymentGatewaySetting::query()->firstOrCreate(
                ['provider' => 'razorpay'],
                [
                    'display_name' => 'Razorpay Secure (UPI, Cards, NetBanking)',
                    'is_active' => true,
                    'is_test_mode' => true,
                    'merchant_id' => 'rzp_merchant_kanakshi',
                    'public_key' => 'rzp_test_KanakshiDemoKey',
                    'secret_key' => 'rzp_test_secret_Kanakshi',
                    'webhook_secret' => null,
                    'sort_order' => 1,
                ]
            );

            PaymentGatewaySetting::query()->firstOrCreate(
                ['provider' => 'cod'],
                [
                    'display_name' => 'Cash on Delivery (COD)',
                    'is_active' => true,
                    'is_test_mode' => false,
                    'extra_config' => [
                        'cod_fee' => 0,
                        'min_order_amount' => 0,
                        'max_order_amount' => 50000,
                    ],
                    'sort_order' => 2,
                ]
            );

            PaymentGatewaySetting::query()->firstOrCreate(
                ['provider' => 'phonepe'],
                [
                    'display_name' => 'PhonePe PG (UPI, QR, Cards)',
                    'is_active' => false,
                    'is_test_mode' => true,
                    'merchant_id' => 'PHONEPE_TEST_MERCHANT',
                    'public_key' => 'TEST_CLIENT_ID',
                    'secret_key' => 'TEST_CLIENT_SECRET',
                    'sort_order' => 3,
                ]
            );
        }

        // Seed default prepaid discount & COD settings in settings table
        if (Schema::hasTable('settings')) {
            $defaultSettings = [
                'prepaid_discount_enabled' => '1',
                'prepaid_discount_title' => 'Extra 5% OFF on Online Payment',
                'prepaid_discount_type' => 'percent',
                'prepaid_discount_value' => '5',
                'prepaid_discount_min_order' => '0',
                'prepaid_discount_max_amount' => '500',
                'cod_enabled' => '1',
                'cod_fee' => '0',
                'cod_max_order_amount' => '50000',
            ];

            foreach ($defaultSettings as $key => $val) {
                Setting::query()->firstOrCreate(
                    ['key_name' => $key],
                    ['value' => $val]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'prepaid_discount')) {
                $table->dropColumn('prepaid_discount');
            }
            if (Schema::hasColumn('orders', 'cod_fee')) {
                $table->dropColumn('cod_fee');
            }
        });
    }
};
