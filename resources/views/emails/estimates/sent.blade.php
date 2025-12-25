<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: Arial, sans-serif; background:#f6f7fb; padding:24px;">
    @php
        $no = $estimate->estimate_no ?? ('EST-' . $estimate->id);
        $currency = $estimate->currency ?? 'LKR';
        $snap = is_array($estimate->customer_snapshot) ? $estimate->customer_snapshot : [];
        $customerName = $snap['full_name'] ?? $snap['name'] ?? 'Customer';
    @endphp

    <div style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;">
        <div style="background:#0f172a;color:#fff;padding:16px 20px;">
            <div style="font-weight:800;font-size:16px;">Printair — Estimate</div>
            <div style="opacity:.85;font-size:12px;margin-top:4px;">{{ $no }}</div>
        </div>

        <div style="padding:20px;">
            <p style="margin:0 0 12px; font-size:14px; color:#0f172a;">
                Hi {{ $customerName }},
            </p>

            <p style="margin:0 0 14px; font-size:14px; color:#0f172a; line-height:1.6;">
                Please find your estimate attached as a PDF. You can also view it online using the secure link below.
            </p>

            <div style="margin:16px 0; padding:14px; border:1px solid #e5e7eb; border-radius:12px; background:#fafafa;">
                <div style="font-weight:700; margin-bottom:8px; color:#0f172a;">Estimate Summary</div>
                <div style="font-size:13px; color:#111827; line-height:1.6;">
                    <div><strong>Estimate:</strong> {{ $no }}</div>
                    <div><strong>Total:</strong> {{ $currency }} {{ number_format((float) $estimate->grand_total, 2) }}</div>
                    @if ($estimate->valid_until)
                        <div><strong>Valid until:</strong> {{ \Carbon\Carbon::parse($estimate->valid_until)->format('Y-m-d') }}</div>
                    @endif
                </div>
            </div>

            <div style="margin:18px 0;">
                <a href="{{ $publicUrl }}" target="_blank" rel="noopener"
                    style="display:inline-block; background:#ff2828; color:#ffffff; text-decoration:none; font-weight:700; padding:12px 18px; border-radius:9999px;">
                    View Estimate Online
                </a>
            </div>

            <p style="margin:0 0 8px 0; font-size:12px; color:#6b7280;">
                If the button doesn’t work, copy and paste this link into your browser:
            </p>
            <p style="margin:0; font-size:12px; color:#475569; word-break:break-all; background:#f1f5f9; padding:10px; border-radius:10px;">
                {{ $publicUrl }}
            </p>

            <p style="margin-top:18px; font-size:12px; color:#6b7280; line-height:1.6;">
                This link is unique and may expire. If you need any changes, reply to this email or contact Printair support.
            </p>
        </div>
    </div>
</body>
</html>

