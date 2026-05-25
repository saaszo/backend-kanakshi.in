<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->decimal('start_price', 12, 2)->default(0);
            $table->decimal('reserve_price', 12, 2)->nullable();
            $table->decimal('min_bid_increment', 12, 2)->default(50);
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->enum('status', ['draft', 'live', 'ended', 'cancelled'])->default('draft');
            $table->foreignId('winner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('winning_bid', 12, 2)->nullable();
            $table->unsignedInteger('total_bids')->default(0);
            $table->unsignedInteger('total_participants')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('auction_bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('ip_address', 45)->nullable();
            $table->boolean('is_winning')->default(false);
            $table->timestamps();
            $table->index(['auction_id', 'amount']);
            $table->index(['auction_id', 'user_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('auction_bids');
        Schema::dropIfExists('auctions');
    }
};
