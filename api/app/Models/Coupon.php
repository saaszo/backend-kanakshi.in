<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Coupon extends Model
{
    protected $table = 'coupons';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'is_active' => 'boolean',
            'show_on_cart' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function isCurrentlyActive(?Carbon $now = null): bool
    {
        $now ??= now();

        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isAfter($now)) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isBefore($now)) {
            return false;
        }

        return true;
    }
}
