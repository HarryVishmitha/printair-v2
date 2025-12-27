@extends('emails.layout', [
    'emailTitle' => 'Order Submitted – Printair',
    'emailHeader' => 'Order Successfully Submitted',
    'emailSubheader' => 'Order ID: ' . $order->id
])

@section('content')
    <div style="margin-bottom: 24px;">
        <p style="margin: 0 0 16px 0; font-size: 16px; color: #0f172a; font-weight: 600;">
            Thank You!
        </p>
        <p style="margin: 0 0 16px 0; font-size: 15px; color: #475569; line-height: 1.7;">
            Your order has been successfully submitted to Printair. Our team will review it shortly and get back to you.
        </p>
    </div>

    <div style="margin: 24px 0; padding: 24px; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); border: 2px solid #e2e8f0; border-radius: 12px;">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td style="padding: 8px 0;">
                    <div style="font-size: 13px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Order ID</div>
                    <div style="font-size: 20px; color: #0f172a; font-weight: 800; margin-top: 4px;">#{{ $order->id }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin: 28px 0; text-align: center;">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center">
                    <a href="{{ $secureUrl }}" 
                       style="display: inline-block; background: linear-gradient(135deg, #ff2828 0%, #ef233c 100%); color: #ffffff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 14px 32px; border-radius: 50px; box-shadow: 0 4px 12px rgba(255, 40, 40, 0.3);">
                        🔒 View Order Securely
                    </a>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 24px; padding: 16px; background: linear-gradient(to right, #fff5f5, #ffffff); border-radius: 10px; border-left: 4px solid #ff2828;">
        <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 1.6;">
            <strong style="color: #ff2828;">🔐 Security Notice:</strong> This secure link is private and unique to your order. Please do not share it with others.
        </p>
    </div>

    <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #e2e8f0; font-size: 14px; color: #64748b; line-height: 1.6;">
        <p style="margin: 0 0 12px 0;">
            <strong style="color: #0f172a;">What happens next?</strong>
        </p>
        <ul style="margin: 0; padding-left: 20px; line-height: 1.8;">
            <li>Our team will review your order</li>
            <li>You'll receive updates via email</li>
            <li>Track your order status using the link above</li>
        </ul>
        <p style="margin: 16px 0 0 0; font-weight: 600; color: #0f172a;">
            — The Printair Team
        </p>
    </div>
@endsection
