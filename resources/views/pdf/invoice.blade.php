<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">

    @php
        $fontDir = public_path('assets/fonts/be-vietnam-pro');

        $beVietnamRegular = is_file($fontDir . '/BeVietnamPro-Regular.ttf')
            ? base64_encode(file_get_contents($fontDir . '/BeVietnamPro-Regular.ttf'))
            : null;
        $beVietnamSemiBold = is_file($fontDir . '/BeVietnamPro-SemiBold.ttf')
            ? base64_encode(file_get_contents($fontDir . '/BeVietnamPro-SemiBold.ttf'))
            : null;
        $beVietnamBold = is_file($fontDir . '/BeVietnamPro-Bold.ttf')
            ? base64_encode(file_get_contents($fontDir . '/BeVietnamPro-Bold.ttf'))
            : null;
        $beVietnamExtraBold = is_file($fontDir . '/BeVietnamPro-ExtraBold.ttf')
            ? base64_encode(file_get_contents($fontDir . '/BeVietnamPro-ExtraBold.ttf'))
            : null;
        $beVietnamBlack = is_file($fontDir . '/BeVietnamPro-Black.ttf')
            ? base64_encode(file_get_contents($fontDir . '/BeVietnamPro-Black.ttf'))
            : null;

        $currency = $invoice->currency ?? 'LKR';
        $invoiceNo = $invoice->invoice_no ?? ('INV-' . $invoice->id);
        $orderNo = $order?->order_no ?? ('ORD-' . ($order?->id ?? ''));
        $issuedDate = $invoice->issued_at ? $invoice->issued_at->format('Y-m-d') : now()->format('Y-m-d');
        $status = strtoupper((string) ($invoice->status ?? 'draft'));

        $custSnap = is_array($invoice->customer_snapshot) ? $invoice->customer_snapshot : (is_array($order?->customer_snapshot) ? $order->customer_snapshot : []);
        $custName = $custSnap['full_name'] ?? $custSnap['name'] ?? ($order?->customer_name ?? 'Customer');
        $custEmail = $custSnap['email'] ?? ($order?->customer_email ?? '');
        $custWhatsapp = $custSnap['whatsapp_number'] ?? ($order?->customer_whatsapp_number ?? '');

        $ship = is_array($order?->shipping_snapshot) ? $order->shipping_snapshot : [];
    @endphp

    <style>
        @page {
            margin: 170px 36px 78px 36px;
        }

        @if ($beVietnamRegular)
            @font-face {
                font-family: 'Be Vietnam Pro';
                font-style: normal;
                font-weight: 400;
                src: url('data:font/truetype;charset=utf-8;base64,{{ $beVietnamRegular }}') format('truetype');
            }
        @endif

        @if ($beVietnamSemiBold)
            @font-face {
                font-family: 'Be Vietnam Pro';
                font-style: normal;
                font-weight: 600;
                src: url('data:font/truetype;charset=utf-8;base64,{{ $beVietnamSemiBold }}') format('truetype');
            }
        @endif

        @if ($beVietnamBold)
            @font-face {
                font-family: 'Be Vietnam Pro';
                font-style: normal;
                font-weight: 700;
                src: url('data:font/truetype;charset=utf-8;base64,{{ $beVietnamBold }}') format('truetype');
            }
        @endif

        @if ($beVietnamExtraBold)
            @font-face {
                font-family: 'Be Vietnam Pro';
                font-style: normal;
                font-weight: 800;
                src: url('data:font/truetype;charset=utf-8;base64,{{ $beVietnamExtraBold }}') format('truetype');
            }
        @endif

        @if ($beVietnamBlack)
            @font-face {
                font-family: 'Be Vietnam Pro';
                font-style: normal;
                font-weight: 900;
                src: url('data:font/truetype;charset=utf-8;base64,{{ $beVietnamBlack }}') format('truetype');
            }
        @endif

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Be Vietnam Pro", DejaVu Sans, Arial, sans-serif;
            color: #0f172a;
            font-size: 12px;
        }

        .muted {
            color: #64748b;
        }

        .brand {
            color: #ff2828;
        }

        .right {
            text-align: right;
        }

        header {
            position: fixed;
            top: -170px;
            left: 0;
            right: 0;
            height: 160px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 9px 8px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        thead th {
            background: #f8fafc;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #475569;
        }

        .titlebar {
            width: 100%;
            background: #ff2828;
            color: #fff;
            border-radius: 8px;
            padding: 9px 12px;
            font-weight: 900;
            letter-spacing: .08em;
        }

        .card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px;
            background: #fff;
        }

        .h2 {
            font-size: 11px;
            font-weight: 800;
            margin: 0 0 8px 0;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .mt-10 {
            margin-top: 10px;
        }

        .mt-14 {
            margin-top: 14px;
        }

        .totals td {
            border-bottom: 0;
            padding: 6px 8px;
        }

        .totals .label {
            color: #64748b;
        }

        .totals .value {
            font-weight: 900;
        }
    </style>
</head>

<body>
    <header>
        <div class="titlebar">INVOICE</div>

        <table style="margin-top: 10px;">
            <tr>
                <td class="card" style="width: 25%;">
                    <div class="muted" style="font-size: 11px;">Issued Date</div>
                    <div style="font-weight: 900;">{{ $issuedDate }}</div>
                </td>
                <td style="width: 10px;"></td>
                <td class="card" style="width: 25%;">
                    <div class="muted" style="font-size: 11px;">Order No</div>
                    <div style="font-weight: 900;">{{ $orderNo }}</div>
                </td>
                <td style="width: 10px;"></td>
                <td class="card" style="width: 25%;">
                    <div class="muted" style="font-size: 11px;">Status</div>
                    <div style="font-weight: 900;">{{ $status }}</div>
                </td>
                <td style="width: 10px;"></td>
                <td class="card right" style="width: 25%;">
                    <div class="muted" style="font-size: 11px;">Total</div>
                    <div style="font-weight: 900; font-size: 14px;">{{ $currency }}
                        {{ number_format((float) $invoice->grand_total, 2) }}</div>
                </td>
            </tr>
        </table>
    </header>

    {{-- Page 1 masthead (not fixed, so it won’t repeat) --}}
    <table class="mt-10">
        <tr>
            <td style="width: 55%;" class="card">
                <div class="h2">From</div>
                <div style="font-size: 16px; font-weight: 900;" class="brand">
                    {{ config('app.name', 'Printair') }}
                </div>
                <div class="muted" style="margin-top: 4px; font-size: 10px; line-height: 1.4;">
                    {{ config('app.url', 'https://printair.lk') }}
                </div>
            </td>
            <td style="width: 10px;"></td>
            <td style="width: 45%;" class="card">
                <div class="h2">Invoice</div>
                <div class="muted" style="font-size: 11px;">Invoice No</div>
                <div style="font-size: 16px; font-weight: 900;">{{ $invoiceNo }}</div>
            </td>
        </tr>
    </table>

    <table class="mt-10">
        <tr>
            <td class="card">
                <div class="h2">Bill To</div>
                <div style="font-weight: 900;">{{ $custName }}</div>
                @if ($custEmail)
                    <div class="muted" style="margin-top: 2px;">{{ $custEmail }}</div>
                @endif
                @if ($custWhatsapp)
                    <div class="muted" style="margin-top: 2px;">{{ $custWhatsapp }}</div>
                @endif
                @if (!empty($ship))
                    <div class="muted" style="margin-top: 6px; font-size: 11px;">
                        {{ $ship['line1'] ?? '' }}
                        {{ !empty($ship['line2']) ? (', ' . $ship['line2']) : '' }}<br>
                        {{ $ship['city'] ?? '' }}
                        {{ !empty($ship['district']) ? (', ' . $ship['district']) : '' }}
                        {{ !empty($ship['postal_code']) ? (' ' . $ship['postal_code']) : '' }}
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <div class="mt-14 card">
        <table>
            <thead>
                <tr>
                    <th style="width: 52%;">Item</th>
                    <th class="right" style="width: 10%;">Qty</th>
                    <th class="right" style="width: 19%;">Unit</th>
                    <th class="right" style="width: 19%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $it)
                    <tr>
                        <td>
                            <div style="font-weight: 900;">{{ $it->title ?? $it->product?->name ?? 'Item' }}</div>

                            @if ($it->variantSetItem?->option)
                                <div class="muted" style="font-size: 11px; margin-top: 2px;">
                                    Variant: {{ $it->variantSetItem->option->label }}
                                </div>
                            @endif

                            @if ($it->roll)
                                <div class="muted" style="font-size: 11px; margin-top: 2px;">
                                    Roll: {{ $it->roll->name }}
                                </div>
                            @endif

                            @if ($it->width && $it->height)
                                <div class="muted" style="font-size: 11px; margin-top: 2px;">
                                    Size: {{ $it->width }} × {{ $it->height }} {{ $it->unit }}
                                </div>
                            @endif

                            @if (!empty($it->description))
                                <div class="muted" style="font-size: 11px; margin-top: 4px;">
                                    {{ $it->description }}
                                </div>
                            @endif

                            @if ($it->relationLoaded('finishings') && $it->finishings->count())
                                <div style="margin-top: 6px; font-size: 11px;">
                                    <div class="muted" style="font-weight: 800;">Finishings</div>
                                    <ul style="margin: 4px 0 0 16px; padding: 0;">
                                        @foreach ($it->finishings as $f)
                                            <li class="muted">
                                                {{ $f->label ?? $f->option?->label ?? $f->finishingProduct?->name ?? 'Finishing' }}
                                                — {{ $currency }} {{ number_format((float) $f->total, 2) }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </td>
                        <td class="right">{{ (int) $it->qty }}</td>
                        <td class="right">{{ $currency }} {{ number_format((float) $it->unit_price, 2) }}</td>
                        @php
                            $finTotal = (float) ($it->relationLoaded('finishings') ? $it->finishings->sum('total') : 0);
                            $lineWithFin = (float) ($it->line_total ?? 0) + $finTotal;
                        @endphp
                        <td class="right" style="font-weight: 900;">{{ $currency }} {{ number_format($lineWithFin, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals" style="margin-top: 10px;">
            <tr>
                <td class="label right" style="width: 81%;">Subtotal</td>
                <td class="value right" style="width: 19%;">{{ $currency }}
                    {{ number_format((float) $invoice->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td class="label right">Discount</td>
                <td class="value right">{{ $currency }}
                    {{ number_format((float) $invoice->discount_total, 2) }}</td>
            </tr>
            <tr>
                <td class="label right">Tax</td>
                <td class="value right">{{ $currency }}
                    {{ number_format((float) $invoice->tax_total, 2) }}</td>
            </tr>
            <tr>
                <td class="label right">Delivery</td>
                <td class="value right">{{ $currency }}
                    {{ number_format((float) $invoice->shipping_fee, 2) }}</td>
            </tr>
            <tr>
                <td class="label right">Other</td>
                <td class="value right">{{ $currency }}
                    {{ number_format((float) $invoice->other_fee, 2) }}</td>
            </tr>
            <tr>
                <td class="label right" style="font-weight: 900; color: #0f172a;">Grand Total</td>
                <td class="value right" style="font-weight: 900;">{{ $currency }}
                    {{ number_format((float) $invoice->grand_total, 2) }}</td>
            </tr>
        </table>

        <div class="mt-14 muted" style="font-size: 10px; line-height: 1.6;">
            This invoice is generated by Printair. Prices and availability are subject to confirmation until the invoice
            is issued.
            If you have questions, contact us with Invoice # <span class="brand"
                style="font-weight:800;">{{ $invoiceNo }}</span>.
        </div>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "printair.lk | Page {PAGE_NUM} of {PAGE_COUNT} | System Approved";
            $font = $fontMetrics->get_font("DejaVu Sans", "normal");
            $size = 9;

            $w = $fontMetrics->get_text_width($text, $font, $size);
            $x = ($pdf->get_width() - $w) / 2;
            $y = $pdf->get_height() - 40;

            $pdf->page_text($x, $y, $text, $font, $size, [0.39, 0.45, 0.55]);
        }
    </script>
</body>

</html>
