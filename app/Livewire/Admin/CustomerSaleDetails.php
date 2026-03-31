<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\Customer;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('Customer Sales Details')]
class CustomerSaleDetails extends Component
{
    use WithDynamicLayout;

    use WithPagination;

    public $modalData = null;

    public function viewSaleDetails($customerId)
    {
        // Get customer details
        $customer = Customer::findOrFail($customerId);

        // Get customer sales summary
        $salesSummary = DB::table('sales')
            ->where('customer_id', $customerId)
            ->select(
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('SUM(due_amount) as total_due'),
                DB::raw('SUM(total_amount) - SUM(due_amount) as total_paid')
            )
            ->first();

        // Get individual invoices
        $invoices = Sale::where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get product-wise sales with Product details
        $productSales = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('product_details', 'sale_items.product_id', '=', 'product_details.id')
            ->leftJoin('brand_lists', 'brand_lists.id', '=', 'product_details.brand_id')
            ->where('sales.customer_id', $customerId)
            ->select(
                'sale_items.*',
                'sales.invoice_number',
                'sales.created_at as sale_date',
                'product_details.name as product_name',
                'brand_lists.brand_name as product_brand',
                'product_details.model as product_model',
                'product_details.image as product_image'
            )
            ->orderBy('sales.created_at', 'desc')
            ->get();

        $this->modalData = [
            'customer' => $customer,
            'salesSummary' => $salesSummary,
            'invoices' => $invoices,
            'productSales' => $productSales
        ];

        $this->dispatch('open-customer-sale-details-modal');
    }

    // For print functionality (main table)
    public function printData()
    {
        // Trigger JavaScript print function from the frontend
        $this->dispatch('print-customer-table');
    }

    // For CSV export
    public function exportToCSV()
    {
        $customerSales = DB::table('sales')
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->select(
                'customers.id as customer_id',
                'customers.name',
                'customers.email',
                'customers.business_name',
                'customers.type',
                DB::raw('COUNT(DISTINCT sales.invoice_number) as invoice_count'),
                DB::raw('SUM(sales.total_amount) as total_sales'),
                DB::raw('SUM(sales.due_amount) as total_due'),
                DB::raw('SUM(sales.total_amount) - SUM(sales.due_amount) as total_paid')
            )
            ->groupBy('customers.id', 'customers.name', 'customers.email', 'customers.business_name', 'customers.type')
            ->orderBy('total_sales', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="customer_sales_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($customerSales) {
            $file = fopen('php://output', 'w');

            // Add headers
            fputcsv($file, ['#', 'Customer Name', 'Email', 'Business Name', 'Type', 'Invoices', 'Total Sales', 'Total Paid', 'Total Due', 'Collection %']);

            // Add data rows
            foreach ($customerSales as $index => $customer) {
                $percentage = $customer->total_sales > 0 ? round(($customer->total_paid / $customer->total_sales) * 100) : 100;

                fputcsv($file, [
                    $index + 1,
                    $customer->name,
                    $customer->email,
                    $customer->business_name ?? 'N/A',
                    ucfirst($customer->type),
                    $customer->invoice_count,
                    'Rs.' . number_format($customer->total_sales, 2),
                    'Rs.' . number_format($customer->total_paid, 2),
                    'Rs.' . number_format($customer->total_due, 2),
                    $percentage . '%'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        $customerSales = DB::table('sales')
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->select(
                'customers.id as customer_id',
                'customers.name',
                'customers.email',
                'customers.business_name',
                'customers.type',
                DB::raw('COUNT(DISTINCT sales.invoice_number) as invoice_count'),
                DB::raw('SUM(sales.total_amount) as total_sales'),
                DB::raw('SUM(sales.due_amount) as total_due')
            )
            ->groupBy('customers.id', 'customers.name', 'customers.email', 'customers.business_name', 'customers.type')
            ->orderBy('total_sales', 'desc')
            ->paginate(10);

        return view('livewire.admin.customer-sale-details', [
            'customerSales' => $customerSales
        ])->layout($this->layout);
    }
}
