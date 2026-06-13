<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('amazon_link', 2048)->nullable()->after('video_url');
            $table->boolean('amazon_button_enabled')->default(false)->after('amazon_link');
            $table->decimal('amazon_price', 10, 2)->nullable()->after('amazon_button_enabled');
            $table->timestamp('amazon_price_fetched_at')->nullable()->after('amazon_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn([
                'amazon_link',
                'amazon_button_enabled',
                'amazon_price',
                'amazon_price_fetched_at',
            ]);
        });
    }
};
