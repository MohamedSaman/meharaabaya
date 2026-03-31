<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\POSSession;
use Illuminate\Support\Facades\Log;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title("Expenses")]
class Expenses extends Component
{
    use WithDynamicLayout;

    // Data variables
    public $dailyExpenses = [];
    public $monthlyExpenses = [];
    public $dailyCategories = [];
    public $monthlyCategories = [];

    // Totals
    public $todayTotal = 0;
    public $monthTotal = 0;
    public $overallTotal = 0;

    // Form inputs for creating
    public $category, $amount, $date, $status, $description;

    // Form inputs for editing
    public $expenseId;
    public $edit_category, $edit_amount, $edit_date, $edit_status, $edit_description, $edit_expense_type;

    // Delete confirmation
    public $expenseToDelete;

    // Modal states
    public $showEditDailyModal = false;
    public $showEditMonthlyModal = false;
    public $showDeleteModal = false;
    public $showViewModal = false;
    public $viewExpense = null;

    public function mount()
    {
        $this->loadExpenses();
        $this->loadCategories();
    }

    public function loadCategories()
    {
        // Load Daily Expenses categories
        $this->dailyCategories = ExpenseCategory::where('expense_category', 'Daily Expenses')
            ->pluck('type')
            ->toArray();

        // Load Monthly Expenses categories
        $this->monthlyCategories = ExpenseCategory::where('expense_category', 'Monthly Expenses')
            ->pluck('type')
            ->toArray();
    }

    public function loadExpenses()
    {
        // Daily and Monthly lists
        $this->dailyExpenses = Expense::where('expense_type', 'daily')->latest()->get();
        $this->monthlyExpenses = Expense::where('expense_type', 'monthly')->latest()->get();

        // Totals
        $this->todayTotal = Expense::whereDate('date', Carbon::today())->sum('amount');
        $this->monthTotal = Expense::whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->sum('amount');
        $this->overallTotal = Expense::sum('amount');
    }

    public function saveDailyExpense()
    {
        $this->validate([
            'category' => 'required',
            'amount' => 'required|numeric|min:0',
        ]);

        Expense::create([
            'category' => $this->category,
            'amount' => $this->amount,
            'description' => $this->description,
            'date' => now(),
            'expense_type' => 'daily',
        ]);

        // Update cash in hands - subtract expense amount
        $cashInHandRecord = DB::table('cash_in_hands')->where('key', 'cash_amount')->first();

        if ($cashInHandRecord) {
            DB::table('cash_in_hands')
                ->where('key', 'cash_amount')
                ->update([
                    'value' => $cashInHandRecord->value - $this->amount,
                    'updated_at' => now()
                ]);
        }

        // Update today's POS session expenses and recalculate expected cash
        try {
            $session = POSSession::getTodaySession(Auth::id());
            if (! $session) {
                // create an open session with zero opening cash so expense is tracked
                $session = POSSession::openSession(Auth::id(), 0);
            }

            $session->expenses = ($session->expenses ?? 0) + $this->amount;
            $session->save();
            // Recalculate expected cash / difference
            $session->calculateDifference();
        } catch (\Exception $e) {
            Log::error('Failed to update POS session after daily expense: ' . $e->getMessage());
        }

        $this->reset(['category', 'amount', 'description']);
        $this->loadExpenses();
        $this->js("swal.fire('Success!', 'Daily expense added successfully.', 'success')");
        $this->dispatch('close-modal', 'addDailyExpenseModal');
        $this->dispatch('refreshPage');
    }

