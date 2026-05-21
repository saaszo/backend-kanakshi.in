<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomepageProductController extends Controller
{
    public function index(): View
    {
        return view('admin.homepage-products.index', [
            'bestSellers' => $this->getOrCreateSection(
                'best-sellers',
                'Best Sellers',
                'Most Loved Across The Storefront',
                'Best Sellers',
                '/shop',
                'Shop all'
            ),
            'newArrivalsProducts' => $this->getOrCreateSection(
                'new-arrivals-products',
                'New Arrival Product Rail',
                'Latest From The Craft Table',
                'New Arrivals',
                '/shop',
                'Explore all'
            ),
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'slug']),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function update(Request $request, string $sectionKey): RedirectResponse
    {
        abort_unless(in_array($sectionKey, ['best-sellers', 'new-arrivals-products'], true), 404);

        $section = HomepageSection::query()->where('section_key', $sectionKey)->firstOrFail();

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'button_text' => ['nullable', 'string', 'max:120'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'source_type' => ['required', 'in:featured,newest,manual,category'],
            'product_count' => ['required', 'integer', 'min:1', 'max:24'],
            'category_slug' => ['nullable', 'string', 'max:180'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['nullable', 'integer', 'exists:products,id'],
        ]);

        $section->update([
            'title' => $validated['title'] ?? $section->title,
            'subtitle' => $validated['subtitle'] ?? $section->subtitle,
            'button_text' => $validated['button_text'] ?? $section->button_text,
            'button_url' => $validated['button_url'] ?? $section->button_url,
            'is_active' => $request->boolean('is_active'),
            'config' => array_merge($section->config ?? [], [
                'source_type' => $validated['source_type'],
                'product_count' => (int) $validated['product_count'],
                'category_slug' => $validated['category_slug'] ?? null,
                'product_ids' => collect($validated['product_ids'] ?? [])->filter()->values()->all(),
            ]),
        ]);

        return back()->with('status', 'Homepage product section updated successfully.');
    }

    private function getOrCreateSection(
        string $key,
        string $label,
        string $title,
        string $subtitle,
        string $buttonUrl,
        string $buttonText
    ): HomepageSection {
        return HomepageSection::query()->firstOrCreate(
            ['section_key' => $key],
            [
                'section_type' => 'product_rail',
                'label' => $label,
                'title' => $title,
                'subtitle' => $subtitle,
                'button_url' => $buttonUrl,
                'button_text' => $buttonText,
                'sort_order' => $key === 'best-sellers' ? 2 : 4,
                'is_active' => true,
                'config' => [
                    'source_type' => $key === 'best-sellers' ? 'featured' : 'newest',
                    'product_count' => $key === 'best-sellers' ? 8 : 4,
                    'product_ids' => [],
                    'category_slug' => null,
                ],
            ]
        );
    }
}
