<?php

namespace App\Livewire\Admin;

use Exception;
use App\Models\Payment;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('Payment Approvals')]
class PaymentApprovals extends Component
{
    use WithDynamicLayout;

    use WithPagination;
    
    public $search = '';
    public $selectedPayment = null;
    public $rejectionReason = '';
    public $filters = [
        'status' => 'pending', // Default to showing pending payments
        'dateRange' => '',
    ];
    
    public function getPaymentDetails($paymentId)
    {
        try {
            $this->selectedPayment = Payment::with([
                'sale.customer', 
                'sale.items',
                'sale.items.product', // Use the correct relationship name
                'sale.user'
            ])->findOrFail($paymentId);
        } catch (Exception $e) {
            // dd([
            //     'error' => 'Payment not found',
            //     'message' => $e->getMessage(),
            //     'line' => $e->getLine(),
            //     'file' => $e->getFile(),
            //     'paymentId' => $paymentId
            // ]);
            $this->js('swal.fire("Error", "' . $e->getMessage() . '", "error")');
            return;
        }

        
        $this->dispatch('openModal', 'payment-approval-modal');
    }
    
    public function approvePayment()
    {
        try {
            DB::beginTransaction();
            
            $payment = Payment::findOrFail($this->selectedPayment->id);
            
            // Update the payment record
            $payment->update([
                'is_completed' => true,    // Mark as completed
                'status' => 'approved',    // Change status to approved
                'payment_date' => now(),   // Set payment date to current date
            ]);
            
            // Update sale if this completes all payments
            $sale = $payment->sale;
            
            // Check if all payments are completed
            $pendingPayments = $sale->payments()->where('is_completed', false)->count();
            
            // Reduce the due_amount by the payment amount
            $newDueAmount = max(0, $sale->due_amount - $payment->amount);
            $sale->update([
                'due_amount' => $newDueAmount,
            ]);

            // If due_amount is now 0, mark payment_status as 'paid'
            if ($newDueAmount == 0) {
                $sale->update([
                    'payment_status' => 'paid',
                ]);
            }
            
            DB::commit();
            
            $this->dispatch('closeModal', 'payment-approval-modal');
            $this->dispatch('showToast', [
                'type' => 'success', 
                'message' => 'Payment approved successfully'
            ]);
            
            $this->selectedPayment = null;
            
        } catch (Exception $e) {
            DB::rollback();
            $this->dispatch('showToast', [
                'type' => 'error', 
                'message' => 'Failed to approve payment: ' . $e->getMessage()
            ]);
        }
    }
    
    public function rejectPayment()
    {
        $this->validate([
            'rejectionReason' => 'required|min:5',
        ]);
        
        try {
            DB::beginTransaction();
            
            $payment = Payment::findOrFail($this->selectedPayment->id);
            
            // Store the original due date string for the note
            $originalDueDate = $payment->due_date->format('Y-m-d');
            
            // Calculate new due date (current due date + 3 days)
            $newDueDate = $payment->due_date->copy()->addDays(3);
            
            $payment->update([
                'status' => 'rejected',
                'due_date' => $newDueDate, // Update with extended due date
                // Keep is_completed as false since it wasn't completed
            ]);
            
            // Add rejection reason and due date extension info to sale notes
            $payment->sale->update([
                'notes' => ($payment->sale->notes ? $payment->sale->notes . "\n" : '') . 
                    "Payment rejected on " . now()->format('Y-m-d H:i') . ". Reason: " . $this->rejectionReason . 
                    "\nDue date extended from " . $originalDueDate . " to " . $newDueDate->format('Y-m-d') . " (3 days grace period).",
                'created_at' => now(), // Update created_at to reflect the change
                'updated_at' => now(), // Update updated_at to reflect the change
            ]);
            
            DB::commit();
            
            $this->dispatch('closeModal', 'payment-approval-modal');
            $this->dispatch('showToast', [
                'type' => 'info', 
                'message' => 'Payment rejected and due date extended by 3 days'
            ]);
            
            $this->selectedPayment = null;
            $this->rejectionReason = '';
            
        } catch (Exception $e) {
            DB::rollback();
            $this->dispatch('showToast', [
                'type' => 'error', 
                'message' => 'Failed to reject payment: ' . $e->getMessage()
            ]);
        }
    }
    
    public function resetFilters()
    {
        $this->reset('filters');
        $this->filters['status'] = 'pending'; // Reset to showing pending payments
    }
    
    public function render()
    {
        $query = Payment::query()
            ->with(['sale.customer', 'sale.user'])
            ->where('is_completed', false);
            
        // Only show payments with status='pending' by default
        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        } else {
            $query->where('status', 'pending');
        }
        
        // Apply search filter
        if ($this->search) {
            $query->whereHas('sale', function($q) {
                $q->where('invoice_number', 'like', "%{$this->search}%")
                  ->orWhereHas('customer', function($q2) {
                      $q2->where('name', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%");
                  });
            });
        }
        
        // Apply date range filter
        if ($this->filters['dateRange']) {
            [$startDate, $endDate] = explode(' to ', $this->filters['dateRange']);
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        $payments = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Calculate summary stats for dashboard
        $stats = [
            'pending' => Payment::where('status', 'pending')->count(),
            'approved' => Payment::where('status', 'approved')->count(),
            'rejected' => Payment::where('status', 'rejected')->count(),
            'today' => Payment::where('status', 'approved')->whereDate('updated_at', today())->count()
        ];
        
        return view('livewire.admin.payment-approvals', [
            'payments' => $payments,
            'stats' => $stats
        ])->layout($this->layout);
    }
    
    // Helper method to check if file is a PDF
    private function isPdf($filename) 
    {
        if (!$filename) return false;
        
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        return strtolower($extension) === 'pdf';
    }
}
