<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_returns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('return_number', 40)->unique();
            $table->string('status', 30)->default('requested'); // requested, approved, rejected, received, refunded
            $table->string('reason', 150);
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->json('requested_items')->nullable();
            $table->json('images')->nullable();
            $table->decimal('requested_amount', 10, 2)->default(0);
            $table->decimal('approved_amount', 10, 2)->default(0);
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('stock_restored_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_returns');
    }
};
