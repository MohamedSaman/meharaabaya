<div class="container-fluid py-4">
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Total Assigned</h6>
                            <h3 class="mb-0">{{ $totalAssigned }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-box-seam fs-1 text-primary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Total Sold</h6>
                            <h3 class="mb-0">{{ $soldInventory }}</h3>
                            <small class="text-muted">{{ $totalAssigned > 0 ? number_format(($soldInventory/$totalAssigned) * 100, 1) : 0 }}% of assigned</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-cash-stack fs-1 text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Remaining Stock</h6>
                            <h3 class="mb-0">{{ $totalInventory - $soldInventory }}</h3>
                            <small class="text-muted">{{ $totalAssigned > 0 ? number_format((($totalAssigned-$totalSold)/$totalAssigned) * 100, 1) : 0 }}% remaining</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-archive fs-1 text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Sales Value</h6>
                            <h3 class="mb-0">Rs. {{ number_format($totalSoldValue, 2) }}</h3>
                            <small class="text-muted">of Rs. {{ number_format($totalValue, 2) }}</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-currency-dollar fs-1 text-info opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- View Toggle and Search -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <!-- Responsive control layout -->
                    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center gap-3 gap-md-0">
                        <!-- Left side controls (flex column on mobile, row on desktop) -->
                        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3">
                            <!-- View toggle buttons - full width on mobile -->
                            <div class="btn-group w-100 w-md-auto" role="group">
                                <button type="button" 
                                    class="btn {{ $activeView == 'Productes' ? 'btn-primary' : 'btn-outline-primary' }}" 
                                    wire:click="switchView('Productes')">
                                    <i class="bi bi-Product me-1"></i> Product View
                                </button>
                                <button type="button" 
                                    class="btn {{ $activeView == 'batches' ? 'btn-primary' : 'btn-outline-primary' }}" 
                                    wire:click="switchView('batches')">
                                    <i class="bi bi-boxes me-1"></i> Batch View
                                </button>
                            </div>
                            
                            <!-- Status filter dropdown - full width on mobile -->
                            <div class="dropdown w-100 w-md-auto">
                                <button class="btn btn-outline-secondary dropdown-toggle w-100 w-md-auto" type="button" id="statusFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-funnel me-1"></i> All Statuses
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="statusFilterDropdown">
                                    <li><a class="dropdown-item status-filter" href="#" data-status="all">All Statuses</a></li>
                                    <li><a class="dropdown-item status-filter" href="#" data-status="pending">Pending</a></li>
                                    <li><a class="dropdown-item status-filter" href="#" data-status="partial">Partial</a></li>
                                    <li><a class="dropdown-item status-filter" href="#" data-status="completed">Completed</a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Search box - full width on mobile -->
                        <div class="search-box w-100 w-md-auto" style="min-width: 250px;">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" 
                                    id="stockSearchInput"
                                    class="form-control border-start-0" 
                                    placeholder="Search Productes..."
                                    autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="row">
        @if($activeView == 'Productes')
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Product Stock Overview</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0" id="ProductViewTable">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Product Details</th>
                                        <th scope="col" class="text-center">Assigned</th>
                                        <th scope="col" class="text-center">Sold</th>
                                        <th scope="col" class="text-center">Remaining</th>
                                        <th scope="col">Sales Progress</th>
                                        <th scope="col" class="text-end">Value</th>
                                        <th scope="col" class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($Productes as $ProductData)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $ProductData['Product']->image ? asset('storage/' . $ProductData['Product']->image) : asset('images/product.jpg') }}" 
                                                        class="rounded me-3" 
                                                        height="50" width="50" 
                                                        alt="{{ $ProductData['Product']->name }}">
                                                    <div>
                                                        <h6 class="mb-0">{{ $ProductData['Product']->name }}</h6>
                                                        <small class="text-muted d-block">{{ $ProductData['Product']->code }}</small>
                                                        <div class="mt-1">
                                                            <span class="badge bg-light text-dark">{{ $ProductData['Product']->brand }}</span>
                                                            @if($ProductData['Product']->type)
                                                                <span class="badge bg-light text-dark">{{ $ProductData['Product']->type }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="fw-bold">{{ $ProductData['total_quantity'] }}</span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="fw-bold text-success">{{ $ProductData['sold_quantity'] }}</span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="fw-bold text-{{ $ProductData['remaining_quantity'] > 0 ? 'warning' : 'muted' }}">
                                                    {{ $ProductData['remaining_quantity'] }}
                                                </span>
                                            </td>
                                            <td class="align-middle" style="width: 20%">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <small>{{ $ProductData['progress_percentage'] }}% Complete</small>
                                                </div>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-{{ 
                                                        $ProductData['progress_percentage'] == 100 ? 'success' : 
                                                        ($ProductData['progress_percentage'] > 75 ? 'info' : 
                                                        ($ProductData['progress_percentage'] > 50 ? 'primary' : 
                                                        ($ProductData['progress_percentage'] > 25 ? 'warning' : 'danger'))) 
                                                    }}" style="width: {{ $ProductData['progress_percentage'] }}%"></div>
                                                </div>
                                            </td>
                                            <td class="text-end align-middle">
                                                <span>Rs. {{ number_format($ProductData['sold_value'], 2) }}</span>
                                                <br/>
                                                <small class="text-muted">of Rs. {{ number_format($ProductData['total_value'], 2) }}</small>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge bg-{{ 
                                                    $ProductData['status'] == 'pending' ? 'warning' : 
                                                    ($ProductData['status'] == 'partial' ? 'info' : 'success') 
                                                }}">
                                                    {{ ucfirst($ProductData['status']) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                                <p class="text-muted mt-2">
                                                    @if(empty($searchQuery))
                                                        No Productes have been assigned to you yet.
                                                    @else
                                                        No Productes match your search query.
                                                    @endif
                                                </p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Sidebar with Sales List -->
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Assigned Stock Batches</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" style="max-height: 600px; overflow-y: auto;">
                            @forelse ($staffSales as $sale)
                                <button type="button" 
                                    class="list-group-item list-group-item-action {{ $selectedSaleId == $sale->id ? 'active' : '' }}"
                                    wire:click="viewSaleDetails({{ $sale->id }})">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Batch #{{ $sale->id }}</h6>
                                        <small>{{ $sale->created_at->format('d/m/Y') }}</small>
                                    </div>
                                    <p class="mb-1">{{ $sale->total_quantity }} items assigned</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small>{{ $sale->sold_quantity }} sold</small>
                                        
                                        <span class="badge bg-{{ 
                                            $sale->status == 'pending' ? 'warning' : 
                                            ($sale->status == 'partial' ? 'info' : 'success') 
                                        }}">
                                            {{ ucfirst($sale->status) }}
                                        </span>
                                    </div>
                                    <div class="progress mt-2" style="height: 5px;">
                                        <div class="progress-bar bg-success" 
                                            style="width: {{ ($sale->total_quantity > 0 ? ($sale->sold_quantity / $sale->total_quantity) * 100 : 0) }}%"></div>
                                    </div>
                                </button>
                            @empty
                                <div class="text-center py-4">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">No stock has been assigned to you yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content Area - Batch Details -->
            <div class="col-md-9">
                @if ($showSaleDetails && $selectedSale)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Stock Details - Batch #{{ $selectedSale->id }}</h5>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="p-3">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Assigned By:</strong> {{ $selectedSale->admin->name }}</p>
                                        <p class="mb-1"><strong>Assigned On:</strong> {{ $selectedSale->created_at->format('d/m/Y h:i A') }}</p>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <p class="mb-1"><strong>Total Items:</strong> {{ $selectedSale->total_quantity }}</p>
                                        <p class="mb-1"><strong>Total Value:</strong> Rs. {{ number_format($selectedSale->total_value, 2) }}</p>
                                    </div>
                                </div>
                                
                                <!-- Progress overview -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span><strong>Sales Progress:</strong> {{ $selectedSale->sold_quantity }} of {{ $selectedSale->total_quantity }} items sold</span>
                                        <span>{{ $selectedSale->total_quantity > 0 ? number_format(($selectedSale->sold_quantity / $selectedSale->total_quantity) * 100, 1) : 0 }}%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" 
                                            style="width: {{ $selectedSale->total_quantity > 0 ? ($selectedSale->sold_quantity / $selectedSale->total_quantity) * 100 : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Products Table -->
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0" id="batchViewTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Product Details</th>
                                            <th scope="col" class="text-center">Assigned</th>
                                            <th scope="col" class="text-center">Sold</th>
                                            <th scope="col" class="text-center">Remaining</th>
                                            <th scope="col">Sales Progress</th>
                                            <th scope="col" class="text-end">Unit Price</th>
                                            <th scope="col" class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($products as $product)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ ($product->Product && $product->Product->image) ? asset('storage/' . $product->Product->image) : asset('images/product.jpg') }}" 
                                                            class="rounded me-3" 
                                                            height="40" width="40" 
                                                            alt="{{ $product->Product->name ?? 'Product' }}">
                                                        <div>
                                                            <h6 class="mb-0">{{ $product->Product->name ?? 'Unknown Product' }}</h6>
                                                            <small class="text-muted">{{ $product->Product->code ?? 'No Code' }}</small><br>
                                                            <small class="text-muted">{{ $product->Product->brand ?? '' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <span class="fw-bold">{{ $product->quantity }}</span>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <span class="fw-bold text-success">{{ $product->sold_quantity }}</span>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <span class="fw-bold text-{{ ($product->quantity - $product->sold_quantity) > 0 ? 'warning' : 'muted' }}">
                                                        {{ $product->quantity - $product->sold_quantity }}
                                                    </span>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="progress" style="height: 8px;">
                                                        <div class="progress-bar bg-{{ 
                                                            $product->quantity > 0 && $product->sold_quantity >= $product->quantity ? 'success' : 'primary' 
                                                        }}" style="width: {{ $product->quantity > 0 ? ($product->sold_quantity / $product->quantity) * 100 : 0 }}%"></div>
                                                    </div>
                                                    <small class="mt-1 d-block">
                                                        {{ $product->quantity > 0 ? number_format(($product->sold_quantity / $product->quantity) * 100, 1) : 0 }}% Complete
                                                    </small>
                                                </td>
                                                <td class="text-end align-middle">
                                                    <span>Rs. {{ number_format($product->unit_price, 2) }}</span>
                                                    @if($product->discount_per_unit > 0)
                                                        <br>
                                                        <small class="text-success">
                                                            Discount: Rs. {{ number_format($product->discount_per_unit, 2) }}
                                                        </small>
                                                    @endif
                                                </td>
                                                <td class="text-center align-middle">
                                                    <span class="badge bg-{{ 
                                                        $product->status == 'pending' ? 'warning' : 
                                                        ($product->status == 'partial' ? 'info' : 'success') 
                                                    }}">
                                                        {{ ucfirst($product->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    @if(empty($searchQuery))
                                                        <p class="text-muted mb-0">No products found in this batch.</p>
                                                    @else
                                                        <p class="text-muted mb-0">No products match your search query.</p>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-box2 fs-1 text-muted"></i>
                        <h5 class="mt-3">No Stock Details Selected</h5>
                        <p class="text-muted">Select a batch from the left to view details.</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .list-group-item.active {
        background-color: #f0f7ff;
        color: #0d6efd;
        border-color: #dee2e6;
        font-weight: 500;
    }
    
    .list-group-item.active .badge {
        background-color: #0d6efd !important;
    }
    
    .search-box .form-control:focus {
        box-shadow: none;
        border-color: #ced4da;
    }
    
    .search-box .input-group-text {
        color: #6c757d;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize search functionality
    initializeSearch();
    initializeStatusFilter();
    
    // Initialize again after Livewire updates
    window.addEventListener('livewire:load', function() {
        initializeSearch();
        initializeStatusFilter();
    });
    
    window.addEventListener('livewire:update', function() {
        initializeSearch();
        initializeStatusFilter();
    });
    
    function initializeStatusFilter() {
        const statusFilters = document.querySelectorAll('.status-filter');
        const statusDropdownButton = document.querySelector('#statusFilterDropdown');
        let currentStatusFilter = 'all';
        
        statusFilters.forEach(filter => {
            filter.addEventListener('click', function(e) {
                e.preventDefault();
                const status = this.getAttribute('data-status');
                currentStatusFilter = status;
                
                // Update dropdown button text
                statusDropdownButton.innerHTML = `<i class="bi bi-funnel me-1"></i> ${this.textContent}`;
                
                // Apply both filters (search and status)
                applyFilters(document.getElementById('stockSearchInput').value, status);
            });
        });
    }
    
    function applyFilters(searchTerm, statusFilter) {
        searchTerm = searchTerm.toLowerCase();
        
        // Determine which view is active
        const isProductView = document.querySelector('.btn-primary[wire\\:click="switchView(\'Productes\')"]') !== null;
        
        if (isProductView) {
            filterProductView(searchTerm, statusFilter);
        } else {
            filterBatchView(searchTerm, statusFilter);
        }
    }
    
    function filterProductView(searchTerm, statusFilter) {
        const ProductTable = document.querySelector('#ProductViewTable');
        if (!ProductTable) return;
        
        const rows = ProductTable.querySelectorAll('tbody tr:not(.no-results-row)');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const ProductName = row.querySelector('h6.mb-0')?.textContent || '';
            const ProductCode = row.querySelector('small.text-muted')?.textContent || '';
            const ProductBrand = row.querySelector('.badge')?.textContent || '';
            const statusBadge = row.querySelector('td:last-child .badge')?.textContent?.toLowerCase() || '';
            
            const matchesSearch = searchTerm === '' || 
                ProductName.toLowerCase().includes(searchTerm) || 
                ProductCode.toLowerCase().includes(searchTerm) || 
                ProductBrand.toLowerCase().includes(searchTerm);
                
            const matchesStatus = statusFilter === 'all' || 
                statusBadge.includes(statusFilter.toLowerCase());
            
            if (matchesSearch && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        updateNoResultsMessage(ProductTable, visibleCount, rows.length);
    }
    
    function filterBatchView(searchTerm, statusFilter) {
        const batchTable = document.querySelector('#batchViewTable');
        if (!batchTable) return;
        
        const rows = batchTable.querySelectorAll('tbody tr:not(.no-results-row)');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const ProductInfo = row.querySelector('td:first-child');
            if (!ProductInfo) return;
            
            const ProductName = ProductInfo.querySelector('h6')?.textContent || '';
            const ProductCode = ProductInfo.querySelector('small:nth-child(1)')?.textContent || '';
            const ProductBrand = ProductInfo.querySelector('small:nth-child(2)')?.textContent || '';
            const statusBadge = row.querySelector('td:last-child .badge')?.textContent?.toLowerCase() || '';
            
            const matchesSearch = searchTerm === '' || 
                ProductName.toLowerCase().includes(searchTerm) || 
                ProductCode.toLowerCase().includes(searchTerm) || 
                ProductBrand.toLowerCase().includes(searchTerm);
                
            const matchesStatus = statusFilter === 'all' || 
                statusBadge.includes(statusFilter.toLowerCase());
            
            if (matchesSearch && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        updateNoResultsMessage(batchTable, visibleCount, rows.length);
    }
    
    function updateNoResultsMessage(table, visibleCount, totalCount) {
        const tbody = table.querySelector('tbody');
        const noResultsRow = table.querySelector('tbody tr.no-results-row');
        
        if (noResultsRow) noResultsRow.remove();
        
        if (visibleCount === 0 && totalCount > 0) {
            const newRow = document.createElement('tr');
            newRow.className = 'no-results-row';
            newRow.innerHTML = `
                <td colspan="7" class="text-center py-4">
                    <i class="bi bi-search fs-1 text-muted"></i>
                    <p class="text-muted mt-2">No Productes match your current filters.</p>
                </td>
            `;
            tbody.appendChild(newRow);
        }
    }
    
    function initializeSearch() {
        const searchInput = document.getElementById('stockSearchInput');
        if (!searchInput) return;
        
        searchInput.addEventListener('keyup', function() {
            // Get current status filter
            const statusDropdownButton = document.querySelector('#statusFilterDropdown');
            const currentStatus = statusDropdownButton.textContent.trim().includes('All') ? 
                'all' : statusDropdownButton.textContent.trim().replace(/[^a-zA-Z]/g, '').toLowerCase();
                
            applyFilters(this.value, currentStatus);
        });
        
        // Clear search when switching views
        document.querySelectorAll('.btn-group button').forEach(button => {
            button.addEventListener('click', function() {
                searchInput.value = '';
                // Also reset status filter
                const statusDropdownButton = document.querySelector('#statusFilterDropdown');
                statusDropdownButton.innerHTML = '<i class="bi bi-funnel me-1"></i> All Statuses';
                // Trigger a keyup event to reset search
                searchInput.dispatchEvent(new Event('keyup'));
            });
        });
    }
});
</script>
@endpush
