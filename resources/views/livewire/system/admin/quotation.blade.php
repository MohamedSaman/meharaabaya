<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-cart-check-fill text-success me-2"></i> Purchase Order Management
            </h3>
            <p class="text-muted mb-0">Create and manage purchase orders from suppliers</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPurchaseOrderModal">
                <i class="bi bi-plus-circle me-2"></i> New Purchase Order
            </button>
        </div>
    </div>

    <div class="container-fluid p-4">
        {{-- Summary Cards --}}
        <div class="row mb-2">
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card summary-card pending h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-container bg-warning bg-opacity-10 me-3">
                                <i class="bi bi-hourglass-split text-warning fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Pending Orders</p>
                                <h4 class="fw-bold mb-0">{{ $pendingCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card summary-card completed h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-container bg-success bg-opacity-10 me-3">
                                <i class="bi bi-patch-check-fill text-success fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Completed Orders</p>
                                <h4 class="fw-bold mb-0">{{ $completedCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-list-check text-primary me-2"></i> Purchase Orders
                    </h5>
                    <p class="text-muted small mb-0">View and manage all purchase orders</p>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Order Code</th>
                                <th>Supplier</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-medium text-dark">{{ $order->order_code }}</span>
                                </td>
                                <td>{{ $order->supplier->name ?? 'N/A' }}</td>
                                <td>
                                    @if($order->status == 'complete')
                                    <span class="badge bg-success">Completed</span>
                                    @elseif($order->status == 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                    @else
                                    <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-link text-primary p-0 me-2"
                                        wire:click="viewOrder({{ $order->id }})"
                                        title="View Order Details">
                                        <i class="bi bi-eye fs-6"></i>
                                    </button>

                                    @if($order->status == 'pending')
                                    <button class="btn btn-link text-success p-0 me-2"
                                        wire:click="confirmComplete({{ $order->id }})"
                                        title="Mark Complete">
                                        <i class="bi bi-check-circle fs-6"></i>
                                    </button>

                                    <button class="btn btn-link text-warning p-0 me-2"
                                        wire:click="editOrder({{ $order->id }})"
                                        title="Edit Order">
                                        <i class="bi bi-pencil-square fs-6"></i>
                                    </button>

                                    <button class="btn btn-link text-danger p-0"
                                        wire:click="confirmDelete({{ $order->id }})"
                                        title="Cancel Order">
                                        <i class="bi bi-x-circle fs-6"></i>
                                    </button>

                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Order Modal --}}
    <div wire:ignore.self class="modal fade" id="addPurchaseOrderModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-plus-circle text-primary me-2"></i> Create New Purchase Order
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Select Supplier</label>
                            <select class="form-select" wire:model="supplier_id">
                                <option value="">Choose supplier...</option>
                                @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label fw-semibold">Search Product</label>
                            <input type="text" class="form-control" wire:model.live="search" placeholder="Search by name or code...">
                            @if(!empty($products) && count($products) > 0)
                            <ul class="list-group mt-1 position-absolute w-100 me-4 z-3 shadow-lg">
                                @foreach($products as $product)
                                <li class="list-group-item list-group-item-action p-2"
                                    wire:click="selectProduct({{ $product->id }})"
                                    style="cursor: pointer;">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $product->image ? asset('storage/' . $product->image) : asset('images/product.jpg') }}"
                                            alt="{{ $product->name }}"
                                            class="me-2"
                                            style="width: 45px; height: 45px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6;">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold text-dark">{{ $product->name }}</div>
                                            <small class="text-muted">
                                                Code: <span class="badge bg-secondary">{{ $product->code }}</span>
                                                | Stock: <span class="badge {{ ($product->stock->total_stock ?? 0) > 0 ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $product->stock->total_stock ?? 0 }} units
                                                </span>
                                            </small>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </div>
                    </div>

                    @if($selectedProduct)
                    <div class="row align-items-end bg-light p-3 rounded border mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Selected Product</label>
                            <input type="text" class="form-control" value="{{ $selectedProduct->name }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Quantity</label>
                            <input type="number" class="form-control" wire:model="quantity" min="1">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-success w-100" wire:click="addItem">
                                <i class="bi bi-plus-circle me-1"></i> Add
                            </button>
                        </div>
                    </div>
                    @endif

                    <h5>Order Items</h5>
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orderItems as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item['name'] }}</td>
                                <td>{{ $item['quantity'] }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-danger" wire:click="removeItem({{ $index }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No items added</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="saveOrder">
                        <i class="bi bi-save me-1"></i> Save Purchase Order
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- View Order Modal --}}
    <div wire:ignore.self class="modal fade" id="viewOrderModal" tabindex="-1" aria-labelledby="viewOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-eye text-primary me-2"></i> Order Details - {{ $selectedOrder?->order_code }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($selectedOrder)
                    <p><strong>Supplier:</strong> {{ $selectedOrder->supplier->name ?? 'N/A' }}</p>
                    <p><strong>Order Date:</strong> {{ $selectedOrder->order_date }}</p>
                    <p><strong>Received Date:</strong> {{ $selectedOrder->received_date ?? '-' }}</p>

                    <h6>Items</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($selectedOrder->items as $item)
                            <tr>
                                <td>{{ $item->product->name ?? 'N/A' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->unit_price }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Order Modal --}}
    <div wire:ignore.self class="modal fade" id="editOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Purchase Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($editOrderItems as $index => $item)
                                <tr>
                                    <td>{{ $item['name'] }}</td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm" min="1" wire:model.defer="editOrderItems.{{ $index }}.quantity">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm" step="0.01" wire:model.defer="editOrderItems.{{ $index }}.unit_price">
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger" wire:click="removeEditItem({{ $index }})">Remove</button>
                                    </td>
                                </tr>
                                @endforeach
                                @if(empty($editOrderItems))
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No items to edit</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" wire:click="updateOrder">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

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

    .summary-card.pending {
        border-left-color: #ffc107;
    }

    .summary-card.completed {
        border-left-color: #28a745;
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

    .table th {
            border-top: none;
            font-weight: 600;
            color: #ffffff;
           
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

    .table td {
        vertical-align: middle;
        padding: 0.75rem 1rem;
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
        padding: 0.50rem 0.75rem;
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

    /* Product Search Dropdown Styling */
    .list-group {
        border-radius: 8px;
        overflow: hidden;
        max-height: 400px;
        overflow-y: auto;
    }

    .list-group-item {
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }

    .list-group-item:hover {
        background-color: #e3f2fd;
        transform: translateX(5px);
    }

    .list-group-item-action:hover {
        background-color: #f0f7ff;
        cursor: pointer;
    }
</style>
@endpush

@push('scripts')
<script>
    // No additional event listeners needed - modals are opened directly via $this->js() in the component
</script>
@endpush
