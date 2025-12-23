<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: Arial, sans-serif; background:#f6f7fb; padding:24px;">
    <div style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;">
        <div style="background:#0f172a;color:#fff;padding:16px 20px;">
            <div style="font-weight:800;font-size:16px;">New Contact Message — Printair</div>
            <div style="opacity:.85;font-size:12px;margin-top:4px;">Received via /contact</div>
        </div>

        <div style="padding:20px;">
            <p style="margin:0 0 10px;"><strong>Name:</strong> {{ $payload['name'] ?? '—' }}</p>
            <p style="margin:0 0 10px;"><strong>Email:</strong> {{ $payload['email'] ?? '—' }}</p>
            <p style="margin:0 0 10px;"><strong>Phone:</strong> {{ ! empty($payload['phone']) ? $payload['phone'] : '—' }}</p>
            <p style="margin:0 0 10px;"><strong>Subject:</strong> {{ $payload['subject'] ?? '—' }}</p>

            <div style="margin-top:16px;padding:14px;border:1px solid #e5e7eb;border-radius:12px;background:#fafafa;">
                <div style="font-weight:700;margin-bottom:8px;">Message</div>
                <div style="white-space:pre-wrap;line-height:1.6;">{{ $payload['message'] ?? '' }}</div>
            </div>

            <p style="margin-top:16px;font-size:12px;color:#6b7280;">
                Tip: Reply directly to this email to respond to the sender (Reply-To is set).
            </p>
        </div>
    </div>
</body>
</html>
