<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WarrantyClaim extends Model
{
    protected $table = 'warranty_claims';

    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (WarrantyClaim $claim): void {
            if (empty($claim->claim_code)) {
                $claim->claim_code = self::generateUniqueClaimCode();
            }
        });
    }

    public static function generateUniqueClaimCode(): string
    {
        do {
            $code = 'CLM-' . Str::upper(Str::random(8));
        } while (self::query()->where('claim_code', $code)->exists());

        return $code;
    }

    protected function casts(): array
    {
        return [
            'image_paths' => 'array',
            'resolved_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(ProductRegistration::class, 'product_registration_id');
    }
}
