<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGatewaySetting extends Model
{
    protected $table = 'payment_gateway_settings';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'extra_config' => 'array',
            'is_active' => 'boolean',
            'is_test_mode' => 'boolean',
        ];
    }
}
