<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-arrow-return-left text-warning me-2"></i> Supplier Returns List
            </h3>
            <p class="text-muted mb-0">View and manage all supplier returns</p>
        </div>
        <div>
            <a href="{{ route('admin.return-supplier') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i> New Return
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card summary-card border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-container bg-primary bg-opacity-10 me-3">
                            <i class="bi bi-arrow-return-left text-primary fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Returns</p>
                            <h4 class="fw-bold mb-0">{{ $summaryStats['total_returns'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card summary-card border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-container bg-success bg-opacity-10 me-3">
                            <i class="bi bi-box text-success fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Quantity</p>
                            <h4 class="fw-bold mb-0">{{ $summaryStats['total_quantity'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card summary-card border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-container bg-info bg-opacity-10 me-3">
                            <i class="bi bi-currency-rupee text-info fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Amount</p>
                            <h4 class="fw-bold mb-0">Rs.{{ number_format($summaryStats['total_amount'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card summary-card border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-container bg-danger bg-opacity-10 me-3">
                            <i class="bi bi-exclamation-triangle text-danger fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Damaged Items</p>
                            <h4 class="fw-bold mb-0">{{ $summaryStats['damaged_returns'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-funnel me-2"></i> Filters
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Search</label>
                    <input type="text" class="form-control" wire:model.live="search"
                        placeholder="Search product, code, or supplier...">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Supplier</label>
                    <select class="form-select" wire:model.live="supplierFilter">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Purchase Order</label>
                    <select class="form-select" wire:model.live="purchaseOrderFilter">
                        <option value="">All POs</option>
                        @foreach($purchaseOrders as $po)
                        <option value="{{ $po->id }}">{{ $po->order_code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Return Reason</label>
                    <select class="form-select" wire:model.live="reasonFilter">
                        <option value="">All Reasons</option>
                        <option value="damaged">Damaged</option>
                        <option value="defective">Defective</option>
                        <option value="wrong_item">Wrong Item</option>
                        <option value="excess">Excess</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Date Range</label>
                    <div class="input-group">
                        <input type="date" class="form-control" wire:model.live="dateFrom" placeholder="From">
                        <span class="input-group-text">to</span>
                        <input type="date" class="form-control" wire:model.live="dateTo" placeholder="To">
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <button class="btn btn-outline-secondary" wire:click="resetFilters">
                            <i class="bi bi-arrow-clockwise me-1"></i> Reset Filters
                        </button>
                        <div class="btn-group">
                            <button class="btn btn-success" wire:click="exportCSV">
                                <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                            </button>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Returns Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-1">
                    <i class="bi bi-list-check text-primary me-2"></i> Supplier Returns
                </h5>
                <p class="text-muted small mb-0">Showing {{ $returns->firstItem() ?? 0 }}-{{ $returns->lastItem() ?? 0 }} of {{ $returns->total() }} returns</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="text-sm text-muted fw-medium">Show</label>
                <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 80px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="500">500</option>
                </select>
                <span class="text-sm text-muted">entries</span>
            </div>

        </div>
        <div class="card-body p-0 overflow-auto ">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 50px;">
                                <input type="checkbox" class="form-check-input" id="selectAll" onclick="toggleAllRows(this)">
                            </th>
                            <th class="ps-4">Return ID</th>
                            <th>Date</th>
                            <th>Purchase Order</th>
                            <th>Supplier</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Amount</th>
                            <th>Reason</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $return)
                        <tr class="table-row" data-id="{{ $return->id }}">
                            <td class="ps-4" onclick="event.stopPropagation();">
                                <input type="checkbox" class="form-check-input row-checkbox" onchange="toggleRowHighlight(this)">
                            </td>
                            <td class="ps-4">
                                <span class="fw-medium text-dark">#{{ $return->id }}</span>
                            </td>
                            <td>
                                <div class="small">
                                    <div class="fw-semibold">{{ $return->created_at->format('M j, Y') }}</div>
                                    <div class="text-muted">{{ $return->created_at->format('g:i A') }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-semibold text-info">{{ $return->purchaseOrder->order_code }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-building me-2 text-muted"></i>
                                    <span class="fw-medium">{{ $return->purchaseOrder->supplier->name }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($return->product->image)
                                    <img src="{{ $return->product->image }}"
                                        alt="{{ $return->product->name }}"
                                        class="rounded me-2"
                                        style="width: 32px; height: 32px; object-fit: cover;">
                                    @else
                                    <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center"
                                        style="width: 32px; height: 32px;">
                                        <i class="bi bi-box text-muted"></i>
                                    </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $return->product->name }}</div>
                                        <small class="text-muted">{{ $return->product->code }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-warning fs-6">{{ $return->return_quantity }}</span>
                            </td>
                            <td>
                                <span class="fw-semibold">Rs.{{ number_format($return->unit_price, 2) }}</span>
                            </td>
                            <td>
                                <span class="fw-bold text-success">Rs.{{ number_format($return->total_amount, 2) }}</span>
                            </td>
                            <td>
                                @php
                                $reasonColors = [
                                'damaged' => 'danger',
                                'defective' => 'warning',
                                'wrong_item' => 'info',
                                'excess' => 'primary',
                                'other' => 'secondary'
                                ];
                                @endphp
                                <span class="badge bg-{{ $reasonColors[$return->return_reason] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $return->return_reason)) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="bi bi-gear-fill"></i> Actions
                                    </button>

                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">

                                        <!-- View Details -->
                                        <li>
                                            <button class="dropdown-item py-2 d-flex align-items-center"
                                                wire:click="viewReturn({{ $return->id }})"
                                                wire:loading.attr="disabled">
                                                <i class="bi bi-eye text-info me-2"></i>
                                                <span>View Details</span>
                                                <div wire:loading wire:target="viewReturn({{ $return->id }})"
                                                    class="spinner-border spinner-border-sm ms-2">
                                                </div>
                                            </button>
                                        </li>

                                        <!-- Download PDF -->
                                        <li>
                                            <button class="dropdown-item py-2 d-flex align-items-center"
                                                wire:click="downloadPDF({{ $return->id }})"
                                                wire:loading.attr="disabled">
                                                <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                                <span>Download PDF</span>
                                                <div wire:loading wire:target="downloadPDF({{ $return->id }})"
                                                    class="spinner-border spinner-border-sm ms-2">
                                                </div>
                                            </button>
                                        </li>

                                        <!-- Export CSV -->
                                        <li>
                                            <button class="dropdown-item py-2 d-flex align-items-center"
                                                wire:click="exportSingleCSV({{ $return->id }})"
                                                wire:loading.attr="disabled">
                                                <i class="bi bi-file-earmark-excel text-success me-2"></i>
                                                <span>Export CSV</span>
                                                <div wire:loading wire:target="exportSingleCSV({{ $return->id }})"
                                                    class="spinner-border spinner-border-sm ms-2">
                                                </div>
                                            </button>
                                        </li>

                                        <li>
                                            <hr class="dropdown-divider my-1">
                                        </li>

                                        <!-- Delete Return -->
                                        <li>
                                            <button class="dropdown-item py-2 d-flex align-items-center text-danger"
                                                wire:click="confirmDelete({{ $return->id }})"
                                                wire:loading.attr="disabled">
                                                <i class="bi bi-trash me-2"></i>
                                                <span>Delete Return</span>
                                                <div wire:loading wire:target="confirmDelete({{ $return->id }})"
                                                    class="spinner-border spinner-border-sm ms-2">
                                                </div>
                                            </button>
                                        </li>

                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="bi bi-inbox text-muted fs-1 mb-3 d-block"></i>
                                <p class="text-muted mb-0">No supplier returns found</p>
                                @if($search || $supplierFilter || $purchaseOrderFilter || $dateFrom || $dateTo || $reasonFilter)
                                <p class="text-muted small">Try adjusting your filters</p>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->

            @if ($returns->hasPages())
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-center">
                    {{ $returns->links('livewire.custom-pagination') }}
                </div>
            </div>
            @endif

        </div>
    </div>

    <!-- Return Details Modal -->
    <div wire:ignore.self class="modal fade" id="returnModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-eye me-2"></i> Return Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($selectedReturn)
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>Return ID:</strong> #{{ $selectedReturn->id }}</p>
                            <p><strong>Purchase Order:</strong> {{ $selectedReturn->purchaseOrder->order_code }}</p>
                            <p><strong>Supplier:</strong> {{ $selectedReturn->purchaseOrder->supplier->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Return Date:</strong> {{ $selectedReturn->created_at->format('M j, Y g:i A') }}</p>
                            <p><strong>Total Amount:</strong> <span class="fw-bold text-success">Rs.{{ number_format($selectedReturn->total_amount, 2) }}</span></p>
                            <p>
                                <strong>Reason:</strong>
                                <span class="badge bg-{{ $reasonColors[$selectedReturn->return_reason] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $selectedReturn->return_reason)) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold border-bottom pb-2">Product Information</h6>
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                @if($selectedReturn->product->image)
                                <img src="{{ $selectedReturn->product->image }}"
                                    alt="{{ $selectedReturn->product->name }}"
                                    class="rounded me-3"
                                    style="width: 60px; height: 60px; object-fit: cover;">
                                @else
                                <div class="bg-white rounded me-3 d-flex align-items-center justify-content-center"
                                    style="width: 60px; height: 60px;">
                                    <i class="bi bi-box text-muted fs-4"></i>
                                </div>
                                @endif
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1">{{ $selectedReturn->product->name }}</h6>
                                    <p class="text-muted mb-1">Code: {{ $selectedReturn->product->code }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">Return Details</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="mb-2"><strong>Return Quantity:</strong></p>
                                            <p class="mb-2"><strong>Unit Price:</strong></p>
                                            <p class="mb-0"><strong>Total Amount:</strong></p>
                                        </div>
                                        <div class="col-6 text-end">
                                            <p class="mb-2"><span class="badge bg-warning fs-6">{{ $selectedReturn->return_quantity }}</span></p>
                                            <p class="mb-2">Rs.{{ number_format($selectedReturn->unit_price, 2) }}</p>
                                            <p class="mb-0 fw-bold text-success">Rs.{{ number_format($selectedReturn->total_amount, 2) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">Additional Information</h6>
                                    <p class="mb-2"><strong>Notes:</strong></p>
                                    <p class="text-muted">{{ $selectedReturn->notes ?? 'No additional notes' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-success" wire:click="downloadPDF({{ $selectedReturn->id ?? '' }})">
                        <i class="bi bi-download me-1"></i> Download PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .summary-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
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
        color: #ffffff;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .table td {
        vertical-align: middle;
        padding: 0.75rem 1rem;
    }

    .btn-group-sm>.btn {
        padding: 0.25rem 0.5rem;
    }

    .form-select-sm {
        padding: 0.25rem 2.25rem 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.025);
    }

    /* Row selection highlight - Multiple specificity levels */
    /* Row selection highlight - Multiple specificity levels, high-contrast for dark backgrounds */
    tr.selected-row,
    table tbody tr.selected-row,
    table.table tbody tr.selected-row,
    .table tbody tr.selected-row {
        background-color: #223046 !important;
        /* dark blue-gray */
        color: #fff !important;
    }

    tr.selected-row td,
    table tbody tr.selected-row td,
    table.table tbody tr.selected-row td {
        background-color: #223046 !important;
        color: #fff !important;
        --bs-table-accent-bg: #223046 !important;
        --bs-table-bg: #223046 !important;
    }

    tr.selected-row td .text-muted,
    table tbody tr.selected-row td .text-muted {
        color: #e0e0e0 !important;
    }

    table tbody tr.selected-row:hover,
    table.table tbody tr.selected-row:hover {
        background-color: #2d3a4a !important;
        /* slightly lighter on hover */
        color: #fff !important;
    }

    table tbody tr.selected-row:hover td,
    table.table tbody tr.selected-row:hover td {
        background-color: #2d3a4a !important;
        color: #fff !important;
        --bs-table-accent-bg: #2d3a4a !important;
        --bs-table-bg: #2d3a4a !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Row selection functions
    function toggleRowHighlight(checkbox) {
        const row = checkbox.closest('tr');
        if (checkbox.checked) {
            row.classList.add('selected-row');
            row.style.backgroundColor = '#d4e6f1';
        } else {
            row.classList.remove('selected-row');
            row.style.backgroundColor = '';
            document.getElementById('selectAll').checked = false;
        }
    }

    function toggleAllRows(selectAllCheckbox) {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
            toggleRowHighlight(checkbox);
        });
    }

    window.addEventListener('alert', event => {
        Swal.fire({
            title: event.detail.type === 'error' ? 'Error!' : 'Success!',
            text: event.detail.message,
            icon: event.detail.type === 'error' ? 'error' : 'success',
            confirmButtonColor: '#4361ee'
        });
    });

    Livewire.on('show-return-modal', () => {
        var modalEl = document.getElementById('returnModal');
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    });

    // Close modal when hidden
    document.getElementById('returnModal')?.addEventListener('hidden.bs.modal', function() {
        Livewire.dispatch('close-return-modal');
    });
</script>
@endpush