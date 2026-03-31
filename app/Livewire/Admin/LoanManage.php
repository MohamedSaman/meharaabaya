<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\loans;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title("Loan Management")]
class LoanManage extends Component
{
    use WithDynamicLayout;

    public $form = [
        'employee_id' => '',
        'loan_amount' => '',
        'interest_rate' => '',
        'start_date' => '',
        'term_month' => '',
    ];

    public $loanBreakdown = null;
    public $loans = [];
    public $employees = [];
    public $showLoanDetailsModal = false;
    public $loanDetails = [];

    protected $rules = [
        'form.employee_id' => 'required|exists:users,id',
        'form.loan_amount' => 'required|numeric|min:1',
        'form.interest_rate' => 'required|numeric|min:0',
        'form.start_date' => 'required|date',
        'form.term_month' => 'required|integer|min:1',
    ];

    public function mount()
    {
        $this->employees = User::all();
        $this->loadLoans();
    }

    public function loadLoans()
    {
        $this->loans = loans::with('user')->get();
    }

    public function addLoan()
    {
        $this->validate();

        $monthlyInterest = ($this->form['interest_rate'] / 100) / 12;
        $principal = $this->form['loan_amount'];
        $months = $this->form['term_month'];
        if ($months > 0) {
            if ($monthlyInterest == 0) {
                $monthlyPayment = $principal / $months;
            } else {
                $monthlyPayment = ($principal * $monthlyInterest) / (1 - pow(1 + $monthlyInterest, -$months));
            }
        } else {
            $monthlyPayment = 0;
        }

        $loan = loans::create([
            'user_id' => $this->form['employee_id'],
            'loan_amount' => $principal,
            'interest_rate' => $this->form['interest_rate'],
            'start_date' => $this->form['start_date'],
            'term_month' => $months,
            'remaining_balance' => $principal,
            'status' => 'active',
            'monthly_payment' => $monthlyPayment,
        ]);

        $this->loanBreakdown = [
            'employee_id' => $loan->user_id,
            'loan_amount' => $loan->loan_amount,
            'interest_rate' => $loan->interest_rate,
            'start_date' => $loan->start_date,
            'term_month' => $loan->term_month,
            'monthly_payment' => $loan->monthly_payment,
            'remaining_balance' => $loan->remaining_balance,
        ];

        Session::flash('message', 'Loan added successfully!');
        $this->resetForm();
        $this->loadLoans();
    }

    public function resetForm()
    {
        $this->form = [
            'employee_id' => '',
            'loan_amount' => '',
            'interest_rate' => '',
            'start_date' => '',
            'term_month' => '',
        ];
        $this->loanBreakdown = null;
    }

    public function showLoanDetails($loan_id)
    {
        $loan = loans::with('user')->find($loan_id);
        if (!$loan) {
            Session::flash('error', 'Loan not found.');
            return;
        }
        $this->loanDetails = [
            'loan_id' => $loan->loan_id,
            'employee_id' => $loan->user_id,
            'employee_name' => $loan->user ? $loan->user->name : 'Unknown',
            'designation' => $loan->user->designation ?? 'Employee',
            'start_date' => $loan->start_date,
            'start_date_full' => date('F d, Y', strtotime($loan->start_date)),
            'loan_amount' => $loan->loan_amount,
            'interest_rate' => $loan->interest_rate,
            'term_month' => $loan->term_month,
            'monthly_payment' => $loan->monthly_payment,
            'remaining_balance' => $loan->remaining_balance,
            'payment_history' => [], // Implement payment history if needed
            'total_paid' => 0,
        ];
        $this->showLoanDetailsModal = true;
    }

    public function closeLoanDetails()
    {
        $this->showLoanDetailsModal = false;
        $this->loanDetails = [];
    }

    public function markAsPaid($loan_id)
    {
        $loan = loans::find($loan_id);
        if ($loan && $loan->status === 'active') {
            $loan->status = 'paid';
            $loan->remaining_balance = 0;
            $loan->save();
            Session::flash('message', 'Loan marked as paid.');
            $this->loadLoans();
        } else {
            Session::flash('error', 'Loan not found or already paid.');
        }
    }

    public function render()
    {
        $this->employees = User::all();
     
        return view('livewire.admin.loan-manage', [
            'employees' => $this->employees,
            'loans' => $this->loans,
            'loanBreakdown' => $this->loanBreakdown,
            'showLoanDetailsModal' => $this->showLoanDetailsModal,
            'loanDetails' => $this->loanDetails,
        ])->layout($this->layout);
    }
}
