<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Receipt - {{ $sale->invoice_number }}</title>
    <style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body {
        font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
        font-size: 9pt;
        color: black;
        background: white;
        line-height: 1.35;
        padding: 8px;
      }

      .inv-wrap {
        position: relative;
        border: none;
        border-radius: 0;
        overflow: visible;
        background: white;
      }

      .inv-wrap > table {
        position: relative;
        z-index: 1;
      }

      .inv-watermark {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 6px;
        opacity: 0.3;
        pointer-events: none;
        z-index: 2;
        text-align: center;
      }

      .inv-watermark-logo {
        width: 300px;
        height: auto;
      }

      /* ── Header ── */
      .inv-hdr-tbl {
        width: 100%;
        border-collapse: collapse;
      }

      .inv-company-td {
        width: 66%;

        border-right: none;
        padding: 5px 8px;
        vertical-align: middle;
        background: white;
      }

      .inv-company-inner {
        width: 100%;
        border-collapse: collapse;
      }

      .inv-logo-td {
        width: 60px;
        padding-right: 8px;
        vertical-align: middle;
      }

      .inv-logo {
        height: 70px;
        width: auto;
        display: block;
      }

      .inv-shop-name {
        font-size: 15pt;
        font-weight: bold;
        letter-spacing: 0.8px;
        padding-bottom: 1px;
        color: #0d5f9a;
      }

      .inv-shop-tag {
        font-size: 8pt;
        font-style: italic;
        color: #0d5f9a;
        padding-bottom: 1px;
        font-weight: 600;
      }

      .inv-shop-addr,
      .inv-shop-contact {
        font-size: 6pt;
      }

      .inv-infobox-td {
        width: 34%;
        padding: 0;
        vertical-align: top;
        background: white;
      }

      .inv-ib-tbl {
        width: 100%;
        border-collapse: collapse;
        height: 100%;
      }

      .inv-ib-lbl {
        border: 1px solid #0d5f9a;
        padding: 2px 5px;
        font-size: 8pt;
        font-weight: bold;
        white-space: nowrap;
        width: 42%;
        background: #0d5f9a;
        color: white;
      }

      .inv-ib-val {
        border: 1px solid #0d5f9a;
        border-left: none;
        padding: 2px 5px;
        font-size: 8pt;
        font-weight: 600;
        color: black;
      }

      /* ── Bill To ── */
      .inv-bto-tbl {
        width: 60%;
        border-collapse: separate;
        border-spacing: 0;
        padding: 2px 6px 0;
        background: white;
      }

      .inv-bto-td {
        border: 1px solid #0d5f9a;
        padding: 4px 8px;
        font-size: 8pt;
        border-radius: 8px;
        background: white;
      }

      .inv-bto-lines {
        min-height: 36px;
        display: flex;
        flex-direction: column;
        gap: 1px;
        justify-content: center;
      }

      .inv-bto-subline {
        font-family: "Arial", sans-serif;
        font-weight: normal;
      }

      /* ── Items Table ── */
      .inv-items-tbl {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 3px;
      }

      .inv-items-tbl th {
        background: #0d5f9a;
        color: white;
        border-top: 1px solid #0d5f9a;
        border-bottom: 1px solid #0d5f9a;
        border-right: 1px solid #0d5f9a;
        padding: 3px 5px;
        font-size: 8.2pt;
        font-weight: bold;
        text-align: left;
      }

      .inv-items-tbl th:first-child {
        border-left: 1px solid #0d5f9a;
        border-top-left-radius: 7px;
      }

      .inv-items-tbl th:last-child {
        border-top-right-radius: 7px;
      }

      .inv-items-tbl td {
        border-right: 1px solid #0d5f9a;
        border-top: none;
        border-bottom: none;
        padding: 2px 5px;
        font-size: 8pt;
      }

      .inv-items-tbl td:first-child {
        border-left: 1px solid #0d5f9a;
      }

      .inv-items-tbl tbody tr {
        height: 11px;
      }

      .inv-items-tbl tbody tr:last-child td {
        border-bottom: 1.5px solid #0d5f9a;
      }

      .inv-filler-row td {
        height: 11px;
      }

      .inv-c-code {
        width: 11%;
      }

      .inv-c-qty {
        width: 7%;
      }

      .inv-c-price {
        width: 14%;
      }

      .inv-c-disc {
        width: 13%;
      }

      .inv-c-amt {
        width: 14%;
      }

      .inv-tc {
        text-align: center;
      }

      .inv-tr {
        text-align: right;
      }

      /* ── Bottom Row ── */
      .inv-bot-tbl {
        width: 100%;
        border-collapse: collapse;
        page-break-inside: avoid;
      }

      .inv-bot-left {
        padding: 5px 8px;
        vertical-align: top;
        background: white;
      }

      .inv-out-lbl {
        font-weight: bold;
        font-size: 8pt;
        margin-bottom: 2px;
      }

      .inv-out-val {
        font-size: 8.5pt;
      }

      .inv-terms-list {
        margin: 0;
        padding-left: 14px;
      }

      .inv-terms-list li {
        margin-bottom: 1px;
        font-size: 8pt;
      }

      .check-box {
        display: inline-block;
        width: 15px;
        height: 15px;
        border: 1px solid #0d5f9a;
        margin-left: 6px;
        margin-bottom: 2px;
        vertical-align: middle;
      }

      .inv-tick-box {
        font-weight: bold;
      }

      .inv-bot-right {
        width: 36%;

        border-left: none;
        padding: 0;
        vertical-align: top;
        background: white;
      }

      .inv-tot-tbl {
        width: 100%;
        border-collapse: collapse;
      }
      .inv-tot-tbl tr {
        margin-bottom: 2px;
      }

      .inv-tot-lbl {
        border: 1px solid #0d5f9a;
        padding: 2px 6px;
        font-size: 8pt;
        font-weight: bold;
        width: 48%;
        background: #0d5f9a;
        color: white;
      }

      .inv-tot-val {
        border: 1px solid #0d5f9a;
        border-left: none;
        padding: 2px 6px;
        font-size: 8pt;
        text-align: right;
      }

      /* ── Signature Row ── */
      .inv-sig-tbl {
        width: 100%;
        border-collapse: collapse;
        page-break-inside: avoid;
        margin-top: 5px;
      }

      .inv-sig-td {
        width: 27%;

        padding: 6px 8px 4px;
        vertical-align: bottom;
        background: white;
      }

      .inv-note-td {
        border-left: none;
        border-right: none;
        padding: 6px 8px;
        font-size: 7.5pt;
        text-align: center;
        vertical-align: middle;
        font-style: italic;
        color: #0d5f9a;
        background: white;
      }

      .inv-sig-line {
        border-bottom: 1px solid #0d5f9a;
        height: 22px;
        margin-bottom: 3px;
      }

      .inv-sig-lbl {
        font-size: 7.5pt;
        font-weight: bold;
        text-align: center;
      }

      /* ═══ Print Styles — A5 Landscape ═══ */
      @media print {
        body {
          padding: 0;
          margin: 0;
          -webkit-print-color-adjust: exact;
          print-color-adjust: exact;
        }

        @page {
          size: A5 landscape;
          margin: 5mm;
        }
      }

      /* Auto-print trigger */
      @media screen {
        .no-print {
          display: block;
        }
      }

      @media print {
        .no-print {
          display: none !important;
        }
      }
    </style>
