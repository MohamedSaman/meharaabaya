<div>
    <!-- Date Filter Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold text-secondary">
                        <i class="bi bi-calendar-range me-1"></i>Start Date
                    </label>
                    <input type="date" 
                           class="form-control" 
                           wire:model.live="reportStartDate"
                           max="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold text-secondary">
                        <i class="bi bi-calendar-range me-1"></i>End Date
                    </label>
                    <input type="date" 
                           class="form-control" 
                           wire:model.live="reportEndDate"
                           max="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold text-secondary">
                        <i class="bi bi-list-ol me-1"></i>Per Page
                    </label>
                    <select class="form-select" wire:model.live="perPage">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-clockwise me-1"></i>Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-1">Daily Purchases Report</h5>
            <p class="text-muted mb-0 small">
                <i class="bi bi-calendar-event me-1"></i>
                {{ \Carbon\Carbon::parse($reportStartDate ?? now())->format('F d, Y') }}
                @if($reportStartDate !== $reportEndDate)
                    to {{ \Carbon\Carbon::parse($reportEndDate ?? now())->format('F d, Y') }}
                @endif
            </p>
        </div>
        <div class="text-end">
            <!-- Total for entire date range -->
            <div class="fs-4 fw-bold text-primary">Rs.{{ number_format($reportTotal, 2) }}</div>
            <small class="text-muted">Total Purchases (All Dates)</small>
        </div>
    </div>

    @if($reportData->isEmpty())
    <div class="text-center py-5">
        <i class="bi bi-inbox display-4 text-muted"></i>
        <p class="mt-3 text-muted">No purchase orders found for this date</p>
    </div>
    @else
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Order Code</th>
                    <th>Supplier</th>
                    <th>Order Date</th>
                    <th>Items</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData as $order)
                <tr>
                    <td>
                        <span class="badge bg-primary">{{ $order->order_code }}</span>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $order->supplier->name ?? 'N/A' }}</div>
                        <small class="text-muted">{{ $order->supplier->businessname ?? '' }}</small>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') }}</td>
                    <td>{{ $order->items->count() }} items</td>
                    <td class="fw-bold">
                        Rs.{{ number_format($order->total_amount ?? 0, 2) }}
                    </td>
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
                @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="4" class="text-end">Page Total:</td>
                    <td>Rs.{{ number_format($reportData->sum('total_amount'), 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pagination -->
    <div class="card-footer bg-light">
        <div class="d-flex justify-content-center">
            {{ $reportData->links('livewire.custom-pagination') }}
        </div>
    </div>
    @endif
</div>
