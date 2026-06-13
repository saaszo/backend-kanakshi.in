<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\AmazonProductLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AdminProductAmazonButtonTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_product_with_amazon_link_disabled_by_default(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'is_active' => true,
        ]);

        $category = Category::query()->create([
            'name' => 'Home Decor',
            'slug' => 'home-decor',
            'description' => 'Home decor',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name' => 'Brass Wall Accent',
                'price' => 5999,
                'shipping_type' => 'default',
                'amazon_link' => 'https://www.amazon.in/dp/B0G1SBBF4M',
                'images_input' => "/storage/products/demo.jpg\n",
                'is_active' => '1',
            ])
            ->assertRedirect();

        $product = Product::query()->where('name', 'Brass Wall Accent')->firstOrFail();

        $this->assertSame('https://www.amazon.in/dp/B0G1SBBF4M', $product->amazon_link);
        $this->assertFalse((bool) $product->amazon_button_enabled);
    }

    public function test_admin_can_enable_amazon_button_and_store_fetched_price(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'is_active' => true,
        ]);

        $category = Category::query()->create([
            'name' => 'Wall Decor',
            'slug' => 'wall-decor',
            'description' => 'Wall decor',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Temple Panel',
            'slug' => 'temple-panel',
            'price' => 6999,
            'shipping_type' => 'default',
            'stock' => 10,
            'images' => ['/storage/products/demo.jpg'],
            'is_active' => true,
        ]);

        $serviceMock = Mockery::mock(AmazonProductLinkService::class);
        $serviceMock->shouldReceive('normalizeUrl')
            ->once()
            ->with('https://www.amazon.in/gp/product/B0G1SBBF4M?ref_=abc')
            ->andReturn('https://www.amazon.in/dp/B0G1SBBF4M');
        $serviceMock->shouldReceive('fetchSnapshot')
            ->once()
            ->with('https://www.amazon.in/dp/B0G1SBBF4M')
            ->andReturn([
                'canonical_url' => 'https://www.amazon.in/dp/B0G1SBBF4M',
                'price' => 5499.00,
                'fetched_at' => now(),
            ]);
        $this->app->instance(AmazonProductLinkService::class, $serviceMock);

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), [
                'category_id' => $category->id,
                'name' => $product->name,
                'price' => 6999,
                'shipping_type' => 'default',
                'stock' => 10,
                'images_input' => "/storage/products/demo.jpg\n",
                'amazon_link' => 'https://www.amazon.in/gp/product/B0G1SBBF4M?ref_=abc',
                'amazon_button_enabled' => '1',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $product->refresh();

        $this->assertTrue((bool) $product->amazon_button_enabled);
        $this->assertSame('https://www.amazon.in/dp/B0G1SBBF4M', $product->amazon_link);
        $this->assertSame('5499.00', $product->amazon_price);
        $this->assertNotNull($product->amazon_price_fetched_at);
    }
}