</head>

<body onload="window.print();">

    {{-- Print Button (screen only) --}}
    <div class="no-print" style="text-align: center; margin-bottom: 10px;">
        <button onclick="window.print();" style="padding: 8px 20px; font-size: 12px; cursor: pointer;">Print Again</button>
        <button onclick="window.close();" style="padding: 8px 20px; font-size: 12px; cursor: pointer; margin-left: 10px;">Close</button>
    </div>

    <div class="inv-wrap">

        <div class="inv-watermark" aria-hidden="true">
          <img src="{{ asset('images/logo.png') }}" alt="" class="inv-watermark-logo" />
        </div>
        {{-- ══ HEADER ══ --}}
        <table class="inv-hdr-tbl" cellpadding="0" cellspacing="0">
            <tr>
                <td class="inv-company-td">
                    <table cellpadding="0" cellspacing="0" class="inv-company-inner">
                        <tr>
                            <td rowspan="4" class="inv-logo-td">
                                <img src="{{ asset('images/logo.png') }}" alt="" class="inv-logo">
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
                            <td class="inv-ib-val">{{ $sale->user->name ?? '-' }}</td>
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

        {{-- ══ BILL TO ══ --}}
        <table class="inv-bto-tbl" cellpadding="0" cellspacing="0">
            <tr>
                <td class="inv-bto-td">
                    @php
                    $billName = trim((string) (optional($sale->customer)->name ?? ''));
                    $billAddress = trim((string) (optional($sale->customer)->address ?? ''));
                    $billPhone = trim((string) (optional($sale->customer)->phone ?? ''));
                    if ($billName === '' || is_numeric($billName)) {
                    $billName = 'Walk-in Customer';
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

        {{-- ══ ITEMS TABLE ══ --}}
        <table class="inv-items-tbl" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th class="inv-c-code">Code</th>
                    <th class="inv-c-desc">Description</th>
                    <th class="inv-c-qty">Qty</th>
                    <th class="inv-c-price">Unit Price</th>
                    <th class="inv-c-disc">Discount</th>
                    <th class="inv-c-amt">Amount</th>
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

        {{-- ══ BOTTOM: OUTSTANDINGS + TOTALS ══ --}}
        <table class="inv-bot-tbl" cellpadding="0" cellspacing="0">
            <tr>
                <td class="inv-bot-left">
                    <div class="inv-out-lbl">Term & Conditions</div>
                    <div class="inv-out-val">
                    <ol class="inv-terms-list">
                        <li>
                        If a manufactured fault is rectified, goodwill can be
                        exchanged.
                        </li>
                        <li>
                          When delivered goods are checked, if any damage return will not be
                          accepted. <span class="check-box"></span>
                        </li>
                        <li>Any goods returning within 14 days strictly.</li>
                        <li>The seller must ensure that all items are checked and free from damage before sale.</li>
                        <li>
                        Any bank charges incurred due to cheque returns (bounced
                        cheques) will be the customer's responsibility and must be
                        paid on the same date.
                        </li>
                    </ol>
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
                                @else
                                None
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- ══ SIGNATURE ROW ══ --}}
        <table class="inv-sig-tbl" cellpadding="0" cellspacing="0">
            <tr>
                <td class="inv-sig-td">
                    <div class="inv-sig-line"></div>
                    <div class="inv-sig-lbl">Customer Signature</div>
                </td>
                <td class="inv-note-td">
                    Thank you for your business! We look forward to serving you again.
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