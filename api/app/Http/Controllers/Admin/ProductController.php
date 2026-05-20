<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index', [
            'products' => Product::query()->with('category')->latest()->get(),
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:220'],
            'short_desc' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'sku' => ['nullable', 'string', 'max:100'],
            'images_input' => ['nullable', 'string'],
            'video_url' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_desc' => ['nullable', 'string', 'max:320'],
        ]);

        Product::query()->create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? null,
            'short_desc' => $validated['short_desc'] ?? null,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'sale_price' => $validated['sale_price'] ?? null,
            'stock' => $validated['stock'] ?? 0,
            'sku' => $validated['sku'] ?? null,
            'images' => $this->parseImages($validated['images_input'] ?? null),
            'video_url' => $validated['video_url'] ?? null,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_desc' => $validated['meta_desc'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active', true),
            'shipping_type' => 'default',
            'shipping_fee' => 0,
            'gst_percent' => 18,
        ]);

        return back()->with('status', 'Product created successfully.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:220'],
            'short_desc' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'sku' => ['nullable', 'string', 'max:100'],
            'images_input' => ['nullable', 'string'],
            'video_url' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_desc' => ['nullable', 'string', 'max:320'],
        ]);

        $product->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? null,
            'short_desc' => $validated['short_desc'] ?? null,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'sale_price' => $validated['sale_price'] ?? null,
            'stock' => $validated['stock'] ?? 0,
            'sku' => $validated['sku'] ?? null,
            'images' => $this->parseImages($validated['images_input'] ?? null),
            'video_url' => $validated['video_url'] ?? null,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_desc' => $validated['meta_desc'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return back()->with('status', 'Product removed successfully.');
    }

    private function parseImages(?string $input): array
    {
        if (! $input) {
            return [];
        }

        $items = preg_split('/[\r\n,]+/', $input) ?: [];

        return array_values(array_filter(array_map(
            static fn (string $item): string => trim($item),
            $items
        )));
    }
}
