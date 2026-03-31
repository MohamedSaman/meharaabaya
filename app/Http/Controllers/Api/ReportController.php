<?php

namespace App\Http\Controllers\Api;

use App\Models\Sale;
use App\Models\PurchaseOrder;
use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends ApiController
{
    /**
     * Get day summary report
     */
    public function daySummary(Request $request)
    {
        $date = $request->get('date', Carbon::today()->toDateString());
        $startOfDay = Carbon::parse($date)->startOfDay();
        $endOfDay = Carbon::parse($date)->endOfDay();

        // Total Sales for the day
        $salesQuery = Sale::whereBetween('created_at', [$startOfDay, $endOfDay]);
        $totalSales = (float) $salesQuery->sum('total_amount');
        $transactionCount = $salesQuery->count();

        // Total Purchases for the day
        $purchasesQuery = PurchaseOrder::whereBetween('created_at', [$startOfDay, $endOfDay]);
        $totalPurchases = (float) $purchasesQuery->sum('total_amount');

        // Total Expenses for the day (if expenses table exists)
        $totalExpenses = 0;
        try {
            $totalExpenses = (float) Expense::whereBetween('created_at', [$startOfDay, $endOfDay])->sum('amount');
        } catch (\Exception $e) {
            // Expenses table might not exist
        }

        // Calculate profit
        $grossProfit = $totalSales - $totalPurchases;
        $netProfit = $grossProfit - $totalExpenses;

        // Payment breakdown
        $payments = Sale::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->select('payment_type', DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_type')
            ->get()
            ->pluck('total', 'payment_type')
            ->toArray();

        return $this->success([
            'date' => $date,
            'totalSales' => $totalSales,
            'totalPurchases' => $totalPurchases,
            'totalExpenses' => $totalExpenses,
            'grossProfit' => $grossProfit,
            'netProfit' => $netProfit,
            'transactionCount' => $transactionCount,
            'paymentBreakdown' => [
                'cash' => (float) ($payments['cash'] ?? $payments['full'] ?? 0),
                'card' => (float) ($payments['card'] ?? 0),
                'credit' => (float) ($payments['partial'] ?? 0),
                'cheque' => (float) ($payments['cheque'] ?? 0),
            ],
        ]);
    }

    /**
     * Get profit/loss report
     */
    public function profitLoss(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Total Sales
        $totalSales = (float) Sale::whereBetween('created_at', [$start, $end])->sum('total_amount');

        // Total Purchases
        $totalPurchases = (float) PurchaseOrder::whereBetween('created_at', [$start, $end])->sum('total_amount');

        // Total Expenses
        $totalExpenses = 0;
        try {
            $totalExpenses = (float) Expense::whereBetween('created_at', [$start, $end])->sum('amount');
        } catch (\Exception $e) {
            // Expenses table might not exist
        }

        // Calculate profits
        $grossProfit = $totalSales - $totalPurchases;
        $netProfit = $grossProfit - $totalExpenses;

        // Monthly breakdown
        $monthlyData = Sale::whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_amount) as sales')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return $this->success([
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'summary' => [
                'totalSales' => $totalSales,
                'totalPurchases' => $totalPurchases,
                'totalExpenses' => $totalExpenses,
                'grossProfit' => $grossProfit,
                'netProfit' => $netProfit,
                'profitMargin' => $totalSales > 0 ? round(($netProfit / $totalSales) * 100, 2) : 0,
            ],
            'monthlyBreakdown' => $monthlyData,
        ]);
    }

    /**
     * Get analytics data
     */
    public function analytics(Request $request)
    {
        $period = $request->get('period', 'month'); // week, month, year

        switch ($period) {
            case 'week':
                $start = Carbon::now()->startOfWeek();
                $end = Carbon::now()->endOfWeek();
                break;
            case 'year':
                $start = Carbon::now()->startOfYear();
                $end = Carbon::now()->endOfYear();
                break;
            default:
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfMonth();
        }

        // Daily sales for the period
        $dailySales = Sale::whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top selling products (based on sale items)
        $topProducts = DB::table('sale_items')
            ->join('product_details', 'sale_items.product_id', '=', 'product_details.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$start, $end])
            ->select(
                'product_details.name',
                'product_details.code',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.total) as total_amount')
            )
            ->groupBy('product_details.id', 'product_details.name', 'product_details.code')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        // Top customers
        $topCustomers = Sale::with('customer')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('customer_id')
            ->select(
                'customer_id',
                DB::raw('SUM(total_amount) as total_spent'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('customer_id')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get()
            ->map(function ($sale) {
                return [
                    'customer' => $sale->customer ? $sale->customer->name : 'Unknown',
                    'total_spent' => (float) $sale->total_spent,
                    'order_count' => $sale->order_count,
                ];
            });

        return $this->success([
            'period' => [
                'type' => $period,
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ],
            'dailySales' => $dailySales,
            'topProducts' => $topProducts,
            'topCustomers' => $topCustomers,
            'summary' => [
                'totalSales' => (float) Sale::whereBetween('created_at', [$start, $end])->sum('total_amount'),
                'totalOrders' => Sale::whereBetween('created_at', [$start, $end])->count(),
                'averageOrderValue' => (float) Sale::whereBetween('created_at', [$start, $end])->avg('total_amount') ?? 0,
            ],
        ]);
    }
}
