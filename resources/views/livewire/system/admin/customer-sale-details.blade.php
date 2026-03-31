<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-graph-up text-success me-2"></i> Customer Sales Details
            </h3>
            <p class="text-muted mb-0">Track and manage customer sales performance efficiently</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="printData" class="btn btn-primary">
                <i class="bi bi-printer me-2"></i> Print Report
            </button>
            <button wire:click="exportToCSV" class="btn btn-success">
                <i class="bi bi-file-earmark-excel me-2"></i> Export CSV
            </button>
        </div>
    </div>

    {{-- Customer Sales Summary --}}
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-dark mb-1">
                    <i class="bi bi-journal-text text-primary me-2"></i> Customer Sales Summary
                </h5>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Customer Name</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th class="text-center">Invoices</th>
                            <th class="text-center">Total Sales</th>
                            <th class="text-center">Total Paid</th>
                            <th class="text-center">Total Due</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customerSales as $index => $customer)
                        <tr>
                            <td class="ps-4">{{ $index + 1 }}</td>
                            <td>
                                <span class="fw-medium text-dark">{{ $customer->name }}</span>
                            </td>
                            <td>{{ $customer->email }}</td>
                            <td>
                                <span class="badge bg-{{ $customer->type == 'wholesale' ? 'primary' : 'info' }}">
                                    {{ ucfirst($customer->type) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold text-dark">{{ $customer->invoice_count }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold text-dark">Rs.{{ number_format($customer->total_sales, 2) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $customer->total_sales-$customer->total_due > 0 ? 'success' : 'danger' }}">
                                    Rs.{{ number_format($customer->total_sales-$customer->total_due, 2) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $customer->total_due > 0 ? 'danger' : 'success' }}">
                                    Rs.{{ number_format($customer->total_due, 2) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-link text-primary p-0" 
                                        wire:click="viewSaleDetails({{ $customer->customer_id }})" 
                                        wire:loading.attr="disabled">
                                    <i class="bi bi-eye fs-6"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-people display-4 d-block mb-2"></i>
                                No customer sales records found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 px-4">
                {{ $customerSales->links('livewire.custom-pagination') }}
            </div>
        </div>
    </div>

    <!-- Customer Sale Details Modal -->
    <div wire:ignore.self class="modal fade" id="customerSalesModal" tabindex="-1" aria-labelledby="customerSalesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-badge text-primary me-2"></i>
                        {{ $modalData ? $modalData['customer']->name . "'s Sales History" : 'Sales History' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($modalData)
                    {{-- Customer Information --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="fw-bold text-dark mb-0">
                                <i class="bi bi-person-lines-fill text-primary me-2"></i> Customer Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold text-muted">Name</label>
                                    <p class="fw-medium text-dark mb-0">{{ $modalData['customer']->name }}</p>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold text-muted">Email</label>
                                    <p class="fw-medium text-dark mb-0">{{ $modalData['customer']->email }}</p>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold text-muted">Phone</label>
                                    <p class="fw-medium text-dark mb-0">{{ $modalData['customer']->phone }}</p>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold text-muted">Type</label>
                                    <p class="mb-0">
                                        <span class="badge bg-primary">{{ ucfirst($modalData['customer']->type) }}</span>
                                    </p>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold text-muted">Business Name</label>
                                    <p class="fw-medium text-dark mb-0">{{ $modalData['customer']->business_name ?? 'N/A' }}</p>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold text-muted">Address</label>
                                    <p class="fw-medium text-dark mb-0">{{ $modalData['customer']->address ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Sales Summary --}}
                    <div class="row g-4 mb-4">
                        <div class="col-12 col-md-4">
                            <div class="card summary-card h-100">
                                <div class="card-header bg-primary bg-opacity-10 border-0">
                                    <h6 class="fw-bold text-primary mb-0">
                                        <i class="bi bi-cart-check me-2"></i> Total Sales
                                    </h6>
                                </div>
                                <div class="card-body text-center">
                                    <h4 class="fw-bold text-primary mb-1">
                                        Rs.{{ number_format($modalData['salesSummary']->total_amount, 2) }}
                                    </h4>
                                    <p class="text-muted small mb-0">
                                        Across {{ count($modalData['invoices']) }} invoices
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="card summary-card h-100">
                                <div class="card-header bg-success bg-opacity-10 border-0">
                                    <h6 class="fw-bold text-success mb-0">
                                        <i class="bi bi-currency-rupee me-2"></i> Amount Paid
                                    </h6>
                                </div>
                                <div class="card-body text-center">
                                    <h4 class="fw-bold text-success mb-1">
                                        Rs.{{ number_format($modalData['salesSummary']->total_paid, 2) }}
                                    </h4>
                                    <p class="text-muted small mb-0">
                                        {{ round(($modalData['salesSummary']->total_paid / $modalData['salesSummary']->total_amount) * 100) }}% of total
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="card summary-card h-100">
                                <div class="card-header bg-danger bg-opacity-10 border-0">
                                    <h6 class="fw-bold text-danger mb-0">
                                        <i class="bi bi-clock-history me-2"></i> Amount Due
                                    </h6>
                                </div>
                                <div class="card-body text-center">
                                    <h4 class="fw-bold text-danger mb-1">
                                        Rs.{{ number_format($modalData['salesSummary']->total_due, 2) }}
                                    </h4>
                                    <p class="text-muted small mb-0">
                                        {{ round(($modalData['salesSummary']->total_due / $modalData['salesSummary']->total_amount) * 100) }}% outstanding
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Progress --}}
                    @php
                    $paymentPercentage = $modalData['salesSummary']->total_amount > 0
                    ? round(($modalData['salesSummary']->total_paid / $modalData['salesSummary']->total_amount) * 100)
                    : 0;
                    @endphp
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="fw-bold text-dark mb-0">
                                <i class="bi bi-graph-up text-primary me-2"></i> Payment Progress
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1" style="height: 10px;">
                                    <div class="progress-bar bg-primary"
                                        role="progressbar"
                                        style="width: {{ $paymentPercentage }}%;"
                                        aria-valuenow="{{ $paymentPercentage }}"
                                        aria-valuemin="0"
                                        aria-valuemax="100">
                                    </div>
                                </div>
                                <span class="ms-3 fw-bold text-primary">{{ $paymentPercentage }}%</span>
                            </div>
                        </div>
                    </div>

                    {{-- Product-wise Sales --}}
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-dark mb-0">
                                <i class="bi bi-box text-primary me-2"></i> Product-wise Sales
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 350px;">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light position-sticky top-0">
                                        <tr>
                                            <th class="ps-4">#</th>
                                            <th>Product</th>
                                            <th>Invoice #</th>
                                            <th>Date</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-end">Unit Price</th>
                                            <th class="text-end">Discount</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($modalData['productSales'] as $item)
                                        <tr>
                                            <td class="ps-4">{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($item->product_image)
                                                    <div class="me-3" style="width: 50px; height: 50px;">
                                                        <img src="$item->product_image)"
                                                            class="img-fluid rounded"
                                                            alt="{{ $item->product_name }}"
                                                            style="width: 100%; height: 100%; object-fit: cover;">
                                                    </div>
                                                    @endif
                                                    <div>
                                                        <h6 class="fw-medium text-dark mb-1">{{ $item->product_name }}</h6>
                                                        <div class="text-muted small">
                                                            {{ $item->product_brand ?? '' }} {{ $item->product_model ?? '' }}
                                                        </div>
                                                        <div class="mt-1">
                                                            <span class="badge bg-light text-dark small">
                                                                {{ $item->product_code }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $item->invoice_number }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item->sale_date)->format('d/m/Y') }}</td>
                                            <td class="text-center">
                                                <span class="fw-bold text-dark">{{ $item->quantity }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-bold text-dark">Rs.{{ number_format($item->unit_price, 2) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="text-muted">Rs.{{ number_format($item->discount, 2) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-bold text-success">Rs.{{ number_format($item->total, 2) }}</span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="bi bi-box display-4 d-block mb-2"></i>
                                                No product sales found
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
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading customer sales data...</p>
                    </div>
                    @endif
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" onclick="printModalContent()" class="btn btn-primary">
                        <i class="bi bi-printer me-1"></i> Print Details
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

    .summary-card:nth-child(1) {
        border-left-color: #4361ee;
    }

    .summary-card:nth-child(2) {
        border-left-color: #4cc9f0;
    }

    .summary-card:nth-child(3) {
        border-left-color: #e63946;
    }

    .table th {
        border-top: none;
        font-weight: 600;
        color: #6c757d;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
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

    .btn-success {
        background-color: #4cc9f0;
        border-color: #4cc9f0;
    }

    .btn-success:hover {
        background-color: #3a7bd5;
        border-color: #3a7bd5;
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

    .progress {
        border-radius: 10px;
    }

    .progress-bar {
        border-radius: 10px;
    }
</style>
@endpush

@push('scripts')
<script>
    window.addEventListener('open-customer-sale-details-modal', event => {
        setTimeout(() => {
            const modal = new bootstrap.Modal(document.getElementById('customerSalesModal'));
            modal.show();
        }, 500);
    });

    // Print functions remain the same as your original implementation
    document.addEventListener('livewire:initialized', function() {
        Livewire.on('print-customer-table', function() {
            // Your existing print function code
            const printWindow = window.open('', '_blank', 'width=1000,height=700');
            // ... rest of your print function code
        });
    });

    function printModalContent() {
        // Your existing modal print function code
        const customerName = document.querySelector('#customerSalesModalLabel').innerText.trim();
        // ... rest of your modal print function code
    }
</script>
@endpush
