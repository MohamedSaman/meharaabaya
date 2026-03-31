<div class="container" style="max-width: 100%;">
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="fw-bolder mb-4 d-flex align-items-center text-primary-emphasis">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                    class="bi bi-calculator-fill me-3" viewBox="0 0 16 16">
                    <path
                        d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm2 .5v2a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5v-2a.5.5 0 0 0-.5-.5h-7a.5.5 0 0 0-.5.5m0 4v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5m0 3v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5m0 3v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5m3-6v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5m0 3v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5m0 3v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5m3-6v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5m0 3v4a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-4a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5" />
                </svg>
                Salary Calculation
            </h2>
            <form wire:submit.prevent="generateSalary" class="card p-4 border-0 shadow-sm rounded-4 mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Employee</label>
                        <select wire:model.live="selectedUser" class="form-select form-select-lg">
                            <option value="">Select Employee...</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedUser') <span class="text-danger mt-1 small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Month</label>
                        <select wire:model="selectedMonth" class="form-select form-select-lg">
                            @for($m=1; $m<=12; $m++) <option value="{{ sprintf('%02d', $m) }}">{{ date('F',
                                mktime(0,0,0,$m,1)) }}</option>
                                @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Year</label>
                        <select wire:model="selectedYear" class="form-select form-select-lg">
                            @for($y = date('Y')-2; $y <= date('Y'); $y++) <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                        </select>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit"
                            class="btn btn-primary btn-lg fw-semibold d-flex align-items-center justify-content-center gap-2">
                            <span wire:loading.remove wire:target="generateSalary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                        d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z" />
                                    <path
                                        d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466" />
                                </svg>
                                Generate
                            </span>
                            <span wire:loading wire:target="generateSalary" class="spinner-border spinner-border-sm"
                                role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
                @error('form') <div class="text-danger mt-2 small">{{ $message }}</div> @enderror
            </form>
        </div>
    </div>

    <x-salary.breakdown :salaryBreakdown="$salaryBreakdown" :errors="$errors" />

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3 text-primary-emphasis">Recent Generated Salaries</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3">User</th>
                                    <th class="py-3">Month</th>
                                    <th class="py-3">Net Salary</th>
                                    <th class="py-3">Status</th>
                                    <th class="py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSalaries as $salary)
                                <tr>
                                    <td class="fw-semibold">{{ $salary->user->name ?? '' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($salary->salary_month)->format('F Y') }}</td>
                                    <td class="fw-semibold">LKR {{ number_format($salary->net_salary,2) }}</td>
                                    <td>
                                        <span
                                            class="badge rounded-pill fs-6 fw-semibold {{ $salary->payment_status === 'paid' ? 'text-bg-success' : 'text-bg-warning' }}">{{
                                            ucfirst($salary->payment_status) }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button onclick="showPayslipModal({{ $salary->salary_id }})"
                                                class="btn btn-outline-secondary btn-sm fw-semibold">View Slip</button>
                                            @if($salary->payment_status !== 'paid')
                                            <button wire:click="markPaid({{ $salary->salary_id }})"
                                                class="btn btn-success btn-sm fw-semibold">Mark Paid</button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No recent salaries found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-salary.payslip-modal :selectedSalary="$selectedSalary" :errors="$errors" />

    @push('scripts')
    <script>
        function showPayslipModal(salaryId) {
            // Ask Livewire to fetch the data and prepare the modal
            window.Livewire.dispatch('showPayslip', { salaryId: salaryId });
        }
        
        // Listen for the event dispatched by the component
        window.addEventListener('open-payslip-modal', event => {
            var payslipModal = new bootstrap.Modal(document.getElementById('payslip-modal'));
            payslipModal.show();
        });

        function printPayslipContent() {
            const content = document.getElementById('payslipContent').innerHTML;
            const printWindow = window.open('', '', 'height=800,width=800');
            printWindow.document.write('<html><head><title>Salary Payslip</title>');
            printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">');
            printWindow.document.write('<style>body { padding: 1.5rem; -webkit-print-color-adjust: exact; } .payslip-container { font-size: 0.9rem; } .table-sm { font-size: 0.85rem; }</style>');
            printWindow.document.write('</head><body onload="window.print(); window.close()">');
            printWindow.document.write(content);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
        }
        
    function downloadPayslip(filename) {
    const content = document.getElementById('payslipContent').innerHTML;
    const blob = new Blob([
        '<html><head><title>Salary Payslip</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{padding:1.5rem;}</style></head><body>' + content + '</body></html>'
    ], { type: 'text/html' });
    
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = filename; // Use the filename passed from the button
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
    </script>
    @endpush
</div>
