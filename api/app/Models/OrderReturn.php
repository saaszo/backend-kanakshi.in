<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReturn extends Model
{
    protected $table = 'order_returns';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'requested_items' => 'array',
            'images' => 'array',
            'requested_amount' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'stock_restored_at' => 'datetime',
            'requested_at' => 'datetime',
            'resolved_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
