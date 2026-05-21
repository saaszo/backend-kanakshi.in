<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        foreach (range(1, 18) as $index => $number) {
            DB::table('products')
                ->where('sku', 'LD-IMP-' . str_pad((string) $number, 3, '0', STR_PAD_LEFT))
                ->update([
                    'is_featured' => true,
                    'is_active' => true,
                    'total_sold' => 100 - $index,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        DB::table('products')
            ->whereIn('sku', collect(range(1, 18))->map(fn (int $number): string => 'LD-IMP-' . str_pad((string) $number, 3, '0', STR_PAD_LEFT))->all())
            ->update([
                'is_featured' => false,
                'total_sold' => 0,
                'updated_at' => now(),
            ]);
    }
};
