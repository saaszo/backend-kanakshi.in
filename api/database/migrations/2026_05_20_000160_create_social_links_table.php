<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_links', function (Blueprint $table): void {
            $table->id();
            $table->string('platform', 50)->index();
            $table->string('title', 100)->nullable();
            $table->string('handle', 120)->nullable();
            $table->string('url')->nullable();
            $table->string('icon', 120)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_links');
    }
};
