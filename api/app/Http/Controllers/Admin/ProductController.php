<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesAdminUploads;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    use HandlesAdminUploads;

    public function index(): View
    {
        $search = trim((string) request()->string('q'));
        $categoryFilter = request()->integer('category_id');
        $statusFilter = request()->string('status')->toString();

        $query = Product::query()->with('category');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $term = '%' . $search . '%';
                $builder
                    ->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term)
                    ->orWhere('slug', 'like', $term);
            });
        }

        if ($categoryFilter > 0) {
            $query->where('category_id', $categoryFilter);
        }

        if ($statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($statusFilter === 'inactive') {
            $query->where('is_active', false);
        } elseif ($statusFilter === 'featured') {
            $query->where('is_featured', true);
        }

        return view('admin.products.index', [
            'products' => $query->latest()->get(),
            'categories' => Category::query()->orderBy('name')->get(),
            'filters' => [
                'q' => $search,
                'category_id' => $categoryFilter,
                'status' => $statusFilter,
            ],
            'stats' => [
                'total_products' => Product::query()->count(),
                'active_products' => Product::query()->where('is_active', true)->count(),
                'featured_products' => Product::query()->where('is_featured', true)->count(),
                'total_stock' => (int) Product::query()->sum('stock'),
            ],
        ]);
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:200'],
            'short_desc' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'shipping_type' => ['required', 'string', 'in:default,custom,free'],
            'shipping_fee' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'sku' => ['nullable', 'string', 'max:100'],
            'images_input' => ['nullable', 'string'],
            'image_urls' => ['nullable', 'array', 'max:8'],
            'image_urls.*' => ['nullable', 'string', 'max:255'],
            'image_uploads' => ['nullable', 'array', 'max:8'],
            'image_uploads.*' => ['nullable', 'image', 'max:5120'],
            'video_url' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_desc' => ['nullable', 'string', 'max:320'],
        ]);

        Product::query()->create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'short_desc' => $validated['short_desc'] ?? null,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'sale_price' => $validated['sale_price'] ?? null,
            'shipping_type' => $validated['shipping_type'],
            'shipping_fee' => $validated['shipping_type'] === 'custom' ? ($validated['shipping_fee'] ?? 0) : 0,
            'stock' => $validated['stock'] ?? 0,
            'sku' => $validated['sku'] ?? null,
            'images' => $this->resolveProductImages($request, $validated),
            'video_url' => $validated['video_url'] ?? null,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_desc' => $validated['meta_desc'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active', true),
            'gst_percent' => 18,
        ]);

        return back()->with('status', 'Product created successfully.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:200'],
            'short_desc' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'shipping_type' => ['required', 'string', 'in:default,custom,free'],
            'shipping_fee' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'sku' => ['nullable', 'string', 'max:100'],
            'images_input' => ['nullable', 'string'],
            'image_urls' => ['nullable', 'array', 'max:8'],
            'image_urls.*' => ['nullable', 'string', 'max:255'],
            'image_uploads' => ['nullable', 'array', 'max:8'],
            'image_uploads.*' => ['nullable', 'image', 'max:5120'],
            'video_url' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_desc' => ['nullable', 'string', 'max:320'],
        ]);

        $product->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'short_desc' => $validated['short_desc'] ?? null,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'sale_price' => $validated['sale_price'] ?? null,
            'shipping_type' => $validated['shipping_type'],
            'shipping_fee' => $validated['shipping_type'] === 'custom' ? ($validated['shipping_fee'] ?? 0) : 0,
            'stock' => $validated['stock'] ?? 0,
            'sku' => $validated['sku'] ?? null,
            'images' => $this->resolveProductImages($request, $validated),
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

    private function resolveProductImages(Request $request, array $validated): array
    {
        $slots = array_fill(0, 8, '');
        $textImages = $this->parseImages($validated['images_input'] ?? null);
        foreach ($textImages as $index => $url) {
            if ($index > 7) {
                break;
            }

            $slots[$index] = $url;
        }

        foreach (($validated['image_urls'] ?? []) as $index => $url) {
            if ($index > 7) {
                break;
            }

            $slots[$index] = trim((string) $url);
        }

        foreach ($request->file('image_uploads', []) as $index => $file) {
            if (! $file || $index > 7) {
                continue;
            }

            $slots[$index] = $this->storeAdminUpload($file, 'products', 'Product image');
        }

        return array_values(array_filter(array_map(
            static fn (string $item): string => trim($item),
            $slots
        )));
    }
}
