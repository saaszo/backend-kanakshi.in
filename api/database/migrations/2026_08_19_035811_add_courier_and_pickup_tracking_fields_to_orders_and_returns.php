<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'courier_name')) {
                $table->string('courier_name', 100)->nullable()->after('coupon_id');
            }
            if (! Schema::hasColumn('orders', 'dispatched_at')) {
                $table->timestamp('dispatched_at')->nullable()->after('tracking_url');
            }
            if (! Schema::hasColumn('orders', 'estimated_delivery_date')) {
                $table->date('estimated_delivery_date')->nullable()->after('dispatched_at');
            }
        });

        Schema::table('order_returns', function (Blueprint $table): void {
            if (! Schema::hasColumn('order_returns', 'pickup_courier_name')) {
                $table->string('pickup_courier_name', 100)->nullable()->after('reason');
            }
            if (! Schema::hasColumn('order_returns', 'pickup_tracking_number')) {
                $table->string('pickup_tracking_number', 100)->nullable()->after('pickup_courier_name');
            }
            if (! Schema::hasColumn('order_returns', 'pickup_tracking_url')) {
                $table->text('pickup_tracking_url')->nullable()->after('pickup_tracking_number');
            }
            if (! Schema::hasColumn('order_returns', 'pickup_scheduled_date')) {
                $table->date('pickup_scheduled_date')->nullable()->after('pickup_tracking_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'courier_name')) {
                $table->dropColumn('courier_name');
            }
            if (Schema::hasColumn('orders', 'dispatched_at')) {
                $table->dropColumn('dispatched_at');
            }
            if (Schema::hasColumn('orders', 'estimated_delivery_date')) {
                $table->dropColumn('estimated_delivery_date');
            }
        });

        Schema::table('order_returns', function (Blueprint $table): void {
            if (Schema::hasColumn('order_returns', 'pickup_courier_name')) {
                $table->dropColumn('pickup_courier_name');
            }
            if (Schema::hasColumn('order_returns', 'pickup_tracking_number')) {
                $table->dropColumn('pickup_tracking_number');
            }
            if (Schema::hasColumn('order_returns', 'pickup_tracking_url')) {
                $table->dropColumn('pickup_tracking_url');
            }
            if (Schema::hasColumn('order_returns', 'pickup_scheduled_date')) {
                $table->dropColumn('pickup_scheduled_date');
            }
        });
    }
};
