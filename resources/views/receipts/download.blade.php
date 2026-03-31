<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Receipt {{ $sale->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }

        .receipt-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .mb-1 {
            margin-bottom: 4px;
        }

        .mb-2 {
            margin-bottom: 8px;
        }

        .mb-3 {
            margin-bottom: 12px;
        }

        .mb-4 {
            margin-bottom: 16px;
        }

        .mt-3 {
            margin-top: 12px;
        }

        .mt-4 {
            margin-top: 16px;
        }

        .pt-3 {
            padding-top: 12px;
        }

        .p-2 {
            padding: 8px;
        }

        .p-3 {
            padding: 12px;
        }

        .pb-2 {
            padding-bottom: 8px;
        }

        .border-bottom {
            border-bottom: 1px solid #ddd;
        }

        .border-top {
            border-top: 1px solid #ddd;
        }

        .border-start {
            border-left: 3px solid;
        }

        .border-success {
            border-color: #28a745;
        }

        .border-warning {
            border-color: #ffc107;
        }

        .bg-light {
            background-color: #f8f9fa;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
            clear: both;
        }

        .col-md-6 {
            width: 50%;
            float: left;
            padding: 0 15px;
            box-sizing: border-box;
        }

        .card {
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .card-body {
            padding: 12px;
        }

        .d-flex {
            display: flex;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .align-items-center {
            align-items: center;
        }

        hr {
            margin: 12px 0;
            border: 0;
            border-top: 1px solid #ddd;
        }

        .hr-bold {
            border-top: 2px solid #000;
        }

        .text-muted {
            color: #6c757d;
        }

        .small {
            font-size: 85%;
        }

        .fw-bold {
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 4px;
        }

        .bg-success {
            background-color: #28a745;
            color: white;
        }

        .bg-warning {
            background-color: #ffc107;
            color: black;
        }

        .bg-danger {
            background-color: #dc3545;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Header Layout */
        .header-layout {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .header-logo {
            flex: 0 0 150px;
        }

        .header-logo img {
            max-height: 80px;
            width: auto;
        }

        .header-center {
            flex: 1;
            text-align: center;
        }

        .header-center h2 {
            margin: 0 0 4px 0;
            font-size: 2rem;
            letter-spacing: 2px;
        }

        .header-center .tagline {
            margin: 0;
            font-size: 10px;
            color: #6c757d;
        }

        .header-right {
            flex: 0 0 150px;
            text-align: right;
        }

        .header-right h5 {
            margin: 0;
            font-size: 1rem;
        }

        .header-right h6 {
            margin: 4px 0 0 0;
            font-size: 0.875rem;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="receipt-container">
        <!-- Global Header (same as print) -->
        <div class="screen-only-header pb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <div style="text-align: center; padding: 8px 0;">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('shop.name') }}" class="img-fluid" style="height: 60px; margin: 0 auto 8px;">
                        <h2 style="margin: 0; font-size: 24px;">{{ config('shop.name') }}</h2>
                        <p style="margin: 0; color: #475569;">{{ config('shop.tagline') }}</p>
                    </div>
                </div>
            </div>
            <hr class="my-2" style="border-top: 2px solid #000;">
        </div>

        <!-- Invoice Details -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h4 class="text-muted mb-2">INVOICE DETAILS</h4>
                <p class="mb-1"><strong>Invoice Number:</strong> {{ $sale->invoice_number }}</p>
                <p class="mb-1"><strong>Date:</strong> {{ $sale->created_at->format('d/m/Y h:i A') }}</p>
                <p class="mb-1">
                    <strong>Payment Status:</strong> {{ ucfirst($sale->payment_status) }}
                </p>
            </div>
            <div class="col-md-6">
                <h4 class="text-muted mb-2">CUSTOMER DETAILS</h4>
                @if($sale->customer)
                <p class="mb-1"><strong>Name:</strong> {{ $sale->customer->name }}</p>
                <p class="mb-1"><strong>Phone:</strong> {{ $sale->customer->phone }}</p>
                <p class="mb-1"><strong>Type:</strong> {{ ucfirst($sale->customer_type) }}</p>
                @else
                <p class="text-muted">Walk-in Customer</p>
                @endif
            </div>
        </div>
        <div class="clearfix"></div>

        <!-- Items Table -->
        <h4 class="text-muted mb-2">PURCHASED ITEMS</h4>
        <table class="mb-4">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Item</th>
                    <th>Code</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Discount</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->Product_name }}</td>
                    <td>{{ $item->Product_code }}</td>
                    <td>Rs.{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>Rs.{{ number_format($item->discount * $item->quantity, 2) }}</td>
                    <td>Rs.{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Payment Details -->
        <div class="row">
            <div class="col-md-6">
                <h4 class="text-muted mb-2">PAYMENT INFORMATION</h4>
                @if($sale->payments->count() > 0)
                @foreach($sale->payments as $payment)
                <div class="mb-2 p-2 border-start {{ $payment->is_completed ? 'border-success' : 'border-warning' }} bg-light">
                    <p class="mb-1">
                        <strong>{{ $payment->is_completed ? 'Payment' : 'Scheduled Payment' }}:</strong>
                        Rs.{{ number_format($payment->amount, 2) }}
                    </p>
                    <p class="mb-1"><strong>Method:</strong> {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</p>
                    @if($payment->payment_reference)
                    <p class="mb-1"><strong>Reference:</strong> {{ $payment->payment_reference }}</p>
                    @endif
                    @if($payment->is_completed)
                    <p class="mb-0"><strong>Date:</strong> {{ $payment->payment_date->format('d/m/Y') }}</p>
                    @else
                    <p class="mb-0"><strong>Due Date:</strong> {{ $payment->due_date->format('d/m/Y') }}</p>
                    @endif
                </div>
                @endforeach
                @else
                <p class="text-muted">No payment information available</p>
                @endif

                @if($sale->notes)
                <h4 class="text-muted mt-3 mb-2">NOTES</h4>
                <p class="text-muted">{{ $sale->notes }}</p>
                @endif
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body p-3">
                        <h4>ORDER SUMMARY</h4>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>Rs.{{ number_format($sale->subtotal, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Discount:</span>
                            <span>Rs.{{ number_format($sale->discount_amount, 2) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Grand Total:</span>
                            <span class="fw-bold">Rs.{{ number_format($sale->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>

        <!-- Global Footer (same as print) -->
        <div class="global-footer" style="margin-top: 40px;">
            <div style="display: flex; justify-content: center; text-align: center; margin-bottom: 20px;">
                <div style="flex: 0 0 33.3333%; max-width: 33.3333%; padding: 0 10px;">
                    <p><strong>.............................</strong></p>
                    <p style="margin-bottom: 8px;"><strong>Checked By</strong></p>
                    <img src="{{ public_path('images/logo.png') }}" alt="{{ config('shop.name') }}" style="height: 35px; margin: auto;">
                </div>
                <div style="flex: 0 0 33.3333%; max-width: 33.3333%; padding: 0 10px;">
                    <p><strong>.............................</strong></p>
                    <p style="margin-bottom: 8px;"><strong>Authorized Officer</strong></p>
                    <img src="{{ public_path('images/logo.png') }}" alt="{{ config('shop.name') }}" style="height: 35px; margin: auto;">
                </div>
                <div style="flex: 0 0 33.3333%; max-width: 33.3333%; padding: 0 10px;">
                    <p><strong>.............................</strong></p>
                    <p style="margin-bottom: 8px;"><strong>Customer Stamp</strong></p>
                    <img src="{{ public_path('images/logo.png') }}" alt="{{ config('shop.name') }}" style="height: 35px; margin: auto;">
                </div>
            </div>
            <div style="border-top: 1px solid #3b5b0c; padding-top: 10px;">
                <p style="text-align: center;"><strong>ADDRESS :</strong> {{ config('shop.address') }}</p>
                <p style="text-align: center;"><strong>TEL :</strong> {{ config('shop.phone') }}, <strong>EMAIL :</strong> {{ config('shop.email') }}</p>
                <p style="text-align: center; font-size: 11px; margin-top: 8px;"><strong>Goods return will be accepted within 10 days only. Electrical and body parts non-returnable.</strong></p>
            </div>
        </div>
    </div>
</body>

</html>
