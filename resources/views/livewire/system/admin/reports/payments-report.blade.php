<div>
    <!-- Customer Payments Section -->
    <div class="mb-4">
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header border-0 py-3 px-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-credit-card" style="font-size: 1.3rem;"></i>
                    <h6 class="mb-0 fw-bold">Customer Payments</h6>
                    <span class="badge bg-white text-primary ms-auto">{{ count($data['customer'] ?? []) }} Records</span>
                </div>
            </div>
            <div class="card-body p-0">
                @if(count($data['customer'] ?? []) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size: 0.9rem;">
                            <thead style="background-color: #f8f9fa; border-bottom: 2px solid #e0e0e0;">
                                <tr>
                                    <th class="ps-4">Invoice #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th class="pe-4">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['customer'] as $payment)
                                    <tr style="border-bottom: 1px solid #f0f0f0;">
                                        <td class="ps-4 fw-semibold" style="color: #667eea;">
                                            {{ $payment->sale->invoice_number ?? '-' }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle" style="width: 32px; height: 32px; background: #e7f5ff; display: flex; align-items: center; justify-content: center; color: #667eea; font-weight: bold; font-size: 0.85rem;">
                                                    {{ substr($payment->sale->customer->name ?? 'C', 0, 1) }}
                                                </div>
                                                <div>
                                                    <div style="font-weight: 500; color: #333;">{{ $payment->sale->customer->name ?? '-' }}</div>
                                                    <small style="color: #999;">{{ $payment->sale->customer->phone ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong style="color: #28a745; font-size: 1rem;">Rs.{{ number_format($payment->amount, 0) }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge" style="background-color: #e7f5ff; color: #667eea; border-radius: 4px; padding: 4px 8px; font-size: 0.75rem; font-weight: 500;">
                                                {{ ucfirst($payment->payment_method) }}
                                            </span>
                                        </td>
                                        <td style="color: #666; font-size: 0.85rem;">
                                            {{ $payment->payment_reference ?? $payment->card_number ?? '-' }}
                                        </td>
                                        <td class="pe-4" style="color: #666;">
                                            {{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-5 text-center text-muted">
                        <i class="bi bi-inbox display-4 mb-3 d-block"></i>
                        <p>No customer payments found for the selected period.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Supplier Payments Section -->
    <div class="mb-4">
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header border-0 py-3 px-4" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-building" style="font-size: 1.3rem;"></i>
                    <h6 class="mb-0 fw-bold">Supplier Payments</h6>
                    <span class="badge bg-white text-danger ms-auto">{{ count($data['supplier'] ?? []) }} Records</span>
                </div>
            </div>
            <div class="card-body p-0">
                @if(count($data['supplier'] ?? []) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size: 0.9rem;">
                            <thead style="background-color: #f8f9fa; border-bottom: 2px solid #e0e0e0;">
                                <tr>
                                    <th class="ps-4">PO #</th>
                                    <th>Supplier</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th class="pe-4">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['supplier'] as $payment)
                                    <tr style="border-bottom: 1px solid #f0f0f0;">
                                        <td class="ps-4 fw-semibold" style="color: #f5576c;">
                                            {{ $payment->purchaseOrder->order_code ?? '-' }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle" style="width: 32px; height: 32px; background: #fce4ec; display: flex; align-items: center; justify-content: center; color: #f5576c; font-weight: bold; font-size: 0.85rem;">
                                                    {{ substr($payment->purchaseOrder->supplier->name ?? 'S', 0, 1) }}
                                                </div>
                                                <div>
                                                    <div style="font-weight: 500; color: #333;">{{ $payment->purchaseOrder->supplier->name ?? '-' }}</div>
                                                    <small style="color: #999;">{{ $payment->purchaseOrder->supplier->phone ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong style="color: #dc3545; font-size: 1rem;">Rs.{{ number_format($payment->amount, 0) }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge" style="background-color: #fce4ec; color: #f5576c; border-radius: 4px; padding: 4px 8px; font-size: 0.75rem; font-weight: 500;">
                                                {{ ucfirst($payment->payment_method) }}
                                            </span>
                                        </td>
                                        <td style="color: #666; font-size: 0.85rem;">
                                            {{ $payment->payment_reference ?? $payment->cheque_number ?? '-' }}
                                        </td>
                                        <td class="pe-4" style="color: #666;">
                                            {{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-5 text-center text-muted">
                        <i class="bi bi-inbox display-4 mb-3 d-block"></i>
                        <p>No paid supplier payments found for the selected period.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 bg-primary text-white h-100" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div class="card-body py-4 px-3">
                    <p class="card-text mb-2" style="font-size: 0.85rem; font-weight: 500; opacity: 0.9;">Customer Paid</p>
                    <h3 class="card-title mb-0" style="font-size: 1.8rem; font-weight: 700;">Rs.{{ number_format(collect($data['customer'] ?? [])->sum('amount'), 0) }}</h3>
                    <small style="opacity: 0.9;">{{ count($data['customer'] ?? []) }} payments</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 text-white h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div class="card-body py-4 px-3">
                    <p class="card-text mb-2" style="font-size: 0.85rem; font-weight: 500; opacity: 0.9;">Supplier Paid</p>
                    <h3 class="card-title mb-0" style="font-size: 1.8rem; font-weight: 700;">Rs.{{ number_format(collect($data['supplier'] ?? [])->sum('amount'), 0) }}</h3>
                    <small style="opacity: 0.9;">{{ count($data['supplier'] ?? []) }} payments</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 bg-success text-white h-100" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div class="card-body py-4 px-3">
                    <p class="card-text mb-2" style="font-size: 0.85rem; font-weight: 500; opacity: 0.9;">Total Paid</p>
                    <h3 class="card-title mb-0" style="font-size: 1.8rem; font-weight: 700;">Rs.{{ number_format(collect($data['customer'] ?? [])->sum('amount') + collect($data['supplier'] ?? [])->sum('amount'), 0) }}</h3>
                    <small style="opacity: 0.9;">All payments</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 bg-info text-white h-100" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div class="card-body py-4 px-3">
                    <p class="card-text mb-2" style="font-size: 0.85rem; font-weight: 500; opacity: 0.9;">Total Records</p>
                    <h3 class="card-title mb-0" style="font-size: 1.8rem; font-weight: 700;">{{ count($data['customer'] ?? []) + count($data['supplier'] ?? []) }}</h3>
                    <small style="opacity: 0.9;">Paid transactions</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
