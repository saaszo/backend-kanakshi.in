<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        return view('admin.coupons.index', [
            'coupons' => Coupon::query()->orderByDesc('is_active')->orderBy('sort_order')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCoupon($request);

        Coupon::query()->create($validated + [
            'is_active' => $request->boolean('is_active'),
            'show_on_cart' => $request->boolean('show_on_cart'),
        ]);

        return back()->with('status', 'Offer created successfully.');
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $validated = $this->validateCoupon($request, $coupon->id);

        $coupon->update($validated + [
            'is_active' => $request->boolean('is_active'),
            'show_on_cart' => $request->boolean('show_on_cart'),
        ]);

        return back()->with('status', 'Offer updated successfully.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return back()->with('status', 'Offer removed successfully.');
    }

    private function validateCoupon(Request $request, ?int $couponId = null): array
    {
        $uniqueRule = 'unique:coupons,code';
        if ($couponId) {
            $uniqueRule .= ',' . $couponId;
        }

        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:60', $uniqueRule],
            'type' => ['required', 'string', 'max:40'],
            'value' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'badge_text' => ['nullable', 'string', 'max:100'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'used_count' => ['nullable', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'sort_order' => ['nullable', 'integer'],
        ]);
    }
}
