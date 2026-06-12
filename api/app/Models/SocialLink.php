<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class SocialLink extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => self::normalizeUrl($value),
            set: fn (?string $value): ?string => self::normalizeUrl($value),
        );
    }

    private static function normalizeUrl(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (
            str_starts_with($value, '/') ||
            str_starts_with($value, '#') ||
            preg_match('/^[a-z][a-z0-9+.-]*:/i', $value) === 1
        ) {
            return $value;
        }

        if (str_starts_with($value, '//')) {
            return 'https:'.$value;
        }

        if (preg_match('/^(?:www\.)?[a-z0-9][a-z0-9.-]*\.[a-z]{2,}(?:[\/?#]|$)/i', $value) === 1) {
            return 'https://'.$value;
        }

        return $value;
    }
}
