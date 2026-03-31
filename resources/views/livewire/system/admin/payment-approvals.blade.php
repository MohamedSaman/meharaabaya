<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-shield-check text-primary me-2"></i> Payment Approvals
            </h3>
            <p class="text-muted mb-0">Manage and approve customer payment requests</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-5">
        <!-- Pending Approvals Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card summary-card pending h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-container bg-warning bg-opacity-10 me-3">
                            <i class="bi bi-hourglass-split text-warning fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Pending Approvals</p>
                            <h4 class="fw-bold mb-0">{{ \App\Models\Payment::where('status', 'pending')->count() }}</h4>
                            <span class="badge bg-warning bg-opacity-10 text-warning">Awaiting Review</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approved Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card summary-card approved h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-container bg-success bg-opacity-10 me-3">
                            <i class="bi bi-check-circle text-success fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Approved</p>
                            <h4 class="fw-bold mb-0">{{ \App\Models\Payment::where('status', 'approved')->count() }}</h4>
                            <span class="badge bg-success bg-opacity-10 text-success">Completed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rejected Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card summary-card rejected h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-container bg-danger bg-opacity-10 me-3">
                            <i class="bi bi-x-circle text-danger fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Rejected</p>
                            <h4 class="fw-bold mb-0">{{ \App\Models\Payment::where('status', 'rejected')->count() }}</h4>
                            <span class="badge bg-danger bg-opacity-10 text-danger">Declined</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Actions Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card summary-card today h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-container bg-info bg-opacity-10 me-3">
                            <i class="bi bi-calendar-check text-info fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Today's Approvals</p>
                            <h4 class="fw-bold mb-0">{{ \App\Models\Payment::where('status', 'approved')->whereDate('updated_at', today())->count() }}</h4>
                            <span class="badge bg-info bg-opacity-10 text-info">Today</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Payments Table --}}
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-dark mb-1">
                    <i class="bi bi-credit-card text-primary me-2"></i> Payment Submissions
                </h5>
                <p class="text-muted small mb-0">Review and manage customer payment requests</p>
            </div>
        </div>
        <div class="card-body">
            {{-- Search and Filters --}}
            <div class="row g-3 mb-4">
                <!-- Search Bar -->
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" 
                               placeholder="Search invoice, customer..."
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
                                <span class="badge bg-primary ms-1">{{ ($filters['status'] ? 1 : 0) + ($filters['dateRange'] ? 1 : 0) }}</span>
                            @endif
                        </button>
                        <div class="dropdown-menu p-3 dropdown-menu-end" style="width: 300px;" 
                             aria-labelledby="filterDropdown">
                            <h6 class="fw-bold text-dark mb-3">Filter Options</h6>
                            
                            <!-- Status Filter -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Payment Status</label>
                                <select class="form-select" wire:model.live="filters.status">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            
                            <!-- Date Filter -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Date Range</label>
                                <input type="text" class="form-control" 
                                       placeholder="Select date range" 
                                       wire:model.live="filters.dateRange">
                            </div>
                            
                            <!-- Clear Filters -->
                            @if ($filters['status'] || $filters['dateRange'])
                            <div class="d-grid">
                                <button class="btn btn-secondary" 
                                        wire:click="resetFilters">
                                    <i class="bi bi-arrow-repeat me-1"></i> Clear Filters
                                </button>
                            </div>
                            @endif
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
                            <th>Staff</th>
                            <th class="text-center">Amount</th>
                            <th class="text-center">Payment Method</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-container bg-primary bg-opacity-10 me-3">
                                            <i class="bi bi-receipt text-primary"></i>
                                        </div>
                                        <div>
                                            <span class="fw-medium text-dark">{{ $payment->sale->invoice_number }}</span>
                                            <div class="text-muted small">{{ $payment->created_at->format('d/m/Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-container bg-dark bg-opacity-10 me-3">
                                            <i class="bi bi-person text-dark"></i>
                                        </div>
                                        <div>
                                            <span class="fw-medium text-dark">{{ $payment->sale->customer->name ?? 'Walk-in Customer' }}</span>
                                            @if($payment->sale->customer && $payment->sale->customer->phone)
                                            <div class="text-muted small">{{ $payment->sale->customer->phone }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-container bg-secondary bg-opacity-10 me-3">
                                            <i class="bi bi-person-badge text-secondary"></i>
                                        </div>
                                        <div>
                                            <span class="fw-medium text-dark">{{ $payment->sale->user->name ?? 'Unknown' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-dark">Rs.{{ number_format($payment->amount, 2) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        {{ ucfirst(str_replace('_', ' ', $payment->due_payment_method ?: $payment->payment_method)) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    {!! $payment->status_badge !!}
                                </td>
                                <td class="text-center">
                                    <div>
                                        <span class="fw-medium text-dark">{{ $payment->created_at->format('d/m/Y') }}</span>
                                        <div class="text-muted small">{{ $payment->created_at->format('h:i A') }}</div>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button class="btn btn-link text-info p-0" 
                                                wire:click="getPaymentDetails({{ $payment->id }})" 
                                                wire:loading.attr="disabled"
                                                title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    No payments found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            <div class="mt-4">
                {{ $payments->links() }}
            </div>
        </div>
    </div>

    <!-- Payment Approval Modal -->
    <div wire:ignore.self class="modal fade" id="payment-approval-modal" tabindex="-1" aria-labelledby="payment-approval-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-shield-check text-primary me-2"></i> Payment Review
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($selectedPayment)
                        <div class="row g-4">
                            <!-- Payment Information -->
                            <div class="col-12 col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="fw-bold text-dark mb-0">
                                            <i class="bi bi-info-circle text-primary me-2"></i> Payment Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <label class="form-label fw-semibold text-muted small">Invoice Number</label>
                                                <p class="fw-bold text-dark mb-0">{{ $selectedPayment->sale->invoice_number }}</p>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold text-muted small">Payment Date</label>
                                                <p class="fw-bold text-dark mb-0">{{ $selectedPayment->created_at->format('d/m/Y h:i A') }}</p>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold text-muted small">Status</label>
                                                <div>
                                                    {!! $selectedPayment->status_badge !!}
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold text-muted small">Due Date</label>
                                                <p class="fw-bold text-dark mb-0 {{ now()->gt($selectedPayment->due_date) ? 'text-danger' : '' }}">
                                                    {{ $selectedPayment->due_date ? $selectedPayment->due_date->format('d/m/Y') : 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer & Staff Details -->
                            <div class="col-12 col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="fw-bold text-dark mb-0">
                                            <i class="bi bi-people text-primary me-2"></i> Customer & Staff
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label fw-semibold text-muted small">Customer</label>
                                                <div class="d-flex align-items-center">
                                                    <div class="icon-container bg-dark bg-opacity-10 me-3">
                                                        <i class="bi bi-person text-dark"></i>
                                                    </div>
                                                    <div>
                                                        <p class="fw-bold text-dark mb-0">{{ $selectedPayment->sale->customer->name ?? 'Walk-in Customer' }}</p>
                                                        @if($selectedPayment->sale->customer && $selectedPayment->sale->customer->phone)
                                                        <div class="text-muted small">{{ $selectedPayment->sale->customer->phone }}</div>
                                                        @endif
                                                        <span class="badge bg-light text-dark mt-1">{{ ucfirst($selectedPayment->sale->customer_type) }} Customer</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold text-muted small">Staff Member</label>
                                                <div class="d-flex align-items-center">
                                                    <div class="icon-container bg-secondary bg-opacity-10 me-3">
                                                        <i class="bi bi-person-badge text-secondary"></i>
                                                    </div>
                                                    <div>
                                                        <p class="fw-bold text-dark mb-0">{{ $selectedPayment->sale->user->name ?? 'Unknown' }}</p>
                                                        <div class="text-muted small">Sales Staff</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Summary -->
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="fw-bold text-dark mb-0">
                                            <i class="bi bi-cash-stack text-primary me-2"></i> Payment Summary
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-12 col-md-3">
                                                <div class="text-center">
                                                    <label class="form-label fw-semibold text-muted small">Amount</label>
                                                    <p class="fw-bold text-primary h5 mb-0">Rs.{{ number_format($selectedPayment->amount, 2) }}</p>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <div class="text-center">
                                                    <label class="form-label fw-semibold text-muted small">Payment Method</label>
                                                    <p class="fw-bold text-info h5 mb-0">
                                                        {{ ucfirst(str_replace('_', ' ', $selectedPayment->due_payment_method ?: $selectedPayment->payment_method)) }}
                                                    </p>
                                                </div>
                                            </div>
                                            @if($selectedPayment->bank_name)
                                            <div class="col-12 col-md-3">
                                                <div class="text-center">
                                                    <label class="form-label fw-semibold text-muted small">Bank</label>
                                                    <p class="fw-bold text-dark h5 mb-0">{{ $selectedPayment->bank_name }}</p>
                                                </div>
                                            </div>
                                            @endif
                                            @if($selectedPayment->payment_date)
                                            <div class="col-12 col-md-3">
                                                <div class="text-center">
                                                    <label class="form-label fw-semibold text-muted small">Payment Date</label>
                                                    <p class="fw-bold text-dark h5 mb-0">{{ $selectedPayment->payment_date->format('d/m/Y') }}</p>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Document Preview & Purchase Details -->
                            <div class="col-12 col-md-8">
                                <!-- Document Preview -->
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="fw-bold text-dark mb-0">
                                            <i class="bi bi-file-earmark-text text-primary me-2"></i> Payment Document
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @if ($selectedPayment->due_payment_attachment)
                                            @php
                                                $extension = pathinfo($selectedPayment->due_payment_attachment, PATHINFO_EXTENSION);
                                                $isPdf = strtolower($extension) === 'pdf';
                                                $fileUrl = asset('storage/' . str_replace('public/', '', $selectedPayment->due_payment_attachment));
                                            @endphp
                                            
                                            @if ($isPdf)
                                                <div class="text-center py-4">
                                                    <div class="icon-shape icon-xl bg-danger bg-opacity-10 rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                                                        <i class="bi bi-file-earmark-pdf fs-1 text-danger"></i>
                                                    </div>
                                                    <h5 class="mb-3">PDF Document</h5>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <a href="{{ $fileUrl }}" target="_blank" class="btn btn-primary">
                                                            <i class="bi bi-eye me-1"></i> View PDF
                                                        </a>
                                                        <a href="{{ $fileUrl }}" download class="btn btn-outline-secondary">
                                                            <i class="bi bi-download me-1"></i> Download
                                                        </a>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="document-preview text-center">
                                                    <img src="{{ $fileUrl }}" class="img-fluid rounded border" style="max-height: 400px;">
                                                    <div class="mt-3">
                                                        <a href="{{ $fileUrl }}" download class="btn btn-outline-primary">
                                                            <i class="bi bi-download me-1"></i> Download Image
                                                        </a>
                                                        <button type="button" class="btn btn-outline-info" onclick="openFullImage('{{ $fileUrl }}')">
                                                            <i class="bi bi-fullscreen me-1"></i> Full Screen
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            <div class="text-center py-4">
                                                <div class="icon-shape icon-xl bg-secondary bg-opacity-10 rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                                                    <i class="bi bi-file-earmark-x fs-1 text-secondary"></i>
                                                </div>
                                                <h5 class="text-muted">No Document Attached</h5>
                                                <p class="text-muted mb-0">No payment proof was provided</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Purchase Details -->
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h6 class="fw-bold text-dark mb-0">
                                            <i class="bi bi-cart-check text-primary me-2"></i> Purchase Details
                                        </h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="ps-4">Item</th>
                                                        <th class="text-center">Qty</th>
                                                        <th class="text-end pe-4">Price</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($selectedPayment->sale->items as $item)
                                                    <tr>
                                                        <td class="ps-4">
                                                            <div>
                                                                @if ($item->Product)
                                                                    <span class="fw-medium text-dark">
                                                                        {{ $item->Product->brand ?? 'Unknown Brand' }} {{ $item->Product->model ?? 'Unknown Model' }}
                                                                    </span>
                                                                    <div class="text-muted small">{{ $item->Product->code ?? 'N/A' }}</div>
                                                                @else
                                                                    <span class="fw-medium text-dark">{{ $item->Product_name ?? 'Product Details Unavailable' }}</span>
                                                                    <div class="text-danger small">Item record may have been deleted</div>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="fw-medium text-dark">{{ $item->quantity }}</span>
                                                        </td>
                                                        <td class="text-end pe-4">
                                                            <span class="fw-medium text-dark">Rs.{{ number_format($item->unit_price, 2) }}</span>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <td colspan="2" class="text-end fw-bold ps-4">Total:</td>
                                                        <td class="text-end pe-4 fw-bold text-primary">
                                                            Rs.{{ number_format($selectedPayment->sale->total_amount, 2) }}
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                @if($selectedPayment->sale->notes)
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h6 class="fw-bold text-dark mb-0">
                                            <i class="bi bi-sticky text-primary me-2"></i> Notes
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-0">{{ $selectedPayment->sale->notes }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="col-12 col-md-4">
                                @if ($selectedPayment->status === 'pending')
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="fw-bold text-dark mb-0">
                                            <i class="bi bi-gear text-primary me-2"></i> Actions
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-3">
                                            <button class="btn btn-success" wire:click="approvePayment">
                                                <i class="bi bi-check-circle me-2"></i>Approve Payment
                                            </button>
                                            <button class="btn btn-danger" 
                                                    onclick="document.getElementById('rejectionSection').classList.toggle('d-none')">
                                                <i class="bi bi-x-circle me-2"></i>Reject Payment
                                            </button>
                                        </div>

                                        <!-- Rejection Reason Section -->
                                        <div id="rejectionSection" class="d-none mt-4">
                                            <div class="alert alert-info">
                                                <div class="d-flex">
                                                    <i class="bi bi-info-circle-fill text-info me-2"></i>
                                                    <div>
                                                        <p class="mb-1 fw-semibold">Due Date Extension</p>
                                                        <p class="mb-0 small">Rejecting this payment will automatically extend the due date by 3 days.</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Rejection Reason</label>
                                                <textarea class="form-control" rows="3"
                                                          wire:model="rejectionReason"
                                                          placeholder="Please provide a reason for rejecting this payment..."></textarea>
                                                @error('rejectionReason')
                                                <span class="text-danger small">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-danger flex-grow-1" wire:click="rejectPayment">
                                                    <i class="bi bi-x-circle me-2"></i>Confirm Rejection
                                                </button>
                                                <button class="btn btn-secondary"
                                                        onclick="document.getElementById('rejectionSection').classList.add('d-none')">
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="alert alert-{{ $selectedPayment->status === 'approved' ? 'success' : 'warning' }}">
                                    <div class="d-flex">
                                        <i class="bi bi-{{ $selectedPayment->status === 'approved' ? 'check-circle' : 'info-circle' }}-fill me-3"></i>
                                        <div>
                                            <p class="mb-0 fw-semibold">
                                                This payment has already been {{ $selectedPayment->status === 'approved' ? 'approved' : 'rejected' }}.
                                            </p>
                                            @if($selectedPayment->status === 'approved' && $selectedPayment->payment_date)
                                            <p class="mb-0 small">Approved on: {{ $selectedPayment->payment_date->format('d/m/Y h:i A') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading payment details...</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
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
        border-left-color: #ffc107;
    }

    .summary-card.approved {
        border-left-color: #198754;
    }

    .summary-card.rejected {
        border-left-color: #dc3545;
    }

    .summary-card.today {
        border-left-color: #0dcaf0;
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

    .btn-success {
        background-color: #2a9d8f;
        border-color: #2a9d8f;
    }

    .btn-success:hover {
        background-color: #248277;
        border-color: #248277;
        transform: translateY(-2px);
    }

    .btn-danger {
        background-color: #e63946;
        border-color: #e63946;
    }

    .btn-danger:hover {
        background-color: #d00000;
        border-color: #d00000;
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

    .alert {
        border-radius: 8px;
        border: none;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
    }

    .input-group-text {
        border-radius: 8px 0 0 8px;
    }

    .dropdown-menu {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .icon-shape {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .icon-xl {
        width: 80px;
        height: 80px;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        on('openModal', (modalId) => {
            let modal = new bootstrap.Modal(document.getElementById(modalId));
            modal.show();
        });

        on('closeModal', (modalId) => {
            let modalElement = document.getElementById(modalId);
            let modal = bootstrap.Modal.getInstance(modalElement);
            modal.hide();
        });

        on('showToast', ({ type, message }) => {
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

    function openFullImage(imageUrl) {
        Swal.fire({
            imageUrl: imageUrl,
            imageAlt: 'Payment Receipt',
            width: '90%',
            showCloseButton: true,
            showConfirmButton: false
        });
    }

    document.addEventListener('livewire:initialized', function() {
        Livewire.on('open--modal', function() {
            const modal = new bootstrap.Modal(document.getElementById('payment-approval-modal'));
            modal.show();
        });
    });
</script>
@endpush>
