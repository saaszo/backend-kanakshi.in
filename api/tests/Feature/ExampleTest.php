<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The API health endpoint should expose the migration phase state.
     */
    public function test_the_api_health_endpoint_reports_phase_one_foundation(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.phase', 'phase-1-foundation');
    }

    /**
     * The catalog endpoint should stay stable before legacy tables are migrated.
     */
    public function test_the_catalog_endpoint_returns_a_safe_empty_payload_without_tables(): void
    {
        $response = $this->getJson('/api/v1/catalog/products');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.items', [])
            ->assertJsonPath('data.pagination.total', 0);
    }

    /**
     * Parent categories should be exposed for the storefront shell.
     */
    public function test_the_category_endpoint_returns_active_parent_categories(): void
    {
        DB::table('categories')->insert([
            [
                'id' => 1,
                'parent_id' => null,
                'name' => 'Jewelry',
                'slug' => 'jewelry',
                'is_active' => 1,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Child Category',
                'slug' => 'child-category',
                'parent_id' => 1,
                'is_active' => 1,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->getJson('/api/v1/catalog/categories');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'jewelry');
    }

    /**
     * Product detail should be available by slug for storefront hydration.
     */
    public function test_the_product_show_endpoint_returns_the_matching_product(): void
    {
        DB::table('categories')->insert([
            'id' => 1,
            'name' => 'Jewelry',
            'slug' => 'jewelry',
            'is_active' => 1,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'category_id' => 1,
            'name' => 'Temple Necklace',
            'slug' => 'temple-necklace',
            'price' => 2999,
            'sale_price' => 2499,
            'images' => json_encode(['uploads/products/demo.jpg']),
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/catalog/products/temple-necklace');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', 'temple-necklace')
            ->assertJsonPath('data.category_slug', 'jewelry');
    }

    /**
     * Public store settings should be readable from the seeded settings table.
     */
    public function test_the_public_settings_endpoint_returns_store_defaults(): void
    {
        DB::table('settings')->insert([
            ['key_name' => 'site_name', 'value' => 'Luxury Jewelry Store', 'label' => 'Site Name', 'group_name' => 'general'],
            ['key_name' => 'site_currency', 'value' => 'INR', 'label' => 'Currency', 'group_name' => 'general'],
        ]);

        $response = $this->getJson('/api/v1/settings/public');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.site_name', 'Luxury Jewelry Store')
            ->assertJsonPath('data.site_currency', 'INR');
    }
}
