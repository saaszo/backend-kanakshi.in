<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $email = 'littledivinity1@gmail.com';

        $userIds = DB::table('users')
            ->where('email', $email)
            ->pluck('id');

        if ($userIds->isNotEmpty()) {
            DB::table('customer_access_tokens')->whereIn('user_id', $userIds)->delete();
            DB::table('otp_codes')->whereIn('user_id', $userIds)->delete();
        }

        DB::table('otp_codes')->where('email', $email)->delete();
        DB::table('pending_customer_registrations')->where('email', $email)->delete();
        DB::table('users')->where('email', $email)->delete();
    }

    public function down(): void
    {
        // Do not recreate deleted customer accounts on rollback.
    }
};
