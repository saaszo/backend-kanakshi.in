<?php

namespace App\Http\Controllers\Api\Settings;

use App\Models\HomepageSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;

class PublicHomepageSectionsController
{
    public function __invoke(): JsonResponse
    {
        if (! Schema::hasTable('homepage_sections')) {
            return response()->json([
                'success' => true,
                'message' => 'Homepage sections table is not available yet.',
                'data' => [],
            ]);
        }

        $sections = HomepageSection::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (HomepageSection $section): array {
                $payload = $section->toArray();

                if ($section->section_key === 'hero') {
                    $config = is_array($payload['config'] ?? null) ? $payload['config'] : [];
                    $config['slides'] = $this->normalizeHeroSlides($config['slides'] ?? []);
                    $config['promos'] = $this->normalizeHeroPromos($config['promos'] ?? []);
                    $payload['config'] = $config;
                }

                return $payload;
            })
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'message' => 'Homepage sections fetched successfully.',
            'data' => $sections,
        ]);
    }

    private function normalizeHeroSlides(array $slides): array
    {
        return array_values(array_map(function (array $slide): array {
            $image = $slide['image'] ?? null;

            return [
                'title' => $slide['title'] ?? '',
                'image' => $image,
                'alt' => $slide['alt'] ?? '',
                'href' => $slide['href'] ?? '',
                'crop_x' => (int) ($slide['crop_x'] ?? 50),
                'crop_y' => (int) ($slide['crop_y'] ?? 50),
                'crop_zoom' => (float) ($slide['crop_zoom'] ?? 1),
                'is_active' => array_key_exists('is_active', $slide)
                    ? (bool) $slide['is_active']
                    : filled($image),
            ];
        }, $slides));
    }

    private function normalizeHeroPromos(array $promos): array
    {
        return array_values(array_map(function (array $promo): array {
            $image = $promo['image'] ?? null;

            return [
                'title' => $promo['title'] ?? '',
                'subtitle' => $promo['subtitle'] ?? '',
                'image' => $image,
                'href' => $promo['href'] ?? '',
                'show_text' => array_key_exists('show_text', $promo) ? (bool) $promo['show_text'] : true,
                'crop_x' => (int) ($promo['crop_x'] ?? 50),
                'crop_y' => (int) ($promo['crop_y'] ?? 50),
                'crop_zoom' => (float) ($promo['crop_zoom'] ?? 1),
                'is_active' => array_key_exists('is_active', $promo)
                    ? (bool) $promo['is_active']
                    : filled($image),
            ];
        }, $promos));
    }
}
