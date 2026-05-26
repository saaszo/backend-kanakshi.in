<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_customer_registrations', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 255)->unique();
            $table->string('phone', 20)->nullable();
            $table->text('password_hash');
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_customer_registrations');
    }
};
