<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-pie-chart-fill text-success me-2"></i> Expense Management
            </h3>
            <p class="text-muted mb-0">Track and manage your company expenses efficiently</p>
        </div>
       
    </div>

    @if (session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif 

    <!-- Summary Cards -->
    <div class="row mb-5">
        <!-- Today's Expenses -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card summary-card today h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-container bg-success bg-opacity-10 me-3">
                            <i class="bi bi-calendar-day text-success fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Today's Expenses</p>
                            <h4 class="fw-bold mb-0">Rs.{{ number_format($todayTotal, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- This Month's Expenses -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card summary-card month h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-container bg-info bg-opacity-10 me-3">
                            <i class="bi bi-calendar3 text-info fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">This Month's Expenses</p>
                            <h4 class="fw-bold mb-0">Rs.{{ number_format($monthTotal, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Expenses -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card summary-card total h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-container bg-primary bg-opacity-10 me-3">
                            <i class="bi bi-cash-coin text-primary fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Expenses</p>
                            <h4 class="fw-bold mb-0">Rs.{{ number_format($overallTotal, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Split Tables -->
    <div class="row g-4">
        <!-- Daily Expenses -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">
                            <i class="bi bi-journal-text text-primary me-2"></i> Daily Expenses
                        </h5>
                        <p class="text-muted small mb-0">Small daily costs like snacks, printouts, or stationery</p>
                    </div>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDailyExpenseModal">
                        <i class="bi bi-plus-lg me-1"></i> Add
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($dailyExpenses as $expense)
                                <tr>
                                    <td class="ps-4">{{ \Carbon\Carbon::parse($expense->date)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="fw-medium text-dark">{{ $expense->category }}</span>
                                    </td>
                                    <td>{{ $expense->description ?? '—' }}</td>
                                    <td>
                                        <span class="fw-bold text-dark">Rs.{{ number_format($expense->amount, 2) }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="text-danger me-2 bg-opacity-0 border-0" wire:click="confirmDelete({{ $expense->id }}) " wire:loading.attr="disabled" title="Delete">
                                            <i class="bi bi-trash fs-6"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No daily expenses found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Expenses -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">
                            <i class="bi bi-calendar2-check text-info me-2"></i> Monthly Expenses
                        </h5>
                        <p class="text-muted small mb-0">Regular monthly costs like bills, rent, and subscriptions</p>
                    </div>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMonthlyExpenseModal">
                        <i class="bi bi-plus-lg me-1"></i> Add
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($monthlyExpenses as $expense)
                                <tr>
                                    <td class="ps-4">{{ \Carbon\Carbon::parse($expense->date)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="fw-medium text-dark">{{ $expense->category }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark">Rs.{{ number_format($expense->amount, 2) }}</span>
                                    </td>
                                    <td>
                                        @if($expense->status == 'Paid')
                                        <span class="badge bg-success">Paid</span>
                                        @elseif($expense->status == 'Pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                        @else
                                        <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="text-danger me-2 bg-opacity-0 border-0" wire:click="confirmDelete({{ $expense->id }})" wire:loading.attr="disabled" title="Delete">
                                            <i class="bi bi-trash fs-6"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No monthly expenses found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Daily Expense Modal -->
    <div class="modal fade" id="addDailyExpenseModal" tabindex="-1" aria-labelledby="addDailyExpenseModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-plus-circle text-white me-2"></i> Add Daily Expense
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveDailyExpense">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Date</label>
                            <input type="date" class="form-control" wire:model="date" required>
                            @error('date') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category</label>
                            <select class="form-select" wire:model="category" required>
                                <option value="">Select Category</option>
                                @foreach($dailyCategories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                            @error('category') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" wire:model="description" rows="2" placeholder="Add description..."></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" step="0.01" class="form-control" wire:model="amount" placeholder="e.g., 100" required>
                            </div>
                            @error('amount') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <i class="bi bi-check2-circle me-1"></i>
                                <span wire:loading.remove>Save Expense</span>
                                <span wire:loading>Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Monthly Expense Modal -->
    <div class="modal fade" id="addMonthlyExpenseModal" tabindex="-1" aria-labelledby="addMonthlyExpenseModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-plus-circle-fill text-white me-2"></i> Add Monthly Expense
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveMonthlyExpense">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Date</label>
                            <input type="date" class="form-control" wire:model="date" required>
                            @error('date') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category</label>
                            <select class="form-select" wire:model="category" required>
                                <option value="">Select Category</option>
                                @foreach($monthlyCategories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                            @error('category') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" step="0.01" class="form-control" wire:model="amount" placeholder="e.g., 500" required>
                            </div>
                            @error('amount') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Status</label>
                            <select class="form-select" wire:model="status">
                                <option value="">Select Status</option>
                                <option value="Paid">Paid</option>
                                <option value="Pending">Pending</option>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-info" wire:loading.attr="disabled">
                                <i class="bi bi-check2-circle me-1"></i>
                                <span wire:loading.remove>Save Expense</span>
                                <span wire:loading>Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Daily Expense Modal -->
    @if($showEditDailyModal)
    <div class="modal fade show d-block" tabindex="-1" aria-labelledby="editDailyExpenseModalLabel" aria-hidden="false" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square text-white me-2"></i> Edit Daily Expense
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeEditDailyModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="updateExpense">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category</label>
                            <select class="form-select" wire:model="edit_category" required>
                                <option value="">Select Category</option>
                                @foreach($dailyCategories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                            @error('edit_category') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" wire:model="edit_description" rows="2" placeholder="Add description...">{{ $edit_description }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" step="0.01" class="form-control" wire:model="edit_amount" required>
                            </div>
                            @error('edit_amount') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <i class="bi bi-check2-circle me-1"></i>
                                <span wire:loading.remove>Update Expense</span>
                                <span wire:loading>Updating...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Edit Monthly Expense Modal -->
    @if($showEditMonthlyModal)
    <div class="modal fade show d-block" tabindex="-1" aria-labelledby="editMonthlyExpenseModalLabel" aria-hidden="false" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square text-white me-2"></i> Edit Monthly Expense
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeEditMonthlyModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="updateExpense">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Date</label>
                            <input type="date" class="form-control" wire:model="edit_date" required>
                            @error('edit_date') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category</label>
                            <select class="form-select" wire:model="edit_category" required>
                                <option value="">Select Category</option>
                                @foreach($monthlyCategories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                            @error('edit_category') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" step="0.01" class="form-control" wire:model="edit_amount" required>
                            </div>
                            @error('edit_amount') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select class="form-select" wire:model="edit_status">
                                <option value="">Select Status</option>
                                <option value="Paid">Paid</option>
                                <option value="Pending">Pending</option>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-info" wire:loading.attr="disabled">
                                <i class="bi bi-check2-circle me-1"></i>
                                <span wire:loading.remove>Update Expense</span>
                                <span wire:loading>Updating...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- View Expense Modal -->
    @if($showViewModal && $viewExpense)
    <div class="modal fade show d-block" tabindex="-1" aria-labelledby="viewExpenseModalLabel" aria-hidden="false" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-eye-fill text-info me-2"></i> Expense Details
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Expense Type -->
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                                <span class="fw-semibold">Expense Type:</span>
                                <span class="badge bg-{{ $viewExpense->expense_type === 'daily' ? 'primary' : 'info' }}">
                                    {{ ucfirst($viewExpense->expense_type) }}
                                </span>
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <small class="text-muted d-block">Date</small>
                                <strong>{{ $viewExpense->date ? \Carbon\Carbon::parse($viewExpense->date)->format('d/m/Y') : 'N/A' }}</strong>
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <small class="text-muted d-block">Category</small>
                                <strong>{{ $viewExpense->category }}</strong>
                            </div>
                        </div>

                        <!-- Amount -->
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <small class="text-muted d-block">Amount</small>
                                <strong class="text-success">Rs.{{ number_format($viewExpense->amount, 2) }}</strong>
                            </div>
                        </div>

                        <!-- Status (for monthly expenses) -->
                        @if($viewExpense->expense_type === 'monthly')
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <small class="text-muted d-block">Status</small>
                                @if($viewExpense->status == 'Paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($viewExpense->status == 'Pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @else
                                    <span class="badge bg-secondary">N/A</span>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Description -->
                        <div class="col-12">
                            <div class="p-3 border rounded">
                                <small class="text-muted d-block">Description</small>
                                <strong>{{ $viewExpense->description ?: 'No description provided' }}</strong>
                            </div>
                        </div>

                        <!-- Created At -->
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <small class="text-muted d-block">Created</small>
                                <small>{{ $viewExpense->created_at->format('d/m/Y h:i A') }}</small>
                            </div>
                        </div>

                        <!-- Last Updated -->
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <small class="text-muted d-block">Last Updated</small>
                                <small>{{ $viewExpense->updated_at->format('d/m/Y h:i A') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeViewModal">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="editExpense({{ $viewExpense->id }})">
                        <i class="bi bi-pencil me-1"></i> Edit Expense
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
    <div class="modal fade show d-block" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="false" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-white ">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" wire:click="cancelDelete"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="bi bi-trash-fill text-danger fs-1 mb-3 d-block"></i>
                    <h5 class="fw-bold mb-3">Are you sure?</h5>
                    <p class="text-muted">You are about to delete this expense. This action cannot be undone.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" wire:click="cancelDelete">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteExpense" wire:loading.attr="disabled">
                        <i class="bi bi-trash me-1"></i>
                        <span wire:loading.remove>Delete Expense</span>
                        <span wire:loading>Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
    .summary-card {
        border-left: 4px solid;
        transition: all 0.3s ease;
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
    }

    .summary-card.today {
        border-left-color: #4cc9f0;
    }

    .summary-card.month {
        border-left-color: #4895ef;
    }

    .summary-card.total {
        border-left-color: #4361ee;
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

    .btn-link.text-info:hover {
        color: #0dcaf0 !important;
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

    .btn-info {
        background-color: #4895ef;
        border-color: #4895ef;
    }

    .btn-info:hover {
        background-color: #3a7bd5;
        border-color: #3a7bd5;
        transform: translateY(-2px);
    }

    .loading-indicator {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 2px solid #ffffff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 1s ease-in-out infinite;
    }

    .modal-body .border {
        border-color: #e9ecef !important;
    }

    .modal-body .bg-light {
        background-color: #f8f9fa !important;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        // Handle close modal events for Bootstrap modals (add modals)
        Livewire.on('close-modal', (modalId) => {
            const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
            if (modal) {
                modal.hide();
            }
        });
        
        Livewire.on('refreshPage', () => {
            setTimeout(() => {
                window.location.reload();
            }, 1500); // Refresh after 1.5 seconds to show success message
        });

        // Reset form fields when add modals are hidden
        const addModals = ['addDailyExpenseModal', 'addMonthlyExpenseModal'];
        
        addModals.forEach(modalId => {
            const modalElement = document.getElementById(modalId);
            if (modalElement) {
                modalElement.addEventListener('hidden.bs.modal', function () {
                    // Reset form fields when modal is closed
                    resetFields();
                });
            }
        });
    });

    // Close modals when clicking on backdrop
    document.addEventListener('click', function(event) {
        const editModals = document.querySelectorAll('.modal[wire\\:id]');
        editModals.forEach(modal => {
            if (event.target === modal) {
                call('closeViewModal');
                call('closeEditDailyModal');
                call('closeEditMonthlyModal');
                call('cancelDelete');
            }
        });
    });
</script>
@endpush
