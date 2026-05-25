<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('registration_code')->unique()->index();
            $table->string('customer_name');
            $table->string('email')->index();
            $table->string('phone')->index();
            $table->string('whatsapp_number')->nullable();
            $table->string('purchase_source')->index(); // website, offline_store, amazon, other_marketplace
            $table->string('order_or_bill_number');
            $table->date('purchase_date');
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('product_name_snapshot');
            $table->string('serial_card_id')->nullable();
            $table->string('source_store_name')->nullable();
            $table->string('source_city')->nullable();
            $table->string('invoice_file_path')->nullable();
            $table->string('product_image_path')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('whatsapp_opt_in')->default(false);
            $table->string('verification_status')->default('pending_verification')->index(); // pending_verification, verified, rejected, expired
            $table->date('warranty_start_date')->nullable();
            $table->date('warranty_end_date')->nullable();
            $table->boolean('buyback_eligible')->default(true);
            $table->text('admin_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
        });

        Schema::create('warranty_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_registration_id')->constrained('product_registrations')->onDelete('cascade');
            $table->string('claim_code')->unique()->index();
            $table->string('issue_type');
            $table->text('description');
            $table->text('image_paths')->nullable(); // stored as JSON array
            $table->string('status')->default('submitted')->index(); // submitted, under_review, approved, rejected, in_service, completed
            $table->text('admin_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('buyback_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_registration_id')->constrained('product_registrations')->onDelete('cascade');
            $table->string('request_code')->unique()->index();
            $table->text('condition_notes');
            $table->text('image_paths')->nullable(); // stored as JSON array
            $table->string('pickup_city')->nullable();
            $table->string('preferred_contact_method')->nullable();
            $table->decimal('estimated_buyback_value', 12, 2)->nullable();
            $table->decimal('final_buyback_value', 12, 2)->nullable();
            $table->string('status')->default('submitted')->index(); // submitted, inspection_pending, valued, approved, rejected, completed
            $table->text('admin_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('registration_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_registration_id')->constrained('product_registrations')->onDelete('cascade');
            $table->string('action');
            $table->text('old_data')->nullable(); // stored as JSON
            $table->text('new_data')->nullable(); // stored as JSON
            $table->string('created_by')->nullable(); // email or system
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_activity_logs');
        Schema::dropIfExists('buyback_requests');
        Schema::dropIfExists('warranty_claims');
        Schema::dropIfExists('product_registrations');
    }
};
