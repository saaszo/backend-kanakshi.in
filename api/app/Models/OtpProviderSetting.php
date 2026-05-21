<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpProviderSetting extends Model
{
    protected $table = 'otp_provider_settings';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'extra_config' => 'array',
        ];
    }
}
