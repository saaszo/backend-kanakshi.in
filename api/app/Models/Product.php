<?php

namespace App\Models;

use App\Support\ProductSchemaBuilder;
use App\Support\UniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $table = 'products';

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            $product->slug = UniqueSlug::for(
                self::class,
                (string) ($product->slug ?: $product->name),
                $product->id
            );

            if (blank($product->meta_title)) {
                $product->meta_title = Str::limit(trim((string) $product->name), 200, '');
            }

            if (blank($product->meta_desc)) {
                $fallbackDescription = $product->short_desc ?: trim(strip_tags((string) $product->description));
                $product->meta_desc = Str::limit($fallbackDescription, 320, '');
            }

            $product->custom_schema = ProductSchemaBuilder::build($product);
        });
    }

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'gst_percent' => 'decimal:2',
            'avg_rating' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
