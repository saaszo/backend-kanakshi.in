<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

class HealthController
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Laravel backend workspace is ready.',
            'data' => [
                'app' => config('app.name'),
                'environment' => app()->environment(),
                'phase' => 'phase-1-foundation',
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }
}
