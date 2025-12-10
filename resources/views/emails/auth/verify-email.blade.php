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
                                padding: 20px 0 10px 0;
                                text-align: center;
                            ">
                            <a href="{{ config('app.url') }}"
                                style="
                                    color: #0f172a;
                                    font-size: 18px;
                                    font-weight: 600;
                                    text-decoration: none;
                                    display: inline-block;
                                ">
                                <img src="{{ asset('assets/printair/printairlogo.png') }}" alt="Printair Advertising"
                                    style="height: 60px; border: none; display: block; margin: 0 auto 6px auto;" />
                                <div
                                    style="
                                        font-size: 11px;
                                        color: #64748b;
                                    ">
                                    Secure Login System - Printair.lk
                                </div>
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
                                        <h1
                                            style="
                                                font-size: 32px;
                                                margin: 0 0 12px 0;
                                                font-weight: 600;
                                                color: #0f172a;
                                                text-align: center;
                                            ">
                                            Verify your email address
                                        </h1>

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
                                                background-color:#dadada;
                                                padding: 10px;
                                                border-radius: 6px;
                                            ">
                                            {{ $url }}
                                        </p>

                                        <hr
                                            style="
                                                border: 0;
                                                border-top: 1px solid #e2e8f0;
                                                margin: 18px 0;
                                            " />

                                        <p
                                            style="
                                                margin: 0 0 14px 0;
                                                font-size: 13px;
                                                color: #64748b;
                                            ">
                                            If you did <strong>not</strong> create a Printair account, you can safely
                                            ignore this email and no changes will be made.
                                        </p>

                                        {{-- Signature --}}
                                        <p
                                            style="
                                                margin: 0 0 4px 0;
                                                font-size: 14px;
                                                color: #0f172a;
                                            ">
                                            Thanks,<br />
                                            <strong>Printair Advertising</strong>
                                        </p>

                                        <p
                                            style="
                                                margin: 4px 0 0 0;
                                                font-size: 11px;
                                                color: #94a3b8;
                                            ">
                                            printair.lk · Designs · Printing · Branding
                                        </p>
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
                                            padding: 24px 16px 8px 16px;
                                            font-size: 12px;
                                            color: #94a3b8;
                                        ">
                                        © {{ date('Y') }} Printair Advertising · printair.lk · You think it, we ink it.
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
