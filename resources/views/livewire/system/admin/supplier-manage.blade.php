<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-people-fill text-success me-2"></i> Supplier Management
            </h3>
            <p class="text-muted mb-0">Manage your suppliers and business contacts efficiently</p>
        </div>
        <div>
            <button class="btn btn-primary" wire:click="createSupplier">
                <i class="bi bi-plus-circle me-2"></i> Add Supplier
            </button>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-list-ul text-primary me-2"></i> Supplier List
                    </h5>
                    <p class="text-muted small mb-0">View and manage all registered suppliers</p>
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
            <div class="card-body p-0 overflow-auto">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Supplier Name</th>
                                <th>Business Name</th>
                                <th>Contact</th>
                                <th>Email</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($suppliers as $supplier)
                            <tr>
                                <td class="ps-4">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="fw-medium text-dark">{{ $supplier->name }}</span>
                                </td>
                                <td>{{ $supplier->businessname }}</td>
                                <td>{{ $supplier->contact }}</td>
                                <td>{{ $supplier->email }}</td>
                                <td class="text-end pe-2">
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary dropdown-toggle"
                                                type="button"
                                                data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                            <i class="bi bi-gear-fill"></i> Actions
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <!-- View Supplier -->
                                            <li>
                                                <button class="dropdown-item"
                                                        wire:click="view({{ $supplier->id }})"
                                                        wire:loading.attr="disabled"
                                                        title="View Supplier Details">
                                                    <span wire:loading wire:target="view({{ $supplier->id }})">
                                                        <i class="spinner-border spinner-border-sm me-2"></i> Loading...
                                                    </span>
                                                    <span wire:loading.remove wire:target="view({{ $supplier->id }})">
                                                        <i class="bi bi-eye text-info me-2"></i> View
                                                    </span>
                                                </button>
                                            </li>

                                            <!-- Edit Supplier -->
                                            <li>
                                                <button class="dropdown-item"
                                                        wire:click="edit({{ $supplier->id }})"
                                                        wire:loading.attr="disabled"
                                                        title="Edit Supplier">
                                                    <span wire:loading wire:target="edit({{ $supplier->id }})">
                                                        <i class="spinner-border spinner-border-sm me-2"></i> Loading...
                                                    </span>
                                                    <span wire:loading.remove wire:target="edit({{ $supplier->id }})">
                                                        <i class="bi bi-pencil text-primary me-2"></i> Edit
                                                    </span>
                                                </button>
                                            </li>

                                            <!-- Delete Supplier -->
                                            <li>
                                                <button class="dropdown-item"
                                                        wire:click="confirmDelete({{ $supplier->id }})"
                                                        wire:loading.attr="disabled"
                                                        title="Delete Supplier">
                                                    <span wire:loading wire:target="confirmDelete({{ $supplier->id }})">
                                                        <i class="spinner-border spinner-border-sm me-2"></i> Loading...
                                                    </span>
                                                    <span wire:loading.remove wire:target="confirmDelete({{ $supplier->id }})">
                                                        <i class="bi bi-trash text-danger me-2"></i> Delete
                                                    </span>
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No suppliers found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-center">
                        {{ $suppliers->links('livewire.custom-pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Supplier Modal -->
    @if($showCreateModal)
    <div class="modal fade show d-block" tabindex="-1" aria-labelledby="createSupplierModalLabel" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form wire:submit.prevent="save">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-plus-circle text-white me-2"></i> Create Supplier
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Supplier Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.blur="name" placeholder="Enter supplier name">
                                @error('name') 
                                    <div class="invalid-feedback d-block">{{ $message }}</div> 
                                @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Business Name</label>
                                <input type="text" class="form-control @error('businessname') is-invalid @enderror" wire:model.blur="businessname" placeholder="Enter business name">
                                @error('businessname') 
                                    <div class="invalid-feedback d-block">{{ $message }}</div> 
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Contact Number</label>
                                <input type="text" class="form-control @error('contact') is-invalid @enderror" wire:model.blur="contact" placeholder="Enter contact number">
                                @error('contact') 
                                    <div class="invalid-feedback d-block">{{ $message }}</div> 
                                @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model.blur="email" placeholder="Enter email">
                                @error('email') 
                                    <div class="invalid-feedback d-block">{{ $message }}</div> 
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" wire:model.blur="phone" placeholder="Enter phone number">
                                @error('phone') 
                                    <div class="invalid-feedback d-block">{{ $message }}</div> 
                                @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" wire:model="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                @error('status') 
                                    <div class="invalid-feedback d-block">{{ $message }}</div> 
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" wire:model.blur="address" rows="2" placeholder="Enter address"></textarea>
                            @error('address') 
                                <div class="invalid-feedback d-block">{{ $message }}</div> 
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" wire:model.blur="notes" rows="2" placeholder="Additional notes (optional)"></textarea>
                            @error('notes') 
                                <div class="invalid-feedback d-block">{{ $message }}</div> 
                            @enderror
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2-circle me-1"></i> Save Supplier
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- View Supplier Modal -->
    @if($showViewModal)
    <div class="modal fade show d-block" tabindex="-1" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-eye text-white me-2"></i> Supplier Details
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-muted">Supplier Name</label>
                            <p class="form-control-plaintext fw-medium">{{ $name }}</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-muted">Business Name</label>
                            <p class="form-control-plaintext">{{ $businessname ?: 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-muted">Contact Number</label>
                            <p class="form-control-plaintext">{{ $contact ?: 'N/A' }}</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-muted">Email</label>
                            <p class="form-control-plaintext">{{ $email ?: 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-muted">Phone</label>
                            <p class="form-control-plaintext">{{ $phone ?: 'N/A' }}</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-muted">Status</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-{{ $status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted">Address</label>
                        <p class="form-control-plaintext">{{ $address ?: 'N/A' }}</p>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-muted">Notes</label>
                        <p class="form-control-plaintext">{{ $notes ?: 'No notes available' }}</p>
                    </div>
                    <div class="d-grid">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="bi bi-x-circle me-1"></i> Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Edit Supplier Modal -->
    @if($showEditModal)
    <div class="modal fade show d-block" tabindex="-1" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form wire:submit.prevent="updateSupplier">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-pencil-square text-white me-2"></i> Edit Supplier
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Supplier Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.blur="name">
                                @error('name') 
                                    <div class="invalid-feedback d-block">{{ $message }}</div> 
                                @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Business Name</label>
                                <input type="text" class="form-control @error('businessname') is-invalid @enderror" wire:model.blur="businessname">
                                @error('businessname') 
                                    <div class="invalid-feedback d-block">{{ $message }}</div> 
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Contact Number</label>
                                <input type="text" class="form-control @error('contact') is-invalid @enderror" wire:model.blur="contact">
                                @error('contact') 
                                    <div class="invalid-feedback d-block">{{ $message }}</div> 
                                @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model.blur="email">
                                @error('email') 
                                    <div class="invalid-feedback d-block">{{ $message }}</div> 
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" wire:model.blur="phone">
                                @error('phone') 
                                    <div class="invalid-feedback d-block">{{ $message }}</div> 
                                @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" wire:model="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                @error('status') 
                                    <div class="invalid-feedback d-block">{{ $message }}</div> 
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" wire:model.blur="address" rows="2"></textarea>
                            @error('address') 
                                <div class="invalid-feedback d-block">{{ $message }}</div> 
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" wire:model.blur="notes" rows="2"></textarea>
                            @error('notes') 
                                <div class="invalid-feedback d-block">{{ $message }}</div> 
                            @enderror
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2-circle me-1"></i> Update Supplier
                            </button>
                        </div>
                    </div>
                </form>
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
        background-color: #f5fdf1ff;
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

    .form-control-plaintext {
        padding: 0.5rem 0;
        border: none;
        background: transparent;
    }

    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }

    .is-invalid {
        border-color: #dc3545;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6.4.4.4-.4'/%3e%3cpath d='M6 7v2'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    .is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        // Toast notifications
        Livewire.on('show-toast', ([type, message]) => {
            Swal.fire({
                title: type === 'success' ? 'Success!' : 'Error!',
                text: message,
                icon: type,
                timer: 2000,
                showConfirmButton: false
            });
        });
        
        Livewire.on('refreshPage', () => {
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        });
        
        // Delete confirmation
        Livewire.on('swal:confirm', ([data]) => {
            Swal.fire({
                title: data.title || 'Are you sure?',
                text: data.text || 'You won\'t be able to revert this!',
                icon: data.icon || 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('delete-supplier', {
                        id: data.id
                    });
                }
            });
        });
    });
</script>
@endpush
