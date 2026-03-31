<div class="container-fluid py-4" style="background-color:#f5fdf1ff;">
    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 fw-bold" style="color:#3b5b0c;">
                                <i class="bi bi-calendar-check me-2" style="color:#8eb922;"></i>Register Report - {{ \Carbon\Carbon::parse($sessionDate)->format('d/m/Y') }}
                            </h4>
                            <p class="text-muted mb-0">
                                <small>Cashier: {{ $session->user->name ?? 'N/A' }} | Session Date: {{ \Carbon\Carbon::parse($sessionDate)->format('l, F d, Y') }}</small>
                            </p>
                        </div>
                        <button class="btn btn-secondary rounded-0" wire:click="goBack">
                            <i class="bi bi-arrow-left me-1"></i>Back to List
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        {{-- Cash in Hand --}}
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #1e88e5 0%, #42a5f5 100%);">
                <div class="card-body text-white text-center py-4">
                    <h2 class="mb-0 fw-bold">{{ number_format($cashInHand, 2) }}</h2>
                    <p class="mb-0 text-uppercase fw-semibold" style="letter-spacing: 1px;">CASH IN HAND</p>
                </div>
            </div>
        </div>

        {{-- Cash Sales --}}
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f4511e 0%, #ff7043 100%);">
                <div class="card-body text-white text-center py-4">
                    <h2 class="mb-0 fw-bold">{{ number_format($cashSales, 2) }}</h2>
                    <p class="mb-0 text-uppercase fw-semibold" style="letter-spacing: 1px;">CASH SALES</p>
                </div>
            </div>
        </div>

        {{-- Cash Late Payments --}}
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #d81b60 0%, #ec407a 100%);">
                <div class="card-body text-white text-center py-4">
                    <h2 class="mb-0 fw-bold">{{ number_format($lateCashPayments, 2) }}</h2>
                    <p class="mb-0 text-uppercase fw-semibold" style="letter-spacing: 1px;">CASH LATE PAYMENTS</p>
                </div>
            </div>
        </div>
    </div>
    {{-- Second Row of Cards --}}
    <div class="row mb-4">
        {{-- Expenses --}}
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #00acc1 0%, #26c6da 100%);">
                <div class="card-body text-white text-center py-4">
                    <h2 class="mb-0 fw-bold">{{ number_format($expenses, 2) }}</h2>
                    <p class="mb-0 text-uppercase fw-semibold" style="letter-spacing: 1px;">EXPENSES</p>
                </div>
            </div>
        </div>

        {{-- Returns --}}
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #8e24aa 0%, #ab47bc 100%);">
                <div class="card-body text-white text-center py-4">
                    <h2 class="mb-0 fw-bold">{{ number_format($returns, 2) }}</h2>
                    <p class="mb-0 text-uppercase fw-semibold" style="letter-spacing: 1px;">RETURNS</p>
                </div>
            </div>
        </div>

        {{-- Cash Deposit --}}
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #7b1fa2 0%, #9c27b0 100%);">
                <div class="card-body text-white text-center py-4">
                    <h2 class="mb-0 fw-bold">{{ number_format($cashDeposit, 2) }}</h2>
                    <p class="mb-0 text-uppercase fw-semibold" style="letter-spacing: 1px;">CASH DEPOSIT</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Current Cash Card --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #212121 0%, #424242 100%);">
                <div class="card-body text-white text-center py-4">
                    <h1 class="mb-0 fw-bold display-4">{{ number_format($currentCash, 2) }}</h1>
                    <p class="mb-0 text-uppercase fw-bold fs-5" style="letter-spacing: 2px;">CURRENT CASH</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Cash Sales List --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold" style="color:#3b5b0c;">
                        <i class="bi bi-cash-coin me-2" style="color:#8eb922;"></i>💰 CASH SALES
                    </h5>
                </div>
                <div class="card-body p-0 overflow-auto">
                    @if($cashPayments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 80px;">INV. NO</th>
                                    <th style="width: 150px;">PAY. REF.</th>
                                    <th>CUSTOMER NAME</th>
                                    <th class="text-center" style="width: 150px;">INV. DATE</th>
                                    <th class="text-center" style="width: 150px;">PAY. DATE</th>
                                    <th class="text-end" style="width: 120px;">PAID</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cashPayments as $payment)
                                <tr>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $payment->sale->invoice_number ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $payment->payment_reference ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $payment->sale->customer->name ?? 'N/A' }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <small>{{ $payment->sale->created_at ? $payment->sale->created_at->format('d/m/Y h:i A') : 'N/A' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <small>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y h:i A') : 'N/A' }}</small>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">Rs.{{ number_format($payment->amount, 2) }}</strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="5" class="text-end">TOTAL:</th>
                                    <th class="text-end">
                                        <strong class="text-white fs-5">Rs.{{ number_format($cashPayments->sum('amount'), 2) }}</strong>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="card-footer bg-white">
                        {{ $cashPayments->links() }}
                    </div>
                    @else
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox display-4 d-block mb-2"></i>
                        <p>No cash sales found for this day</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .form-control,
    .btn {
        border-radius: 0 !important;
    }

    .table tbody tr {
        border-bottom: 1px solid #e9ecef;
    }

    .table tbody tr:last-child {
        border-bottom: none;
    }

    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>
@endpush
