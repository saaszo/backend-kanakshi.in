<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Services\Catalog\ProductCatalogService;
use Illuminate\Http\JsonResponse;

class ProductShowController
{
    public function __invoke(string $slug, ProductCatalogService $catalog): JsonResponse
    {
        $product = $catalog->findBySlug($slug);

        if ($product === null) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product fetched successfully.',
            'data' => $product,
        ]);
    }
}
