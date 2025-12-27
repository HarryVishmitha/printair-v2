<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ $emailTitle ?? 'Printair' }}</title>
    <style type="text/css">
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        
        body {
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
        }
        
        @media only screen and (max-width: 640px) {
            .email-container {
                width: 100% !important;
                border-radius: 0 !important;
            }
            .content-padding {
                padding: 24px 16px !important;
            }
            .button {
                width: 100% !important;
                display: block !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #f8fafc; padding: 32px 16px;">
        <tr>
            <td align="center">
                <!-- Email Container -->
                <table class="email-container" width="640" cellpadding="0" cellspacing="0" role="presentation" 
                    style="width: 100%; max-width: 640px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(15, 23, 42, 0.08); border: 1px solid #e2e8f0;">
                    
                    <!-- Logo Header -->
                    <tr>
                        <td align="center" style="padding: 32px 24px 24px 24px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                            <a href="{{ config('app.url', 'https://printair.lk') }}" style="text-decoration: none; display: inline-block;">
                                <img src="{{ asset('assets/printair/printairlogo.png') }}" 
                                     alt="Printair Advertising" 
                                     style="height: 56px; width: auto; display: block; border: none;" />
                            </a>
                        </td>
                    </tr>
                    
                    @if(isset($emailHeader))
                    <!-- Email Header Bar -->
                    <tr>
                        <td style="background-color: #ff2828; padding: 16px 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td>
                                        <div style="font-size: 18px; font-weight: 800; color: #ffffff; margin: 0;">
                                            {{ $emailHeader }}
                                        </div>
                                        @if(isset($emailSubheader))
                                        <div style="font-size: 13px; color: rgba(255, 255, 255, 0.9); margin-top: 4px;">
                                            {{ $emailSubheader }}
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif
                    
                    <!-- Main Content -->
                    <tr>
                        <td class="content-padding" style="padding: 32px 28px; color: #0f172a; font-size: 15px; line-height: 1.6;">
                            @yield('content')
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 28px; background-color: #f8fafc; border-top: 1px solid #e2e8f0;">
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td align="center">
                                        <div style="font-size: 13px; color: #64748b; line-height: 1.6; margin-bottom: 12px;">
                                            <strong style="color: #0f172a; font-weight: 700;">Printair Advertising</strong><br>
                                            Your trusted partner for printing solutions
                                        </div>
                                        <div style="font-size: 12px; color: #94a3b8; line-height: 1.5;">
                                            Email: <a href="mailto:contact@printair.lk" style="color: #ff2828; text-decoration: none;">contact@printair.lk</a> &nbsp;|&nbsp; 
                                            Web: <a href="https://printair.lk" style="color: #ff2828; text-decoration: none;">printair.lk</a>
                                        </div>
                                        <div style="margin-top: 16px; font-size: 11px; color: #94a3b8;">
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
