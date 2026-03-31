<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Cheque;
use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('Return Cheque')]
class ReturnCheque extends Component
{
    use WithDynamicLayout;

    use WithPagination;

    public $selectedChequeId = null;

    // New cheque fields
    public $newChequeNumber;
    public $newBankName;
    public $newChequeDate;
    public $newChequeAmount;

    protected $rules = [
        'newChequeNumber' => 'required|string|max:255',
        'newBankName' => 'required|string|max:255',
        'newChequeDate' => 'required|date',
        'newChequeAmount' => 'required|numeric|min:0.01',
    ];

    public function getChequesProperty()
    {
        // Only return and cancelled cheques
        return Cheque::with('customer')
            ->whereIn('status', ['return', 'cancelled'])
            ->orderByRaw("CASE WHEN status = 'return' THEN 0 ELSE 1 END ASC")
            ->orderByDesc('cheque_date')
            ->paginate(20);
    }

    public function getReturnCountProperty()
    {
        return Cheque::where('status', 'return')->count();
    }

    public function getCancelledCountProperty()
    {
        return Cheque::where('status', 'cancelled')->count();
    }

    public function getOverdueCountProperty()
    {
        return Cheque::where('status', 'overdue')->count();
    }

    public function setSelectedCheque($id)
    {
        $this->selectedChequeId = $id;

        // Pre-fill the cheque amount from the selected cheque
        $cheque = Cheque::find($id);
        if ($cheque) {
            $this->newChequeAmount = $cheque->cheque_amount;
        }
    }

    public function rechequeSubmit()
    {
        $this->validate();

        // Check if cheque number already exists
        $existingCheque = Cheque::where('cheque_number', $this->newChequeNumber)->first();
        if ($existingCheque) {
            $this->js("Swal.fire('Error!', 'Cheque number already exists. Please use a different cheque number.', 'error');");
            return;
        }

        $oldCheque = Cheque::find($this->selectedChequeId);
        if (!$oldCheque) {
            $this->js("Swal.fire('Error!', 'Cheque not found.', 'error');");
            return;
        }

        try {
            // Mark old cheque as cancelled
            $oldCheque->status = 'cancelled';
            $oldCheque->save();

            // Add new cheque (copy customer/payment if needed)
            Cheque::create([
                'cheque_number' => $this->newChequeNumber,
                'bank_name' => $this->newBankName,
                'cheque_date' => $this->newChequeDate,
                'cheque_amount' => $this->newChequeAmount,
                'status' => 'pending',
                'customer_id' => $oldCheque->customer_id,
                'payment_id' => $oldCheque->payment_id,
            ]);

            // Reset form
            $this->resetForm();

            // Close modal and show success message
            $this->js("bootstrap.Modal.getInstance(document.getElementById('rechequeModal')).hide();");
            $this->js("Swal.fire('Success!', 'New cheque added and old cheque cancelled successfully.', 'success');");
            $this->js("location.reload();");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Failed to process re-cheque: " . addslashes($e->getMessage()) . "', 'error');");
        }
    }

    private function resetForm()
    {
        $this->selectedChequeId = null;
        $this->reset(['newChequeNumber', 'newBankName', 'newChequeDate', 'newChequeAmount']);
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.return-cheque', [
            'cheques' => $this->cheques,
            'returnCount' => $this->returnCount,
            'cancelledCount' => $this->cancelledCount,
            'overdueCount' => $this->overdueCount,
        ])->layout($this->layout);
    }
}
