@php
    $no = $estimate->estimate_no ?? ('EST-' . $estimate->id);
    $snap = is_array($estimate->customer_snapshot) ? $estimate->customer_snapshot : [];
    $customerName = $snap['full_name'] ?? $snap['name'] ?? 'Customer';
@endphp

@extends('emails.layout', [
    'emailTitle' => 'Verify to View Estimate – ' . $no,
    'emailHeader' => 'Estimate Verification Required',
    'emailSubheader' => 'Estimate No: ' . $no
])

@section('content')
    <div style="margin-bottom: 24px;">
        <p style="margin: 0 0 16px 0; font-size: 16px; color: #0f172a; font-weight: 600;">
            Hi {{ $customerName }},
        </p>
        <p style="margin: 0 0 16px 0; font-size: 15px; color: #475569; line-height: 1.7;">
            Use the one-time password (OTP) below to verify and view, accept, or reject your estimate.
        </p>
    </div>

    <div style="margin: 28px 0; padding: 32px 24px; background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%); border: 2px solid #ff2828; border-radius: 16px; text-align: center;">
        <div style="font-size: 12px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px;">
            Your One-Time Password (OTP)
        </div>
        <div style="font-size: 42px; font-weight: 900; letter-spacing: 8px; color: #ff2828; font-family: 'Courier New', monospace; text-shadow: 0 2px 4px rgba(239, 35, 60, 0.1);">
            {{ $code }}
        </div>
        <div style="margin-top: 16px; font-size: 13px; color: #64748b; font-weight: 600;">
            ⏱️ Expires in <strong style="color: #ff2828;">{{ $expiresMinutes }} minutes</strong>
        </div>
    </div>

    <div style="margin: 24px 0; padding: 24px; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); border: 2px solid #e2e8f0; border-radius: 12px;">
        <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 12px;">
            📋 Estimate Details
        </div>
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td style="padding: 4px 0;">
                    <div style="font-size: 13px; color: #64748b; font-weight: 600;">Estimate Number</div>
                    <div style="font-size: 16px; color: #0f172a; font-weight: 700; margin-top: 2px;">{{ $no }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 24px; padding: 16px; background: linear-gradient(to right, #fff5f5, #ffffff); border-radius: 10px; border-left: 4px solid #3b82f6;">
        <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 1.6;">
            <strong style="color: #0f172a;">🔒 Security Notice:</strong> If you didn't request this verification code, you can safely ignore this email. Never share your OTP with anyone.
        </p>
    </div>

    <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #e2e8f0; font-size: 14px; color: #64748b; line-height: 1.6;">
        <p style="margin: 0;">
            If you need assistance, feel free to reply to this email.
        </p>
        <p style="margin: 12px 0 0 0; font-weight: 600; color: #0f172a;">
            — The Printair Team
        </p>
    </div>
@endsection
