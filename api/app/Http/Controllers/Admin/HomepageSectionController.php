<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesAdminUploads;
use App\Http\Controllers\Controller;
use App\Models\HomepageSection;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class HomepageSectionController extends Controller
{
    use HandlesAdminUploads;

    private const HERO_SLIDE_COUNT = 5;
    private const HERO_PROMO_COUNT = 2;
    private const MEDIA_URL_MAX_LENGTH = 2048;
    private const FRONTEND_REVALIDATE_SECRET_FALLBACK = 'little-divinity-homepage-revalidate';

    public function index(): View
    {
        return view('admin.homepage-sections.index', [
            'sections' => HomepageSection::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function edit(HomepageSection $homepageSection): View
    {
        return view('admin.homepage-sections.edit', [
            'section' => $homepageSection,
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function editHero(): View
    {
        $section = $this->getHeroSection();
        $config = $section->config ?? [];

        return view('admin.homepage-sections.hero', [
            'section' => $section,
            'sliderSettings' => array_merge([
                'show_text' => true,
                'show_dots' => false,
                'show_arrows' => true,
                'autoplay_ms' => 3500,
                'nav_gap' => 34,
            ], $config['slider_settings'] ?? []),
            'secondaryButtonText' => (string) ($config['secondary_button_text'] ?? ''),
            'secondaryButtonUrl' => (string) ($config['secondary_button_url'] ?? ''),
            'slides' => $this->normalizeHeroSlides($config['slides'] ?? []),
            'promos' => $this->normalizeHeroPromos($config['promos'] ?? []),
        ]);
    }

    public function update(Request $request, HomepageSection $homepageSection): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:150'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'heading' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'button_text' => ['nullable', 'string', 'max:120'],
            'button_url' => ['nullable', 'string', 'max:2048'],
            'image_url' => ['nullable', 'string', 'max:255'],
            'image_file' => ['nullable', 'image', 'max:5120'],
            'mobile_image_url' => ['nullable', 'string', 'max:255'],
            'mobile_image_file' => ['nullable', 'image', 'max:5120'],
            'side_image_url' => ['nullable', 'string', 'max:255'],
            'side_image_file' => ['nullable', 'image', 'max:5120'],
            'side_secondary_image_url' => ['nullable', 'string', 'max:255'],
            'side_secondary_image_file' => ['nullable', 'image', 'max:5120'],
            'sort_order' => ['required', 'integer'],
            'config_json' => ['nullable', 'string'],
        ]);

        $config = [];
        if (! empty($validated['config_json'])) {
            $config = json_decode($validated['config_json'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors([
                    'config_json' => 'Config JSON is invalid.',
                ])->withInput();
            }
        }

        unset($validated['config_json']);

        if ($request->hasFile('image_file')) {
            $validated['image_url'] = $this->storeAdminUpload($request->file('image_file'), 'homepage', 'Homepage main image');
        }
        if ($request->hasFile('mobile_image_file')) {
            $validated['mobile_image_url'] = $this->storeAdminUpload($request->file('mobile_image_file'), 'homepage', 'Homepage mobile image');
        }
        if ($request->hasFile('side_image_file')) {
            $validated['side_image_url'] = $this->storeAdminUpload($request->file('side_image_file'), 'homepage', 'Homepage side image');
        }
        if ($request->hasFile('side_secondary_image_file')) {
            $validated['side_secondary_image_url'] = $this->storeAdminUpload($request->file('side_secondary_image_file'), 'homepage', 'Homepage secondary side image');
        }

        unset(
            $validated['image_file'],
            $validated['mobile_image_file'],
            $validated['side_image_file'],
            $validated['side_secondary_image_file']
        );

        $homepageSection->update($validated + [
            'is_active' => $request->boolean('is_active'),
            'config' => $config,
        ]);

        $this->triggerFrontendRevalidation(['/', '/shop']);

        return back()->with('status', 'Homepage section updated successfully.');
    }

    public function updateHero(Request $request): RedirectResponse
    {
        $section = $this->getHeroSection();
        $existingConfig = $section->config ?? [];
        $existingSlides = $this->normalizeHeroSlides($existingConfig['slides'] ?? []);
        $existingPromos = $this->normalizeHeroPromos($existingConfig['promos'] ?? []);

        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:150'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'heading' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'button_text' => ['nullable', 'string', 'max:120'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'secondary_button_text' => ['nullable', 'string', 'max:120'],
            'secondary_button_url' => ['nullable', 'string', 'max:2048'],
            'sort_order' => ['required', 'integer'],
            'show_text' => ['nullable', 'boolean'],
            'show_dots' => ['nullable', 'boolean'],
            'show_arrows' => ['nullable', 'boolean'],
            'autoplay_ms' => ['nullable', 'integer', 'min:1000', 'max:15000'],
            'nav_gap' => ['nullable', 'integer', 'min:0', 'max:240'],
            'slide_urls' => ['nullable', 'array'],
            'slide_urls.*' => ['nullable', 'string', 'max:'.self::MEDIA_URL_MAX_LENGTH],
            'slide_files' => ['nullable', 'array'],
            'slide_files.*' => ['nullable', 'image', 'max:5120'],
            'clear_slide_image' => ['nullable', 'array'],
            'clear_slide_image.*' => ['nullable', 'boolean'],
            'slides' => ['nullable', 'array'],
            'slides.*.title' => ['nullable', 'string', 'max:120'],
            'slides.*.alt' => ['nullable', 'string', 'max:150'],
            'slides.*.href' => ['nullable', 'string', 'max:2048'],
            'slides.*.crop_x' => ['nullable', 'integer', 'min:0', 'max:100'],
            'slides.*.crop_y' => ['nullable', 'integer', 'min:0', 'max:100'],
            'slides.*.crop_zoom' => ['nullable', 'numeric', 'min:1', 'max:2.5'],
            'promos' => ['nullable', 'array'],
            'promos.*.title' => ['nullable', 'string', 'max:120'],
            'promos.*.subtitle' => ['nullable', 'string', 'max:180'],
            'promos.*.href' => ['nullable', 'string', 'max:2048'],
            'promos.*.crop_x' => ['nullable', 'integer', 'min:0', 'max:100'],
            'promos.*.crop_y' => ['nullable', 'integer', 'min:0', 'max:100'],
            'promos.*.crop_zoom' => ['nullable', 'numeric', 'min:1', 'max:2.5'],
            'promo_urls' => ['nullable', 'array'],
            'promo_urls.*' => ['nullable', 'string', 'max:'.self::MEDIA_URL_MAX_LENGTH],
            'promo_files' => ['nullable', 'array'],
            'promo_files.*' => ['nullable', 'image', 'max:5120'],
            'clear_promo_image' => ['nullable', 'array'],
            'clear_promo_image.*' => ['nullable', 'boolean'],
        ]);

        $slides = [];
        for ($index = 0; $index < self::HERO_SLIDE_COUNT; $index++) {
            $current = $existingSlides[$index] ?? [];
            $image = trim((string) $request->input("slide_urls.$index", ''));

            if ($request->boolean("clear_slide_image.$index")) {
                $image = null;
            } elseif ($request->hasFile("slide_files.$index")) {
                $image = $this->storeAdminUpload($request->file("slide_files.$index"), 'homepage/hero', 'Hero slide');
            } elseif ($image === '') {
                $image = $current['image'] ?? null;
            }

            $slide = [
                'title' => trim((string) $request->input("slides.$index.title", '')),
                'alt' => trim((string) $request->input("slides.$index.alt", '')),
                'href' => trim((string) $request->input("slides.$index.href", '')),
                'image' => $image,
                'crop_x' => max(0, min(100, (int) $request->input("slides.$index.crop_x", $current['crop_x'] ?? 50))),
                'crop_y' => max(0, min(100, (int) $request->input("slides.$index.crop_y", $current['crop_y'] ?? 50))),
                'crop_zoom' => max(1, min(2.5, (float) $request->input("slides.$index.crop_zoom", $current['crop_zoom'] ?? 1))),
                'is_active' => $request->boolean("slides.$index.is_active"),
            ];

            $slides[] = $slide;
        }

        $promos = [];
        for ($index = 0; $index < self::HERO_PROMO_COUNT; $index++) {
            $current = $existingPromos[$index] ?? [];
            $image = trim((string) $request->input("promo_urls.$index", ''));

            if ($request->boolean("clear_promo_image.$index")) {
                $image = null;
            } elseif ($request->hasFile("promo_files.$index")) {
                $image = $this->storeAdminUpload($request->file("promo_files.$index"), 'homepage/hero', 'Hero side promo');
            } elseif ($image === '') {
                $image = $current['image'] ?? null;
            }

            $promos[] = [
                'title' => trim((string) $request->input("promos.$index.title", '')),
                'subtitle' => trim((string) $request->input("promos.$index.subtitle", '')),
                'href' => trim((string) $request->input("promos.$index.href", '')),
                'image' => $image,
                'crop_x' => max(0, min(100, (int) $request->input("promos.$index.crop_x", $current['crop_x'] ?? 50))),
                'crop_y' => max(0, min(100, (int) $request->input("promos.$index.crop_y", $current['crop_y'] ?? 50))),
                'crop_zoom' => max(1, min(2.5, (float) $request->input("promos.$index.crop_zoom", $current['crop_zoom'] ?? 1))),
                'show_text' => $request->boolean("promos.$index.show_text"),
                'is_active' => $request->boolean("promos.$index.is_active"),
            ];
        }

        $section->update([
            'label' => $validated['label'] ?? $section->label,
            'title' => $validated['title'] ?? $section->title,
            'subtitle' => $validated['subtitle'] ?? $section->subtitle,
            'heading' => $validated['heading'] ?? $section->heading,
            'content' => $validated['content'] ?? $section->content,
            'button_text' => $validated['button_text'] ?? $section->button_text,
            'button_url' => $validated['button_url'] ?? $section->button_url,
            'sort_order' => $validated['sort_order'],
            'is_active' => $request->boolean('is_active'),
            'config' => [
                'slider_settings' => [
                    'show_text' => $request->boolean('show_text'),
                    'show_dots' => $request->boolean('show_dots'),
                    'show_arrows' => $request->boolean('show_arrows'),
                    'autoplay_ms' => (int) ($validated['autoplay_ms'] ?? 3500),
                    'nav_gap' => (int) ($validated['nav_gap'] ?? 34),
                ],
                'secondary_button_text' => trim((string) ($validated['secondary_button_text'] ?? '')),
                'secondary_button_url' => trim((string) ($validated['secondary_button_url'] ?? '')),
                'slides' => $slides,
                'promos' => $promos,
            ],
        ]);

        $this->triggerFrontendRevalidation(['/', '/shop']);

        return back()->with('status', 'Hero slider updated successfully.');
    }

    private function getHeroSection(): HomepageSection
    {
        $section = HomepageSection::withTrashed()->firstOrNew([
            'section_key' => 'hero',
        ]);

        if ($section->trashed()) {
            $section->restore();
        }

        if (! filled($section->section_type)) {
            $section->section_type = 'hero';
        }

        if (! filled($section->label)) {
            $section->label = 'Homepage Hero';
        }

        if (! filled($section->title)) {
            $section->title = 'Homepage Hero';
        }

        if (! $section->sort_order) {
            $section->sort_order = 1;
        }

        if (! $section->exists) {
            $section->is_active = true;
        }

        $config = is_array($section->config) ? $section->config : [];
        $config['slider_settings'] = array_merge([
            'show_text' => true,
            'show_dots' => false,
            'show_arrows' => true,
            'autoplay_ms' => 3500,
            'nav_gap' => 34,
        ], is_array($config['slider_settings'] ?? null) ? $config['slider_settings'] : []);
        $config['slides'] = $config['slides'] ?? [];
        $config['promos'] = $config['promos'] ?? [];
        $config['secondary_button_text'] = (string) ($config['secondary_button_text'] ?? '');
        $config['secondary_button_url'] = (string) ($config['secondary_button_url'] ?? '');
        $section->config = $config;

        if (! $section->exists || $section->isDirty()) {
            $section->save();
        }

        return $section;
    }

    private function normalizeHeroSlides(array $slides): array
    {
        $normalized = [];

        for ($index = 0; $index < self::HERO_SLIDE_COUNT; $index++) {
            $slide = $slides[$index] ?? [];
            $image = $slide['image'] ?? null;
            $normalized[] = [
                'title' => $slide['title'] ?? '',
                'alt' => $slide['alt'] ?? '',
                'href' => $slide['href'] ?? '',
                'image' => $image,
                'preview_image' => $this->resolveAdminMediaPreviewUrl($image),
                'crop_x' => (int) ($slide['crop_x'] ?? 50),
                'crop_y' => (int) ($slide['crop_y'] ?? 50),
                'crop_zoom' => (float) ($slide['crop_zoom'] ?? 1),
                'is_active' => array_key_exists('is_active', $slide)
                    ? (bool) $slide['is_active']
                    : filled($image),
            ];
        }

        return $normalized;
    }

    private function normalizeHeroPromos(array $promos): array
    {
        $normalized = [];

        for ($index = 0; $index < self::HERO_PROMO_COUNT; $index++) {
            $promo = $promos[$index] ?? [];
            $image = $promo['image'] ?? null;
            $normalized[] = [
                'title' => $promo['title'] ?? '',
                'subtitle' => $promo['subtitle'] ?? '',
                'href' => $promo['href'] ?? '',
                'image' => $image,
                'preview_image' => $this->resolveAdminMediaPreviewUrl($image),
                'crop_x' => (int) ($promo['crop_x'] ?? 50),
                'crop_y' => (int) ($promo['crop_y'] ?? 50),
                'crop_zoom' => (float) ($promo['crop_zoom'] ?? 1),
                'show_text' => (bool) ($promo['show_text'] ?? true),
                'is_active' => array_key_exists('is_active', $promo)
                    ? (bool) $promo['is_active']
                    : filled($image),
            ];
        }

        return $normalized;
    }

    private function resolveAdminMediaPreviewUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/reference-assets/') || str_starts_with($path, 'reference-assets/')) {
            $frontendUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');

            return $frontendUrl.'/'.ltrim($path, '/');
        }

        if (str_starts_with($path, '/storage/') || str_starts_with($path, 'storage/')) {
            $appUrl = rtrim((string) config('app.url'), '/');

            return $appUrl.'/'.ltrim($path, '/');
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        return rtrim((string) config('app.url'), '/').'/'.ltrim($path, '/');
    }

    private function triggerFrontendRevalidation(array $paths): void
    {
        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');
        $secret = (string) env('FRONTEND_REVALIDATE_SECRET', self::FRONTEND_REVALIDATE_SECRET_FALLBACK);

        if ($frontendUrl === '') {
            return;
        }

        try {
            Http::timeout(8)->acceptJson()->post($frontendUrl.'/api/revalidate', [
                'secret' => $secret,
                'paths' => array_values(array_unique($paths)),
            ])->throw();
        } catch (\Throwable $exception) {
            Log::warning('Frontend revalidation request failed after homepage section update.', [
                'frontend_url' => $frontendUrl,
                'paths' => $paths,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
