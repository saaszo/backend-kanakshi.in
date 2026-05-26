<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('pending_access_token_hash', 64)->nullable()->after('payment_id');
            $table->timestamp('pending_access_expires_at')->nullable()->after('pending_access_token_hash');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'pending_access_token_hash',
                'pending_access_expires_at',
            ]);
        });
    }
};
