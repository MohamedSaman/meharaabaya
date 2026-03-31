<?php

namespace App\Livewire\Staff;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

#[Layout('components.layouts.staff')]
#[Title('Customer Sale Management')]
class CustomerSaleManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = '';
    public $filterCustomerType = '';
    public $selectedSaleId = null;
    public $selectedSale = null;
    public $saleItems = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterCustomerType' => ['except' => '']
    ];

    public function render()
    {
        $userId = Auth::id();

        // Convert this query to use Eloquent ORM
        $customerSales = Customer::select('customers.id', 'customers.name', 'customers.email', 'customers.phone', 'customers.type')
            ->withCount(['sales' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->withSum(['sales as total_sales' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }], 'total_amount')
            ->withSum(['sales as total_due' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }], 'due_amount')
            ->whereHas('sales', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->get();

        // Get all sales by this staff member
        $salesQuery = Sale::where('user_id', $userId)
            ->with('customer');

        if ($this->search) {
            $salesQuery->where(function ($query) {
                $query->where('invoice_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('customer', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%')
                            ->orWhere('phone', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->filterStatus) {
            $salesQuery->where('payment_status', $this->filterStatus);
        }

        if ($this->filterCustomerType) {
            $salesQuery->where('customer_type', $this->filterCustomerType);
        }

        $sales = $salesQuery->orderBy('created_at', 'desc')
            ->paginate(10);

        // Calculate total sales and due amount
        $totals = Sale::where('user_id', $userId)
            ->select(
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('SUM(due_amount) as total_due'),
                DB::raw('COUNT(DISTINCT customer_id) as customer_count')
            )
            ->first();

        return view('livewire.staff.customer-sale-management', [
            'sales' => $sales,
            'customerSales' => $customerSales,
            'totals' => $totals
        ]);
    }

    public function viewSaleDetails($saleId)
    {
        $this->selectedSaleId = $saleId;
        $this->selectedSale = Sale::with('customer')->find($saleId);

        // Assuming a 'product' relationship exists on the SaleItem model
        // Assuming a 'product' relationship exists on the SaleItem model
        // that links to the productDetail model.
        $this->saleItems = SaleItem::with('product')
            ->where('sale_id', $saleId)
            ->get();
        // dd($this->saleItems);

        $this->js("$('#saleDetailsModal').modal('show');");
        $this->js("$('#saleDetailsModal').modal('show');");
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filterStatus = '';
        $this->filterCustomerType = '';
    }

    public function downloadInvoice($saleId)
    {
        // Check web.php for the correct route name - should be 'receipts.download' not 'receipt.download'
        return $this->redirect(route('receipts.download', ['id' => $saleId]), navigate: false);
    }

    public function downloadReceipt()
    {
        if (!$this->selectedSale) {
            return;
        }

        return $this->redirect(route('receipts.download', ['id' => $this->selectedSale->id]), navigate: false);
    }
}
