<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>Verify your email – Printair</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="color-scheme" content="light" />
    <meta name="supported-color-schemes" content="light" />
    <style type="text/css">
        @media only screen and (max-width: 640px) {
            .inner-body {
                width: 100% !important;
            }

            .footer {
                width: 100% !important;
            }

            .content-cell {
                padding: 24px !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }
    </style>
</head>

<body
    style="
        margin: 0;
        padding: 0;
        width: 100% !important;
        -webkit-text-size-adjust: none;
        background-color: #f8fafc;
        color: #0f172a;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
        line-height: 1.5;
    ">

    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation"
        style="
            width: 100%;
            background-color: #f8fafc;
            margin: 0;
            padding: 24px 0;
        ">
        <tr>
            <td align="center">
                <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation"
                    style="width: 100%; margin: 0; padding: 0;">

                    {{-- Header --}}
                    <tr>
                        <td class="header"
                            style="
                                padding: 32px 0 24px 0;
                                text-align: center;
                                background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                            ">
                            <a href="{{ config('app.url') }}"
                                style="
                                    color: #ffffff;
                                    font-size: 18px;
                                    font-weight: 600;
                                    text-decoration: none;
                                    display: inline-block;
                                ">
                                <img src="{{ asset('assets/printair/printairlogo.png') }}" alt="Printair Advertising"
                                    style="height: 56px; border: none; display: block; margin: 0 auto;" />
                            </a>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td class="body" width="100%" cellpadding="0" cellspacing="0" role="presentation"
                            style="background-color: #f8fafc; margin: 0; padding: 0;">
                            <table class="inner-body" align="center" width="640" cellpadding="0" cellspacing="0"
                                role="presentation"
                                style="
                                    width: 640px;
                                    margin: 0 auto;
                                    background-color: #ffffff;
                                    border-radius: 14px;
                                    border: 1px solid #e2e8f0;
                                    box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
                                ">

                                <tr>
                                    <td class="content-cell"
                                        style="
                                            padding: 32px 36px;
                                            font-size: 14px;
                                            color: #0f172a;
                                        ">

                                        {{-- Title --}}
                                        <div style="background-color: #ff2828; padding: 16px 24px; margin: -32px -36px 24px -36px; text-align: center;">
                                            <div style="font-size: 18px; font-weight: 800; color: #ffffff; margin: 0;">
                                                Verify Your Email Address
                                            </div>
                                            <div style="font-size: 13px; color: rgba(255, 255, 255, 0.9); margin-top: 4px;">
                                                Activate your Printair account
                                            </div>
                                        </div>

                                        {{-- Intro text --}}
                                        <p
                                            style="
                                                margin: 0 0 12px 0;
                                                font-size: 14px;
                                                color: #0f172a;
                                            ">
                                            Hi {{ $user->first_name ?? 'there' }},
                                        </p>

                                        <p
                                            style="
                                                margin: 0 0 16px 0;
                                                font-size: 14px;
                                                color: #0f172a;
                                            ">
                                            Thank you for creating an account with
                                            <strong>Printair Advertising</strong>.
                                        </p>

                                        <p
                                            style="
                                                margin: 0 0 20px 0;
                                                font-size: 14px;
                                                color: #0f172a;
                                            ">
                                            To activate your account and start managing your
                                            <strong>quotations, print jobs, and design files</strong>,
                                            please confirm your email address by clicking the button below.
                                        </p>

                                        {{-- Button --}}
                                        <table class="action" align="center" width="100%" cellpadding="0"
                                            cellspacing="0" role="presentation" style="margin: 16px 0 24px 0;">
                                            <tr>
                                                <td align="center">
                                                    <table width="100%" border="0" cellpadding="0" cellspacing="0"
                                                        role="presentation">
                                                        <tr>
                                                            <td align="center">
                                                                <table border="0" cellpadding="0" cellspacing="0"
                                                                    role="presentation">
                                                                    <tr>
                                                                        <td>
                                                                            <a href="{{ $url }}"
                                                                                target="_blank" rel="noopener"
                                                                                class="button button-primary"
                                                                                style="
                                                                                    -webkit-text-size-adjust: none;
                                                                                    border-radius: 9999px;
                                                                                    color: #ffffff;
                                                                                    display: inline-block;
                                                                                    text-decoration: none;
                                                                                    background-color: #ff2828;
                                                                                    border-top: 10px solid #ff2828;
                                                                                    border-bottom: 10px solid #ff2828;
                                                                                    border-left: 24px solid#ff2828;
                                                                                    border-right: 24px solid #ff2828;
                                                                                    font-weight: 600;
                                                                                ">
                                                                                Verify my email
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>

                                        {{-- Fallback link --}}
                                        <p
                                            style="
                                                margin: 0 0 8px 0;
                                                font-size: 13px;
                                                color: #475569;
                                            ">
                                            If the button doesn’t work, copy and paste this link into your browser:
                                        </p>

                                        <p
                                            style="
                                                margin: 0 0 20px 0;
                                                font-size: 12px;
                                                color: #475569;
                                                word-break: break-all;
                                                background-color:#f1f5f9;
                                                padding: 12px;
                                                border-radius: 10px;
                                                border-left: 4px solid #ff2828;
                                            ">
                                            {{ $url }}
                                        </p>

                                        <hr
                                            style="
                                                border: 0;
                                                border-top: 1px solid #e2e8f0;
                                                margin: 18px 0;
                                            " />

                                        <div
                                            style="
                                                margin: 0 0 20px 0;
                                                padding: 16px;
                                                background: linear-gradient(to right, #fff5f5, #ffffff);
                                                border-radius: 10px;
                                                border-left: 4px solid #3b82f6;
                                            ">
                                            <p style="margin: 0; font-size: 13px; color: #475569; line-height: 1.6;">
                                                <strong style="color: #0f172a;">🔒 Security Notice:</strong> If you did <strong>not</strong> create a Printair account, you can safely ignore this email and no changes will be made.
                                            </p>
                                        </div>

                                        {{-- Signature --}}
                                        <div style="padding-top: 20px; border-top: 1px solid #e2e8f0;">
                                            <p
                                                style="
                                                    margin: 0 0 4px 0;
                                                    font-size: 14px;
                                                    color: #64748b;
                                                ">
                                                Thank you,
                                            </p>
                                            <p
                                                style="
                                                    margin: 0 0 8px 0;
                                                    font-size: 16px;
                                                    font-weight: 700;
                                                    color: #0f172a;
                                                ">
                                                The Printair Team
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td>
                            <table class="footer" align="center" width="640" cellpadding="0" cellspacing="0"
                                role="presentation"
                                style="
                                    width: 640px;
                                    margin: 0 auto;
                                    text-align: center;
                                ">
                                <tr>
                                    <td class="content-cell"
                                        style="
                                            padding: 24px 16px;
                                            font-size: 12px;
                                            color: #94a3b8;
                                            background-color: #f8fafc;
                                            border-top: 1px solid #e2e8f0;
                                        ">
                                        <div style="margin-bottom: 8px;">
                                            <strong style="color: #0f172a;">Printair Advertising</strong><br>
                                            Your trusted partner for printing solutions
                                        </div>
                                        <div style="font-size: 11px;">
                                            Email: <a href="mailto:contact@printair.lk" style="color: #ff2828; text-decoration: none;">contact@printair.lk</a> · 
                                            Web: <a href="https://printair.lk" style="color: #ff2828; text-decoration: none;">printair.lk</a>
                                        </div>
                                        <div style="margin-top: 12px; font-size: 11px;">
                                            © {{ date('Y') }} Printair Advertising. All rights reserved.
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>

</html>
