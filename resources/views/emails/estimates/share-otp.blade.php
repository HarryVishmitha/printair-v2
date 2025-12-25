<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: Arial, sans-serif; background:#f6f7fb; padding:24px;">
    @php
        $no = $estimate->estimate_no ?? ('EST-' . $estimate->id);
        $snap = is_array($estimate->customer_snapshot) ? $estimate->customer_snapshot : [];
        $customerName = $snap['full_name'] ?? $snap['name'] ?? 'Customer';
    @endphp

    <div style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;">
        <div style="background:#0f172a;color:#fff;padding:16px 20px;">
            <div style="font-weight:800;font-size:16px;">Printair — Verify to view estimate</div>
            <div style="opacity:.85;font-size:12px;margin-top:4px;">{{ $no }}</div>
        </div>

        <div style="padding:20px;">
            <p style="margin:0 0 10px; font-size:14px; color:#0f172a;">
                Hi {{ $customerName }},
            </p>

            <p style="margin:0 0 14px; font-size:14px; color:#0f172a; line-height:1.6;">
                Use the OTP below to verify and view/accept/reject your estimate.
            </p>

            <div style="margin:16px 0; padding:14px; border:1px solid #e5e7eb; border-radius:12px; background:#fafafa; text-align:center;">
                <div style="font-size:12px; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:.08em;">
                    One-time password (OTP)
                </div>
                <div style="margin-top:10px; font-size:28px; font-weight:900; letter-spacing:6px; color:#0f172a;">
                    {{ $code }}
                </div>
                <div style="margin-top:10px; font-size:12px; color:#64748b;">
                    Expires in {{ $expiresMinutes }} minutes
                </div>
            </div>

            <p style="margin:0; font-size:12px; color:#6b7280; line-height:1.6;">
                If you didn’t request this, you can ignore this email. Do not share this OTP with anyone.
            </p>
        </div>
    </div>
</body>
</html>

