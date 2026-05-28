<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment');
            $table->json('images')->nullable();
            $table->boolean('is_verified_purchase')->default(true);
            $table->boolean('is_published')->default(false)->index();
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('moderated_at')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'user_id']);
            $table->index(['product_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
