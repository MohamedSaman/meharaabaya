<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $sale->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #000;
            background: #fff;
            padding: 6px;
        }

        @page {
            size: A5 landscape;
            margin: 5mm;
        }

        /* -- HEADER -- */
        .inv-wrap {
            width: 100%;
            border: 2px solid #000;
            position: relative;
        }

        .inv-hdr-tbl {
            width: 100%;
            border-bottom: 2px solid #000;
            border-collapse: collapse;
        }

        .inv-company-td {
            padding: 6px;
            vertical-align: top;
            width: 60%;
        }

        .inv-infobox-td {
            width: 40%;
            vertical-align: top;
            border-left: 2px solid #000;
            padding: 0;
        }

        .inv-ib-tbl {
            width: 100%;
            border-collapse: collapse;
        }

        .inv-ib-tbl td {
            padding: 2px 5px;
            border-bottom: 1px solid #000;
            font-size: 9px;
        }

        .inv-ib-tbl tr:last-child td {
            border-bottom: none;
        }

        .inv-ib-lbl {
            background-color: #0072BC;
            color: #fff;
            font-family: 'Helvetica', 'Roboto', sans-serif;
            font-weight: bold;
            width: 80px;
            border-right: 1px solid #000;
        }

        .inv-ib-val {
            background-color: #fff;
            color: #000;
            font-weight: bold;
        }

        .inv-company-inner {
            width: 100%;
            border-collapse: collapse;
        }

        .inv-logo-td {
            width: 70px;
            text-align: center;
            vertical-align: middle;
            padding-right: 10px;
        }

        .inv-logo {
            max-width: 70px;
            max-height: 70px;
        }

        .inv-shop-name {
            font-family: 'Impact', 'Arial Black', sans-serif;
            font-size: 16px;
            color: #ED1C24;
            padding-bottom: 2px;
            letter-spacing: 0.5px;
        }

        .inv-shop-tag {
            font-size: 9px;
            font-style: italic;
            font-family: 'Helvetica', 'Roboto', sans-serif;
            font-weight: bold;
            color: #0072BC;
            padding-bottom: 2px;
        }

        .inv-shop-addr,
        .inv-shop-contact {
            font-size: 9px;
            font-family: 'Arial', sans-serif;
            color: #000;
        }

        /* -- BILL TO -- */
        .inv-bto-tbl {
            width: 100%;
            border-bottom: 2px solid #000;
            border-collapse: collapse;
        }

        .inv-bto-td {
            padding: 4px 8px;
            font-size: 9px;
            font-family: 'Helvetica', 'Roboto', sans-serif;
            font-weight: bold;
        }

        .inv-bto-lines {
            min-height: 36px;
            display: flex;
            flex-direction: column;
            gap: 1px;
            justify-content: center;
        }

        .inv-bto-subline {
            font-family: 'Arial', sans-serif;
            font-weight: normal;
        }

        /* -- ITEMS TABLE -- */
        .inv-items-tbl {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #000;
        }

        .inv-items-tbl th {
            background-color: #0072BC;
            color: #fff;
            text-align: left;
            padding: 3px 5px;
            font-size: 9px;
            font-family: 'Helvetica', 'Roboto', sans-serif;
            font-weight: bold;
            border-right: 1px solid #000;
            border-bottom: 2px solid #000;
        }

        .inv-items-tbl td {
            padding: 2px 5px;
            font-size: 9px;
            font-family: 'Arial', sans-serif;
            border-right: 1px solid #000;
            border-bottom: none;
        }

        .inv-items-tbl tbody tr:last-child td {
            border-bottom: 2px solid #000;
        }

        .inv-items-tbl th:last-child,
        .inv-items-tbl td:last-child {
            border-right: none;
        }

        .inv-tr {
            text-align: right;
        }

        .inv-tc {
            text-align: center;
        }

        .inv-filler td {
            height: 11px;
            border-bottom: none;
        }

        /* -- BOTTOM TABLE -- */
        .inv-bot-tbl {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #000;
            page-break-inside: avoid;
        }

        .inv-bot-left {
            vertical-align: top;
            padding: 6px 8px;
            width: 60%;
            font-family: 'Helvetica', 'Roboto', sans-serif;
        }

        .inv-bot-right {
            vertical-align: top;
            width: 40%;
            border-left: 2px solid #000;
            padding: 0;
        }

        .inv-out-lbl {
            font-weight: bold;
            margin-bottom: 3px;
            font-size: 9px;
        }

        .inv-out-val {
            font-style: italic;
            font-family: 'Arial', sans-serif;
            font-size: 9px;
        }

        .inv-tot-tbl {
            width: 100%;
            border-collapse: collapse;
        }

        .inv-tot-tbl td {
            padding: 3px 6px;
            font-size: 9px;
            font-weight: bold;
            font-family: 'Arial', sans-serif;
            border-bottom: 1px solid #000;
        }

        .inv-tot-tbl tr:last-child td {
            border-bottom: none;
        }

        .inv-tot-lbl {
            background-color: #0072BC;
            color: #fff;
            width: 40%;
            font-family: 'Helvetica', sans-serif;
            border-right: 1px solid #000;
        }

        .inv-tot-val {
            text-align: right;
            border-bottom: 1px solid #000;
        }

        /* -- SIGNATURE ROW -- */
        .inv-sig-tbl {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            page-break-inside: avoid;
        }

        .inv-sig-td {
            width: 30%;
            text-align: center;
            vertical-align: bottom;
            padding: 8px 8px 4px;
        }

        .inv-note-td {
            width: 40%;
            text-align: center;
            vertical-align: top;
            font-style: italic;
            font-size: 8px;
            font-family: 'Arial', sans-serif;
            color: #ED1C24;
            padding: 6px;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
        }

        .inv-sig-line {
            border-top: 1px solid #000;
            margin: 0 auto 5px;
            width: 80%;
        }

        .inv-sig-lbl {
            font-size: 9px;
            font-family: 'Helvetica', sans-serif;
            font-weight: bold;
        }

        /* WATERMARK FIX FOR PDF */
        .inv-watermark-container {
            position: relative;
        }

        .inv-watermark {
            position: absolute;
            top: 50px;
            left: 200px;
            width: 350px;
            opacity: 0.1;
            z-index: -1;
        }
    </style>
