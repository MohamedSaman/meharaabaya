<div>
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="bi bi-clock-history text-primary me-2"></i> Transaction History
                <span class="text-muted fs-6 ms-2">- {{ $customer->name ?? '' }}</span>
            </h4>
            <a href="{{ route((auth()->user()->role === 'admin' ? 'admin.' : 'staff.') . 'manage-customer') }}" wire:navigate class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Customers
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card shadow-sm border-0 border-start border-4 border-info h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-1" style="font-size:0.8rem;">Total Sales</h6>
                    <h4 class="mb-0 fw-bold">Rs.{{ number_format($transactions->where('type', 'Sale')->sum('debit'), 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card shadow-sm border-0 border-start border-4 border-success h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-1" style="font-size:0.8rem;">Total Payments</h6>
                    <h4 class="mb-0 fw-bold">Rs.{{ number_format($transactions->where('type', 'Payment')->sum('credit'), 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card shadow-sm border-0 border-start border-4 border-warning h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-1" style="font-size:0.8rem;">Total Returns</h6>
                    <h4 class="mb-0 fw-bold">Rs.{{ number_format($transactions->where('type', 'Return')->sum('credit'), 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            @php
            $currentBalance = $transactions->last() ? $transactions->last()['balance'] : 0;
            @endphp
            <div class="card shadow-sm border-0 border-start border-4 {{ $currentBalance > 0 ? 'border-danger' : 'border-primary' }} h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-1" style="font-size:0.8rem;">Closing Balance</h6>
                    <h4 class="mb-0 fw-bold text-{{ $currentBalance > 0 ? 'danger' : 'success' }}">
                        Rs.{{ number_format($currentBalance, 2) }}
                        <small class="fs-6">{{ $currentBalance > 0 ? '(Due)' : ($currentBalance < 0 ? '(Advance)' : '') }}</small>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Excel-like Data Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered mb-0 align-middle" style="font-size: 0.9rem;">
                    <thead class="table-dark">
                        <tr>
                            <th class="py-3 px-3 text-nowrap" style="width: 15%">Date & Time</th>
                            <th class="py-3 px-3">Type</th>
                            <th class="py-3 px-3">Reference / Details</th>
                            <th class="py-3 px-3 text-center" style="width: 10%">Cheque Count</th>
                            <th class="py-3 px-3 text-center" style="width: 8%">Due Days</th>
                            <th class="py-3 px-3 text-end" style="width: 15%">Debit <small class="text-white-50">(Rs.)</small></th>
                            <th class="py-3 px-3 text-end" style="width: 15%">Credit <small class="text-white-50">(Rs.)</small></th>
                            <th class="py-3 px-3 text-end" style="width: 15%">Balance <small class="text-white-50">(Rs.)</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                        <tr>
                            <td class="px-3 text-nowrap text-muted">
                                {{ \Carbon\Carbon::parse($transaction['date'])->format('d/m/Y h:i A') }}
                            </td>
                            <td class="px-3">
                                @if($transaction['type'] == 'Sale')
                                <span class="badge bg-info text-dark w-100">Sale</span>
                                @elseif($transaction['type'] == 'Payment')
                                <span class="badge bg-success w-100">Payment</span>
                                @elseif($transaction['type'] == 'Return')
                                <span class="badge bg-warning text-dark w-100">Return</span>
                                @endif
                            </td>
                            <td class="px-3">
                                <span class="fw-bold text-dark">{{ $transaction['reference'] }}</span><br>
                                <span class="text-muted" style="font-size:0.8rem;">{{ $transaction['details'] }}</span>
                            </td>
                            <td class="px-3 text-center">
                                @if(!is_null($transaction['cheque_count']))
                                <span class="fw-bold text-dark">{{ $transaction['cheque_count'] }}</span>
                                @else
                                -
                                @endif
                            </td>
                            <td class="px-3 text-center">
                                @if(!is_null($transaction['due_days'] ?? null))
                                <span class="badge bg-warning text-dark">{{ $transaction['due_days'] }} days</span>
                                @else
                                -
                                @endif
                            </td>
                            <td class="px-3 text-end text-danger fw-semibold">
                                {{ $transaction['debit'] > 0 ? number_format($transaction['debit'], 2) : '-' }}
                            </td>
                            <td class="px-3 text-end text-success fw-semibold">
                                {{ $transaction['credit'] > 0 ? number_format($transaction['credit'], 2) : '-' }}
                            </td>
                            <td class="px-3 text-end fw-bold {{ $transaction['balance'] > 0 ? 'text-danger' : 'text-success' }}" style="background-color: rgba(0,0,0,0.02)">
                                {{ number_format($transaction['balance'], 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3 text-black-50"></i>
                                <h5>No transactions found</h5>
                                <p class="mb-0">There are no sales, payments, or returns for this customer yet.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold border-top-2">
                        <tr>
                            <td colspan="5" class="text-end py-3">Totals:</td>
                            <td class="text-end text-danger py-3">Rs.{{ number_format($transactions->sum('debit'), 2) }}</td>
                            <td class="text-end text-success py-3">Rs.{{ number_format($transactions->sum('credit'), 2) }}</td>
                            <td class="text-end py-3 text-{{ $currentBalance > 0 ? 'danger' : 'success' }}">Rs.{{ number_format($currentBalance, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>