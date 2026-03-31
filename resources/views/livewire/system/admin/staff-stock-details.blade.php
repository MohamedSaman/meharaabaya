<div>
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap bg-light p-2 p-md-3">
                <h4 class="card-title fs-5 fs-md-4 mb-2 mb-md-0">Product Stock Details</h4>
                <div class="card-tools">
                    <button wire:click="exportToCSV" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-download me-1"></i> Export
                    </button>
                </div>
            </div>
            <div class="card-body p-2 p-md-3">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm table-md-lg">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Staff Name</th>
                                <th class="d-none d-md-table-cell">Email</th>
                                <th class="d-none d-sm-table-cell">Contact</th>
                                <th class="text-center">Total Qty</th>
                                <th class="text-center">Sold Qty</th>
                                <th class="text-center">Available</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($staffStocks->count() > 0)
                                @foreach ($staffStocks as $staffStock)
                                    <tr>
                                        <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                        <td class="align-middle">
                                            {{ $staffStock->name ?? 'Unknown' }}
                                            <div class="d-block d-sm-none small text-muted">
                                                {{ $staffStock->contact ?? '-' }}
                                            </div>
                                            <div class="d-block d-md-none small text-muted">
                                                {{ $staffStock->email ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="d-none d-md-table-cell align-middle">{{ $staffStock->email ?? '-' }}</td>
                                        <td class="d-none d-sm-table-cell align-middle">{{ $staffStock->contact ?? '-' }}</td>
                                        <td class="text-center align-middle">{{ $staffStock->total_quantity }}</td>
                                        <td class="text-center align-middle">{{ $staffStock->sold_quantity }}</td>
                                        <td class="text-center align-middle">
                                            <span
                                                class="badge {{ $staffStock->total_quantity - $staffStock->sold_quantity > 0 ? 'bg-success' : 'bg-danger' }}">
                                                {{ $staffStock->total_quantity - $staffStock->sold_quantity }}
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <button wire:click="viewStockDetails({{ $staffStock->user_id }})"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>

                                            </button>
                                            <a href="{{ route('admin.staff.reentry', $staffStock->user_id) }}" class="btn btn-outline-secondary">Re-entry</a>

                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="10" class="text-center">
                                        <div class="alert alert-primary bg-opacity-10 my-2">
                                            <i class="bi bi-info-circle me-2"></i> No staff stock data found.
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="stockDetailsModal" tabindex="-1" aria-labelledby="stockDetailsModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-fullscreen-md-down modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0">
                <div class="modal-header bg-light">
                    <div>
                        <h5 class="modal-title fw-bold" id="stockDetailsModalLabel">
                            @if ($stockDetails && count($stockDetails) > 0)
                                {{ $stockDetails[0]->staff_name }}'s Inventory
                            @else
                                Staff Inventory
                            @endif
                        </h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    @if ($stockDetails && count($stockDetails) > 0)
                        <!-- Summary Stats -->
                        <div class="px-3 px-md-4 py-3 bg-light border-bottom">
                            <div class="row g-2 g-md-3 text-center">
                                <div class="col-4">
                                    <div class="p-2 p-md-3 rounded-3 bg-white shadow-sm">
                                        <div class="text-primary fs-5 fs-md-4 fw-bold">
                                            {{ $stockDetails->sum('quantity') }}
                                        </div>
                                        <div class="text-muted small">Total Assigned</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 p-md-3 rounded-3 bg-white shadow-sm">
                                        <div class="text-success fs-5 fs-md-4 fw-bold">
                                            {{ $stockDetails->sum('sold_quantity') }}</div>
                                        <div class="text-muted small">Total Sold</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 p-md-3 rounded-3 bg-white shadow-sm">
                                        <div class="text-danger fs-5 fs-md-4 fw-bold">
                                            {{ $stockDetails->sum('quantity') - $stockDetails->sum('sold_quantity') }}
                                        </div>
                                        <div class="text-muted small">Available</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Item Grid -->
                        <div class="p-2 p-md-4">
                            <div class="mb-3">
                                <input type="text" class="form-control form-control-sm"
                                    placeholder="Search Products..." id="ProductSearchInput">
                            </div>
                            <div class="row g-2 g-md-3 Product-items">
                                @foreach ($stockDetails as $item)
                                    <div class="col-12 col-sm-6 col-lg-4 Product-item">
                                        <div class="card h-100 border-0 shadow-sm">
                                            <div class="row g-0">
                                                <div class="col-4">
                                                    <div
                                                        class="p-2 p-md-3 h-100 d-flex align-items-center justify-content-center bg-light rounded-start">
                                                        <img src="{{ $item->Product_image ? asset('storage/' . $item->Product_image) : asset('images/product.jpg') }}"
                                                            alt="{{ $item->Product_name }}" class="img-fluid"
                                                            style="max-height: 80px; max-height: 100px; object-fit: contain;">
                                                    </div>
                                                </div>
                                                <div class="col-8">
                                                    <div class="card-body p-2 p-md-3">
                                                        <h6 class="card-title mb-1 fw-bold Product-brand fs-6 fs-md-5">
                                                            {{ $item->Product_brand }}</h6>
                                                        <p class="card-text small mb-0 Product-name">
                                                            {{ $item->Product_name }} {{ $item->Product_model }}
                                                        </p>
                                                        <p class="card-text small text-muted mb-2">
                                                            Code: {{ $item->Product_code }}
                                                        </p>

                                                        <!-- Stock Status -->
                                                        <div
                                                            class="d-flex align-items-center justify-content-between mt-auto flex-wrap gap-1">
                                                            <div class="small">
                                                                <span class="text-muted">Status:</span>
                                                                @php
                                                                    $available = $item->quantity - $item->sold_quantity;
                                                                    $percentSold =
                                                                        $item->quantity > 0
                                                                            ? ($item->sold_quantity / $item->quantity) *
                                                                                100
                                                                            : 0;

                                                                    if ($available == 0) {
                                                                        $badgeClass = 'bg-danger';
                                                                        $statusText = 'Sold Out';
                                                                    } elseif ($available < 3) {
                                                                        $badgeClass = 'bg-warning';
                                                                        $statusText = 'Low Stock';
                                                                    } else {
                                                                        $badgeClass = 'bg-success';
                                                                        $statusText = 'In Stock';
                                                                    }
                                                                @endphp
                                                                <span
                                                                    class="badge {{ $badgeClass }} rounded-pill">{{ $statusText }}</span>
                                                            </div>
                                                            <div class="small">
                                                                <span
                                                                    class="fw-bold">{{ $available ?? 0 }}/{{ $item->quantity ?? 0 }}</span>
                                                            </div>
                                                        </div>

                                                        <!-- Progress bar -->
                                                        <div class="progress mt-2" style="height: 5px;">
                                                            <div class="progress-bar bg-primary" role="progressbar"
                                                                style="width: {{ $percentSold }}%;"
                                                                aria-valuenow="{{ $percentSold }}" aria-valuemin="0"
                                                                aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="p-4 text-center">
                            <div class="py-5">
                                <i class="bi bi-search display-4 text-muted"></i>
                                <p class="mt-3">No Product inventory data found for this staff member.</p>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer flex-wrap gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="printReportBtn" class="btn btn-primary">
                        <i class="bi bi-printer me-1"></i> Print Report
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        window.addEventListener('open-stock-details-modal', event => {
            setTimeout(() => {
                const modal = new bootstrap.Modal(document.getElementById('stockDetailsModal'));
                modal.show();
            }, 500); // 500ms delay before showing the modal
        });

        // Search functionality for Productes in modal
        document.addEventListener('DOMContentLoaded', function() {
            document.body.addEventListener('input', function(e) {
                if (e.target && e.target.id === 'ProductSearchInput') {
                    const searchValue = e.target.value.toLowerCase();
                    document.querySelectorAll('.Product-item').forEach(item => {
                        const brand = item.querySelector('.Product-brand').textContent.toLowerCase();
                        const name = item.querySelector('.Product-name').textContent.toLowerCase();

                        if (brand.includes(searchValue) || name.includes(searchValue)) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                }
            });

            const printBtn = document.getElementById('printReportBtn');
            if (printBtn) {
                printBtn.addEventListener('click', function() {
                    printStaffInventory();
                });
            }
        });

        function printStaffInventory() {
            // Get the modal content
            const modalContent = document.querySelector('#stockDetailsModal .modal-content').cloneNode(true);
            
            // Remove buttons and search input
            if (modalContent.querySelector('.modal-footer')) {
                modalContent.querySelector('.modal-footer').remove();
            }
            
            const searchInput = modalContent.querySelector('#ProductSearchInput');
            if (searchInput) {
                searchInput.parentElement.remove();
            }
            
            // Create print window
            const printWindow = window.open('', '_blank', 'height=600,width=800');
            
            // Get staff name for title
            let staffName = "Staff Inventory";
            const titleElement = modalContent.querySelector('.modal-title');
            if (titleElement) {
                staffName = titleElement.textContent.trim();
            }
            
            // Create HTML content for print
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>${staffName} - Print Report</title>
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
                    <style>
                        body { padding: 20px; }
                        @media print {
                            .no-print { display: none; }
                        }
                        .Product-item { page-break-inside: avoid; }
                        @media (max-width: 768px) {
                            body { padding: 10px; }
                            .container-fluid { padding: 0; }
                        }
                    </style>
                </head>
                <body>
                    <div class="container-fluid">
                        <div class="d-flex justify-content-between mb-4 flex-wrap">
                            <h2>${staffName}</h2>
                            <div class="text-end no-print">
                                <button class="btn btn-primary" onclick="window.print();">Print</button>
                                <button class="btn btn-secondary ms-2" onclick="window.close();">Close</button>
                            </div>
                        </div>
                        <div class="card">
                            ${modalContent.querySelector('.modal-body').innerHTML}
                        </div>
                        <div class="mt-4 text-center text-muted">
                            <small>Generated on ${new Date().toLocaleString()}</small>
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
