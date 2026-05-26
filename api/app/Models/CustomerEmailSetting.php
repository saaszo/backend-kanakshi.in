<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerEmailSetting extends Model
{
    protected $table = 'customer_email_settings';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'send_account_creation_emails' => 'boolean',
            'send_email_verification_emails' => 'boolean',
            'send_password_reset_emails' => 'boolean',
            'send_order_emails' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
