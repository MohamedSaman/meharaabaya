<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-journal-check text-success me-2"></i> Cheque Management
            </h3>
            <p class="text-muted mb-0">View and manage all customer cheques</p>
        </div>

    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-5">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-start border-warning border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">Pending Cheques</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $pendingCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-clock-history fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-start border-success border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Completed Cheques</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $completeCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-check2-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-start border-danger border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">Overdue Cheques</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $overdueCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-exclamation-triangle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cheque Table --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-list-ul text-primary me-2"></i> Cheque List
                </h5>
                <span class="badge bg-primary">{{ $cheques->total() ?? 0 }} records</span>
            </div>

            {{-- Search --}}
            <div class="flex-grow-1" style="max-width: 280px;">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" wire:model.live="search"
                        placeholder="Search cheque / customer...">
                </div>
            </div>

            {{-- Date Range Filter --}}
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <label class="text-sm text-muted fw-medium mb-0">Date:</label>
                <input type="date" class="form-control form-control-sm" wire:model.live="dateFrom"
                    style="width: 145px;" title="From date">
                <span class="text-muted">–</span>
                <input type="date" class="form-control form-control-sm" wire:model.live="dateTo"
                    style="width: 145px;" title="To date">
                @if($dateFrom || $dateTo)
                <button class="btn btn-sm btn-outline-secondary" wire:click="clearDateFilter" title="Clear date filter">
                    <i class="bi bi-x-lg"></i>
                </button>
                @endif
            </div>

            {{-- Status & Per Page --}}
            <div class="d-flex align-items-center gap-2">
                <label class="text-sm text-muted fw-medium">Filter</label>
                <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width: 130px;">
                    <option value="all">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="complete">Complete</option>
                    <option value="overdue">Overdue</option>
                    <option value="return">Return</option>
                </select>
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
        <div class="card-body p-0 overflow-auto">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 50px;">
                                <input type="checkbox" class="form-check-input" id="selectAll" onclick="toggleAllRows(this)">
                            </th>
                            <th class="ps-4">Cheque No</th>
                            <th>Customer</th>
                            <th class="text-center">Bank</th>
                            <th class="text-center">Amount</th>
                            <th class="text-center">Date</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cheques as $cheque)
                        <tr wire:key="cheque-{{ $cheque->id }}" class="table-row" data-id="{{ $cheque->id }}">
                            <td class="ps-4" onclick="event.stopPropagation();">
                                <input type="checkbox" class="form-check-input row-checkbox" onchange="toggleRowHighlight(this)">
                            </td>
                            <td class="ps-4">{{ $cheque->cheque_number }}</td>
                            <td>{{ $cheque->customer->name ?? '-' }}</td>
                            <td class="text-center">{{ $cheque->bank_name }}</td>
                            <td class="text-center">Rs.{{ number_format($cheque->cheque_amount, 2) }}</td>
                            <td class="text-center">{{ $cheque->cheque_date ? date('M d, Y', strtotime($cheque->cheque_date)) : '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $cheque->status == 'pending' ? 'warning' : ($cheque->status == 'complete' ? 'success' : ($cheque->status == 'return' ? 'danger' : 'secondary')) }}">
                                    {{ ucfirst($cheque->status) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                @if($cheque->status == 'pending' || $cheque->status == 'overdue')
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="bi bi-gear-fill"></i> Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <!-- Edit Cheque -->
                                        <li>
                                            <button class="dropdown-item"
                                                wire:click="openEditModal({{ $cheque->id }})">
                                                <i class="bi bi-pencil-square text-primary me-2"></i>
                                                Edit
                                            </button>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <!-- Mark as Complete -->
                                        <li>
                                            <button class="dropdown-item"
                                                wire:click="confirmComplete({{ $cheque->id }})">
                                                <i class="bi bi-check2-circle text-success me-2"></i>
                                                Complete
                                            </button>
                                        </li>
                                        <!-- Return Cheque -->
                                        <li>
                                            <button class="dropdown-item"
                                                wire:click="confirmReturn({{ $cheque->id }})">
                                                <i class="bi bi-arrow-counterclockwise text-danger me-2"></i>
                                                Return
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-x-circle display-4 d-block mb-2"></i>
                                No cheques found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($cheques->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $cheques->links('livewire.custom-pagination') }}
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Edit Cheque Modal --}}
    @if($showEditModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square text-primary me-2"></i> Edit Cheque
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeEditModal"></button>
                </div>
                <div class="modal-body">
                    {{-- Amount (read-only) --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cheque Amount <span class="text-muted">(not editable)</span></label>
                        <input type="text" class="form-control bg-light" value="Rs. {{ number_format($editChequeAmount, 2) }}" readonly disabled>
                    </div>

                    {{-- Cheque Number --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cheque Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('editChequeNumber') is-invalid @enderror"
                            wire:model.live="editChequeNumber" placeholder="Enter cheque number">
                        @error('editChequeNumber')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Bank Name --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bank Name <span class="text-danger">*</span></label>
                        <select class="form-select @error('editBankName') is-invalid @enderror" wire:model.live="editBankName">
                            <option value="">Select Bank</option>
                            <option value="Bank of Ceylon">Bank of Ceylon</option>
                            <option value="Commercial Bank of Ceylon">Commercial Bank of Ceylon</option>
                            <option value="People's Bank">People's Bank</option>
                            <option value="Hatton National Bank">Hatton National Bank</option>
                            <option value="Sampath Bank">Sampath Bank</option>
                            <option value="DFCC Bank">DFCC Bank</option>
                            <option value="National Development Bank">National Development Bank</option>
                            <option value="Nations Trust Bank">Nations Trust Bank</option>
                            <option value="Seylan Bank">Seylan Bank</option>
                            <option value="Amana Bank">Amana Bank</option>
                        </select>
                        @error('editBankName')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Cheque Date --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cheque Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('editChequeDate') is-invalid @enderror"
                            wire:model.live="editChequeDate">
                        @error('editChequeDate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeEditModal">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="updateCheque" wire:loading.attr="disabled">
                        <span wire:loading wire:target="updateCheque" class="spinner-border spinner-border-sm me-1"></span>
                        <i class="bi bi-check2 me-1" wire:loading.remove wire:target="updateCheque"></i>
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
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

    // Toast notifications
    document.addEventListener('livewire:init', function() {
        Livewire.on('toast', (data) => {
            const type = data.type || 'info';
            const message = data.message || '';
            const colors = {
                success: '#198754',
                error: '#dc3545',
                warning: '#ffc107',
                info: '#0dcaf0'
            };
            const toast = document.createElement('div');
            toast.style.cssText = `position:fixed;top:20px;right:20px;z-index:99999;padding:12px 20px;border-radius:6px;color:#fff;background:${colors[type]||colors.info};font-size:14px;box-shadow:0 4px 12px rgba(0,0,0,.2);transition:opacity .5s;`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 500);
            }, 3000);
        });
    });
</script>
@endpush