<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_returns', function (Blueprint $table): void {
            if (!Schema::hasColumn('order_returns', 'refund_mode')) {
                $table->string('refund_mode', 30)->default('wallet');
            }
            if (!Schema::hasColumn('order_returns', 'refund_processed_at')) {
                $table->timestamp('refund_processed_at')->nullable();
            }
            if (!Schema::hasColumn('order_returns', 'reason_detail')) {
                $table->string('reason_detail', 255)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_returns', function (Blueprint $table): void {
            if (Schema::hasColumn('order_returns', 'refund_mode')) {
                $table->dropColumn('refund_mode');
            }
            if (Schema::hasColumn('order_returns', 'refund_processed_at')) {
                $table->dropColumn('refund_processed_at');
            }
            if (Schema::hasColumn('order_returns', 'reason_detail')) {
                $table->dropColumn('reason_detail');
            }
        });
    }
};
