@php
    $no = $estimate->estimate_no ?? ('EST-' . $estimate->id);
    $currency = $estimate->currency ?? 'LKR';
    $snap = is_array($estimate->customer_snapshot) ? $estimate->customer_snapshot : [];
    $customerName = $snap['full_name'] ?? $snap['name'] ?? 'Customer';
@endphp

@extends('emails.layout', [
    'emailTitle' => 'Estimate ' . $no . ' – Printair',
    'emailHeader' => 'Your Estimate is Ready',
    'emailSubheader' => 'Estimate No: ' . $no
])

@section('content')
    <div style="margin-bottom: 24px;">
        <p style="margin: 0 0 16px 0; font-size: 16px; color: #0f172a; font-weight: 600;">
            Hi {{ $customerName }},
        </p>
        <p style="margin: 0 0 16px 0; font-size: 15px; color: #475569; line-height: 1.7;">
            Thank you for your interest! Please find your estimate attached as a PDF. You can also view it online using the secure link below.
        </p>
    </div>

    <div style="margin: 24px 0; padding: 24px; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); border: 2px solid #e2e8f0; border-radius: 12px;">
        <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.5px;">
            Estimate Summary
        </div>
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td style="padding: 6px 0;">
                    <div style="font-size: 13px; color: #64748b; font-weight: 600;">Estimate Number</div>
                    <div style="font-size: 16px; color: #0f172a; font-weight: 700; margin-top: 2px;">{{ $no }}</div>
                </td>
            </tr>
            <tr>
                <td style="padding: 6px 0;">
                    <div style="font-size: 13px; color: #64748b; font-weight: 600;">Total Amount</div>
                    <div style="font-size: 20px; color: #ff2828; font-weight: 800; margin-top: 2px;">{{ $currency }} {{ number_format((float) $estimate->grand_total, 2) }}</div>
                </td>
            </tr>
            @if ($estimate->valid_until)
            <tr>
                <td style="padding: 6px 0;">
                    <div style="font-size: 13px; color: #64748b; font-weight: 600;">Valid Until</div>
                    <div style="font-size: 15px; color: #0f172a; font-weight: 600; margin-top: 2px;">{{ \Carbon\Carbon::parse($estimate->valid_until)->format('F d, Y') }}</div>
                </td>
            </tr>
            @endif
        </table>
    </div>

    <div style="margin: 28px 0; text-align: center;">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center">
                    <a href="{{ $publicUrl }}" 
                       target="_blank" 
                       rel="noopener"
                       style="display: inline-block; background: linear-gradient(135deg, #ff2828 0%, #ef233c 100%); color: #ffffff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 14px 32px; border-radius: 50px; box-shadow: 0 4px 12px rgba(255, 40, 40, 0.3);">
                        📋 View Estimate Online
                    </a>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin: 24px 0; padding: 16px; background-color: #f1f5f9; border-radius: 10px;">
        <p style="margin: 0 0 8px 0; font-size: 13px; color: #64748b; font-weight: 600;">
            If the button doesn't work, copy this link:
        </p>
        <p style="margin: 0; font-size: 12px;">
            <a href="{{ $publicUrl }}" style="color: #ff2828; text-decoration: none; word-break: break-all; font-weight: 600;">
                {{ $publicUrl }}
            </a>
        </p>
    </div>

    <div style="margin-top: 24px; padding: 16px; background: linear-gradient(to right, #fff5f5, #ffffff); border-radius: 10px; border-left: 4px solid #3b82f6;">
        <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 1.6;">
            <strong style="color: #0f172a;">💡 Note:</strong> This link is unique and may expire. If you need any changes to your estimate, reply to this email or contact our support team.
        </p>
    </div>

    <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #e2e8f0; font-size: 14px; color: #64748b; line-height: 1.6;">
        <p style="margin: 0;">
            We look forward to working with you!
        </p>
        <p style="margin: 12px 0 0 0; font-weight: 600; color: #0f172a;">
            — The Printair Team
        </p>
    </div>
@endsection
