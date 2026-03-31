<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Payment;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('View Payments')]
class ViewPayments extends Component
{
    use WithDynamicLayout;

    use WithPagination;
    
    public $search = '';
    public $selectedPayment = null;
    public $filters = [
        'status' => '',
        'paymentMethod' => '',
        'dateRange' => '',
    ];
    
    public function viewPaymentDetails($paymentId)
    {
        try {
            $this->selectedPayment = Payment::with([
                'sale',
                'sale.customer',
                'sale.user',
                'sale.items',
                'sale.items.product'
            ])->findOrFail($paymentId);
            
            // Dispatch event to open modal
            $this->dispatch('open-payment-modal');
            
        } catch (\Exception $e) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'Error loading payment: ' . $e->getMessage()
            ]);
        }
    }
    
    public function resetFilters()
    {
        $this->reset('filters');
    }
    
    public function render()
    {
        $query = Payment::query()
            ->with(['sale', 'sale.customer', 'sale.user'])
            ->when($this->search, function($q) {
                return $q->whereHas('sale', function($sq) {
                    $sq->where('invoice_number', 'like', "%{$this->search}%")
                       ->orWhereHas('customer', function($cq) {
                           $cq->where('name', 'like', "%{$this->search}%")
                              ->orWhere('phone', 'like', "%{$this->search}%");
                       });
                });
            })
            ->when($this->filters['status'], function($q) {
                return $q->where('status', $this->filters['status']);
            })
            ->when($this->filters['paymentMethod'], function($q) {
                return $q->where('payment_method', $this->filters['paymentMethod']);
            });
            
        $payments = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get summary stats
        $totalPayments = Payment::sum('amount');
        $pendingPayments = Payment::where('status', 'pending')->sum('amount');
        $approvedPayments = Payment::where('status', 'approved')->sum('amount');
        $completedPayments = Payment::where('status', 'paid')->sum('amount');
        
        return view('livewire.admin.view-payments', [
            'payments' => $payments,
            'totalPayments' => $totalPayments,
            'pendingPayments' => $pendingPayments,
            'approvedPayments' => $approvedPayments,
            'completedPayments' => $completedPayments,
        ])->layout($this->layout);
    }
}
