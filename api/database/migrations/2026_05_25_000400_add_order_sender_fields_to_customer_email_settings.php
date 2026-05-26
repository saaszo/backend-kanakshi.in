<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_email_settings', function (Blueprint $table): void {
            $table->string('order_from_name', 150)->nullable()->after('reply_to_email');
            $table->string('order_from_email', 150)->nullable()->after('order_from_name');
            $table->string('order_reply_to_email', 150)->nullable()->after('order_from_email');
            $table->string('order_smtp_username', 150)->nullable()->after('smtp_username');
            $table->text('order_smtp_password')->nullable()->after('smtp_password');
        });
    }

    public function down(): void
    {
        Schema::table('customer_email_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'order_from_name',
                'order_from_email',
                'order_reply_to_email',
                'order_smtp_username',
                'order_smtp_password',
            ]);
        });
    }
};
