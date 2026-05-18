<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Services\Catalog\ProductCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryIndexController
{
    public function __invoke(Request $request, ProductCatalogService $catalog): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Categories fetched successfully.',
            'data' => $catalog->categories($request),
        ]);
    }
}
