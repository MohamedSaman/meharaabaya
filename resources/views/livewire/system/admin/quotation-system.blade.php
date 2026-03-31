<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="fw-bold text-dark mb-2">
                        <i class="bi bi-file-earmark-text text-success me-2"></i> 
                        {{ $isEditMode ? 'Edit Quotation' : 'Create Quotation' }}
                    </h3>
                    <p class="text-muted">
                        {{ $isEditMode ? 'Modify existing quotation details and items' : 'Quickly create professional quotations for customers' }}
                    </p>
                </div>
                @if($isEditMode)
                <div>
                    <a href="{{ route('admin.quotation-list') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to List
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if (session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-exclamation-circle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if (session()->has('message'))
    <div class="alert alert-info alert-dismissible fade show mb-4">
        <i class="bi bi-info-circle me-2"></i> {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        {{-- Customer Information --}}
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-1">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="bi bi-person me-2 text-primary"></i> Customer Information
                    </h5>
                    <button class="btn btn-sm btn-primary" wire:click="openCustomerModal">
                        <i class="bi bi-plus-circle me-1"></i> Add New Customer
                    </button>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        {{-- Select Customer --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Select Customer *</label>
                            <select class="form-select shadow-sm" wire:model.live="customerId">
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ $customer->name === 'Walking Customer' ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                        @if($customer->phone)
                                            - {{ $customer->phone }}
                                        @endif
                                        @if($customer->name === 'Walking Customer')
                                            (Default)
                                        @endif
                                    </option>
                                @endforeach
                            </select>

                            <div class="form-text mt-2">
                                @if($selectedCustomer && $selectedCustomer->name === 'Walking Customer')
                                    <span class="text-info">
                                        <i class="bi bi-info-circle me-1"></i> Using default walking customer
                                    </span>
                                @else
                                    Select an existing customer or add a new one.
                                @endif
                            </div>
                        </div>

                        {{-- Valid Until --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Valid Until *</label>
                            <input type="date" class="form-control shadow-sm" wire:model="validUntil">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Add Products --}}
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-1">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="bi bi-search me-2 text-success"></i> Add Products
                    </h5>
                </div>

                <div class="card-body">
                    {{-- Search Field --}}
                    <div class="mb-3">
                        <input type="text" class="form-control shadow-sm"
                            wire:model.live="search"
                            placeholder="Search by product name, code, or model...">
                    </div>

                    {{-- Search Results --}}
                    @if($search && count($searchResults) > 0)
                        <div class="search-results mt-1 position-absolute w-100 shadow-lg" style="max-height: 300px; max-width: 96%; z-index: 1055;">
                            @foreach($searchResults as $product)
                                <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-white rounded-1"
                                    wire:key="product-{{ $product['id'] }}">
                                    <div>
                                        <h6 class="mb-1 fw-semibold">{{ $product['name'] }}</h6>
                                        <p class="text-muted small mb-0">
                                            Code: {{ $product['code'] }} | Model: {{ $product['model'] }}
                                        </p>
                                        <p class="text-success small mb-0">
                                            Rs.{{ number_format($product['price'], 2) }} | Stock: {{ $product['stock'] }}
                                        </p>
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary"
                                        wire:click="addToCart({{ json_encode($product) }})">
                                        <i class="bi bi-plus-lg"></i> Add
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @elseif($search)
                        <div class="text-center text-muted p-3">
                            <i class="bi bi-exclamation-circle me-1"></i> No products found
                        </div>
                    @endif
                </div>
            </div>
        </div>


        {{-- Quotation Items Table --}}
        <div class="col-md-12 mb-4 overflow-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-cart me-2"></i>Quotation Items
                    </h5>
                    <span class="badge bg-primary">{{ count($cart) }} items</span>
                </div>
                <div class="card-body p-0">
                    @if(count($cart) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="30">#</th>
                                    <th>Product</th>
                                    <th width="120">Unit Price</th>
                                    <th width="150">Quantity</th>
                                    <th width="120">Discount/Unit</th>
                                    <th width="120">Total</th>
                                    <th width="100" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cart as $index => $item)
                                <tr wire:key="cart-item-{{ $index }}-{{ $item['code'] }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div>
                                            <strong>{{ $item['name'] }}</strong>
                                            <div class="text-muted small">
                                                {{ $item['code'] }} | {{ $item['model'] }}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm text-primary" min="0" step="0.01"
                                            wire:change="updateUnitPrice({{ $index }}, $event.target.value)"
                                            value="{{ $item['price'] }}">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <button class="btn btn-outline-secondary" type="button"
                                                wire:click="decrementQuantity({{ $index }})">-</button>
                                            <input type="number" class="form-control text-center"
                                                wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                                value="{{ $item['quantity'] }}" min="1">
                                            <button class="btn btn-outline-secondary" type="button"
                                                wire:click="incrementQuantity({{ $index }})">+</button>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm text-danger"
                                            wire:change="updateDiscount({{ $index }}, $event.target.value)"
                                            value="{{ $item['discount'] }}" min="0" step="0.01"
                                            placeholder="0">
                                    </td>
                                    <td class="fw-bold">
                                        Rs.{{ number_format($item['total'], 2) }}
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-danger"
                                            wire:click="removeFromCart({{ $index }})"
                                            title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="5" class="text-end fw-bold">Subtotal:</td>
                                    <td class="fw-bold">Rs.{{ number_format($subtotal, 2) }}</td>
                                    <td></td>
                                </tr>
                               
                               

                                {{-- Additional Discount Section --}}
                                <tr>
                                    <td colspan="3" class="text-end fw-bold align-middle">
                                        Additional Discount:
                                        @if($additionalDiscount > 0)
                                        <button type="button" class=" btn-link text-danger me-2 p-0 border-0"
                                            wire:click="removeAdditionalDiscount" title="Remove discount">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                        @endif
                                    </td>
                                    <td colspan="2">
                                        <div class="input-group input-group-sm">
                                            <input type="number"
                                                class="form-control form-control-sm text-danger"
                                                wire:model.live="additionalDiscount"
                                                min="0"
                                                step="{{ $additionalDiscountType === 'percentage' ? '1' : '0.01' }}"
                                                @if($additionalDiscountType==='percentage') max="100" @endif
                                                placeholder="0{{ $additionalDiscountType === 'percentage' ? '' : '.00' }}">

                                            <span class="input-group-text">
                                                {{ $additionalDiscountType === 'percentage' ? '%' : 'Rs.' }}
                                            </span>

                                            <button type="button"
                                                class="btn btn-outline-secondary"
                                                wire:click="toggleDiscountType"
                                                title="Switch Discount Type">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="fw-bold text-danger">
                                        @if($additionalDiscount > 0)
                                        - Rs.{{ number_format($additionalDiscountAmount, 2) }}
                                        @if($additionalDiscountType === 'percentage')
                                        <div class="text-muted small">({{ $additionalDiscount }}%)</div>
                                        @endif
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td></td>
                                </tr>

                                {{-- Grand Total --}}
                                <tr>
                                    <td colspan="5" class="text-end fw-bold fs-5">Grand Total:</td>
                                    <td class="fw-bold fs-5 text-primary">Rs.{{ number_format($grandTotal, 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @else
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-cart display-4 d-block mb-2"></i>
                        No items added yet
                    </div>
                    @endif
                </div>
                @if(count($cart) > 0)
                <div class="card-footer">
                    <button class="btn btn-danger" wire:click="clearCart">
                        <i class="bi bi-trash me-2"></i>Clear All Items
                    </button>
                </div>
                @endif
            </div>
        </div>

        {{-- Notes and Terms & Conditions --}}  
        <div class="col-md-12 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list-check me-2"></i>Terms & Conditions
                    </h5>
                </div>
                <div class="card-body">
                    <textarea class="form-control" wire:model="termsConditions" rows="6"
                        placeholder="Enter terms and conditions for the quotation..."></textarea>
                </div>
            </div>
        </div>
    
        

        {{-- Create/Update Quotation Button --}}
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <button class="btn btn-primary btn-lg px-5" wire:click="createQuotation"
                        {{ count($cart) == 0 ? 'disabled' : '' }}>
                        <i class="bi bi-{{ $isEditMode ? 'check-circle' : 'file-earmark-plus' }} me-2"></i>
                        {{ $isEditMode ? 'Update Quotation' : 'Create Quotation' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Customer Modal --}}
    @if($showCustomerModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus me-2"></i>Add New Customer
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeCustomerModal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name *</label>
                            <input type="text" class="form-control" wire:model="customerName" placeholder="Enter customer name">
                            @error('customerName') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone *</label>
                            <input type="text" class="form-control" wire:model="customerPhone" placeholder="Enter phone number">
                            @error('customerPhone') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" wire:model="customerEmail" placeholder="Enter email address">
                            @error('customerEmail') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Customer Type *</label>
                            <select class="form-select" wire:model="customerType">
                                <option value="retail">Retail</option>
                                <option value="wholesale">Wholesale</option>
                             
                            </select>
                            @error('customerType') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Business Name</label>
                            <input type="text" class="form-control" wire:model="businessName" placeholder="Enter business name">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address *</label>
                            <textarea class="form-control" wire:model="customerAddress" rows="3" placeholder="Enter address"></textarea>
                            @error('customerAddress') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeCustomerModal">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="createCustomer">
                        <i class="bi bi-check-circle me-2"></i>Create Customer
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

   {{-- Quotation Preview Modal --}}
    @if($showQuotationModal && $createdQuotation)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        Quotation Preview - {{ $createdQuotation->quotation_number }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="createNewQuotation"></button>
                </div>
                <div class="modal-body p-0">
                    {{-- Quotation Preview --}}
                    <div class="quotation-preview p-4">
                        {{-- Header --}}
                        <div class="screen-only-header ">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <img src="{{ asset('images/usn-quotation.png') }}" alt="header" class="img-fluid" style="width: 100%;">
                                </div>
                            </div>
                            <hr class="my-2" style="border-top: 2px solid #000;">
                        </div>

                        {{-- Customer & Quotation Details --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Customer Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1"><strong>{{ $createdQuotation->customer_name }}</strong></p>
                                        <p class="mb-1">{{ $createdQuotation->customer_address }}</p>
                                        <p class="mb-1">Tel: {{ $createdQuotation->customer_phone }}</p>
                                        @if($createdQuotation->customer_email)
                                        <p class="mb-0">Email: {{ $createdQuotation->customer_email }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Quotation Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1"><strong>Quotation No:</strong> {{ $createdQuotation->quotation_number }}</p>
                                        <p class="mb-1"><strong>Date:</strong> {{ $createdQuotation->quotation_date->format('d/m/Y') }}</p>
                                        <p class="mb-1"><strong>Valid Until:</strong> {{ \Carbon\Carbon::parse($createdQuotation->valid_until)->format('d/m/Y') }}</p>

                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Items Table --}}
                        <div class="table-responsive mb-4 overflow-auto">
                            <table class="table table-bordered">
                                <thead class="table-primary">
                                    <tr>
                                        <th width="40">#</th>
                                        <th>Item Code</th>
                                        <th>Description</th>
                                        <th width="80">Qty</th>
                                        <th width="120">Unit Price (LKR)</th>
                                        <th width="120">Discount (LKR)</th>
                                        <th width="120">Subtotal (LKR)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($createdQuotation->items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item['product_code'] }}</td>
                                        <td>{{ $item['product_name'] }}</td>
                                        <td class="text-center">{{ $item['quantity'] }}</td>
                                        <td class="text-end">{{ number_format($item['unit_price'], 2) }}</td>
                                        <td class="text-end">{{ number_format($item['discount_per_unit'] ?? 0, 2) }}</td>
                                        <td class="text-end">{{ number_format($item['total'], 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>

                                {{-- Totals Section --}}
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">Subtotal:</td>
                                        <td class="text-end fw-bold">{{ number_format($createdQuotation->subtotal, 2) }}</td>
                                    </tr>

                                    @php
                                        $totalDiscount = $createdQuotation->discount_amount + $createdQuotation->additional_discount;
                                    @endphp

                                    @if($totalDiscount > 0)
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold text-danger">Total Discount:</td>
                                        <td class="text-end fw-bold text-danger">- {{ number_format($totalDiscount, 2) }}</td>
                                    </tr>
                                    @endif

                                    <tr>
                                        <td colspan="6" class="text-end fw-bold fs-5">Grand Total:</td>
                                        <td class="text-end fw-bold fs-5 text-primary">
                                            {{ number_format($createdQuotation->total_amount, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- Terms & Conditions --}}
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Terms & Conditions</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-0">{!! nl2br(e($createdQuotation->terms_conditions)) !!}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer Buttons --}}
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary me-2" wire:click="createNewQuotation">
                        <i class="bi bi-plus-circle me-2"></i>Create New Quotation
                    </button>
                    <button type="button" class="btn btn-success me-2" wire:click="downloadQuotation">
                        <i class="bi bi-download me-2"></i>Download Quotation
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

@push('styles')
<style>
    .search-results {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }

    .search-results .p-3 {
        transition: background-color 0.2s ease;
    }

    .search-results .p-3:hover {
        background-color: #f8f9fa;
    }

    .table th {
        font-size: 0.875rem;
        font-weight: 600;
        background-color: #f8f9fa;
    }

    .form-control-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .card {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: 1rem 1.25rem;
    }

    .input-group-sm>.btn {
        padding: 0.25rem 0.5rem;
    }

    /* Modal Styles */
    .modal.show {
        backdrop-filter: blur(2px);
    }

    /* Quotation Preview Styles */
    .quotation-preview {
        background: white;
        font-family: 'Segoe UI', Arial, sans-serif;
    }

    .quotation-preview .header {
        border-bottom: 2px solid #057642ff;
        padding-bottom: 1rem;
    }

    .quotation-preview table th {
        background-color: #038d4fff;
        color: white;
        border: none;
    }

    .quotation-preview table td {
        border: 1px solid #dee2e6;
    }

    /* Discount input styling */
    .text-danger.form-control:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    /* Quick discount buttons */
    .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: white;
    }

    /* Animation for cart actions */
    .btn {
        transition: all 0.2s ease-in-out;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.875rem;
        }

        .input-group-sm {
            flex-wrap: nowrap;
        }

        .card-header {
            padding: 0.75rem 1rem;
        }

        .modal-dialog {
            margin: 0.5rem;
        }
    }

    /* Loading states */
    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Success message styling */
    .alert-success {
        border-left: 4px solid #198754;
    }

    .alert-danger {
        border-left: 4px solid #dc3545;
    }

    .alert-info {
        border-left: 4px solid #0dcaf0;
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto-close alerts after 5 seconds
    document.addEventListener('livewire:initialized', () => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });

    // Prevent form submission on enter key in search
    document.addEventListener('keydown', function(e) {
        if (e.target.type === 'text' && e.target.getAttribute('wire:model') === 'search') {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        }
    });

    // Additional discount input handling
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('additionalDiscountUpdated', (value) => {
            // Additional discount validation can be handled here if needed
        });
    });
</script>
@endpush
