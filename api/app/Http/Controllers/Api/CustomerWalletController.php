<?php

namespace App\Http\Controllers\Api;

use App\Models\CustomerAccessToken;
use App\Models\CustomerWalletTransaction;
use App\Models\User;
use App\Services\CustomerWalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerWalletController
{
    public function show(Request $request, CustomerWalletService $walletService): JsonResponse
    {
        $user = $this->resolveCustomer($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized customer session.',
            ], 401);
        }

        // Release any matured order cashbacks if 7-day cooldown elapsed
        $walletService->releaseEligibleOrderCashbacks();

        $user->refresh();

        $transactions = CustomerWalletTransaction::query()
            ->where('user_id', $user->id)
            ->with(['order:id,order_number,total_amount'])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function (CustomerWalletTransaction $tx) {
                return [
                    'id' => $tx->id,
                    'order_id' => $tx->order_id,
                    'order_number' => $tx->order?->order_number,
                    'type' => $tx->type,
                    'source' => $tx->source,
                    'amount' => (float)$tx->amount,
                    'balance_after' => (float)$tx->balance_after,
                    'description' => $tx->description,
                    'status' => $tx->status,
                    'available_at' => optional($tx->available_at)->toIso8601String(),
                    'created_at' => optional($tx->created_at)->toIso8601String(),
                ];
            });

        $totalEarned = (float)CustomerWalletTransaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'credit')
            ->where('status', 'completed')
            ->sum('amount');

        $totalSpent = (float)CustomerWalletTransaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'debit')
            ->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'wallet_balance' => (float)$user->wallet_balance,
                'total_earned' => $totalEarned,
                'total_spent' => $totalSpent,
                'config' => $walletService->getWalletConfig(),
                'transactions' => $transactions,
            ],
        ]);
    }

    private function resolveCustomer(Request $request): ?User
    {
        $authHeader = (string) $request->header('Authorization', '');
        if (! str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        $plainTextToken = trim(substr($authHeader, 7));
        if ($plainTextToken === '') {
            return null;
        }

        $tokenHash = hash('sha256', $plainTextToken);
        $token = CustomerAccessToken::query()
            ->with('user')
            ->where('token_hash', $tokenHash)
            ->first();

        if (! $token || ! $token->isValid()) {
            return null;
        }

        $user = $token->user;
        if (! $user || ! $user->is_active || $user->role !== 'customer') {
            return null;
        }

        return $user;
    }
}
