<?php

namespace App\Services;

use App\Models\CustomerWalletTransaction;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerWalletService
{
    /**
     * Fetch wallet configuration settings.
     */
    public function getWalletConfig(): array
    {
        $settings = Setting::query()
            ->where('key_name', 'like', 'wallet_%')
            ->pluck('value', 'key_name')
            ->toArray();

        $isEnabled = isset($settings['wallet_enabled'])
            ? in_array((string)$settings['wallet_enabled'], ['1', 'true', 'yes', 'on'], true)
            : true;

        $signupBonusEnabled = isset($settings['wallet_signup_bonus_enabled'])
            ? in_array((string)$settings['wallet_signup_bonus_enabled'], ['1', 'true', 'yes', 'on'], true)
            : true;

        $signupBonusAmount = (float)($settings['wallet_signup_bonus_amount'] ?? 500.00);

        $orderCashbackEnabled = isset($settings['wallet_order_cashback_enabled'])
            ? in_array((string)$settings['wallet_order_cashback_enabled'], ['1', 'true', 'yes', 'on'], true)
            : true;

        $orderCashbackType = in_array(($settings['wallet_order_cashback_type'] ?? 'percent'), ['percent', 'fix'], true)
            ? ($settings['wallet_order_cashback_type'] ?? 'percent')
            : 'percent';

        $orderCashbackValue = (float)($settings['wallet_order_cashback_value'] ?? 5.00);
        $orderCashbackMinOrder = (float)($settings['wallet_order_cashback_min_order'] ?? 1000.00);
        $orderCashbackMaxAmount = (float)($settings['wallet_order_cashback_max_amount'] ?? 1000.00);
        $orderCashbackReleaseDays = (int)($settings['wallet_order_cashback_release_days'] ?? 7);
        $maxRedemptionPercent = (float)($settings['wallet_max_redemption_percent'] ?? 100.00);

        return [
            'is_enabled' => $isEnabled,
            'signup_bonus_enabled' => $signupBonusEnabled,
            'signup_bonus_amount' => $signupBonusAmount,
            'order_cashback_enabled' => $orderCashbackEnabled,
            'order_cashback_type' => $orderCashbackType,
            'order_cashback_value' => $orderCashbackValue,
            'order_cashback_min_order' => $orderCashbackMinOrder,
            'order_cashback_max_amount' => $orderCashbackMaxAmount,
            'order_cashback_release_days' => $orderCashbackReleaseDays,
            'max_redemption_percent' => $maxRedemptionPercent,
        ];
    }

    /**
     * Award welcome sign-up bonus to newly registered customer.
     */
    public function awardSignupBonus(User $user): ?CustomerWalletTransaction
    {
        $config = $this->getWalletConfig();

        if (!$config['is_enabled'] || !$config['signup_bonus_enabled'] || $config['signup_bonus_amount'] <= 0) {
            return null;
        }

        // Ensure user hasn't already received signup bonus
        $alreadyAwarded = CustomerWalletTransaction::query()
            ->where('user_id', $user->id)
            ->where('source', 'signup_bonus')
            ->exists();

        if ($alreadyAwarded) {
            return null;
        }

        return $user->creditWallet(
            $config['signup_bonus_amount'],
            'signup_bonus',
            null,
            "Welcome to Kanakshi Privé! Bonus wallet credit ₹" . number_format($config['signup_bonus_amount'], 2) . " added."
        );
    }

    /**
     * Calculate cashback amount for an order based on current admin settings.
     */
    public function calculateOrderCashback(float $orderSubtotal): float
    {
        $config = $this->getWalletConfig();

        if (!$config['is_enabled'] || !$config['order_cashback_enabled'] || $orderSubtotal < $config['order_cashback_min_order']) {
            return 0.00;
        }

        if ($config['order_cashback_type'] === 'percent') {
            $cashback = $orderSubtotal * ($config['order_cashback_value'] / 100);
            if ($config['order_cashback_max_amount'] > 0 && $cashback > $config['order_cashback_max_amount']) {
                $cashback = $config['order_cashback_max_amount'];
            }
        } else {
            $cashback = $config['order_cashback_value'];
        }

        return round(max(0, $cashback), 2);
    }

    /**
     * Record a pending post-purchase reward when an order is created or delivered.
     * The reward becomes available after the 7-day return window closes.
     */
    public function scheduleOrderCashback(Order $order): ?CustomerWalletTransaction
    {
        $config = $this->getWalletConfig();

        if (!$config['is_enabled'] || !$config['order_cashback_enabled'] || !$order->user_id) {
            return null;
        }

        $user = User::query()->find($order->user_id);
        if (!$user) {
            return null;
        }

        $alreadyScheduled = CustomerWalletTransaction::query()
            ->where('order_id', $order->id)
            ->where('source', 'purchase_reward')
            ->exists();

        if ($alreadyScheduled) {
            return null;
        }

        $cashbackAmount = $this->calculateOrderCashback((float)$order->total_amount);
        if ($cashbackAmount <= 0) {
            return null;
        }

        $availableAt = Carbon::now()->addDays($config['order_cashback_release_days']);

        return CustomerWalletTransaction::query()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'type' => 'credit',
            'source' => 'purchase_reward',
            'amount' => $cashbackAmount,
            'balance_after' => (float)$user->wallet_balance, // not yet incremented until cleared
            'description' => "Cashback on order #{$order->order_number} (Unlocks in {$config['order_cashback_release_days']} days post 7-day return window)",
            'status' => 'pending_clearance',
            'available_at' => $availableAt,
        ]);
    }

    /**
     * Process & credit any pending rewards whose 7-day return cooldown has completed.
     */
    public function releaseEligibleOrderCashbacks(): int
    {
        $pending = CustomerWalletTransaction::query()
            ->where('status', 'pending_clearance')
            ->where('available_at', '<=', now())
            ->with(['order', 'user'])
            ->get();

        $clearedCount = 0;

        foreach ($pending as $transaction) {
            $order = $transaction->order;
            // If order was cancelled or returned, cancel the reward
            if ($order && in_array($order->status, ['cancelled', 'returned', 'refunded'], true)) {
                $transaction->update(['status' => 'cancelled']);
                continue;
            }

            // Check if order has any active returns
            $hasApprovedReturn = $order && $order->returns()
                ->whereIn('status', ['approved', 'received', 'refunded'])
                ->exists();

            if ($hasApprovedReturn) {
                $transaction->update(['status' => 'cancelled']);
                continue;
            }

            $user = $transaction->user;
            if ($user) {
                DB::transaction(function () use ($user, $transaction, &$clearedCount) {
                    $user->increment('wallet_balance', $transaction->amount);
                    $transaction->update([
                        'status' => 'completed',
                        'balance_after' => $user->fresh()->wallet_balance,
                        'description' => "Cashback on order unlocked successfully.",
                    ]);
                    $clearedCount++;
                });
            }
        }

        return $clearedCount;
    }
}
