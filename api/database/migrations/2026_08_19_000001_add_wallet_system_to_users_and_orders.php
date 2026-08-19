<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'wallet_balance')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('wallet_balance', 10, 2)->default(0.00);
            });
        }

        if (!Schema::hasColumn('orders', 'wallet_discount')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('wallet_discount', 10, 2)->default(0.00);
            });
        }

        if (!Schema::hasTable('customer_wallet_transactions')) {
            Schema::create('customer_wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->enum('type', ['credit', 'debit']);
                $table->string('source', 50); // signup_bonus, purchase_reward, checkout_redemption, admin_adjustment, order_refund
                $table->decimal('amount', 10, 2);
                $table->decimal('balance_after', 10, 2);
                $table->string('description')->nullable();
                $table->string('status', 30)->default('completed'); // completed, pending_clearance, cancelled
                $table->timestamp('available_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
                $table->index(['user_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_wallet_transactions');

        if (Schema::hasColumn('orders', 'wallet_discount')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('wallet_discount');
            });
        }

        if (Schema::hasColumn('users', 'wallet_balance')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('wallet_balance');
            });
        }
    }
};
