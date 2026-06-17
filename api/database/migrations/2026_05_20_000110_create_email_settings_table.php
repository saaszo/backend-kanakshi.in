<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('mailer', 30)->default('smtp');
            $table->string('from_name', 150)->default('Kanakshi.in Service');
            $table->string('from_email', 150);
            $table->string('reply_to_email', 150)->nullable();
            $table->string('smtp_host', 150)->nullable();
            $table->unsignedSmallInteger('smtp_port')->nullable();
            $table->string('smtp_encryption', 20)->nullable();
            $table->string('smtp_username', 150)->nullable();
            $table->text('smtp_password')->nullable();
            $table->string('imap_host', 150)->nullable();
            $table->unsignedSmallInteger('imap_port')->nullable();
            $table->string('imap_encryption', 20)->nullable();
            $table->string('pop_host', 150)->nullable();
            $table->unsignedSmallInteger('pop_port')->nullable();
            $table->string('pop_encryption', 20)->nullable();
            $table->boolean('send_otp_emails')->default(true);
            $table->boolean('send_password_reset_emails')->default(true);
            $table->boolean('send_account_creation_emails')->default(true);
            $table->boolean('send_order_emails')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_settings');
    }
};
