<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Cheque;
use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Log;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('Cheque List')]
class ChequeList extends Component
{
    use WithDynamicLayout;

    use WithPagination;
    public $perPage = 10;
    public $search = '';
    public $statusFilter = 'all';
    public $dateFrom = '';
    public $dateTo = '';

    // Edit modal
    public $showEditModal = false;
    public $editId = null;
    public $editChequeNumber = '';
    public $editBankName = '';
    public $editChequeDate = '';
    public $editChequeAmount = '';

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function getChequesProperty()
    {
        // Show pending cheques first, then others by cheque_date desc
        $query = Cheque::with('customer')
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END ASC")
            ->orderByDesc('cheque_date');

        if (!empty($this->search)) {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('cheque_number', 'like', $term)
                    ->orWhere('bank_name', 'like', $term)
                    ->orWhereHas('customer', function ($cq) use ($term) {
                        $cq->where('name', 'like', $term)
                            ->orWhere('phone', 'like', $term);
                    });
            });
        }
        // Apply status filter if set
        if (!empty($this->statusFilter) && $this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }
        // Apply date range filter
        if (!empty($this->dateFrom)) {
            $query->whereDate('cheque_date', '>=', $this->dateFrom);
        }
        if (!empty($this->dateTo)) {
            $query->whereDate('cheque_date', '<=', $this->dateTo);
        }

        return $query->paginate($this->perPage);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function clearDateFilter()
    {
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function getPendingCountProperty()
    {
        return Cheque::where('status', 'pending')->count();
    }

    public function getCompleteCountProperty()
    {
        return Cheque::where('status', 'complete')->count();
    }

    public function getOverdueCountProperty()
    {
        return Cheque::where('status', 'overdue')->count();
    }

    public function openEditModal($id)
    {
        $cheque = Cheque::find($id);
        if (!$cheque) {
            $this->dispatch('toast', type: 'error', message: 'Cheque not found!');
            return;
        }
        $this->editId = $id;
        $this->editChequeNumber = $cheque->cheque_number;
        $this->editBankName = $cheque->bank_name;
        $this->editChequeDate = $cheque->cheque_date;
        $this->editChequeAmount = $cheque->cheque_amount;
        $this->showEditModal = true;
    }

    public function updateCheque()
    {
        $this->validate([
            'editChequeNumber' => 'required|string|max:100',
            'editBankName'     => 'required|string|max:100',
            'editChequeDate'   => 'required|date',
        ], [
            'editChequeNumber.required' => 'Cheque number is required.',
            'editBankName.required'     => 'Bank name is required.',
            'editChequeDate.required'   => 'Cheque date is required.',
        ]);

        try {
            $cheque = Cheque::find($this->editId);
            if (!$cheque) {
                $this->dispatch('toast', type: 'error', message: 'Cheque not found!');
                return;
            }

            $cheque->update([
                'cheque_number' => $this->editChequeNumber,
                'bank_name'     => $this->editBankName,
                'cheque_date'   => $this->editChequeDate,
            ]);

            $this->showEditModal = false;
            $this->dispatch('toast', type: 'success', message: 'Cheque updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating cheque: ' . $e->getMessage());
            $this->dispatch('toast', type: 'error', message: 'Failed to update cheque!');
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editId = null;
        $this->editChequeNumber = '';
        $this->editBankName = '';
        $this->editChequeDate = '';
        $this->editChequeAmount = 0;
    }

    public function confirmComplete($id)
    {
        $this->js("
            if (confirm('Mark this cheque as complete?')) {
                \$wire.completeCheque({$id});
            }
        ");
    }

    public function confirmReturn($id)
    {
        $this->js("
            if (confirm('Return this cheque?')) {
                \$wire.returnCheque({$id});
            }
        ");
    }

    public function completeCheque($id)
    {
        try {
            $cheque = Cheque::find($id);
            if (!$cheque) {
                $this->dispatch('toast', type: 'error', message: 'Cheque not found!');
                return;
            }
            $cheque->status = 'complete';
            $cheque->save();
            $this->dispatch('toast', type: 'success', message: 'Cheque marked as complete successfully!');
        } catch (\Exception $e) {
            Log::error("Error completing cheque: " . $e->getMessage());
            $this->dispatch('toast', type: 'error', message: 'Failed to mark cheque as complete!');
        }
    }

    public function returnCheque($id)
    {
        try {
            $cheque = Cheque::find($id);
            if (!$cheque) {
                $this->dispatch('toast', type: 'error', message: 'Cheque not found!');
                return;
            }
            $cheque->status = 'return';
            $cheque->save();
            $this->dispatch('toast', type: 'success', message: 'Cheque returned successfully!');
        } catch (\Exception $e) {
            Log::error("Error returning cheque: " . $e->getMessage());
            $this->dispatch('toast', type: 'error', message: 'Failed to return cheque!');
        }
    }

    public function render()
    {
        return view('livewire.admin.cheque-list', [
            'cheques' => $this->cheques,
            'pendingCount' => $this->pendingCount,
            'completeCount' => $this->completeCount,
            'overdueCount' => $this->overdueCount,
        ])->layout($this->layout);
    }
}
