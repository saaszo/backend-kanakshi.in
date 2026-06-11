<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $monthlySales = Order::query()
            ->latest()
            ->get(['created_at', 'payment_status', 'total_amount'])
            ->groupBy(fn (Order $order): string => $order->created_at->format('Y-m'))
            ->map(fn ($orders, string $monthKey): object => (object) [
                'month_key' => $monthKey,
                'paid_revenue' => $orders
                    ->where('payment_status', 'paid')
                    ->sum(fn (Order $order): float => (float) $order->total_amount),
                'orders_count' => $orders->count(),
            ])
            ->sortByDesc('month_key')
            ->take(12)
            ->values();

        $paymentBreakdown = Order::query()
            ->select('payment_method')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('SUM(total_amount) as gross_total')
            ->groupBy('payment_method')
            ->get();

        $statusBreakdown = Order::query()
            ->select('status')
            ->selectRaw('COUNT(*) as orders_count')
            ->groupBy('status')
            ->get();

        $totals = [
            'gross_revenue' => (float) Order::query()->sum('total_amount'),
            'paid_revenue' => (float) Order::query()->where('payment_status', 'paid')->sum('total_amount'),
            'refunded_total' => (float) Order::query()->where('payment_status', 'refunded')->sum('total_amount'),
            'total_orders' => (int) Order::query()->count(),
            'prepaid_orders' => (int) Order::query()->whereIn('payment_method', ['razorpay', 'phonepe'])->count(),
            'cod_orders' => (int) Order::query()->where('payment_method', 'cod')->count(),
        ];

        return view('admin.reports.index', [
            'monthlySales' => $monthlySales,
            'paymentBreakdown' => $paymentBreakdown,
            'statusBreakdown' => $statusBreakdown,
            'totals' => $totals,
        ]);
    }
}
