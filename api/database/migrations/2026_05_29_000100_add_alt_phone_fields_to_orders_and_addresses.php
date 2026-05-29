<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'ship_alt_phone')) {
                $table->string('ship_alt_phone', 20)->nullable()->after('ship_phone');
            }
        });

        Schema::table('customer_addresses', function (Blueprint $table): void {
            if (! Schema::hasColumn('customer_addresses', 'alternate_phone')) {
                $table->string('alternate_phone', 20)->nullable()->after('phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'ship_alt_phone')) {
                $table->dropColumn('ship_alt_phone');
            }
        });

        Schema::table('customer_addresses', function (Blueprint $table): void {
            if (Schema::hasColumn('customer_addresses', 'alternate_phone')) {
                $table->dropColumn('alternate_phone');
            }
        });
    }
};
