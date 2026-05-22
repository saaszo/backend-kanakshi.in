<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        // 1. Core KPIs
        $totalSales = Order::query()->where('payment_status', 'paid')->sum('total_amount');
        $ordersCount = Order::query()->count();
        $completedOrders = Order::query()->where('status', 'delivered')->count();
        $pendingOrdersCount = Order::query()->where('status', 'pending')->count();
        $shippedOrdersCount = Order::query()->where('status', 'shipped')->count();

        // 2. 30-Day Sales Trend (Chronological)
        // We initialize last 30 days with 0s to guarantee continuous data on the chart
        $days = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $days[$date] = [
                'label' => Carbon::now()->subDays($i)->format('M d'),
                'revenue' => 0.0,
                'orders' => 0
            ];
        }

        $rawSales = Order::query()
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->select(
                DB::raw("date(created_at) as sales_date"),
                DB::raw("SUM(total_amount) as total_revenue"),
                DB::raw("COUNT(id) as total_orders")
            )
            ->groupBy('sales_date')
            ->get();

        foreach ($rawSales as $sale) {
            $dateStr = $sale->sales_date;
            if (isset($days[$dateStr])) {
                $days[$dateStr]['revenue'] = (float)$sale->total_revenue;
                $days[$dateStr]['orders'] = (int)$sale->total_orders;
            }
        }

        // Split into separate arrays for Chart.js input
        $trendLabels = [];
        $trendRevenue = [];
        $trendOrders = [];
        foreach ($days as $dayData) {
            $trendLabels[] = $dayData['label'];
            $trendRevenue[] = $dayData['revenue'];
            $trendOrders[] = $dayData['orders'];
        }

        // 3. Category Sales Distribution (Doughnut Chart)
        $categoryShares = OrderItem::query()
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name as cat_name', DB::raw('SUM(order_items.quantity) as qty'))
            ->groupBy('cat_name')
            ->orderByDesc('qty')
            ->get();

        $catLabels = [];
        $catValues = [];
        foreach ($categoryShares as $share) {
            $catLabels[] = $share->cat_name;
            $catValues[] = (int)$share->qty;
        }

        // If category shares is empty, seed placeholder for beautiful chart rendering
        if (empty($catLabels)) {
            $catLabels = ['No Sales'];
            $catValues = [1];
        }

        // 4. Top Selling Products
        $topProducts = OrderItem::query()
            ->select('product_id', 'name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(line_total) as sales_amount'))
            ->groupBy('product_id', 'name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // 5. Recent Pending Orders (Needs Attention)
        $needsAttention = Order::query()
            ->where('status', 'pending')
            ->with(['items'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'stats' => [
                'products' => Product::query()->count(),
                'categories' => Category::query()->count(),
                'homepage_sections' => HomepageSection::query()->count(),
                'admins' => User::query()->whereIn('role', ['super_admin', 'admin', 'manager', 'staff'])->count(),
                
                // Analytics KPIs
                'total_sales' => $totalSales,
                'orders_count' => $ordersCount,
                'completed_orders' => $completedOrders,
                'pending_orders' => $pendingOrdersCount,
                'shipped_orders' => $shippedOrdersCount,
            ],
            'chartData' => [
                'labels' => $trendLabels,
                'revenue' => $trendRevenue,
                'orders' => $trendOrders,
                'catLabels' => $catLabels,
                'catValues' => $catValues,
            ],
            'topProducts' => $topProducts,
            'needsAttention' => $needsAttention,
        ]);
    }
}

