<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $existing = DB::table('customer_email_settings')->where('id', 1)->first();

        DB::table('customer_email_settings')->updateOrInsert(
            ['id' => 1],
            [
                'from_name' => 'Little Divinity',
                'from_email' => 'noreply@littledivinity.com',
                'reply_to_email' => 'noreply@littledivinity.com',
                'smtp_host' => 'smtp.hostinger.com',
                'smtp_port' => 465,
                'smtp_encryption' => 'ssl',
                'smtp_username' => 'noreply@littledivinity.com',
                'smtp_password' => 'Littledivinity@123',
                'order_from_name' => 'Little Divinity Orders',
                'order_from_email' => 'order@littledivinity.com',
                'order_reply_to_email' => 'order@littledivinity.com',
                'order_smtp_username' => 'order@littledivinity.com',
                'order_smtp_password' => 'Littledivinity@123',
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
