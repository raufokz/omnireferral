<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your OmniReferral Portal Access Is Ready</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f0f4f8; margin: 0; padding: 0; color: #1e293b; }
        .email-wrap { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .email-header { background: linear-gradient(135deg, #0b3668 0%, #1d5fa0 100%); padding: 40px 32px; text-align: center; }
        .email-header h1 { color: #ffffff; font-size: 22px; font-weight: 700; margin: 0; letter-spacing: -0.3px; }
        .email-header p { color: rgba(255,255,255,0.8); font-size: 14px; margin: 6px 0 0; }
        .email-body { padding: 36px 32px; }
        .success-badge { display: inline-flex; align-items: center; gap: 8px; background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; font-size: 13px; font-weight: 600; padding: 8px 16px; border-radius: 999px; margin-bottom: 20px; }
        .email-body h2 { font-size: 20px; font-weight: 700; color: #0b3668; margin: 0 0 8px; }
        .email-body p { font-size: 15px; line-height: 1.65; color: #475569; margin: 0 0 16px; }
        .cta-section { text-align: center; margin: 28px 0; }
        .cta-button { display: inline-block; background: linear-gradient(135deg, #ff6b00, #e05500); color: #ffffff; font-size: 15px; font-weight: 700; padding: 14px 32px; border-radius: 8px; text-decoration: none; letter-spacing: 0.2px; }
        .link-fallback { font-size: 13px; color: #64748b; word-break: break-all; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; }
        .expiry-note { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 14px 18px; margin: 20px 0; }
        .expiry-note p { font-size: 13px; color: #92400e; margin: 0; line-height: 1.6; }
        .divider { border: none; border-top: 1px solid #e2e8f0; margin: 24px 0; }
        .email-footer { background: #f8fafc; padding: 24px 32px; text-align: center; }
        .email-footer p { font-size: 12px; color: #94a3b8; margin: 0 0 4px; line-height: 1.6; }
        .email-footer a { color: #0b3668; text-decoration: none; }
    </style>
</head>
<body>
<div class="email-wrap">

    <div class="email-header">
        <h1>OmniReferral Portal</h1>
        <p>Your portal access is ready</p>
    </div>

    <div class="email-body">
        <div class="success-badge">
            &#10003; Onboarding Complete
        </div>

        <h2>Hi {{ $userName }},</h2>
        <p>Your onboarding form has been completed successfully.</p>
        <p>Your OmniReferral portal access is ready.</p>
        <p>Please click the button below to set your password and access your account:</p>

        <div class="cta-section">
            <a href="{{ $setupUrl }}" class="cta-button">Set Your Password &rarr;</a>
        </div>

        <p style="font-size:13px; margin-bottom:8px;">If the button does not work, copy and paste this link into your browser:</p>
        <p class="link-fallback">{{ $setupUrl }}</p>

        <div class="expiry-note">
            <p><strong>Heads up:</strong> This link will expire in {{ $expiresHours }} hours and can only be used once.</p>
        </div>

        <hr class="divider">

        <p style="font-size:14px;">
            Login page: <a href="{{ $loginUrl }}" style="color:#0b3668; font-weight:600;">{{ $loginUrl }}</a>
        </p>

        <p style="font-size:14px;">
            Thank you,<br>
            OmniReferral Team
        </p>
    </div>

    <div class="email-footer">
        <p>This email was sent to <strong>{{ $userEmail }}</strong></p>
        <p>OmniReferral &mdash; Real Estate Referral &amp; Lead Management Platform</p>
        <p><a href="{{ $loginUrl }}">Login</a> &bull; <a href="mailto:{{ $supportEmail }}">Support</a></p>
    </div>

</div>
</body>
</html>
