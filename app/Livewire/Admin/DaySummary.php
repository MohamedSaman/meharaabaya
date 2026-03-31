<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithDynamicLayout;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\POSSession;
use App\Models\Deposit;
use App\Models\CashInHand;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

#[Title('Day Summary List')]

class DaySummary extends Component
{
    use WithPagination;
    use WithDynamicLayout;

    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';

    // Deposit properties
    public $depositDate;
    public $depositAmount;
    public $depositDescription;

    // Today's summary
    public $todayCashAmount = 0;
    public $todayDepositAmount = 0;
    public $todayExpenses = 0;
    public $todayRefunds = 0;
    public $openingCash = 0;
    public $perPage = 10;

     public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function mount()
    {
        // Set default date range to current month
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->depositDate = now()->format('Y-m-d');

        // Calculate today's cash and deposits
        $this->calculateTodaySummary();
    }

    public function calculateTodaySummary()
    {
        $today = now()->toDateString();

        // Get today's POS session opening cash
        $todaySession = POSSession::whereDate('session_date', $today)
            ->where('user_id', auth()->id())
            ->first();

        $this->openingCash = $todaySession ? $todaySession->opening_cash : 0;

        // Today's cash amount (cash payments)
        $this->todayCashAmount = Payment::whereDate('payment_date', $today)
            ->where('payment_method', 'cash')
            ->where('is_completed', true)
            ->sum('amount');

        // Today's deposit amount
        $this->todayDepositAmount = Deposit::whereDate('date', $today)
            ->sum('amount');

        // Today's expenses
        $this->todayExpenses = DB::table('expenses')
            ->whereDate('created_at', $today)
            ->sum('amount');

        // Today's refunds/returns
        $this->todayRefunds = DB::table('returns_products')
            ->whereDate('created_at', $today)
            ->sum('total_amount');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function viewDetails($sessionId)
    {
        return redirect()->route('admin.day-summary-details', ['sessionId' => $sessionId]);
    }

    public function addDeposit()
    {
        $this->validate([
            'depositDate' => 'required|date',
            'depositAmount' => 'required|numeric|min:0.01',
            'depositDescription' => 'nullable|string|max:255',
        ]);

        Deposit::create([
            'date' => $this->depositDate,
            'amount' => $this->depositAmount,
            'description' => $this->depositDescription,
        ]);

        $this->reset(['depositAmount', 'depositDescription']);
        $this->depositDate = now()->format('Y-m-d');

        // Recalculate today's summary
        $this->calculateTodaySummary();

        $this->js("Swal.fire('Success!', 'Deposit added successfully.', 'success')");
        $this->dispatch('close-modal', modalId: 'addDepositModal');
        $this->dispatch('refreshPage');
    }

    public function render()
    {
        $sessions = POSSession::query()
            ->with('user')
            ->where('status', 'closed')
            ->when($this->search, function ($query) {
                $query->where('session_date', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('session_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('session_date', '<=', $this->dateTo);
            })
            ->orderBy('session_date', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.day-summary', [
            'sessions' => $sessions
        ])->layout($this->layout);
    }
}
