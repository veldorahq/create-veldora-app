<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veldora — Welcome</title>
    <style>
        body {
            background-color: #000000;
            color: #ededed;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
        }
        .card {
            max-width: 480px;
            padding: 2.5rem;
            border: 1px solid #1a1a1a;
            border-radius: 8px;
            background-color: #050505;
            box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.01);
        }
        .header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1.5rem;
        }
        .logo {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #ffffff;
            border: 1px solid #222222;
            padding: 4px 10px;
            border-radius: 4px;
            background-color: #0c0c0c;
            font-weight: 600;
        }
        .tagline {
            font-size: 0.75rem;
            color: #666666;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        h1 {
            font-size: 1.75rem;
            font-weight: 600;
            margin: 0 0 0.75rem 0;
            color: #ffffff;
            letter-spacing: -0.02em;
        }
        p {
            font-size: 0.875rem;
            color: #888888;
            line-height: 1.6;
            margin: 0 0 2rem 0;
        }
        .post-card {
            border: 1px solid #1a1a1a;
            background-color: #080808;
            padding: 1.25rem;
            border-radius: 6px;
            margin-bottom: 2rem;
        }
        .post-header {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.7rem;
            color: #666666;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
        }
        .post-card h3 {
            font-size: 0.95rem;
            font-weight: 500;
            color: #ffffff;
            margin: 0 0 0.5rem 0;
        }
        .post-body {
            font-size: 0.8rem;
            color: #777777;
            line-height: 1.5;
            margin: 0;
        }
        .footer {
            border-top: 1px solid #111111;
            padding-top: 1.5rem;
            display: flex;
            gap: 16px;
        }
        .link {
            font-size: 0.75rem;
            color: #888888;
            text-decoration: none;
            transition: color 0.15s ease;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .link:hover {
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <span class="logo">Veldora</span>
            <span class="tagline">v0.1.0</span>
        </div>
        
        @yield('content')
        
    </div>
</body>
</html>
