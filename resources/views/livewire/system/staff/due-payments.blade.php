<div>
    <div class="container-fluid py-4">
        <!-- Page Header with Stats -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow overflow-hidden">
                    <!-- Header Content -->
                    <div class="card-header bg-gradient-primary bg-opacity-10 p-4 border-0">
                        <div class="d-flex align-items-center">
                            <div class="icon-shape icon-lg bg-gradient-primary shadow-lg text-center border-radius-lg me-3">
                                <i class="bi bi-cash-stack text-white opacity-10"></i>
                            </div>
                            <div>
                                <h4 class="mb-1 fw-bold text-dark">Customer Due Payments</h4>
                                <p class="text-muted mb-0">Manage and collect pending payments from customers
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <!-- Pending Payments Card -->
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-white shadow-sm h-100 border-0 hover-shadow">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape icon-md rounded-circle bg-info bg-opacity-10 me-3 text-center">
                                                <i class="bi bi-hourglass text-info"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs text-muted mb-0 text-uppercase">Pending Payment</p>
                                                <div class="d-flex align-items-baseline mt-1">
                                                    <h3 class="mb-0 fw-bold">{{ $duePayments->where('status', null)->count() }}</h3>
                                                    <span class="badge bg-info bg-opacity-10 text-info ms-2">To Collect</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Awaiting Approval Card -->
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-white shadow-sm h-100 border-0 hover-shadow">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape icon-md rounded-circle bg-warning bg-opacity-10 me-3 text-center">
                                                <i class="bi bi-clock-history text-warning"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs text-muted mb-0 text-uppercase">Awaiting Approval</p>
                                                <div class="d-flex align-items-baseline mt-1">
                                                    <h3 class="mb-0 fw-bold">{{ $duePayments->where('status', 'pending')->count() }}</h3>
                                                    <span class="badge bg-warning bg-opacity-10 text-warning ms-2">In Review</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Overdue Payments Card -->
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-white shadow-sm h-100 border-0 hover-shadow">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape icon-md rounded-circle bg-danger bg-opacity-10 me-3 text-center">
                                                <i class="bi bi-exclamation-circle text-danger"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs text-muted mb-0 text-uppercase">Overdue</p>
                                                <div class="d-flex align-items-baseline mt-1">
                                                    <h3 class="mb-0 fw-bold">
                                                        {{ $duePayments->where('status', null)->filter(function($payment) { return now()->gt($payment->due_date); })->count() }}
                                                    </h3>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger ms-2">Attention</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Due Amount Card -->
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-white shadow-sm h-100 border-0 hover-shadow">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape icon-md rounded-circle bg-success bg-opacity-10 me-3 text-center">
                                                <i class="bi bi text-success">Rs.</i>
                                            </div>
                                            <div>
                                                <p class="text-xs text-muted mb-0 text-uppercase">Total Due</p>
                                                <div class="d-flex align-items-baseline mt-1">
                                                    <h3 class="mb-0 fw-bold">
                                                        Rs.{{ number_format($duePayments->where('status', null)->sum('amount'), 0) }}
                                                    </h3>
                                                    <span class="badge bg-success bg-opacity-10 text-success ms-2">Amount</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0 overflow-hidden">
                    <!-- Search & Filter Bar -->
                    <div class="card-header bg-white p-3 border-bottom">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div class="input-group input-group-merge border rounded-pill bg-light shadow-none"
                                style="max-width: 350px;">
                                <span class="input-group-text bg-transparent border-0">
                                    <i class="bi bi-search text-primary"></i>
                                </span>
                                <input type="text" class="form-control border-0 py-2"
                                    placeholder="Search invoices or customers..."
                                    wire:model.live.debounce.300ms="search">
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <div class="dropdown">
                                    <button class="btn btn-outline-primary rounded-pill dropdown-toggle shadow-none"
                                        type="button" id="filterDropdown" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="bi bi-funnel me-1"></i> Filters
                                        @if ($filters['status'] || $filters['dateRange'])
                                        <span class="badge bg-primary ms-1">!</span>
                                        @endif
                                    </button>
                                    <div class="dropdown-menu p-3 shadow-lg border-0" style="width: 300px;"
                                        aria-labelledby="filterDropdown">
                                        <h6 class="dropdown-header bg-light rounded py-2 mb-3 text-center">Filter
                                            Options</h6>

                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Payment Status</label>
                                            <select class="form-select form-select-sm rounded-pill shadow-none"
                                                wire:model.live="filters.status">
                                                <option value="">All Statuses</option>
                                                <option value="null">Pending Payment</option>
                                                <option value="pending">Pending Approval</option>
                                                <option value="approved">Approved</option>
                                                <option value="rejected">Rejected</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Due Date Range</label>
                                            <input type="text"
                                                class="form-control form-control-sm rounded-pill shadow-none"
                                                placeholder="Select date range" wire:model.live="filters.dateRange">
                                        </div>

                                        <div class="d-grid">
                                            <button class="btn btn-sm btn-secondary rounded-pill"
                                                wire:click="resetFilters">
                                                <i class="bi bi-x-circle me-1"></i> Reset Filters
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Content -->
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th
                                            class="ps-4 text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Invoice</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Customer</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Amount</th>

                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Status</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($duePayments as $payment)
                                    <tr
                                        class="border-bottom @if (now()->gt($payment->due_date) && $payment->status === null) bg-danger bg-opacity-10 @endif">
                                        <td class="ps-4">
                                            <div class="d-flex flex-column">
                                                <h6 class="mb-0 text-sm">{{ $payment->sale->invoice_number }}</h6>
                                                <p class="text-xs text-muted mb-0">
                                                    {{ $payment->sale->created_at->format('d/m/Y') }}
                                                </p>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="avatar avatar-sm rounded-circle bg-gradient-primary shadow-sm me-2 d-flex align-items-center justify-content-center">
                                                    <span
                                                        class="text-white fw-bold">{{ substr($payment->sale->customer->name, 0, 1) }}</span>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-weight-bold mb-0">
                                                        {{ $payment->sale->customer->name }}
                                                    </p>
                                                    <p class="text-xs text-muted mb-0">
                                                        {{ $payment->sale->customer->phone }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="icon-shape icon-xs rounded-circle bg-success bg-opacity-10 me-2 text-center">
                                                    <i class="bi bi text-success">Rs.</i>
                                                </div>
                                                <span class="text-sm font-weight-bold">
                                                    {{ number_format($payment->amount, 2) }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            {!! $payment->status_badge !!}
                                        </td>
                                        <td class="text-center">
                                            @if ($payment->status === null)
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-primary rounded-pill shadow-sm"
                                                    wire:click="getSaleDetails({{ $payment->sale_id }})">
                                                    <i class="bi bi-currency-dollar me-1"></i> Receive
                                                </button>

                                            </div>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="text-center py-5">
                                                <div class="mb-3">
                                                    <div
                                                        class="icon-shape icon-xl rounded-circle bg-light mx-auto">
                                                        <i class="bi bi-cash-coin text-muted"
                                                            style="font-size: 2rem;"></i>
                                                    </div>
                                                </div>
                                                <h5 class="text-muted font-weight-normal">No due payments found
                                                </h5>
                                                <p class="text-sm mb-0">All customer payments are completed or no
                                                    matching results found.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="px-4 py-3 border-top">
                            {{ $duePayments->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Detail Modal - Changed to XL size -->
    <div wire:ignore.self class="modal fade" id="payment-detail-modal" tabindex="-1" role="dialog"
        aria-labelledby="payment-detail-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document"> <!-- Changed to modal-xl -->
            <div class="modal-content border-0 shadow overflow-hidden">
                <div class="modal-header bg-gradient-primary text-white p-3">
                    <h5 class="modal-title" id="payment-detail-modal-label">
                        <i class="bi bi-credit-card me-2"></i> Receive Due Payment
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    @if ($saleDetail)
                    <div class="row g-0">
                        <!-- Invoice Overview - Reduced to 3 columns for more payment form space -->
                        <div class="col-md-3 bg-light p-4 border-end">
                            <div class="text-center mb-4">
                                <div
                                    class="avatar avatar-xl rounded-circle bg-gradient-primary shadow mx-auto d-flex align-items-center justify-content-center">
                                    <span class="text-white"
                                        style="font-size: 2rem;">{{ substr($saleDetail->customer->name ?? 'W', 0, 1) }}</span>
                                </div>
                                <h6 class="mt-3 mb-0 fw-bold">{{ $saleDetail->customer->name ?? 'Walk-in Customer' }}</h6>
                                <p class="text-muted mb-0 small">{{ $saleDetail->customer->phone ?? 'No phone' }}</p>
                            </div>

                            <h6 class="text-uppercase text-muted small fw-bold mb-3 border-bottom pb-2">Invoice
                                Details</h6>
                            <div class="mb-3">
                                <p class="mb-2 d-flex justify-content-between">
                                    <span class="text-muted">Invoice:</span>
                                    <span class="fw-bold">{{ $saleDetail->invoice_number }}</span>
                                </p>
                                <p class="mb-2 d-flex justify-content-between">
                                    <span class="text-muted">Sale Date:</span>
                                    <span>{{ $saleDetail->created_at->format('d/m/Y') }}</span>
                                </p>
                                <p class="mb-2 d-flex justify-content-between">
                                    <span class="text-muted">Due Date:</span>
                                    <span
                                        class="{{ now()->gt($saleDetail->due_date) ? 'text-danger fw-bold' : '' }}">
                                        {{ \Carbon\Carbon::parse($saleDetail->due_date)->format('d/m/Y') }}
                                    </span>
                                </p>
                                <div class="card bg-white border-0 shadow-sm p-2 mt-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small">Amount Due:</span>
                                        <span
                                            class="fw-bold fs-4 text-primary">Rs.{{ number_format($saleDetail->due_amount, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            @if (strpos($saleDetail->notes ?? '', 'Due date extended') !== false)
                            <div class="alert alert-warning bg-warning bg-opacity-10 border-0 mt-3 p-2 small">
                                <div class="d-flex">
                                    <div class="me-2">
                                        <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-bold">Due date has been extended</p>
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

                        <!-- Payment Form - Increased to 9 columns -->
                        <div class="col-md-9">
                            <form wire:submit.prevent="submitPayment">
                                <div class="row g-0">
                                    <div class="col-lg-12">
                                        <!-- Form Header -->
                                        <div class="bg-gradient-light p-4 border-bottom">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-white shadow p-2 me-3">
                                                    <i class="bi bi-wallet2 text-primary fs-4"></i>
                                                </div>
                                                <div>
                                                    <h5 class="mb-0 fw-bold">Payment Collection</h5>
                                                    <p class="text-muted mb-0">Record customer payment details for
                                                        admin approval</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Two-column form layout -->
                                    <div class="col-lg-6 p-4">
                                        <div class="mb-4">
                                            <label class="form-label small fw-bold mb-2">Received Amount</label>
                                            <input type="text" class="form-control @error('receivedAmount') is-invalid @enderror"
                                                wire:model="receivedAmount" required>
                                            @error('receivedAmount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <label class="form-label small fw-bold mb-2">Payment Method <span
                                                    class="text-danger">*</span></label>

                                            <div class="input-group shadow-sm">
                                                <span class="input-group-text bg-white border-end-0">
                                                    <i class="bi bi-credit-card text-primary"></i>
                                                </span>


                                                <select
                                                    class="form-select border-start-0 ps-0 @error('duePaymentMethod') is-invalid @enderror"
                                                    wire:model="duePaymentMethod" required>
                                                    <option value="">-- Select payment method --</option>
                                                    <option value="cash">Cash</option>
                                                    <option value="cheque">Cheque</option>
                                                </select>
                                                @error('duePaymentMethod')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label small fw-bold mb-2">Payment Notes</label>
                                            <textarea class="form-control shadow-sm" rows="3" wire:model="paymentNote"
                                                placeholder="Add any notes about this payment (optional)"></textarea>
                                            <div class="form-text">Include any specific details about this payment.
                                            </div>
                                        </div>

                                        <div
                                            class="alert alert-info bg-info bg-opacity-10 border-0 d-flex align-items-center shadow-sm">
                                            <i class="bi bi-info-circle-fill text-info fs-5 me-3"></i>
                                            <div>
                                                <p class="mb-0">This payment will be sent for admin approval.</p>
                                                <p class="mb-0 small">The customer's account will be updated once
                                                    approved.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 p-4 bg-light border-start">
                                        <div class="mb-4">
                                            <label class="form-label small fw-bold mb-2">Payment
                                                Receipt/Document</label>
                                            <div class="input-group shadow-sm mb-2">
                                                <span class="input-group-text bg-white border-end-0">
                                                    <i class="bi bi-file-earmark-image text-primary"></i>
                                                </span>
                                                <input type="file"
                                                    class="form-control border-start-0 ps-0 @error('duePaymentAttachment') is-invalid @enderror"
                                                    wire:model="duePaymentAttachment">
                                                @error('duePaymentAttachment')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-text">Upload receipt, cheque image, or other payment
                                                proof.</div>
                                        </div>

                                        <!-- Preview Area with better styling -->
                                        <div class="card shadow-sm border-0 mb-4 bg-white">
                                            <div class="card-header bg-light py-2 px-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-image text-primary me-2"></i>
                                                    <span class="small fw-bold">Document Preview</span>
                                                </div>
                                            </div>
                                            <div class="card-body p-0 text-center">
                                                @if ($duePaymentAttachment)
                                                <div class="position-relative">
                                                    @if(is_array($duePaymentAttachmentPreview))
                                                    @if($duePaymentAttachmentPreview['type'] === 'pdf')
                                                    <!-- PDF Icon Display -->
                                                    <div class="d-flex flex-column align-items-center p-4">
                                                        <i class="bi bi-file-earmark-pdf text-danger fs-1 mb-2"></i>
                                                        <span class="text-muted">PDF document</span>
                                                        <span class="text-muted small">{{ $duePaymentAttachment->getClientOriginalName() }}</span>
                                                    </div>
                                                    @elseif($duePaymentAttachmentPreview['type'] === 'image' && !empty($duePaymentAttachmentPreview['preview']))
                                                    <!-- Image Preview -->
                                                    <img src="{{ $duePaymentAttachmentPreview['preview'] }}" class="img-fluid" style="max-height: 200px">
                                                    @else
                                                    <!-- Generic file icon fallback -->
                                                    <div class="d-flex flex-column align-items-center p-4">
                                                        <i class="bi {{ $duePaymentAttachmentPreview['icon'] ?? 'bi-file-earmark' }} {{ $duePaymentAttachmentPreview['color'] ?? 'text-secondary' }} fs-1 mb-2"></i>
                                                        <span class="text-muted">File attached</span>
                                                        <span class="text-muted small">{{ $duePaymentAttachment->getClientOriginalName() }}</span>
                                                    </div>
                                                    @endif
                                                    @else
                                                    <!-- Fallback for unexpected preview data -->
                                                    <div class="d-flex flex-column align-items-center p-4">
                                                        <i class="bi bi-file-earmark text-secondary fs-1 mb-2"></i>
                                                        <span class="text-muted">File attached</span>
                                                        <span class="text-muted small">{{ $duePaymentAttachment->getClientOriginalName() }}</span>
                                                    </div>
                                                    @endif
                                                    <div class="position-absolute bottom-0 start-0 end-0 py-2 px-3 bg-dark bg-opacity-50 text-white text-start small">
                                                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                                                        New attachment preview
                                                    </div>
                                                </div>

                                                @else
                                                <div class="p-5 d-flex flex-column align-items-center">
                                                    <div class="icon-shape icon-md bg-light rounded-circle mb-3">
                                                        <i class="bi bi-file-earmark-plus fs-4 text-muted"></i>
                                                    </div>
                                                    <p class="text-muted mb-0">No document attached</p>
                                                    <p class="text-xs text-muted">Upload receipt or payment proof</p>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 p-4 bg-white border-top">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" class="btn btn-light border shadow-sm"
                                                data-bs-dismiss="modal">
                                                <i class="bi bi-x me-1"></i> Cancel
                                            </button>
                                            <button type="submit" class="btn btn-primary shadow-sm">
                                                <i class="bi bi-send me-1"></i> Submit for Approval
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
                        <p class="mt-2">Loading payment details...</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        /* Add smooth transitions */
        .modal.fade .modal-dialog {
            transition: transform .3s ease-out;
        }

        /* Custom avatar classes */
        .avatar {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-xl {
            width: 80px;
            height: 80px;
        }

        /* Custom icon shape */
        .icon-shape {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-shape.icon-lg {
            width: 48px;
            height: 48px;
        }

        .icon-shape.icon-xl {
            width: 72px;
            height: 72px;
        }

        .icon-shape.icon-md {
            width: 42px;
            height: 42px;
        }

        .icon-shape.icon-xs {
            width: 24px;
            height: 24px;
        }

        /* Hover effect on table rows */
        .table tbody tr:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.05);
        }

        /* Custom form controls with consistent height */
        .form-control,
        .form-select {
            min-height: 42px;
        }

        .input-group-text {
            min-height: 42px;
        }

        /* Add these styles to your existing styles */
        .hover-shadow:hover {
            transform: translateY(-3px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
            transition: all .2s ease;
        }

        .icon-shape.icon-md {
            width: 48px;
            height: 48px;
        }

        .bg-gradient-primary {
            background: linear-gradient(45deg, rgba(var(--bs-primary-rgb), 1), rgba(var(--bs-primary-rgb), 0.8)) !important;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('openModal', (modalId) => {
                let modal = new bootstrap.Modal(document.getElementById(modalId));
                modal.show();
            });

            @this.on('closeModal', (modalId) => {
                let modalElement = document.getElementById(modalId);
                let modal = bootstrap.Modal.getInstance(modalElement);
                modal.hide();
            });

            @this.on('showToast', ({
                type,
                message
            }) => {
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
    <!-- filepath: /home/amhar-dev/Desktop/mr.webxkey/resources/views/livewire/staff/due-payments.blade.php -->

    <!-- Add this modal after the payment-detail-modal -->
    <div wire:ignore.self class="modal fade" id="extend-due-modal" tabindex="-1" role="dialog"
        aria-labelledby="extend-due-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-gradient-warning text-white p-3">
                    <h5 class="modal-title" id="extend-due-modal-label">
                        <i class="bi bi-calendar-plus me-2"></i> Extend Due Date
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="extendDueDate">
                        <div class="text-center mb-4">
                            <div class="icon-shape icon-xl bg-warning bg-opacity-10 rounded-circle mx-auto mb-3">
                                <i class="bi bi-calendar-week text-warning fs-2"></i>
                            </div>
                            <h5 class="fw-bold">Extend Payment Due Date</h5>
                            <p class="text-muted">Provide a new due date and reason for extension</p>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold mb-2">New Due Date <span
                                    class="text-danger">*</span></label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-calendar-date text-primary"></i>
                                </span>
                                <input type="date"
                                    class="form-control border-start-0 ps-0 @error('newDueDate') is-invalid @enderror"
                                    wire:model="newDueDate" min="{{ date('Y-m-d') }}">
                                @error('newDueDate')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold mb-2">Reason for Extension <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control shadow-sm @error('extensionReason') is-invalid @enderror" wire:model="extensionReason"
                                rows="3" placeholder="Explain why the due date needs to be extended..."></textarea>
                            @error('extensionReason')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">This information will be added to the sale notes.</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
                                <i class="bi bi-x me-1"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2-circle me-1"></i> Confirm Extension
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
