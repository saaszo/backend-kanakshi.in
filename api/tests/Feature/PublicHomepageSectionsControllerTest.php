<?php

namespace Tests\Feature;

use App\Models\HomepageSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicHomepageSectionsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_homepage_sections_normalizes_legacy_hero_slide_states(): void
    {
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
                        'title' => 'Legacy Slide One',
                        'image' => '/reference-assets/slide-one.png',
                    ],
                    [
                        'title' => 'Legacy Slide Two',
                        'image' => '/reference-assets/slide-two.png',
                    ],
                ],
                'promos' => [
                    [
                        'title' => 'Legacy Promo',
                        'image' => '/reference-assets/promo-one.png',
                    ],
                ],
            ],
        ]);

        $response = $this->getJson('/api/v1/settings/homepage-sections');

        $response->assertOk();
        $response->assertJsonPath('data.0.config.slides.0.is_active', true);
        $response->assertJsonPath('data.0.config.slides.1.is_active', true);
        $response->assertJsonPath('data.0.config.promos.0.is_active', true);
        $response->assertJsonPath('data.0.config.slides.0.crop_x', 50);
        $response->assertJsonPath('data.0.config.slides.0.crop_zoom', 1);
    }
}
