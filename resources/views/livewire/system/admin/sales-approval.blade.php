<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-shield-check text-primary me-2"></i> Sales Approvals
            </h3>
            <p class="text-muted mb-0">Manage and approve staff sales submissions</p>
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
                            <p class="text-muted mb-1">Pending Sales</p>
                            <h4 class="fw-bold mb-0">{{ $pendingCount }}</h4>
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
                            <h4 class="fw-bold mb-0">{{ $approvedCount }}</h4>
                            <span class="badge bg-success bg-opacity-10 text-success">Confirmed</span>
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
                            <h4 class="fw-bold mb-0">{{ $rejectedCount }}</h4>
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
                            <p class="text-muted mb-1">Today's Actions</p>
                            <h4 class="fw-bold mb-0">{{ $todayCount }}</h4>
                            <span class="badge bg-info bg-opacity-10 text-info">Today</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sales Table --}}
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-dark mb-1">
                    <i class="bi bi-journal-text text-primary me-2"></i> Sales Submissions
                </h5>
                <p class="text-muted small mb-0">Review and manage staff sales submissions</p>
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
                            @if ($statusFilter || $dateFilter)
                                <span class="badge bg-primary ms-1">{{ ($statusFilter ? 1 : 0) + ($dateFilter ? 1 : 0) }}</span>
                            @endif
                        </button>
                        <div class="dropdown-menu p-3 dropdown-menu-end" style="width: 300px;" 
                             aria-labelledby="filterDropdown">
                            <h6 class="fw-bold text-dark mb-3">Filter Options</h6>
                            
                            <!-- Status Filter -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Status</label>
                                <select class="form-select" wire:model.live="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="confirm">Confirmed</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            
                            <!-- Date Filter -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Date Range</label>
                                <select class="form-select" wire:model.live="dateFilter">
                                    <option value="">All Dates</option>
                                    <option value="today">Today</option>
                                    <option value="yesterday">Yesterday</option>
                                    <option value="this_week">This Week</option>
                                    <option value="this_month">This Month</option>
                                </select>
                            </div>
                            
                            <!-- Clear Filters -->
                            @if ($statusFilter || $dateFilter)
                            <div class="d-grid">
                                <button class="btn btn-secondary" 
                                        wire:click="$set('statusFilter', ''); $set('dateFilter', '')">
                                    <i class="bi bi-arrow-repeat me-1"></i> Clear Filters
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sales Table --}}
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Invoice</th>
                            <th>Customer</th>
                            <th>Staff</th>
                            <th class="text-center">Amount</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            <tr>
                                <td class="ps-4">
                                    <div>
                                        <span class="fw-medium text-dark">{{ $sale->invoice_number }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-container bg-primary bg-opacity-10 me-3">
                                            <i class="bi bi-person text-primary"></i>
                                        </div>
                                        <div>
                                            <span class="fw-medium text-dark">{{ $sale->customer->name ?? 'Walk-in Customer' }}</span>
                                            @if($sale->customer && $sale->customer->phone)
                                            <div class="text-muted small">{{ $sale->customer->phone }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium text-dark">{{ $sale->user->name ?? 'Unknown' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-dark">Rs.{{ number_format($sale->total_amount, 2) }}</span>
                                </td>
                                <td class="text-center">
                                    @if($sale->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                    @elseif($sale->status === 'confirm')
                                    <span class="badge bg-success">Confirmed</span>
                                    @elseif($sale->status === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div>
                                        <span class="fw-medium text-dark">{{ $sale->created_at->format('d/m/Y') }}</span>
                                        <div class="text-muted small">{{ $sale->created_at->format('h:i A') }}</div>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button class="btn btn-link text-info p-0" 
                                                wire:click="viewSale({{ $sale->id }})" 
                                                wire:loading.attr="disabled"
                                                title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @if($sale->status === 'pending')
                                        <button class="btn btn-link text-warning p-0" 
                                                wire:click="editSale({{ $sale->id }})" 
                                                wire:loading.attr="disabled"
                                                title="Edit Sale">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-link text-success p-0" 
                                                wire:click="viewSale({{ $sale->id }})" 
                                                wire:loading.attr="disabled"
                                                title="Approve/Reject">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    No sales found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            <div class="mt-4">
                {{ $sales->links() }}
            </div>
        </div>
    </div>

    <!-- Sale Approval Modal -->
    <div wire:ignore.self class="modal fade" id="sale-approval-modal" tabindex="-1" aria-labelledby="sale-approval-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-shield-check text-primary me-2"></i> Sale Review
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($selectedSale)
                        <div class="row g-4">
                            <!-- Sale Information -->
                            <div class="col-12 col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="fw-bold text-dark mb-0">
                                            <i class="bi bi-info-circle text-primary me-2"></i> Sale Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <label class="form-label fw-semibold text-muted small">Invoice Number</label>
                                                <p class="fw-bold text-dark mb-0">{{ $selectedSale->invoice_number }}</p>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold text-muted small">Sale Date</label>
                                                <p class="fw-bold text-dark mb-0">{{ $selectedSale->created_at->format('d/m/Y h:i A') }}</p>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold text-muted small">Status</label>
                                                <div>
                                                    @if($selectedSale->status === 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                    @elseif($selectedSale->status === 'confirm')
                                                    <span class="badge bg-success">Confirmed</span>
                                                    @elseif($selectedSale->status === 'rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold text-muted small">Staff Member</label>
                                                <p class="fw-bold text-dark mb-0">{{ $selectedSale->user->name ?? 'Unknown' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer Details -->
                            <div class="col-12 col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="fw-bold text-dark mb-0">
                                            <i class="bi bi-person text-primary me-2"></i> Customer Details
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @if($selectedSale->customer)
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label fw-semibold text-muted small">Customer Name</label>
                                                <p class="fw-bold text-dark mb-0">{{ $selectedSale->customer->name }}</p>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold text-muted small">Phone</label>
                                                <p class="fw-bold text-dark mb-0">{{ $selectedSale->customer->phone ?? 'N/A' }}</p>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold text-muted small">Type</label>
                                                <span class="badge bg-info">{{ ucfirst($selectedSale->customer->type) }}</span>
                                            </div>
                                        </div>
                                        @else
                                        <p class="text-muted mb-0">Walk-in Customer</p>
                                        @endif
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
                                                    <label class="form-label fw-semibold text-muted small">Subtotal</label>
                                                    <p class="fw-bold text-primary h5 mb-0">Rs.{{ number_format($selectedSale->subtotal, 2) }}</p>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <div class="text-center">
                                                    <label class="form-label fw-semibold text-muted small">Discount</label>
                                                    <p class="fw-bold text-warning h5 mb-0">Rs.{{ number_format($selectedSale->discount_amount, 2) }}</p>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <div class="text-center">
                                                    <label class="form-label fw-semibold text-muted small">Total Amount</label>
                                                    <p class="fw-bold text-success h4 mb-0">Rs.{{ number_format($selectedSale->total_amount, 2) }}</p>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <div class="text-center">
                                                    <label class="form-label fw-semibold text-muted small">Payment Status</label>
                                                    <div>
                                                        <span class="badge bg-{{ $selectedSale->payment_status === 'paid' ? 'success' : ($selectedSale->payment_status === 'pending' ? 'warning' : 'info') }}">
                                                            {{ ucfirst($selectedSale->payment_status) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sale Items -->
                            <div class="col-12 col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="fw-bold text-dark mb-0">
                                            <i class="bi bi-list-ul text-primary me-2"></i> Sale Items
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
                                                    @foreach ($selectedSale->items as $item)
                                                    <tr>
                                                        <td class="ps-4">
                                                            <div>
                                                                <span class="fw-medium text-dark">{{ $item->product_name }}</span>
                                                                <div class="text-muted small">{{ $item->product_code }}</div>
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
                                                            Rs.{{ number_format($selectedSale->total_amount, 2) }}
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                @if($selectedSale->notes)
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h6 class="fw-bold text-dark mb-0">
                                            <i class="bi bi-sticky text-primary me-2"></i> Notes
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-0">{{ $selectedSale->notes }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="col-12 col-md-4">
                                @if ($selectedSale->status === 'pending')
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="fw-bold text-dark mb-0">
                                            <i class="bi bi-gear text-primary me-2"></i> Actions
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-3">
                                            <button class="btn btn-success" wire:click="approveSale">
                                                <i class="bi bi-check-circle me-2"></i>Approve Sale
                                            </button>
                                            <button class="btn btn-danger" 
                                                    onclick="document.getElementById('rejectionSection').classList.toggle('d-none')">
                                                <i class="bi bi-x-circle me-2"></i>Reject Sale
                                            </button>
                                        </div>

                                        <!-- Rejection Reason Section -->
                                        <div id="rejectionSection" class="d-none mt-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Rejection Reason</label>
                                                <textarea class="form-control" rows="3"
                                                          wire:model="rejectionReason"
                                                          placeholder="Please provide a reason for rejecting this sale..."></textarea>
                                                @error('rejectionReason')
                                                <span class="text-danger small">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-danger flex-grow-1" wire:click="rejectSale">
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
                                <div class="alert alert-info">
                                    <div class="d-flex">
                                        <i class="bi bi-info-circle-fill text-info me-3"></i>
                                        <div>
                                            <p class="mb-0 fw-semibold">This sale has already been processed.</p>
                                            <p class="mb-0 small">Status: <strong>{{ $selectedSale->status === 'confirm' ? 'Approved' : 'Rejected' }}</strong></p>
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
                            <p class="mt-2 text-muted">Loading sale details...</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Sale Edit Modal -->
    <div wire:ignore.self class="modal fade" id="sale-edit-modal" tabindex="-1" aria-labelledby="sale-edit-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square text-primary me-2"></i> Edit Sale
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($editingSale)
                        <div class="row g-4">
                            <!-- Sale Information -->
                            <div class="col-12 col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="fw-bold text-dark mb-0">
                                            <i class="bi bi-info-circle text-primary me-2"></i> Sale Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <label class="form-label fw-semibold text-muted small">Invoice Number</label>
                                                <p class="fw-bold text-dark mb-0">{{ $editingSale->invoice_number }}</p>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold text-muted small">Sale Date</label>
                                                <p class="fw-bold text-dark mb-0">{{ $editingSale->created_at->format('d/m/Y h:i A') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer Details -->
                            <div class="col-12 col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="fw-bold text-dark mb-0">
                                            <i class="bi bi-person text-primary me-2"></i> Customer Details
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @if($editingSale->customer)
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label fw-semibold text-muted small">Customer Name</label>
                                                <p class="fw-bold text-dark mb-0">{{ $editingSale->customer->name }}</p>
                                            </div>
                                            @if($editingSale->customer->phone)
                                            <div class="col-12">
                                                <label class="form-label fw-semibold text-muted small">Phone</label>
                                                <p class="fw-bold text-dark mb-0">{{ $editingSale->customer->phone }}</p>
                                            </div>
                                            @endif
                                        </div>
                                        @else
                                        <p class="text-muted mb-0">Walk-in Customer</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Sale Items Edit -->
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="fw-bold text-dark mb-0">
                                            <i class="bi bi-list-ul text-primary me-2"></i> Sale Items
                                        </h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="ps-4">Product</th>
                                                        <th class="text-center">Quantity</th>
                                                        <th class="text-center">Unit Price</th>
                                                        <th class="text-center">Discount</th>
                                                        <th class="text-end">Total</th>
                                                        <th class="text-center pe-4">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($editItems as $itemId => $item)
                                                    <tr wire:key="edit-item-{{ $itemId }}">
                                                        <td class="ps-4">
                                                            <div>
                                                                <span class="fw-medium text-dark">{{ $item->product_name }}</span>
                                                                <div class="text-muted small">{{ $item->product_code }}</div>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <input type="number" class="form-control form-control-sm mx-auto text-center"
                                                                   style="width: 80px;"
                                                                   wire:model.live="editQuantities.{{ $itemId }}"
                                                                   wire:change="updateEditItem({{ $itemId }})"
                                                                   min="1">
                                                        </td>
                                                        <td class="text-center">
                                                            <input type="number" class="form-control form-control-sm mx-auto text-center"
                                                                   style="width: 100px;"
                                                                   wire:model.live="editPrices.{{ $itemId }}"
                                                                   wire:change="updateEditItem({{ $itemId }})"
                                                                   step="0.01" min="0">
                                                        </td>
                                                        <td class="text-center">
                                                            <input type="number" class="form-control form-control-sm mx-auto text-center"
                                                                   style="width: 100px;"
                                                                   wire:model.live="editDiscounts.{{ $itemId }}"
                                                                   wire:change="updateEditItem({{ $itemId }})"
                                                                   step="0.01" min="0">
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="fw-bold text-dark">
                                                                Rs.{{ number_format(($editPrices[$itemId] ?? 0) * ($editQuantities[$itemId] ?? 0) - ($editDiscounts[$itemId] ?? 0) * ($editQuantities[$itemId] ?? 0), 2) }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center pe-4">
                                                            <button type="button" class="btn btn-link text-danger p-0"
                                                                    wire:click="removeEditItem({{ $itemId }})"
                                                                    title="Remove Item">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sale Summary -->
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="fw-bold text-dark mb-0">
                                            <i class="bi bi-calculator text-primary me-2"></i> Sale Summary
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-12 col-md-4">
                                                <div class="text-center">
                                                    <label class="form-label fw-semibold text-muted small">Subtotal</label>
                                                    <p class="fw-bold text-primary h5 mb-0">Rs.{{ number_format($editSubtotal, 2) }}</p>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <div class="text-center">
                                                    <label class="form-label fw-semibold text-muted small">Total Discount</label>
                                                    <p class="fw-bold text-warning h5 mb-0">Rs.{{ number_format($editTotalDiscount, 2) }}</p>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <div class="text-center">
                                                    <label class="form-label fw-semibold text-muted small">Grand Total</label>
                                                    <p class="fw-bold text-success h4 mb-0">Rs.{{ number_format($editGrandTotal, 2) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </button>
                            <button type="button" class="btn btn-success" wire:click="saveSaleEdit">
                                <i class="bi bi-check-circle me-2"></i>Save Changes
                            </button>
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
</script>
@endpush>
