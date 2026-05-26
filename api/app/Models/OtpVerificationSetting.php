<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerificationSetting extends Model
{
    protected $table = 'otp_verification_settings';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'email_verification_enabled' => 'boolean',
            'mobile_verification_enabled' => 'boolean',
            'email_otp_enabled' => 'boolean',
            'sms_otp_enabled' => 'boolean',
            'whatsapp_otp_enabled' => 'boolean',
            'otp_length' => 'integer',
            'otp_expiry_minutes' => 'integer',
            'resend_wait_seconds' => 'integer',
        ];
    }
}
