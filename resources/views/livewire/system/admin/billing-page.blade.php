<div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <h6>Billing System</h6>
                    </div>
                    <div class="card-body">
                        <!-- Search Section -->
                        <div class="row mb-4">
                            <div class="col-md-6 mx-auto">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" class="form-control"
                                        placeholder="Search by code, model, barcode, brand or name..."
                                        wire:model.live="search" autocomplete="off">
                                </div>

                                <!-- Search Results Dropdown -->
                                @if ($search && count($searchResults) > 0)
                                    <div class="search-results-container position-relative mt-1 w-100 bg-white shadow-lg rounded z-index-1000"
                                        style="max-height: 350px; overflow-y: auto;">
                                        @foreach ($searchResults as $result)
                                            <div class="search-result-item p-2 border-bottom"
                                                wire:key="result-{{ $result->id }}">
                                                <div class="d-flex align-items-stretch position-relative">
                                                    <!-- Product Image - Full height -->
                                                    <div class="product-image me-3" style="min-width: 60px;">
                                                        <img src="{{ $result->image ? asset('storage/' . $result->image) : asset('images/product.jpg') }}"
                                                            alt="{{ $result->name }}" class="img-fluid rounded"
                                                            style="width: 60px; height: 60px; object-fit: cover;">
                                                    </div>

                                                    <!-- Vertical divider -->
                                                    <div class="vr mx-2 h-100"></div>

                                                    <!-- Product Info - Middle section -->
                                                    <div class="product-info flex-grow-1">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <h6 class="mb-0 fw-bold">
                                                                {{ $result->name ?? 'Unnamed Product' }}</h6>
                                                            <div>
                                                                <span
                                                                    class="badge bg-success">Rs.{{ $result->selling_price ?? '-' }}</span>
                                                                <span class="badge bg-info">Stock:
                                                                    {{ $result->available_stock ?? '-' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="text-muted small mt-1">
                                                            <span class="me-2">Code: {{ $result->code }}</span> |
                                                            <span class="mx-2">Model: {{ $result->model }}</span> |
                                                            <span class="ms-2">Brand: {{ $result->brand }}</span>
                                                        </div>
                                                    </div>

                                                    <!-- Vertical divider -->
                                                    <div class="vr mx-2 h-100"></div>

                                                    <!-- Product Action - Right side -->
                                                    <div class="product-action d-flex align-items-center">
                                                        <button class="btn btn-sm btn-primary"
                                                            wire:click="addToCart({{ $result->id }})"
                                                            {{ !$result->stock || $result->stock->available_stock <= 0 ? 'disabled' : '' }}>
                                                            <i class="fas fa-plus"></i> Add
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($search && count($searchResults) == 0)
                                    <div
                                        class="search-results-container position-absolute mt-1 w-100 bg-white shadow-lg rounded">
                                        <div class="p-3 text-center text-muted">
                                            No Productes found matching "{{ $search }}"
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Cart Table -->
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Product</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Unit Price</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Quantity</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Discount (per unit)</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Total</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($cart as $id => $item)
                                        <tr wire:key="cart-{{ $id }}">
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div>
                                                        <img src="{{ $item['image'] ? asset('storage/' . $item['image']) : asset('images/product.jpg') }}"
                                                            class="avatar avatar-sm me-3"
                                                            alt="{{ $item['name'] }}">
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $item['name'] }}</h6>
                                                        <p class="text-xs text-secondary mb-0">{{ $item['code'] }} |
                                                            {{ $item['brand'] }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">
                                                    Rs.{{ number_format($item['discountPrice'] ?: $item['price'], 2) }}
                                                </p>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm" style="width: 100px;">
                                                    <button class="btn btn-outline-primary btn-sm"
                                                        wire:click="updateQuantity({{ $id }}, {{ $quantities[$id] - 1 }})"
                                                        {{ $quantities[$id] <= 1 ? 'disabled' : '' }}>-</button>
                                                    <input type="number"
                                                        class="form-control form-control-sm text-center quantity-input"
                                                        data-Product-id="{{ $id }}"
                                                        data-max="{{ $item['inStock'] }}"
                                                        value="{{ $quantities[$id] }}" min="1"
                                                        max="{{ $item['inStock'] }}"
                                                        wire:change="updateQuantity({{ $id }}, $event.target.value)">
                                                    <button class="btn btn-outline-primary btn-sm"
                                                        wire:click="updateQuantity({{ $id }}, {{ $quantities[$id] + 1 }})"
                                                        {{ $quantities[$id] >= $item['inStock'] ? 'disabled' : '' }}>+</button>
                                                </div>
                                                @php
                                                // $qty = $quantities[$id] ?? 1;
                                                // $stock = $item['inStock'] ?? 0;
                                                // $maxQuantity = $item['inStock'] - $quantities[$id];
                                                // dump($maxQuantity, $qty, $stock);
                                                @endphp
                                                <div class="invalid-feedback quantity-error">
                                                    Maximum available quantity is {{ $item['inStock'] }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm" style="width: 100px;">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" class="form-control form-control-sm"
                                                        value="{{ $discounts[$id] ?? 0 }}" min="0"
                                                        max="{{ $item['price'] }}" step="0.01"
                                                        wire:change="updateDiscount({{ $id }}, $event.target.value)">
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">
                                                    Rs.{{ number_format(($item['discountPrice'] ?: $item['price']) * $quantities[$id] - ($discounts[$id] ?? 0) * $quantities[$id], 2) }}
                                                </p>
                                            </td>
                                            <td>
                                                <button class="btn btn-link btn-sm text-info"
                                                    wire:click="showDetail({{ $id }})">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn btn-link btn-sm text-danger"
                                                    wire:click="removeFromCart({{ $id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                                    <p>Your cart is empty. Search and add products to create a bill.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if (!empty($cart))
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="staff" class="form-label">Assign to Staff <span class="text-danger">*</span></label>
                                        <select wire:model="selectedStaffId" id="staff" class="form-select @error('selectedStaffId') is-invalid @enderror">
                                            <option value="">Select a staff member</option>
                                            @foreach($staffs as $staff)
                                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('selectedStaffId')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Customer & Payment Information -->
                        @if (!empty($cart))
                            <div class="row mt-4">

                                <div class="col-md-6">
                                    <!-- Order Summary -->
                                    <div class="card mt-4">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0 fw-bold">Order Summary</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Subtotal:</span>
                                                <span>Rs.{{ number_format($subtotal, 2) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Total Discount:</span>
                                                <span>Rs.{{ number_format($totalDiscount, 2) }}</span>
                                            </div>
                                            <hr>
                                            <div class="d-flex justify-content-between">
                                                <span class="fw-bold">Grand Total:</span>
                                                <span class="fw-bold">Rs.{{ number_format($grandTotal, 2) }}</span>
                                            </div>

                                            <div class="d-flex mt-4">
                                                <button class="btn btn-danger me-2" wire:click="clearCart">
                                                    <i class="fas fa-times me-2"></i>Clear
                                                </button>
                                                <button class="btn btn-success flex-grow-1" wire:click="completeSale">
                                                    <i class="fas fa-check me-2"></i>Complete Sale
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <!-- View Product Modal -->
            <div wire:ignore.self class="modal fade" id="viewDetailModal" tabindex="-1"
                aria-labelledby="viewDetailModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-primary">
                            <h1 class="modal-title fs-5 text-white" id="viewDetailModalLabel">Product Details</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        @if ($ProductDetails)
                            <div class="modal-body p-4">
                                <div class="card shadow-sm border-0">
                                    <div class="card-body p-0">
                                        <div class="row g-0">
                                            <!-- Image Column -->
                                            <div class="col-md-4 border-end">
                                                <div class="position-relative h-100">
                                                    <img src="{{ $ProductDetails->image ? asset('storage/' . $ProductDetails->image) : asset('images/product.jpg') }}"
                                                        alt="{{ $ProductDetails->name }}"
                                                        class="img-fluid rounded-start h-100 w-100 object-fit-cover">
                                                </div>

                                                    <!-- Status badges in corner -->
                                                    <div
                                                        class="position-absolute top-0 end-0 p-2 d-flex flex-column gap-2">
                                                        <span
                                                            class="badge bg-{{ $ProductDetails->status == 'active' ? 'success' : 'danger' }}">
                                                            {{ ucfirst($ProductDetails->status) }}
                                                        </span>

                                                        <!-- Stock Status Badge -->
                                                        @if ($ProductDetails->available_stock > 0)
                                                            <span class="badge bg-success">
                                                                <i class="bi bi-check-circle-fill"></i>
                                                                In Stock
                                                            </span>
                                                        @else
                                                            <span class="badge bg-danger">
                                                                <i class="bi bi-x-circle-fill"></i>
                                                                Out of Stock
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Main Details Column -->
                                            <div class="col-md-8">
                                                <div class="p-4">
                                                    <div
                                                        class="d-flex justify-content-between align-items-center mb-3">
                                                        <h3 class="fw-bold mb-0 text-primary">
                                                            {{ $ProductDetails->name }}
                                                        </h3>
                                                    </div>

                                                    <!-- Display code prominently -->
                                                    <div class="mb-3">
                                                        <span class="badge bg-dark p-2 fs-6">Code:
                                                            {{ $ProductDetails->code }}</span>
                                                    </div>

                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <p class="text-muted mb-1">
                                                                Brand</p>
                                                            <h5 class="fw-bold text-primary">
                                                                {{ $ProductDetails->brand }}
                                                            </h5>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p class="text-muted mb-1">
                                                                Model</p>
                                                            <h5 class="text-primary">
                                                                {{ $ProductDetails->model }}
                                                            </h5>
                                                        </div>
                                                    </div>

                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <p class="text-muted mb-1">
                                                                Category</p>
                                                            <h5 class="text-primary">
                                                                {{ $ProductDetails->category }}
                                                            </h5>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p class="text-muted mb-1">
                                                                Gender</p>
                                                            <h5 class="text-primary">
                                                                {{ ucfirst($ProductDetails->gender) }}
                                                            </h5>
                                                        </div>
                                                    </div>

                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            Description</p>
                                                        <p>{{ $ProductDetails->description }}
                                                        </p>
                                                    </div>

                                                    <!-- Pricing with attractive discount display -->
                                                    <div class="card bg-light p-3 mb-3">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <h5 class="text-danger fw-bold mb-0">
                                                                    Rs.
                                                                    {{ number_format($ProductDetails->selling_price, 2) }}
                                                                </h5>
                                                                @if ($ProductDetails->available_stock > 0)
                                                                    <small class="text-success">
                                                                        <i class="bi bi-check-circle-fill"></i>
                                                                        {{ $ProductDetails->available_stock }}
                                                                        units
                                                                        available
                                                                    </small>
                                                                @else
                                                                    <small class="text-danger fw-bold">
                                                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                                                        OUT
                                                                        OF STOCK
                                                                    </small>
                                                                @endif
                                                            </div>
                                                            @if ($ProductDetails->discount_price > 0)
                                                                <div class="position-relative">
                                                                    <div
                                                                        class="position-absolute top-0 start-50 translate-middle">
                                                                        <span
                                                                            class="badge bg-danger p-2 rounded-pill">SPECIAL
                                                                            OFFER</span>
                                                                    </div>
                                                                    <div class="border border-success rounded-3 p-2 text-center mt-3"
                                                                        style="background-color: rgba(25, 135, 84, 0.1);">
                                                                        <span class="text-success fw-bold fs-5">
                                                                            SAVE Rs.
                                                                            {{ number_format($ProductDetails->discount_price, 2) }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Detailed Specifications using Accordion instead of Tabs -->
                                <div class="accordion mt-4" id="ProductDetailsAccordion">
                                    <!-- Specifications Section -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#specs-collapse" aria-expanded="true"
                                                aria-controls="specs-collapse">
                                                <i class="bi bi-info-circle me-2"></i>
                                                Specifications
                                            </button>
                                        </h2>
                                        <div id="specs-collapse" class="accordion-collapse collapse show"
                                            data-bs-parent="#ProductDetailsAccordion">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <table class="table table-borderless">
                                                            <tbody>
                                                                <tr>
                                                                    <th class="text-muted" width="40%">
                                                                        Color</th>
                                                                    <td class="text-primary fw-medium">
                                                                        {{ $ProductDetails->color }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="text-muted">
                                                                        Made By</th>
                                                                    <td class="text-primary fw-medium">
                                                                        {{ $ProductDetails->made_by }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="text-muted">
                                                                        Movement</th>
                                                                    <td class="text-primary fw-medium">
                                                                        {{ $ProductDetails->movement }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="text-muted">
                                                                        Type</th>
                                                                    <td class="text-primary fw-medium">
                                                                        {{ $ProductDetails->type }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="text-muted">
                                                                        Dial Color</th>
                                                                    <td class="text-primary fw-medium">
                                                                        {{ $ProductDetails->dial_color }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="text-muted">
                                                                        Water Resistance
                                                                    </th>
                                                                    <td class="text-primary fw-medium">
                                                                        {{ $ProductDetails->water_resistance }}
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <table class="table table-borderless">
                                                            <tbody>
                                                                <tr>
                                                                    <th class="text-muted" width="40%">Case
                                                                        Diameter
                                                                    </th>
                                                                    <td class="text-primary fw-medium">
                                                                        {{ $ProductDetails->case_diameter_mm }}
                                                                        mm</td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="text-muted">
                                                                        Case Thickness</th>
                                                                    <td class="text-primary fw-medium">
                                                                        {{ $ProductDetails->case_thickness_mm }}
                                                                        mm</td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="text-muted">
                                                                        Glass Type</th>
                                                                    <td class="text-primary fw-medium">
                                                                        {{ $ProductDetails->glass_type }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="text-muted">
                                                                        Strap Material</th>
                                                                    <td class="text-primary fw-medium">
                                                                        {{ $ProductDetails->strap_material }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="text-muted">
                                                                        Strap Color</th>
                                                                    <td class="text-primary fw-medium">
                                                                        {{ $ProductDetails->strap_color }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="text-muted">
                                                                        Features</th>
                                                                    <td class="text-primary fw-medium">
                                                                        {{ $ProductDetails->features }}
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Inventory Section -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#inventory-collapse"
                                                aria-expanded="false" aria-controls="inventory-collapse">
                                                <i class="bi bi-box-seam me-2"></i>
                                                Inventory
                                            </button>
                                        </h2>
                                        <div id="inventory-collapse" class="accordion-collapse collapse"
                                            data-bs-parent="#ProductDetailsAccordion">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="card mb-3 border-primary">
                                                            <div class="card-body">
                                                                <div class="d-flex justify-content-between">
                                                                    <p class="card-text fw-bold">
                                                                        Shop Stock</p>
                                                                    <h4 class="card-title text-primary">
                                                                        {{ $ProductDetails->shop_stock }}
                                                                    </h4>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="card mb-3 border-primary">
                                                            <div class="card-body">
                                                                <div class="d-flex justify-content-between">
                                                                    <p class="card-text fw-bold">
                                                                        Store Stock</p>
                                                                    <h4 class="card-title text-primary">
                                                                        {{ $ProductDetails->store_stock }}
                                                                    </h4>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="card mb-3 border-danger">
                                                            <div class="card-body">
                                                                <div class="d-flex justify-content-between">
                                                                    <p class="card-text fw-bold">
                                                                        Damage Stock</p>
                                                                    <h4 class="card-title text-danger">
                                                                        {{ $ProductDetails->damage_stock }}
                                                                    </h4>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div
                                                            class="card mb-3 {{ $ProductDetails->available_stock > 0 ? 'border-success' : 'border-danger' }}">
                                                            <div class="card-body">
                                                                <div class="d-flex justify-content-between">
                                                                    <p class="card-text fw-bold">
                                                                        Available Stock</p>
                                                                    <h4
                                                                        class="card-title {{ $ProductDetails->available_stock > 0 ? 'text-success' : 'text-danger' }}">
                                                                        {{ $ProductDetails->available_stock }}
                                                                    </h4>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="card mb-3 border-dark">
                                                            <div class="card-body">
                                                                <div class="d-flex justify-content-between">
                                                                    <p class="card-text fw-bold">
                                                                        Total Stock</p>
                                                                    <h4 class="card-title">
                                                                        {{ $ProductDetails->total_stock }}
                                                                    </h4>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="card mb-3 border-info">
                                                            <div class="card-body">
                                                                <div class="d-flex justify-content-between">
                                                                    <p class="card-text fw-bold">
                                                                        Store Location</p>
                                                                    <h5 class="card-title text-info">
                                                                        {{ $ProductDetails->location }}
                                                                    </h5>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Supplier Section -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#supplier-collapse"
                                                aria-expanded="false" aria-controls="supplier-collapse">
                                                <i class="bi bi-truck me-2"></i> Supplier
                                                Details
                                            </button>
                                        </h2>
                                        <div id="supplier-collapse" class="accordion-collapse collapse"
                                            data-bs-parent="#ProductDetailsAccordion">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h5 class="fw-bold mb-3">Supplier
                                                            Information</h5>
                                                        <table class="table table-borderless">
                                                            <tbody>
                                                                <tr>
                                                                    <th class="text-muted" width="40%">
                                                                        Supplier</th>
                                                                    <td class="text-primary fw-medium">
                                                                        {{ $ProductDetails->supplier_name }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="text-muted">
                                                                        Email</th>
                                                                    <td class="text-primary fw-medium">
                                                                        {{ $ProductDetails->email ?: 'N/A' }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="text-muted">
                                                                        Phone</th>
                                                                    <td class="text-primary fw-medium">
                                                                        {{ $ProductDetails->contact ?: 'N/A' }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="text-muted">
                                                                        Barcode</th>
                                                                    <td class="text-primary fw-medium">
                                                                        {{ $ProductDetails->barcode }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="text-muted">
                                                                        Warranty</th>
                                                                    <td class="text-primary fw-medium">
                                                                        {{ $ProductDetails->warranty }}
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <h5 class="fw-bold mb-3">Pricing
                                                            Information</h5>
                                                        <div class="card mb-3 border-secondary">
                                                            <div class="card-body">
                                                                <div class="d-flex justify-content-between">
                                                                    <p class="card-text fw-bold">
                                                                        Supplier Price</p>
                                                                    <h5 class="card-title">
                                                                        Rs.
                                                                        {{ number_format($ProductDetails->supplier_price, 2) }}
                                                                    </h5>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="card mb-3 border-primary">
                                                            <div class="card-body">
                                                                <div class="d-flex justify-content-between">
                                                                    <p class="card-text fw-bold">
                                                                        Selling Price</p>
                                                                    <h5 class="card-title text-primary">
                                                                        Rs.
                                                                        {{ number_format($ProductDetails->selling_price, 2) }}
                                                                    </h5>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        @if ($ProductDetails->discount_price > 0)
                                                            <div class="card border-success bg-success bg-opacity-10">
                                                                <div
                                                                    class="card-header bg-success bg-opacity-25 border-success">
                                                                    <h6 class="text-success mb-0 fw-bold">
                                                                        SPECIAL DISCOUNT
                                                                    </h6>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div
                                                                        class="d-flex justify-content-between align-items-center">
                                                                        <p class="card-text fw-bold text-success">
                                                                            Discount
                                                                            Amount</p>
                                                                        <h5 class="card-title text-success">
                                                                            Rs.
                                                                            {{ number_format($ProductDetails->discount_price, 2) }}
                                                                        </h5>
                                                                    </div>
                                                                    <div class="progress mt-2" style="height: 10px;">
                                                                        <div class="progress-bar bg-success"
                                                                            role="progressbar"
                                                                            style="width: {{ min(($ProductDetails->discount_price / $ProductDetails->selling_price) * 100, 100) }}%"
                                                                            aria-valuenow="{{ ($ProductDetails->discount_price / $ProductDetails->selling_price) * 100 }}"
                                                                            aria-valuemin="0" aria-valuemax="100">
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between mt-1">
                                                                        <small class="text-muted">0%</small>
                                                                        <small class="text-muted">Save
                                                                            {{ number_format(($ProductDetails->discount_price / $ProductDetails->selling_price) * 100, 0) }}%</small>
                                                                        <small class="text-muted">100%</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

                        </div>
                    </div>
                </div>
            </div>
            <!-- View Product Modal End-->

        </div>
    </div>
    @push('styles')
        <style>
            .search-results-container {
                z-index: 1050;
            }

            .search-result-item:hover {
                background-color: #f8f9fa;
                cursor: pointer;
            }
        </style>
    @endpush
    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Quantity validation
            function setupQuantityValidation() {
                document.querySelectorAll('.quantity-input').forEach(input => {
                    input.addEventListener('input', function() {
                        const max = parseInt(this.getAttribute('max'));
                        const value = parseInt(this.value) || 0;
                        
                        if (value > max) {
                            this.classList.add('is-invalid');
                            this.nextElementSibling?.classList.add('d-block');
                        } else {
                            this.classList.remove('is-invalid');
                            this.nextElementSibling?.classList.remove('d-block');
                        }
                    });
                    
                    input.addEventListener('blur', function() {
                        const max = parseInt(this.getAttribute('max'));
                        const min = parseInt(this.getAttribute('min') || 1);
                        let value = parseInt(this.value) || 0;
                        
                        if (value > max) {
                            this.value = max;
                            Livewire.dispatch('quantity-corrected', {
                                ProductId: this.dataset.ProductId, 
                                quantity: max
                            });
                        } else if (value < min) {
                            this.value = min;
                            Livewire.dispatch('quantity-corrected', {
                                ProductId: this.dataset.ProductId, 
                                quantity: min
                            });
                        }
                    });
                });
            }
            
            setupQuantityValidation();
            document.addEventListener('livewire:update', setupQuantityValidation);
        });
    </script>
    @endpush
</div>
