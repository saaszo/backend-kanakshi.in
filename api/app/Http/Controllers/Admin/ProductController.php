<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesAdminUploads;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\AmazonProductLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    use HandlesAdminUploads;

    public function __construct(private readonly AmazonProductLinkService $amazonProductLinkService) {}

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
            'weight' => ['nullable', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'dimension_unit' => ['nullable', 'string', 'max:20'],
            'weight_unit' => ['nullable', 'string', 'max:20'],
            'size_label' => ['nullable', 'string', 'max:120'],
            'material' => ['nullable', 'string', 'max:150'],
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
            'amazon_link' => ['nullable', 'url', 'max:2048'],
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_desc' => ['nullable', 'string', 'max:320'],
        ]);

        $resolvedImages = $this->resolveProductImages($request, $validated);
        $amazonPayload = $this->resolveAmazonPayload($request, $validated);

        Product::query()->create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'short_desc' => $validated['short_desc'] ?? null,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'sale_price' => $validated['sale_price'] ?? null,
            'weight' => $validated['weight'] ?? null,
            'length' => $validated['length'] ?? null,
            'width' => $validated['width'] ?? null,
            'height' => $validated['height'] ?? null,
            'dimension_unit' => $validated['dimension_unit'] ?? null,
            'weight_unit' => $validated['weight_unit'] ?? null,
            'size_label' => $validated['size_label'] ?? null,
            'material' => $validated['material'] ?? null,
            'shipping_type' => $validated['shipping_type'],
            'shipping_fee' => $validated['shipping_type'] === 'custom' ? ($validated['shipping_fee'] ?? 0) : 0,
            'stock' => $validated['stock'] ?? 0,
            'sku' => $validated['sku'] ?? null,
            'images' => $resolvedImages,
            'video_url' => $validated['video_url'] ?? null,
            'amazon_link' => $amazonPayload['amazon_link'],
            'amazon_button_enabled' => $amazonPayload['amazon_button_enabled'],
            'amazon_price' => $amazonPayload['amazon_price'],
            'amazon_price_fetched_at' => $amazonPayload['amazon_price_fetched_at'],
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_desc' => $validated['meta_desc'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active', true),
            'gst_percent' => 18,
            'is_sellable' => Product::determineSellable(new Product([
                'price' => $validated['price'],
                'sale_price' => $validated['sale_price'] ?? null,
                'images' => $resolvedImages,
            ])),
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
            'weight' => ['nullable', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'dimension_unit' => ['nullable', 'string', 'max:20'],
            'weight_unit' => ['nullable', 'string', 'max:20'],
            'size_label' => ['nullable', 'string', 'max:120'],
            'material' => ['nullable', 'string', 'max:150'],
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
            'amazon_link' => ['nullable', 'url', 'max:2048'],
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_desc' => ['nullable', 'string', 'max:320'],
        ]);

        $resolvedImages = $this->resolveProductImages($request, $validated);
        $amazonPayload = $this->resolveAmazonPayload($request, $validated, $product);

        $product->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'short_desc' => $validated['short_desc'] ?? null,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'sale_price' => $validated['sale_price'] ?? null,
            'weight' => $validated['weight'] ?? null,
            'length' => $validated['length'] ?? null,
            'width' => $validated['width'] ?? null,
            'height' => $validated['height'] ?? null,
            'dimension_unit' => $validated['dimension_unit'] ?? null,
            'weight_unit' => $validated['weight_unit'] ?? null,
            'size_label' => $validated['size_label'] ?? null,
            'material' => $validated['material'] ?? null,
            'shipping_type' => $validated['shipping_type'],
            'shipping_fee' => $validated['shipping_type'] === 'custom' ? ($validated['shipping_fee'] ?? 0) : 0,
            'stock' => $validated['stock'] ?? 0,
            'sku' => $validated['sku'] ?? null,
            'images' => $resolvedImages,
            'video_url' => $validated['video_url'] ?? null,
            'amazon_link' => $amazonPayload['amazon_link'],
            'amazon_button_enabled' => $amazonPayload['amazon_button_enabled'],
            'amazon_price' => $amazonPayload['amazon_price'],
            'amazon_price_fetched_at' => $amazonPayload['amazon_price_fetched_at'],
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_desc' => $validated['meta_desc'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active'),
            'is_sellable' => Product::determineSellable(new Product([
                'price' => $validated['price'],
                'sale_price' => $validated['sale_price'] ?? null,
                'images' => $resolvedImages,
            ])),
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

    /**
     * @return array{amazon_link:?string,amazon_button_enabled:bool,amazon_price:?float,amazon_price_fetched_at:?string}
     */
    private function resolveAmazonPayload(Request $request, array $validated, ?Product $existingProduct = null): array
    {
        $hasAmazonLinkInput = $request->exists('amazon_link');
        $amazonLink = $hasAmazonLinkInput
            ? trim((string) ($validated['amazon_link'] ?? ''))
            : (string) ($existingProduct?->amazon_link ?? '');
        $amazonLink = $amazonLink !== '' ? $this->amazonProductLinkService->normalizeUrl($amazonLink) : null;
        $amazonButtonEnabled = $amazonLink !== null
            && ($request->exists('amazon_button_enabled')
                ? $request->boolean('amazon_button_enabled')
                : (bool) ($existingProduct?->amazon_button_enabled));
        $amazonPrice = $existingProduct?->amazon_price ? (float) $existingProduct->amazon_price : null;
        $amazonPriceFetchedAt = $existingProduct?->amazon_price_fetched_at?->toDateTimeString();

        if (! $amazonLink) {
            return [
                'amazon_link' => null,
                'amazon_button_enabled' => false,
                'amazon_price' => null,
                'amazon_price_fetched_at' => null,
            ];
        }

        try {
            $snapshot = $this->amazonProductLinkService->fetchSnapshot($amazonLink);
            $amazonLink = $snapshot['canonical_url'];
            $amazonPrice = $snapshot['price'];
            $amazonPriceFetchedAt = $snapshot['fetched_at']->toDateTimeString();
        } catch (\Throwable) {
            // Keep the link and existing price snapshot if Amazon blocks or times out.
        }

        if (! $amazonButtonEnabled) {
            return [
                'amazon_link' => $amazonLink,
                'amazon_button_enabled' => false,
                'amazon_price' => $amazonPrice,
                'amazon_price_fetched_at' => $amazonPriceFetchedAt,
            ];
        }

        return [
            'amazon_link' => $amazonLink,
            'amazon_button_enabled' => true,
            'amazon_price' => $amazonPrice,
            'amazon_price_fetched_at' => $amazonPriceFetchedAt,
        ];
    }
}
