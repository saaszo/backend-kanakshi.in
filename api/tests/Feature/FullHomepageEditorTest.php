<?php

namespace Tests\Feature;

use App\Models\HomepageSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FullHomepageEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_full_homepage_content(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.homepage-sections.full.edit'))
            ->put(route('admin.homepage-sections.full.update'), [
                'label' => 'Full Homepage',
                'title' => 'Full Homepage Content',
                'sort_order' => 10,
                'is_active' => '1',
                'collections_section_is_active' => '1',
                'collections_eyebrow' => 'Collections',
                'collections_title' => 'Shop The Main Categories',
                'collections_button_text' => 'Browse All',
                'collections_button_url' => '/shop',
                'collections' => [
                    [
                        'title' => 'God Idols',
                        'subtitle' => 'Sacred brass centerpieces',
                        'image' => 'https://example.com/collections-1.jpg',
                        'href' => '/shop?category=god-idols',
                    ],
                ],
                'newsletter_is_active' => '1',
                'newsletter_eyebrow' => 'The Circle',
                'newsletter_title' => 'Get 10% Off',
                'newsletter_description' => 'Fresh launches and festive edits in your inbox.',
                'newsletter_button_text' => 'Join Now',
                'newsletter_placeholder' => 'name@example.com',
                'newsletter_footnote' => 'Trusted by thousands of homes.',
            ]);

        $response
            ->assertRedirect(route('admin.homepage-sections.full.edit'))
            ->assertSessionHas('status', 'Full homepage content updated successfully.');

        $section = HomepageSection::query()->where('section_key', 'full-homepage')->firstOrFail();
        $config = $section->config ?? [];

        $this->assertSame('Shop The Main Categories', data_get($config, 'collections.title'));
        $this->assertSame('Sacred brass centerpieces', data_get($config, 'collections.items.0.subtitle'));
        $this->assertSame('https://example.com/collections-1.jpg', data_get($config, 'collections.items.0.image'));
        $this->assertSame('Get 10% Off', data_get($config, 'newsletter.title'));
        $this->assertSame('Join Now', data_get($config, 'newsletter.button_text'));
    }

    public function test_admin_can_clear_existing_homepage_images(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'is_active' => true,
        ]);

        HomepageSection::query()->create([
            'section_key' => 'full-homepage',
            'section_type' => 'homepage_config',
            'label' => 'Full Homepage',
            'title' => 'Full Homepage Content',
            'sort_order' => 10,
            'is_active' => true,
            'config' => [
                'collections' => [
                    'items' => [
                        ['title' => 'God Idols', 'subtitle' => 'Sacred brass centerpieces', 'image' => 'https://example.com/collections-1.jpg', 'href' => '/shop?category=god-idols'],
                    ],
                ],
                'about_brand' => [
                    'image' => 'https://example.com/about-banner.jpg',
                ],
            ],
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.homepage-sections.full.edit'))
            ->put(route('admin.homepage-sections.full.update'), [
                'label' => 'Full Homepage',
                'title' => 'Full Homepage Content',
                'sort_order' => 10,
                'is_active' => '1',
                'clear_collections_image' => ['1'],
                'clear_about_brand_image' => '1',
            ]);

        $response
            ->assertRedirect(route('admin.homepage-sections.full.edit'))
            ->assertSessionHas('status', 'Full homepage content updated successfully.');

        $section = HomepageSection::query()->where('section_key', 'full-homepage')->firstOrFail();
        $config = $section->config ?? [];

        $this->assertSame('', data_get($config, 'collections.items.0.image'));
        $this->assertSame('', data_get($config, 'about_brand.image'));
    }
}
