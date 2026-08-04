<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>{{ $title ?? config('app.name') }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            background: #f6f9ff;
            font-family: 'Public Sans', Helvetica, Arial, sans-serif;
            color: #0b1220;
        }
        a { color: #2563eb; }
        .email-wrapper { width: 100%; background: #f6f9ff; }
        .email-body { width: 100%; padding: 40px 16px; }
        .email-card {
            max-width: 560px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.07);
        }
        .email-header {
            padding: 28px 32px 8px;
            text-align: center;
        }
        .brand-link {
            display: inline-block;
            text-decoration: none;
            color: #0b1220;
        }
        .brand-name {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.02em;
            vertical-align: middle;
            color: #0b1220;
        }
        .email-content {
            padding: 8px 32px 32px;
            text-align: left;
        }
        .email-title {
            margin: 0 0 12px;
            font-size: 22px;
            line-height: 1.3;
            font-weight: 700;
            color: #0b1220;
        }
        .email-text {
            margin: 0 0 16px;
            font-size: 15px;
            line-height: 1.6;
            color: #475569;
        }
        .btn-wrap { margin: 28px 0; text-align: center; }
        .btn-primary {
            display: inline-block;
            background: linear-gradient(135deg, #2563eb 0%, #06b6d4 100%);
            color: #ffffff !important;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            padding: 14px 28px;
            border-radius: 10px;
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.28);
        }
        .email-footer {
            padding: 0 32px 28px;
            text-align: center;
        }
        .email-footer-text {
            margin: 0;
            font-size: 12px;
            line-height: 1.5;
            color: #94a3b8;
        }
        .divider {
            height: 1px;
            background: rgba(15, 23, 42, 0.08);
            margin: 8px 32px 20px;
        }
        @media only screen and (max-width: 620px) {
            .email-content, .email-header, .email-footer { padding-left: 20px !important; padding-right: 20px !important; }
            .divider { margin-left: 20px !important; margin-right: 20px !important; }
        }
    </style>
</head>
<body>
    <table role="presentation" class="email-wrapper" cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr>
            <td class="email-body" align="center">
                <table role="presentation" class="email-card" cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tr>
                        <td class="email-header">
                            <a href="{{ url('/') }}" class="brand-link">
                                <img
                                    src="{{ asset('assets/img/logo/occ.png') }}"
                                    alt="{{ config('app.name') }}"
                                    width="36"
                                    height="36"
                                    style="vertical-align: middle; margin-right: 10px; border-radius: 8px;" />
                                <span class="brand-name">{{ config('app.name') }}</span>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><div class="divider"></div></td>
                    </tr>
                    <tr>
                        <td class="email-content">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td class="email-footer">
                            <p class="email-footer-text">
                                {{ config('app.name') }} &mdash; Automotive POS &amp; operations<br />
                                If you did not expect this email, you can safely ignore it.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
