<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateway_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 50)->unique();
            $table->string('display_name', 100);
            $table->boolean('is_active')->default(false);
            $table->boolean('is_test_mode')->default(true);
            $table->string('merchant_id', 150)->nullable();
            $table->string('public_key', 255)->nullable();
            $table->text('secret_key')->nullable();
            $table->text('secret_key_secondary')->nullable();
            $table->text('webhook_secret')->nullable();
            $table->json('extra_config')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_settings');
    }
};
