<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-box-seam text-success me-2"></i> Product Stock Details
            </h3>
            <p class="text-muted mb-0">Manage and monitor product inventory levels</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="exportToCSV" class="btn btn-outline-primary text-white">
                <i class="bi bi-download me-2"></i> Export
            </button>
            <button id="printButton" class="btn btn-primary">
                <i class="bi bi-printer me-2"></i> Print
            </button>
        </div>
    </div>

    {{-- Stock Summary Cards --}}
    <div class="row mb-5">
        <!-- Total Products Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card summary-card total h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-container bg-primary bg-opacity-10 me-3">
                            <i class="bi bi-box text-primary fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Products</p>
                            <h4 class="fw-bold mb-0">{{ $ProductStocks->count() }}</h4>
                            <span class="badge bg-primary bg-opacity-10 text-primary">All Items</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Stock Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card summary-card available h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-container bg-success bg-opacity-10 me-3">
                            <i class="bi bi-check-circle text-success fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Available Stock</p>
                            <h4 class="fw-bold mb-0">{{ $ProductStocks->sum('available_stock') }}</h4>
                            <span class="badge bg-success bg-opacity-10 text-success">Ready to Sell</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sold Items Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card summary-card sold h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-container bg-info bg-opacity-10 me-3">
                            <i class="bi bi-cart-check text-info fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Sold Items</p>
                            <h4 class="fw-bold mb-0">{{ $ProductStocks->sum('sold_count') }}</h4>
                            <span class="badge bg-info bg-opacity-10 text-info">Total Sales</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Damaged Stock Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card summary-card damaged h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-container bg-danger bg-opacity-10 me-3">
                            <i class="bi bi-exclamation-triangle text-danger fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Damaged Stock</p>
                            <h4 class="fw-bold mb-0">{{ $ProductStocks->sum('damage_stock') }}</h4>
                            <span class="badge bg-danger bg-opacity-10 text-danger">Needs Attention</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Product Stock Table --}}
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-dark mb-1">
                    <i class="bi bi-list-ul text-primary me-2"></i> Product Stock Inventory
                </h5>
                <p class="text-muted small mb-0">Detailed view of all product stock levels</p>
            </div>
            <div class="search-bar flex-grow-1">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="product-search"
                        wire:model.live="search" placeholder="Search Products...">
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
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 50px;">
                                <input type="checkbox" class="form-check-input" id="selectAll" onclick="toggleAllRows(this)">
                            </th>
                            <th class="ps-4">#</th>
                            <th class="text-center">Image</th>
                            <th>Product Details</th>
                            <th class="text-center">Sold</th>
                            <th class="text-center">Available</th>
                            <th class="text-center">Damage</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($ProductStocks->count() > 0)
                            @foreach ($ProductStocks as $ProductStock)
                            <tr class="table-row" data-id="{{ $ProductStock->id }}">
                                <td class="ps-4" onclick="event.stopPropagation();">
                                    <input type="checkbox" class="form-check-input row-checkbox" onchange="toggleRowHighlight(this)">
                                </td>
                                <td class="ps-4">
                                    <span class="fw-medium text-dark">{{ $loop->iteration }}</span>
                                </td>
                                <td class="text-center">
                                    <img src="{{ $ProductStock->Product_image ? asset('storage/' . $ProductStock->Product_image) : asset('images/product.jpg') }}" 
                                         alt="{{ $ProductStock->Product_name }}"
                                         class="rounded"
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-bold text-dark">{{ $ProductStock->Product_name ?? '-' }}</span>
                                        <div class="text-muted small">
                                            <div>Code: {{ $ProductStock->Product_code ?? '-' }}</div>
                                            <div>Brand: {{ $ProductStock->Product_brand ?? '-' }} | Model: {{ $ProductStock->Product_model ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-dark">{{ $ProductStock->sold_count ?? '0' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $ProductStock->available_stock > 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $ProductStock->available_stock ?? '0' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-medium text-dark">{{ $ProductStock->damage_stock ?? '0' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-dark">{{ $ProductStock->total_stock ?? '0' }}</span>
                                </td>
                                <td class="text-center">
                                    @if($ProductStock->available_stock > 10)
                                        <span class="badge bg-success">In Stock</span>
                                    @elseif($ProductStock->available_stock > 0)
                                        <span class="badge bg-warning">Low Stock</span>
                                    @else
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    No product stock data found
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-center">
                    {{ $ProductStocks->links('livewire.custom-pagination') }}
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
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
    }

    .card-header {
        background-color: white;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 12px 12px 0 0 !important;
        padding: 1.25rem 1.5rem;
    }

    .summary-card {
        border-left: 4px solid;
        transition: all 0.3s ease;
    }

    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
    }

    .summary-card.total {
        border-left-color: #4361ee;
    }

    .summary-card.available {
        border-left-color: #2a9d8f;
    }

    .summary-card.sold {
        border-left-color: #4cc9f0;
    }

    .summary-card.damaged {
        border-left-color: #e63946;
    }

    .icon-container {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    

    .table td {
        vertical-align: middle;
        padding: 1rem 0.75rem;
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

    .btn-outline-primary {
        color: #4361ee;
        border-color: #4361ee;
    }

    .btn-outline-primary:hover {
        background-color: #4361ee;
        border-color: #4361ee;
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

    .table-responsive {
        border-radius: 0 0 12px 12px;
    }

    /* Row selection highlight */
    tr.selected-row {
        background-color: #d1e7dd !important;
    }
// Row selection functions
    function toggleRowHighlight(checkbox) {
        const row = checkbox.closest('tr');
        if (checkbox.checked) {
            row.classList.add('selected-row');
            row.style.backgroundColor = '#d4e6f1';
        } else {
            row.classList.remove('selected-row');
            row.style.backgroundColor = '';
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
            toggleRowHighlight(checkbox);
        });
    }

    
    tr.selected-row {
        background-color: #d4e6f1 !important;
    }

    tr.selected-row:hover {
        background-color: #b8d7e8 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Print functionality
        document.getElementById('printButton').addEventListener('click', function() {
            printProductStockDetails();
        });
    });

    function printProductStockDetails() {
        // Clone the table content
        const tableContent = document.querySelector('.table-responsive').cloneNode(true);
        
        // Create print window
        const printWindow = window.open('', '_blank', 'height=600,width=800');
        
        // Create HTML content for print
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Product Stock Details - Print Report</title>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
                <style>
                    body { 
                        padding: 20px; 
                        font-size: 14px;
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    }
                    
                    .print-header {
                        margin-bottom: 20px;
                        padding-bottom: 15px;
                        border-bottom: 2px solid #4361ee;
                    }
                    
                    .print-header h2 {
                        color: #4361ee;
                        font-weight: 700;
                    }
                    
                    table { 
                        width: 100%; 
                        border-collapse: collapse;
                        margin-top: 15px;
                    }
                    
                    th { 
                        background-color: #f8f9fa !important;
                        color: #6c757d;
                        font-weight: 600;
                        text-transform: uppercase;
                        font-size: 0.8rem;
                        letter-spacing: 0.5px;
                    }
                    
                    th, td { 
                        padding: 10px 8px;
                        border: 1px solid #dee2e6;
                    }
                    
                    .badge {
                        font-size: 0.7rem;
                        padding: 0.25rem 0.5rem;
                    }
                    
                    @media print {
                        .no-print { display: none !important; }
                        body { padding: 10px; }
                        .print-header { margin-bottom: 15px; }
                        th, td { padding: 6px 4px; }
                    }
                    
                    @media (max-width: 768px) {
                        body { padding: 10px; }
                        th, td { padding: 4px; font-size: 12px; }
                    }
                </style>
            </head>
            <body>
                <div class="container-fluid">
                    <div class="print-header d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h2 class="mb-1">
                                <i class="bi bi-box-seam me-2"></i>Product Stock Details
                            </h2>
                            <p class="text-muted mb-0">Inventory Report - Generated on ${new Date().toLocaleString()}</p>
                        </div>
                        <div class="no-print">
                            <button class="btn btn-primary btn-sm" onclick="window.print();">
                                <i class="bi bi-printer me-1"></i>Print
                            </button>
                            <button class="btn btn-secondary btn-sm ms-2" onclick="window.close();">
                                <i class="bi bi-x-circle me-1"></i>Close
                            </button>
                        </div>
                    </div>
                    
                    ${tableContent.outerHTML}
                    
                    <div class="row mt-4 no-print">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                This report shows current inventory levels as of ${new Date().toLocaleString()}
                            </div>
                        </div>
                    </div>
                </div>
            </body>
            </html>
        `);
        
        printWindow.document.close();
        printWindow.focus();
    }
</script>
@endpush
