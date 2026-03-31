<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Deposit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Livewire\Concerns\WithDynamicLayout;
use App\Models\POSSession;
use Carbon\Carbon;

#[Title("Deposits Management")]
class Deposits extends Component
{
    use WithDynamicLayout, WithPagination;

    // Filter properties
    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';

    // Modal properties
    public $showAddModal = false;
    public $showViewModal = false;
    public $depositDate = '';
    public $depositAmount = '';
    public $depositDescription = '';
    public $selectedDeposit = null;

    // Cash summary properties
    public $openingCash = 0;
    public $todayCashAmount = 0;
    public $todayExpenses = 0;
    public $todayRefunds = 0;
    public $todayDepositAmount = 0;
    public $todaySupplierPayments = 0;
    public $todayAdminCashAmount = 0;

    protected $rules = [
        'depositDate' => 'required|date',
        'depositAmount' => 'required|numeric|min:0.01',
        'depositDescription' => 'required|string|min:3|max:255',
    ];

    public function mount()
    {
        $this->depositDate = now()->format('Y-m-d');
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->loadCashSummary();
    }

    public function loadCashSummary()
    {
        // Get all today's POS sessions (for all users)
        $todaySessions = POSSession::where('session_date', now()->toDateString())->get();

        if ($todaySessions->count() > 0) {
            // Sum up all sessions for today
            $this->openingCash = $todaySessions->sum('opening_cash');
            $this->todayCashAmount = $todaySessions->sum('cash_sales');
            $this->todayAdminCashAmount = $todaySessions->sum('late_payment_bulk');
            $this->todayExpenses = $todaySessions->sum('expenses');
            $this->todayRefunds = $todaySessions->sum('refunds');
            $this->todaySupplierPayments = $todaySessions->sum('supplier_payment');
        } else {
            // No sessions today, get the latest closed session to determine opening cash
            $latestSession = POSSession::where('status', 'closed')
                ->latest('session_date')
                ->latest('closed_at')
                ->first();

            if ($latestSession) {
                $this->openingCash = $latestSession->closing_cash ?? 0; // Use previous session's closing as opening
                $this->todayCashAmount = 0; // No sales yet today
                $this->todayExpenses = 0; // No expenses yet today
                $this->todayRefunds = 0; // No refunds yet today
                $this->todaySupplierPayments = 0; // No supplier payments yet today
                $this->todayAdminCashAmount = 0; // No admin cash amount yet today
            } else {
                // Complete fallback to defaults if no sessions exist
                $this->openingCash = 0;
                $this->todayCashAmount = 0;
                $this->todayExpenses = 0;
                $this->todayRefunds = 0;
                $this->todaySupplierPayments = 0;
                $this->todayAdminCashAmount = 0;
            }
        }

        $this->todayDepositAmount = Deposit::whereDate('date', now())->sum('amount');
    }

    public function openAddModal()
    {
        $this->reset(['depositAmount', 'depositDescription']);
        $this->depositDate = now()->format('Y-m-d');
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->reset(['depositAmount', 'depositDescription']);
        $this->resetErrorBag();
    }

    public function addDeposit()
    {
        $this->validate();

        Deposit::create([
            'date' => $this->depositDate,
            'amount' => $this->depositAmount,
            'description' => $this->depositDescription,
        ]);

        // After adding deposit, update cash_deposit_bank in today's POS sessions
        $totalDepositsToday = Deposit::whereDate('date', $this->depositDate)->sum('amount');
        POSSession::where('session_date', $this->depositDate)
            ->update(['cash_deposit_bank' => $totalDepositsToday]);

        $this->closeAddModal();
        $this->loadCashSummary();
        $this->js("Swal.fire('Success!', 'Deposit added successfully!', 'success')");
    }

    public function viewDeposit($depositId)
    {
        $this->selectedDeposit = Deposit::find($depositId);
        if ($this->selectedDeposit) {
            $this->showViewModal = true;
        }
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->selectedDeposit = null;
    }

    public function deleteDeposit($depositId)
    {
        $deposit = Deposit::find($depositId);
        if ($deposit) {
            $deposit->delete();
            $this->loadCashSummary();
            $this->js("Swal.fire('Success!', 'Deposit deleted successfully!', 'success')");
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function render()
    {
        $deposits = Deposit::query()
            ->when($this->search, function ($query) {
                $query->where('description', 'like', '%' . $this->search . '%')
                    ->orWhere('amount', 'like', '%' . $this->search . '%');
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('date', '<=', $this->dateTo);
            })
            ->latest('date')
            ->paginate(15);

        return view('livewire.admin.deposits', [
            'deposits' => $deposits,
        ])->layout($this->layout);
    }
}
