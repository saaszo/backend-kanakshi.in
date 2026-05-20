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
            ->toArray();

        return response()->json([
            'success' => true,
            'message' => 'Homepage sections fetched successfully.',
            'data' => $sections,
        ]);
    }
}
