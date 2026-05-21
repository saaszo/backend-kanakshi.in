<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_email_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('from_name', 150)->nullable();
            $table->string('from_email', 150)->nullable();
            $table->string('reply_to_email', 150)->nullable();
            $table->string('smtp_host', 150)->nullable();
            $table->unsignedSmallInteger('smtp_port')->nullable();
            $table->string('smtp_encryption', 20)->nullable();
            $table->string('smtp_username', 150)->nullable();
            $table->text('smtp_password')->nullable();
            $table->boolean('send_account_creation_emails')->default(false);
            $table->boolean('send_email_verification_emails')->default(false);
            $table->boolean('send_password_reset_emails')->default(false);
            $table->boolean('send_order_emails')->default(false);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('otp_verification_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('email_verification_enabled')->default(true);
            $table->boolean('mobile_verification_enabled')->default(false);
            $table->boolean('email_otp_enabled')->default(true);
            $table->boolean('sms_otp_enabled')->default(false);
            $table->boolean('whatsapp_otp_enabled')->default(false);
            $table->string('default_otp_channel', 20)->default('email');
            $table->unsignedTinyInteger('otp_length')->default(6);
            $table->unsignedSmallInteger('otp_expiry_minutes')->default(10);
            $table->unsignedSmallInteger('resend_wait_seconds')->default(60);
            $table->timestamps();
        });

        Schema::create('otp_provider_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 60)->unique();
            $table->string('display_name', 100);
            $table->string('channel', 20)->default('sms');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->string('api_key', 255)->nullable();
            $table->text('api_secret')->nullable();
            $table->string('sender_id', 100)->nullable();
            $table->string('template_id', 120)->nullable();
            $table->string('base_url', 255)->nullable();
            $table->json('extra_config')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_provider_settings');
        Schema::dropIfExists('otp_verification_settings');
        Schema::dropIfExists('customer_email_settings');
    }
};
