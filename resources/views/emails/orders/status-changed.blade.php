@php
    $no = $order->order_no ?? ('ORD-' . $order->id);
    $snap = is_array($order->customer_snapshot) ? $order->customer_snapshot : [];
    $customerName = $snap['full_name'] ?? $snap['name'] ?? 'Customer';
    $fromLabel = ucwords(str_replace('_', ' ', (string) $fromStatus));
    $toLabel = ucwords(str_replace('_', ' ', (string) $toStatus));
@endphp

@extends('emails.layout', [
    'emailTitle' => 'Order ' . $no . ' – Printair',
    'emailHeader' => 'Order Status Updated',
    'emailSubheader' => 'Order No: ' . $no
])

@section('content')
    <div style="margin-bottom: 24px;">
        <p style="margin: 0 0 16px 0; font-size: 16px; color: #0f172a; font-weight: 600;">
            Hi {{ $customerName }},
        </p>
        <p style="margin: 0 0 16px 0; font-size: 15px; color: #475569; line-height: 1.7;">
            Your order status has been updated.
        </p>
    </div>

    <div style="margin: 24px 0; padding: 24px; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); border: 2px solid #e2e8f0; border-radius: 12px;">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td style="padding: 6px 0;">
                    <div style="font-size: 13px; color: #64748b; font-weight: 600;">Order Number</div>
                    <div style="font-size: 16px; color: #0f172a; font-weight: 800; margin-top: 2px;">{{ $no }}</div>
                </td>
            </tr>
            <tr>
                <td style="padding: 6px 0;">
                    <div style="font-size: 13px; color: #64748b; font-weight: 600;">Status</div>
                    <div style="font-size: 15px; color: #0f172a; font-weight: 700; margin-top: 2px;">
                        {{ $fromLabel }} → <span style="color:#ff2828;">{{ $toLabel }}</span>
                    </div>
                </td>
            </tr>
            @if (is_string($reason) && trim($reason) !== '')
                <tr>
                    <td style="padding: 6px 0;">
                        <div style="font-size: 13px; color: #64748b; font-weight: 600;">Note</div>
                        <div style="font-size: 14px; color: #0f172a; font-weight: 600; margin-top: 2px;">
                            {{ $reason }}
                        </div>
                    </td>
                </tr>
            @endif
        </table>
    </div>

    <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e2e8f0; font-size: 14px; color: #64748b; line-height: 1.6;">
        <p style="margin: 0;">
            If you have any questions, reply to this email and we’ll help you.
        </p>
        <p style="margin: 12px 0 0 0; font-weight: 600; color: #0f172a;">
            — The Printair Team
        </p>
    </div>
@endsection

