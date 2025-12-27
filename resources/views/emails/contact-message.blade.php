@extends('emails.layout', [
    'emailTitle' => 'New Contact Message – Printair',
    'emailHeader' => 'New Contact Message',
    'emailSubheader' => 'Received via contact form'
])

@section('content')
    <div style="margin-bottom: 24px;">
        <div style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 16px;">
            Contact Details
        </div>
        
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 20px;">
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">
                    <strong style="color: #64748b; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Name</strong><br>
                    <span style="color: #0f172a; font-size: 15px; font-weight: 600;">{{ $payload['name'] ?? '—' }}</span>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">
                    <strong style="color: #64748b; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Email</strong><br>
                    <span style="color: #0f172a; font-size: 15px; font-weight: 600;">{{ $payload['email'] ?? '—' }}</span>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">
                    <strong style="color: #64748b; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Phone</strong><br>
                    <span style="color: #0f172a; font-size: 15px; font-weight: 600;">{{ !empty($payload['phone']) ? $payload['phone'] : '—' }}</span>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0;">
                    <strong style="color: #64748b; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Subject</strong><br>
                    <span style="color: #0f172a; font-size: 15px; font-weight: 600;">{{ $payload['subject'] ?? '—' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 24px; padding: 20px; border: 2px solid #ff2828; border-radius: 12px; background: linear-gradient(to bottom, #fff5f5, #ffffff);">
        <div style="font-weight: 700; margin-bottom: 12px; color: #ff2828; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">
            Message
        </div>
        <div style="white-space: pre-wrap; line-height: 1.7; color: #0f172a; font-size: 15px;">{{ $payload['message'] ?? '' }}</div>
    </div>

    <div style="margin-top: 24px; padding: 16px; background-color: #f1f5f9; border-radius: 10px; border-left: 4px solid #ff2828;">
        <p style="margin: 0; font-size: 13px; color: #475569; line-height: 1.6;">
            <strong style="color: #0f172a;">💡 Quick Tip:</strong> Reply directly to this email to respond to <strong>{{ $payload['name'] ?? 'the sender' }}</strong> (Reply-To is configured).
        </p>
    </div>
@endsection
