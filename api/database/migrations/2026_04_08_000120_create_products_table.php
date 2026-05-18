<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->string('name', 200);
            $table->string('slug', 220)->unique();
            $table->longText('description')->nullable();
            $table->string('short_desc', 500)->nullable();
            $table->text('bullet_points')->nullable();
            $table->text('aplus_content')->nullable();
            $table->string('hsn_code', 20)->nullable();
            $table->decimal('gst_percent', 5, 2)->default(3.00);
            $table->decimal('price', 10, 2)->default(0.00);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('shipping_type', 20)->default('default');
            $table->decimal('shipping_fee', 10, 2)->default(0.00);
            $table->integer('stock')->default(0);
            $table->string('sku', 100)->nullable();
            $table->json('images')->nullable();
            $table->string('video_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('total_sold')->default(0);
            $table->decimal('avg_rating', 3, 2)->default(0.00);
            $table->integer('review_count')->default(0);
            $table->string('meta_title', 200)->nullable();
            $table->string('meta_desc', 320)->nullable();
            $table->longText('custom_schema')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
