<?php

namespace App\Http\Controllers\Api\Marketing;

use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class PublicCouponsController
{
    public function __invoke(): JsonResponse
    {
        if (! Schema::hasTable('coupons')) {
            return response()->json([
                'success' => true,
                'message' => 'Coupons table is not available yet.',
                'data' => [],
            ]);
        }

        $now = Carbon::now();

        $coupons = Coupon::query()
            ->where('show_on_cart', true)
            ->where('is_active', true)
            ->where(function ($query) use ($now): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->orderBy('sort_order')
            ->latest()
            ->get([
                'id',
                'title',
                'code',
                'type',
                'value',
                'min_order_amount',
                'description',
                'badge_text',
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Public offers fetched successfully.',
            'data' => $coupons,
        ]);
    }
}
