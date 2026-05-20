<?php

namespace App\Support;

use App\Models\Product;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductSchemaBuilder
{
    public static function build(Product $product): string
    {
        $store = Schema::hasTable('store_settings')
            ? StoreSetting::query()->first()
            : null;

        $siteUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');
        $siteName = $store?->site_name ?: config('app.name', 'Little Divinity');
        $currency = $store?->currency ?: 'INR';
        $images = collect((array) $product->images)
            ->filter()
            ->map(fn ($image) => self::resolveUrl((string) $image, $siteUrl))
            ->values()
            ->all();
        $description = $product->meta_desc
            ?: $product->short_desc
            ?: Str::limit(trim(strip_tags((string) $product->description)), 320, '');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->meta_title ?: $product->name,
            'description' => $description,
            'sku' => $product->sku ?: str_replace('-', '', (string) $product->slug),
            'image' => $images,
            'brand' => [
                '@type' => 'Brand',
                'name' => $siteName,
            ],
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => $currency,
                'price' => (float) ($product->sale_price ?: $product->price ?: 0),
                'availability' => (int) $product->stock > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'itemCondition' => 'https://schema.org/NewCondition',
                'url' => "{$siteUrl}/product/{$product->slug}",
            ],
        ];

        if ((float) $product->avg_rating > 0 && (int) $product->review_count > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => (float) $product->avg_rating,
                'reviewCount' => (int) $product->review_count,
            ];
        }

        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private static function resolveUrl(string $path, string $siteUrl): string
    {
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return $siteUrl . '/' . ltrim($path, '/');
    }
}
