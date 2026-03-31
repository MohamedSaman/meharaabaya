<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProductSupplier;
use App\Models\PurchasePayment;
use App\Models\PurchaseOrder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title("List Supplier Receipt")]
class ListSupplierReceipt extends Component
{
    use WithDynamicLayout;

    use WithPagination;

    public $showPaymentModal = false;
    public $selectedSupplier = null;
    public $payments = [];
    public $searchOrderNumber = '';
    public $searchedOrder = null;
    public $orderPayments = [];

    public function getSuppliersProperty()
    {
        // Get suppliers with total paid and receipt count (sum from purchase_payments table)
        return ProductSupplier::select(
            'product_suppliers.id',
            'product_suppliers.name',
            'product_suppliers.address',
            'product_suppliers.created_at',
            'product_suppliers.updated_at'
        )
            ->selectRaw('COALESCE(SUM(purchase_payments.amount),0) as total_paid')
            ->selectRaw('COUNT(purchase_payments.id) as receipts_count')
            ->leftJoin('purchase_payments', 'purchase_payments.supplier_id', '=', 'product_suppliers.id')
            ->groupBy(
                'product_suppliers.id',
                'product_suppliers.name',
                'product_suppliers.address',
                'product_suppliers.created_at',
                'product_suppliers.updated_at'
            )
            ->having('total_paid', '>', 0)
            ->orderByDesc('total_paid')
            ->paginate(20);
    }

    public function showSupplierPayments($supplierId)
    {
        $this->selectedSupplier = ProductSupplier::find($supplierId);
        $this->payments = PurchasePayment::with(['allocations', 'allocations.order'])
            ->where('supplier_id', $supplierId)
            ->orderByDesc('payment_date')
            ->get();
        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->selectedSupplier = null;
        $this->payments = [];
    }

    public function updatedSearchOrderNumber()
    {
        if (trim($this->searchOrderNumber) === '') {
            $this->searchedOrder = null;
            $this->orderPayments = [];
            return;
        }

        $this->searchedOrder = PurchaseOrder::with('supplier')
            ->where('order_code', 'LIKE', '%' . $this->searchOrderNumber . '%')
            ->first();

        if ($this->searchedOrder) {
            $this->orderPayments = PurchasePayment::with(['allocations.order'])
                ->whereHas('allocations', function ($q) {
                    $q->where('purchase_order_id', $this->searchedOrder->id);
                })
                ->orderByDesc('payment_date')
                ->get();
        } else {
            $this->orderPayments = [];
        }
    }

    public function clearSearch()
    {
        $this->searchOrderNumber = '';
        $this->searchedOrder = null;
        $this->orderPayments = [];
    }

    public function render()
    {
        return view('livewire.admin.list-supplier-receipt', [
            'suppliers' => $this->suppliers,
            'showPaymentModal' => $this->showPaymentModal,
            'selectedSupplier' => $this->selectedSupplier,
            'payments' => $this->payments,
        ])->layout($this->layout);
    }
}
