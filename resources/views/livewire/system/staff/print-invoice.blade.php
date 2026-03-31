<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice {{ $sale->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .invoice-info {
            width: 100%;
            margin-bottom: 20px;
        }

        .invoice-info td {
            vertical-align: top;
            padding: 5px;
        }

        .customer-info {
            width: 50%;
        }

        .invoice-details {
            width: 50%;
            text-align: right;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.items th,
        table.items td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        table.items th {
            background-color: #f2f2f2;
            text-align: left;
        }

        .summary {
            width: 300px;
            float: right;
            margin-top: 20px;
        }

        .summary table {
            width: 100%;
        }

        .summary td {
            padding: 5px;
        }

        .summary .total {
            font-weight: bold;
            border-top: 1px solid #ddd;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>INVOICE</h2>
    </div>

    <table class="invoice-info">
        <tr>
            <td class="customer-info">
                <strong>Billed To:</strong><br>
                {{ $sale->customer->name }}<br>
                {{ $sale->customer->email }}<br>
                {{ $sale->customer->phone }}
            </td>
            <td class="invoice-details">
                <strong>Invoice #:</strong> {{ $sale->invoice_number }}<br>
                <strong>Date:</strong> {{ \Carbon\Carbon::parse($sale->created_at)->format('d/m/Y') }}<br>
                <strong>Payment Status:</strong> {{ ucfirst($sale->payment_status) }}
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Item</th>
                <th>Code</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Discount</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($saleItems as $item)
            <tr>
                <td>{{ $item->brand }} {{ $item->product_name }}</td>
                <td>{{ $item->product_code }}</td>
                <td>{{ $item->quantity }}</td>
                <td>Rs.{{ number_format($item->unit_price, 2) }}</td>
                <td>Rs.{{ number_format($item->discount, 2) }}</td>
                <td>Rs.{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td align="right">Rs.{{ number_format($sale->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>Discount:</td>
                <td align="right">Rs.{{ number_format($sale->discount_amount, 2) }}</td>
            </tr>
            <tr class="total">
                <td>Total:</td>
                <td align="right">Rs.{{ number_format($sale->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Amount Paid:</td>
                <td align="right">Rs.{{ number_format($sale->total_amount - $sale->due_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Amount Due:</td>
                <td align="right">Rs.{{ number_format($sale->due_amount, 2) }}</td>
            </tr>
        </table>
    </div>
</body>

</html>
