<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'profile_image')) {
                $table->string('profile_image')->nullable()->after('pincode');
            }

            if (! Schema::hasColumn('users', 'permissions')) {
                $table->json('permissions')->nullable()->after('role');
            }

            if (! Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('permissions');
            }

            if (! Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable()->after('last_login');
            }

            if (! Schema::hasColumn('users', 'two_factor_channel')) {
                $table->enum('two_factor_channel', ['email', 'sms'])->default('email')->after('two_factor_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'two_factor_channel')) {
                $table->dropColumn('two_factor_channel');
            }

            if (Schema::hasColumn('users', 'last_login_ip')) {
                $table->dropColumn('last_login_ip');
            }

            if (Schema::hasColumn('users', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('users', 'permissions')) {
                $table->dropColumn('permissions');
            }

            if (Schema::hasColumn('users', 'profile_image')) {
                $table->dropColumn('profile_image');
            }
        });
    }
};
