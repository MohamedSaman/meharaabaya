<!DOCTYPE html>
<html>
<head>
    <title>Payment Receipt</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .company-name { font-size: 24px; font-weight: bold; color: #333; }
        .receipt-title { font-size: 20px; margin: 10px 0; }
        .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .details-table td, .details-table th { padding: 8px; border: 1px solid #ddd; }
        .details-table .label { font-weight: bold; background-color: #f8f9fa; width: 30%; }
        .details-table th { background-color: #333; color: white; text-align: left; }
        .total-row { background-color: #f8f9fa; font-weight: bold; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
        .signature-area { margin-top: 60px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .allocations-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .allocations-table th, .allocations-table td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        .allocations-table th { background-color: #333; color: white; }
        .info-badge { display: inline-block; padding: 2px 6px; background-color: #e3f2fd; color: #1976d2; font-size: 10px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">USN Auto Parts</div>
        <div class="receipt-title">PAYMENT RECEIPT</div>
    </div>

    <table class="details-table">
        <tr>
            <td class="label">Receipt ID</td>
            <td>#{{ $payment->id }}</td>
            <td class="label">Payment Date</td>
            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Customer Name</td>
            <td>{{ $customer->name }}</td>
            <td class="label">Phone</td>
            <td>{{ $customer->phone }}</td>
        </tr>
        <tr>
            <td class="label">Payment Method</td>
            <td class="text-capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
            <td class="label">Reference No</td>
            <td>{{ $payment->payment_reference ?: 'N/A' }}</td>
        </tr>
    </table>

    {{-- Show payment method specific details --}}
    @if($payment->payment_method === 'cheque' && $payment->cheques && count($payment->cheques) > 0)
    <div style="margin: 20px 0;">
        <h3 style="margin-bottom: 10px;">Cheque Details:</h3>
        <table class="allocations-table">
            <thead>
                <tr>
                    <th>Cheque Number</th>
                    <th>Bank Name</th>
                    <th>Cheque Date</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payment->cheques as $cheque)
                <tr>
                    <td>{{ $cheque->cheque_number }}</td>
                    <td>{{ $cheque->bank_name }}</td>
                    <td>{{ \Carbon\Carbon::parse($cheque->cheque_date)->format('d/m/Y') }}</td>
                    <td class="text-right">Rs.{{ number_format($cheque->cheque_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($payment->payment_method === 'bank_transfer')
    <table class="details-table">
        <tr>
            <td class="label">Bank Name</td>
            <td>{{ $payment->bank_name }}</td>
            <td class="label">Transfer Date</td>
            <td>{{ $payment->transfer_date ? \Carbon\Carbon::parse($payment->transfer_date)->format('d/m/Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label" colspan="1">Transfer Reference</td>
            <td colspan="3">{{ $payment->transfer_reference ?: 'N/A' }}</td>
        </tr>
    </table>
    @endif

    {{-- Payment Allocation Details --}}
    @if($allocations && count($allocations) > 0)
    <div style="margin: 20px 0;">
        <h3 style="margin-bottom: 10px;">Payment Allocation:</h3>
        <table class="allocations-table">
            <thead>
                <tr>
                    <th>Invoice Number</th>
                    <th>Invoice Total</th>
                    <th class="text-right">Allocated Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allocations as $allocation)
                <tr>
                    <td>
                        {{ $allocation->invoice_number }}
                        @if($allocation->return_amount > 0)
                        <span class="info-badge">Returns: Rs.{{ number_format($allocation->return_amount, 2) }}</span>
                        @endif
                    </td>
                    <td>
                        @if($allocation->return_amount > 0)
                            <span style="text-decoration: line-through; color: #999;">Rs.{{ number_format($allocation->total_amount, 2) }}</span>
                            <br>
                            <strong>Rs.{{ number_format($allocation->adjusted_total, 2) }}</strong>
                            <span style="font-size: 10px; color: #666;">(Adjusted)</span>
                        @else
                            Rs.{{ number_format($allocation->total_amount, 2) }}
                        @endif
                    </td>
                    <td class="text-right">Rs.{{ number_format($allocation->allocated_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <table class="details-table">
        <tr class="total-row">
            <td class="label">Total Amount Paid</td>
            <td class="text-right">Rs.{{ number_format($payment->amount, 2) }}</td>
        </tr>
    </table>

    @if($payment->notes)
    <div style="margin-top: 20px;">
        <strong>Notes:</strong>
        <p>{{ $payment->notes }}</p>
    </div>
    @endif

    <div class="signature-area">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    <div style="border-top: 1px solid #333; padding-top: 10px;">
                        Customer Signature
                    </div>
                </td>
                <td style="width: 50%;">
                    <div style="border-top: 1px solid #333; padding-top: 10px;">
                        Received By: {{ $received_by }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Thank you for your payment!</p>
        <p>Generated on: {{ now()->format('d/m/Y h:i A') }}</p>
    </div>
</body>
</html>
