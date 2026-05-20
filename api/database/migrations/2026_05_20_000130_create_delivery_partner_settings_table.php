<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_partner_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('code', 60)->unique();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->string('contact_person', 120)->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->string('contact_email', 150)->nullable();
            $table->text('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->string('account_number', 120)->nullable();
            $table->string('pickup_location', 180)->nullable();
            $table->string('tracking_url_template')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_partner_settings');
    }
};
