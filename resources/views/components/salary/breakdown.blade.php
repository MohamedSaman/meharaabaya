@props(['salaryBreakdown', 'errors' => null])
@if(!empty($salaryBreakdown))
    <div class="row mb-5">
        <div class="col-12">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-primary text-white rounded-top-4 p-3">
                    <h5 class="mb-0 fw-bold">Salary Breakdown for {{ $salaryBreakdown['user']->name ?? 'N/A' }} ({{ $salaryBreakdown['month'] ?? 'N/A' }})</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0"><span>Basic Salary</span> <span class="fw-semibold fs-6">LKR {{ number_format($salaryBreakdown['basic_salary'] ?? 0,2) }}</span></li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0"><span>Allowance</span> <span class="fw-semibold fs-6">LKR {{ number_format($salaryBreakdown['allowance'] ?? 0,2) }}</span></li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0"><span>Gross Earnings</span> <span class="fw-bold fs-6">LKR {{ number_format($salaryBreakdown['gross_earnings'] ?? 0,2) }}</span></li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0"><span>Worked Days</span> <span class="fw-semibold fs-6">{{ $salaryBreakdown['present_days'] ?? 'N/A' }}</span></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 text-danger"><span>Deductions</span> <span class="fw-semibold fs-6">(LKR {{ number_format($salaryBreakdown['deductions'] ?? 0,2) }})</span></li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 text-danger"><span>Loan Deduction</span> <span class="fw-semibold fs-6">(LKR {{ number_format($salaryBreakdown['loan_deduction'] ?? 0,2) }})</span></li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 text-danger"><span>Total Deductions</span> <span class="fw-bold fs-6">(LKR {{ number_format($salaryBreakdown['total_deductions'] ?? 0,2) }})</span></li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 text-danger"><span>Leave Days</span> <span class="fw-bold fs-6">{{ $salaryBreakdown['leave_days'] ?? 'N/A' }}</span></li>
                            </ul>
                        </div>
                    </div>
                    <hr class="my-4">
                    <div class="p-3 bg-success-subtle text-success-emphasis rounded-3 d-flex justify-content-between align-items-center">
                        <h4 class="fw-bolder mb-0">Net Salary</h4>
                        <h4 class="fw-bolder mb-0">LKR {{ number_format($salaryBreakdown['net_salary'] ?? 0,2) }}</h4>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button wire:click="prepareAndShowPayslip" class="btn btn-outline-success fw-semibold">Print Slip</button>
                        <button wire:click="saveSalary" class="btn btn-primary fw-semibold">Confirm & Save</button>
                        <button wire:click="cancelSalary" class="btn btn-secondary">Cancel</button>
                    </div>
                    @if($errors && $errors->has('form'))
                        <div class="text-danger mt-2 small">{{ $errors->first('form') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
