<?php

namespace App\Models;

use App\Support\ProductSchemaBuilder;
use App\Support\UniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
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
                (string) $product->name,
                $product->id
            );

            if (blank($product->meta_title)) {
                $product->meta_title = Str::limit(trim((string) $product->name), 200, '');
            }

            if (blank($product->meta_desc)) {
                $fallbackDescription = $product->short_desc ?: trim(strip_tags((string) $product->description));
                $product->meta_desc = Str::limit($fallbackDescription, 320, '');
            }

            if (Schema::hasColumn($product->getTable(), 'is_sellable')) {
                $product->is_sellable = self::determineSellable($product);
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
            'weight' => 'decimal:2',
            'length' => 'decimal:2',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'gst_percent' => 'decimal:2',
            'avg_rating' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'is_sellable' => 'boolean',
        ];
    }

    public static function determineSellable(Product $product): bool
    {
        $price = (float) ($product->sale_price ?: $product->price ?: 0);
        $images = array_values(array_filter((array) $product->images, static fn ($image): bool => filled($image)));

        return $price > 0 && $images !== [];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }
}
