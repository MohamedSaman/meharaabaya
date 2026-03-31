<div class="container-fluid py-4" style="background-color:#f5fdf1ff;">
    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0 fw-bold" style="color:#3b5b0c;">
                                <i class="bi bi-bank2 me-2" style="color:#8eb922;"></i>Deposits Management
                            </h4>
                            <small class="text-muted">Manage cash deposits and view history</small>
                        </div>
                        <div>
                            <button class="btn btn-sm rounded-0" style="background: linear-gradient(0deg, rgba(59, 91, 12, 1) 0%, rgba(142, 185, 34, 1) 100%); color:white;" wire:click="resetFilters">
                                <i class="bi bi-arrow-clockwise me-1"></i>Reset Filters
                            </button>
                            <button class="btn btn-success btn-sm rounded-0 ms-2" wire:click="openAddModal">
                                <i class="bi bi-plus-lg me-1"></i>Add Deposit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cash Summary Card --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-3" style="color:#3b5b0c;">
                        <i class="bi bi-wallet2 me-2" style="color:#8eb922;"></i>Today's Cash Summary
                    </h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3 rounded" style="background-color: #f8f9fa;">
                                <h6 class="text-muted mb-1">Opening Cash</h6>
                                <h5 class="fw-bold" style="color:#3b5b0c;">Rs. {{ number_format($openingCash, 2) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 rounded" style="background-color: #d4edda;">
                                <h6 class="text-muted mb-1">Cash Sales</h6>
                                <h5 class="fw-bold text-success">Rs. {{ number_format($todayCashAmount, 2) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center p-3 rounded" style="background-color: #fff3cd;">
                                <h6 class="text-muted mb-1">Deposited</h6>
                                <h5 class="fw-bold" style="color:#8eb922;">Rs. {{ number_format($todayDepositAmount, 2) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center p-3 rounded" style="background-color: #f8d7da;">
                                <h6 class="text-muted mb-1">Supplier Pay</h6>
                                <h5 class="fw-bold text-danger">Rs. {{ number_format($todaySupplierPayments, 2) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center p-3 rounded" style="background-color: #d1ecf1;">
                                <h6 class="text-muted mb-1">Remaining</h6>
                                <h5 class="fw-bold text-info">Rs. {{ number_format($openingCash + $todayCashAmount - $todayExpenses - $todayRefunds - $todaySupplierPayments - $todayDepositAmount, 2) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <label class="form-label fw-semibold" style="color:#3b5b0c;">
                        <i class="bi bi-search me-1"></i>Search
                    </label>
                    <input type="text" class="form-control rounded-0" wire:model.live="search" placeholder="Search by description or amount...">
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <label class="form-label fw-semibold" style="color:#3b5b0c;">
                        <i class="bi bi-calendar-event me-1"></i>From Date
                    </label>
                    <input type="date" class="form-control rounded-0" wire:model.live="dateFrom">
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <label class="form-label fw-semibold" style="color:#3b5b0c;">
                        <i class="bi bi-calendar-event me-1"></i>To Date
                    </label>
                    <input type="date" class="form-control rounded-0" wire:model.live="dateTo">
                </div>
            </div>
        </div>
    </div>

    {{-- Deposits List --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold" style="color:#3b5b0c;">
                        <i class="bi bi-list-ul me-2" style="color:#8eb922;"></i>Deposits History ({{ $deposits->total() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($deposits->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color:#f8f9fa;">
                                <tr>
                                    <th class="border-0 py-3">#</th>
                                    <th class="border-0 py-3">Date</th>
                                    <th class="border-0 py-3">Amount</th>
                                    <th class="border-0 py-3">Description</th>
                                    <th class="border-0 py-3">Created</th>
                                    <th class="border-0 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deposits as $index => $deposit)
                                <tr wire:click="viewDeposit({{ $deposit->id }})"
                                    style="cursor: pointer; transition: background-color 0.2s;"
                                    onmouseover="this.style.backgroundColor='#f0f8f0';"
                                    onmouseout="this.style.backgroundColor='';">
                                    <td>{{ $deposits->firstItem() + $index }}</td>
                                    <td>
                                        <strong style="color:#3b5b0c;">{{ \Carbon\Carbon::parse($deposit->date)->format('d/m/Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($deposit->date)->format('l') }}</small>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">Rs. {{ number_format($deposit->amount, 2) }}</span>
                                    </td>
                                    <td>{{ $deposit->description }}</td>
                                    <td>
                                        <small class="text-muted">{{ $deposit->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td onclick="event.stopPropagation();">
                                        <button class="btn btn-sm btn-outline-primary rounded-0 me-1" wire:click="viewDeposit({{ $deposit->id }})">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger rounded-0"
                                            wire:click="deleteDeposit({{ $deposit->id }})"
                                            onclick="return confirm('Are you sure you want to delete this deposit?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="card-footer bg-white">
                        {{ $deposits->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <p class="text-muted mt-3">No deposits found</p>
                        <button class="btn btn-success rounded-0" wire:click="openAddModal">
                            <i class="bi bi-plus-lg me-1"></i>Add First Deposit
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Add Deposit Modal --}}
    @if($showAddModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-0">
                <div class="modal-header" style="background: linear-gradient(0deg, rgba(59, 91, 12, 1) 0%, rgba(142, 185, 34, 1) 100%); color:white;">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-plus-circle me-2"></i>Add New Deposit
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeAddModal"></button>
                </div>
                <form wire:submit.prevent="addDeposit">
                    <div class="modal-body">
                        {{-- Today's Cash Summary --}}
                        <div class="p-3 mb-4 rounded-0" style="background-color: #f5fdf1ff; border: 1px solid #8eb922;">
                            <h6 class="fw-bold mb-3" style="color:#3b5b0c;">
                                <i class="bi bi-wallet2 me-2" style="color:#8eb922;"></i>Today's Cash Summary
                            </h6>
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">Opening Cash:</span>
                                <span class="fw-semibold" style="color:#3b5b0c;">Rs. {{ number_format($openingCash, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">Today's Cash Amount:</span>
                                <span class="fw-semibold text-success">Rs. {{ number_format($todayCashAmount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">Today's Expenses:</span>
                                <span class="fw-semibold text-danger">Rs. {{ number_format($todayExpenses, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">Today's Refunds:</span>
                                <span class="fw-semibold text-danger">Rs. {{ number_format($todayRefunds, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">Today's Supplier Payments:</span>
                                <span class="fw-semibold text-danger">Rs. {{ number_format($todaySupplierPayments, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">Today's Deposit Amount:</span>
                                <span class="fw-semibold" style="color:#8eb922;">Rs. {{ number_format($todayDepositAmount, 2) }}</span>
                            </div>
                            <hr style="border-color: #8eb922;">
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted fw-bold">Remaining Cash:</span>
                                <span class="fw-bold text-success">Rs. {{ number_format($openingCash + $todayCashAmount - $todayExpenses - $todayRefunds - $todaySupplierPayments - $todayDepositAmount, 2) }}</span>
                            </div>
                        </div>

                        {{-- Date & Amount --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold" style="color:#3b5b0c;">Date</label>
                                <input type="date" class="form-control rounded-0" wire:model="depositDate">
                                @error('depositDate') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold" style="color:#3b5b0c;">Amount (Rs.)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light rounded-0">Rs.</span>
                                    <input type="number" step="0.01" class="form-control rounded-0" wire:model="depositAmount" placeholder="0">
                                </div>
                                @error('depositAmount') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="color:#3b5b0c;">Description / Source</label>
                            <input type="text" class="form-control rounded-0" wire:model="depositDescription" placeholder="e.g., POS Sales / Invoice #125">
                            @error('depositDescription') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="alert alert-info rounded-0 border-0 small" role="alert">
                            <i class="bi bi-info-circle me-1"></i>
                            This deposit will be added to your records.
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-0" wire:click="closeAddModal">Cancel</button>
                        <button type="submit" class="btn rounded-0" style="background: linear-gradient(0deg, rgba(59, 91, 12, 1) 0%, rgba(142, 185, 34, 1) 100%); color:white;">
                            <i class="bi bi-check2-circle me-1"></i>Add Deposit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- View Deposit Modal --}}
    @if($showViewModal && $selectedDeposit)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-0">
                <div class="modal-header" style="background: linear-gradient(0deg, rgba(59, 91, 12, 1) 0%, rgba(142, 185, 34, 1) 100%); color:white;">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-eye me-2"></i>Deposit Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeViewModal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-semibold" style="color:#3b5b0c;">Date:</h6>
                            <p>{{ \Carbon\Carbon::parse($selectedDeposit->date)->format('d M Y, l') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-semibold" style="color:#3b5b0c;">Amount:</h6>
                            <p class="fw-bold text-success">Rs. {{ number_format($selectedDeposit->amount, 2) }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="fw-semibold" style="color:#3b5b0c;">Description:</h6>
                            <p>{{ $selectedDeposit->description }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="fw-semibold" style="color:#3b5b0c;">Created:</h6>
                            <p>{{ $selectedDeposit->created_at->format('d/m/Y h:i A') }}</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-0" wire:click="closeViewModal">Close</button>
                    <button class="btn btn-outline-danger rounded-0"
                        wire:click="deleteDeposit({{ $selectedDeposit->id }})"
                        onclick="return confirm('Are you sure you want to delete this deposit?')">
                        <i class="bi bi-trash me-1"></i>Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
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

    .modal.show {
        animation: fadeIn 0.15s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    window.addEventListener('alert', event => {
        if (event.detail.type === 'success') {
            Swal.fire('Success', event.detail.message, 'success');
        } else {
            Swal.fire('Error', event.detail.message, 'error');
        }
    });
</script>
@endpush
