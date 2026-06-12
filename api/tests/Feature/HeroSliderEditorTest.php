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
                'heading' => 'Heading',
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

        $this->assertSame(58, $config['promos'][0]['crop_x']);
        $this->assertSame(33, $config['promos'][0]['crop_y']);
        $this->assertSame(1.2, $config['promos'][0]['crop_zoom']);
        $this->assertSame('https://example.com/promo.jpg', $config['promos'][0]['image']);
    }
}
