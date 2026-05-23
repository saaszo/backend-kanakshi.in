<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $monthlySales = Order::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_key")
            ->selectRaw('SUM(CASE WHEN payment_status = "paid" THEN total_amount ELSE 0 END) as paid_revenue')
            ->selectRaw('COUNT(*) as orders_count')
            ->groupBy('month_key')
            ->orderByDesc('month_key')
            ->limit(12)
            ->get();

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
