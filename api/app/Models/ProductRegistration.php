<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductRegistration extends Model
{
    protected $table = 'product_registrations';

    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (ProductRegistration $reg): void {
            if (empty($reg->registration_code)) {
                $reg->registration_code = self::generateUniqueRegistrationCode();
            }
        });
    }

    public static function generateUniqueRegistrationCode(): string
    {
        do {
            $code = 'REG-' . Str::upper(Str::random(8));
        } while (self::query()->where('registration_code', $code)->exists());

        return $code;
    }

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date:Y-m-d',
            'warranty_start_date' => 'date:Y-m-d',
            'warranty_end_date' => 'date:Y-m-d',
            'whatsapp_opt_in' => 'boolean',
            'buyback_eligible' => 'boolean',
            'verified_at' => 'datetime',
            'rejected_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(WarrantyClaim::class, 'product_registration_id');
    }

    public function buybacks(): HasMany
    {
        return $this->hasMany(BuybackRequest::class, 'product_registration_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(RegistrationActivityLog::class, 'product_registration_id');
    }
}
