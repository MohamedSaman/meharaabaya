<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-graph-up-arrow text-success me-2"></i> Day Summary Report
            </h3>
            <p class="text-muted mb-0">Track and manage your company revenue efficiently</p>
        </div>
        <div>
            {{--<button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addCashInHandModal">
                <i class="bi bi-wallet2 me-2"></i> Add Cash-in-Hand
            </button>--}}
            <!-- <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
                <i class="bi bi-plus-lg me-1"></i> Add & Deposit
            </button> -->
            <button class="btn btn-primary">
                <i class="bi bi-download me-2"></i> Export Report
            </button>
        </div>
    </div>

    <!-- Main Income Metrics Cards - First Row -->
    <div class="row mb-0">
        <!-- Cash in Hand Card - Blue -->
        <div class="col-xl-3 col-md-6 mb-2">
            <div class="card gradient-card" style="background: linear-gradient(135deg, #1e88e5 0%, #42a5f5 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="text-white-50 mb-0" style="font-size: 0.85rem; font-weight: 500; letter-spacing: 1px;">💰 OPENING CASH</h6>
                    </div>
                    <h2 class="fw-bold mb-0" ">Rs. {{ number_format($openingCash, 2) }}</h2>
                </div>
            </div>
        </div>

        <!-- Cash Sales Card - Orange/Red -->
        <div class=" col-xl-3 col-md-6 mb-2">
                        <div class="card gradient-card" style="background: linear-gradient(135deg, #f4511e 0%, #ff7043 100%);">
                            <div class="card-body text-white">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="text-white-50 mb-0" style="font-size: 0.85rem; font-weight: 500; letter-spacing: 1px;">💵 CASH SALES</h6>
                                </div>
                                <h2 class="fw-bold mb-0" ">Rs. {{ number_format($cashIncome, 2) }}</h2>
                </div>
            </div>
        </div>

        <!-- Cash Late Payments Card - Pink -->
        <div class=" col-xl-3 col-md-6 mb-2">
                                    <div class="card gradient-card" style="background: linear-gradient(135deg, #d81b60 0%, #ec407a 100%);">
                                        <div class="card-body text-white">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="text-white-50 mb-0" style="font-size: 0.85rem; font-weight: 500; letter-spacing: 1px;">⏰ CHEQUE PAYMENTS</h6>
                                            </div>
                                            <h2 class="fw-bold mb-0" ">Rs. {{ number_format($chequeIncome, 2) }}</h2>
                </div>
            </div>
        </div>

        <!-- Cash Late Payments Card - Cyan -->
        <div class=" col-xl-3 col-md-6 mb-2">
                                                <div class="card gradient-card" style="background: linear-gradient(135deg, #00acc1 0%, #26c6da 100%);">
                                                    <div class="card-body text-white">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <h6 class="text-white-50 mb-0" style="font-size: 0.85rem; font-weight: 500; letter-spacing: 1px;">💰 CASH LATE PAYMENT</h6>
                                                        </div>
                                                        <h2 class="fw-bold mb-0" ">Rs. {{ number_format($cashLatePayments, 2) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row -->
    <div class=" row mb-0">
                                                            <!-- Today's Deposits Card - Dark Purple -->
                                                            <div class="col-xl-3 col-md-6 mb-2">
                                                                <div class="card gradient-card" style="background: linear-gradient(135deg, #7b1fa2 0%, #9c27b0 100%);">
                                                                    <div class="card-body text-white">
                                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                                            <h6 class="text-white-50 mb-0" style="font-size: 0.85rem; font-weight: 500; letter-spacing: 1px;">� TODAY DEPOSITS</h6>
                                                                        </div>
                                                                        <h2 class="fw-bold mb-0">Rs. {{ number_format($todayDeposits, 2) }}</h2>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Expenses Card - Orange -->
                                                            <div class="col-xl-3 col-md-6 mb-2">
                                                                <div class="card gradient-card" style="background: linear-gradient(135deg, #f57c00 0%, #ff9800 100%);">
                                                                    <div class="card-body text-white">
                                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                                            <h6 class="text-white-50 mb-0" style="font-size: 0.85rem; font-weight: 500; letter-spacing: 1px;">� EXPENSES</h6>
                                                                        </div>
                                                                        <h2 class="fw-bold mb-0">Rs. {{ number_format($todayExpenses, 2) }}</h2>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Return Amount Card - Red -->
                                                            <div class="col-xl-3 col-md-6 mb-2">
                                                                <div class="card gradient-card" style="background: linear-gradient(135deg, #c62828 0%, #e53935 100%);">
                                                                    <div class="card-body text-white">
                                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                                            <h6 class="text-white-50 mb-0" style="font-size: 0.85rem; font-weight: 500; letter-spacing: 1px;">↩️ RETURN AMOUNT</h6>
                                                                        </div>
                                                                        <h2 class="fw-bold mb-0">Rs. {{ number_format($todayReturns, 2) }}</h2>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Current Total Cash Card - Dark Gray -->
                                                            <div class="col-xl-3 col-md-6 mb-2">
                                                                <div class="card gradient-card" style="background: linear-gradient(135deg, #212121 0%, #424242 100%);">
                                                                    <div class="card-body text-white">
                                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                                            <h6 class="text-white-50 mb-0" style="font-size: 0.85rem; font-weight: 500; letter-spacing: 1px;">💎 CURRENT TOTAL CASH</h6>
                                                                        </div>
                                                                        <h2 class="fw-bold mb-0">Rs. {{ number_format(($openingCash + $cashIncome + $cashLatePayments) - ($todayReturns + $todayDeposits+$todayExpenses + $supplierPayment), 2) }}</h2>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                    </div>

                                                    <!-- Cash Sales List Table -->
                                                    <div class="card">
                                                        <div class="card-header" style="background: linear-gradient(135deg, #198754 0%, #20c997 100%);">
                                                            <h5 class="fw-bold text-white mb-0">
                                                                <i class="bi bi-cash-coin me-2"></i>💰 CASH SALES - TODAY
                                                            </h5>
                                                        </div>
                                                        <div class="card-body p-0 overflow-auto">
                                                            <div class="table-responsive">
                                                                <table class="table table-hover mb-0">
                                                                    <thead style="background: #f8f9fa;">
                                                                        <tr>
                                                                            <th class="ps-4">INV.NO</th>
                                                                            <th>PAY.REF</th>
                                                                            <th>CUSTOMER NAME</th>
                                                                            <th>INV.DATE</th>
                                                                            <th>PAY.DATE</th>
                                                                            <th class="text-end pe-4">PAID</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @forelse($cashPayments as $payment)
                                                                        <tr>
                                                                            <td class="ps-4">
                                                                                <span class="badge bg-primary">{{ $payment->sale->invoice_number ?? 'N/A' }}</span>
                                                                            </td>
                                                                            <td>{{ $payment->payment_reference ?? '-' }}</td>
                                                                            <td>{{ $payment->sale->customer->name ?? 'Walk-in Customer' }}</td>
                                                                            <td>{{ $payment->sale->invoice_date ?? '-' }}</td>
                                                                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d H:i') }}</td>
                                                                            <td class="text-end pe-4">
                                                                                <span class="fw-bold text-success">Rs. {{ number_format($payment->amount, 2) }}</span>
                                                                            </td>
                                                                        </tr>
                                                                        @empty
                                                                        <tr>
                                                                            <td colspan="6" class="text-center text-muted py-4">
                                                                                <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                                                                <p class="mb-0">No cash sales today</p>
                                                                            </td>
                                                                        </tr>
                                                                        @endforelse
                                                                    </tbody>
                                                                    @if($cashPayments->count() > 0)
                                                                    <tfoot style="background: #f8f9fa;">
                                                                        <tr>
                                                                            <td colspan="5" class="ps-4 fw-bold">TOTAL CASH SALES:</td>
                                                                            <td class="text-end pe-4 fw-bold text-success">
                                                                                Rs. {{ number_format($cashPayments->sum('amount'), 2) }}
                                                                            </td>
                                                                        </tr>
                                                                    </tfoot>
                                                                    @endif
                                                                </table>
                                                            </div>
                                                            @if($cashPayments->hasPages())
                                                            <div class="card-footer bg-light">
                                                                <div class="d-flex justify-content-center">
                                                                    {{ $cashPayments->links() }}
                                                                </div>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <!-- Daily Income & Cash Deposit Section -->
                                                    {{-- <div class="card h-100 mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-dark mb-1">
                    <i class="bi bi-calendar2-day text-success me-2"></i> Daily Income Entries
                </h5>
                <p class="text-muted small mb-0">Record daily sales and automatically deposit cash into Cash in Hand balance</p>
            </div>

        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Source/Description</th>
                            <th class="text-end">Amount (Rs.)</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deposits as $deposit)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium text-dark">{{ $deposit->date->format('Y-m-d') }}</span>
                                                    </td>
                                                    <td>{{ $deposit->description ?? '—' }}</td>
                                                    <td class="text-end">
                                                        <span class="fw-bold text-success">{{ number_format($deposit->amount, 2) }}</span>
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        <button class="text-danger  me-2 bg-opacity-0 border-0" wire:click="deleteDeposit({{ $deposit->id }})">
                                                            <i class="bi bi-trash fs-6"></i>
                                                        </button>
                                                    </td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-3">No deposits recorded yet.</td>
                                                    </tr>
                                                    @endforelse
                                                    </tbody>

                                                    </table>
                                                </div>
                                        </div>
                                    </div> --}}


                                    <!-- Modal for Adding Daily Income -->
                                    <div class="modal fade" id="addIncomeModal" tabindex="-1" aria-labelledby="addIncomeModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">
                                                        <i class="bi bi-plus-circle text-white me-2"></i> Add & Deposit Daily Income
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form wire:submit.prevent="addDeposit">
                                                    <div class="modal-body">

                                                        <!-- Cash Summary Section -->
                                                        <div class="p-3 mb-4 bg-light rounded-3">
                                                            <h6 class="fw-bold text-dark mb-3">
                                                                <i class="bi bi-wallet2 text-success me-2"></i> Current Cash Summary
                                                            </h6>
                                                            <div class="d-flex justify-content-between small mb-2">
                                                                <span class="text-muted">Opening Cash (POS):</span>
                                                                <span class="fw-semibold text-dark">Rs. {{ number_format($openingCash, 2) }}</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between small mb-2">
                                                                <span class="text-muted">Today Cash Sales:</span>
                                                                <span class="fw-semibold text-primary">Rs. {{ number_format($cashIncome, 2) }}</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between small mb-2">
                                                                <span class="text-muted">Today Returns:</span>
                                                                <span class="fw-semibold text-danger">Rs. {{ number_format($todayReturns, 2) }}</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between small mb-2">
                                                                <span class="text-muted">Today's Deposit Amount:</span>
                                                                <span class="fw-semibold text-info">Rs. {{ number_format($todayDeposits, 2) }}</span>
                                                            </div>
                                                            <hr>
                                                            <div class="d-flex justify-content-between small mb-2">
                                                                <span class="text-muted fw-bold">Current Total Cash:</span>
                                                                <span class="fw-bold text-success">Rs. {{ number_format($openingCash + $cashIncome - $todayReturns - $todayDeposits, 2) }}</span>
                                                            </div>
                                                        </div>

                                                        <!-- Date & Amount in One Row -->
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-semibold">Date</label>
                                                                <input type="date" class="form-control" wire:model="depositDate">
                                                                @error('depositDate') <span class="text-danger small">{{ $message }}</span> @enderror
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-semibold">Amount (Rs.)</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light">Rs.</span>
                                                                    <input type="number" step="0.01" class="form-control" wire:model="depositAmount" placeholder="0">
                                                                </div>
                                                                @error('depositAmount') <span class="text-danger small">{{ $message }}</span> @enderror
                                                            </div>
                                                        </div>

                                                        <!-- Description Field -->
                                                        <div class="mb-4">
                                                            <label class="form-label fw-semibold">Description / Source</label>
                                                            <input type="text" class="form-control" wire:model="depositDescription" placeholder="e.g., POS Sales / Invoice #125">
                                                            @error('depositDescription') <span class="text-danger small">{{ $message }}</span> @enderror
                                                        </div>

                                                        <div class="alert alert-info bg-info bg-opacity-10 border-0 small" role="alert">
                                                            <i class="bi bi-info-circle me-1"></i>
                                                            This income will be added to today's records and automatically deposited into Cash in Hand.
                                                        </div>

                                                    </div>
                                                    <div class="modal-footer border-0">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="bi bi-check2-circle me-1"></i> Record & Deposit
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal for Adding Cash-in-Hand -->
                                    <div class="modal fade" id="addCashInHandModal" tabindex="-1" aria-labelledby="addCashInHandModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">
                                                        <i class="bi bi-wallet2 text-warning me-2"></i> Add Cash-in-Hand
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form wire:submit.prevent="updateCashInHand">
                                                    <div class="modal-body">

                                                        <!-- Current Cash Status -->
                                                        <div class="p-3 mb-4 bg-warning bg-opacity-10 rounded-3 border border-warning border-opacity-25">
                                                            <h6 class="fw-bold text-dark mb-3">
                                                                <i class="bi bi-info-circle text-warning me-2"></i> Current Cash Status
                                                            </h6>
                                                            <div class="d-flex justify-content-between small mb-2">
                                                                <span class="text-muted">Opening Cash (POS):</span>
                                                                <span class="fw-semibold text-dark">Rs. {{ number_format($openingCash, 2) }}</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between small mb-2">
                                                                <span class="text-muted">Today's Cash Sales:</span>
                                                                <span class="fw-semibold text-primary">Rs. {{ number_format($cashIncome, 2) }}</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between small mb-2">
                                                                <span class="text-muted">Today's Returns:</span>
                                                                <span class="fw-semibold text-danger">Rs. {{ number_format($todayReturns, 2) }}</span>
                                                            </div>
                                                            <hr>
                                                            <div class="d-flex justify-content-between small">
                                                                <span class="text-muted fw-bold">Current Total:</span>
                                                                <span class="fw-semibold text-success">Rs. {{ number_format($openingCash + $cashIncome - $todayReturns, 2) }}</span>
                                                            </div>
                                                        </div>

                                                        <!-- Amount Input -->
                                                        <div class="mb-4">
                                                            <label class="form-label fw-semibold">Add Cash Amount (Rs.)</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text bg-light">Rs.</span>
                                                                <input type="number" step="0.01" class="form-control"
                                                                    placeholder="Enter amount to add" wire:model="newCashInHand">
                                                            </div>
                                                            <div class="form-text text-muted small">
                                                                Enter the amount you want to add to your base cash-in-hand.
                                                            </div>
                                                        </div>



                                                        <!-- New Total Preview -->
                                                        <div class="p-3 bg-success bg-opacity-10 rounded-3 border border-success border-opacity-25">
                                                            <h6 class="fw-bold text-dark mb-2">
                                                                <i class="bi bi-calculator text-success me-2"></i> New Total Preview
                                                            </h6>
                                                            <div class="d-flex justify-content-between small">
                                                                <span class="text-muted">New Base Cash:</span>
                                                                <span class="fw-semibold text-success">Rs. {{ number_format($cashInHand, 2) }}</span>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <div class="modal-footer border-0">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-warning text-white">
                                                            <i class="bi bi-check2-circle me-1"></i> Update Cash-in-Hand
                                                        </button>

                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                            </div>

                            @push('styles')
                            <style>
                                .gradient-card {
                                    border: none;
                                    border-radius: 15px;
                                    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
                                    transition: all 0.3s ease;
                                    overflow: hidden;
                                }

                                .gradient-card:hover {
                                    transform: translateY(-5px);
                                    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.25);
                                }

                                .gradient-card .card-body {
                                    padding: 1.75rem;
                                }

                                .gradient-card h2 {
                                    font-size: 1.5rem;
                                    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                }

                                .summary-card {
                                    border-left: 4px solid;
                                    transition: all 0.3s ease;
                                    border: none;
                                    border-radius: 12px;
                                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                                }

                                .summary-card:hover {
                                    transform: translateY(-3px);
                                    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
                                }

                                .summary-card.today {
                                    border-left-color: #198754;
                                }

                                .summary-card.total {
                                    border-left-color: #4361ee;
                                }

                                .summary-card.cash {
                                    border-left-color: #ffc107;
                                }

                                .icon-container {
                                    width: 50px;
                                    height: 50px;
                                    border-radius: 12px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                }

                                .card {
                                    border: none;
                                    border-radius: 12px;
                                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                                    transition: all 0.3s ease;
                                }

                                .card:hover {
                                    transform: translateY(-5px);
                                    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
                                }

                                .card-header {
                                    background-color: white;
                                    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
                                    border-radius: 12px 12px 0 0 !important;
                                    padding: 1.25rem 1.5rem;
                                }

                                .table th {
                                    border-top: none;
                                    font-weight: 600;
                                    color: #fff;
                                    font-size: 0.75rem;
                                    text-transform: uppercase;
                                    letter-spacing: 0.5px;
                                    padding: 1rem 0.75rem;
                                }

                                .card-header h5 {
                                    margin: 0;
                                    font-size: 1.1rem;
                                    letter-spacing: 0.5px;
                                }

                                .table td {
                                    vertical-align: middle;
                                    padding: 1rem 0.75rem;
                                }

                                .btn-link {
                                    text-decoration: none;
                                    transition: all 0.2s ease;
                                }

                                .btn-link:hover {
                                    transform: scale(1.1);
                                }

                                .modal-content {
                                    border: none;
                                    border-radius: 12px;
                                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
                                }

                                .form-control,
                                .form-select {
                                    border-radius: 8px;
                                    padding: 0.75rem 1rem;
                                    border: 1px solid #e2e8f0;
                                }

                                .form-control:focus,
                                .form-select:focus {
                                    box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.15);
                                    border-color: #198754;
                                }

                                .btn {
                                    border-radius: 8px;
                                    font-weight: 500;
                                    padding: 0.75rem 1.5rem;
                                    transition: all 0.3s ease;
                                }

                                .btn-success {
                                    background-color: #198754;
                                    border-color: #198754;
                                }

                                .btn-success:hover {
                                    background-color: #157347;
                                    border-color: #157347;
                                    transform: translateY(-2px);
                                }

                                .btn-warning {
                                    background-color: #ffc107;
                                    border-color: #ffc107;
                                }

                                .btn-warning:hover {
                                    background-color: #ffca2c;
                                    border-color: #ffca2c;
                                    transform: translateY(-2px);
                                }
                            </style>
                            @endpush
                            @push('scripts')
                            <script>
                                document.addEventListener('livewire:init', () => {
                                    Livewire.on('close-modal', (event) => {
                                        const modalId = event.modalId || event; // handle both old/new formats
                                        const modalEl = document.getElementById(modalId);
                                        if (modalEl) {
                                            const modalInstance = bootstrap.Modal.getInstance(modalEl);
                                            if (modalInstance) {
                                                modalInstance.hide();
                                            }
                                        }
                                    });
                                });

                                Livewire.on('refreshPage', () => {
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1500); // Refresh after 1.5 seconds to show success message
                                });

                                // Auto-hide success alert after 3 seconds
                                document.addEventListener('livewire:navigated', () => {
                                    const alert = document.querySelector('.alert-success');
                                    if (alert) {
                                        setTimeout(() => {
                                            alert.classList.remove('show');
                                            alert.remove();
                                        }, 3000);
                                    }
                                });
                            </script>
                            @endpush
