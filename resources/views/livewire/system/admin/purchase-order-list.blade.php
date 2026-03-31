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
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" style="width: 60%; margin: auto">
                <!-- 🔍 Search Bar -->
                    <div class="search-bar flex-grow-1">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" wire:model.live="search"
                                placeholder="Search by order code or supplier name...">
                        </div>
                    </div>
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
                <div class="table-responsive" style="overflow:visible !important;">
                    <table class="table table-hover mb-0" style="overflow:visible;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width: 50px;">
                                    <input type="checkbox" class="form-check-input" id="selectAll" onclick="toggleAllRows(this)">
                                </th>
                                <th class="ps-4">Order Code</th>
                                <th>Supplier</th>
                                <th>Status</th>
                                <th>GRN Status</th>
                                <th>Order Quantity</th>
                                <th>Received Quantity</th>
                                <th>Total Amount</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            @php
                            // Calculate totals for this order
                            $totalQuantity = $order->items->sum('quantity');
                            $totalReceivedQty = $order->items->sum('received_quantity');
                            $totalAmount = $order->items->sum(function($item) {
                                $qty = floatval($item->quantity);
                                $unitPrice = floatval($item->unit_price);
                                $discount = floatval($item->discount ?? 0);
                                $discountAmount = ($unitPrice * $discount) / 100;
                                $netUnitPrice = $unitPrice - $discountAmount;
                                return $qty * $netUnitPrice;
                            });
                            $ReceivedTotalAmount = $order->items->sum(function($item) {
                                $qty = floatval($item->received_quantity);
                                $unitPrice = floatval($item->unit_price);
                                $discount = floatval($item->discount ?? 0);
                                $discountAmount = ($unitPrice * $discount) / 100;
                                $netUnitPrice = $unitPrice - $discountAmount;
                                return $qty * $netUnitPrice;
                            });

                            // Calculate GRN status
                            $totalItems = $order->items->count();
                            $receivedItems = $order->items->where('status', 'received')->count();
                            $notReceivedItems = $order->items->where('status', 'notreceived')->count();
                            $pendingItems = $order->items->whereNotIn('status', ['received', 'notreceived'])->count();

                            $grnStatus = 'Pending';
                            $grnBadge = 'bg-warning';
                            if ($receivedItems > 0 && $notReceivedItems == 0 && $pendingItems == 0) {
                            $grnStatus = 'Completed';
                            $grnBadge = 'bg-success';
                            } elseif ($notReceivedItems > 0) {
                            $grnStatus = 'Partial';
                            $grnBadge = 'bg-danger';
                            } elseif ($receivedItems > 0) {
                            $grnStatus = 'In Progress';
                            $grnBadge = 'bg-info';
                            }
                            @endphp
                            <tr class="table-row" data-id="{{ $order->id }}">
                                <td class="ps-4" onclick="event.stopPropagation();">
                                    <input type="checkbox" class="form-check-input row-checkbox" onchange="toggleRowHighlight(this)">
                                </td>
                                <td class="ps-4">
                                    <span class="fw-medium text-dark">{{ $order->order_code }}</span>
                                </td>
                                <td>{{ $order->supplier->name ?? 'N/A' }}</td>
                                <td>
                                    @if($order->status == 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                    @elseif($order->status == 'complete')
                                    <span class="badge bg-success">Completed</span>
                                    @elseif($order->status == 'received')
                                    <span class="badge bg-info">Partial Receipt</span>
                                    @else
                                    <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $grnBadge }}">
                                        {{ $grnStatus }}
                                        @if($grnStatus != 'Pending')
                                        ({{ $receivedItems }}/{{ $totalItems }})
                                        @endif
                                    </span>
                                </td>
                                <td class="text-center">{{ $totalQuantity }}</td>
                                <td class="text-center">{{ $totalReceivedQty }}</td>
                                @if($grnStatus == 'Pending')
                                <td>{{ number_format($totalAmount, 2) }}</td>
                                @else

                                <td>{{ number_format($ReceivedTotalAmount, 2) }}</td>
                                @endif
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="bi bi-gear-fill"></i> Actions
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" style="z-index: 1060;">
                                            <!-- View Order -->
                                            <li>
                                                <button class="dropdown-item" wire:click="viewOrder({{ $order->id }})">
                                                    <i class="bi bi-eye text-primary me-2"></i> View
                                                </button>
                                            </li>

                                            @if($order->status == 'pending')
                                            <!-- Convert to GRN -->
                                            <li>
                                                <button class="dropdown-item" wire:click="convertToGRN({{ $order->id }})">
                                                    <i class="bi bi-arrow-repeat text-info me-2"></i> Process GRN
                                                </button>
                                            </li>

                                            <!-- Edit Order -->
                                            <li>
                                                <button class="dropdown-item" wire:click="editOrder({{ $order->id }})">
                                                    <i class="bi bi-pencil-square text-warning me-2"></i> Edit
                                                </button>
                                            </li>

                                            <!-- Cancel Order -->
                                            <li>
                                                <button class="dropdown-item" wire:click="confirmDelete({{ $order->id }})">
                                                    <i class="bi bi-x-circle text-danger me-2"></i> Cancel Order
                                                </button>
                                            </li>
                                            @endif

                                            @if($order->status == 'received')
                                            <!-- Re-Process GRN for Partial Orders -->
                                            <li>
                                                <button class="dropdown-item" wire:click="reProcessGRN({{ $order->id }})">
                                                    <i class="bi bi-arrow-clockwise text-success me-2"></i> Re-Process GRN
                                                </button>
                                            </li>

                                            <!-- Force Complete Order - Mark pending items as not received -->
                                            <li>
                                                <button class="dropdown-item" wire:click="confirmForceComplete({{ $order->id }})">
                                                    <i class="bi bi-check-circle text-primary me-2"></i> Force Complete Order
                                                </button>
                                            </li>
                                            @endif

                                            <!-- Download PDF -->
                                            <li>
                                                <button class="dropdown-item" wire:click="downloadPDF({{ $order->id }})">
                                                    <i class="bi bi-file-earmark-pdf text-danger me-2"></i> Download PDF
                                                </button>
                                            </li>

                                            <!-- Delete Order (for all statuses or only completed) -->
                                            <li>
                                                <button class="dropdown-item" wire:click="confirmPermanentDelete({{ $order->id }})">
                                                    <i class="bi bi-trash text-danger me-2"></i> Delete
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-center">
                        {{ $orders->links('livewire.custom-pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Order Modal --}}
    <div wire:ignore.self class="modal fade" id="addPurchaseOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-plus-circle text-white me-2"></i> Create New Purchase Order
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Select Supplier <span class="text-danger">*</span></label>
                            <select class="form-select" wire:model="supplier_id">
                                <option value="">Choose supplier...</option>
                                @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12 position-relative">
                            <label class="form-label fw-semibold">Search & Add Product</label>
                            <input type="text"
                                class="form-control"
                                wire:model.live.debounce.300ms="searchProduct"
                                placeholder="Type product name or code (min 2 characters)..."
                                autocomplete="off">
                            @if(!empty($products) && count($products) > 0)
                            <ul class="list-group mt-1 position-absolute w-100 z-3 shadow-lg" style="max-height: 300px; overflow-y: auto;">
                                @foreach($products as $product)
                                <li class="list-group-item list-group-item-action p-2"
                                    wire:key="search-product-{{ $product->id }}"
                                    wire:click="selectProduct({{ $product->id }})"
                                    style="cursor: pointer;">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $product->image ? asset($product->image) : asset('images/product.jpg') }}"
                                            alt="{{ $product->name }}"
                                            class="me-2"
                                            style="width: 45px; height: 45px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6;">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold text-dark">{{ $product->name }}</div>
                                            <small class="text-muted">
                                                Code: <span class="badge bg-secondary">{{ $product->code }}</span>
                                                | Available: <span class="badge {{ ($product->stock->available_stock ?? 0) > 0 ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $product->stock->available_stock ?? 0 }} units
                                                </span>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-success">Click to Add</span>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                            @endif
                            @if(strlen($searchProduct) >= 1 && strlen($searchProduct) < 2)
                                <div class="text-muted small mt-1">
                                <i class="bi bi-info-circle"></i> Type at least 2 characters to search
                        </div>
                        @endif
                    </div>
                </div>

                <h5 class="mt-4 mb-3">
                    <i class="bi bi-cart3 me-2"></i>Order Items
                    <span class="badge bg-primary">{{ count($orderItems) }}</span>
                </h5>
                <div class="table-responsive ">
                    <table class="table table-bordered table-hover overflow-auto">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th style="width: 120px;">Code</th>
                                <th>Product Name</th>
                                <th style="width: 120px;">Order Quantity</th>
                                
                                <th style="width: 150px;">Supplier Price</th>
                                <th style="width: 150px;">Total Price</th>
                                <th style="width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orderItems as $index => $item)
                            <tr wire:key="order-item-{{ $item['product_id'] }}-{{ $index }}">
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $item['code'] }}</span>
                                </td>
                                <td>
                                    <strong>{{ $item['name'] }}</strong>
                                </td>
                                <td>
                                    <input type="number"
                                        class="form-control form-control-sm"
                                        wire:model.live.debounce.300ms="orderItems.{{ $index }}.quantity"
                                        wire:change="updateOrderItemQuantity({{ $index }}, $event.target.value)"
                                        min="1"
                                        style="width: 100%;">
                                </td>
                                <td>
                                    <input type="number"
                                        class="form-control form-control-sm"
                                        wire:model.live.debounce.300ms="orderItems.{{ $index }}.supplier_price"
                                        wire:change="updateOrderItemPrice({{ $index }}, $event.target.value)"
                                        min="0"
                                        step="0.01"
                                        style="width: 100%;">
                                </td>
                                <td class="text-end">
                                    <strong class="text-success">Rs. {{ number_format($item['total_price'], 2) }}</strong>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger"
                                        wire:click="removeItem({{ $index }})"
                                        title="Remove item">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-cart-x display-4 d-block mb-2"></i>
                                    <p class="mb-0">No items added yet. Search and select products above to add them.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if(count($orderItems) > 0)
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="5" class="text-end"><strong>Grand Total:</strong></td>
                                <td class="text-end">
                                    <strong class="text-primary fs-5">Rs. {{ number_format($grandTotal, 2) }}</strong>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
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

