<div class="card mb-3">
    <div class="card-header">
        <h6 class="mb-0">Sales Report</h6>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Total Amount</th>
                    <th>Paid</th>
                    <th>Due</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $sale)
                    <tr>
                        <td>{{ $sale->invoice_number }}</td>
                        <td>{{ $sale->customer->name ?? '-' }}</td>
                        <td>Rs.{{ number_format($sale->total_amount, 2) }}</td>
                        <td>
                            @if($sale->payment_status === 'paid')
                                <span class="badge bg-success">Paid</span>
                            @elseif($sale->payment_status === 'partial')
                                <span class="badge bg-warning">Partial</span>
                            @else
                                <span class="badge bg-danger">Unpaid</span>
                            @endif
                        </td>
                        <td>Rs.{{ number_format($sale->due_amount, 2) }}</td>
                        <td>{{ $sale->created_at->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No sales data available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
