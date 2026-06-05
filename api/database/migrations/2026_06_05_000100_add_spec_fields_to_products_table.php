<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->decimal('length', 10, 2)->nullable()->after('weight');
            $table->decimal('width', 10, 2)->nullable()->after('length');
            $table->decimal('height', 10, 2)->nullable()->after('width');
            $table->string('dimension_unit', 20)->nullable()->after('height');
            $table->string('weight_unit', 20)->nullable()->after('dimension_unit');
            $table->string('size_label', 120)->nullable()->after('weight_unit');
            $table->string('material', 150)->nullable()->after('size_label');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn([
                'length',
                'width',
                'height',
                'dimension_unit',
                'weight_unit',
                'size_label',
                'material',
            ]);
        });
    }
};
