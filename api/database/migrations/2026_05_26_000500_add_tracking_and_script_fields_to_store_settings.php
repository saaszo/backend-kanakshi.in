<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table): void {
            $table->string('google_tag_manager_id', 80)->nullable()->after('custom_domain');
            $table->string('facebook_pixel_id', 80)->nullable()->after('google_tag_manager_id');
            $table->longText('custom_header_scripts')->nullable()->after('custom_js');
            $table->longText('custom_footer_scripts')->nullable()->after('custom_header_scripts');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'google_tag_manager_id',
                'facebook_pixel_id',
                'custom_header_scripts',
                'custom_footer_scripts',
            ]);
        });
    }
};
