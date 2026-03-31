<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Sale;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('List Customer Receipt')]
class ListCustomerReceipt extends Component
{
    use WithDynamicLayout;

    use WithPagination;

    public $showPaymentModal = false;
    public $selectedCustomer = null;
    public $payments = [];

    public function getCustomersProperty()
    {
        // Get customers with total paid and receipt count (sum from payments table)
        return Customer::select(
            'customers.id',
            'customers.name',
            'customers.email',
            'customers.address',
            'customers.created_at',
            'customers.updated_at'
        )
            ->selectRaw('COALESCE(SUM(payments.amount),0) as total_paid')
            ->selectRaw('COUNT(payments.id) as receipts_count')
            ->leftJoin('payments', 'payments.customer_id', '=', 'customers.id')
            ->groupBy(
                'customers.id',
                'customers.name',
                'customers.email',
                'customers.address',
                'customers.created_at',
                'customers.updated_at'
            )
            ->having('total_paid', '>', 0)
            ->orderByDesc('total_paid')
            ->paginate(20);
    }

    public function showCustomerPayments($customerId)
    {
        $this->selectedCustomer = Customer::find($customerId);
        $this->payments = Payment::with(['allocations', 'allocations.sale', 'cheques'])
            ->where('customer_id', $customerId)
            ->orderByDesc('payment_date')
            ->get();
        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->selectedCustomer = null;
        $this->payments = [];
    }

    public function render()
    {
        return view('livewire.admin.list-customer-receipt', [
            'customers' => $this->customers,
            'showPaymentModal' => $this->showPaymentModal,
            'selectedCustomer' => $this->selectedCustomer,
            'payments' => $this->payments,
        ])->layout($this->layout);
    }
}
