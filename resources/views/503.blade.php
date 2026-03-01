<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Application') }} — Maintenance</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, sans-serif;
            background: #0f0f1a;
            color: #e2e8f0;
        }

        .card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.5rem;
            padding: 3rem 2.5rem;
            max-width: 480px;
            text-align: center;
            backdrop-filter: blur(12px);
        }

        h1 {
            font-size: 1.75rem;
            margin-bottom: 1rem;
        }

        p {
            color: #94a3b8;
            line-height: 1.6;
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>🔧 Maintenance</h1>
        <p>{{ config('maintenance.response.message', 'We are performing scheduled maintenance. Be right back!') }}</p>
    </div>
</body>

</html>