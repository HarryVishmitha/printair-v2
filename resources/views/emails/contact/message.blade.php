@extends('emails.layout', [
    'emailTitle' => 'New Contact Message – Printair',
    'emailHeader' => 'New Contact Message Received',
    'emailSubheader' => 'From contact form'
])

@section('content')
    <div style="margin-bottom: 24px;">
        <div style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 16px;">
            Contact Information
        </div>
        
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 20px;">
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">
                    <strong style="color: #64748b; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Name</strong><br>
                    <span style="color: #0f172a; font-size: 15px; font-weight: 600;">{{ $p['name'] ?? '—' }}</span>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">
                    <strong style="color: #64748b; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Email</strong><br>
                    <span style="color: #0f172a; font-size: 15px; font-weight: 600;">{{ $p['email'] ?? '—' }}</span>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">
                    <strong style="color: #64748b; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Phone</strong><br>
                    <span style="color: #0f172a; font-size: 15px; font-weight: 600;">{{ $p['phone'] ?? '—' }}</span>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0;">
                    <strong style="color: #64748b; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Subject</strong><br>
                    <span style="color: #0f172a; font-size: 15px; font-weight: 600;">{{ $p['subject'] ?? '—' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 24px; padding: 20px; border: 2px solid #ff2828; border-radius: 12px; background: linear-gradient(to bottom, #fff5f5, #ffffff);">
        <div style="font-weight: 700; margin-bottom: 12px; color: #ff2828; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">
            Message
        </div>
        <div style="white-space: pre-wrap; line-height: 1.7; color: #0f172a; font-size: 15px;">{{ $p['message'] ?? '' }}</div>
    </div>

    <div style="margin-top: 24px; padding: 16px; background-color: #f1f5f9; border-radius: 10px;">
        <div style="font-size: 12px; color: #64748b; font-weight: 600; margin-bottom: 8px;">Technical Details</div>
        <div style="font-size: 12px; color: #475569; line-height: 1.6;">
            <strong>IP:</strong> {{ $p['ip'] ?? '—' }}<br>
            <strong>User Agent:</strong> {{ $p['ua'] ?? '—' }}
        </div>
    </div>
@endsection

