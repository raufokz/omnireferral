<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your OmniReferral Portal Login Access</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f0f4f8; margin: 0; padding: 0; color: #1e293b; }
        .email-wrap { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .email-header { background: linear-gradient(135deg, #0b3668 0%, #1d5fa0 100%); padding: 40px 32px; text-align: center; }
        .email-header img { height: 36px; margin-bottom: 16px; }
        .email-header h1 { color: #ffffff; font-size: 22px; font-weight: 700; margin: 0; letter-spacing: -0.3px; }
        .email-header p { color: rgba(255,255,255,0.8); font-size: 14px; margin: 6px 0 0; }
        .email-body { padding: 36px 32px; }
        .success-badge { display: inline-flex; align-items: center; gap: 8px; background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; font-size: 13px; font-weight: 600; padding: 8px 16px; border-radius: 999px; margin-bottom: 20px; }
        .email-body h2 { font-size: 20px; font-weight: 700; color: #0b3668; margin: 0 0 8px; }
        .email-body p { font-size: 15px; line-height: 1.65; color: #475569; margin: 0 0 16px; }
        .credentials-box { background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 10px; padding: 20px 24px; margin: 20px 0; }
        .credentials-box .cred-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
        .credentials-box .cred-row:last-child { border-bottom: none; }
        .credentials-box .cred-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; min-width: 100px; padding-top: 2px; }
        .credentials-box .cred-value { font-size: 14px; font-weight: 600; color: #0f172a; font-family: 'Courier New', monospace; word-break: break-all; text-align: right; }
        .cta-section { text-align: center; margin: 28px 0; }
        .cta-button { display: inline-block; background: linear-gradient(135deg, #ff6b00, #e05500); color: #ffffff; font-size: 15px; font-weight: 700; padding: 14px 32px; border-radius: 8px; text-decoration: none; letter-spacing: 0.2px; }
        .instructions-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 16px 20px; margin: 20px 0; }
        .instructions-box p { font-size: 13px; color: #92400e; margin: 0; line-height: 1.6; }
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
        <p>Your access credentials are ready</p>
    </div>

    <div class="email-body">
        <div class="success-badge">
            &#10003; Onboarding Complete
        </div>

        <h2>Hello, {{ $userName }}!</h2>
        <p>
            Your onboarding is complete. You can now login to your portal and access your
            {{ ucfirst($userRole ?? 'agent') }} dashboard.
            @if($planName)
                Your <strong>{{ $planName }}</strong> plan is now active.
            @endif
        </p>

        <div class="credentials-box">
            <div class="cred-row">
                <span class="cred-label">Login Email</span>
                <span class="cred-value">{{ $userEmail }}</span>
            </div>
            <div class="cred-row">
                <span class="cred-label">Temp Password</span>
                <span class="cred-value">{{ $temporaryPassword }}</span>
            </div>
            <div class="cred-row">
                <span class="cred-label">Dashboard</span>
                <span class="cred-value">{{ $dashboardPath }}</span>
            </div>
        </div>

        <div class="cta-section">
            <a href="{{ $loginUrl }}" class="cta-button">Login to Your Portal &rarr;</a>
        </div>

        <div class="instructions-box">
            <p>
                <strong>Important:</strong> This is a temporary password. You will be prompted to choose
                a new password immediately after your first login. Please do not share this email.
            </p>
        </div>

        <hr class="divider">

        <h2 style="font-size:16px; color:#475569; font-weight:600;">Your Next Steps</h2>
        <p style="font-size:14px;">
            1. Click the login button above or go to <a href="{{ $loginUrl }}" style="color:#0b3668; font-weight:600;">{{ $loginUrl }}</a><br>
            2. Enter your email and the temporary password above<br>
            3. Set a new personal password when prompted<br>
            4. Complete your profile to activate full portal access
        </p>

        <p style="font-size:14px; margin-top:16px;">
            If you need help, reply to this email or contact us at
            <a href="mailto:{{ $supportEmail }}" style="color:#0b3668; font-weight:600;">{{ $supportEmail }}</a>.
        </p>
    </div>

    <div class="email-footer">
        <p>This email was sent to <strong>{{ $userEmail }}</strong></p>
        <p>OmniReferral &mdash; Real Estate Referral &amp; Lead Management Platform</p>
        <p><a href="{{ $dashboardUrl }}">Dashboard</a> &bull; <a href="{{ $loginUrl }}">Login</a> &bull; <a href="mailto:{{ $supportEmail }}">Support</a></p>
    </div>

</div>
</body>
</html>
