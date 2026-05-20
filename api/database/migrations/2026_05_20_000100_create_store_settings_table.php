<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('site_name', 150)->default('Little Divinity');
            $table->string('site_tagline', 255)->nullable();
            $table->string('business_name', 150)->nullable();
            $table->string('business_email', 150)->nullable();
            $table->string('business_phone', 30)->nullable();
            $table->string('support_email', 150)->nullable();
            $table->string('support_phone', 30)->nullable();
            $table->string('whatsapp_number', 30)->nullable();
            $table->string('logo_url')->nullable();
            $table->string('favicon_url')->nullable();
            $table->string('custom_domain', 180)->nullable();
            $table->string('currency', 12)->default('INR');
            $table->string('currency_symbol', 12)->default('₹');
            $table->string('timezone', 80)->default('Asia/Kolkata');
            $table->string('language', 12)->default('en');
            $table->string('address_line1', 255)->nullable();
            $table->string('address_line2', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 20)->nullable();
            $table->string('country', 100)->default('India');
            $table->string('invoice_prefix', 25)->default('LD');
            $table->text('invoice_footer_note')->nullable();
            $table->boolean('show_logo_on_invoice')->default(true);
            $table->longText('return_policy')->nullable();
            $table->longText('privacy_policy')->nullable();
            $table->longText('terms_conditions')->nullable();
            $table->longText('custom_css')->nullable();
            $table->longText('custom_js')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
