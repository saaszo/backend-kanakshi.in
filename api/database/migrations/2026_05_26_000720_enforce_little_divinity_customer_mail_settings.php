<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $existing = DB::table('customer_email_settings')->where('id', 1)->first();
        $sharedSmtpPassword = env('CUSTOMER_AUTH_SMTP_PASSWORD')
            ?: env('CUSTOMER_ORDER_SMTP_PASSWORD')
            ?: env('CUSTOMER_SMTP_PASSWORD')
            ?: env('SMTP_SETTINGS_PASSWORD')
            ?: ($existing->smtp_password ?? null);

        DB::table('customer_email_settings')->updateOrInsert(
            ['id' => 1],
            [
                'from_name' => 'Kanakshi.in',
                'from_email' => 'no-reply@kanakshi.in',
                'reply_to_email' => env('CUSTOMER_AUTH_REPLY_TO_EMAIL', env('STORE_SUPPORT_EMAIL', 'support@kanakshi.in')),
                'smtp_host' => 'smtp.hostinger.com',
                'smtp_port' => 465,
                'smtp_encryption' => 'ssl',
                'smtp_username' => 'no-reply@kanakshi.in',
                'smtp_password' => $sharedSmtpPassword,
                'order_from_name' => 'Kanakshi.in Orders',
                'order_from_email' => 'no-reply@kanakshi.in',
                'order_reply_to_email' => env('CUSTOMER_ORDER_REPLY_TO_EMAIL', env('STORE_SUPPORT_EMAIL', 'support@kanakshi.in')),
                'order_smtp_username' => 'no-reply@kanakshi.in',
                'order_smtp_password' => $sharedSmtpPassword,
                'send_account_creation_emails' => true,
                'send_email_verification_emails' => true,
                'send_password_reset_emails' => true,
                'send_order_emails' => true,
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => $existing?->created_at ?? now(),
            ]
        );
    }

    public function down(): void
    {
        // Keep production customer mail settings intact on rollback.
    }
};
