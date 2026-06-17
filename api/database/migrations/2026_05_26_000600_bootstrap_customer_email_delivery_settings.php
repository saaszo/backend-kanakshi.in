<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $existing = DB::table('customer_email_settings')->where('id', 1)->first();

        $authFromEmail = env('CUSTOMER_AUTH_FROM_EMAIL', 'noreply@kanakshi.in');
        $orderFromEmail = env('CUSTOMER_ORDER_FROM_EMAIL', 'noreply@kanakshi.in');
        $replyToEmail = env('CUSTOMER_AUTH_REPLY_TO_EMAIL', env('STORE_SUPPORT_EMAIL', 'support@kanakshi.in'));
        $orderReplyToEmail = env('CUSTOMER_ORDER_REPLY_TO_EMAIL', env('STORE_SUPPORT_EMAIL', 'support@kanakshi.in'));
        $sharedSmtpPassword = env('CUSTOMER_AUTH_SMTP_PASSWORD')
            ?: env('CUSTOMER_ORDER_SMTP_PASSWORD')
            ?: env('CUSTOMER_SMTP_PASSWORD')
            ?: env('SMTP_SETTINGS_PASSWORD')
            ?: ($existing->smtp_password ?? null);

        DB::table('customer_email_settings')->updateOrInsert(
            ['id' => 1],
            [
                'from_name' => filled($existing?->from_name ?? null) ? $existing->from_name : env('CUSTOMER_AUTH_FROM_NAME', 'Kanakshi.in'),
                'from_email' => filled($existing?->from_email ?? null) ? $existing->from_email : $authFromEmail,
                'reply_to_email' => filled($existing?->reply_to_email ?? null) ? $existing->reply_to_email : $replyToEmail,
                'smtp_host' => filled($existing?->smtp_host ?? null) ? $existing->smtp_host : env('CUSTOMER_SMTP_HOST', 'smtp.hostinger.com'),
                'smtp_port' => (int) ($existing?->smtp_port ?: env('CUSTOMER_SMTP_PORT', 465)),
                'smtp_encryption' => filled($existing?->smtp_encryption ?? null) ? $existing->smtp_encryption : env('CUSTOMER_SMTP_ENCRYPTION', 'ssl'),
                'smtp_username' => filled($existing?->smtp_username ?? null) ? $existing->smtp_username : env('CUSTOMER_AUTH_SMTP_USERNAME', $authFromEmail),
                'smtp_password' => filled($existing?->smtp_password ?? null) ? $existing->smtp_password : $sharedSmtpPassword,
                'order_from_name' => filled($existing?->order_from_name ?? null) ? $existing->order_from_name : env('CUSTOMER_ORDER_FROM_NAME', 'Kanakshi.in Orders'),
                'order_from_email' => filled($existing?->order_from_email ?? null) ? $existing->order_from_email : $orderFromEmail,
                'order_reply_to_email' => filled($existing?->order_reply_to_email ?? null) ? $existing->order_reply_to_email : $orderReplyToEmail,
                'order_smtp_username' => filled($existing?->order_smtp_username ?? null) ? $existing->order_smtp_username : env('CUSTOMER_ORDER_SMTP_USERNAME', $authFromEmail),
                'order_smtp_password' => filled($existing?->order_smtp_password ?? null) ? $existing->order_smtp_password : (env('CUSTOMER_ORDER_SMTP_PASSWORD') ?: $sharedSmtpPassword),
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
        // Preserve production customer email settings on rollback.
    }
};
