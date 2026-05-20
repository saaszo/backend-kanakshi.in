<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_sections', function (Blueprint $table): void {
            $table->id();
            $table->string('section_key', 100)->unique();
            $table->string('section_type', 60)->default('content')->index();
            $table->string('label', 150)->nullable();
            $table->string('title', 255)->nullable();
            $table->string('subtitle', 255)->nullable();
            $table->string('heading', 255)->nullable();
            $table->longText('content')->nullable();
            $table->string('button_text', 120)->nullable();
            $table->string('button_url')->nullable();
            $table->string('image_url')->nullable();
            $table->string('mobile_image_url')->nullable();
            $table->string('side_image_url')->nullable();
            $table->string('side_secondary_image_url')->nullable();
            $table->json('config')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_sections');
    }
};
