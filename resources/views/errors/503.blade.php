<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OmniReferral — Under Maintenance</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(140deg, #071632 0%, #0e315d 55%, #0f2851 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-align: center;
            padding: 2rem;
        }
        .card {
            max-width: 520px;
            width: 100%;
        }
        .logo {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 2rem;
            letter-spacing: -0.02em;
        }
        .logo span:first-child { color: #FF6B00; }
        .logo span:last-child { color: #fff; }
        .icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            display: block;
        }
        h1 {
            font-size: 1.75rem;
            font-weight: 800;
            margin: 0 0 1rem;
            color: #fff;
        }
        p {
            font-size: 1rem;
            color: rgba(255,255,255,0.75);
            line-height: 1.6;
            margin: 0 0 2rem;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            color: rgba(255,255,255,0.85);
            padding: 0.6rem 1.2rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .pulse {
            width: 8px; height: 8px;
            background: #FF6B00;
            border-radius: 50%;
            animation: pulse 1.5s ease-in-out infinite;
            display: inline-block;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.7); }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">
            <span>Omni</span><span>Referral</span>
        </div>
        <span class="icon">&#128296;</span>
        <h1>We'll be right back</h1>
        <p>OmniReferral is undergoing a quick maintenance update to improve your experience. We'll be back online in just a few minutes.</p>
        <span class="badge">
            <span class="pulse"></span>
            Deployment in progress
        </span>
    </div>
</body>
</html>
