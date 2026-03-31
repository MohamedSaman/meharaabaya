<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-people-fill text-success me-2"></i> Manage Customers
            </h3>
            <p class="text-muted mb-0">Manage all customer information efficiently</p>
        </div>
        <div>
            <button class="btn btn-primary" wire:click="createCustomer">
                <i class="bi bi-plus-lg me-2"></i> Create Customer
            </button>
        </div>
    </div>

    @if (session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if (session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Customer List --}}
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-dark mb-1">
                    <i class="bi bi-journal-text text-primary me-2"></i> Customer List
                </h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0" placeholder="Search customers..." wire:model.live="search">
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="text-sm text-muted fw-medium mb-0">Show</label>
                    <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 80px;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                        <option value="500">500</option>
                    </select>
                    <span class="text-sm text-muted mb-0">entries</span>
                </div>
            </div>
        </div>
        <div class="card-body p-0 overflow-auto">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Customer Name</th>
                            <th>Business Name</th>
                            <th>Contact Number</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>Address</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($customers->count() > 0)
                            @foreach ($customers as $customer)
                            <tr>
                                <td class="ps-4">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="fw-medium text-dark">{{ $customer->name ?? '-' }}</span>
                                </td>
                                <td>{{ $customer->business_name ?? '-' }}</td>
                                <td>{{ $customer->phone ?? '-' }}</td>
                                <td>{{ $customer->email ?? '-' }}</td>
                                <td>
                                    @if($customer->type == 'retail')
                                    <span class="badge bg-success">Retail</span>
                                    @elseif($customer->type == 'wholesale')
                                    <span class="badge bg-info">Wholesale</span>
                                    @else
                                    <span class="badge bg-secondary">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $customer->address ?? '-' }}</td>
                                <td class="text-end pe-2">
    <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false">
            <i class="bi bi-gear-fill"></i> Actions
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <!-- View Customer -->
            <li>
                <button class="dropdown-item"
                        wire:click="viewDetails({{ $customer->id }})"
                        wire:loading.attr="disabled"
                        title="View Details">
                    <span wire:loading wire:target="viewDetails({{ $customer->id }})">
                        <i class="spinner-border spinner-border-sm me-2"></i> Loading...
                    </span>
                    <span wire:loading.remove wire:target="viewDetails({{ $customer->id }})">
                        <i class="bi bi-eye text-info me-2"></i> View
                    </span>
                </button>
            </li>

            <!-- Transaction History -->
            <li>
                <a class="dropdown-item" href="{{ route((auth()->user()->role === 'admin' ? 'admin.' : 'staff.') . 'customer-transactions', $customer->id) }}" wire:navigate>
                    <i class="bi bi-clock-history text-secondary me-2"></i> Transaction History
                </a>
            </li>

            <!-- Edit Customer -->
            <li>
                <button class="dropdown-item"
                        wire:click="editCustomer({{ $customer->id }})"
                        wire:loading.attr="disabled"
                        title="Edit Customer">
                    <span wire:loading wire:target="editCustomer({{ $customer->id }})">
                        <i class="spinner-border spinner-border-sm me-2"></i> Loading...
                    </span>
                    <span wire:loading.remove wire:target="editCustomer({{ $customer->id }})">
                        <i class="bi bi-pencil text-primary me-2"></i> Edit
                    </span>
                </button>
            </li>

            <!-- Delete Customer -->
            <li>
                <button class="dropdown-item"
                        wire:click="confirmDelete({{ $customer->id }})"
                        wire:loading.attr="disabled"
                        title="Delete Customer">
                    <span wire:loading wire:target="confirmDelete({{ $customer->id }})">
                        <i class="spinner-border spinner-border-sm me-2"></i> Loading...
                    </span>
                    <span wire:loading.remove wire:target="confirmDelete({{ $customer->id }})">
                        <i class="bi bi-trash text-danger me-2"></i> Delete
                    </span>
                </button>
            </li>
        </ul>
    </div>
