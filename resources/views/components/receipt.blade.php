{{-- components/receipt.blade.php --}}
<div class="receipt-container">
    <h4>Invoice #{{ $receipt->invoice_number }}</h4>
    <p>Date: {{ $receipt->created_at->format('d/m/Y H:i') }}</p>
    <p>Customer: {{ $receipt->customer->name ?? '' }}</p>
    <hr>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>product</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Discount</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipt->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>₹{{ number_format($item->unit_price,2) }}</td>
                <td>
                    @if($item->discount_per_unit > 0)
                        - ₹{{ number_format($item->discount_per_unit,2) }}
                    @else
                        - ₹0.00
                    @endif
                </td>
                <td>₹{{ number_format(($item->unit_price - $item->discount_per_unit) * $item->quantity,2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <hr>
    <p><strong>Subtotal:</strong> ₹{{ number_format($receipt->subtotal,2) }}</p>
    <p><strong>Discount:</strong> ₹{{ number_format($receipt->discount_amount,2) }}</p>
    <p><strong>Grand Total:</strong> ₹{{ number_format($receipt->total_amount,2) }}</p>
    <p><strong>Payment Status:</strong> {{ ucfirst($receipt->payment_status) }}</p>
</div>
