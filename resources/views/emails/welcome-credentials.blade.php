<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>Welcome to OmniReferral</title>
    <!--[if mso]><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml><![endif]-->
    <style>
        body { margin: 0; padding: 0; background-color: #f4f6f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
        .email-wrapper { width: 100%; background-color: #f4f6f9; padding: 40px 0; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .email-header { background: linear-gradient(135deg, #0B3668 0%, #1a5296 100%); padding: 36px 40px; text-align: center; }
        .email-header img { height: 36px; margin-bottom: 12px; }
        .email-header h1 { color: #ffffff; font-size: 22px; font-weight: 700; margin: 0 0 6px 0; line-height: 1.3; }
        .email-header p { color: rgba(255,255,255,0.85); font-size: 14px; margin: 0; line-height: 1.5; }
        .email-body { padding: 36px 40px; }
        .email-body p { color: #374151; font-size: 15px; line-height: 1.65; margin: 0 0 16px 0; }
        .credentials-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; margin: 24px 0; }
        .credentials-box h3 { color: #0B3668; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 16px 0; font-weight: 600; }
        .credential-row { display: flex; margin-bottom: 12px; }
        .credential-row:last-child { margin-bottom: 0; }
        .credential-label { color: #6b7280; font-size: 13px; font-weight: 500; min-width: 100px; padding-right: 12px; }
        .credential-value { color: #111827; font-size: 14px; font-weight: 600; word-break: break-all; }
        .credential-value.password { font-family: 'SF Mono', 'Fira Code', 'Courier New', monospace; background-color: #fef3c7; padding: 3px 8px; border-radius: 4px; color: #92400e; letter-spacing: 0.03em; }
        .cta-section { text-align: center; margin: 28px 0; }
        .cta-button { display: inline-block; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #ffffff !important; font-size: 15px; font-weight: 600; text-decoration: none; padding: 14px 36px; border-radius: 8px; letter-spacing: 0.01em; }
        .steps-list { margin: 20px 0; padding: 0; list-style: none; }
        .steps-list li { color: #374151; font-size: 14px; line-height: 1.6; padding: 8px 0 8px 32px; position: relative; border-bottom: 1px solid #f1f5f9; }
        .steps-list li:last-child { border-bottom: none; }
        .steps-list li::before { content: attr(data-step); position: absolute; left: 0; top: 8px; width: 22px; height: 22px; background-color: #0B3668; color: #ffffff; font-size: 12px; font-weight: 700; text-align: center; line-height: 22px; border-radius: 50%; }
        .security-note { background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 16px 20px; margin: 24px 0; }
        .security-note p { color: #991b1b; font-size: 13px; margin: 0; line-height: 1.55; }
        .security-note strong { color: #7f1d1d; }
        .email-footer { background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 24px 40px; text-align: center; }
        .email-footer p { color: #9ca3af; font-size: 12px; margin: 0 0 6px 0; line-height: 1.5; }
        .email-footer a { color: #0B3668; text-decoration: none; }

        @media only screen and (max-width: 620px) {
            .email-wrapper { padding: 16px 12px; }
            .email-header, .email-body, .email-footer { padding-left: 24px; padding-right: 24px; }
            .credentials-box { padding: 16px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr>
                <td align="center">
                    <div class="email-container">

                        <div class="email-header">
                            <h1>Welcome to OmniReferral</h1>
                            <p>Your account has been created and is ready to use</p>
                        </div>

                        <div class="email-body">
                            <p>Hi {{ $userName ?: 'there' }},</p>

                            <p>
                                Great news — your OmniReferral account has been successfully created
                                @if($planName) for the <strong>{{ $planName }}</strong> plan@endif.
                                Below you will find your login credentials and instructions to access your dashboard.
                            </p>

                            <div class="cta-section">
                                <a href="{{ $loginUrl }}" class="cta-button" target="_blank">Set Your Password &amp; Sign In &rarr;</a>
                            </div>

                            <p style="font-size:13px; text-align:center; margin-top:-12px; margin-bottom:24px;">
                                <a href="{{ $loginUrl }}" style="color:#0B3668;">{{ $loginUrl }}</a>
                            </p>

                            <p style="font-weight:600; color:#0B3668; margin-bottom:8px;">Getting Started:</p>
                            <ol class="steps-list">
                                <li data-step="1">Click the button above to set up your secure password</li>
                                <li data-step="2">Log in with your email and new password</li>
                                <li data-step="3">Explore your dashboard — leads, listings, and tools are ready</li>
                            </ol>

                            <p>If you have any questions or need help getting started, reply to this email or reach out to <a href="mailto:{{ $supportEmail }}" style="color:#0B3668;">{{ $supportEmail }}</a>.</p>

                            <p style="margin-bottom:0;">
                                Welcome aboard,<br>
                                <strong>The OmniReferral Team</strong>
                            </p>
                        </div>

                        <div class="email-footer">
                            <p>&copy; {{ date('Y') }} OmniReferral. All rights reserved.</p>
                            <p>
                                <a href="{{ url('/') }}">Website</a> &nbsp;&middot;&nbsp;
                                <a href="{{ url('/pricing') }}">Pricing</a> &nbsp;&middot;&nbsp;
                                <a href="{{ url('/contact') }}">Support</a>
                            </p>
                        </div>

                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
