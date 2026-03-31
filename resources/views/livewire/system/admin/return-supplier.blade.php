<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-arrow-return-left text-warning me-2"></i> Supplier Returns
            </h3>
            <p class="text-muted mb-0">Manage product returns to suppliers efficiently</p>
        </div>
        <div>
            <button class="btn btn-primary">
                <i class="bi bi-download me-2"></i> Export Report
            </button>
        </div>
    </div>

    <!-- Supplier Search and Purchase Order Selection -->
    <div class="row mb-4">
        <!-- Supplier Search -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-truck text-primary me-2"></i> Supplier Search
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Search Supplier or PO #</label>
                        <input type="text" class="form-control" wire:model.live="searchSupplier" placeholder="Search by supplier name or purchase order number...">
                    </div>

                    @if($searchSupplier && (count($suppliers) > 0 || count($supplierPurchaseOrders) > 0))
                    <div class="border rounded p-3 bg-light">
                        <h6 class="fw-semibold mb-2">Search Results</h6>
                        <div class="list-group mb-2">
                            @foreach($suppliers as $supplier)
                            <button class="list-group-item list-group-item-action p-2"
                                wire:click="selectSupplier({{ $supplier->id }})"
                                type="button">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="bi bi-building fs-4 text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $supplier->name }}</div>
                                        <small class="text-muted">{{ $supplier->phone }} | {{ $supplier->email }}</small>
                                    </div>
                                </div>
                            </button>
                            @endforeach
                        </div>
                        <div class="list-group">
                            @foreach($supplierPurchaseOrders as $purchaseOrder)
                            @if(str_contains($purchaseOrder->order_code, $searchSupplier))
                            <button class="list-group-item list-group-item-action p-2"
                                wire:click="selectPurchaseOrderForReturn({{ $purchaseOrder->id }})"
                                type="button">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="bi bi-receipt fs-4 text-info"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">PO #{{ $purchaseOrder->order_code }}</div>
                                        <small class="text-muted">{{ $purchaseOrder->created_at->format('Y-m-d') }} | Rs.{{ number_format($purchaseOrder->total_amount, 2) }}</small>
                                    </div>
                                </div>
                            </button>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($selectedSupplier)
                    <div class="mt-3 p-3 bg-info bg-opacity-10 rounded border border-info">
                        <h6 class="fw-semibold text-info mb-2">Selected Supplier</h6>
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-building-check fs-4 text-info"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $selectedSupplier->name }}</div>
                                <small class="text-muted">{{ $selectedSupplier->phone }} | {{ $selectedSupplier->email }}</small>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Supplier Purchase Orders -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-receipt text-info me-2"></i> Recent Purchase Orders
                    </h5>
                    <button class="btn btn-info btn-sm" wire:click="loadSupplierPurchaseOrders">
                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                    </button>
                </div>
                <div class="card-body overflow-auto">
                    @if($selectedSupplier && count($supplierPurchaseOrders) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">PO #</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($supplierPurchaseOrders as $purchaseOrder)
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-medium text-dark">{{ $purchaseOrder->order_code }}</span>
                                    </td>
                                    <td>{{ $purchaseOrder->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        @if($purchaseOrder->status == 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                        @elseif($purchaseOrder->status == 'received')
                                        <span class="badge bg-success">Received</span>
                                        @elseif($purchaseOrder->status == 'complete')
                                        <span class="badge bg-info">Complete</span>
                                        @else
                                        <span class="badge bg-secondary">{{ ucfirst($purchaseOrder->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark">Rs.{{ number_format($purchaseOrder->total_amount, 2) }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-success"
                                                wire:click="selectPurchaseOrderForReturn({{ $purchaseOrder->id }})">
                                                <i class="bi bi-check-circle me-1"></i> Select
                                            </button>
                                            <button class="btn btn-outline-info"
                                                wire:click="viewPurchaseOrder({{ $purchaseOrder->id }})">
                                                <i class="bi bi-eye me-1"></i> View
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-receipt-cutoff text-muted fs-1 mb-3"></i>
                        <p class="text-muted mb-0">No purchase orders found for this supplier</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($showReturnSection && $selectedPurchaseOrder)
    <!-- Previous Returns Section -->
    @if(!empty($previousReturns))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning bg-opacity-10 border-bottom border-warning">
                    <h5 class="fw-bold mb-0 text-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i> Previous Returns for PO #{{ $selectedPurchaseOrder->order_code }}
                    </h5>
                </div>
                <div class="card-body overflow-auto">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Total Returned</th>
                                    <th>Total Amount</th>
                                    <th>Return Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($previousReturns as $productId => $returnData)
                                <tr>
                                    <td>{{ $returnData['product_name'] }}</td>
                                    <td><span class="badge bg-warning">{{ $returnData['total_returned'] }} units</span></td>
                                    <td class="fw-bold">Rs.{{ number_format($returnData['total_amount'], 2) }}</td>
                                    <td>
                                        <div class="small">
                                            @foreach($returnData['returns'] as $return)
                                            <div class="mb-1">
                                                <span class="badge bg-secondary">{{ $return['quantity'] }} units</span>
                                                <span class="text-muted">- Rs.{{ number_format($return['amount'], 2) }}</span>
                                                <span class="badge bg-{{ $return['reason'] == 'damaged' ? 'danger' : 'warning' }}">{{ $return['reason'] }}</span>
                                                <span class="text-muted">on {{ $return['date'] }}</span>
                                            </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Purchase Order Items for Return -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-receipt text-info me-2"></i> PO #{{ $selectedPurchaseOrder->order_code }} Items
                            </h5>
                            <p class="text-muted small mb-0">Select return quantity and reason for each item below</p>
                        </div>
                        @if($overallDiscountPerItem > 0)
                        <div class="text-end">
                            <span class="badge bg-success">Overall Discount Applied</span>
                            <p class="small text-muted mb-0">Rs.{{ number_format($overallDiscountPerItem, 2) }} per item</p>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="card-body overflow-auto">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Code</th>
                                    <th>Original Qty</th>
                                    <th>Returned</th>
                                    <th>Total Stock</th>
                                    <th>Available to Return</th>
                                    <th>Return Qty</th>
                                    <th>Return Reason</th>
                                    <th>Unit Price</th>
                                    <th>Item Disc.</th>
                                    <th>Overall Disc.</th>
                                    <th>Net Price</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($returnItems as $index => $item)
                                <tr>
                                    <td>{{ $item['name'] }}</td>
                                    <td>{{ $item['code'] }}</td>
                                    <td>{{ $item['original_qty'] }}</td>
                                    <td>
                                        @if($item['already_returned'] > 0)
                                        <span class="badge bg-warning">{{ $item['already_returned'] }}</span>
                                        @else
                                        <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $item['total_stock'] ?? 0 }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $item['max_qty'] > 0 ? 'bg-success' : 'bg-danger' }}">{{ $item['max_qty'] }}</span>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm" style="width: 80px;" 
                                            min="0" max="{{ $item['max_qty'] }}"
                                            wire:model.lazy="returnItems.{{ $index }}.return_qty"
                                            @if($item['max_qty'] == 0) disabled @endif>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm" wire:model.lazy="returnItems.{{ $index }}.return_reason">
                                            <option value="damaged">Damaged</option>
                                            <option value="defective">Defective</option>
                                            <option value="wrong_item">Wrong Item</option>
                                            <option value="excess">Excess Quantity</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </td>
                                    <td>Rs.{{ number_format($item['unit_price'], 2) }}</td>
                                    <td>
                                        @if($item['discount_percentage'] > 0)
                                        <span class="text-danger fw-bold">{{ $item['discount_percentage'] }}%</span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item['overall_discount_per_unit'] > 0)
                                        <span class="text-danger">-Rs.{{ number_format($item['overall_discount_per_unit'], 2) }}</span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold">Rs.{{ number_format($item['net_unit_price'], 2) }}</td>
                                    <td class="fw-bold text-success">
                                        Rs.{{ number_format($item['return_qty'] * $item['net_unit_price'], 2) }}
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger" wire:click="removeFromReturn({{ $index }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3 bg-light p-3 rounded overflow-auto">
                        <div>
                            <button class="btn btn-outline-danger" wire:click="clearReturnCart">
                                <i class="bi bi-trash me-1"></i> Clear All
                            </button>
                        </div>
                        <span class="fw-bold fs-4 text-warning">Total Return Value: Rs.{{ number_format($totalReturnValue, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-end mt-2">
                        <button class="btn btn-success px-4" wire:click="processReturn">
                            <i class="bi bi-check2-circle me-1"></i> Process Return
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Return Processing Modal -->
    <div wire:ignore.self class="modal fade" id="returnModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-arrow-return-left me-2"></i> Confirm Supplier Return
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Supplier:</strong> {{ $selectedSupplier?->name }}</p>
                            <p><strong>Purchase Order:</strong> #{{ $selectedPurchaseOrder?->order_code }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Return Value:</strong> <span class="text-success fw-bold">Rs.{{ number_format($totalReturnValue, 2) }}</span></p>
                            <p><strong>Items:</strong> {{ count(array_filter($returnItems, fn($item) => $item['return_qty'] > 0)) }}</p>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3">Return Items Summary</h6>
                    <div class="table-responsive overflow-auto">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Return Qty</th>
                                    <th>Reason</th>
                                    <th>Net Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($returnItems as $item)
                                @if($item['return_qty'] > 0)
                                <tr>
                                    <td>
                                        {{ $item['name'] }}
                                        @if($item['total_discount_per_unit'] > 0)
                                        <br><small class="text-muted">(Discounts applied: Rs.{{ number_format($item['total_discount_per_unit'], 2) }}/unit)</small>
                                        @endif
                                    </td>
                                    <td>{{ $item['return_qty'] }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item['return_reason'] == 'damaged' ? 'danger' : ($item['return_reason'] == 'defective' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst(str_replace('_', ' ', $item['return_reason'])) }}
                                        </span>
                                    </td>
                                    <td>Rs.{{ number_format($item['net_unit_price'], 2) }}</td>
                                    <td class="fw-bold">Rs.{{ number_format($item['return_qty'] * $item['net_unit_price'], 2) }}</td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total Return Amount:</td>
                                    <td class="fw-bold text-success">Rs.{{ number_format($totalReturnValue, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Note:</strong> This action will reduce the product stock and create a supplier return record.
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" wire:click="confirmReturn">Confirm Return</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Order Details Modal -->
    <div wire:ignore.self class="modal fade" id="purchaseOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-receipt me-2"></i> Purchase Order Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($purchaseOrderModalData)
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>PO Number:</strong> {{ $purchaseOrderModalData['order_code'] }}</p>
                            <p><strong>Supplier:</strong> {{ $purchaseOrderModalData['supplier_name'] }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Date:</strong> {{ $purchaseOrderModalData['date'] }}</p>
                            <p><strong>Total Amount:</strong> Rs.{{ number_format($purchaseOrderModalData['total_amount'], 2) }}</p>
                            @if($purchaseOrderModalData['overall_discount'] > 0)
                            <p><strong>Overall Discount:</strong> <span class="text-danger">Rs.{{ number_format($purchaseOrderModalData['overall_discount'], 2) }}</span></p>
                            @endif
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3">Purchase Order Items</h6>
                    <div class="table-responsive overflow-auto">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Code</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Item Disc.</th>
                                    <th>Overall Disc.</th>
                                    <th>Net Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrderModalData['items'] as $item)
                                <tr>
                                    <td>{{ $item['product_name'] }}</td>
                                    <td>{{ $item['product_code'] }}</td>
                                    <td>{{ $item['quantity'] }}</td>
                                    <td>Rs.{{ number_format($item['unit_price'], 2) }}</td>
                                    <td>
                                        @if($item['item_discount_percentage'] > 0)
                                        <span class="text-danger fw-bold">{{ $item['item_discount_percentage'] }}%</span>
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td>
                                        @if($item['overall_discount'] > 0)
                                        <span class="text-danger">-Rs.{{ number_format($item['overall_discount'], 2) }}</span>
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td class="fw-bold">Rs.{{ number_format($item['net_price'], 2) }}</td>
                                    <td class="fw-bold">Rs.{{ number_format($item['total'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
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
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    }

    .card-header {
        background-color: white;
        border-bottom: 1px solid #dee2e6;
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
        padding: 0.75rem;
    }

    .btn-group-sm>.btn {
        padding: 0.25rem 0.5rem;
    }

    .modal-header {
        border-bottom: 1px solid #dee2e6;
    }

    .badge {
        font-size: 0.75em;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.025);
    }

    .border-warning {
        border-width: 2px !important;
    }

    .form-select-sm {
        padding: 0.25rem 2.25rem 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
</style>
@endpush

@push('scripts')
<script>
    window.addEventListener('alert', event => {
        Swal.fire('Success', event.detail.message, 'success');
    });

    Livewire.on('show-return-modal', () => {
        var modalEl = document.getElementById('returnModal');
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    });

    Livewire.on('show-purchase-order-modal', () => {
        var modalEl = document.getElementById('purchaseOrderModal');
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    });

    Livewire.on('close-return-modal', () => {
        var modalEl = document.getElementById('returnModal');
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
            modal.hide();
        }
    });

    Livewire.on('reload-page', () => {
        window.location.reload();
    });
</script>
@endpush
