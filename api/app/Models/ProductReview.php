<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReview extends Model
{
    protected $table = 'product_reviews';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'is_verified_purchase' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'moderated_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function moderatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public static function refreshProductMetrics(int $productId): void
    {
        $product = Product::query()->find($productId);

        if (! $product) {
            return;
        }

        $aggregate = self::query()
            ->where('product_id', $productId)
            ->where('is_published', true)
            ->selectRaw('COUNT(*) as total_reviews, COALESCE(AVG(rating), 0) as avg_rating')
            ->first();

        $product->forceFill([
            'review_count' => (int) ($aggregate?->total_reviews ?? 0),
            'avg_rating' => round((float) ($aggregate?->avg_rating ?? 0), 2),
        ])->save();
    }
}
