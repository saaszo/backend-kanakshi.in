<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoSeoGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_slug_is_auto_generated_and_uniqued(): void
    {
        $first = Category::query()->create([
            'name' => 'God Idols',
            'is_active' => true,
        ]);

        $second = Category::query()->create([
            'name' => 'God Idols',
            'is_active' => true,
        ]);

        $this->assertSame('god-idols', $first->slug);
        $this->assertSame('god-idols-1', $second->slug);
    }

    public function test_product_slug_meta_and_schema_are_auto_generated(): void
    {
        StoreSetting::query()->create([
            'site_name' => 'Little Divinity',
            'custom_domain' => 'https://littledivinity.in',
            'currency' => 'INR',
        ]);

        $category = Category::query()->create([
            'name' => 'God Idols',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Little Divinity Krishna Brass Idol',
            'short_desc' => 'A premium handcrafted Krishna idol for sacred corners.',
            'description' => 'Longer handcrafted description for the product page.',
            'price' => 12999,
            'sale_price' => 9999,
            'stock' => 8,
            'images' => ['/products/krishna-idol.jpg'],
            'is_active' => true,
        ]);

        $this->assertSame('little-divinity-krishna-brass-idol', $product->slug);
        $this->assertSame('Little Divinity Krishna Brass Idol', $product->meta_title);
        $this->assertSame('A premium handcrafted Krishna idol for sacred corners.', $product->meta_desc);
        $this->assertNotNull($product->custom_schema);

        $schema = json_decode((string) $product->custom_schema, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('Product', $schema['@type']);
        $this->assertSame('Little Divinity Krishna Brass Idol', $schema['name']);
        $this->assertSame('https://littledivinity.in/product/little-divinity-krishna-brass-idol', $schema['offers']['url']);
        $this->assertSame('INR', $schema['offers']['priceCurrency']);
    }
}
