<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CatalogDemoSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'God Idols',
                'slug' => 'god-idols',
                'image' => 'storage/products/little-divinity-krishna.jpg',
                'description' => 'Sacred brass idols and devotional decor pieces.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Wall Decor',
                'slug' => 'wall-decor',
                'image' => 'storage/products/little-divinity-portrait.png',
                'description' => 'Statement handcrafted accents for warm interior walls.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Table Decor',
                'slug' => 'table-decor',
                'image' => 'storage/products/little-divinity-candle-stand.png',
                'description' => 'Decor-led centrepieces for festive and everyday styling.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Pooja Decor',
                'slug' => 'pooja-decor',
                'image' => 'storage/products/little-divinity-krishna.jpg',
                'description' => 'Pooja essentials, idol accents, and sacred brass styling.',
                'sort_order' => 4,
            ],
            [
                'name' => 'Home Kitchen',
                'slug' => 'home-kitchen',
                'image' => 'storage/products/little-divinity-candle-stand.png',
                'description' => 'Giftable utility pieces with a handcrafted home story.',
                'sort_order' => 5,
            ],
            [
                'name' => 'Gifting Edit',
                'slug' => 'gifting-edit',
                'image' => 'storage/products/little-divinity-portrait.png',
                'description' => 'Curated festive and occasion-ready gifting selections.',
                'sort_order' => 6,
            ],
        ];

        $categoryIds = [];

        foreach ($categories as $categoryData) {
            $category = Category::query()->updateOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData + ['is_active' => true]
            );

            $categoryIds[$category->slug] = $category->id;
        }

        $products = [
            [
                'category_slug' => 'god-idols',
                'name' => 'Little Divinity Krishna Brass Idol',
                'slug' => 'little-divinity-krishna-brass-idol',
                'price' => 12999,
                'sale_price' => 9999,
                'short_desc' => 'A detailed brass idol styled for devotional corners and premium gifting.',
                'description' => 'A live preview catalog piece seeded from your provided product photography so the storefront can display real brand imagery.',
                'images' => ['storage/products/little-divinity-krishna.jpg'],
                'sku' => 'LD-KRISHNA-001',
                'stock' => 12,
                'is_featured' => true,
                'total_sold' => 34,
                'avg_rating' => 4.8,
                'review_count' => 11,
            ],
            [
                'category_slug' => 'table-decor',
                'name' => 'Little Divinity Brass Candle Stand',
                'slug' => 'little-divinity-brass-candle-stand',
                'price' => 8499,
                'sale_price' => 6799,
                'short_desc' => 'An editorial brass decor piece for tables, consoles, and festive corners.',
                'description' => 'Seeded with your provided banner artwork so the live shop can show a warmer handcrafted decor story immediately.',
                'images' => ['storage/products/little-divinity-candle-stand.png'],
                'sku' => 'LD-CANDLE-001',
                'stock' => 18,
                'is_featured' => true,
                'total_sold' => 21,
                'avg_rating' => 4.6,
                'review_count' => 8,
            ],
            [
                'category_slug' => 'wall-decor',
                'name' => 'Little Divinity Heritage Portrait Panel',
                'slug' => 'little-divinity-heritage-portrait-panel',
                'price' => 7999,
                'sale_price' => 6299,
                'short_desc' => 'A portrait-led wall styling piece for dramatic handcrafted interiors.',
                'description' => 'Added as a visual catalog demo using your provided portrait artwork so the live listing feels fuller and more on-brand.',
                'images' => ['storage/products/little-divinity-portrait.png'],
                'sku' => 'LD-PORTRAIT-001',
                'stock' => 9,
                'is_featured' => true,
                'total_sold' => 14,
                'avg_rating' => 4.7,
                'review_count' => 5,
            ],
            [
                'category_slug' => 'pooja-decor',
                'name' => 'Krishna Mandir Accent',
                'slug' => 'krishna-mandir-accent',
                'price' => 6999,
                'sale_price' => 5599,
                'short_desc' => 'A temple-ready brass accent suited to pooja styling and festive gifting.',
                'description' => 'This seeded preview product helps the live category pages feel populated with devotional brass options.',
                'images' => ['storage/products/little-divinity-krishna.jpg'],
                'sku' => 'LD-POOJA-002',
                'stock' => 15,
                'is_featured' => false,
                'total_sold' => 12,
                'avg_rating' => 4.5,
                'review_count' => 4,
            ],
            [
                'category_slug' => 'home-kitchen',
                'name' => 'Festive Console Candle Accent',
                'slug' => 'festive-console-candle-accent',
                'price' => 7499,
                'sale_price' => 5899,
                'short_desc' => 'A warm metallic accent built for entertaining zones and festive styling.',
                'description' => 'This live preview listing uses your supplied decor art so product cards feel richer on the shop page.',
                'images' => ['storage/products/little-divinity-candle-stand.png'],
                'sku' => 'LD-HK-001',
                'stock' => 10,
                'is_featured' => false,
                'total_sold' => 7,
                'avg_rating' => 4.4,
                'review_count' => 3,
            ],
            [
                'category_slug' => 'gifting-edit',
                'name' => 'Heritage Gifting Portrait Decor',
                'slug' => 'heritage-gifting-portrait-decor',
                'price' => 8999,
                'sale_price' => 7199,
                'short_desc' => 'A gifting-led decor piece with a stronger visual story for curated edits.',
                'description' => 'Seeded from your uploaded artwork so the live gifting grid can show branded photography instead of placeholders.',
                'images' => ['storage/products/little-divinity-portrait.png'],
                'sku' => 'LD-GIFT-001',
                'stock' => 11,
                'is_featured' => true,
                'total_sold' => 16,
                'avg_rating' => 4.6,
                'review_count' => 6,
            ],
        ];

        foreach ($products as $productData) {
            $categorySlug = $productData['category_slug'];
            unset($productData['category_slug']);

            Product::query()->updateOrCreate(
                ['slug' => $productData['slug']],
                $productData + [
                    'category_id' => $categoryIds[$categorySlug],
                    'gst_percent' => 18.00,
                    'cost_price' => null,
                    'weight' => null,
                    'shipping_type' => 'default',
                    'shipping_fee' => 0,
                    'is_active' => true,
                    'meta_title' => $productData['name'],
                    'meta_desc' => $productData['short_desc'],
                ]
            );
        }
    }
}
