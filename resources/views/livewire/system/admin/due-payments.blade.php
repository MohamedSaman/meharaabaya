<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-cash-stack text-success me-2"></i> Customer Due Payments
            </h3>
            <p class="text-muted mb-0">Manage and collect pending payments from customers efficiently</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-5">
        <!-- Pending Payments Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card summary-card pending h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-container bg-info bg-opacity-10 me-3">
                            <i class="bi bi-hourglass text-info fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Pending Payment</p>
                            <h4 class="fw-bold mb-0">{{ $pendingCount }}</h4>
                            <span class="badge bg-info bg-opacity-10 text-info">To Collect</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Amount Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card summary-card pending-amount h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-container bg-secondary bg-opacity-10 me-3">
                            <i class="bi bi-currency-rupee text-secondary fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Pending Amount</p>
                            <h4 class="fw-bold mb-0">Rs.{{ number_format($pendingAmount, 2) }}</h4>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">To Collect</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Awaiting Approval Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card summary-card awaiting h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-container bg-warning bg-opacity-10 me-3">
                            <i class="bi bi-clock-history text-warning fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Awaiting Approval</p>
                            <h4 class="fw-bold mb-0">{{ $awaitingApprovalCount }}</h4>
                            <span class="badge bg-warning bg-opacity-10 text-warning">In Review</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Due Amount Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card summary-card total h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-container bg-success bg-opacity-10 me-3">
                            <i class="bi bi-currency-rupee text-success fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Due</p>
                            <h4 class="fw-bold mb-0">Rs.{{ number_format($totalDueAmount, 2) }}</h4>
                            <span class="badge bg-success bg-opacity-10 text-success">Amount</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="card h-100 mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-dark mb-1">
                    <i class="bi bi-journal-text text-primary me-2"></i> Due Payments
                </h5>
                <p class="text-muted small mb-0">View and manage all customer due payments</p>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4">
                <!-- Search Bar -->
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" 
                               placeholder="Search invoices or customers..."
                               wire:model.live.debounce.300ms="search">
                    </div>
                </div>

                <!-- Filter Dropdown -->
                <div class="col-12 col-md-6">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle w-100" type="button" 
                                id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-funnel me-2"></i> Filters
                            @if ($filters['status'] || $filters['dateRange'])
                                <span class="badge bg-primary ms-1">!</span>
                            @endif
                        </button>
                        <div class="dropdown-menu p-3 dropdown-menu-end" style="width: 300px;" 
                             aria-labelledby="filterDropdown">
                            <h6 class="fw-bold text-dark mb-3">Filter Options</h6>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Payment Status</label>
                                <select class="form-select" wire:model.live="filters.status">
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="partial">Partial</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Date Range</label>
                                <input type="text" class="form-control" 
                                       placeholder="Select date range" 
                                       wire:model.live="filters.dateRange">
                            </div>
                            
                            <div class="d-grid">
                                <button class="btn btn-secondary" wire:click="resetFilters">
                                    <i class="bi bi-arrow-repeat me-1"></i> Reset Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payments Table --}}
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Invoice</th>
                            <th>Customer</th>
                            <th class="text-center">Due Amount</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dueSales as $sale)
                            <tr>
                                <td class="ps-4">
                                    <div>
                                        <span class="fw-medium text-dark">{{ $sale->invoice_number }}</span>
                                        <div class="text-muted small">
                                            {{ $sale->created_at->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-container bg-primary bg-opacity-10 me-3">
                                            <i class="bi bi-person text-primary"></i>
                                        </div>
                                        <div>
                                            <span class="fw-medium text-dark">{{ $sale->customer->name }}</span>
                                            <div class="text-muted small">
                                                {{ $sale->customer->phone }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-danger">Rs.{{ number_format($sale->due_amount, 2) }}</span>
                                    <div class="text-muted small">
                                        Total: Rs.{{ number_format($sale->total_amount, 2) }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($sale->payment_status === 'paid')
                                        <span class="badge bg-success">Paid</span>
                                    @elseif($sale->payment_status === 'partial')
                                        <span class="badge bg-warning">Partial</span>
                                    @else
                                        <span class="badge bg-danger">Unpaid</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-primary rounded-pill " 
                                                wire:click="getSaleDetails({{ $sale->id }})" 
                                                wire:loading.attr="disabled"
                                                title="Receive Payment">
                                            <i class="bi bi-currency-dollar"></i>Receive
                                        </button>
                                        {{--<button class="btn btn-sm btn-warning" 
                                                wire:click="openExtendDueModal({{ $sale->id }})" 
                                                wire:loading.attr="disabled"
                                                title="Extend Due Date">
                                            <i class="bi bi-calendar-plus"></i>
                                        </button>--}}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-cash-coin display-4 d-block mb-2"></i>
                                    No due payments found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            <div class="mt-4">
                {{ $dueSales->links() }}
            </div>
        </div>
    </div>

    <!-- Payment Detail Modal -->
    <div wire:ignore.self class="modal fade" id="payment-detail-modal" tabindex="-1" aria-labelledby="payment-detail-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-credit-card text-primary me-2"></i> Receive Due Payment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($saleDetail)
                        <div class="row g-4">
                            <!-- Invoice Overview -->
                            <div class="col-12 col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="fw-bold text-dark mb-0">
                                            <i class="bi bi-receipt text-primary me-2"></i> Invoice Details
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-4">
                                            <div class="icon-container bg-primary bg-opacity-10 mx-auto mb-3">
                                                <i class="bi bi-person text-primary fs-2"></i>
                                            </div>
                                            <h6 class="fw-bold mb-1">{{ $saleDetail->customer->name }}</h6>
                                            <p class="text-muted small mb-0">{{ $saleDetail->customer->phone }}</p>
                                        </div>

                                        <div class="row g-2">
                                            <div class="col-12">
                                                <label class="form-label fw-semibold text-muted small">Invoice</label>
                                                <p class="fw-medium text-dark mb-0">{{ $saleDetail->invoice_number }}</p>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold text-muted small">Sale Date</label>
                                                <p class="fw-medium text-dark mb-0">{{ $saleDetail->created_at->format('d/m/Y') }}</p>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold text-muted small">Total Amount</label>
                                                <p class="fw-medium text-dark mb-0">Rs.{{ number_format($saleDetail->total_amount, 2) }}</p>
                                            </div>
                                            <div class="col-12">
                                                <div class="card bg-danger bg-opacity-5 border-0 mt-3">
                                                    <div class="card-body text-center py-3">
                                                        <label class="form-label fw-semibold text-muted text-white small mb-1">Amount Due</label>
                                                        <h4 class="fw-bold text-white mb-0">Rs.{{ number_format($saleDetail->due_amount, 2) }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if (strpos($saleDetail->notes ?? '', 'Due date extended') !== false)
                                            <div class="alert alert-warning bg-warning bg-opacity-10 border-0 mt-3">
                                                <div class="d-flex">
                                                    <div class="me-2">
                                                        <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                                                    </div>
                                                    <div>
                                                        <p class="mb-0 fw-bold small">Due date has been extended</p>
                                                        @php
                                                        $notes = explode("\n", $saleDetail->notes);
                                                        $extensionNotes = array_filter($notes, function ($note) {
                                                            return strpos($note, 'Due date extended') !== false;
                                                        });
                                                        @endphp
                                                        @foreach ($extensionNotes as $note)
                                                        <p class="mb-0 text-xs">{{ $note }}</p>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Form -->
                            <div class="col-12 col-md-8">
                                <form wire:submit.prevent="submitPayment">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="fw-bold text-dark mb-0">
                                                <i class="bi bi-wallet2 text-primary me-2"></i> Payment Collection
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-12 col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Received Amount <span class="text-danger">*</span></label>
                                                        <input type="number" step="0.01" class="form-control @error('receivedAmount') is-invalid @enderror"
                                                            wire:model="receivedAmount" required placeholder="0">
                                                        @error('receivedAmount')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                                                        <select class="form-select @error('duePaymentMethod') is-invalid @enderror"
                                                            wire:model="duePaymentMethod" required>
                                                            <option value="">-- Select payment method --</option>
                                                            <option value="cash">Cash</option>
                                                            <option value="cheque">Cheque</option>
                                                            <option value="bank_transfer">Bank Transfer</option>
                                                            <option value="credit_card">Credit Card</option>
                                                            <option value="debit_card">Debit Card</option>
                                                        </select>
                                                        @error('duePaymentMethod')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Payment Notes</label>
                                                        <textarea class="form-control" rows="3" wire:model="paymentNote"
                                                            placeholder="Add any notes about this payment (optional)"></textarea>
                                                        <div class="form-text">Include any specific details about this payment.</div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Payment Receipt/Document</label>
                                                        <input type="file" class="form-control @error('duePaymentAttachment') is-invalid @enderror"
                                                            wire:model="duePaymentAttachment">
                                                        @error('duePaymentAttachment')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                        <div class="form-text">Upload receipt, cheque image, or other payment proof.</div>
                                                    </div>
                                                </div>

                                                <!-- Preview Area -->
                                                <div class="col-12">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h6 class="fw-bold text-dark mb-0">
                                                                <i class="bi bi-image text-primary me-2"></i> Document Preview
                                                            </h6>
                                                        </div>
                                                        <div class="card-body">
                                                            @if ($duePaymentAttachment)
                                                                <div class="text-center">
                                                                    @if(is_array($duePaymentAttachmentPreview))
                                                                        @if($duePaymentAttachmentPreview['type'] === 'pdf')
                                                                            <div class="d-flex flex-column align-items-center p-3">
                                                                                <i class="bi bi-file-earmark-pdf text-danger fs-1 mb-2"></i>
                                                                                <span class="text-muted">PDF document</span>
                                                                                <span class="text-muted small">{{ $duePaymentAttachment->getClientOriginalName() }}</span>
                                                                            </div>
                                                                        @elseif($duePaymentAttachmentPreview['type'] === 'image' && !empty($duePaymentAttachmentPreview['preview']))
                                                                            <img src="{{ $duePaymentAttachmentPreview['preview'] }}" class="img-fluid rounded border" style="max-height: 200px">
                                                                        @else
                                                                            <div class="d-flex flex-column align-items-center p-3">
                                                                                <i class="bi {{ $duePaymentAttachmentPreview['icon'] ?? 'bi-file-earmark' }} {{ $duePaymentAttachmentPreview['color'] ?? 'text-secondary' }} fs-1 mb-2"></i>
                                                                                <span class="text-muted">File attached</span>
                                                                                <span class="text-muted small">{{ $duePaymentAttachment->getClientOriginalName() }}</span>
                                                                            </div>
                                                                        @endif
                                                                    @else
                                                                        <div class="d-flex flex-column align-items-center p-3">
                                                                            <i class="bi bi-file-earmark text-secondary fs-1 mb-2"></i>
                                                                            <span class="text-muted">File attached</span>
                                                                            <span class="text-muted small">{{ $duePaymentAttachment->getClientOriginalName() }}</span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <div class="text-center p-4">
                                                                    <div class="icon-container bg-light mb-3 mx-auto">
                                                                        <i class="bi bi-file-earmark-plus text-muted fs-4"></i>
                                                                    </div>
                                                                    <p class="text-muted mb-0">No document attached</p>
                                                                    <p class="text-muted small">Upload receipt or payment proof</p>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <div class="d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    <i class="bi bi-x me-1"></i> Cancel
                                                </button>
                                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                                    <span wire:loading.remove>
                                                        <i class="bi bi-send me-1"></i> Submit
                                                    </span>
                                                    <span wire:loading>
                                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                                        Submitting...
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading sale details...</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Extend Due Date Modal -->
    {{--<div wire:ignore.self class="modal fade" id="extend-due-modal" tabindex="-1" aria-labelledby="extend-due-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-calendar-plus text-primary me-2"></i> Extend Due Date
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="extendDueDate">
                        <div class="text-center mb-4">
                            <div class="icon-container bg-warning bg-opacity-10 mx-auto mb-3">
                                <i class="bi bi-calendar-week text-warning fs-2"></i>
                            </div>
                            <h5 class="fw-bold">Extend Payment Due Date</h5>
                            <p class="text-muted">Provide a new due date and reason for extension</p>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">New Due Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('newDueDate') is-invalid @enderror"
                                wire:model="newDueDate" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            @error('newDueDate')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Reason for Extension <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('extensionReason') is-invalid @enderror" wire:model="extensionReason"
                                rows="3" placeholder="Explain why the due date needs to be extended..."></textarea>
                            @error('extensionReason')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">This information will be added to the sale notes.</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x me-1"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    <i class="bi bi-check2-circle me-1"></i> Confirm Extension
                                </span>
                                <span wire:loading>
                                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                    Processing...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>--}}
</div>

@push('styles')
<style>
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

    .summary-card {
        border-left: 4px solid;
        transition: all 0.3s ease;
    }

    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
    }

    .summary-card.pending {
        border-left-color: #4cc9f0;
    }

    .summary-card.pending-amount {
        border-left-color: #6c757d;
    }

    .summary-card.awaiting {
        border-left-color: #ffc107;
    }

    .summary-card.total {
        border-left-color: #198754;
    }

    .icon-container {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .table th {
        border-top: none;
        font-weight: 600;
        color: #6c757d;
        font-size: 0.85rem;
        text-transform: uppercase;
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
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        border-color: #4361ee;
    }

    .btn {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background-color: #4361ee;
        border-color: #4361ee;
    }

    .btn-primary:hover {
        background-color: #3f37c9;
        border-color: #3f37c9;
        transform: translateY(-2px);
    }

    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #5a6268;
        transform: translateY(-2px);
    }

    .btn-sm {
        padding: 0.4rem 0.8rem;
        font-size: 0.875rem;
    }

    .alert {
        border-radius: 8px;
        border: none;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
        font-weight: 500;
    }

    .input-group-text {
        border-radius: 8px 0 0 8px;
        background-color: #f8f9fa;
        border: 1px solid #e2e8f0;
    }

    .dropdown-menu {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        padding: 0.5rem;
    }

    .btn-group .btn {
        padding: 0.4rem 0.75rem;
    }

    .btn-group .btn:first-child {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .btn-group .btn:last-child {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .text-xs {
        font-size: 0.75rem;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('openModal', (modalId) => {
            let modal = new bootstrap.Modal(document.getElementById(modalId));
            modal.show();
        });

        Livewire.on('closeModal', (modalId) => {
            let modalElement = document.getElementById(modalId);
            let modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        });

        // Close modal with delay to allow toast to be seen
        Livewire.on('closeModalDelayed', (modalId) => {
            setTimeout(() => {
                let modalElement = document.getElementById(modalId);
                let modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
            }, 1500); // 1.5 second delay
        });

        Livewire.on('showToast', ({ type, message }) => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
        });
    });
</script>
@endpush