</head>

<body>
    <div class="inv-wrap">

        {{-- -- HEADER -- --}}
        <table class="inv-hdr-tbl" cellpadding="0" cellspacing="0">
            <tr>
                <td class="inv-company-td">
                    <table cellpadding="0" cellspacing="0" class="inv-company-inner">
                        <tr>
                            <td rowspan="4" class="inv-logo-td">
                                <?php
                                $logoPath = public_path('images/logo.png');
                                if (file_exists($logoPath)) {
                                    $imgData = base64_encode(file_get_contents($logoPath));
                                    $src = 'data:image/png;base64,' . $imgData;
                                } else {
                                    $src = '';
                                }
                                ?>
                                @if($src)
                                <img src="{{ $src }}" alt="" class="inv-logo">
                                @endif
                            </td>
                            <td class="inv-shop-name">{{ config('shop.name') }}</td>
                        </tr>
                        <tr>
                            <td class="inv-shop-tag">{{ config('shop.tagline') }}</td>
                        </tr>
                        <tr>
                            <td class="inv-shop-addr">{{ config('shop.address') }}</td>
                        </tr>
                        <tr>
                            <td class="inv-shop-contact">Tele: {{ config('shop.phone') }} &nbsp;&nbsp; Email: {{ config('shop.email') }}</td>
                        </tr>
                    </table>
                </td>
                <td class="inv-infobox-td">
                    <table class="inv-ib-tbl" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="inv-ib-lbl">Date</td>
                            <td class="inv-ib-val">{{ $sale->created_at->format('d/m/Y') }} {{ $sale->created_at->format('H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="inv-ib-lbl">Invoice No.</td>
                            <td class="inv-ib-val">{{ $sale->invoice_number }}</td>
                        </tr>
                        <tr>
                            <td class="inv-ib-lbl">Sales Rep.</td>
                            <td class="inv-ib-val">
                                @php
                                $repName = '-';
                                if ($sale->sales_rep_id) {
                                $repUser = \App\Models\User::find($sale->sales_rep_id);
                                $repName = $repUser ? $repUser->name : '-';
                                } elseif ($sale->admin_id) {
                                $repUser = \App\Models\User::find($sale->admin_id);
                                $repName = $repUser ? $repUser->name : 'Admin';
                                }
                                @endphp
                                {{ $repName }}
                            </td>
                        </tr>
                        <tr>
                            <td class="inv-ib-lbl">Payment</td>
                            @php
                            $paymentMethodLabels = [
                            'cash' => 'Cash',
                            'cheque' => 'Cheque',
                            'bank_transfer' => 'Bank Transfer',
                            'credit_card' => 'Credit Card',
                            ];

                            $paymentMethods = $sale->payments
                            ->pluck('payment_method')
                            ->filter()
                            ->unique()
                            ->values();

                            if ($paymentMethods->count() > 1) {
                            $methodText = $paymentMethods
                            ->map(fn ($method) => $paymentMethodLabels[$method] ?? ucwords(str_replace('_', ' ', $method)))
                            ->implode(' + ');
                            $paymentLabel = 'Multiple (' . $methodText . ')';
                            } elseif ($paymentMethods->count() === 1) {
                            $singleMethod = $paymentMethods->first();
                            $paymentLabel = $paymentMethodLabels[$singleMethod] ?? ucwords(str_replace('_', ' ', $singleMethod));
                            } else {
                            $paymentLabel = ($sale->due_amount ?? 0) > 0 ? 'Due' : 'Cash';
                            }
                            @endphp
                            <td class="inv-ib-val">{{ $paymentLabel }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- -- BILL TO -- --}}
        <table class="inv-bto-tbl" cellpadding="0" cellspacing="0">
            <tr>
                <td class="inv-bto-td">
                    @php
                    $billName = trim((string) (optional($sale->customer)->name ?? ''));
                    $billAddress = trim((string) (optional($sale->customer)->address ?? ''));
                    $billPhone = trim((string) (optional($sale->customer)->phone ?? ''));
                    if ($billName === '' || is_numeric($billName)) {
                    $billName = 'Walking Customer';
                    }
                    @endphp
                    <div class="inv-bto-lines">
                        <div><strong>Bill To:</strong> <span class="inv-bto-subline">{{ $billName }}</span></div>
                        <div class="inv-bto-subline"><strong>Address:</strong> {{ $billAddress !== '' ? $billAddress : 'None' }}</div>
                        <div class="inv-bto-subline"><strong>Tel:</strong> {{ $billPhone !== '' ? $billPhone : 'None' }}</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- -- ITEMS TABLE -- --}}
        <div class="inv-watermark-container">
            @if($src)
            <img src="{{ $src }}" class="inv-watermark" alt="">
            @endif
            <table class="inv-items-tbl" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th class="inv-c-code">Code</th>
                        <th class="inv-c-desc">Description</th>
                        <th class="inv-c-qty inv-tc">Qty</th>
                        <th class="inv-c-price inv-tr">Unit Price</th>
                        <th class="inv-c-disc inv-tr">Discount</th>
                        <th class="inv-c-amt inv-tr">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $item)
                    <tr>
                        <td class="inv-c-code">{{ $item->product_code }}</td>
                        <td class="inv-c-desc">{{ $item->product_name }}</td>
                        <td class="inv-c-qty inv-tc">{{ $item->quantity }}</td>
                        <td class="inv-c-price inv-tr">Rs.{{ number_format($item->unit_price, 2) }}</td>
                        <td class="inv-c-disc inv-tr">
                            @if($item->discount_per_unit > 0)-Rs.{{ number_format($item->discount_per_unit, 2) }}@else&nbsp;-@endif
                        </td>
                        <td class="inv-c-amt inv-tr">Rs.{{ number_format(($item->unit_price - $item->discount_per_unit) * $item->quantity, 2) }}</td>
                    </tr>
                    @endforeach
                    @php $invFiller = max(0, 10 - count($sale->items)); @endphp
                    @for($f = 0; $f < $invFiller; $f++)
                        <tr class="inv-filler">
                        <td>&nbsp;</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        </tr>
                        @endfor
                </tbody>
            </table>
        </div>

        {{-- -- BOTTOM: OUTSTANDINGS + TOTALS -- --}}
        <table class="inv-bot-tbl" cellpadding="0" cellspacing="0">
            <tr>
                <td class="inv-bot-left">
                    <div class="inv-out-lbl">OUT STANDINGS:-</div>
                    <div class="inv-out-val">
                        @if($sale->due_amount > 0)
                        Rs.{{ number_format($sale->due_amount, 2) }}
                        @else
                        None
                        @endif
                    </div>
                </td>
                <td class="inv-bot-right">
                    <table class="inv-tot-tbl" cellpadding="0" cellspacing="0">
                        @php
                        $displayDiscount = max(0, (float) ($sale->discount_amount ?? 0));
                        @endphp
                        <tr>
                            <td class="inv-tot-lbl">Sub Total</td>
                            <td class="inv-tot-val">Rs.{{ number_format($sale->total_amount + $displayDiscount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="inv-tot-lbl">Discount</td>
                            <td class="inv-tot-val" @if($displayDiscount> 0) style="color:#c0392b;" @endif>
                                @if($displayDiscount > 0)
                                -Rs.{{ number_format($displayDiscount, 2) }}
                                @else
                                Rs.0.00
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="inv-tot-lbl">Net Total</td>
                            <td class="inv-tot-val">Rs.{{ number_format($sale->total_amount, 2) }}</td>
                        </tr>
                        @php
                        $displayPaid = min($sale->payments->sum('amount'), $sale->total_amount);
                        $displayBalance = max(0, $sale->total_amount - $displayPaid);
                        @endphp
                        <tr>
                            <td class="inv-tot-lbl">Paid</td>
                            <td class="inv-tot-val">Rs.{{ number_format($displayPaid, 2) }}</td>
                        </tr>
                        <tr class="inv-bal-row">
                            <td class="inv-tot-lbl">Balance</td>
                            <td class="inv-tot-val">Rs.{{ number_format($displayBalance, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="inv-tot-lbl">Due Date</td>
                            <td class="inv-tot-val">
                                @if($displayBalance > 0 && $sale->due_date)
                                {{ \Carbon\Carbon::parse($sale->due_date)->format('d/m/Y') }}
                                @php
                                $dueDays = \Carbon\Carbon::parse($sale->created_at)->startOfDay()->diffInDays(\Carbon\Carbon::parse($sale->due_date)->startOfDay(), false);
                                @endphp
                                @if($dueDays > 0)
                                <span style="font-size: 9px; font-weight: normal;">({{ $dueDays }} Days)</span>
                                @endif
                                @else
                                None
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- -- SIGNATURE ROW -- --}}
        <table class="inv-sig-tbl" cellpadding="0" cellspacing="0">
            <tr>
                <td class="inv-sig-td">
                    <div class="inv-sig-line"></div>
                    <div class="inv-sig-lbl">Customer Signature</div>
                </td>
                <td class="inv-note-td">
                    Goods Received in good condition. &lsquo;Warranty covers only manufacturing defects.&rsquo;
                </td>
                <td class="inv-sig-td">
                    <div class="inv-sig-line"></div>
                    <div class="inv-sig-lbl">Authorised Signature</div>
                </td>
            </tr>
        </table>

    </div>
</body>

</html>