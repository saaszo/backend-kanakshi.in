<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_number', 50)->unique();
            $table->string('status', 30)->default('pending'); // pending, confirmed, processing, shipped, delivered, cancelled, refunded
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->decimal('tax', 10, 2)->default(0.00);
            $table->decimal('shipping_cost', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2)->default(0.00);
            
            $table->string('payment_method', 30)->default('cod'); // cod, razorpay, phonepe
            $table->string('payment_status', 30)->default('pending'); // pending, paid, failed, refunded
            $table->string('payment_id', 150)->nullable();
            
            $table->string('ship_name', 100);
            $table->string('ship_email', 150);
            $table->string('ship_phone', 20);
            $table->text('ship_address');
            $table->string('ship_city', 100);
            $table->string('ship_state', 100);
            $table->string('ship_pincode', 10);
            $table->text('notes')->nullable();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            
            $table->string('tracking_number', 100)->nullable();
            $table->string('tracking_url', 500)->nullable();
            
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('name', 200);
            $table->decimal('price', 10, 2);
            $table->integer('quantity')->default(1);
            $table->string('image', 255)->nullable();
            $table->string('size', 30)->nullable();
            $table->string('color', 50)->nullable();
            $table->string('variant_details', 120)->nullable();
            $table->decimal('line_total', 10, 2)->default(0.00);
            $table->decimal('gst_percent', 5, 2)->default(0.00);
            $table->string('sku', 100)->nullable();
            $table->string('hsn_code', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('order_tracking', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('status', 100);
            $table->string('location', 200)->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_tracking');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
