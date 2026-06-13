<?php

namespace Tests\Feature;

use App\Models\HomepageSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeroSliderEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_slide_and_promo_crop_settings(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.homepage-sections.hero.edit'))
            ->put(route('admin.homepage-sections.hero.update'), [
                'label' => 'Homepage Hero',
                'title' => 'Hero',
                'subtitle' => 'Sub',
                'heading' => 'Sacred Craft. Pure Brass. Pan-India Delivery.',
                'content' => 'Handcrafted god idols, home decor & festive gifting — trusted by 45,000+ customers across India.',
                'button_text' => 'Shop the Collection →',
                'button_url' => '/shop',
                'secondary_button_text' => 'Explore Gifting Picks',
                'secondary_button_url' => '/shop?category=gifting-edit',
                'sort_order' => 1,
                'is_active' => '1',
                'show_text' => '1',
                'show_dots' => '1',
                'show_arrows' => '1',
                'autoplay_ms' => 3600,
                'nav_gap' => 36,
                'slide_urls' => ['https://example.com/slide.jpg'],
                'slides' => [
                    [
                        'title' => 'Slide One',
                        'alt' => 'Slide alt',
                        'href' => '/shop?category=featured',
                        'crop_x' => 62,
                        'crop_y' => 41,
                        'crop_zoom' => 1.45,
                        'is_active' => '1',
                    ],
                ],
                'promo_urls' => ['https://example.com/promo.jpg'],
                'promos' => [
                    [
                        'title' => 'Promo One',
                        'subtitle' => 'Promo sub',
                        'href' => '/shop?category=decor',
                        'crop_x' => 58,
                        'crop_y' => 33,
                        'crop_zoom' => 1.2,
                        'show_text' => '1',
                        'is_active' => '1',
                    ],
                ],
            ]);

        $response
            ->assertRedirect(route('admin.homepage-sections.hero.edit'))
            ->assertSessionHas('status', 'Hero slider updated successfully.');

        $section = HomepageSection::query()->where('section_key', 'hero')->firstOrFail();
        $config = $section->config ?? [];

        $this->assertSame(62, $config['slides'][0]['crop_x']);
        $this->assertSame(41, $config['slides'][0]['crop_y']);
        $this->assertSame(1.45, $config['slides'][0]['crop_zoom']);
        $this->assertSame('https://example.com/slide.jpg', $config['slides'][0]['image']);
        $this->assertSame('Handcrafted god idols, home decor & festive gifting — trusted by 45,000+ customers across India.', $section->content);
        $this->assertSame('Shop the Collection →', $section->button_text);
        $this->assertSame('/shop', $section->button_url);
        $this->assertSame('Explore Gifting Picks', $config['secondary_button_text']);
        $this->assertSame('/shop?category=gifting-edit', $config['secondary_button_url']);

        $this->assertSame(58, $config['promos'][0]['crop_x']);
        $this->assertSame(33, $config['promos'][0]['crop_y']);
        $this->assertSame(1.2, $config['promos'][0]['crop_zoom']);
        $this->assertSame('https://example.com/promo.jpg', $config['promos'][0]['image']);
    }

    public function test_admin_can_save_existing_long_media_urls_in_hero_editor(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'is_active' => true,
        ]);

        $longSlideUrl = 'https://littledivinity.example/storage/homepage/hero/'.str_repeat('slide-path-segment-', 12).'.jpg';
        $longPromoUrl = 'https://littledivinity.example/storage/homepage/hero/'.str_repeat('promo-path-segment-', 12).'.jpg';

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.homepage-sections.hero.edit'))
            ->put(route('admin.homepage-sections.hero.update'), [
                'label' => 'Homepage Hero',
                'title' => 'Hero',
                'sort_order' => 1,
                'is_active' => '1',
                'slide_urls' => [$longSlideUrl],
                'slides' => [
                    [
                        'title' => 'Slide One',
                        'is_active' => '1',
                    ],
                ],
                'promo_urls' => [$longPromoUrl],
                'promos' => [
                    [
                        'title' => 'Promo One',
                        'is_active' => '1',
                    ],
                ],
            ]);

        $response
            ->assertRedirect(route('admin.homepage-sections.hero.edit'))
            ->assertSessionHas('status', 'Hero slider updated successfully.');

        $section = HomepageSection::query()->where('section_key', 'hero')->firstOrFail();
        $config = $section->config ?? [];

        $this->assertSame($longSlideUrl, $config['slides'][0]['image']);
        $this->assertSame($longPromoUrl, $config['promos'][0]['image']);
    }

    public function test_hero_editor_resolves_reference_asset_previews_for_admin(): void
    {
        config(['app.frontend_url' => 'https://littledivinity.example']);

        $admin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'is_active' => true,
        ]);

        HomepageSection::query()->create([
            'section_key' => 'hero',
            'section_type' => 'hero',
            'label' => 'Homepage Hero',
            'title' => 'Homepage Hero',
            'sort_order' => 1,
            'is_active' => true,
            'config' => [
                'slides' => [
                    [
                        'title' => 'Legacy Slide',
                        'image' => '/reference-assets/legacy-slide.png',
                        'alt' => 'Legacy preview',
                    ],
                ],
                'promos' => [],
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.homepage-sections.hero.edit'));

        $response->assertOk();
        $response->assertSee('https://littledivinity.example/reference-assets/legacy-slide.png', false);
        $response->assertSee('Currently visible on storefront rotation');
    }

    public function test_hero_editor_handles_legacy_malformed_media_config_without_crashing(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'is_active' => true,
        ]);

        HomepageSection::query()->create([
            'section_key' => 'hero',
            'section_type' => 'hero',
            'label' => 'Homepage Hero',
            'title' => 'Homepage Hero',
            'sort_order' => 1,
            'is_active' => true,
            'config' => [
                'slides' => [
                    [
                        'title' => ['unexpected-array'],
                        'image' => ['bad-media-payload'],
                    ],
                    'legacy-string-entry',
                ],
                'promos' => 'legacy-promo-string',
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.homepage-sections.hero.edit'));

        $response->assertOk();
        $response->assertSee('Hero Slider Configuration');
        $response->assertDontSee('bad-media-payload');
    }
}
