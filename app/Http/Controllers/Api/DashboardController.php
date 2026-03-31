<?php

namespace App\Http\Controllers\Api;

use App\Models\Sale;
use App\Models\ProductDetail;
use App\Models\ProductStock;
use App\Models\Customer;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends ApiController
{
    /**
     * Get dashboard statistics
     */
    public function index(Request $request)
    {
        // Total Products
        $totalProducts = ProductDetail::count();
        $activeProducts = ProductDetail::where('status', 'active')->count();

        // Stock statistics
        $totalStock = ProductStock::sum('available_stock');
        $lowStockCount = ProductStock::where('available_stock', '<', 10)->count();

        // Low stock products for alerts
        $lowStockProducts = ProductStock::with('product')
            ->where('available_stock', '<', 10)
            ->orderBy('available_stock', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($stock) {
                return [
                    'product__id' => $stock->product_id,
                    'product__name' => $stock->product ? $stock->product->name : 'Unknown',
                    'product__code' => $stock->product ? $stock->product->code : 'N/A',
                    'quantity' => (int) $stock->available_stock,
                ];
            });

        // Sales statistics
        $todaySales = Sale::whereDate('created_at', today())->sum('total_amount');
        $monthSales = Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
        $totalSalesCount = Sale::count();
        $totalSalesAmount = Sale::sum('total_amount');

        // Recent sales
        $recentSales = Sale::with('customer')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'sale_number' => $sale->invoice_number ?? $sale->id,
                    'customer__name' => $sale->customer ? $sale->customer->name : 'Walk-in',
                    'total_amount' => (string) $sale->total_amount,
                    'sale_date' => $sale->created_at->toDateString(),
                ];
            });

        // Due amount
        $totalDueAmount = Sale::where('payment_status', '!=', 'paid')->sum('due_amount');

        // Customer count
        $totalCustomers = Customer::count();

        // Total expenses (placeholder - adjust if you have Expense model)
        $totalExpenses = 0;

        // Purchase orders
        $pendingOrders = PurchaseOrder::where('status', 'pending')->count();
        $totalOrdersAmount = PurchaseOrder::whereMonth('created_at', now()->month)->sum('total_amount');

        return $this->success([
            'products' => [
                'total' => $totalProducts,
                'active' => $activeProducts,
                'low_stock' => $lowStockCount,
            ],
            'stock' => [
                'total_available' => (int) $totalStock,
                'low_stock_count' => $lowStockCount,
            ],
            'sales' => [
                'total' => $totalSalesCount,
                'amount' => (float) $totalSalesAmount,
                'today' => (float) $todaySales,
                'this_month' => (float) $monthSales,
            ],
            'finance' => [
                'total_due' => (float) $totalDueAmount,
            ],
            'customers' => [
                'total' => $totalCustomers,
            ],
            'expenses' => [
                'total' => $totalExpenses,
            ],
            'purchases' => [
                'pending_orders' => $pendingOrders,
                'month_total' => (float) $totalOrdersAmount,
            ],
            'recent_sales' => $recentSales,
            'low_stock_products' => $lowStockProducts,
        ]);
    }

    /**
     * Get recent activity
     */
    public function recentActivity(Request $request)
    {
        $limit = $request->get('limit', 10);

        // Recent sales
        $recentSales = Sale::with(['customer'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($sale) {
                return [
                    'type' => 'sale',
                    'id' => $sale->id,
                    'description' => "Sale #{$sale->invoice_number}",
                    'customer' => $sale->customer ? $sale->customer->name : 'Walk-in',
                    'amount' => (float) $sale->total_amount,
                    'status' => $sale->status,
                    'created_at' => $sale->created_at,
                ];
            });

        // Recent purchase orders
        $recentOrders = PurchaseOrder::with(['supplier'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($order) {
                return [
                    'type' => 'purchase',
                    'id' => $order->id,
                    'description' => "Order #{$order->order_code}",
                    'supplier' => $order->supplier ? $order->supplier->name : 'Unknown',
                    'amount' => (float) $order->total_amount,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                ];
            });

        // Merge and sort by date
        $activities = $recentSales->concat($recentOrders)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values();

        return $this->success($activities);
    }
}
