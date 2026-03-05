<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ config('app.name', 'Application') }} — Maintenance</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg: #06070f;
            --surface: rgba(255, 255, 255, 0.04);
            --border: rgba(255, 255, 255, 0.08);
            --accent: #6366f1;
            --accent-glow: rgba(99, 102, 241, 0.35);
            --text: #e2e8f0;
            --muted: #64748b;
            --green: #22d3ee;
        }

        html,
        body {
            height: 100%;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            overflow: hidden;
        }

        /* Animated background */
        .bg-orbs {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.3;
            animation: float 12s ease-in-out infinite;
        }

        .orb-1 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, #6366f1, transparent);
            top: -150px;
            left: -150px;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, #a855f7, transparent);
            bottom: -100px;
            right: -100px;
            animation-delay: 4s;
        }

        .orb-3 {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, #22d3ee, transparent);
            top: 40%;
            left: 60%;
            animation-delay: 8s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) scale(1);
            }

            50% {
                transform: translateY(-30px) scale(1.06);
            }
        }

        /* Grid overlay */
        .grid-overlay {
            position: fixed;
            inset: 0;
            z-index: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
            background-size: 50px 50px;
            pointer-events: none;
        }

        /* Main layout */
        .container {
            position: relative;
            z-index: 10;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        /* Card */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 1.5rem;
            padding: 3rem 2.5rem;
            max-width: 520px;
            width: 100%;
            text-align: center;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow:
                0 0 0 1px rgba(255, 255, 255, 0.04),
                0 40px 80px rgba(0, 0, 0, 0.4),
                0 0 60px var(--accent-glow);
            animation: card-in 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes card-in {
            from {
                opacity: 0;
                transform: translateY(24px) scale(0.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Icon */
        .icon-wrap {
            width: 72px;
            height: 72px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            border-radius: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            box-shadow: 0 8px 32px rgba(99, 102, 241, 0.4);
            animation: pulse-glow 3s ease-in-out infinite;
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 8px 32px rgba(99, 102, 241, 0.4);
            }

            50% {
                box-shadow: 0 8px 48px rgba(99, 102, 241, 0.7);
            }
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            margin-bottom: 0.75rem;
            background: linear-gradient(135deg, #e2e8f0, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            font-size: 1rem;
            color: var(--muted);
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        /* Divider */
        .divider {
            height: 1px;
            background: var(--border);
            margin: 1.75rem 0;
        }

        /* Countdown */
        .countdown-wrap {
            margin-bottom: 1.5rem;
        }

        .countdown-label {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 0.75rem;
        }

        .countdown {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
        }

        .countdown-block {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
        }

        .countdown-num {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            color: var(--accent);
            letter-spacing: -0.02em;
        }

        .countdown-unit {
            font-size: 0.65rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Status pill */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.875rem;
            background: rgba(99, 102, 241, 0.12);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 500;
            color: #a5b4fc;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            background: var(--accent);
            border-radius: 50%;
            animation: blink 1.5s ease-in-out infinite;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.3;
            }
        }

        /* Footer */
        .footer {
            margin-top: 1.5rem;
            font-size: 0.75rem;
            color: var(--muted);
        }
    </style>
</head>

<body>
    <div class="bg-orbs">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>
    <div class="grid-overlay"></div>

    <div class="container">
        <div class="card">
            <div class="icon-wrap">🔧</div>

            <h1>Under Maintenance</h1>

            <p class="subtitle">
                {{ $setting->message ?? 'We are performing scheduled maintenance. We\'ll be back shortly!' }}
            </p>

            <div class="status-pill">
                <div class="status-dot"></div>
                Maintenance in progress
            </div>

            @if($setting->ends_at)
                <div class="divider"></div>
                <div class="countdown-wrap">
                    <div class="countdown-label">Back online in</div>
                    <div class="countdown" id="countdown">
                        <div class="countdown-block">
                            <div class="countdown-num" id="cd-h">--</div>
                            <div class="countdown-unit">Hours</div>
                        </div>
                        <div class="countdown-block">
                            <div class="countdown-num" id="cd-m">--</div>
                            <div class="countdown-unit">Minutes</div>
                        </div>
                        <div class="countdown-block">
                            <div class="countdown-num" id="cd-s">--</div>
                            <div class="countdown-unit">Seconds</div>
                        </div>
                    </div>
                </div>
                <script>
                    (function () {
                        var end = new Date("{{ $setting->ends_at->toIso8601String() }}").getTime();
                        function pad(n) { return String(n).padStart(2, '0'); }
                        function tick() {
                            var diff = end - Date.now();
                            if (diff <= 0) { location.reload(); return; }
                            var h = Math.floor(diff / 3600000);
                            var m = Math.floor((diff % 3600000) / 60000);
                            var s = Math.floor((diff % 60000) / 1000);
                            document.getElementById('cd-h').textContent = pad(h);
                            document.getElementById('cd-m').textContent = pad(m);
                            document.getElementById('cd-s').textContent = pad(s);
                        }
                        tick();
                        setInterval(tick, 1000);
                    })();
                </script>
            @endif

            <div class="divider"></div>

            <div class="footer">
                {{ config('app.name', 'Application') }} &bull; Please try again later
                @if($setting->retry_after)
                    &bull; Retry in {{ $setting->retry_after }}s
                @endif
            </div>
        </div>
    </div>
</body>

</html>