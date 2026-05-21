<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->boolean('show_topbar')->default(true)->after('custom_domain');
            $table->string('topbar_bg_color', 20)->nullable()->after('show_topbar');
            $table->string('topbar_text_color', 20)->nullable()->after('topbar_bg_color');
            $table->text('topbar_offers')->nullable()->after('topbar_text_color');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn([
                'show_topbar',
                'topbar_bg_color',
                'topbar_text_color',
                'topbar_offers',
            ]);
        });
    }
};
