<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerWalletTransaction;
use App\Models\Setting;
use App\Models\User;
use App\Services\CustomerWalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletSettingsController extends Controller
{
    public function index(Request $request, CustomerWalletService $walletService): View
    {
        $config = $walletService->getWalletConfig();

        $search = trim((string)$request->get('q', ''));
        $customersQuery = User::query()
            ->where('role', 'customer')
            ->orderByDesc('wallet_balance');

        if ($search !== '') {
            $customersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $customersQuery->paginate(20);

        $recentTransactions = CustomerWalletTransaction::query()
            ->with(['user', 'order'])
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        $totalCirculation = (float)User::query()->where('role', 'customer')->sum('wallet_balance');
        $totalEarnedAllTime = (float)CustomerWalletTransaction::query()->where('type', 'credit')->where('status', 'completed')->sum('amount');
        $totalRedeemedAllTime = (float)CustomerWalletTransaction::query()->where('type', 'debit')->sum('amount');

        return view('admin.wallet.index', [
            'config' => $config,
            'customers' => $customers,
            'recentTransactions' => $recentTransactions,
            'totalCirculation' => $totalCirculation,
            'totalEarnedAllTime' => $totalEarnedAllTime,
            'totalRedeemedAllTime' => $totalRedeemedAllTime,
            'search' => $search,
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'wallet_enabled' => ['nullable', 'boolean'],
            'wallet_signup_bonus_enabled' => ['nullable', 'boolean'],
            'wallet_signup_bonus_amount' => ['required', 'numeric', 'min:0'],
            'wallet_order_cashback_enabled' => ['nullable', 'boolean'],
            'wallet_order_cashback_type' => ['required', 'string', 'in:percent,fix'],
            'wallet_order_cashback_value' => ['required', 'numeric', 'min:0'],
            'wallet_order_cashback_min_order' => ['nullable', 'numeric', 'min:0'],
            'wallet_order_cashback_max_amount' => ['nullable', 'numeric', 'min:0'],
            'wallet_order_cashback_release_days' => ['required', 'integer', 'min:1', 'max:90'],
            'wallet_max_redemption_percent' => ['required', 'numeric', 'min:1', 'max:100'],
        ]);

        $settings = [
            'wallet_enabled' => $request->boolean('wallet_enabled') ? '1' : '0',
            'wallet_signup_bonus_enabled' => $request->boolean('wallet_signup_bonus_enabled') ? '1' : '0',
            'wallet_signup_bonus_amount' => (string) $validated['wallet_signup_bonus_amount'],
            'wallet_order_cashback_enabled' => $request->boolean('wallet_order_cashback_enabled') ? '1' : '0',
            'wallet_order_cashback_type' => $validated['wallet_order_cashback_type'],
            'wallet_order_cashback_value' => (string) $validated['wallet_order_cashback_value'],
            'wallet_order_cashback_min_order' => (string) ($validated['wallet_order_cashback_min_order'] ?? 0),
            'wallet_order_cashback_max_amount' => (string) ($validated['wallet_order_cashback_max_amount'] ?? 0),
            'wallet_order_cashback_release_days' => (string) $validated['wallet_order_cashback_release_days'],
            'wallet_max_redemption_percent' => (string) $validated['wallet_max_redemption_percent'],
        ];

        foreach ($settings as $key => $val) {
            Setting::query()->updateOrCreate(['key_name' => $key], ['value' => $val]);
        }

        return back()->with('status', 'Customer wallet and rewards settings saved successfully.');
    }

    public function adjustCustomerBalance(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'action' => ['required', 'string', 'in:credit,debit'],
            'amount' => ['required', 'numeric', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $user = User::query()->findOrFail($validated['user_id']);
        $amount = (float)$validated['amount'];

        if ($validated['action'] === 'credit') {
            $user->creditWallet($amount, 'admin_adjustment', null, "Admin credit: " . $validated['reason']);
            return back()->with('status', "Credited ₹{$amount} to {$user->name}'s wallet.");
        } else {
            if ((float)$user->wallet_balance < $amount) {
                return back()->withErrors(['amount' => "Customer only has ₹{$user->wallet_balance} in their wallet."]);
            }
            $user->debitWallet($amount, 'admin_adjustment', null, "Admin debit: " . $validated['reason']);
            return back()->with('status', "Debited ₹{$amount} from {$user->name}'s wallet.");
        }
    }
}
