<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? env('APP_NAME', 'Veldora') . ' — The Modern PHP Framework' }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Veldora UI Styles -->
    <link rel="stylesheet" href="/css/veldora-ui.css">

    <style>
        :root {
            --bg-base: #09090b;
            --bg-surface: #121215;
            --border-subtle: #27272a;
            --text-main: #f4f4f5;
            --text-muted: #a1a1aa;
            --accent: #8b5cf6;
            --accent-glow: rgba(139, 92, 246, 0.15);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-base);
            color: var(--text-main);
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            background-image: 
                radial-gradient(ellipse 80% 50% at 50% -20%, var(--accent-glow), transparent),
                radial-gradient(circle at 100% 100%, rgba(24, 24, 27, 0.5), transparent);
            background-repeat: no-repeat;
        }

        /* ── Modern App Navbar ────────────────────────────────── */
        .app-navbar {
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            background: rgba(9, 9, 11, 0.75);
            border-bottom: 1px solid var(--border-subtle);
        }

        .navbar-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0.85rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #fff;
        }

        .brand-logo {
            font-family: 'Outfit', sans-serif;
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, #ffffff 40%, #a78bfa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .brand-logo svg {
            width: 20px;
            height: 20px;
            fill: #8b5cf6;
        }

        .version-badge {
            font-size: 0.7rem;
            font-family: 'JetBrains Mono', monospace;
            padding: 2px 8px;
            border-radius: 999px;
            background: rgba(139, 92, 246, 0.12);
            color: #a78bfa;
            border: 1px solid rgba(139, 92, 246, 0.25);
            font-weight: 600;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .nav-link {
            font-size: 0.85rem;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.15s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav-link:hover {
            color: #ffffff;
        }

        .nav-btn {
            font-size: 0.8rem;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            background: #ffffff;
            color: #09090b;
            transition: opacity 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .nav-btn:hover {
            opacity: 0.9;
        }

        /* ── Main Layout ──────────────────────────────────────── */
        main {
            flex: 1;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 3.5rem 1.5rem 5rem;
        }

        /* ── Footer ───────────────────────────────────────────── */
        .app-footer {
            border-top: 1px solid var(--border-subtle);
            padding: 2rem 1.5rem;
            background: rgba(9, 9, 11, 0.6);
        }

        .footer-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.8rem;
            color: var(--text-muted);
            flex-wrap: wrap;
            gap: 12px;
        }

        .footer-links {
            display: flex;
            gap: 16px;
        }

        .footer-links a {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.15s;
        }

        .footer-links a:hover {
            color: #fff;
        }

        @media (max-width: 640px) {
            .nav-links .hide-sm {
                display: none;
            }
            .footer-inner {
                flex-direction: column;
                text-align: center;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header class="app-navbar">
        <div class="navbar-inner">
            <a href="/" class="brand">
                <span class="brand-logo">
                    <svg viewBox="0 0 24 24">
                        <polygon points="12,2 22,20 2,20"></polygon>
                    </svg>
                    {{ env('APP_NAME', 'Veldora') }}
                </span>
                <span class="version-badge">v0.4.0-beta</span>
            </a>

            <nav class="nav-links">
                <a href="https://veldora.dev/docs" target="_blank" rel="noopener" class="nav-link hide-sm">Documentation</a>
                <a href="https://veldora.dev/components" target="_blank" rel="noopener" class="nav-link hide-sm">Components</a>
                <a href="https://github.com/veldorahq" target="_blank" rel="noopener" class="nav-btn">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                    </svg>
                    GitHub
                </a>
            </nav>
        </div>
    </header>

    <!-- Main View Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="app-footer">
        <div class="footer-inner">
            <div>
                Crafted with love by <strong>Shahriyar Fahim</strong> • MIT Licensed
            </div>
            <div class="footer-links">
                <a href="https://veldora.dev" target="_blank" rel="noopener">Official Website</a>
                <a href="https://veldora.dev/docs" target="_blank" rel="noopener">Docs</a>
                <a href="https://veldora.dev/components" target="_blank" rel="noopener">UI Components</a>
                <a href="https://github.com/veldorahq" target="_blank" rel="noopener">GitHub</a>
            </div>
        </div>
    </footer>

</body>
</html>
