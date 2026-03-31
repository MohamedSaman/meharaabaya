<div>
    <div class="container-fluid py-4">
        <div class="d-flex align-items-center mb-5">
            <div>
                <h2 class="fw-bolder display-6 mb-1 text-dark">Loan Management</h2>
                <p class="text-muted mb-0">Manage and track employee loans and repayments</p>
            </div>
        </div>

        <div class="card p-4 p-md-5 mb-5 shadow-lg border-light rounded-4">
            <h3 class="h3 fw-bold mb-4 text-primary d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-plus-circle-fill me-2" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/>
                </svg>
                Add New Loan
            </h3>
            
            @if (session()->has('message'))
                <div class="alert alert-success rounded-3 px-4 py-3">{{ session('message') }}</div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger rounded-3 px-4 py-3">{{ session('error') }}</div>
            @endif
            
            <form wire:submit.prevent="addLoan" class="needs-validation" novalidate>
                <div class="row g-4">
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Select Employee</label>
                        <select wire:model="form.employee_id" class="form-select p-3 rounded-3 shadow-sm">
                            <option value="">Select Employee</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                        @error('form.employee_id') <div class="text-danger mt-1 small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Loan Amount (LKR)</label>
                        <input type="number" wire:model="form.loan_amount" class="form-control p-3 rounded-3 shadow-sm" placeholder="Enter loan amount">
                        @error('form.loan_amount') <div class="text-danger mt-1 small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Interest Rate (%)</label>
                        <input type="number" step="0.01" wire:model="form.interest_rate" class="form-control p-3 rounded-3 shadow-sm" placeholder="Enter interest rate">
                        @error('form.interest_rate') <div class="text-danger mt-1 small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Start Date</label>
                        <input type="date" wire:model="form.start_date" class="form-control p-3 rounded-3 shadow-sm">
                        @error('form.start_date') <div class="text-danger mt-1 small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Term (Months)</label>
                        <input type="number" wire:model="form.term_month" class="form-control p-3 rounded-3 shadow-sm" placeholder="Enter term in months">
                        @error('form.term_month') <div class="text-danger mt-1 small">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="mt-5 d-flex gap-3">
                    <button type="submit" class="btn btn-primary btn-lg px-4 fw-semibold rounded-3 shadow">Add Loan</button>
                    <button type="button" wire:click="resetForm" class="btn btn-outline-secondary btn-lg px-4 fw-semibold rounded-3">Reset</button>
                </div>
            </form>
        </div>

        @if ($loanBreakdown)
            <div class="card p-4 p-md-5 mb-5 shadow-lg border-light rounded-4">
                <h3 class="h3 fw-bold mb-4 text-primary">Loan Breakdown</h3>
                <div class="row g-4">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">Employee <span>{{ $employees->firstWhere('emp_id', $loanBreakdown['employee_id'])->fname ?? 'Unknown' }}</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">Loan Amount <span>LKR {{ number_format($loanBreakdown['loan_amount'], 2) }}</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">Interest Rate <span>{{ number_format($loanBreakdown['interest_rate'], 2) }}%</span></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">Start Date <span>{{ \Carbon\Carbon::parse($loanBreakdown['start_date'])->format('F d, Y') }}</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">Term (Months) <span>{{ $loanBreakdown['term_month'] }}</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">Monthly Payment <span>LKR {{ number_format($loanBreakdown['monthly_payment'], 2) }}</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center fw-bold">Remaining Balance <span>LKR {{ number_format($loanBreakdown['remaining_balance'], 2) }}</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="card p-4 p-md-5 shadow-lg border-light rounded-4">
            <h3 class="h3 fw-bold mb-4 text-primary">Loan Records</h3>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="py-3">Employee</th>
                            <th scope="col" class="py-3">Loan Amount</th>
                            <th scope="col" class="py-3">Interest Rate</th>
                            <th scope="col" class="py-3">Start Date</th>
                            <th scope="col" class="py-3">Term</th>
                            <th scope="col" class="py-3">Monthly Payment</th>
                            <th scope="col" class="py-3">Remaining</th>
                            <th scope="col" class="py-3">Status</th>
                            <th scope="col" class="py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($loans as $loan)
                            <tr class="table-row-hover">
                                <td class="fw-medium">{{ $loan->employee ? $loan->employee->fname : 'Unknown' }}</td>
                                <td>LKR {{ number_format($loan->loan_amount, 0) }}</td>
                                <td>{{ number_format($loan->interest_rate, 2) }}%</td>
                                <td>{{ \Carbon\Carbon::parse($loan->start_date)->format('d/m/Y') }}</td>
                                <td>{{ $loan->term_month }}</td>
                                <td>LKR {{ number_format($loan->monthly_payment, 0) }}</td>
                                <td>LKR {{ number_format($loan->remaining_balance, 0) }}</td>
                                <td>
                                    <span class="badge {{ $loan->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }} px-3 py-2 fs-6">{{ ucfirst($loan->status) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button wire:click="showLoanDetails({{ $loan->loan_id }})" class="btn btn-primary btn-sm fw-semibold rounded-3">View</button>
                                        @if ($loan->status === 'active')
                                            <button wire:click="markAsPaid({{ $loan->loan_id }})" class="btn btn-outline-primary btn-sm fw-semibold rounded-3">Mark Paid</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No loan records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="loanDetailsModal" tabindex="-1" aria-labelledby="loanDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content rounded-4 shadow-lg">
                <div class="modal-header bg-light border-bottom-0 rounded-top-4">
                    <h5 class="modal-title h4 text-primary" id="loanDetailsModalLabel">Loan Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 p-md-5">
                    <div id="loan-details-content">
                        <h1 class="h3 fw-bolder text-center my-4 text-primary">Loan Statement</h1>
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
                            <div>
                                <p class="mb-1"><span class="fw-semibold text-dark">Employee:</span> {{ $loanDetails['employee_name'] ?? 'Unknown' }}</p>
                                <p class="mb-1 text-muted">EMP ID: {{ $loanDetails['employee_id'] ?? '' }}</p>
                                <p class="mb-1 text-muted">Designation: {{ $loanDetails['designation'] ?? 'Employee' }}</p>
                            </div>
                            <div class="text-md-end">
                                <p class="mb-1"><span class="fw-semibold text-dark">Loan Start:</span> {{ $loanDetails['start_date'] ?? '' }}</p>
                                <p class="mb-1 text-muted">Loan ID: {{ $loanDetails['loan_id'] ?? '' }}</p>
                            </div>
                        </div>
                        <table class="table table-bordered mb-4 rounded-3 overflow-hidden">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Description</th>
                                    <th scope="col" class="text-end">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>Principal Amount</td><td class="text-end">LKR {{ number_format($loanDetails['loan_amount'] ?? 0, 2) }}</td></tr>
                                <tr><td>Interest Rate</td><td class="text-end">{{ number_format($loanDetails['interest_rate'] ?? 0, 2) }}%</td></tr>
                                <tr><td>Term (Months)</td><td class="text-end">{{ $loanDetails['term_month'] ?? 0 }}</td></tr>
                                <tr><td>Start Date</td><td class="text-end">{{ $loanDetails['start_date_full'] ?? '' }}</td></tr>
                                <tr class="table-primary fw-bold"><td>Monthly Payment</td><td class="text-end">LKR {{ number_format($loanDetails['monthly_payment'] ?? 0, 2) }}</td></tr>
                                <tr class="table-primary fw-bold"><td>Remaining Balance</td><td class="text-end">LKR {{ number_format($loanDetails['remaining_balance'] ?? 0, 2) }}</td></tr>
                            </tbody>
                        </table>
                        <h3 class="h5 fw-semibold mb-2 mt-5 text-primary">Payment History</h3>
                        <table class="table table-bordered mb-2 rounded-3 overflow-hidden">
                            <thead class="table-light">
                                <tr>
                                    <th>Payment Date</th>
                                    <th class="text-end">Amount Paid (LKR)</th>
                                    <th class="text-end">Remaining Balance (LKR)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loanDetails['payment_history'] ?? [] as $payment)
                                    <tr>
                                        <td>{{ $payment['payment_date'] }}</td>
                                        <td class="text-end">{{ number_format($payment['amount_paid'], 2) }}</td>
                                        <td class="text-end">{{ number_format($payment['remaining_balance'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">No payment history available</td></tr>
                                @endforelse
                                @if(!empty($loanDetails['payment_history']))
                                    <tr class="table-success fw-bold">
                                        <td>Total Paid</td>
                                        <td class="text-end">LKR {{ number_format($loanDetails['total_paid'] ?? 0, 2) }}</td>
                                        <td></td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                        <div class="text-muted small mt-5">
                            <p>Printed Date: {{ now()->format('Y-m-d') }} | Time: {{ now()->format('H:i') }}</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 rounded-bottom-4">
                    <button type="button" class="btn btn-secondary fw-semibold rounded-3" data-bs-dismiss="modal">Close</button>
                    <button type="button" onclick="printLoanDetails()" class="btn btn-success fw-semibold rounded-3">Print Loan Details</button>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    <script>
        // Handle Livewire events to show/hide the Bootstrap modal
        window.addEventListener('show-loan-details-modal', event => {
            const loanModal = new bootstrap.Modal(document.getElementById('loanDetailsModal'));
            loanModal.show();
        });

        function printLoanDetails() {
            var printContents = document.getElementById('loan-details-content').innerHTML;
            var printWindow = window.open('', '', 'height=700,width=900');
            printWindow.document.write('<html><head><title>Loan Details</title>');
            // Link to Bootstrap for print styling
            printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">');
            printWindow.document.write('<style>body { padding: 2rem; } .table { margin-top: 1rem; } </style>');
            printWindow.document.write('</head><body onload="window.print(); window.close()">');
            printWindow.document.write(printContents);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
        }
    </script>
    @endpush
</div>

