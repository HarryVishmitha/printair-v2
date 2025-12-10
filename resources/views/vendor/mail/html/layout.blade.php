<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>{{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">

    <style type="text/css">
        /* Mobile tweaks */
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

    {!! $head ?? '' !!}
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
                    style="width: 100%;">
                    {{-- Header --}}
                    {!! $header ?? '' !!}

                    {{-- Email Body --}}
                    <tr>
                        <td class="body" width="100%" cellpadding="0" cellspacing="0" role="presentation"
                            style="
                                width: 100%;
                                border: none !important;
                            ">
                            <table class="inner-body" align="center" width="640" cellpadding="0" cellspacing="0"
                                role="presentation"
                                style="
                                    width: 640px;
                                    margin: 0 auto;
                                    background-color: #ffffff;
                                    border-radius: 12px;
                                    border: 1px solid #e2e8f0;
                                    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
                                ">
                                {{-- Body content --}}
                                <tr>
                                    <td class="content-cell"
                                        style="
                                            padding: 32px 36px;
                                            font-size: 14px;
                                            line-height: 1.6;
                                            color: #0f172a;
                                        ">
                                        {!! Illuminate\Mail\Markdown::parse($slot) !!}

                                        {{-- Subcopy (e.g., 'If you're having trouble…') --}}
                                        @if (!empty($subcopy ?? ''))
                                            <div style="margin-top: 24px; font-size: 12px; color: #64748b;">
                                                {!! $subcopy !!}
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    {!! $footer ?? '' !!}
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
