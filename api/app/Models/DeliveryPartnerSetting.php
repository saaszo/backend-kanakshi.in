<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryPartnerSetting extends Model
{
    protected $table = 'delivery_partner_settings';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
}
