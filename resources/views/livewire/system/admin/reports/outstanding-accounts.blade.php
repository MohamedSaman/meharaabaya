<div>
    <div class="mb-4">
        <h5 class="fw-bold mb-1">Outstanding Accounts Summary</h5>
        <p class="text-muted mb-0 small">
            <i class="bi bi-calendar-event me-1"></i>As of {{ now()->format('F d, Y') }}
        </p>
    </div>

    <!-- Customer Outstanding Section -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-semibold mb-0">
                <i class="bi bi-people-fill text-primary me-2"></i>Customer Outstanding
            </h6>
            <span class="badge bg-primary fs-6">
                {{ $reportData['customers']->count() }} Customers
            </span>
        </div>

        @if($reportData['customers']->isEmpty())
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>No outstanding customer payments
        </div>
        @else
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1 small">Total Outstanding</h6>
                        <h3 class="mb-0 fw-bold text-danger">
                            Rs.{{ number_format($reportData['customers']->sum('total_due'), 2) }}
                        </h3>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h6 class="text-muted mb-1 small">Total Invoices</h6>
                        <h3 class="mb-0 fw-bold">
                            {{ $reportData['customers']->sum('invoices') }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Outstanding Invoices</th>
                        <th>Total Due Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData['customers'] as $customer)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $customer['customer']->name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $customer['customer']->business_name ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <i class="bi bi-telephone me-1"></i>{{ $customer['customer']->phone ?? 'N/A' }}
                            </div>
                            @if($customer['customer']->email)
                            <small class="text-muted">
                                <i class="bi bi-envelope me-1"></i>{{ $customer['customer']->email }}
                            </small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-warning">{{ $customer['invoices'] }} invoices</span>
                        </td>
                        <td class="fw-bold text-danger fs-5">
                            Rs.{{ number_format($customer['total_due'], 2) }}
                        </td>
                        <td>
                            <button wire:click="viewCustomerDetails({{ $customer['customer']->id }})" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>View Details
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="3" class="text-end">Total Customer Outstanding:</td>
                        <td class="text-danger">Rs.{{ number_format($reportData['customers']->sum('total_due'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>

    <!-- Supplier Outstanding Section -->
    <div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-semibold mb-0">
                <i class="bi bi-truck text-info me-2"></i>Supplier Outstanding
            </h6>
            <span class="badge bg-info fs-6">
                {{ $reportData['suppliers']->count() }} Suppliers
            </span>
        </div>

        @if($reportData['suppliers']->isEmpty())
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>No outstanding supplier payments
        </div>
        @else
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1 small">Total Outstanding</h6>
                        <h3 class="mb-0 fw-bold text-warning">
                            Rs.{{ number_format($reportData['suppliers']->sum('total_due'), 2) }}
                        </h3>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h6 class="text-muted mb-1 small">Total Orders</h6>
                        <h3 class="mb-0 fw-bold">
                            {{ $reportData['suppliers']->sum('orders') }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Supplier</th>
                        <th>Contact</th>
                        <th>Pending Orders</th>
                        <th>Total Due Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData['suppliers'] as $supplier)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-info bg-opacity-10 text-info rounded-circle p-2 me-3">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $supplier['supplier']->name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $supplier['supplier']->businessname ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <i class="bi bi-telephone me-1"></i>{{ $supplier['supplier']->phone ?? 'N/A' }}
                            </div>
                            @if($supplier['supplier']->email)
                            <small class="text-muted">
                                <i class="bi bi-envelope me-1"></i>{{ $supplier['supplier']->email }}
                            </small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-warning">{{ $supplier['orders'] }} orders</span>
                        </td>
                        <td class="fw-bold text-warning fs-5">
                            Rs.{{ number_format($supplier['total_due'], 2) }}
                        </td>
                        <td>
                            <button wire:click="viewSupplierDetails({{ $supplier['supplier']->id }})" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye me-1"></i>View Details
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="3" class="text-end">Total Supplier Outstanding:</td>
                        <td class="text-warning">Rs.{{ number_format($reportData['suppliers']->sum('total_due'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>

    <!-- Overall Summary -->
    <div class="row g-3 mt-4">
        <div class="col-md-6">
            <div class="card border-0 bg-danger bg-opacity-10">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Total Receivables (Customers)</h6>
                    <h2 class="mb-0 text-danger">Rs.{{ number_format($reportData['customers']->sum('total_due'), 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 bg-warning bg-opacity-10">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Total Payables (Suppliers)</h6>
                    <h2 class="mb-0 text-warning">Rs.{{ number_format($reportData['suppliers']->sum('total_due'), 2) }}</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
@if($showDetailModal && $selectedDetailData)
<div class="modal fade show" id="detailModal" style="display: block;" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">
                    @if($selectedDetailType === 'customer')
                        <i class="bi bi-person-fill text-primary me-2"></i>Customer Details
                    @else
                        <i class="bi bi-building text-info me-2"></i>Supplier Details
                    @endif
                </h5>
                <button type="button" class="btn-close" wire:click="closeDetailModal()"></button>
            </div>
            <div class="modal-body">
                @if($selectedDetailType === 'customer')
                    <!-- Customer Details -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Customer Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> {{ $selectedDetailData['customer']->name }}</p>
                                <p><strong>Phone:</strong> {{ $selectedDetailData['customer']->phone ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Email:</strong> {{ $selectedDetailData['customer']->email ?? 'N/A' }}</p>
                                <p><strong>Business:</strong> {{ $selectedDetailData['customer']->business_name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Outstanding Invoices</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Due</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($selectedDetailData['invoices'] as $invoice)
                                        <tr>
                                            <td><span class="badge bg-primary">{{ $invoice->invoice_number }}</span></td>
                                            <td>{{ $invoice->created_at->format('d/m/Y') }}</td>
                                            <td>Rs.{{ number_format($invoice->total_amount, 2) }}</td>
                                            <td class="text-danger fw-bold">Rs.{{ number_format($invoice->due_amount, 2) }}</td>
                                            <td>
                                                @if($invoice->payment_status === 'paid')
                                                    <span class="badge bg-success">Paid</span>
                                                @elseif($invoice->payment_status === 'partial')
                                                    <span class="badge bg-warning">Partial</span>
                                                @else
                                                    <span class="badge bg-danger">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No invoices found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <strong>Total Due Amount:</strong> Rs.{{ number_format($selectedDetailData['total_due'], 2) }}
                    </div>
                @else
                    <!-- Supplier Details -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Supplier Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> {{ $selectedDetailData['supplier']->name }}</p>
                                <p><strong>Phone:</strong> {{ $selectedDetailData['supplier']->phone ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Email:</strong> {{ $selectedDetailData['supplier']->email ?? 'N/A' }}</p>
                                <p><strong>Address:</strong> {{ $selectedDetailData['supplier']->address ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Pending Orders</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Amount</th>
                                        <th>Due</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($selectedDetailData['orders'] as $order)
                                        <tr>
                                            <td><span class="badge bg-info">{{ $order->order_code }}</span></td>
                                            <td>{{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') }}</td>
                                            <td>{{ $order->items->count() }}</td>
                                            <td>Rs.{{ number_format($order->total_amount, 2) }}</td>
                                            <td class="text-warning fw-bold">Rs.{{ number_format($order->due_amount, 2) }}</td>
                                            <td>
                                                @if($order->status === 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @elseif($order->status === 'received')
                                                    <span class="badge bg-success">Received</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No orders found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <strong>Total Due Amount:</strong> Rs.{{ number_format($selectedDetailData['total_due'], 2) }}
                    </div>
                @endif
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" wire:click="closeDetailModal()">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Backdrop -->
<div class="modal-backdrop fade show"></div>
@endif