</td>

                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-people display-4 d-block mb-2"></i>
                                    No customers found
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-center">
                    {{ $customers->links('livewire.custom-pagination') }}
                </div>
            </div>
        </div>
    </div>

 {{-- Create Customer Modal --}}
@if($showCreateModal)
<div class="modal fade show d-block" tabindex="-1" aria-labelledby="createCustomerModalLabel" aria-hidden="false" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-plus-circle text-white me-2"></i> Create Customer
                </h5>
                <button type="button" class="btn-close" wire:click="closeModal"></button>
            </div>
            <div class="modal-body">
                <form wire:submit.prevent="saveCustomer">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="mb-1">
                                <label class="form-label fw-semibold">Customer Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       wire:model="name" placeholder="Enter customer name" required>
                                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-1">
                                <label class="form-label fw-semibold">Contact Number</label>
                                <input type="text" class="form-control @error('contactNumber') is-invalid @enderror" 
                                       wire:model="contactNumber" placeholder="Enter contact number" >
                                @error('contactNumber') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="mb-1">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       wire:model="email" placeholder="Enter email">
                                @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-1">
                                <label class="form-label fw-semibold">Business Name</label>
                                <input type="text" class="form-control @error('businessName') is-invalid @enderror" 
                                       wire:model="businessName" placeholder="Enter Business Name">
                                @error('businessName') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="mb-1">
                                <label class="form-label fw-semibold">Customer Type</label>
                                <select class="form-select @error('customerType') is-invalid @enderror" wire:model="customerType" >
                                    <option value="">Select customer type</option>
                                    <option value="retail">Retail</option>
                                    <option value="wholesale">Wholesale</option>
                                </select>
                                @error('customerType') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-1">
                                <label class="form-label fw-semibold">Address</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                       wire:model="address" placeholder="Enter address">
                                @error('address') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <i class="bi bi-check2-circle me-1"></i>
                            <span wire:loading.remove>Save Customer</span>
                            <span wire:loading>Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

    {{-- Edit Customer Modal --}}
    @if($showEditModal)
    <div class="modal fade show d-block" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="false" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square text-white me-2"></i> Edit Customer
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="updateCustomer">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Customer Name</label>
                                    <input type="text" class="form-control @error('editName') is-invalid @enderror" 
                                           wire:model="editName" required>
                                    @error('editName') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Contact Number</label>
                                    <input type="text" class="form-control @error('editContactNumber') is-invalid @enderror" 
                                           wire:model="editContactNumber" required>
                                    @error('editContactNumber') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Email</label>
                                    <input type="email" class="form-control @error('editEmail') is-invalid @enderror" 
                                           wire:model="editEmail">
                                    @error('editEmail') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Business Name</label>
                                    <input type="text" class="form-control @error('editBusinessName') is-invalid @enderror" 
                                           wire:model="editBusinessName">
                                    @error('editBusinessName') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Customer Type</label>
                                    <select class="form-select @error('editCustomerType') is-invalid @enderror" wire:model="editCustomerType" required>
                                        <option value="retail">Retail</option>
                                        <option value="wholesale">Wholesale</option>
                                    </select>
                                    @error('editCustomerType') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Address</label>
                                    <input type="text" class="form-control @error('editAddress') is-invalid @enderror" 
                                           wire:model="editAddress">
                                    @error('editAddress') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <i class="bi bi-check2-circle me-1"></i>
                                <span wire:loading.remove>Update Customer</span>
                                <span wire:loading>Updating...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- View Details Modal --}}
    @if($showViewModal)
    <div class="modal fade show d-block" tabindex="-1" aria-labelledby="viewDetailsModalLabel" aria-hidden="false" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-badge text-white me-2"></i> Customer Details
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-0">
                        <div class="col-md-4 d-flex flex-column align-items-center justify-content-center p-4 border-end bg-light rounded-start">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mb-3" 
                                 style="width: 100px; height: 100px;">
                                <i class="bi bi-person-fill text-white fs-1"></i>
                            </div>
                            <span class="fw-bold fs-5 text-dark">{{ $viewCustomerDetail['name'] ?? '-' }}</span>
                            <span class="text-muted text-capitalize">{{ $viewCustomerDetail['type'] ?? '-' }} Customer</span>
                        </div>
                        <div class="col-md-8 p-4">
                            <div class="mb-4 pb-3 border-bottom">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="bi bi-person-lines-fill me-1"></i> Personal Information
                                </h6>
                                <div class="row">
                                    <div class="col-4 text-muted fw-semibold mb-2">Contact:</div>
                                    <div class="col-8 mb-2">{{ $viewCustomerDetail['phone'] ?? '-' }}</div>
                                    
                                    <div class="col-4 text-muted fw-semibold mb-2">Email:</div>
                                    <div class="col-8 mb-2">{{ $viewCustomerDetail['email'] ?? '-' }}</div>
                                    
                                    <div class="col-4 text-muted fw-semibold mb-2">Business Name:</div>
                                    <div class="col-8 mb-2">{{ $viewCustomerDetail['business_name'] ?? '-' }}</div>
                                    
                                    <div class="col-4 text-muted fw-semibold mb-2">Customer Type:</div>
                                    <div class="col-8 mb-2">
                                        @if(($viewCustomerDetail['type'] ?? '') == 'retail')
                                            <span class="badge bg-success">Retail</span>
                                        @elseif(($viewCustomerDetail['type'] ?? '') == 'wholesale')
                                            <span class="badge bg-info">Wholesale</span>
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4 pb-3 border-bottom">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="bi bi-geo-alt me-1"></i> Address Information
                                </h6>
                                <div class="row">
                                    <div class="col-12">
                                        <p class="mb-0">{{ $viewCustomerDetail['address'] ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="bi bi-clock-history me-1"></i> Account Information
                                </h6>
                                <div class="row">
                                    <div class="col-4 text-muted fw-semibold mb-2">Created:</div>
                                    <div class="col-8 mb-2">
                                        {{ $viewCustomerDetail['created_at'] ? \Carbon\Carbon::parse($viewCustomerDetail['created_at'])->format('d/m/Y h:i A') : '-' }}
                                    </div>
                                    
                                    <div class="col-4 text-muted fw-semibold mb-2">Last Updated:</div>
                                    <div class="col-8 mb-2">
                                        {{ $viewCustomerDetail['updated_at'] ? \Carbon\Carbon::parse($viewCustomerDetail['updated_at'])->format('d/m/Y h:i A') : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
    <div class="modal fade show d-block" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="false" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" wire:click="cancelDelete"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="bi bi-person-x text-danger fs-1 mb-3 d-block"></i>
                    <h5 class="fw-bold mb-3">Are you sure?</h5>
                    <p class="text-muted">You are about to delete this customer. This action cannot be undone.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" wire:click="cancelDelete">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteCustomer" wire:loading.attr="disabled">
                        <i class="bi bi-trash me-1"></i>
                        <span wire:loading.remove>Delete Customer</span>
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

    .btn-danger {
        background-color: #e63946;
        border-color: #e63946;
    }

    .btn-danger:hover {
        background-color: #d00000;
        border-color: #d00000;
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

    /* Additional styles for view modal */
    .bg-light {
        background-color: #f8f9fa !important;
    }

    .rounded-start {
        border-top-left-radius: 12px !important;
        border-bottom-left-radius: 12px !important;
    }

    .text-capitalize {
        text-transform: capitalize;
    }

    .bg-primary {
        background-color: #4361ee !important;
    }

    .border-bottom {
        border-bottom: 1px solid #dee2e6 !important;
    }

    .pb-3 {
        padding-bottom: 1rem !important;
    }

    .mb-4 {
        margin-bottom: 1.5rem !important;
    }
</style>
@endpush
