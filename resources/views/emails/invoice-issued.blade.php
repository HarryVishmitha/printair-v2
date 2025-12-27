@php
    $invoiceNo = $invoice->invoice_no ?? ('INV-' . $invoice->id);
@endphp

@extends('emails.layout', [
    'emailTitle' => 'Invoice Issued – ' . $invoiceNo,
    'emailHeader' => 'Your Invoice is Ready',
    'emailSubheader' => 'Invoice No: ' . $invoiceNo
])

@section('content')
    <div style="margin-bottom: 24px;">
        <p style="margin: 0 0 16px 0; font-size: 16px; color: #0f172a; font-weight: 600;">
            Hello!
        </p>
        <p style="margin: 0 0 16px 0; font-size: 15px; color: #475569; line-height: 1.7;">
            Your invoice has been successfully issued. Please find the PDF attached to this email for your records.
        </p>
    </div>

    <div style="margin: 24px 0; padding: 24px; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); border: 2px solid #e2e8f0; border-radius: 12px;">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td style="padding: 8px 0;">
                    <div style="font-size: 13px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Invoice Number</div>
                    <div style="font-size: 18px; color: #0f172a; font-weight: 800; margin-top: 4px;">{{ $invoiceNo }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin: 28px 0; text-align: center;">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center">
                    <a href="{{ $invoiceUrl }}" 
                       style="display: inline-block; background: linear-gradient(135deg, #ff2828 0%, #ef233c 100%); color: #ffffff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 14px 32px; border-radius: 50px; box-shadow: 0 4px 12px rgba(255, 40, 40, 0.3);">
                        📄 View Invoice Online
                    </a>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin: 24px 0; padding: 16px; background-color: #f1f5f9; border-radius: 10px;">
        <p style="margin: 0 0 8px 0; font-size: 13px; color: #64748b; font-weight: 600;">
            Direct Download Link:
        </p>
        <p style="margin: 0; font-size: 13px;">
            <a href="{{ $pdfUrl }}" style="color: #ff2828; text-decoration: none; word-break: break-all; font-weight: 600;">
                {{ $pdfUrl }}
            </a>
        </p>
    </div>

    <div style="margin-top: 24px; padding: 16px; background: linear-gradient(to right, #fff5f5, #ffffff); border-radius: 10px; border-left: 4px solid #ff2828;">
        <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 1.6;">
            <strong style="color: #ff2828;">⚠️ Important:</strong> The secure links above will expire in <strong>7 minutes</strong> for security purposes. Please download or view your invoice promptly.
        </p>
    </div>

    <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #e2e8f0; font-size: 14px; color: #64748b; line-height: 1.6;">
        <p style="margin: 0;">
            If you have any questions or need assistance, feel free to reply to this email.
        </p>
        <p style="margin: 12px 0 0 0; font-weight: 600; color: #0f172a;">
            — The Printair Team
        </p>
    </div>
@endsection
