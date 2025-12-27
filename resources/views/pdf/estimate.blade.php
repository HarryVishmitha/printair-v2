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
    @endphp

    <style>
        @page {
            margin: 170px 36px 78px 36px;
            /* top right bottom left */
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

        .center {
            text-align: center;
        }

        /* Fixed header & footer for multi-page */
        header {
            position: fixed;
            top: -170px;
            left: 0;
            right: 0;
            height: 160px;
        }

        footer {
            position: fixed;
            bottom: -58px;
            left: 0;
            right: 0;
            height: 58px;
            font-size: 10px;
            color: #64748b;
        }

        .footer-line {
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }

        /* Header blocks */
        .masthead {
            width: 100%;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
        }

        .masthead td {
            vertical-align: top;
        }

        .logo {
            width: 130px;
            height: auto;
        }

        .company-meta {
            font-size: 10px;
            line-height: 1.35;
        }

        .titlebar {
            margin-top: 0;
            width: 100%;
            background: #ff2828;
            color: #fff;
            border-radius: 8px;
            padding: 9px 12px;
        }

        .titlebar td {
            vertical-align: middle;
        }

        .title {
            font-size: 16px;
            font-weight: 900;
            letter-spacing: .08em;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            background: rgba(15, 23, 42, 0.95);
            color: #fff;
            font-size: 10px;
            font-weight: 800;
        }

        /* Cards */
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

        .mt-18 {
            margin-top: 18px;
        }

        /* Items table - page-safe */
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

        thead {
            display: table-header-group;
        }

        /* repeat headers every page */
        tfoot {
            display: table-row-group;
        }

        /* safe in dompdf */

        th {
            text-align: left;
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .08em;
            background: #f8fafc;
        }

        tr {
            page-break-inside: avoid;
        }

        /* prevent splitting rows */
        .no-break {
            page-break-inside: avoid;
        }

        /* prevent splitting blocks */

        .num {
            text-align: right;
            white-space: nowrap;
        }

        .items-table {
            table-layout: fixed;
            width: 100%;
        }

        .items-table th,
        .items-table td {
            padding: 6px 6px;
            font-size: 10px;
        }

        .item-title {
            font-weight: 800;
            color: #0f172a;
        }

        .item-desc {
            color: #64748b;
            margin-top: 3px;
        }

        .pill {
            display: inline-block;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 4px 8px;
            border-radius: 9999px;
            font-size: 10px;
            color: #475569;
        }

        /* Totals */
        .totals td {
            border-bottom: none;
            padding: 6px 8px;
        }

        .totals .label {
            color: #334155;
        }

        .totals .value {
            font-weight: 800;
        }

        .grand {
            font-size: 14px;
            font-weight: 900;
            color: #0f172a;
        }

        .grand-row td {
            border-top: 2px solid #0f172a;
            padding-top: 10px;
        }

        a {
            color: #ff2828;
            text-decoration: none;
        }

        /* Small meta table */
        .meta td {
            border: none;
            padding: 3px 0;
        }

        .meta .k {
            color: #64748b;
            width: 90px;
        }

        .meta .v {
            font-weight: 700;
            color: #0f172a;
        }

        .public-url {
            word-break: break-all;
            overflow-wrap: anywhere;
            font-size: 9px;
            line-height: 1.35;
        }
    </style>
</head>

<body>
    @php
        $no = $estimate->estimate_no ?? 'EST-' . $estimate->id;
        $currency = $estimate->currency ?? 'LKR';
        $snap = is_array($estimate->customer_snapshot) ? $estimate->customer_snapshot : [];

        $customerName = $snap['full_name'] ?? ($snap['name'] ?? ($estimate->customer?->full_name ?? '—'));
        $customerPhone = $snap['phone'] ?? ($estimate->customer?->phone ?? null);
        $customerEmail = $snap['email'] ?? ($estimate->customer?->email ?? null);
        $customerAddress = $snap['address'] ?? ($estimate->customer?->address ?? null);

        $validUntil = $estimate->valid_until ? \Carbon\Carbon::parse($estimate->valid_until) : null;

        // ✅ Dompdf-proof logo embedding (base64)
        $logoFile = public_path('assets/printair/printairlogo.png');
        $logoSrc = null;
        if (is_file($logoFile)) {
            $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile));
        }

        // Make long URLs breakable for Dompdf (CSS alone is not always enough).
        $publicUrlText = is_string($publicUrl ?? null) ? (string) $publicUrl : '';
        $publicUrlBreakable = $publicUrlText !== '' ? preg_replace('/(.{32})/u', '$1&#8203;', $publicUrlText) : '';

    @endphp

    <header>
        <table class="masthead">
            <tr>
                <td style="width: 55%;">
                    @if ($logoSrc)
                        <img class="logo" src="{{ $logoSrc }}" alt="Printair Advertising">
                    @endif
                    <div class="company-meta muted" style="margin-top:6px;">
                        <div style="font-weight:800; color:#0f172a;">Printair Advertising</div>
                        <div>No. 67/D/2, Uggashena Road, Walpola, Ragama, Sri Lanka</div>
                        <div>Phone: 076 886 0175 · WhatsApp: +94 76 886 0175</div>
                        <div>Email: contact@printair.lk · Web: printair.lk</div>
                    </div>
                </td>

                <td class="right" style="width: 45%;">
                    <table class="meta" style="width:100%;">
                        <tr>
                            <td class="k">Estimate #</td>
                            <td class="v right">{{ $no }}</td>
                        </tr>
                        <tr>
                            <td class="k">Date</td>
                            <td class="v right">{{ optional($estimate->created_at)->format('Y-m-d') }}</td>
                        </tr>
                        @if ($validUntil)
                            <tr>
                                <td class="k">Valid Until</td>
                                <td class="v right">{{ $validUntil->format('Y-m-d') }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="k">Status</td>
                            <td class="right">
                                <span class="badge">{{ strtoupper((string) ($estimate->status ?? 'draft')) }}</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </header>

    <div class="titlebar" style="margin-top: 0;">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td class="title">QUOTATION</td>
                <td class="right" style="font-size:11px;">
                    <span style="opacity:.9;">Currency:</span> <span
                        style="font-weight:900;">{{ $currency }}</span>
                </td>
            </tr>
        </table>
    </div>

    <footer>
        <div class="footer-line"></div>
    </footer>

    {{-- CONTENT --}}
    <div>

        {{-- BILL TO + ONLINE VIEW --}}
        <table class="mt-14" style="width:100%;">
            <tr>
                <td style="width:50%; padding-right:10px;">
                    <div class="card no-break">
                        <div class="h2">Bill To</div>
                        <div style="font-weight:900; font-size:13px;">{{ $customerName }}</div>
                        @if ($customerPhone)
                            <div class="muted">{{ $customerPhone }}</div>
                        @endif
                        @if ($customerEmail)
                            <div class="muted">{{ $customerEmail }}</div>
                        @endif
                        @if ($customerAddress)
                            <div class="muted" style="margin-top:4px;">{{ $customerAddress }}</div>
                        @endif
                    </div>
                </td>
                <td style="width:50%; padding-left:10px;">
                    <div class="card no-break">
                        <div class="h2">Online View</div>
                        <div class="muted">Use this link to view & respond online:</div>
                        <div class="mt-10 public-url">
                            @if (!empty($publicUrl))
                                <a href="{{ $publicUrl }}">{!! $publicUrlBreakable !!}</a>
                            @else
                                <span class="muted">Available after sending.</span>
                            @endif
                        </div>
                        <div class="mt-10 muted" style="font-size:10px;">
                            Tip: Customers can accept/reject directly from the online page.
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- ITEMS --}}
        <div class="mt-18 card">
            <div class="h2">Items</div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width:26px;">#</th>
                        <th>Item</th>
                        <th style="width:46px;" class="num">Qty</th>
                        <th style="width:74px;" class="num">Unit</th>
                        <th style="width:80px;" class="num">Subtotal</th>
                        <th style="width:62px;" class="num">Disc</th>
                        <th style="width:54px;" class="num">Tax</th>
                        <th style="width:78px;" class="num">Total</th>
                    </tr>
                </thead>
                <tbody>
	                    @foreach ($estimate->items as $idx => $it)
	                        @php
	                            $finTotal = (float) ($it->finishings?->sum('total') ?? 0);
	                            $variantLabel = (string) (data_get($it->pricing_snapshot, 'variant_label') ?? '');
	                            if ($variantLabel === '' && $it->variantSetItem) {
	                                $g = $it->variantSetItem->option?->group?->name;
	                                $o = $it->variantSetItem->option?->label;
	                                $variantLabel = trim(($g ? ($g . ': ') : '') . ($o ?: ''));
	                            }
	                            $lineSubtotalWithFin = (float) ($it->line_subtotal ?? 0) + $finTotal;
	                            $lineTotalWithFin = (float) ($it->line_total ?? 0) + $finTotal;
	                        @endphp
	                        <tr>
	                            <td class="muted">{{ $idx + 1 }}</td>
	                            <td>
	                                <div class="item-title">{{ $it->title }}</div>

                                @if ($it->description)
                                    <div class="item-desc">{{ $it->description }}</div>
                                @endif

	                                <div style="margin-top:6px;">
	                                    @if ($it->roll)
	                                        <span class="pill">Roll: {{ $it->roll->name ?? '#' . $it->roll_id }}</span>
	                                    @endif
	                                    @if ($variantLabel !== '')
	                                        <span class="pill">Variant: {{ $variantLabel }}</span>
	                                    @endif
	 
	                                    {{-- Add more pills later if you want:
	                                     Variant, dimensions, finishing count, etc. --}}
	                                </div>

	                                @if ($it->finishings?->count())
	                                    <div class="muted" style="margin-top:6px; font-size:10px; line-height:1.4;">
	                                        <div style="font-weight:700; color:#475569;">Finishings</div>
	                                        @foreach ($it->finishings as $f)
	                                            <div>
	                                                - {{ $f->label ?? ($f->option?->label ?? ('Finishing #' . $f->finishing_product_id)) }}
	                                                × {{ (int) ($f->qty ?? 1) }}
	                                                ({{ $currency }} {{ number_format((float) ($f->total ?? 0), 2) }})
	                                            </div>
	                                        @endforeach
	                                    </div>
	                                @endif
	                            </td>

                            <td class="num">{{ rtrim(rtrim(number_format((float) $it->qty, 2), '0'), '.') }}</td>
                            <td class="num">{{ number_format((float) $it->unit_price, 2) }}</td>
	                            <td class="num">{{ number_format((float) $lineSubtotalWithFin, 2) }}</td>
	                            <td class="num">{{ number_format((float) ($it->discount_amount ?? 0), 2) }}</td>
	                            <td class="num">{{ number_format((float) ($it->tax_amount ?? 0), 2) }}</td>
	                            <td class="num" style="font-weight:900;">{{ number_format((float) $lineTotalWithFin, 2) }}
	                            </td>
	                        </tr>
	                    @endforeach
	                </tbody>
            </table>
        </div>

        {{-- NOTES + TOTALS --}}
        <table class="mt-18" style="width:100%;">
            <tr>
                <td style="width:55%; padding-right:10px; vertical-align:top;">
                    @if ($estimate->notes_customer)
                        <div class="card no-break">
                            <div class="h2">Customer Notes</div>
                            <div class="muted" style="white-space: pre-wrap; line-height:1.55;">
                                {{ $estimate->notes_customer }}</div>
                        </div>
                    @endif

                    @if ($estimate->terms)
                        <div class="card mt-14 no-break">
                            <div class="h2">Terms & Conditions</div>
                            <div class="muted" style="white-space: pre-wrap; line-height:1.55;">{{ $estimate->terms }}
                            </div>
                        </div>
                    @endif
                </td>

                <td style="width:45%; padding-left:10px; vertical-align:top;">
                    <div class="card no-break">
                        <div class="h2">Totals</div>
                        <table class="totals">
                            <tr>
                                <td class="label">Subtotal</td>
                                <td class="value right">{{ $currency }}
                                    {{ number_format((float) $estimate->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="label">Discount</td>
                                <td class="value right">{{ $currency }}
                                    {{ number_format((float) $estimate->discount_total, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="label">Tax</td>
                                <td class="value right">{{ $currency }}
                                    {{ number_format((float) $estimate->tax_total, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="label">Shipping</td>
                                <td class="value right">{{ $currency }}
                                    {{ number_format((float) ($estimate->shipping_fee ?? 0), 2) }}</td>
                            </tr>
                            <tr>
                                <td class="label">Other</td>
                                <td class="value right">{{ $currency }}
                                    {{ number_format((float) ($estimate->other_fee ?? 0), 2) }}</td>
                            </tr>
                            <tr class="grand-row">
                                <td class="label grand">Grand Total</td>
                                <td class="value right grand">{{ $currency }}
                                    {{ number_format((float) $estimate->grand_total, 2) }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="mt-14 muted" style="font-size: 10px; line-height: 1.6;">
                        This quotation is generated by Printair. Prices and availability are subject to confirmation
                        until accepted.
                        If you have questions, WhatsApp us with the Estimate # <span class="brand"
                            style="font-weight:800;">{{ $no }}</span>.
                    </div>
                </td>
            </tr>
        </table>

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
