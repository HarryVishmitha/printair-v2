@extends('emails.layout', [
    'emailTitle' => 'Verification Code – Printair',
    'emailHeader' => 'Verification Code',
    'emailSubheader' => 'One-time password for secure access'
])

@section('content')
    <div style="margin-bottom: 24px;">
        <p style="margin: 0 0 16px 0; font-size: 15px; color: #475569; line-height: 1.7;">
            Hello! Use the verification code below to proceed with your request.
        </p>
    </div>

    <div style="margin: 28px 0; padding: 32px 24px; background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%); border: 2px solid #ff2828; border-radius: 16px; text-align: center;">
        <div style="font-size: 12px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px;">
            Your Verification Code
        </div>
        <div style="font-size: 42px; font-weight: 900; letter-spacing: 8px; color: #ff2828; font-family: 'Courier New', monospace; text-shadow: 0 2px 4px rgba(239, 35, 60, 0.1);">
            {{ $otp }}
        </div>
        <div style="margin-top: 16px; font-size: 13px; color: #64748b; font-weight: 600;">
            ⏱️ Expires in <strong style="color: #ff2828;">{{ $expiresMinutes }} minutes</strong>
        </div>
    </div>

    <div style="margin-top: 24px; padding: 16px; background-color: #f1f5f9; border-radius: 10px; border-left: 4px solid #3b82f6;">
        <p style="margin: 0; font-size: 13px; color: #475569; line-height: 1.6;">
            <strong style="color: #0f172a;">🔒 Security Notice:</strong> If you didn't request this code, you can safely ignore this email. Never share your verification code with anyone.
        </p>
    </div>
@endsection

