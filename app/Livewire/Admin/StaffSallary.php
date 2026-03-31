<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\Salary;
use App\Models\Attendance;
use App\Models\UserDetail;
use App\Models\loans;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('Staff Salary')]
class StaffSallary extends Component
{
    use WithDynamicLayout;

    public $selectedUser;
    public $selectedMonth;
    public $selectedYear;
    public $salaryBreakdown = [];
    public $showBreakdown = false;
    public $recentSalaries;
    public $users;
    public ?Salary $selectedSalary = null; // Use a typed property for the selected salary object

    public function mount()
    {
        $this->users = User::where('role', '!=', 'admin')->get(); // Fetch non-admin users
        $this->selectedMonth = date('m');
        $this->selectedYear = date('Y');
        $this->loadRecentSalaries();
    }

    #[On('showPayslip')]
    public function showPayslip($salaryId)
    {
        $this->selectedSalary = Salary::with('user')->find($salaryId);
        if ($this->selectedSalary && $this->selectedSalary->user) {
            $userDetail = UserDetail::where('user_id', $this->selectedSalary->user->id)->first();
            $this->selectedSalary->user->userDetail = $userDetail;
        }
        $this->dispatch('open-payslip-modal');
    }

    public function loadRecentSalaries()
    {
        $this->recentSalaries = Salary::with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    public function generateSalary()
    {
        $this->validate([
            'selectedUser' => 'required',
            'selectedMonth' => 'required',
            'selectedYear' => 'required',
        ], ['selectedUser.required' => 'Please select an employee.']);

        $user = User::find($this->selectedUser);
        $userId = $user->id;
        $userDetail = UserDetail::where('user_id', $userId)->first();
        if (!$userDetail || !$userDetail->basic_salary) {
            $this->addError('form', 'Basic salary is not set for this employee.');
            return;
        }

        $basicSalary = $userDetail->basic_salary;
        $allowance = is_array($userDetail->allowance) ? array_sum($userDetail->allowance) : ($userDetail->allowance ?? 0);
        $salaryType = $userDetail->work_type; // 'monthly' or 'daily'

        $monthStart = Carbon::create($this->selectedYear, $this->selectedMonth, 1);
        $monthEnd = $monthStart->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get();

        $totalWorkedHours = $attendances->sum('time_worked') / 60; // in hours
        $lateHours = $attendances->sum('late_hours') / 60; // in hours
        $leaveDays = $attendances->where('status', 'absent')->count();
        $presentDays = $attendances->whereIn('status', ['present', 'normal', 'early'])->count();
     
        $compulsoryMonthDays = 26; // Standard working days in a month
        $compulsoryMonthHours = $compulsoryMonthDays * 8;
        
        $salary = 0;
        $deductions = 0;
        $perHourRate = 0;

        if ($salaryType === 'monthly') {
            $perHourRate = $basicSalary / $compulsoryMonthHours;
            $salary = $totalWorkedHours * $perHourRate; // Pro-rata salary based on hours worked
            $deductions = $lateHours * $perHourRate;
        } else { // daily
            $perDayRate = $basicSalary;
            $perHourRate = $perDayRate / 8;
            $salary = $totalWorkedHours * $perHourRate;
            $deductions = $lateHours * $perHourRate;
        }
        
        $netSalary = $salary + $allowance - $deductions;

        $loanDeduction = loans::where('user_id', $user->id)
                             ->where('status', 'active')
                             ->value('monthly_payment') ?? 0;
                             
        $netSalary -= $loanDeduction;

        $this->salaryBreakdown = [
            'user' => $user,
            'month' => $monthStart->format('F Y'),
            'salary_month_date' => $monthStart->toDateString(),
            'basic_salary' => $salary,
            'allowance' => $allowance,
            'total_worked_hours' => round($totalWorkedHours, 2),
            'late_hours' => round($lateHours, 2),
            'leave_days' => $leaveDays,
            'present_days' => $presentDays,
            'per_hour_rate' => round($perHourRate, 2),
            'gross_earnings' => round($salary + $allowance, 2),
            'deductions' => round($deductions, 2),
            'loan_deduction' => round($loanDeduction, 2),
            'total_deductions' => round($deductions + $loanDeduction, 2),
            'net_salary' => round($netSalary, 2),
            'salary_type' => $salaryType,
        ];
        
        $this->showBreakdown = true;
    }

    public function saveSalary()
    {
        if (empty($this->salaryBreakdown)) return;

        Salary::updateOrCreate(
            [
                'user_id' => $this->salaryBreakdown['user']->id,
                'salary_month' => $this->salaryBreakdown['salary_month_date'],
            ],
            [
                'basic_salary' => $this->salaryBreakdown['basic_salary'],
                'allowance' => $this->salaryBreakdown['allowance'],
                'deductions' => $this->salaryBreakdown['total_deductions'],
                'net_salary' => $this->salaryBreakdown['net_salary'],
                'total_hours' => $this->salaryBreakdown['total_worked_hours'],
                'salary_type' => $this->salaryBreakdown['salary_type'],
                'payment_status' => 'pending',
                // You can add other fields like bonus, overtime etc. here
            ]
        );
        
        $this->showBreakdown = false;
        $this->loadRecentSalaries();
        // Optionally, add a success notification
    }

    public function cancelSalary()
    {
        $this->showBreakdown = false;
        $this->salaryBreakdown = [];
    }

    public function markPaid($salaryId)
    {
        $salary = Salary::find($salaryId);
        if ($salary) {
            $salary->update(['payment_status' => 'paid']);
            $this->loadRecentSalaries();
        }
    }

    public function prepareAndShowPayslip()
    {
        if (empty($this->salaryBreakdown)) return;
        
        // Create a temporary Salary object to pass to the modal for consistent data structure
        $this->selectedSalary = new Salary($this->salaryBreakdown);
        $this->selectedSalary->user = $this->salaryBreakdown['user']; // Manually attach user object
        $this->selectedSalary->salary_month = \Carbon\Carbon::parse($this->salaryBreakdown['salary_month_date']);
        $this->selectedSalary->salary_id = Salary::orderByDesc('salary_id')->value('salary_id') + 1; // Get the most recent ID and increment for modal

        // dd($this->selectedSalary);

        // Attach userDetail for preview (fixes N/A issue)
        $userDetail = \App\Models\UserDetail::where('user_id', $this->selectedSalary->user->id)->first();
        $this->selectedSalary->user->userDetail = $userDetail;

        $this->dispatch('open-payslip-modal');
    }

    public function render()
    {
        return view('livewire.admin.staff-sallary')->layout($this->layout);
    }
}
