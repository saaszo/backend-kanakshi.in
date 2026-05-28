<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table): void {
            $table->string('seasonal_campaign_name', 150)->nullable()->after('facebook_pixel_id');
            $table->string('meta_title', 255)->nullable()->after('seasonal_campaign_name');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('og_title', 255)->nullable()->after('meta_description');
            $table->text('og_description')->nullable()->after('og_title');
            $table->string('og_image', 255)->nullable()->after('og_description');
            $table->string('twitter_title', 255)->nullable()->after('og_image');
            $table->text('twitter_description')->nullable()->after('twitter_title');
            $table->string('twitter_image', 255)->nullable()->after('twitter_description');
            $table->string('twitter_handle', 100)->nullable()->after('twitter_image');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'seasonal_campaign_name',
                'meta_title',
                'meta_description',
                'og_title',
                'og_description',
                'og_image',
                'twitter_title',
                'twitter_description',
                'twitter_image',
                'twitter_handle',
            ]);
        });
    }
};
