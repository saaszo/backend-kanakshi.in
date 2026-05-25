<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BuybackRequest extends Model
{
    protected $table = 'buyback_requests';

    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (BuybackRequest $request): void {
            if (empty($request->request_code)) {
                $request->request_code = self::generateUniqueRequestCode();
            }
        });
    }

    public static function generateUniqueRequestCode(): string
    {
        do {
            $code = 'BBK-' . Str::upper(Str::random(8));
        } while (self::query()->where('request_code', $code)->exists());

        return $code;
    }

    protected function casts(): array
    {
        return [
            'image_paths' => 'array',
            'estimated_buyback_value' => 'decimal:2',
            'final_buyback_value' => 'decimal:2',
            'approved_at' => 'datetime',
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(ProductRegistration::class, 'product_registration_id');
    }
}
