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
    private const COLLECTION_COUNT = 4;
    private const OCCASION_COUNT = 5;
    private const EDITORIAL_PICK_COUNT = 3;
    private const TESTIMONIAL_COUNT = 3;
    private const INSTAGRAM_TILE_COUNT = 6;
    private const STATS_COUNT = 4;
    private const FESTIVE_EDIT_COUNT = 4;
    private const MEDIA_URL_MAX_LENGTH = 2048;
    private const FRONTEND_REVALIDATE_SECRET_FALLBACK = 'little-divinity-homepage-revalidate';

    public function index(): View
    {
        $this->getHeroSection();
        $this->getFullHomepageSection();

        return view('admin.homepage-sections.index', [
            'sections' => HomepageSection::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function edit(HomepageSection $homepageSection): View
    {
        return view('admin.homepage-sections.edit', [
            'section' => $homepageSection,
            'previewUrls' => [
                'image_url' => $this->resolveAdminMediaPreviewUrl($homepageSection->image_url),
                'mobile_image_url' => $this->resolveAdminMediaPreviewUrl($homepageSection->mobile_image_url),
                'side_image_url' => $this->resolveAdminMediaPreviewUrl($homepageSection->side_image_url),
                'side_secondary_image_url' => $this->resolveAdminMediaPreviewUrl($homepageSection->side_secondary_image_url),
            ],
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

    public function editFullHomepage(): View
    {
        $section = $this->getFullHomepageSection();

        return view('admin.homepage-sections.full-homepage', [
            'section' => $section,
            'config' => $this->normalizeFullHomepageConfig($section->config ?? []),
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

    public function updateFullHomepage(Request $request): RedirectResponse
    {
        $section = $this->getFullHomepageSection();
        $existingConfig = $section->config ?? [];

        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:150'],
            'title' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'collections.*.title' => ['nullable', 'string', 'max:120'],
            'collections.*.subtitle' => ['nullable', 'string', 'max:160'],
            'collections.*.image' => ['nullable', 'string', 'max:'.self::MEDIA_URL_MAX_LENGTH],
            'collections.*.href' => ['nullable', 'string', 'max:2048'],
            'collections_files' => ['nullable', 'array'],
            'collections_files.*' => ['nullable', 'image', 'max:5120'],
            'occasions.*.title' => ['nullable', 'string', 'max:120'],
            'occasions.*.image' => ['nullable', 'string', 'max:'.self::MEDIA_URL_MAX_LENGTH],
            'occasions.*.href' => ['nullable', 'string', 'max:2048'],
            'occasions_files' => ['nullable', 'array'],
            'occasions_files.*' => ['nullable', 'image', 'max:5120'],
            'editorial_picks.*.badge' => ['nullable', 'string', 'max:120'],
            'editorial_picks.*.title' => ['nullable', 'string', 'max:120'],
            'editorial_picks.*.description' => ['nullable', 'string', 'max:255'],
            'editorial_picks.*.image' => ['nullable', 'string', 'max:'.self::MEDIA_URL_MAX_LENGTH],
            'editorial_picks.*.href' => ['nullable', 'string', 'max:2048'],
            'editorial_picks_files' => ['nullable', 'array'],
            'editorial_picks_files.*' => ['nullable', 'image', 'max:5120'],
            'about_brand_file' => ['nullable', 'image', 'max:5120'],
            'founders_main_file' => ['nullable', 'image', 'max:5120'],
            'founders_side_file' => ['nullable', 'image', 'max:5120'],
            'testimonials.*.title' => ['nullable', 'string', 'max:120'],
            'testimonials.*.quote' => ['nullable', 'string', 'max:400'],
            'testimonials.*.author' => ['nullable', 'string', 'max:120'],
            'testimonials.*.stars' => ['nullable', 'string', 'max:10'],
            'instagram.tiles.*.image' => ['nullable', 'string', 'max:'.self::MEDIA_URL_MAX_LENGTH],
            'instagram.tiles.*.alt' => ['nullable', 'string', 'max:160'],
            'instagram_tiles_files' => ['nullable', 'array'],
            'instagram_tiles_files.*' => ['nullable', 'image', 'max:5120'],
            'stats.*.value' => ['nullable', 'string', 'max:40'],
            'stats.*.label' => ['nullable', 'string', 'max:120'],
            'festive_edits.*.badge' => ['nullable', 'string', 'max:120'],
            'festive_edits.*.title' => ['nullable', 'string', 'max:120'],
            'festive_edits.*.image' => ['nullable', 'string', 'max:'.self::MEDIA_URL_MAX_LENGTH],
            'festive_edits.*.href' => ['nullable', 'string', 'max:2048'],
            'festive_edits_files' => ['nullable', 'array'],
            'festive_edits_files.*' => ['nullable', 'image', 'max:5120'],
        ]);

        $defaults = $this->getDefaultFullHomepageConfig();

        // 1. Collections
        $collectionsItems = [];
        $existingCollections = $existingConfig['collections']['items'] ?? [];
        for ($index = 0; $index < self::COLLECTION_COUNT; $index++) {
            $current = $existingCollections[$index] ?? [];
            $image = trim((string) $request->input("collections.$index.image", ''));
            $shouldClear = $request->boolean("clear_collections_image.$index");

            if ($shouldClear) {
                $image = '';
            } elseif ($request->hasFile("collections_files.$index")) {
                $image = $this->storeAdminUpload($request->file("collections_files.$index"), 'homepage/collections', 'Collection card');
            } elseif ($image === '') {
                $image = array_key_exists('image', $current)
                    ? (string) ($current['image'] ?? '')
                    : (string) ($defaults['collections']['items'][$index]['image'] ?? '');
            }

            $collectionsItems[] = [
                'title' => $this->sanitizeTextValue($request->input("collections.$index.title")),
                'subtitle' => $this->sanitizeTextValue($request->input("collections.$index.subtitle")),
                'href' => $this->sanitizeTextValue($request->input("collections.$index.href")),
                'image' => is_string($image) ? $image : '',
            ];
        }

        // 2. Occasions
        $occasionsItems = [];
        $existingOccasions = $existingConfig['occasions']['items'] ?? [];
        for ($index = 0; $index < self::OCCASION_COUNT; $index++) {
            $current = $existingOccasions[$index] ?? [];
            $image = trim((string) $request->input("occasions.$index.image", ''));
            $shouldClear = $request->boolean("clear_occasions_image.$index");

            if ($shouldClear) {
                $image = '';
            } elseif ($request->hasFile("occasions_files.$index")) {
                $image = $this->storeAdminUpload($request->file("occasions_files.$index"), 'homepage/occasions', 'Occasion card');
            } elseif ($image === '') {
                $image = array_key_exists('image', $current)
                    ? (string) ($current['image'] ?? '')
                    : (string) ($defaults['occasions']['items'][$index]['image'] ?? '');
            }

            $occasionsItems[] = [
                'title' => $this->sanitizeTextValue($request->input("occasions.$index.title")),
                'href' => $this->sanitizeTextValue($request->input("occasions.$index.href")),
                'image' => is_string($image) ? $image : '',
            ];
        }

        // 3. Editorial Picks
        $editorialPicksItems = [];
        $existingEditorial = $existingConfig['editorial_picks']['items'] ?? [];
        for ($index = 0; $index < self::EDITORIAL_PICK_COUNT; $index++) {
            $current = $existingEditorial[$index] ?? [];
            $image = trim((string) $request->input("editorial_picks.$index.image", ''));
            $shouldClear = $request->boolean("clear_editorial_picks_image.$index");

            if ($shouldClear) {
                $image = '';
            } elseif ($request->hasFile("editorial_picks_files.$index")) {
                $image = $this->storeAdminUpload($request->file("editorial_picks_files.$index"), 'homepage/editorial', 'Editorial pick');
            } elseif ($image === '') {
                $image = array_key_exists('image', $current)
                    ? (string) ($current['image'] ?? '')
                    : (string) ($defaults['editorial_picks']['items'][$index]['image'] ?? '');
            }

            $editorialPicksItems[] = [
                'badge' => $this->sanitizeTextValue($request->input("editorial_picks.$index.badge")),
                'title' => $this->sanitizeTextValue($request->input("editorial_picks.$index.title")),
                'description' => $this->sanitizeTextValue($request->input("editorial_picks.$index.description")),
                'href' => $this->sanitizeTextValue($request->input("editorial_picks.$index.href")),
                'image' => is_string($image) ? $image : '',
            ];
        }

        // 4. About Brand
        $aboutBrandImage = trim((string) $request->input('about_brand_image', ''));
        if ($request->boolean('clear_about_brand_image')) {
            $aboutBrandImage = '';
        } elseif ($request->hasFile('about_brand_file')) {
            $aboutBrandImage = $this->storeAdminUpload($request->file('about_brand_file'), 'homepage/about', 'About brand image');
        } elseif ($aboutBrandImage === '') {
            $aboutBrandImage = array_key_exists('image', $existingConfig['about_brand'] ?? [])
                ? (string) ($existingConfig['about_brand']['image'] ?? '')
                : (string) ($defaults['about_brand']['image'] ?? '');
        }

        // 5. Founders
        $foundersMainImage = trim((string) $request->input('founders_main_image', ''));
        if ($request->boolean('clear_founders_main_image')) {
            $foundersMainImage = '';
        } elseif ($request->hasFile('founders_main_file')) {
            $foundersMainImage = $this->storeAdminUpload($request->file('founders_main_file'), 'homepage/founders', 'Founders main image');
        } elseif ($foundersMainImage === '') {
            $foundersMainImage = array_key_exists('main_image', $existingConfig['founders'] ?? [])
                ? (string) ($existingConfig['founders']['main_image'] ?? '')
                : (string) ($defaults['founders']['main_image'] ?? '');
        }

        $foundersSideImage = trim((string) $request->input('founders_side_image', ''));
        if ($request->boolean('clear_founders_side_image')) {
            $foundersSideImage = '';
        } elseif ($request->hasFile('founders_side_file')) {
            $foundersSideImage = $this->storeAdminUpload($request->file('founders_side_file'), 'homepage/founders', 'Founders side image');
        } elseif ($foundersSideImage === '') {
            $foundersSideImage = array_key_exists('side_image', $existingConfig['founders'] ?? [])
                ? (string) ($existingConfig['founders']['side_image'] ?? '')
                : (string) ($defaults['founders']['side_image'] ?? '');
        }

        // 6. Testimonials
        $testimonialsItems = $this->buildRepeaterItems($request->input('testimonials', []), self::TESTIMONIAL_COUNT, $defaults['testimonials']['items'], ['title', 'quote', 'author', 'stars']);

        // 7. Newsletter - handled in config array

        // 8. Instagram Grid
        $instagramTiles = [];
        $existingInstagram = $existingConfig['instagram']['tiles'] ?? [];
        for ($index = 0; $index < self::INSTAGRAM_TILE_COUNT; $index++) {
            $current = $existingInstagram[$index] ?? [];
            $image = trim((string) $request->input("instagram.tiles.$index.image", ''));
            $shouldClear = $request->boolean("clear_instagram_tiles_image.$index");

            if ($shouldClear) {
                $image = '';
            } elseif ($request->hasFile("instagram_tiles_files.$index")) {
                $image = $this->storeAdminUpload($request->file("instagram_tiles_files.$index"), 'homepage/instagram', 'Instagram tile');
            } elseif ($image === '') {
                $image = array_key_exists('image', $current)
                    ? (string) ($current['image'] ?? '')
                    : (string) ($defaults['instagram']['tiles'][$index]['image'] ?? '');
            }

            $instagramTiles[] = [
                'image' => is_string($image) ? $image : '',
                'alt' => $this->sanitizeTextValue($request->input("instagram.tiles.$index.alt")),
            ];
        }

        // 9. Stats
        $statsItems = $this->buildRepeaterItems($request->input('stats', []), self::STATS_COUNT, $defaults['stats']['items'], ['value', 'label']);

        // 10. Festive Edits
        $festiveEditsItems = [];
        $existingFestive = $existingConfig['festive_edits']['items'] ?? [];
        for ($index = 0; $index < self::FESTIVE_EDIT_COUNT; $index++) {
            $current = $existingFestive[$index] ?? [];
            $image = trim((string) $request->input("festive_edits.$index.image", ''));
            $shouldClear = $request->boolean("clear_festive_edits_image.$index");

            if ($shouldClear) {
                $image = '';
            } elseif ($request->hasFile("festive_edits_files.$index")) {
                $image = $this->storeAdminUpload($request->file("festive_edits_files.$index"), 'homepage/festive', 'Festive edit card');
            } elseif ($image === '') {
                $image = array_key_exists('image', $current)
                    ? (string) ($current['image'] ?? '')
                    : (string) ($defaults['festive_edits']['items'][$index]['image'] ?? '');
            }

            $festiveEditsItems[] = [
                'badge' => $this->sanitizeTextValue($request->input("festive_edits.$index.badge")),
                'title' => $this->sanitizeTextValue($request->input("festive_edits.$index.title")),
                'href' => $this->sanitizeTextValue($request->input("festive_edits.$index.href")),
                'image' => is_string($image) ? $image : '',
            ];
        }

        $config = [
            'collections' => [
                'is_active' => $request->boolean('collections_section_is_active'),
                'eyebrow' => $this->sanitizeTextValue($request->input('collections_eyebrow')),
                'title' => $this->sanitizeTextValue($request->input('collections_title')),
                'button_text' => $this->sanitizeTextValue($request->input('collections_button_text')),
                'button_url' => $this->sanitizeTextValue($request->input('collections_button_url')),
                'items' => $collectionsItems,
            ],
            'occasions' => [
                'is_active' => $request->boolean('occasions_section_is_active'),
                'eyebrow' => $this->sanitizeTextValue($request->input('occasions_eyebrow')),
                'title' => $this->sanitizeTextValue($request->input('occasions_title')),
                'items' => $occasionsItems,
            ],
            'editorial_picks' => [
                'is_active' => $request->boolean('editorial_picks_section_is_active'),
                'items' => $editorialPicksItems,
            ],
            'about_brand' => [
                'is_active' => $request->boolean('about_brand_is_active'),
                'eyebrow' => $this->sanitizeTextValue($request->input('about_brand_eyebrow')),
                'title' => $this->sanitizeTextValue($request->input('about_brand_title')),
                'paragraph_one' => $this->sanitizeTextValue($request->input('about_brand_paragraph_one')),
                'paragraph_two' => $this->sanitizeTextValue($request->input('about_brand_paragraph_two')),
                'button_text' => $this->sanitizeTextValue($request->input('about_brand_button_text')),
                'button_url' => $this->sanitizeTextValue($request->input('about_brand_button_url')),
                'image' => is_string($aboutBrandImage) ? $aboutBrandImage : '',
            ],
            'founders' => [
                'is_active' => $request->boolean('founders_is_active'),
                'eyebrow' => $this->sanitizeTextValue($request->input('founders_eyebrow')),
                'title' => $this->sanitizeTextValue($request->input('founders_title')),
                'content' => $this->sanitizeTextValue($request->input('founders_content')),
                'button_text' => $this->sanitizeTextValue($request->input('founders_button_text')),
                'button_url' => $this->sanitizeTextValue($request->input('founders_button_url')),
                'main_image' => is_string($foundersMainImage) ? $foundersMainImage : '',
                'side_image' => is_string($foundersSideImage) ? $foundersSideImage : '',
            ],
            'testimonials' => [
                'is_active' => $request->boolean('testimonials_is_active'),
                'eyebrow' => $this->sanitizeTextValue($request->input('testimonials_eyebrow')),
                'title' => $this->sanitizeTextValue($request->input('testimonials_title')),
                'items' => $testimonialsItems,
            ],
            'newsletter' => [
                'is_active' => $request->boolean('newsletter_is_active'),
                'eyebrow' => $this->sanitizeTextValue($request->input('newsletter_eyebrow')),
                'title' => $this->sanitizeTextValue($request->input('newsletter_title')),
                'description' => $this->sanitizeTextValue($request->input('newsletter_description')),
                'button_text' => $this->sanitizeTextValue($request->input('newsletter_button_text')),
                'placeholder' => $this->sanitizeTextValue($request->input('newsletter_placeholder')),
                'footnote' => $this->sanitizeTextValue($request->input('newsletter_footnote')),
            ],
            'instagram' => [
                'is_active' => $request->boolean('instagram_is_active'),
                'eyebrow' => $this->sanitizeTextValue($request->input('instagram_eyebrow')),
                'title' => $this->sanitizeTextValue($request->input('instagram_title')),
                'profile_url' => $this->sanitizeTextValue($request->input('instagram_profile_url')),
                'profile_label' => $this->sanitizeTextValue($request->input('instagram_profile_label')),
                'tiles' => $instagramTiles,
            ],
            'stats' => [
                'is_active' => $request->boolean('stats_is_active'),
                'eyebrow' => $this->sanitizeTextValue($request->input('stats_eyebrow')),
                'title' => $this->sanitizeTextValue($request->input('stats_title')),
                'items' => $statsItems,
            ],
            'festive_edits' => [
                'is_active' => $request->boolean('festive_edits_is_active'),
                'eyebrow' => $this->sanitizeTextValue($request->input('festive_edits_eyebrow')),
                'title' => $this->sanitizeTextValue($request->input('festive_edits_title')),
                'button_text' => $this->sanitizeTextValue($request->input('festive_edits_button_text')),
                'button_url' => $this->sanitizeTextValue($request->input('festive_edits_button_url')),
                'items' => $festiveEditsItems,
            ],
        ];

        $section->update([
            'label' => $validated['label'] ?? $section->label,
            'title' => $validated['title'] ?? $section->title,
            'sort_order' => $validated['sort_order'],
            'is_active' => $request->boolean('is_active'),
            'config' => $config,
        ]);

        $this->triggerFrontendRevalidation(['/']);

        return back()->with('status', 'Full homepage content updated successfully.');
    }

    private function getFullHomepageSection(): HomepageSection
    {
        $section = HomepageSection::withTrashed()->firstOrNew([
            'section_key' => 'full-homepage',
        ]);

        if ($section->trashed()) {
            $section->restore();
        }

        if (! filled($section->section_type)) {
            $section->section_type = 'homepage_config';
        }

        if (! filled($section->label)) {
            $section->label = 'Full Homepage';
        }

        if (! filled($section->title)) {
            $section->title = 'Full Homepage Content';
        }

        if (! $section->sort_order) {
            $section->sort_order = 10;
        }

        if (! $section->exists) {
            $section->is_active = true;
        }

        $section->config = $this->normalizeFullHomepageConfig($section->config ?? []);

        if (! $section->exists || $section->isDirty()) {
            $section->save();
        }

        return $section;
    }

    private function normalizeFullHomepageConfig(array|string|null $config): array
    {
        $defaults = $this->getDefaultFullHomepageConfig();
        $config = is_array($config) ? $config : [];

        $merged = array_replace_recursive($defaults, $config);

        $merged['collections']['items'] = $this->buildRepeaterItemsWithPreview($merged['collections']['items'] ?? [], self::COLLECTION_COUNT, $defaults['collections']['items'], ['title', 'subtitle', 'image', 'href']);
        $merged['occasions']['items'] = $this->buildRepeaterItemsWithPreview($merged['occasions']['items'] ?? [], self::OCCASION_COUNT, $defaults['occasions']['items'], ['title', 'image', 'href']);
        $merged['editorial_picks']['items'] = $this->buildRepeaterItemsWithPreview($merged['editorial_picks']['items'] ?? [], self::EDITORIAL_PICK_COUNT, $defaults['editorial_picks']['items'], ['badge', 'title', 'description', 'image', 'href']);
        
        $merged['about_brand']['preview_image'] = $this->resolveAdminMediaPreviewUrl($merged['about_brand']['image'] ?? $defaults['about_brand']['image']);
        $merged['founders']['preview_main_image'] = $this->resolveAdminMediaPreviewUrl($merged['founders']['main_image'] ?? $defaults['founders']['main_image']);
        $merged['founders']['preview_side_image'] = $this->resolveAdminMediaPreviewUrl($merged['founders']['side_image'] ?? $defaults['founders']['side_image']);

        $merged['testimonials']['items'] = $this->buildRepeaterItemsWithPreview($merged['testimonials']['items'] ?? [], self::TESTIMONIAL_COUNT, $defaults['testimonials']['items'], ['title', 'quote', 'author', 'stars']);
        $merged['instagram']['tiles'] = $this->buildRepeaterItemsWithPreview($merged['instagram']['tiles'] ?? [], self::INSTAGRAM_TILE_COUNT, $defaults['instagram']['tiles'], ['image', 'alt']);
        $merged['stats']['items'] = $this->buildRepeaterItemsWithPreview($merged['stats']['items'] ?? [], self::STATS_COUNT, $defaults['stats']['items'], ['value', 'label']);
        $merged['festive_edits']['items'] = $this->buildRepeaterItemsWithPreview($merged['festive_edits']['items'] ?? [], self::FESTIVE_EDIT_COUNT, $defaults['festive_edits']['items'], ['badge', 'title', 'image', 'href']);

        return $merged;
    }

    private function getDefaultFullHomepageConfig(): array
    {
        return [
            'collections' => [
                'is_active' => true,
                'eyebrow' => 'Collections',
                'title' => 'Shop By Category',
                'button_text' => 'View all',
                'button_url' => '/shop',
                'items' => [
                    ['title' => 'God Idols', 'subtitle' => 'Temple-inspired classics', 'image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_1_86f2e0a3_a3c3_4425_a004/screen.png', 'href' => '/shop?category=god-idols'],
                    ['title' => 'Home Decor', 'subtitle' => 'Statement brass accents', 'image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_1_3758284a_c859_469d_be0a/screen.png', 'href' => '/shop?category=wall-decor'],
                    ['title' => 'Pooja Decor', 'subtitle' => 'Sacred corner essentials', 'image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_1_0b0cbc19_17a3_496c_8345/screen.png', 'href' => '/shop?category=pooja-decor'],
                    ['title' => 'Kitchen & Utility', 'subtitle' => 'Functional heirloom pieces', 'image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_1_600x.jpg_v_1683015923/screen.png', 'href' => '/shop?category=home-kitchen'],
                ],
            ],
            'occasions' => [
                'is_active' => true,
                'eyebrow' => 'Shop By Occasion',
                'title' => 'Festival Categories',
                'items' => [
                    ['title' => 'Ganesh Chaturthi', 'image' => '/reference-assets/image_from_https_cdn.shopify.com_s_files_1_0709_7421_0333_files_ganesh/screen.png', 'href' => '/shop?category=ganesh-chaturthi'],
                    ['title' => 'Janmashtami', 'image' => '/reference-assets/image_from_https_cdn.shopify.com_s_files_1_0709_7421_0333_files_janmasthami.jpg/screen.png', 'href' => '/shop?category=janmashtami'],
                    ['title' => 'Navratri', 'image' => '/reference-assets/image_from_https_cdn.shopify.com_s_files_1_0709_7421_0333_files_navratri.png_v/screen.png', 'href' => '/shop?category=navratri'],
                    ['title' => 'Diwali', 'image' => '/reference-assets/image_from_https_cdn.shopify.com_s_files_1_0709_7421_0333_files_diwali.jpg_v/screen.png', 'href' => '/shop?category=diwali'],
                    ['title' => 'Dhanteras', 'image' => '/reference-assets/image_from_https_cdn.shopify.com_s_files_1_0709_7421_0333_files_dhanteras.png_v/screen.png', 'href' => '/shop?category=dhanteras'],
                ],
            ],
            'editorial_picks' => [
                'is_active' => true,
                'items' => [
                    ['badge' => 'Editorial Pick', 'title' => 'God Idols', 'description' => 'Discover our curated god idols collection — handcrafted with care for your home and sacred spaces.', 'image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_1_86f2e0a3_a3c3_4425_a004/screen.png', 'href' => '/shop?category=god-idols'],
                    ['badge' => 'Editorial Pick', 'title' => 'Wall Decor', 'description' => 'Discover our curated wall decor collection — handcrafted with care for your home and sacred spaces.', 'image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_whatsapp_image_2026_04_15_at/screen.png', 'href' => '/shop?category=wall-decor'],
                    ['badge' => 'Editorial Pick', 'title' => 'Table Decor', 'description' => 'Discover our curated table decor collection — handcrafted with care for your home and sacred spaces.', 'image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_brass_superfine_shiva_idol/screen.png', 'href' => '/shop?category=table-decor'],
                ],
            ],
            'about_brand' => [
                'is_active' => true,
                'eyebrow' => 'About The Brand',
                'title' => 'A Home For Handcrafted Brass And Heritage Decor',
                'paragraph_one' => 'Little Divinity is a home for handcrafted brass idols, home decor, pooja essentials, and meaningful gifting pieces. Every product is made by skilled Indian artisans using traditional techniques passed down through generations.',
                'paragraph_two' => 'Whether you are decorating a sacred corner, gifting a housewarming, or adding warmth to your living space, we curate only the finest pieces in solid brass, wood, and stone. Trusted by over 45,000 happy customers across India.',
                'button_text' => 'Explore Our Collection',
                'button_url' => '/shop',
                'image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_banner_4ab_copy_1_800x.jpg_v/screen.png',
            ],
            'founders' => [
                'is_active' => true,
                'eyebrow' => 'About The Founders',
                'title' => 'Built Around Craft, Story, And Artisan Heritage',
                'content' => 'Every piece begins with a craftsperson\'s hands. We work directly with artisan families across Rajasthan and Uttar Pradesh — preserving ancient metalworking traditions while bringing their finest work to homes across India. Our 30+ years of craft expertise ensures every product meets the highest standards of quality and authenticity.',
                'button_text' => 'Shop Handcrafted Pieces',
                'button_url' => '/shop',
                'main_image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_chatgpt_image_mar_5_2026_04_30/screen.png',
                'side_image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_gemini_generated_image/screen.png',
            ],
            'testimonials' => [
                'is_active' => true,
                'eyebrow' => 'Testimonials',
                'title' => 'Customers Love Our Products',
                'items' => [
                    ['title' => 'Excellent Quality', 'quote' => 'The finish, weight, and carving detail immediately made the piece feel premium and gift-worthy.', 'author' => 'Saikat Gaur', 'stars' => '★★★★★'],
                    ['title' => 'Great Collection', 'quote' => 'A strong mix of god idols, decor, and gifting items that feels like a complete handcrafted store.', 'author' => 'Sunita', 'stars' => '★★★★★'],
                    ['title' => 'Beautiful Design', 'quote' => 'The styling and product presentation made it easy to pick a statement piece for our living room.', 'author' => 'Rita Paria', 'stars' => '★★★★★'],
                ],
            ],
            'newsletter' => [
                'is_active' => true,
                'eyebrow' => 'The Divinity Circle',
                'title' => 'Unlock 10% Off Your First Order',
                'description' => 'Subscribe to get early access to festive edits, curated gifting guides, care instructions, and exclusive subscriber-only collections.',
                'button_text' => 'Claim Discount',
                'placeholder' => 'Enter your email address',
                'footnote' => 'Join 45,000+ happy homes. Free shipping above ₹999 nationwide.',
            ],
            'instagram' => [
                'is_active' => true,
                'eyebrow' => 'Follow Us On',
                'title' => 'Instagram',
                'profile_url' => 'https://www.instagram.com/littledivinity_official/',
                'profile_label' => '@littledivinity_official',
                'tiles' => [
                    ['image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_1_3758284a_c859_469d_be0a/screen.png', 'alt' => 'Brass god idol handcrafted'],
                    ['image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_untitled_design_2025_10_1/screen.png', 'alt' => 'Home decor brass collection'],
                    ['image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_51_e49ec306_c8b1_411b_937b/screen.png', 'alt' => 'Peacock brass wall art'],
                    ['image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_brass_buddha_statue_intricate/screen.png', 'alt' => 'Buddha statue brass'],
                    ['image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_13_86d4189e_e6d5_4292_8326/screen.png', 'alt' => 'Candle stand brass'],
                    ['image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_1_86f2e0a3_a3c3_4425_a004/screen.png', 'alt' => 'God idols collection'],
                ],
            ],
            'stats' => [
                'is_active' => true,
                'eyebrow' => 'Trusted By Thousands',
                'title' => 'Why Customers Choose Little Divinity',
                'items' => [
                    ['value' => '50000+', 'label' => 'Orders Fulfilled'],
                    ['value' => '45000+', 'label' => 'Happy Customers'],
                    ['value' => '30+', 'label' => 'Years Experience'],
                    ['value' => '10000+', 'label' => 'Products Available'],
                ],
            ],
            'festive_edits' => [
                'is_active' => true,
                'eyebrow' => 'Festive Edits',
                'title' => 'Occasions, Gifting, And Seasonal Stories',
                'button_text' => 'View All',
                'button_url' => '/shop',
                'items' => [
                    ['badge' => 'Curated Edit', 'title' => 'Ganesh Chaturthi Edit', 'image' => '/reference-assets/image_from_https_cdn.shopify.com_s_files_1_0709_7421_0333_files_ganesh/screen.png', 'href' => '/shop?category=gifting-edit'],
                    ['badge' => 'Curated Edit', 'title' => 'Diwali Styling Picks', 'image' => '/reference-assets/image_from_https_cdn.shopify.com_s_files_1_0709_7421_0333_files_diwali.jpg_v/screen.png', 'href' => '/shop?category=gifting-edit'],
                    ['badge' => 'Curated Edit', 'title' => 'Wedding Gifting', 'image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_whatsapp_image_2026_02_20_at_5/screen.png', 'href' => '/shop?category=gifting-edit'],
                    ['badge' => 'Curated Edit', 'title' => 'Artisan Craft Story', 'image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_chatgpt_image_mar_5_2026_04_30/screen.png', 'href' => '/shop?category=gifting-edit'],
                ],
            ],
        ];
    }

    private function buildRepeaterItems(mixed $items, int $count, array $defaults, array $fields): array
    {
        $items = is_array($items) ? array_values($items) : [];
        $normalized = [];

        for ($index = 0; $index < $count; $index++) {
            $current = is_array($items[$index] ?? null) ? $items[$index] : [];
            $fallback = $defaults[$index] ?? [];
            $entry = [];

            foreach ($fields as $field) {
                $fallbackValue = $fallback[$field] ?? '';
                if (str_contains($field, 'image')) {
                    $entry[$field] = $this->resolveConfiguredImageValue($current, $field, $fallbackValue);
                    continue;
                }

                $entry[$field] = $this->sanitizeTextValue($current[$field] ?? null) ?: (is_string($fallbackValue) ? $fallbackValue : '');
            }

            $normalized[] = $entry;
        }

        return $normalized;
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
        $config['slides'] = is_array($config['slides'] ?? null) ? $config['slides'] : [];
        $config['promos'] = is_array($config['promos'] ?? null) ? $config['promos'] : [];
        $config['secondary_button_text'] = (string) ($config['secondary_button_text'] ?? '');
        $config['secondary_button_url'] = (string) ($config['secondary_button_url'] ?? '');
        $section->config = $config;

        if (! $section->exists || $section->isDirty()) {
            $section->save();
        }

        return $section;
    }

    private function normalizeHeroSlides(array|string|null $slides): array
    {
        if (! is_array($slides)) {
            $slides = [];
        }

        $normalized = [];

        for ($index = 0; $index < self::HERO_SLIDE_COUNT; $index++) {
            $slide = is_array($slides[$index] ?? null) ? $slides[$index] : [];
            $image = $this->sanitizeMediaPath($slide['image'] ?? null);
            $normalized[] = [
                'title' => $this->sanitizeTextValue($slide['title'] ?? ''),
                'alt' => $this->sanitizeTextValue($slide['alt'] ?? ''),
                'href' => $this->sanitizeTextValue($slide['href'] ?? ''),
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

    private function normalizeHeroPromos(array|string|null $promos): array
    {
        if (! is_array($promos)) {
            $promos = [];
        }

        $normalized = [];

        for ($index = 0; $index < self::HERO_PROMO_COUNT; $index++) {
            $promo = is_array($promos[$index] ?? null) ? $promos[$index] : [];
            $image = $this->sanitizeMediaPath($promo['image'] ?? null);
            $normalized[] = [
                'title' => $this->sanitizeTextValue($promo['title'] ?? ''),
                'subtitle' => $this->sanitizeTextValue($promo['subtitle'] ?? ''),
                'href' => $this->sanitizeTextValue($promo['href'] ?? ''),
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

    private function resolveAdminMediaPreviewUrl(mixed $path): ?string
    {
        $path = $this->sanitizeMediaPath($path);

        if (! filled($path)) {
            return null;
        }

        $requestBaseUrl = request()?->getSchemeAndHttpHost();
        $appUrl = $requestBaseUrl ?: rtrim((string) config('app.url'), '/');

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/reference-assets/') || str_starts_with($path, 'reference-assets/')) {
            $frontendUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');

            return $frontendUrl.'/'.ltrim($path, '/');
        }

        if (str_starts_with($path, '/storage/') || str_starts_with($path, 'storage/')) {
            return $appUrl.'/'.ltrim($path, '/');
        }

        if (str_starts_with($path, '/')) {
            return $appUrl.$path;
        }

        return $appUrl.'/'.ltrim($path, '/');
    }

    private function sanitizeMediaPath(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $path = trim((string) $value);

        return $path !== '' ? $path : null;
    }

    private function resolveConfiguredImageValue(array $current, string $field, mixed $fallbackValue): string
    {
        if (array_key_exists($field, $current)) {
            if (! is_scalar($current[$field]) || $current[$field] === null) {
                return '';
            }

            return trim((string) $current[$field]);
        }

        return is_string($fallbackValue) ? $fallbackValue : '';
    }

    private function sanitizeTextValue(mixed $value): string
    {
        if (! is_scalar($value)) {
            return '';
        }

        return trim((string) $value);
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

    private function buildRepeaterItemsWithPreview(mixed $items, int $count, array $defaults, array $fields): array
    {
        $items = is_array($items) ? array_values($items) : [];
        $normalized = [];

        for ($index = 0; $index < $count; $index++) {
            $current = is_array($items[$index] ?? null) ? $items[$index] : [];
            $fallback = $defaults[$index] ?? [];
            $entry = [];

            foreach ($fields as $field) {
                $fallbackValue = $fallback[$field] ?? '';
                if (str_contains($field, 'image')) {
                    $entry[$field] = $this->resolveConfiguredImageValue($current, $field, $fallbackValue);
                    continue;
                }

                $entry[$field] = $this->sanitizeTextValue($current[$field] ?? null) ?: (is_string($fallbackValue) ? $fallbackValue : '');
            }

            if (array_key_exists('image', $entry)) {
                $entry['preview_image'] = $this->resolveAdminMediaPreviewUrl($entry['image']);
            }

            $normalized[] = $entry;
        }

        return $normalized;
    }

    public function getMediaList(Request $request): \Illuminate\Http\JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $query = \App\Models\MediaLibrary::query()->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('original_name', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%");
            });
        }

        $media = $query->paginate(24);
        return response()->json($media);
    }
}
