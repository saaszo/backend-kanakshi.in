<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGatewaySetting;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentSettingsController extends Controller
{
    public function index(): View
    {
        $settings = Setting::query()->pluck('value', 'key_name');

        $gateways = PaymentGatewaySetting::query()->orderBy('sort_order')->get();
        $razorpay = $gateways->firstWhere('provider', 'razorpay') ?? new PaymentGatewaySetting(['provider' => 'razorpay']);
        $phonepe = $gateways->firstWhere('provider', 'phonepe') ?? new PaymentGatewaySetting(['provider' => 'phonepe']);
        $cod = $gateways->firstWhere('provider', 'cod') ?? new PaymentGatewaySetting(['provider' => 'cod']);

        return view('admin.settings.payment', [
            'settings' => $settings,
            'razorpay' => $razorpay,
            'phonepe' => $phonepe,
            'cod' => $cod,
        ]);
    }

    public function updatePrepaidOffer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prepaid_discount_enabled' => ['nullable', 'boolean'],
            'prepaid_discount_title' => ['required', 'string', 'max:150'],
            'prepaid_discount_type' => ['required', 'string', 'in:percent,fixed'],
            'prepaid_discount_value' => ['required', 'numeric', 'min:0'],
            'prepaid_discount_min_order' => ['nullable', 'numeric', 'min:0'],
            'prepaid_discount_max_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $settingsToSave = [
            'prepaid_discount_enabled' => $request->boolean('prepaid_discount_enabled') ? '1' : '0',
            'prepaid_discount_title' => $validated['prepaid_discount_title'],
            'prepaid_discount_type' => $validated['prepaid_discount_type'],
            'prepaid_discount_value' => (string) $validated['prepaid_discount_value'],
            'prepaid_discount_min_order' => (string) ($validated['prepaid_discount_min_order'] ?? 0),
            'prepaid_discount_max_amount' => (string) ($validated['prepaid_discount_max_amount'] ?? 0),
        ];

        foreach ($settingsToSave as $key => $val) {
            Setting::query()->updateOrCreate(['key_name' => $key], ['value' => $val]);
        }

        return back()->with('status', 'Prepaid order discount offer updated successfully.');
    }

    public function updateCod(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cod_enabled' => ['nullable', 'boolean'],
            'cod_fee' => ['nullable', 'numeric', 'min:0'],
            'cod_max_order_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $codEnabled = $request->boolean('cod_enabled') ? '1' : '0';
        $codFee = (string) ($validated['cod_fee'] ?? 0);
        $maxLimit = (string) ($validated['cod_max_order_amount'] ?? 50000);

        Setting::query()->updateOrCreate(['key_name' => 'cod_enabled'], ['value' => $codEnabled]);
        Setting::query()->updateOrCreate(['key_name' => 'cod_fee'], ['value' => $codFee]);
        Setting::query()->updateOrCreate(['key_name' => 'cod_max_order_amount'], ['value' => $maxLimit]);

        PaymentGatewaySetting::query()->updateOrCreate(
            ['provider' => 'cod'],
            [
                'display_name' => 'Cash on Delivery (COD)',
                'is_active' => $request->boolean('cod_enabled'),
                'is_test_mode' => false,
                'extra_config' => [
                    'cod_fee' => (float) $codFee,
                    'max_order_amount' => (float) $maxLimit,
                ],
                'sort_order' => 2,
            ]
        );

        return back()->with('status', 'Cash on Delivery (COD) settings saved.');
    }

    public function updateGateway(Request $request, string $provider): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:100'],
            'merchant_id' => ['nullable', 'string', 'max:150'],
            'public_key' => ['nullable', 'string', 'max:255'],
            'secret_key' => ['nullable', 'string'],
            'webhook_secret' => ['nullable', 'string'],
        ]);

        $isActive = $request->boolean('is_active');
        $isTestMode = $request->boolean('is_test_mode');

        PaymentGatewaySetting::query()->updateOrCreate(
            ['provider' => $provider],
            [
                'display_name' => $validated['display_name'],
                'is_active' => $isActive,
                'is_test_mode' => $isTestMode,
                'merchant_id' => $validated['merchant_id'] ?? null,
                'public_key' => $validated['public_key'] ?? null,
                'secret_key' => $validated['secret_key'] ?? null,
                'webhook_secret' => $validated['webhook_secret'] ?? null,
            ]
        );

        return back()->with('status', ucfirst($provider) . ' gateway configuration updated.');
    }
}