{{-- GRN Modal --}}
<div wire:ignore.self class="modal fade" id="grnModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-clipboard-check text-white me-2"></i>
                    @if($selectedPO && $selectedPO->status == 'received')
                    Re-Process GRN for {{ $selectedPO->order_code }} (Pending Items Only)
                    @else
                    Create GRN for {{ $selectedPO?->order_code }}
                    @endif
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($selectedPO)
                <p><strong>Supplier:</strong> {{ $selectedPO->supplier->name }}</p>

                @if($selectedPO->status == 'received')
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Re-Processing:</strong> Only pending items from this order are shown below.
                </div>
                @endif

                <h5>
                    @if($selectedPO->status == 'received')
                    Pending Items to Receive
                    @else
                    Received Items
                    @endif
                </h5>
                <div class="table-responsive ">
                    <table class="table table-bordered overflow-auto">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Code</th>
                                <th style="width: 150px;">Product</th>
                                <th style="width: 80px;">Ord Qty</th>
                                <th style="width: 80px;">Recv Qty</th>
                                <th style="width: 150px;">Supplier Price</th>
                                <th style="width: 180px;">Discount</th>
                                <th style="width: 150px;">Selling Price</th>
                                <th style="width: 80px;">Cost</th>
                                <th style="width: 80px;">Total</th>
                                <th style="width: 80px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($grnItems as $index => $item)
                            @php
                            $statusClass = '';
                            $statusText = 'Pending';
                            $statusBadge = 'bg-warning';
                            if (strtolower($item['status'] ?? '') === 'received') {
                            $statusClass = 'table-success';
                            $statusText = 'Received';
                            $statusBadge = 'bg-success';
                            } elseif (strtolower($item['status'] ?? '') === 'notreceived') {
                            $statusClass = 'table-danger';
                            $statusText = 'Not Received';
                            $statusBadge = 'bg-danger';
                            }
                            @endphp
                            <tr wire:key="item-{{ $index }}" class="{{ $statusClass }}">
                                <td>
                                    <input type="text"
                                        class="form-control"
                                        wire:model.live="grnItems.{{ $index }}.code"
                                        placeholder="Product Code"
                                        {{ !($item['is_new'] ?? false) ? 'readonly' : '' }}>

                                </td>
                                <td class="position-relative">
                                    @if($item['is_new'] ?? false)
                                    <input type="text"
                                        class="form-control"
                                        wire:model.live="grnItems.{{ $index }}.name"
                                        placeholder="New Product Name">
                                    @else
                                    <input type="text"
                                        class="form-control product-search"
                                        wire:model.live="grnItems.{{ $index }}.name"
                                        placeholder="Search by name or code...">
                                    @if(isset($searchResults[$index]) && count($searchResults[$index]) > 0)
                                    <ul class="list-group position-absolute z-10 shadow-lg mt-1" style="min-width: 350px; max-width: 450px; left: 0;">
                                        @foreach($searchResults[$index] as $product)
                                        <li class="list-group-item list-group-item-action p-2"
                                            wire:key="grn-search-{{ $index }}-{{ $product->id }}"
                                            wire:click="selectGRNProduct({{ $index }}, {{ $product->id }})"
                                            style="cursor: pointer;">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $product->image ? asset('storage/' . $product->image) : asset('images/product.jpg') }}"
                                                    alt="{{ $product->name }}"
                                                    class="me-2"
                                                    style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
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
                                    @endif
                                </td>
                                <td class="text-center">{{ $item['ordered_qty'] ?? 0 }}</td>
                                <td>
                                    <input type="number"
                                        class="form-control text-center"
                                        wire:model.live="grnItems.{{ $index }}.received_quantity"
                                        min="0"
                                        wire:change="calculateGRNTotal({{ $index }})">
                                </td>
                                <td>
                                    <input type="number"
                                        class="form-control text-end supplier-price-input"
                                        wire:model.live.debounce.300ms="grnItems.{{ $index }}.unit_price"
                                        data-index="{{ $index }}"
                                        step="0.01"
                                        min="0"
                                        placeholder="0">
                                </td>
                                <td>
                                    <div class="discount-container">
                                        <div class="input-group input-group-sm">
                                            <input type="number"
                                                class="form-control discount-input"
                                                wire:model.live.debounce.300ms="grnItems.{{ $index }}.discount"
                                                data-index="{{ $index }}"
                                                placeholder="10"
                                                min="0"
                                                step="0.1"
                                                autocomplete="off">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        @if(floatval($grnItems[$index]['discount'] ?? 0) > 0)
                                        <small class="text-muted d-block mt-1">
                                            Rs. {{ number_format($this->calculateDiscountAmount($index), 2) }}
                                        </small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <input type="number"
                                        class="form-control text-end selling-price-input"
                                        wire:model.live="grnItems.{{ $index }}.selling_price"
                                        data-index="{{ $index }}"
                                        step="10"
                                        min="0"
                                        placeholder="Enter price"
                                        title="Selling Price (Enter manually)"
                                        wire:change="calculateGRNTotal({{ $index }})">
                                    <small class="text-muted d-block mt-1">
                                        Calc: {{ number_format($this->calculateSellingPrice($index), 0) }}
                                    </small>
                                </td>
                                <td class="text-end fw-semibold text-success">
                                    {{ number_format($this->calculateCost($index), 2) }}
                                </td>
                                <td class="text-end fw-bold text-primary">
                                    {{ number_format($this->calculateGRNTotal($index), 2) }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        @if(strtolower($item['status'] ?? '') !== 'received')
                                        <!-- Show complete button only for pending/not received items -->
                                        <button class="btn btn-sm btn-outline-success"
                                            wire:click="correctGRNItem({{ $index }})"
                                            title="Mark as Received">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                        @endif
                                        <!-- Delete button always available -->
                                        <button class="btn btn-sm btn-outline-danger"
                                            wire:click="deleteGRNItem({{ $index }})"
                                            title="Remove Item">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <div>
                        <button class="btn btn-success" wire:click="addNewRow">
                            <i class="bi bi-plus-circle"></i> Add New Item
                        </button>
                    </div>
                    <div class="card bg-light">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark pr-10">Grand Total:</span>
                                <span class="fw-bold fs-5 text-primary">
                                    {{ number_format($this->grnGrandTotal, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" wire:click="saveGRN">Save GRN</button>
                </div>

            </div>
            @endif
        </div>
    </div>
</div>
</div>

{{-- View Order Modal --}}
<div wire:ignore.self class="modal fade" id="viewOrderModal" tabindex="-1" aria-labelledby="viewOrderModalLabel" >
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

                <!-- <h6>Items</h6> -->
                <table class="table table-sm overflow-auto">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Product</th>
                            <th>Status</th>
                            <th>Order Qty</th>
                            <th>Received Qty</th>
                            <th>Price</th>
                            <th>Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($selectedOrder->items as $item)
                        @php
                            // Calculate discount amount (discount is percentage)
                            $unitPrice = floatval($item->unit_price);
                            $discount = floatval($item->discount ?? 0);
                            $discountAmount = ($unitPrice * $discount) / 100;
                            $netUnitPrice = $unitPrice - $discountAmount;
                            
                            // Calculate total based on received quantity
                            $qty = floatval($item->received_quantity ?? 0);
                            $totalPrice = $netUnitPrice * $qty;
                        @endphp
                        <tr>
                            <td>{{ $item->product->code ?? 'N/A' }}</td>
                            <td>{{ $item->product->name ?? 'N/A' }}</td>
                            <td>
                                @if($item->status == 'pending')
                                <span class="badge bg-warning">Pending</span>
                                @elseif($item->status == 'received')
                                <span class="badge bg-success">Received</span>
                                @elseif($item->status == 'notreceived')
                                <span class="badge bg-danger">Not Received</span>
                                @else
                                <span class="badge bg-secondary">{{ ucfirst($item->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $item->quantity }}</td>
                            <td>
                                @if($item->status == 'received')
                                <span class="text-success fw-bold">{{ $item->received_quantity }}</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <span class="d-block">{{ number_format($unitPrice, 2) }}</span>
                                    @if($discount > 0)
                                    <small class="text-danger">-{{ $discount }}% (Rs.{{ number_format($discountAmount, 2) }})</small>
                                    <br>
                                    <strong class="text-success">Net: Rs.{{ number_format($netUnitPrice, 2) }}</strong>
                                    @endif
                                </div>
                            </td>
                            <td>{{ number_format($totalPrice, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>
                <div class="card bg-light">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark pr-10">Grand Total:</span>
                            <span class="fw-bold fs-5 text-primary">
                                {{ number_format($this->viewOrderTotal, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
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
                {{-- Search and Add Product Section --}}
                <div class="row mb-3">
                    <div class="col-md-12 position-relative">
                        <label class="form-label fw-semibold">Search & Add Product</label>
                        <input type="text"
                            class="form-control"
                            wire:model.live.debounce.300ms="searchProduct"
                            placeholder="Type product name or code (min 2 characters)..."
                            autocomplete="off">
                        @if(!empty($products) && count($products) > 0)
                        <ul class="list-group mt-1 position-absolute w-100 z-3 shadow-lg" style="max-height: 300px; overflow-y: auto;">
                            @foreach($products as $product)
                            <li class="list-group-item list-group-item-action p-2"
                                wire:key="edit-search-product-{{ $product->id }}"
                                wire:click="addProductToEdit({{ $product->id }})"
                                style="cursor: pointer;">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $product->image ? asset('storage/' . $product->image) : asset('images/product.jpg') }}"
                                        alt="{{ $product->name }}"
                                        class="me-3"
                                        style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $product->name }}</div>
                                        <small class="text-muted">
                                            <span class="badge bg-secondary">{{ $product->code }}</span>
                                            @if($product->stock)
                                            <span class="ms-2 badge {{ ($product->stock->available_stock ?? 0) > 0 ? 'bg-success' : 'bg-danger' }}">Available: {{ $product->stock->available_stock ?? 0 }} units</span>
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                        @if(strlen($searchProduct) >= 1 && strlen($searchProduct) < 2)
                            <div class="text-muted small mt-1">
                            <i class="bi bi-info-circle"></i> Type at least 2 characters to search
                    </div>
                    @endif
                </div>
            </div>

            <h6 class="mb-3">
                <i class="bi bi-cart3 me-2"></i>Order Items
                <span class="badge bg-primary">{{ count($editOrderItems) }}</span>
            </h6>

            <div class="table-responsive overflow-auto">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th style="width: 90px;">Code</th>
                            <th>Product</th>
                            <th style="width: 120px;">Quantity</th>
                            <th style="width: 120px;">Unit Price</th>
                            <th style="width: 150px;">Total</th>
                            <th style="width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($editOrderItems as $index => $item)
                        <tr wire:key="edit-item-{{ $item['product_id'] }}-{{ $index }}">
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $item['code'] ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <strong>{{ $item['name'] }}</strong>
                            </td>
                            <td>
                                <input type="number"
                                    class="form-control form-control-sm"
                                    min="1"
                                    wire:model.live.debounce.300ms="editOrderItems.{{ $index }}.quantity"
                                    wire:change="updateEditItemTotal({{ $index }})">
                            </td>
                            <td>
                                <input type="number"
                                    class="form-control form-control-sm"
                                    step="0.01"
                                    wire:model.live.debounce.300ms="editOrderItems.{{ $index }}.unit_price"
                                    wire:change="updateEditItemTotal({{ $index }})">
                            </td>
                            <td class="text-end">
                                <strong class="text-success">
                                    Rs. {{ number_format(floatval($item['quantity'] ?? 0) * floatval($item['unit_price'] ?? 0), 2) }}
                                </strong>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-danger"
                                    wire:click="removeEditItem({{ $index }})"
                                    title="Remove item">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                        @if(empty($editOrderItems))
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">
                                <i class="bi bi-cart-x display-6 d-block mb-2"></i>
                                <p class="mb-0">No items in this order. Search and add products above.</p>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                    @if(count($editOrderItems) > 0)
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="5" class="text-end"><strong>Grand Total:</strong></td>
                            <td class="text-end">
                                <strong class="text-primary" style="font-size: 1rem;">
                                    Rs. {{ number_format(collect($editOrderItems)->sum(function($item) { return ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0); }), 2) }}
                                </strong>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button class="btn btn-primary" wire:click="updateOrder">
                <i class="bi bi-save me-1"></i> Save Changes
            </button>
        </div>
    </div>
</div>
</div>

</div>

@push('styles')
<style>
    /* Row selection highlight - Multiple specificity levels */
    tr.selected-row,
    table tbody tr.selected-row,
    table.table tbody tr.selected-row,
    .table tbody tr.selected-row {
        background-color: #d4e6f1 !important;
    }

    tr.selected-row td,
    table tbody tr.selected-row td,
    table.table tbody tr.selected-row td {
        background-color: #d4e6f1 !important;
    }

    table tbody tr.selected-row:hover,
    table.table tbody tr.selected-row:hover {
        background-color: #b8d7e8 !important;
    }

    table tbody tr.selected-row:hover td,
    table.table tbody tr.selected-row:hover td {
        background-color: #b8d7e8 !important;
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

    // Handle discount input - no auto-calculation of selling price
    document.addEventListener('DOMContentLoaded', function() {
        // Use event delegation for dynamically added inputs
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('discount-input')) {
                const input = e.target;
                const index = input.dataset.index;

                // Calculate GRN total when discount changes
                setTimeout(() => {
                    @this.call('calculateGRNTotal', index);
                }, 150);
            }
            
            if (e.target.classList.contains('supplier-price-input')) {
                const input = e.target;
                const index = input.dataset.index;

                // Calculate GRN total when supplier price changes
                setTimeout(() => {
                    @this.call('calculateGRNTotal', index);
                }, 150);
            }
        });
    });
</script>
@endpush
