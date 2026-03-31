<?php

namespace App\Livewire\Admin;

use App\Models\Payment;
use App\Models\CashInHand;
use App\Models\Deposit;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Concerns\WithDynamicLayout;
use App\Models\PurchasePayment;

#[Title("Deposit By Cash")]
class Income extends Component
{
    use WithDynamicLayout, WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $todayIncome;
    public $cashIncome;
    public $chequeIncome;
    public $creditIncome;
    public $cashInHand;
    

    public $newCashInHand;

    public $depositDate;
    public $depositAmount;
    public $depositDescription;
    public $supplierPayment;

    public $totalDeposits;
    public $thisMonthDeposit = 0;
    public $previousMonthDeposit = 0;
    public $todayDeposits = 0;

    // New properties
    public $openingCash = 0; // POS opening cash for today
    public $todayReturns = 0; // Today's return amount (cash refunded)
    public $todayExpenses = 0; // Today's expenses
    public $cashLatePayments = 0; // Cash late payments (sale_id is null)

    public function mount()
    {
        $today = now()->toDateString();

        // Today's total income
        $this->todayIncome = Payment::whereDate('payment_date', $today)->sum('amount');

        // Breakdown by payment method
        $this->cashIncome = Payment::whereDate('payment_date', $today)
            ->whereNotNull('sale_id')
            ->where('payment_method', 'cash')
            ->sum('amount');

        $this->chequeIncome = Payment::whereDate('payment_date', $today)
            ->where('payment_method', 'cheque')
            ->sum('amount');

        $this->creditIncome = Payment::whereDate('payment_date', $today)
            ->where('payment_method', 'credit')
            ->sum('amount');

        // Get or create Cash in Hand
        $cashRecord = CashInHand::firstOrCreate(
            ['key' => 'cash in hand'],
            ['value' => 0]
        );

        $this->cashInHand = (int) $cashRecord->value;
        $this->newCashInHand = $this->cashInHand;

        // Get today's POS session opening cash
        $todaySession = \App\Models\POSSession::whereDate('session_date', $today)
            ->where('user_id', auth()->id())
            ->first();

        $this->openingCash = $todaySession ? $todaySession->opening_cash : 0;

        // Get today's returns (cash refunded to customers)
        $this->todayReturns = DB::table('returns_products')
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        // Get today's expenses
        $this->todayExpenses = DB::table('expenses')
            ->whereDate('created_at', $today)
            ->sum('amount');

        // Get cash late payments (where sale_id is null and payment_method is cash)
        $this->cashLatePayments = Payment::whereNull('sale_id')
            ->whereDate('payment_date', $today)
            ->where('payment_method', 'cash')
            ->where('is_completed', true)
            ->sum('amount');

        $this->supplierPayment = PurchasePayment::whereDate('payment_date', $today)
            ->where('payment_method', 'cash')
            ->sum('amount');

        // Calculate deposits
        $this->calculateDeposits();
        $this->calculateTodayDeposits();

        $this->depositDate = $today;
    }

    protected function calculateDeposits()
    {
        $this->thisMonthDeposit = Deposit::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        $this->previousMonthDeposit = Deposit::whereMonth('date', now()->subMonth()->month)
            ->whereYear('date', now()->subMonth()->year)
            ->sum('amount');

        $this->totalDeposits = Deposit::sum('amount');
    }

    protected function calculateTodayDeposits()
    {
        $this->todayDeposits = Deposit::whereDate('date', now()->toDateString())
            ->sum('amount');
    }

    // Updates CashInHand in DB considering today's deposits
    protected function updateCashInHandFromDeposits()
    {
        $cashRecord = CashInHand::where('key', 'cash in hand')->first();

        if ($cashRecord) {
            $extra = max(0, $this->todayDeposits - $this->cashIncome);
            $newCash = max(0, $cashRecord->value - $extra);

            $cashRecord->update(['value' => $newCash]);
            $this->cashInHand = $newCash;
        }
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

        $this->calculateDeposits();
        $this->calculateTodayDeposits();
        $this->updateCashInHandFromDeposits();

        $this->reset(['depositAmount', 'depositDescription']);

        $this->js("Swal.fire('Success!', 'Deposit added and Cash in Hand updated.', 'success')");
        $this->dispatch('close-modal', modalId: 'addIncomeModal');
        $this->dispatch('refreshPage');
    }

    public function deleteDeposit($id)
    {
        Deposit::find($id)?->delete();

        $this->calculateDeposits();
        $this->calculateTodayDeposits();
        $this->updateCashInHandFromDeposits();

        $this->js("Swal.fire('Deleted!', 'Deposit has been deleted and Cash in Hand updated.', 'success')");
        $this->dispatch('refreshPage');
    }

    public function updateCashInHand()
    {
        $this->validate([
            'newCashInHand' => 'required|integer|min:0',
        ]);

        $record = CashInHand::where('key', 'cash in hand')->first();

        if ($record) {
            $record->update(['value' => $this->newCashInHand]);
            $this->cashInHand = $this->newCashInHand;
        } else {
            // Create new record if missing
            CashInHand::create([
                'key' => 'cash in hand',
                'value' => $this->newCashInHand,
            ]);
            $this->cashInHand = $this->newCashInHand;
        }

        // ✅ SweetAlert confirmation
        $this->js("Swal.fire('Success!', 'Cash in Hand updated successfully.', 'success')");

        // ✅ Close the correct modal (the one in your top bar)
        $this->dispatch('close-modal', modalId: 'addCashInHandModal');
        $this->dispatch('close-modal', modalId: 'editCashModal');
        $this->dispatch('refreshPage');
    }


    public function render()
    {
        $deposits = Deposit::latest()->get();

        $today = now()->toDateString();

        // Get today's cash payments with sale and customer details
        $cashPayments = Payment::with(['sale.customer'])
            ->whereDate('payment_date', $today)
            ->where('payment_method', 'cash')
            ->orderBy('payment_date', 'desc')
            ->paginate(10);

        return view('livewire.admin.income', [
            'deposits' => $deposits,
            'cashPayments' => $cashPayments,
        ])->layout($this->layout);
    }
}
