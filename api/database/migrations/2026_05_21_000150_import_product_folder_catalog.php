<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $categories = [
            'god-idols' => 'God Idols',
            'pooja-decor' => 'Pooja Decor',
            'home-decor' => 'Home Decor',
            'table-decor' => 'Table Decor',
            'home-kitchen' => 'Home Kitchen',
        ];

        foreach ($categories as $slug => $name) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => "{$name} collection for Little Divinity.",
                    'is_active' => true,
                    'sort_order' => 10,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $categoryIds = DB::table('categories')->pluck('id', 'slug');
        $products = [
            [
                'name' => 'Heritage Brass Decor Accent 1',
                'category' => 'home-decor',
                'sku' => 'LD-IMP-001',
                'price' => 6999,
                'sale_price' => 4499,
                'stock' => 20,
                'images' => $this->images(1, ['01-1.png', '02-2.png', '03-3.png', '04-4.png', '05-5.png', '06-6.png', '07-7.png', '08-8.png']),
            ],
            [
                'name' => 'Heritage Brass Decor Accent 2',
                'category' => 'home-decor',
                'sku' => 'LD-IMP-002',
                'price' => 7999,
                'sale_price' => 4999,
                'stock' => 20,
                'images' => $this->images(2, ['01-a1.png', '02-a10.png', '03-a2.png', '04-a3.png', '05-a4.png', '06-a5.png', '07-a6.png', '08-a7.png']),
            ],
            [
                'name' => 'Brass Ram Darbar Idol',
                'category' => 'god-idols',
                'sku' => 'LD-IMP-003',
                'price' => 14999,
                'sale_price' => 9999,
                'stock' => 12,
                'images' => $this->images(3, ['01-ram-1.png', '02-ram-2.png', '03-ram-3.png', '04-ram-4.png', '05-ram-5.png', '06-ram-6.png', '07-ram-7.png', '08-ram-8.png']),
            ],
            [
                'name' => 'Brass Shankh Pooja Decor',
                'category' => 'pooja-decor',
                'sku' => 'LD-IMP-004',
                'price' => 5999,
                'sale_price' => 3499,
                'stock' => 18,
                'images' => $this->images(4, ['01-shank1.png', '02-shank10.png', '03-shank2.png', '04-shank3.png', '05-shank4.png', '06-shank5.png', '07-shank6.png', '08-shank7.png']),
            ],
            [
                'name' => 'Brass Table Decor Accent',
                'category' => 'table-decor',
                'sku' => 'LD-IMP-005',
                'price' => 6499,
                'sale_price' => 3999,
                'stock' => 20,
                'images' => $this->images(5, ['01-t1.png', '02-t2.png', '03-t3.png', '04-t4.png', '05-t5.png', '06-t7.png', '07-t8.png']),
            ],
            [
                'name' => 'Little Divinity Brass Idol Set',
                'category' => 'god-idols',
                'sku' => 'LD-IMP-006',
                'price' => 10999,
                'sale_price' => 7499,
                'stock' => 15,
                'images' => $this->images(6, ['01-4.png', '02-5.png', '03-6.png', '04-7.png', '05-8.png', '06-9.png', '07-little-divinity-4.png', '08-little-divinity-5.png']),
            ],
            [
                'name' => 'Sacred Brass Decor Accent',
                'category' => 'pooja-decor',
                'sku' => 'LD-IMP-007',
                'price' => 8999,
                'sale_price' => 5999,
                'stock' => 16,
                'images' => $this->images(7, ['01-s1.png', '02-s2-copy.png', '03-s3-copy.png', '04-s4-copy.png', '05-s5-copy.png', '06-s6-copy.png', '07-s7-copy.png', '08-s8-copy.png']),
            ],
            [
                'name' => 'Brass Pen Stand Decor',
                'category' => 'table-decor',
                'sku' => 'LD-IMP-008',
                'price' => 3999,
                'sale_price' => 2499,
                'stock' => 25,
                'images' => $this->images(8, ['01-pen1.png', '02-pen2.png', '03-pen3.png', '04-pen4.png', '05-pen5.png', '06-pen6.png', '07-pen7.png', '08-pen8.png']),
            ],
            [
                'name' => 'Brass Bell Pooja Decor',
                'category' => 'pooja-decor',
                'sku' => 'LD-IMP-009',
                'price' => 4499,
                'sale_price' => 2999,
                'stock' => 25,
                'images' => $this->images(9, ['01-b1.png', '02-b2.png', '03-b3.png', '04-b4.png', '05-b5.png', '06-b6.png', '07-b7.png', '08-b8.png']),
            ],
            [
                'name' => 'Antique Brass Home Decor Set',
                'category' => 'home-decor',
                'sku' => 'LD-IMP-010',
                'price' => 11999,
                'sale_price' => 7999,
                'stock' => 12,
                'images' => $this->images(10, ['01-a1.png', '02-a2.png', '03-a3.png', '04-image2.jpg', '05-new-image.jpg', '06-s4.png', '07-s5.png', '08-s6.png']),
            ],
            [
                'name' => 'Pomelli Brass Decor Accent',
                'category' => 'table-decor',
                'sku' => 'LD-IMP-011',
                'price' => 7499,
                'sale_price' => 4999,
                'stock' => 16,
                'images' => $this->images(11, ['01-pomelli-photoshoot-image-1-1-0427-1.png', '02-pomelli-photoshoot-image-1-1-0427-2.png', '03-pomelli-photoshoot-image-1-1-0427-3.png', '04-pomelli-photoshoot-image-1-1-0427-4.png', '05-pomelli-photoshoot-image-1-1-0427-5.png', '06-pomelli-photoshoot-image-1-1-0427-6.png', '07-pomelli-photoshoot-image-1-1-0427-7.png', '08-pomelli-photoshoot-image-1-1-0427-8.png']),
            ],
            [
                'name' => 'Brass Mandir Decor Accent',
                'category' => 'pooja-decor',
                'sku' => 'LD-IMP-012',
                'price' => 6999,
                'sale_price' => 4299,
                'stock' => 18,
                'images' => $this->images(12, ['01-m1.png', '02-m2.png', '03-m3.png', '04-m4.png', '05-m5.png', '06-m7.png', '07-m8.png', '08-m9.png']),
            ],
            [
                'name' => 'Brass Kuber Pooja Idol',
                'category' => 'pooja-decor',
                'sku' => 'LD-IMP-013',
                'price' => 8999,
                'sale_price' => 5799,
                'stock' => 14,
                'images' => $this->images(13, ['01-kubara-1.png', '02-kubara-2.png', '03-kubara-3.png', '04-kubara-4.png', '05-kubara-5.png', '06-kubara-6.png', '07-kubara-7.png', '08-kubara-8.png']),
            ],
            [
                'name' => 'Brass Glass Home Kitchen Set',
                'category' => 'home-kitchen',
                'sku' => 'LD-IMP-014',
                'price' => 5499,
                'sale_price' => 3499,
                'stock' => 22,
                'images' => $this->images(14, ['01-glass1.png', '02-glass2.png', '03-glass3.png', '04-glass4.png', '05-glass5.png', '06-glass6.png', '07-glass7.png', '08-glass8.png']),
            ],
            [
                'name' => 'Brass Dashavatar Wall Hanging',
                'category' => 'home-decor',
                'sku' => 'LD-IMP-015',
                'price' => 12999,
                'sale_price' => 8999,
                'stock' => 10,
                'images' => $this->images(15, ['01-10.jpg', '02-2.png', '03-3.png', '04-4.png', '05-5.png', '06-6.png', '07-7.png', '08-8.png']),
            ],
            [
                'name' => 'Brass Diya Pooja Set',
                'category' => 'pooja-decor',
                'sku' => 'LD-IMP-016',
                'price' => 3999,
                'sale_price' => 2499,
                'stock' => 30,
                'images' => $this->images(16, ['01-d2.png', '02-d3.png', '03-d4.png', '04-d5.png', '05-d6.png', '06-d7.png', '07-d8.png', '08-d9.png']),
            ],
            [
                'name' => 'Mini Brass Kalash',
                'category' => 'pooja-decor',
                'sku' => 'LD-IMP-017',
                'price' => 2499,
                'sale_price' => 1499,
                'stock' => 35,
                'images' => $this->images(17, ['01-mini-kalash-main.png', '02-mini-kaslsh-1.png', '03-mini-kaslsh-2.png', '04-mini-kaslsh-3.png', '05-mini-kaslsh-4.png', '06-mini-kaslsh-5.png', '07-mini-kaslsh-6.png', '08-mini-kalash-7.png']),
            ],
            [
                'name' => 'Brass Diya Accent',
                'category' => 'pooja-decor',
                'sku' => 'LD-IMP-018',
                'price' => 2999,
                'sale_price' => 1899,
                'stock' => 30,
                'images' => $this->images(18, ['01-diya-1.png', '02-diya-2.png', '03-diya-3.png', '04-diya-4.png', '05-diya-5.png']),
            ],
        ];

        foreach ($products as $index => $product) {
            $slug = Str::slug($product['name']);
            $categoryId = $categoryIds[$product['category']] ?? $categoryIds['home-decor'];
            $shortDescription = "Imported Little Divinity product gallery from product folder " . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) . '.';

            DB::table('products')->updateOrInsert(
                ['sku' => $product['sku']],
                [
                    'category_id' => $categoryId,
                    'name' => $product['name'],
                    'slug' => $slug,
                    'description' => 'This product was imported from the Little Divinity product image folders. Pricing, stock, category, and copy can be refined from the admin product editor.',
                    'short_desc' => $shortDescription,
                    'price' => $product['price'],
                    'sale_price' => $product['sale_price'],
                    'cost_price' => null,
                    'stock' => $product['stock'],
                    'images' => json_encode($product['images'], JSON_UNESCAPED_SLASHES),
                    'video_url' => null,
                    'is_featured' => $index < 8,
                    'is_active' => true,
                    'shipping_type' => 'default',
                    'shipping_fee' => 0,
                    'gst_percent' => 18,
                    'meta_title' => $product['name'],
                    'meta_desc' => $shortDescription,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('products')
            ->whereIn('sku', collect(range(1, 18))->map(fn (int $number): string => 'LD-IMP-' . str_pad((string) $number, 3, '0', STR_PAD_LEFT))->all())
            ->delete();
    }

    private function images(int $productNumber, array $files): array
    {
        return array_map(
            fn (string $file): string => '/storage/products/imported-optimized/product-' . str_pad((string) $productNumber, 2, '0', STR_PAD_LEFT) . '/' . preg_replace('/\.[^.]+$/', '.jpg', $file),
            $files
        );
    }
};