    public function saveMonthlyExpense()
    {
        $this->validate([
            'date' => 'required|date',
            'category' => 'required',
            'amount' => 'required|numeric|min:0',
        ]);

        Expense::create([
            'date' => $this->date,
            'category' => $this->category,
            'amount' => $this->amount,
            'status' => $this->status,
            'description' => $this->description,
            'expense_type' => 'monthly',
        ]);

        // Update cash in hands - subtract expense amount
        $cashInHandRecord = DB::table('cash_in_hands')->where('key', 'cash_amount')->first();

        if ($cashInHandRecord) {
            DB::table('cash_in_hands')
                ->where('key', 'cash_amount')
                ->update([
                    'value' => $cashInHandRecord->value - $this->amount,
                    'updated_at' => now()
                ]);
        }

        // If the monthly expense is for today, update today's POS session totals
        try {
            if ($this->date && Carbon::parse($this->date)->toDateString() === Carbon::today()->toDateString()) {
                $session = POSSession::getTodaySession(Auth::id());
                if (! $session) {
                    $session = POSSession::openSession(Auth::id(), 0);
                }

                $session->expenses = ($session->expenses ?? 0) + $this->amount;
                $session->save();
                $session->calculateDifference();
            }
        } catch (\Exception $e) {
            Log::error('Failed to update POS session after monthly expense: ' . $e->getMessage());
        }

        $this->reset(['date', 'category', 'amount', 'status', 'description']);
        $this->loadExpenses();
        $this->js("swal.fire('Success!', 'Monthly expense added successfully.', 'success')");
        $this->dispatch('close-modal', 'addMonthlyExpenseModal');
        $this->dispatch('refreshPage');
    }

    public function confirmDelete($id)
    {
        $this->expenseToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteExpense()
    {
        if ($this->expenseToDelete) {
            Expense::findOrFail($this->expenseToDelete)->delete();
            $this->loadExpenses();
            $this->js("swal.fire('Deleted!', 'Expense has been deleted.', 'success')");
            $this->showDeleteModal = false;
            $this->expenseToDelete = null;
            $this->dispatch('refreshPage');
        }
    }

    public function editExpense($id)
    {
        $expense = Expense::findOrFail($id);

        $this->expenseId = $expense->id;
        $this->edit_category = $expense->category;
        $this->edit_description = $expense->description;
        $this->edit_amount = $expense->amount;
        $this->edit_date = $expense->date ? $expense->date->format('Y-m-d') : '';
        $this->edit_status = $expense->status;
        $this->edit_expense_type = $expense->expense_type;

        // Open modal based on expense type
        if ($expense->expense_type === 'daily') {
            $this->showEditDailyModal = true;
        } else {
            $this->showEditMonthlyModal = true;
        }
    }

    public function viewExpense($id)
    {
        $this->viewExpense = Expense::findOrFail($id);
        $this->showViewModal = true;
    }

    public function updateExpense()
    {
        $this->validate([
            'edit_category' => 'required|string',
            'edit_amount' => 'required|numeric|min:0',
        ]);

        $expense = Expense::findOrFail($this->expenseId);

        $updateData = [
            'category' => $this->edit_category,
            'description' => $this->edit_description,
            'amount' => $this->edit_amount,
        ];

        if ($expense->expense_type === 'monthly') {
            $this->validate(['edit_date' => 'required|date']);
            $updateData['date'] = $this->edit_date;
            $updateData['status'] = $this->edit_status;
        } else {
            // For daily, use today's date
            $updateData['date'] = now();
        }

        $expense->update($updateData);

        // Close the modals
        $this->showEditDailyModal = false;
        $this->showEditMonthlyModal = false;

        $this->resetEditFields();
        $this->loadExpenses();
        $this->js("swal.fire('Success!', 'Expense updated successfully.', 'success')");
        $this->dispatch('refreshPage');
    }

    public function resetEditFields()
    {
        $this->reset([
            'expenseId',
            'edit_category',
            'edit_amount',
            'edit_date',
            'edit_status',
            'edit_description',
            'edit_expense_type'
        ]);
        $this->resetErrorBag();
    }

    public function resetFields()
    {
        $this->reset(['category', 'amount', 'date', 'status', 'description']);
        $this->resetErrorBag();
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewExpense = null;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->expenseToDelete = null;
    }

    public function closeEditDailyModal()
    {
        $this->showEditDailyModal = false;
        $this->resetEditFields();
    }

    public function closeEditMonthlyModal()
    {
        $this->showEditMonthlyModal = false;
        $this->resetEditFields();
    }

    public function render()
    {
        return view('livewire.admin.expenses')->layout($this->layout);
    }
}
