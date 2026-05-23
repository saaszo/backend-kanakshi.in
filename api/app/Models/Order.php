<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $table = 'orders';

    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if (empty($order->order_number)) {
                $order->order_number = self::generateUniqueOrderNumber();
            }
        });
    }

    public static function generateUniqueOrderNumber(): string
    {
        do {
            $number = 'LD-' . date('Ymd') . '-' . Str::upper(Str::random(5));
        } while (self::query()->where('order_number', $number)->exists());

        return $number;
    }

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function trackingUpdates(): HasMany
    {
        return $this->hasMany(OrderTracking::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }
}
