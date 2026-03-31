<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-receipt text-success me-2"></i> Supplier Payment List
            </h3>
            <p class="text-muted mb-0">View all supplier receipts and payment allocations</p>
        </div>
    </div>

    {{-- Search Order Section --}}
    <div class="card mb-4 border-primary border-2">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-8">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-primary text-white">
                            <i class="bi bi-search"></i>
                        </span>
                        <input 
                            type="text" 
                            class="form-control" 
                            placeholder="Search by order number..." 
                            wire:model.live="searchOrderNumber"
                        >
                        @if($searchOrderNumber)
                        <button 
                            class="btn btn-outline-secondary" 
                            type="button"
                            wire:click="clearSearch"
                        >
                            <i class="bi bi-x-circle me-1"></i> Clear
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Order Search Results --}}
    @if($searchOrderNumber && $searchedOrder)
    <div class="card mb-4 border-success border-2">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="bi bi-check-circle me-2"></i> Order Details - {{ $searchedOrder->order_code }}
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <small class="text-muted d-block">Order Code</small>
                    <strong class="text-primary">{{ $searchedOrder->order_code }}</strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Order Date</small>
                    <strong>{{ $searchedOrder->order_date ? date('M d, Y', strtotime($searchedOrder->order_date)) : '-' }}</strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Total Amount</small>
                    <strong>Rs.{{ number_format($searchedOrder->total_amount ?? 0, 2) }}</strong>
                </div>
                
            </div>
            <hr>
            <div class="mb-3">
                <small class="text-muted d-block mb-1">Supplier</small>
                <h5 class="mb-0 text-dark">
                    <i class="bi bi-building me-2"></i>{{ $searchedOrder->supplier->name }}
                </h5>
            </div>

            {{-- Payment Details for Order --}}
            <div class="mt-4">
                <h6 class="fw-bold mb-3 text-primary">
                    <i class="bi bi-receipt-cutoff me-2"></i> Payment Details
                </h6>

                @if(count($orderPayments) > 0)
                    @foreach($orderPayments as $index => $payment)
                    <div class="card mb-3 shadow-sm border-start border-4 border-success">
                        <div class="card-header bg-light">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <strong>#{{ $index + 1 }}</strong>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Receipt #{{ $payment->id }}</h6>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                {{ $payment->payment_date ? date('M d, Y', strtotime($payment->payment_date)) : '-' }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="fs-4 fw-bold text-success">Rs.{{ number_format($payment->amount, 2) }}</div>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-{{ $payment->payment_method === 'cash' ? 'cash' : ($payment->payment_method === 'cheque' ? 'receipt' : 'bank') }} me-1"></i>
                                        {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($payment->payment_method === 'cheque' && $payment->cheque_number)
                            <div class="alert alert-info mb-3">
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Cheque Number</small>
                                        <strong>{{ $payment->cheque_number }}</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Bank Name</small>
                                        <strong>{{ $payment->bank_name ?? '-' }}</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Cheque Date</small>
                                        <strong>{{ $payment->cheque_date ? date('M d, Y', strtotime($payment->cheque_date)) : '-' }}</strong>
                                    </div>
                                </div>
                            </div>
                            @elseif($payment->payment_method === 'bank_transfer')
                            <div class="alert alert-info mb-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Bank Name</small>
                                        <strong>{{ $payment->bank_name ?? '-' }}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Reference</small>
                                        <strong>{{ $payment->bank_transaction ?? $payment->reference ?? '-' }}</strong>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($payment->allocations && count($payment->allocations) > 0)
                            <div class="table-responsive allocation-table">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Order Code</th>
                                            <th class="text-end">Allocated Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($payment->allocations->where('purchase_order_id', $searchedOrder->id) as $alloc)
                                        <tr>
                                            <td>{{ $searchedOrder->order_code }}</td>
                                            <td class="text-end fw-bold text-success">Rs.{{ number_format($alloc->allocated_amount, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif

                            @if($payment->notes)
                            <div class="mt-3">
                                <small class="text-muted d-block">Notes:</small>
                                <div class="alert alert-light mb-0">{{ $payment->notes }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach

                    <div class="alert alert-success">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Total Payments: </strong>{{ count($orderPayments) }}
                            </div>
                            <div class="col-md-6 text-end">
                                <strong>Total Amount Paid: </strong>Rs.{{ number_format(collect($orderPayments)->sum('amount'), 2) }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>No payments yet</strong>
                        <p class="mb-0 mt-2">This order has not received any payments yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @elseif($searchOrderNumber && !$searchedOrder)
    <div class="alert alert-danger">
        <i class="bi bi-x-circle me-2"></i>
        <strong>Order not found!</strong> No order found with code: "{{ $searchOrderNumber }}"
    </div>
    @endif

    {{-- Supplier List Table - Only show when not searching --}}
    @if(!$searchOrderNumber)
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center bg-light">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-people me-2"></i> Suppliers with Payments
            </h5>
            <span class="badge bg-primary">{{ $suppliers->total() }} suppliers</span>
        </div>
        <div class="card-body p-0 overflow-auto">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Supplier Name</th>
                            <th class="text-center">Total Paid</th>
                            <th class="text-center">No. of Receipts</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                        <tr wire:key="supplier-{{ $supplier->id }}" style="cursor:pointer" wire:click="showSupplierPayments({{ $supplier->id }})">
                            <td class="ps-4 fw-semibold">{{ $supplier->name }}</td>
                            <td class="text-center">Rs.{{ number_format($supplier->total_paid, 2) }}</td>
                            <td class="text-center">{{ $supplier->receipts_count }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                <i class="bi bi-x-circle display-4 d-block mb-2"></i>
                                No supplier payments found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($suppliers->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $suppliers->links('livewire.custom-pagination') }}
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Payment Details Modal --}}
    @if($showPaymentModal && $selectedSupplier)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <div>
                        <h5 class="modal-title fw-bold mb-1">
                            <i class="bi bi-receipt-cutoff me-2"></i> Payment History
                        </h5>
                        <small class="opacity-75">{{ $selectedSupplier->name }}</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" wire:click="closePaymentModal"></button>
                </div>
                <div class="modal-body p-0">
                    {{-- Supplier Info Card --}}
                    <div class="bg-light border-bottom p-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-circle text-primary me-2" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <small class="text-muted d-block">Supplier Name</small>
                                        <strong>{{ $selectedSupplier->name }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-telephone text-success me-2" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <small class="text-muted d-block">Mobile</small>
                                        <strong>{{ $selectedSupplier->mobile }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-envelope text-info me-2" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <small class="text-muted d-block">Email</small>
                                        <strong>{{ $selectedSupplier->email }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card bg-white border-0 shadow-sm">
                                    <div class="card-body py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted">Total Payments Made</span>
                                            <span class="badge bg-primary rounded-pill">{{ count($payments) }} receipts</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-success text-white border-0 shadow-sm">
                                    <div class="card-body py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Total Amount Paid</span>
                                            <strong class="fs-5">Rs.{{ number_format($payments->sum('amount'), 2) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Payment List --}}
                    <div class="p-3">
                        @forelse($payments as $index => $payment)
                        <div class="card mb-3 shadow-sm border-start border-4 border-primary">
                            <div class="card-header bg-white">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                <strong>#{{ $index + 1 }}</strong>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">Receipt #{{ $payment->id }}</h6>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar3 me-1"></i>
                                                    {{ $payment->payment_date ? date('M d, Y', strtotime($payment->payment_date)) : '-' }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="fs-4 fw-bold text-success">Rs.{{ number_format($payment->amount, 2) }}</div>
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-{{ $payment->payment_method === 'cash' ? 'cash' : ($payment->payment_method === 'cheque' ? 'receipt' : 'bank') }} me-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                {{-- Payment Method Details --}}
                                @if($payment->payment_method === 'cheque')
                                <div class="alert alert-info mb-3">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">Cheque Number</small>
                                            <strong>{{ $payment->cheque_number }}</strong>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">Bank Name</small>
                                            <strong>{{ $payment->bank_name }}</strong>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">Cheque Date</small>
                                            <strong>{{ $payment->cheque_date ? date('M d, Y', strtotime($payment->cheque_date)) : '-' }}</strong>
                                        </div>
                                    </div>
                                </div>
                                @elseif($payment->payment_method === 'bank_transfer')
                                <div class="alert alert-info mb-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Bank Name</small>
                                            <strong>{{ $payment->bank_name }}</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Transaction Reference</small>
                                            <strong>{{ $payment->bank_transaction }}</strong>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                {{-- Allocated Orders --}}
                                <div class="mb-2">
                                    <strong class="text-muted d-block mb-2">
                                        <i class="bi bi-box-seam me-1"></i> Allocated to Orders:
                                    </strong>
                                    @if($payment->allocations && count($payment->allocations) > 0)
                                    <div class="table-responsive allocation-table">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Order Code</th>
                                                    <th class="text-end">Allocated Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($payment->allocations as $alloc)
                                                <tr>
                                                    <td><span class="badge bg-dark">#{{ $alloc->purchase_order_id }}</span></td>
                                                    <td>{{ $alloc->order ? $alloc->order->order_code : 'N/A' }}</td>
                                                    <td class="text-end fw-bold text-success">Rs.{{ number_format($alloc->allocated_amount, 2) }}</td>
                                                </tr>
                                                @endforeach
                                                <tr class="table-active">
                                                    <td colspan="2" class="text-end"><strong>Total Allocated:</strong></td>
                                                    <td class="text-end fw-bold text-primary">Rs.{{ number_format($payment->allocations->sum('allocated_amount'), 2) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <div class="alert alert-warning mb-0">
                                        <i class="bi bi-exclamation-triangle me-1"></i> No order allocation found for this payment
                                    </div>
                                    @endif
                                </div>

                                {{-- Notes --}}
                                @if($payment->notes)
                                <div class="mt-3">
                                    <small class="text-muted d-block">Notes:</small>
                                    <div class="alert alert-light mb-0">{{ $payment->notes }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                            <p class="text-muted mt-3">No payments found for this supplier.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="closePaymentModal">
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <style>
        .sticky-top {
            position: sticky;
            z-index: 10;
        }

        .table th {
            font-weight: 600;
        }

        .badge {
            font-size: 0.75em;
        }

        .modal.show {
            display: block !important;
        }

        .btn-group-sm>.btn {
            padding: 0.25rem 0.5rem;
        }

        .input-group-lg .form-control {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .allocation-table {
            min-height: 100px; !important
            overflow-y: auto; !important
        }

        .allocation-table::-webkit-scrollbar {
            width: 6px;
        }

        .allocation-table::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .allocation-table::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .allocation-table::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</div>
