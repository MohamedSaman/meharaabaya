<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('Analytics')]
class Analytics extends Component
{
    use WithDynamicLayout;

    public $totalRevenue = 0;
    public $totalDueAmount = 0;
    public $totalSales = 0;
    public $revenuePercentage = 0;
    public $duePercentage = 0;
    public $previousMonthRevenue = 0;
    public $revenueChangePercentage = 0;
    public $fullPaidCount = 0;
    public $fullPaidAmount = 0;
    public $partialPaidCount = 0;
    public $partialPaidAmount = 0;

    // Analytics data
    public $monthlySalesData = [];
    public $monthlyRevenueData = [];
    public $invoiceStatusData = [];
    public $paymentTrendsData = [];
    public $topPerformingMonths = [];

    public function mount()
    {
        // Get sales statistics
        $salesStats = Sale::select(
            DB::raw('SUM(total_amount) as total_sales'),
            DB::raw('SUM(due_amount) as total_due'),
            DB::raw('COUNT(*) as sales_count')
        )->first();

        // Calculate total revenue (total_amount - due_amount)
        $this->totalSales = $salesStats->total_sales ?? 0;
        $this->totalDueAmount = $salesStats->total_due ?? 0;
        $this->totalRevenue = $this->totalSales - $this->totalDueAmount;

        // Calculate percentages
        if ($this->totalSales > 0) {
            $this->revenuePercentage = round(($this->totalRevenue / $this->totalSales) * 100, 1);
            $this->duePercentage = round(($this->totalDueAmount / $this->totalSales) * 100, 1);
        }

        // Get previous month's revenue for comparison
        $previousMonthSales = Sale::whereMonth(
            'created_at',
            '=',
            now()->subMonth()->month
        )->select(
            DB::raw('SUM(total_amount - due_amount) as revenue')
        )->first();

        $this->previousMonthRevenue = $previousMonthSales->revenue ?? 0;

        // Calculate month-over-month change percentage
        if ($this->previousMonthRevenue > 0) {
            $this->revenueChangePercentage = round((($this->totalRevenue - $this->previousMonthRevenue) / $this->previousMonthRevenue) * 100, 1);
        }

        // Get fully paid invoices data
        $fullPaidData = Sale::where('payment_status', 'paid')
            ->select(
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as amount')
            )->first();

        $this->fullPaidCount = $fullPaidData->count ?? 0;
        $this->fullPaidAmount = $fullPaidData->amount ?? 0;

        // Get partially paid invoices data
        $partialPaidData = Sale::where('payment_status', 'partial')
            ->select(
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(due_amount) as amount')
            )->first();

        $this->partialPaidCount = $partialPaidData->count ?? 0;
        $this->partialPaidAmount = $partialPaidData->amount ?? 0;

        // Load analytics data
        $this->loadAnalyticsData();
    }

    public function loadAnalyticsData()
    {
        // Get monthly sales data for the last 12 months
        $this->monthlySalesData = DB::table('sales')
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total_invoices'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('SUM(total_amount - due_amount) as revenue'),
                DB::raw('SUM(due_amount) as due_amount')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'year' => $item->year,
                    'month' => $item->month,
                    'month_name' => date('M Y', mktime(0, 0, 0, $item->month, 1, $item->year)),
                    'total_invoices' => $item->total_invoices,
                    'total_sales' => $item->total_sales,
                    'revenue' => $item->revenue,
                    'due_amount' => $item->due_amount
                ];
            })
            ->toArray();

        // Get invoice status distribution
        $this->invoiceStatusData = DB::table('sales')
            ->select(
                'payment_status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as amount')
            )
            ->groupBy('payment_status')
            ->get()
            ->map(function ($item) {
                return [
                    'payment_status' => $item->payment_status,
                    'count' => $item->count,
                    'amount' => $item->amount
                ];
            })
            ->toArray();

        // Get payment trends (last 6 months)
        $this->paymentTrendsData = DB::table('payments')
            ->select(
                DB::raw('YEAR(payment_date) as year'),
                DB::raw('MONTH(payment_date) as month'),
                DB::raw('COUNT(*) as payment_count'),
                DB::raw('SUM(amount) as total_payments')
            )
            ->where('payment_date', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'year' => $item->year,
                    'month' => $item->month,
                    'month_name' => date('M Y', mktime(0, 0, 0, $item->month, 1, $item->year)),
                    'payment_count' => $item->payment_count,
                    'total_payments' => $item->total_payments
                ];
            })
            ->toArray();

        // Get top performing months
        $this->topPerformingMonths = collect($this->monthlySalesData)
            ->sortByDesc('revenue')
            ->take(3)
            ->values()
            ->toArray();
    }

    public function refreshAnalytics()
    {
        $this->loadAnalyticsData();
        $this->dispatch('analytics-refreshed');
    }

    public function render()
    {
        return view('livewire.admin.analytics', [
            'monthlySalesData' => $this->monthlySalesData,
            'invoiceStatusData' => $this->invoiceStatusData,
            'paymentTrendsData' => $this->paymentTrendsData,
            'topPerformingMonths' => $this->topPerformingMonths,
        ])->layout($this->layout);
    }
}
