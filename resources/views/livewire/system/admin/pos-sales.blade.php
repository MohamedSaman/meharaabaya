@php
use App\Models\Sale;
@endphp

<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-cash-stack text-success me-2"></i> POS Sales Management
            </h3>
            <p class="text-muted mb-0">View and manage POS sales</p>
        </div>
        <div>
            <a href="{{ route('admin.store-billing') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i> New POS Sale
            </a>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-primary border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                Total POS Sales
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $stats['total_sales'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cart-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-success border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                Total Revenue
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">Rs.{{ number_format($stats['total_amount'], 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-currency-dollar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-warning border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                Pending Payments
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">Rs.{{ number_format($stats['pending_payments'], 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clock-history fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-info border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                Today's Sales
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $stats['today_sales'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Search</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" placeholder="Search by invoice, customer name or phone..."
                            wire:model.live="search">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Payment Status</label>
                    <select class="form-select" wire:model.live="paymentStatusFilter">
                        <option value="all">All Status</option>
                        <option value="paid">Paid</option>
                        <option value="partial">Partial</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Date</label>
                    <input type="date" class="form-control" wire:model.live="dateFilter">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold invisible">Actions</label>
                    <button class="btn btn-outline-secondary w-100" wire:click="$set('dateFilter', '')">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Sales Table --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-list-ul text-primary me-2"></i> POS Sales List
                </h5>
                <span class="badge bg-primary">{{ $sales->total() }} records</span>
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
            <div class="table-responsive ">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 50px;">
                                <input type="checkbox" class="form-check-input" id="selectAll" onclick="toggleAllRows(this)">
                            </th>
                            <th class="text-center">Date</th>
                            <th>Customer</th>
                            <th class="ps-4">Invoice</th>
                            <th class="text-center">Amount</th>
                            <th class="text-center">Payment Status</th>
                            <th class="text-center">Sale Type</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr wire:key="sale-{{ $sale->id }}" style="cursor:pointer" class="table-row" data-id="{{ $sale->id }}">
                            <td class="ps-4" onclick="event.stopPropagation();">
                                <input type="checkbox" class="form-check-input row-checkbox" onchange="toggleRowHighlight(this)">
                            </td>
                            <td class="text-center" wire:click="viewSale({{ $sale->id }})">
                                <div>{{ $sale->created_at->format('d/m/Y') }}</div>
                            </td>
                            <td wire:click="viewSale({{ $sale->id }})">
                                @if($sale->customer)
                                <div class="fw-medium">{{ $sale->customer->name }}</div>
                                <small class="text-muted">{{ $sale->customer->phone }}</small>
                                @else
                                <span class="text-muted">Walk-in Customer</span>
                                @endif
                            </td>
                            <td class="ps-4" wire:click="viewSale({{ $sale->id }})">
                                <div class="fw-bold text-primary">{{ $sale->invoice_number }}</div>
                                <small class="text-muted">#{{ $sale->sale_id }}</small>
                            </td>

                            <td class="text-center" wire:click="viewSale({{ $sale->id }})">
                                @php
                                    $returnTotal = $sale->returns->sum('total_amount');
                                    $netAmount = $sale->total_amount - $returnTotal;
                                @endphp
                                @if($returnTotal > 0)
                                    <div class="fw-bold">Rs.{{ number_format($netAmount, 2) }}</div>
                                    <small class="text-muted text-decoration-line-through">Rs.{{ number_format($sale->total_amount, 2) }}</small>
                                    <small class="text-warning d-block">Return: -Rs.{{ number_format($returnTotal, 2) }}</small>
                                @else
                                    <div class="fw-bold">Rs.{{ number_format($sale->total_amount, 2) }}</div>
                                     @if($sale->due_amount > 0)
                                        <small class="text-danger">Due: Rs.{{ number_format($sale->due_amount, 2) }}</small>
                                    @endif
                                @endif
                               
                            </td>
                            <td class="text-center" wire:click="viewSale({{ $sale->id }})">
                                <span class="badge bg-{{ $sale->payment_status == 'paid' ? 'success' : ($sale->payment_status == 'partial' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($sale->payment_status) }}
                                </span>
                            </td>
                            <td class="text-center" wire:click="viewSale({{ $sale->id }})">
                                <span class="badge bg-primary">{{ strtoupper($sale->sale_type) }}</span>
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
                                        <!-- Download Invoice -->
                                        <li>
                                            <button class="dropdown-item"
                                                wire:click="downloadInvoice({{ $sale->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="downloadInvoice({{ $sale->id }})">

                                                <span wire:loading wire:target="downloadInvoice({{ $sale->id }})">
                                                    <i class="spinner-border spinner-border-sm me-2"></i>
                                                    Loading...
                                                </span>
                                                <span wire:loading.remove wire:target="downloadInvoice({{ $sale->id }})">
                                                    <i class="bi bi-download text-success me-2"></i>
                                                    Download Invoice
                                                </span>
                                            </button>
                                        </li>
                                        <!-- Print Invoice -->
                                        <li>
                                            <button class="dropdown-item"
                                                wire:click="printInvoice({{ $sale->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="printInvoice({{ $sale->id }})">

                                                <span wire:loading wire:target="printInvoice({{ $sale->id }})">
                                                    <i class="spinner-border spinner-border-sm me-2"></i>
                                                    Loading...
                                                </span>
                                                <span wire:loading.remove wire:target="printInvoice({{ $sale->id }})">
                                                    <i class="bi bi-download text-success me-2"></i>
                                                    Print
                                                </span>
                                            </button>
                                        </li>
                                        <!-- Payment History -->
                                        <li>
                                            <button class="dropdown-item"
                                                wire:click="showPaymentHistory({{ $sale->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="showPaymentHistory({{ $sale->id }})">

                                                <span wire:loading wire:target="showPaymentHistory({{ $sale->id }})">
                                                    <i class="spinner-border spinner-border-sm me-2"></i>
                                                    Loading...
                                                </span>
                                                <span wire:loading.remove wire:target="showPaymentHistory({{ $sale->id }})">
                                                    <i class="bi bi-clock-history text-info me-2"></i>
                                                    Payment History
                                                </span>
                                            </button>
                                        </li>
                                        <!-- Delete Sale -->
                                        <li>
                                            <button class="dropdown-item"
                                                wire:click="deleteSale({{ $sale->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="deleteSale({{ $sale->id }})">

                                                <span wire:loading wire:target="deleteSale({{ $sale->id }})">
                                                    <i class="spinner-border spinner-border-sm me-2"></i>
                                                    Loading...
                                                </span>
                                                <span wire:loading.remove wire:target="deleteSale({{ $sale->id }})">
                                                    <i class="bi bi-trash text-danger me-2"></i>
                                                    Delete
                                                </span>
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-cart-x display-4 d-block mb-2"></i>
                                No POS sales found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($sales->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $sales->links('livewire.custom-pagination') }}
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- View Sale Modal --}}
    <div wire:ignore.self class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true" >
        <div class="modal-dialog modal-lg">
            <div class="modal-content p-4" id="printableInvoice">
                {{-- Screen Only Header (visible on screen, hidden on print) --}}
                <div class="screen-only-header pb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="w-100 text-center">
                            <h5 class="mb-1">{{ config('shop.name') }}</h5>
                            <p class="mb-0">{{ config('shop.tagline') }}</p>
                        </div>
                    </div>
                    <hr class="my-2" style="border-top: 2px solid #000;">
                </div>

                @if($selectedSale)
                <div class="modal-body">
                    {{-- ==================== CUSTOMER + INVOICE INFO ==================== --}}
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Invoice to:</strong><br>
                            <strong>{{ $selectedSale->customer->name ?? 'Walk-in Customer' }}</strong><br>
                            {{ $selectedSale->customer->address ?? '' }}<br>
                            Tel: {{ $selectedSale->customer->phone ?? '' }}
                        </div>
                        <div class="col-6 text-end">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Invoice #</strong></td>
                                    <td>{{ $selectedSale->invoice_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Date</strong></td>
                                    <td>{{ $selectedSale->created_at->format('d/m/Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Sale Type</strong></td>
                                    <td><span class="badge bg-primary">{{ strtoupper($selectedSale->sale_type) }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Created By</strong></td>
                                    <td>{{ $selectedSale->user->name ?? 'System' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- ==================== ITEMS TABLE ==================== --}}
                    <div class="table-responsive mb-3" style="min-height: 10px;">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th class="text-center">Code</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Discount</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedSale->items as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $item->product_name }}</td>
                                    <td class="text-center">{{ $item->product_code }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">Rs.{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">Rs.{{ number_format($item->discount_per_unit * $item->quantity, 2) }}</td>
                                    <td class="text-end">Rs.{{ number_format($item->total, 2) }}</td>
                                </tr>
                                @endforeach
                                @if($selectedSale->items->count() == 0)
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No items found.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>


                    {{-- ==================== TOTALS (right-aligned) ==================== --}}
                    <div class="row">
                        <div class="col-7"></div>
                        <div class="col-5">
                            <table class="table table-sm table-bordered">
                                <tbody>
                                    <tr>
                                        <td><strong>Subtotal</strong></td>
                                        <td class="text-end">Rs.{{ number_format($selectedSale->subtotal, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Discount</strong></td>
                                        <td class="text-end">- Rs.{{ number_format($selectedSale->discount_amount, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>


                    {{-- ==================== RETURNED ITEMS TABLE ==================== --}}
                    @if(isset($selectedSale->returns) && count($selectedSale->returns) > 0)
                    <h6 class="text-muted mb-3 mt-4">RETURNED ITEMS</h6>
                    <div class="table-responsive mb-4" style="min-height: 10px;">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th class="text-center">Code</th>
                                    <th class="text-center">Return Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $returnAmount = 0; @endphp
                                @foreach($selectedSale->returns as $rIndex => $return)
                                @php $returnAmount += $return->total_amount; @endphp
                                <tr>
                                    <td>{{ $rIndex + 1 }}</td>
                                    <td>{{ $return->product?->name ?? '-' }}</td>
                                    <td class="text-center">{{ $return->product?->code ?? '-' }}</td>
                                    <td class="text-center">{{ $return->return_quantity }}</td>
                                    <td class="text-end">Rs.{{ number_format($return->selling_price, 2) }}</td>
                                    <td class="text-end">Rs.{{ number_format($return->total_amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="5" class="text-end"><strong>Return Amount:</strong></td>
                                    <td class="text-end">- Rs.@php echo number_format($returnAmount, 2); @endphp</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end"><strong>Net Amount:</strong></td>
                                    <td class="text-end fw-bold">
                                        Rs.@php echo number_format(($selectedSale->subtotal - $selectedSale->discount_amount) - $returnAmount, 2); @endphp
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif

                    {{-- ==================== GRAND TOTAL & DUE AMOUNT (Only show if NO returns) ==================== --}}
                    @if(!isset($selectedSale->returns) || count($selectedSale->returns) == 0)
                    <div class="row">
                        <div class="col-7"></div>
                        <div class="col-5">
                            <table class="table table-sm table-bordered">
                                <tbody>
                                    <tr>
                                        <td><strong>Grand Total</strong></td>
                                        <td class="text-end"><strong>Rs.{{ number_format($selectedSale->total_amount, 2) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Due Amount</strong></td>
                                        <td class="text-end"><strong>Rs.{{ number_format($selectedSale->due_amount, 2) }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    @if($selectedSale->notes)
                    <h6 class="text-muted mb-2">NOTES</h6>
                    <div class="card bg-light">
                        <div class="card-body">
                            <p class="mb-0">{{ $selectedSale->notes }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- Footer Note --}}
                    <div class="invoice-footer mt-4">
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <p class=""><strong>.............................</strong></p>
                                <p class="mb-2"><strong>Checked By</strong></p>
                            </div>
                            <div class="col-4">
                                <p class=""><strong>.............................</strong></p>
                                <p class="mb-2"><strong>Authorized Officer</strong></p>
                            </div>
                            <div class="col-4">
                                <p class=""><strong>.............................</strong></p>
                                <p class="mb-2"><strong>Customer Stamp</strong></p>
                            </div>
                        </div>
                        <div class="border-top pt-3">
                            <p class="text-center mb-0"><strong>ADDRESS :</strong> {{ config('shop.address', 'NO 92, PASYALA ROAD, KAL-ELIYA, SRI-LANKA.') }}</p>
                            <p class="text-center mb-0"><strong>TEL :</strong> {{ config('shop.phone', '+94 70 58 53 442') }}, <strong>EMAIL :</strong> {{ config('shop.email', 'phoenixbathware@gmail.com') }}</p>
                            <p class="text-center" style="font-size: 11px;"><strong>Goods return will be accepted within 10 days only. Electrical and body parts non-returnable.</strong></p>
                        </div>
                    </div>
                </div>
                @endif
                {{-- ==================== FOOTER BUTTONS ==================== --}}
                <div class="modal-footer justify-content-between" style="background: #fdfbf7; border-top: none; padding: 25px; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                    <button type="button" class="btn px-4 py-2" wire:click="closeModals" style="background: white; border: 1px solid #dee2e6; color: #495057; border-radius: 8px;">
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                    @if($selectedSale)
                    <div class="d-flex gap-2">
                        <button type="button" class="btn px-4 py-2" wire:click="printInvoice({{ $selectedSale->id }})" style="background: white; border: 1px solid #c7952a; color: #c7952a; border-radius: 8px;">
                            <i class="bi bi-printer me-1"></i> Print
                        </button>
                        <button type="button" class="btn px-4 py-2" wire:click="downloadInvoice({{ $selectedSale->id }})" style="background: linear-gradient(135deg, #c7952a, #b8860b); color: white; border: none; border-radius: 8px; font-weight: 600;">
                            <i class="bi bi-download me-1"></i> Download Invoice
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Payment History Modal --}}
    <div wire:ignore.self class="modal fade" id="paymentHistoryModal" tabindex="-1" aria-labelledby="paymentHistoryModalLabel" aria-hidden="true" >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-gradient-info text-white">
                    <div>
                        <h5 class="modal-title fw-bold mb-1">
                            <i class="bi bi-clock-history me-2"></i> Payment History
                        </h5>
                        @if($selectedSale)
                        <small class="opacity-75">Invoice: {{ $selectedSale->invoice_number }}</small>
                        @endif
                    </div>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModals"></button>
                </div>
                <div class="modal-body">
                    @if($selectedSale)
                    {{-- Sale Summary --}}
                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-circle text-primary me-2" style="font-size: 1.5rem;"></i>
                                        <div>
                                            <small class="text-muted d-block">Customer</small>
                                            <strong>{{ $selectedSale->customer ? $selectedSale->customer->name : 'Walk-in Customer' }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Total Amount</small>
                                    <strong class="text-dark fs-5">Rs.{{ number_format($selectedSale->total_amount, 2) }}</strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Payment Status</small>
                                    <span class="badge bg-{{ $selectedSale->payment_status == 'paid' ? 'success' : ($selectedSale->payment_status == 'partial' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($selectedSale->payment_status) }}
                                    </span>
                                </div>
                            </div>
                            @if($selectedSale->due_amount > 0)
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Due Amount: Rs.{{ number_format($selectedSale->due_amount, 2) }}</strong>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Payment List --}}
                    <div class="mb-3">
                        <h6 class="fw-bold text-dark mb-3">
                            <i class="bi bi-list-check me-2"></i> Payment Records
                            <span class="badge bg-primary ms-2">{{ count($paymentHistory) }} payments</span>
                        </h6>

                        @forelse($paymentHistory as $index => $payment)
                        <div class="card mb-3 shadow-sm border-start border-4 border-{{ $payment->status === 'approved' || $payment->status === 'paid' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                            <div class="card-header bg-white">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                <strong>#{{ $index + 1 }}</strong>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">Payment #{{ $payment->id }}</h6>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar3 me-1"></i>
                                                    {{ $payment->payment_date ? $payment->payment_date->format('d/m/Y h:i A') : '-' }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="fs-4 fw-bold text-success">Rs.{{ number_format($payment->amount, 2) }}</div>
                                        <span class="badge bg-secondary">
                                            {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    {{-- Payment Method Details --}}
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Payment Method</small>
                                        <strong>
                                            <i class="bi bi-{{ $payment->payment_method === 'cash' ? 'cash' : ($payment->payment_method === 'card' ? 'credit-card' : ($payment->payment_method === 'cheque' ? 'receipt' : 'bank')) }} me-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                        </strong>
                                    </div>

                                    @if($payment->payment_reference)
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Reference Number</small>
                                        <strong>{{ $payment->payment_reference }}</strong>
                                    </div>
                                    @endif

                                    @if($payment->card_number)
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Card Number</small>
                                        <strong>{{ $payment->card_number }}</strong>
                                    </div>
                                    @endif

                                    @if($payment->bank_name)
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Bank Name</small>
                                        <strong>{{ $payment->bank_name }}</strong>
                                    </div>
                                    @endif

                                    @if($payment->transfer_date)
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Transfer Date</small>
                                        <strong>{{ date('M d, Y', strtotime($payment->transfer_date)) }}</strong>
                                    </div>
                                    @endif

                                    @if($payment->transfer_reference)
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Transfer Reference</small>
                                        <strong>{{ $payment->transfer_reference }}</strong>
                                    </div>
                                    @endif

                                    @if($payment->status)
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Status</small>
                                        {!! $payment->status_badge !!}
                                    </div>
                                    @endif
                                </div>

                                {{-- Cheques --}}
                                @if($payment->cheques && count($payment->cheques) > 0)
                                <div class="mt-3">
                                    <small class="text-muted d-block mb-2"><strong>Cheque Details:</strong></small>
                                    <div class="table-responsive "style="min-height: 100px;">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Cheque Number</th>
                                                    <th>Bank</th>
                                                    <th>Amount</th>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($payment->cheques as $cheque)
                                                <tr>
                                                    <td><strong>{{ $cheque->cheque_number }}</strong></td>
                                                    <td>{{ $cheque->bank_name }}</td>
                                                    <td class="text-success fw-bold">Rs.{{ number_format($cheque->amount, 2) }}</td>
                                                    <td>{{ $cheque->cheque_date ? date('M d, Y', strtotime($cheque->cheque_date)) : '-' }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $cheque->status === 'cleared' ? 'success' : ($cheque->status === 'pending' ? 'warning' : 'danger') }}">
                                                            {{ ucfirst($cheque->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif

                                {{-- Notes --}}
                                @if($payment->notes)
                                <div class="mt-3">
                                    <small class="text-muted d-block">Notes:</small>
                                    <div class="alert alert-light mb-0">{{ $payment->notes }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                            <p class="text-muted mt-3 mb-2"><strong>No payment records found</strong></p>
                            <p class="text-muted">This sale hasn't received any payments yet.</p>
                        </div>
                        @endforelse

                        {{-- Summary --}}
                        @if(count($paymentHistory) > 0)
                        <div class="card bg-success text-white border-0 shadow-sm mt-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="opacity-75">Total Payments Made</small>
                                        <h4 class="mb-0 fw-bold">Rs.{{ number_format($paymentHistory->sum('amount'), 2) }}</h4>
                                    </div>
                                    <i class="bi bi-cash-stack" style="font-size: 3rem; opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="closeModals">
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>


    {{-- Delete Confirmation Modal --}}
    <div wire:ignore.self class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-exclamation-triangle me-2"></i> Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModals"></button>
                </div>
                <div class="modal-body">
                    @if($selectedSale)
                    <div class="alert alert-danger">
                        <h6 class="alert-heading">Warning!</h6>
                        <p class="mb-0">You are about to delete the following sale. This action cannot be undone and will restore product stock.</p>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <p><strong>Invoice:</strong> {{ $selectedSale->invoice_number }}</p>
                            <p><strong>Customer:</strong> {{ $selectedSale->customer->name ?? 'Walk-in Customer' }}</p>
                            <p><strong>Amount:</strong> Rs.{{ number_format($selectedSale->total_amount, 2) }}</p>
                            <p><strong>Date:</strong> {{ $selectedSale->created_at->format('d/m/Y') }}</p>
                            <p><strong>Items:</strong> {{ $selectedSale->items->count() }} products</p>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModals">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="confirmDelete">
                        <i class="bi bi-trash me-1"></i> Delete Sale
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast Container --}}
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="livewire-toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">Notification</strong>
                <small>Just now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <!-- Toast message will be inserted here -->
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .table th {
        font-weight: 600;
        border-top: none;
        color: #ffffff;
        
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .closebtn {
        top: 3%;
        right: 3%;
        position: absolute;
    }

    .btn-group-sm>.btn {
        padding: 0.25rem 0.5rem;
    }

    .modal-header {
        border-bottom: 1px solid #dee2e6;
        background: linear-gradient(90deg, #3b5b0c, #8eb922);
        color: #fff;
    }

    .badge {
        font-size: 0.75em;
    }

    /* Hover effects */
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.025);
    }

    .table td {
        vertical-align: middle;
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

    /* Print styles */
    @page {
        size: A4;
        margin: 0;
    }

    @media print {

        /* Remove browser header/footer */
        @page {
            margin: 0mm;
        }

        /* Hide everything except the invoice */
        body * {
            visibility: hidden;
        }

        #printableInvoice,
        #printableInvoice * {
            visibility: visible;
        }

        /* Position the invoice */
        #printableInvoice {
            position: fixed !important;
            left: 0 !important;
            top: 0 !important;
            width: 210mm !important;
            height: 297mm !important;
            margin: 0 !important;
            padding: 10mm 10mm 20mm 15mm !important;
            background: #fff !important;
            font-size: 10pt !important;
            color: #000 !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
            page-break-after: avoid !important;
            page-break-before: avoid !important;
        }

        /* Reset modal styles for print */
        .modal,
        .modal-dialog,
        .modal-content {
            all: unset !important;
            display: block !important;
            width: 100% !important;
            height: auto !important;
            position: static !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Hide modal chrome */
        .modal-footer,
        .btn,
        .btn-close,
        .closebtn {
            display: none !important;
        }

        /* Header styles - Fixed at top */
        .modal-header {
            border: none !important;
            padding: 0 0 10px 0 !important;
            text-align: center !important;
            margin-bottom: 15px !important;
            background: transparent !important;
            border-bottom: 2px solid #3b5b0c !important;
        }

        .modal-header img {
            max-height: 100px !important;
            margin-bottom: 5px !important;
        }

        .modal-header h4 {
            margin: 5px 0 !important;
            font-size: 1rem !important;
            color: #000 !important;
            font-weight: bold !important;
        }

        .modal-header p {
            margin: 2px 0 !important;
            font-size: 0.8rem !important;
            color: #000 !important;
        }

        /* Body content */
        .modal-body {
            padding: 0 !important;
            margin: 0 !important;
            max-height: none !important;
            overflow: visible !important;
        }

        /* Layout fixes */
        .row {
            display: flex !important;
            margin: 0 !important;
            page-break-inside: avoid !important;
        }

        .row>.col-6 {
            page-break-inside: avoid !important;
            flex: 0 0 50% !important;
            max-width: 50% !important;
        }

        .row>.col-6:first-child {
            text-align: left !important;
        }

        .row>.col-6:last-child {
            text-align: right !important;
        }

        .row>.col-7 {
            display: none !important;
        }

        .row>.col-5 {
            flex: 0 0 100% !important;
            max-width: 100% !important;
        }

        /* Table styles */
        .table {
            border-collapse: collapse !important;
            width: 100% !important;
            margin-bottom: 10px !important;
            font-size: 9pt !important;
        }

        .table th,
        .table td {
            border: 1px solid #999 !important;
            padding: 4px 6px !important;
            color: #000 !important;
            background: transparent !important;
        }

        .table-light th,
        .table-light td,
        tfoot.table-light tr,
        tfoot.table-light td {
            background: #e9ecef !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .table-sm {
            font-size: 8pt !important;
        }

        .table-borderless td {
            border: none !important;
            padding: 2px 4px !important;
        }

        .table-borderless strong {
            min-width: 110px !important;
            display: inline-block !important;
        }

        /* Compact spacing */
        h6 {
            color: #000 !important;
            margin: 10px 0 5px 0 !important;
            font-weight: bold !important;
            font-size: 11pt !important;
        }

        /* Badge and color fixes */
        .badge {
            border: 1px solid #000 !important;
            padding: 2px 6px !important;
            border-radius: 3px !important;
            color: #000 !important;
            background: transparent !important;
        }

        .fw-bold,
        strong {
            font-weight: bold !important;
            color: #000 !important;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .text-success {
            color: #198754 !important;
        }

        .text-muted {
            font-size: 8pt !important;
            color: #666 !important;
        }

        /* Card styles */
        .card {
            border: 1px solid #ddd !important;
            page-break-inside: avoid !important;
            margin-bottom: 10px !important;
        }

        .card-body {
            padding: 8px !important;
        }

        .bg-light {
            background-color: #f8f9fa !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Remove extra spacing */
        .mb-3,
        .mb-4 {
            margin-bottom: 8px !important;
        }

        .mt-4 {
            margin-top: 15px !important;
        }

        /* Prevent page breaks */
        .table-responsive {
            page-break-inside: avoid !important;
        }
        
        /* Ensure single page */
        html,
        body {
            height: 297mm !important;
            width: 210mm !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
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

    // Print function
    function printInvoice() {
        window.print();
    }

    document.addEventListener('livewire:initialized', () => {
        // Modal management
        Livewire.on('showModal', (modalId) => {
            console.log('Showing modal:', modalId);
            const modalElement = document.getElementById(modalId);
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();

                // Close modal when hidden
                modalElement.addEventListener('hidden.bs.modal', function() {
                    Livewire.dispatch('closeModals');
                });
            }
        });

        Livewire.on('hideModal', (modalId) => {
            console.log('Hiding modal:', modalId);
            const modalElement = document.getElementById(modalId);
            if (modalElement) {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
            }
        });

        // Toast notifications
        Livewire.on('showToast', (event) => {
            const toastElement = document.getElementById('livewire-toast');
            if (toastElement) {
                const toastBody = toastElement.querySelector('.toast-body');
                const toastHeader = toastElement.querySelector('.toast-header');

                if (toastBody) toastBody.textContent = event.message;
                if (toastHeader) {
                    // Remove existing color classes
                    toastHeader.className = 'toast-header text-white';
                    // Add new color class
                    toastHeader.classList.add('bg-' + event.type);
                }

                const toast = new bootstrap.Toast(toastElement);
                toast.show();
            }
        });

        // Close modals when escape key is pressed
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                Livewire.dispatch('closeModals');
            }
        });
    });

    // Handle download button state
    document.addEventListener('livewire:request-start', (event) => {
        const buttons = document.querySelectorAll('[wire\\:click*="downloadInvoice"]');
        buttons.forEach(button => {
            button.disabled = true;
            const icon = button.querySelector('i');
            if (icon) {
                icon.className = 'bi bi-hourglass-split me-1';
            }
        });
    });

    document.addEventListener('livewire:request-finish', (event) => {
        const buttons = document.querySelectorAll('[wire\\:click*="downloadInvoice"]');
        buttons.forEach(button => {
            button.disabled = false;
            const icon = button.querySelector('i');
            if (icon) {
                icon.className = 'bi bi-download me-1';
            }
        });
    });
</script>
@endpush
