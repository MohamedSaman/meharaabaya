<div class="container-fluid p-3">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-file-earmark-text text-success me-2"></i> Quotation Management
            </h3>
            <p class="text-muted mb-0">Review, manage and track all quotations efficiently</p>
        </div>
    </div>

    <!-- Message -->
    @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if (session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if (session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Quotations Table -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">
                            <i class="bi bi-journal-text text-primary me-2"></i> Quotation List
                        </h5>
                        <p class="text-muted small mb-0">View and manage all quotations in your system</p>
                    </div>
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" style="width: 60%; margin: auto">
                        <!-- 🔍 Search Bar -->
                        <div class="search-bar flex-grow-1">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" wire:model.live="search"
                                    placeholder="Search by quotation number, customer name or phone...">
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
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" style="width: 50px;">
                                        <input type="checkbox" class="form-check-input" id="selectAll" onclick="toggleAllRows(this)">
                                    </th>
                                    <th class="ps-4">No</th>
                                    <th>Quotation No</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th class="text-end pe-5">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($quotations->count() > 0)
                                @foreach($quotations as $quotation)
                                <tr class="table-row" data-id="{{ $quotation->id }}">
                                    <td class="ps-4" onclick="event.stopPropagation();">
                                        <input type="checkbox" class="form-check-input row-checkbox" onchange="toggleRowHighlight(this)">
                                    </td>
                                    <td class="ps-4" wire:click="viewQuotation({{ $quotation->id }})">
                                        <span class="fw-medium text-dark">{{ $loop->iteration }}</span>
                                    </td>
                                    <td class="fw-bold text-primary" wire:click="viewQuotation({{ $quotation->id }})">{{ $quotation->quotation_number }}</td>
                                    <td wire:click="viewQuotation({{ $quotation->id }})">
                                        <strong class="fw-medium text-dark">{{ $quotation->customer_name }}</strong>
                                        <div class="text-muted small">{{ $quotation->customer_phone }}</div>
                                    </td>
                                    <td wire:click="viewQuotation({{ $quotation->id }})">
                                        <span class="fw-medium text-dark">{{ $quotation->quotation_date->format('d/m/Y') }}</span>
                                    </td>
                                    <td wire:click="viewQuotation({{ $quotation->id }})">
                                        <span class="fw-bold text-success">Rs. {{ number_format($quotation->total_amount, 2) }}</span>
                                    </td>
                                    <td wire:click="viewQuotation({{ $quotation->id }})">
                                        <span class="badge bg-{{ $quotation->status === 'converted' ? 'success' : ($quotation->status === 'draft' ? 'secondary' : 'primary') }}">
                                            {{ ucfirst($quotation->status) }}
                                        </span>
                                    </td>
                                   <td class="text-end pe-4">
    <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false">
            <i class="bi bi-gear-fill"></i> Actions
        </button>

        <ul class="dropdown-menu dropdown-menu-end">

            <!-- Edit Quotation (only if not converted) -->
            @if($quotation->status !== 'converted')
            <li>
                <a href="{{ route('admin.quotation-system', ['quotation' => $quotation->id]) }}" 
                   class="dropdown-item">
                    <i class="bi bi-pencil-square text-warning me-2"></i> Edit Quotation
                </a>
            </li>
            @endif

            <!-- Create Sale (only if not converted) -->
            @if($quotation->status !== 'converted')
            <li>
                <button class="dropdown-item"
                        wire:click="openCreateSaleModal({{ $quotation->id }})"
                        wire:loading.attr="disabled"
                        wire:target="openCreateSaleModal({{ $quotation->id }})">

                    <span wire:loading wire:target="openCreateSaleModal({{ $quotation->id }})">
                        <i class="spinner-border spinner-border-sm me-2"></i>
                        Loading...
                    </span>
                    <span wire:loading.remove wire:target="openCreateSaleModal({{ $quotation->id }})">
                        <i class="bi bi-cart-check text-success me-2"></i>
                        Create Sale
                    </span>
                </button>
            </li>
            @endif

            <!-- Delete Quotation -->
            <li>
                <button class="dropdown-item"
                        wire:click="confirmDeleteQuotation({{ $quotation->id }})"
                        wire:loading.attr="disabled"
                        wire:target="confirmDeleteQuotation({{ $quotation->id }})">

                    <span wire:loading wire:target="confirmDeleteQuotation({{ $quotation->id }})">
                        <i class="spinner-border spinner-border-sm me-2"></i>
                        Loading...
                    </span>
                    <span wire:loading.remove wire:target="confirmDeleteQuotation({{ $quotation->id }})">
                        <i class="bi bi-trash text-danger me-2"></i>
                        Delete
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
                                    <td colspan="7" class="text-center py-5">
                                        <div class="alert alert-primary bg-opacity-10">
                                            <i class="bi bi-info-circle me-2"></i> No quotations found.
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @if ($quotations->hasPages())
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-center">
                            {{ $quotations->links('livewire.custom-pagination') }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Create Sale Modal -->
    <div wire:ignore.self class="modal fade" id="createSaleModal" tabindex="-1"
        aria-labelledby="createSaleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-cart-check text-success me-2 "></i> Create Sale from Quotation
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        wire:click="closeCreateSaleModal"></button>
                </div>
                <div class="modal-body">
                    @if($selectedQuotation)
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6 class="fw-bold text-primary">Quotation Information</h6>
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p class="mb-1"><strong>Quotation #:</strong> {{ $selectedQuotation->quotation_number }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="mb-1"><strong>Customer:</strong> {{ $selectedQuotation->customer_name }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="mb-0"><strong>Quotation Total:</strong> Rs. {{ number_format($selectedQuotation->total_amount, 2) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sale Items -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0">Sale Items</h6>
                            <button type="button" class="btn btn-sm btn-primary" wire:click="addNewItem">
                                <i class="bi bi-plus-circle me-1"></i> Add New Product
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="30%">Product</th>
                                            <th width="10%">Qty</th>
                                            <th width="15%">Unit Price (Rs.)</th>
                                            <th width="15%">Discount/Unit (Rs.)</th>
                                            <th width="15%">Total Discount (Rs.)</th>
                                            <th width="15%">Total (Rs.)</th>
                                            <th width="5%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($editableItems as $index => $item)
                                        <tr>
                                            <td>
                                                <div class="position-relative">
                                                    @if($item['product_name'])
                                                    <div class="mb-2">
                                                        <small class="text-success fw-bold d-block">{{ $item['product_name'] }}</small>
                                                        <small class="text-muted">Code: {{ $item['product_code'] }} | Model: {{ $item['product_model'] }} | Stock: {{ $item['current_stock'] }}</small>
                                                    </div>
                                                    @else
                                                    <input type="text"
                                                        class="form-control form-control-sm"
                                                        placeholder="Search and select product..."
                                                        wire:model="searchTerms.{{ $index }}"
                                                        wire:key="search-{{ $index }}"
                                                        wire:keydown.debounce.500ms="showSearchResult({{ $index }}, $event.target.value)">

                                                    <!-- Search Results -->
                                                    @if($showSearchResults[$index] ?? false)
                                                    <div class="search-results border rounded p-2 mt-1 bg-white shadow"
                                                        style="max-height: 200px; overflow-y: auto; position: absolute; z-index: 1050; width: calc(100% - 30px);">
                                                        @php
                                                        $searchResults = $this->searchProducts($searchTerms[$index]);
                                                        @endphp
                                                        @if(count($searchResults) > 0)
                                                        @foreach($searchResults as $product)
                                                        <div class="search-result-item p-2 border-bottom cursor-pointer"
                                                            wire:click="selectProduct({{ $index }}, {{ $product['id'] }})"
                                                            wire:key="product-{{ $product['id'] }}-{{ $index }}">
                                                            <strong>{{ $product['name'] }}</strong>
                                                            <br>
                                                            <small>Code: {{ $product['code'] }} | Model: {{ $product['model'] }}</small>
                                                            <br>
                                                            <small>Price: Rs.{{ number_format($product['price'], 2) }} | Discount: Rs.{{ number_format($product['discount_price'], 2) }} | Stock: {{ $product['stock'] }}</small>
                                                        </div>
                                                        @endforeach
                                                        @else
                                                        <div class="text-muted p-2">No products found for "{{ $searchTerms[$index] }}"</div>
                                                        @endif
                                                    </div>
                                                    @endif
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number"
                                                    class="form-control form-control-sm"
                                                    wire:model="editableItems.{{ $index }}.quantity"
                                                    wire:change="updateItemQuantity({{ $index }}, $event.target.value)"
                                                    min="1"
                                                    max="{{ $item['current_stock'] }}">
                                                <small class="text-muted">Max: {{ $item['current_stock'] }}</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-bold">Rs. {{ number_format($item['unit_price'], 2) }}</span>
                                            </td>
                                            <td>
                                                <input type="number"
                                                    class="form-control form-control-sm"
                                                    wire:model="editableItems.{{ $index }}.discount_per_unit"
                                                    wire:change="updateItemDiscount({{ $index }}, $event.target.value)"
                                                    step="0.01" min="0" max="{{ $item['unit_price'] }}">
                                            </td>
                                            <td class="text-end fw-bold text-danger">
                                                Rs. {{ number_format($item['total_discount'], 2) }}
                                            </td>
                                            <td class="text-end fw-bold text-success">
                                                Rs. {{ number_format($item['total'], 2) }}
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    wire:click="removeItem({{ $index }})"
                                                    {{ count($editableItems) <= 1 ? 'disabled' : '' }}>
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Discount and Totals -->
                    <div class="row mt-4">
                        <div class="col-md-8">
                            <!-- Combined Discount Field -->
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">Additional Discount</h6>
                                    <div class="row align-items-end">
                                        <div class="col-md-6">
                                            <label class="form-label">Discount Type & Value</label>
                                            <div class="input-group mb-2">
                                                <select class="form-select" wire:model.live="saleData.additional_discount_type" style="max-width: 100px;">
                                                    <option value="fixed">Rs.</option>
                                                    <option value="percentage">%</option>
                                                </select>
                                                <input type="number"
                                                    class="form-control"
                                                    wire:model.live="saleData.additional_discount"
                                                    wire:change="calculateTotals"
                                                    step="0.01"
                                                    min="0"
                                                    @if($saleData['additional_discount_type']==='percentage' ) max="100" @endif
                                                    placeholder="Enter discount">
                                            </div>
                                            <small class="text-muted">
                                                @if($saleData['additional_discount_type'] === 'percentage')
                                                Percentage discount applied to subtotal
                                                @else
                                                Fixed amount discount
                                                @endif
                                            </small>
                                        </div>

                                    </div>

                                    <!-- Display current discount info from quotation -->
                                    @if($selectedQuotation && ($selectedQuotation->additional_discount > 0 || $selectedQuotation->additional_discount_value > 0))
                                    <div class="mt-2 p-2 bg-info bg-opacity-10 rounded">
                                        <small class="text-info">
                                            <i class="bi bi-info-circle"></i>
                                            Quotation had additional discount:
                                            @if($selectedQuotation->additional_discount_type === 'percentage')
                                            {{ $selectedQuotation->additional_discount_value ?? $selectedQuotation->additional_discount }}%
                                            (Rs. {{ number_format($selectedQuotation->additional_discount ?? 0, 2) }})
                                            @else
                                            Rs. {{ number_format($selectedQuotation->additional_discount_value ?? $selectedQuotation->additional_discount ?? 0, 2) }}
                                            @endif
                                        </small>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="mt-3">
                                <label class="form-label">Sale Notes</label>
                                <textarea class="form-control" wire:model="saleData.notes" rows="3"
                                    placeholder="Any additional notes for this sale..."></textarea>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Pricing Summary -->
                            <div class="card border-0 bg-light">
                                <div class="card-header bg-transparent">
                                    <h6 class="fw-bold mb-0">Pricing Summary</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold">Subtotal:</td>
                                                <td class="text-end">Rs. {{ number_format($subtotal, 2) }}</td>
                                            </tr>

                                            <!-- Item Discount -->
                                            @if($totalDiscount > 0)
                                            <tr>
                                                <td class="fw-bold text-danger">Item Discount:</td>
                                                <td class="text-end text-danger">- Rs. {{ number_format($totalDiscount, 2) }}</td>
                                            </tr>
                                            @endif

                                            <!-- Additional Discount -->
                                            @if($additionalDiscountAmount > 0)
                                            <tr>
                                                <td class="fw-bold text-danger">Additional Discount:</td>
                                                <td class="text-end text-danger">- Rs. {{ number_format($additionalDiscountAmount, 2) }}</td>
                                            </tr>
                                            @endif

                                            <tr class="border-top">
                                                <td class="fw-bold fs-5">Grand Total:</td>
                                                <td class="text-end fw-bold fs-5 text-success">Rs. {{ number_format($grandTotal, 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeCreateSaleModal">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-success" wire:click="createSale"
                        wire:loading.attr="disabled">
                        <i class="bi bi-cart-check me-1" wire:loading.class="d-none"></i>
                        <span wire:loading wire:target="createSale">
                            <i class="spinner-border spinner-border-sm me-1"></i>
                        </span>
                        Create Sale
                    </button>
                </div>
            </div>
        </div>
    </div>

<!-- Professional Quotation View Modal -->
<div wire:ignore.self class="modal fade" id="viewQuotationModal" tabindex="-1"
     aria-labelledby="viewQuotationModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="printableQuotation">

           
            <div class="screen-only-header p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <img src="{{ asset('images/usn-quotation.png') }}" alt="header" class="img-fluid" style="width: 100%;">
                        </div>
                    </div>
                    <hr class="my-2" style="border-top: 2px solid #000;">
                </div>

            @if($selectedQuotation)
            <div class="modal-body">

                {{-- Quotation + Customer info (two columns) --}}
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Customer :</strong><br>
                        {{ $selectedQuotation->customer_name }}<br>
                        {{ $selectedQuotation->customer_address ?? '' }}<br>
                        Tel: {{ $selectedQuotation->customer_phone }}<br>
                        @if($selectedQuotation->customer_email)
                        Email: {{ $selectedQuotation->customer_email }}
                        @endif
                    </div>
                    <div class="col-6 text-end">
                        <table class="table table-sm table-borderless">
                            <tr><td><strong>Quotation No :</strong></td><td>{{ $selectedQuotation->quotation_number }}</td></tr>
                            <tr><td><strong>Quotation Date :</strong></td><td>{{ $selectedQuotation->quotation_date->format('d/m/Y') }}</td></tr>
                            @if($selectedQuotation->valid_until)
                            <tr><td><strong>Valid Until :</strong></td><td>{{ \Carbon\Carbon::parse($selectedQuotation->valid_until)->format('d/m/Y') }}</td></tr>
                            @endif
                            <tr><td><strong>Status :</strong></td><td>{{ ucfirst($selectedQuotation->status) }}</td></tr>
                           
                        </table>
                    </div>
                </div>

                {{-- Items table --}}
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th style="width:5%">#</th>
                                <th style="width:15%">ITEM CODE</th>
                                <th>DESCRIPTION</th>
                                <th class="text-center" style="width:12%">QTY</th>
                                <th class="text-end" style="width:12%">UNIT PRICE</th>
                                <th class="text-end" style="width:12%">SUBTOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($selectedQuotation->items && count($selectedQuotation->items) > 0)
                            @foreach($selectedQuotation->items as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item['product_code'] ?? 'N/A' }}</td>
                                <td>
                                    {{ $item['product_name'] ?? $item['name'] ?? 'N/A' }}
                                    @if(isset($item['product_model']) && $item['product_model'])
                                    <br><small class="text-muted">Model: {{ $item['product_model'] }}</small>
                                    @endif
                                </td>
                                <td class="text-center">{{ $item['quantity'] ?? 0 }} Pc(s)</td>
                                <td class="text-end">{{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                                <td class="text-end">{{ number_format($item['total'] ?? (($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0)), 2) }}</td>
                            </tr>
                            @endforeach
                            @else
                            <tr><td colspan="6" class="text-center text-muted">No items</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Totals – right-aligned block --}}
                <div class="row">
                    <div class="col-7"></div>
                    <div class="col-5">
                        <table class="table table-sm table-borderless">
                            <tr><td class="text-end"><strong>Subtotal (LKR)</strong></td><td class="text-end">{{ number_format($selectedQuotation->subtotal, 2) }}</td></tr>
                            
                            @php
                            $totalDiscount = $selectedQuotation->discount_amount + $selectedQuotation->additional_discount;
                            @endphp
                            
                            @if($totalDiscount > 0)
                            <tr>
                                <td class="text-end">
                                    <strong>Total Discount</strong>
                                    @if($selectedQuotation->discount_amount > 0 && $selectedQuotation->additional_discount > 0)
                                    <br><small class="text-muted">(Item: Rs.{{ number_format($selectedQuotation->discount_amount, 2) }} + Additional: Rs.{{ number_format($selectedQuotation->additional_discount, 2) }})</small>
                                    @elseif($selectedQuotation->discount_amount > 0)
                                    <br><small class="text-muted">(Item Discount)</small>
                                    @elseif($selectedQuotation->additional_discount > 0)
                                    <br><small class="text-muted">(Additional Discount)</small>
                                    @endif
                                </td>
                                <td class="text-end text-danger">- {{ number_format($totalDiscount, 2) }}</td>
                            </tr>
                            @endif
                            
                            <tr><td class="text-end"><strong>Total Amount (LKR)</strong></td><td class="text-end">{{ number_format($selectedQuotation->total_amount, 2) }}</td></tr>
                        </table>
                    </div>
                </div>

                {{-- Terms & Conditions --}}
                @if($selectedQuotation->terms_conditions)
                <div class="mt-4">
                    <h6 class="fw-bold">Terms & Conditions:</h6>
                    <p class="small text-muted mb-0">{!! nl2br(e($selectedQuotation->terms_conditions)) !!}</p>
                </div>
                @endif

                {{-- Footer – logos + address + note --}}
                <div class="mt-4 text-center small">
                    <p class="mb-0">
                        <strong>ADDRESS :</strong> {{ config('shop.address', 'NO 92, PASYALA ROAD, KAL-ELIYA, SRI-LANKA.') }}<br>
                        <strong>TEL :</strong> {{ config('shop.phone', '+94 70 58 53 442') }}, <strong>EMAIL :</strong> {{ config('shop.email', 'phoenixbathware@gmail.com') }}
                    </p>
                    
                </div>

            </div>
            @endif

            {{-- Modal footer buttons --}}
            <div class="modal-footer bg-light justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Close
                </button>
                <div>
                    <button type="button" class="btn btn-success me-2" wire:click="downloadQuotation">
                        <i class="bi bi-download me-2"></i>Download Quotation
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Delete Confirmation Modal -->
    <div wire:ignore.self class="modal fade" id="deleteConfirmationModal" tabindex="-1"
        aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-exclamation-triangle me-2"></i> Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this quotation? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" wire:click="deleteQuotation" data-bs-dismiss="modal">
                        <i class="bi bi-trash me-1"></i> Delete Quotation
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 2px;
        transition: all 0.2s;
        border: 1px solid transparent;
    }

    .action-btn.view {
        color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.1);
    }

    .action-btn.view:hover {
        background-color: rgba(13, 110, 253, 0.2);
    }

    .action-btn.sale {
        color: #198754;
        background-color: rgba(25, 135, 84, 0.1);
    }

    .action-btn.sale:hover {
        background-color: rgba(25, 135, 84, 0.2);
    }

    .action-btn.success {
        color: #6c757d;
        background-color: rgba(108, 117, 125, 0.1);
    }

    .action-btn.delete {
        color: #dc3545;
        background-color: rgba(220, 53, 69, 0.1);
    }

    .action-btn.delete:hover {
        background-color: rgba(220, 53, 69, 0.2);
    }

    .search-results {
        background: white;
        z-index: 1050;
        border: 1px solid #dee2e6;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .search-result-item {
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .search-result-item:hover {
        background-color: #f8f9fa;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .discount-toggle-btn {
        min-width: 60px;
    }

    /* Disable number input arrows */
    input[type=number]::-webkit-outer-spin-button,
    input[type=number]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type=number] {
        -moz-appearance: textfield;
    }

    /* Quotation View Modal Styles */
    .quotation-template {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .quotation-header {
        background: linear-gradient(135deg, #3b5b0c 0%, #8eb922 100%);
    }

    .company-info h6,
    .client-info h6 {
        color: #0d6efd;
    }

    .table-light th {
        background-color: #f8f9fa;
        color: #495057;
        font-weight: 600;
    }

    .table-bordered {
        border: 1px solid #dee2e6;
    }

    .table-bordered th,
    .table-bordered td {
        border: 1px solid #dee2e6;
    }

    .table th {
            border-top: none;
            font-weight: 600;
            color: #ffffff;
            
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

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

    .table-active {
        background-color: rgba(13, 110, 253, 0.1) !important;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .action-btns {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .action-btn {
            width: 32px;
            height: 32px;
        }

        .modal-dialog {
            margin: 0.5rem;
        }
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

    document.addEventListener('DOMContentLoaded', function() {
        // Handle view modal
        Livewire.on('showViewModal', () => {
            const modal = new bootstrap.Modal(document.getElementById('viewQuotationModal'));
            modal.show();
        });

        // Handle create sale modal
        Livewire.on('showCreateSaleModal', () => {
            const modal = new bootstrap.Modal(document.getElementById('createSaleModal'));
            modal.show();
        });

        Livewire.on('closeCreateSaleModal', () => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('createSaleModal'));
            if (modal) {
                modal.hide();
            }
        });

        // Handle delete confirmation modal
        Livewire.on('show-delete-confirmation', () => {
            const modal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
            modal.show();
        });

        // Handle success messages
        Livewire.on('show-success', (message) => {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: message,
                confirmButtonColor: '#198754',
                timer: 3000
            });
        });

        // Handle error messages
        Livewire.on('show-error', (message) => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                confirmButtonColor: '#dc3545'
            });
        });

        // Handle product selection
        Livewire.on('product-selected', () => {
            // Close any open search results
            const searchResults = document.querySelectorAll('.search-results');
            searchResults.forEach(result => {
                result.style.display = 'none';
            });
        });

        // Auto-calculate when modal opens
        document.getElementById('createSaleModal')?.addEventListener('shown.bs.modal', function() {
            Livewire.dispatch('calculateTotals');
        });
    });

    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-results') && !e.target.matches('input[wire\\:model^="searchTerms"]')) {
            const searchResults = document.querySelectorAll('.search-results');
            searchResults.forEach(result => {
                result.style.display = 'none';
            });
        }
    });

    // Show search results when focusing on input
    document.addEventListener('focusin', function(e) {
        if (e.target.matches('input[wire\\:model^="searchTerms"]')) {
            const index = e.target.getAttribute('wire:model').split('.')[1];
            const searchTerm = e.target.value;
            if (searchTerm.length >= 2) {
                Livewire.dispatch('showSearchResult', {
                    index: index,
                    searchTerm: searchTerm
                });
            }
        }
    });

    // Real-time calculation triggers
    document.addEventListener('input', function(e) {
        // Trigger calculations when quantity or discount inputs change
        if (e.target.matches('input[wire\\:model^="editableItems"][type="number"]')) {
            setTimeout(() => {
                Livewire.dispatch('calculateTotals');
            }, 300);
        }
    });
</script>
@endpush
