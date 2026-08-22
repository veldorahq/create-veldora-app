<?php

declare(strict_types=1);

namespace Veldora\Framework\Foundation\Exception;

use Throwable;
use ErrorException;

class Handler
{
    /**
     * Register the exception, error, and fatal shutdown handlers.
     */
    public static function register(): void
    {
        $handler = new self();

        // 1. Uncaught exception handler
        set_exception_handler([$handler, 'handleException']);

        // 2. Standard PHP warning / notice / error handler
        set_error_handler(function (int $level, string $message, string $file = '', int $line = 0) {
            if (error_reporting() & $level) {
                throw new ErrorException($message, 0, $level, $file, $line);
            }
        });

        // 3. Fatal shutdown handler for ParseError, fatal errors, and compile errors
        register_shutdown_function(function () use ($handler) {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR], true)) {
                $e = new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
                $handler->handleException($e);
            }
        });
    }

    /**
     * Handle an uncaught exception.
     */
    public function handleException(Throwable $e): void
    {
        // Clean any active output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (php_sapi_name() === 'cli') {
            $this->renderConsoleException($e);
            return;
        }

        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');
        }

        $debugVal = env('APP_DEBUG', true);
        $isDebug = ($debugVal === true || $debugVal === 'true' || $debugVal === 1 || $debugVal === '1' || $debugVal === 'TRUE' || $debugVal === null);

        if ($isDebug) {
            echo $this->renderDebugPage($e);
        } else {
            echo $this->renderProductionPage();
        }

        exit(1);
    }

    /**
     * Render the exception to the CLI.
     */
    protected function renderConsoleException(Throwable $e): void
    {
        $class = get_class($e);
        fwrite(STDERR, "\n\033[41;37m {$class} \033[0m " . $e->getMessage() . "\n");
        fwrite(STDERR, "In \033[33m" . $e->getFile() . "\033[0m on line \033[33m" . $e->getLine() . "\033[0m\n\n");
        fwrite(STDERR, "\033[1mStack Trace:\033[0m\n");
        fwrite(STDERR, $e->getTraceAsString() . "\n\n");
        exit(1);
    }

    /**
     * Render the minimal production error page.
     */
    protected function renderProductionPage(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — Server Error</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #09090b; color: #a1a1aa;
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; padding: 24px;
        }
        .card {
            background: #111114; border: 1px solid #222228;
            border-radius: 14px; padding: 40px 36px;
            max-width: 480px; width: 100%; text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .badge {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 11px; font-weight: 700; letter-spacing: .08em;
            text-transform: uppercase; color: #8b5cf6;
            background: rgba(139,92,246,.1); border: 1px solid rgba(139,92,246,.25);
            padding: 4px 12px; border-radius: 9999px; margin-bottom: 20px;
        }
        h1 { font-size: 1.5rem; font-weight: 700; color: #fafafa; margin-bottom: 10px; }
        p  { font-size: .9rem; line-height: 1.6; color: #71717a; margin-bottom: 24px; }
        a  { color: #8b5cf6; text-decoration: none; font-size: .85rem; font-weight: 600; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="card">
    <div class="badge">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><polygon points="12,2 22,20 2,20"></polygon></svg>
        Veldora
    </div>
    <h1>500 — Server Error</h1>
    <p>An unexpected server error occurred. Please try again later.</p>
    <a href="/">← Back to Home</a>
</div>
</body>
</html>
HTML;
    }

    /**
     * Render the interactive Laravel/Ignition-style developer debug page.
     */
    protected function renderDebugPage(Throwable $e): string
    {
        $exceptionName = get_class($e);
        $shortName = basename(str_replace('\\', '/', $exceptionName));
        $message = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        if ($message === '') {
            $message = 'No error message was provided.';
        }
        $file = htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8');
        $line = $e->getLine();

        // Build code snippet
        $codeSnippet = '';
        if (file_exists($e->getFile())) {
            $lines = file($e->getFile());
            $start = max(0, $line - 7);
            $end = min(count($lines) - 1, $line + 6);

            for ($i = $start; $i <= $end; $i++) {
                $currLine = $i + 1;
                $rawCode = $lines[$i] ?? '';
                $lineContent = htmlspecialchars($rawCode);
                $isErrorLine = $currLine === $line;
                $class = $isErrorLine ? 'class="code-line error-line"' : 'class="code-line"';
                $codeSnippet .= "<div {$class}><span class=\"line-num\">{$currLine}</span><span class=\"line-code\">{$lineContent}</span></div>";
            }
        } else {
            $codeSnippet = '<div class="code-line"><span class="line-code" style="color:#71717a;">Source file could not be read.</span></div>';
        }

        // Build stack trace
        $traceHtml = '';
        $fullTraceString = $e->getTraceAsString();
        foreach ($e->getTrace() as $index => $step) {
            $traceFile = isset($step['file']) ? htmlspecialchars($step['file'], ENT_QUOTES, 'UTF-8') : '[internal PHP execution]';
            $traceLine = $step['line'] ?? '-';
            $class = $step['class'] ?? '';
            $type = $step['type'] ?? '';
            $function = $step['function'] ?? '';
            $call = $class ? "{$class}{$type}{$function}()" : "{$function}()";

            $isApp = !str_contains($traceFile, 'vendor') && !str_contains($traceFile, '[internal');
            $badgeClass = $isApp ? 'app-badge' : 'vendor-badge';
            $badgeText = $isApp ? 'App' : 'Vendor';

            $traceHtml .= <<<HTML
            <div class="trace-item">
                <div class="trace-header">
                    <span class="trace-num">#{$index}</span>
                    <span class="trace-call">{$call}</span>
                    <span class="trace-tag {$badgeClass}">{$badgeText}</span>
                </div>
                <div class="trace-file">
                    {$traceFile} <span class="trace-line-num">:{$traceLine}</span>
                </div>
            </div>
HTML;
        }

        $phpVersion = PHP_VERSION;
        $requestUri = htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8');
        $requestMethod = htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? 'GET', ENT_QUOTES, 'UTF-8');
        $serverHost = htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost:8000', ENT_QUOTES, 'UTF-8');
        $memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 2);

        $copyPayload = addslashes("{$exceptionName}: {$e->getMessage()}\nIn {$e->getFile()}:{$e->getLine()}\n\nStack Trace:\n{$fullTraceString}");

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$shortName}: {$message}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base: #09090b;
            --bg-surface: #121215;
            --bg-code: #060608;
            --border-color: #27272a;
            --border-hover: #3f3f46;
            --text-main: #f4f4f5;
            --text-muted: #a1a1aa;
            --danger: #ef4444;
            --danger-bg: rgba(239, 68, 68, 0.12);
            --danger-border: rgba(239, 68, 68, 0.25);
            --accent: #8b5cf6;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            background-color: var(--bg-base);
            color: var(--text-main);
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.5;
            padding: 2.5rem 1.5rem 4rem;
            -webkit-font-smoothing: antialiased;
        }

        .debug-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* ── Header ─────────────────────────────────────────── */
        .error-banner {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.75rem 2rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1.5rem;
        }

        .error-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 0.75rem;
        }

        .exc-badge {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            font-weight: 700;
            color: #f87171;
            background: var(--danger-bg);
            border: 1px solid var(--danger-border);
            padding: 3px 10px;
            border-radius: 6px;
        }

        .status-badge {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            background: #18181b;
            border: 1px solid var(--border-color);
            padding: 3px 8px;
            border-radius: 6px;
        }

        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.02em;
            margin-bottom: 0.75rem;
            line-height: 1.35;
        }

        .error-file {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            color: var(--text-muted);
            word-break: break-all;
        }

        .error-file b {
            color: #38bdf8;
        }

        .copy-error-btn {
            background: #18181b;
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }

        .copy-error-btn:hover {
            border-color: var(--border-hover);
            background: #27272a;
        }

        /* ── Code Viewer ────────────────────────────────────── */
        .code-panel {
            background: var(--bg-code);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .code-panel-header {
            background: #101014;
            border-bottom: 1px solid var(--border-color);
            padding: 10px 16px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.78rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .code-viewport {
            padding: 14px 0;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            overflow-x: auto;
        }

        .code-line {
            display: flex;
            padding: 2px 16px;
            border-left: 3px solid transparent;
        }

        .line-num {
            width: 44px;
            text-align: right;
            margin-right: 20px;
            color: #52525b;
            user-select: none;
            flex-shrink: 0;
        }

        .line-code {
            color: #e4e4e7;
            white-space: pre;
        }

        .error-line {
            background: rgba(239, 68, 68, 0.08);
            border-left-color: var(--danger);
        }

        .error-line .line-num {
            color: #f87171;
            font-weight: 700;
        }

        .error-line .line-code {
            color: #ffffff;
            font-weight: 600;
        }

        /* ── Grid Layout ────────────────────────────────────── */
        .details-grid {
            display: grid;
            grid-template-columns: 3fr 2fr;
            gap: 1.5rem;
        }

        @media (max-width: 860px) {
            .details-grid { grid-template-columns: 1fr; }
            .error-banner { flex-direction: column; }
        }

        .panel {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
        }

        .panel-title {
            font-size: 1rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── Stack Trace ────────────────────────────────────── */
        .trace-item {
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #1c1c22;
            background: #0e0e11;
            margin-bottom: 8px;
            transition: border-color 0.15s;
        }

        .trace-item:hover {
            border-color: var(--border-hover);
        }

        .trace-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
        }

        .trace-num {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: #71717a;
        }

        .trace-call {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            font-weight: 600;
            color: #ffffff;
            flex: 1;
            word-break: break-all;
        }

        .trace-tag {
            font-size: 0.68rem;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
        }

        .app-badge {
            background: rgba(139, 92, 246, 0.15);
            color: #a78bfa;
            border: 1px solid rgba(139, 92, 246, 0.3);
        }

        .vendor-badge {
            background: #1f1f23;
            color: #71717a;
        }

        .trace-file {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: var(--text-muted);
            word-break: break-all;
        }

        .trace-line-num {
            color: #38bdf8;
            font-weight: 600;
        }

        /* ── Info Table ─────────────────────────────────────── */
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #1c1c22;
            font-size: 0.825rem;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: var(--text-muted);
        }

        .info-val {
            font-family: 'JetBrains Mono', monospace;
            color: #ffffff;
            font-weight: 500;
        }

        .method-tag {
            background: #8b5cf6;
            color: #ffffff;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 700;
        }
    </style>
</head>
<body>

    <div class="debug-container">

        <!-- Error Header Banner -->
        <div class="error-banner">
            <div>
                <div class="error-meta">
                    <span class="exc-badge">{$shortName}</span>
                    <span class="status-badge">HTTP 500</span>
                    <span class="status-badge">PHP v{$phpVersion}</span>
                </div>
                <h1 class="error-title">{$message}</h1>
                <div class="error-file">
                    {$file}<b>:{$line}</b>
                </div>
            </div>

            <button class="copy-error-btn" onclick="copyDebugInfo(this)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                </svg>
                Copy Error
            </button>
        </div>

        <!-- Code Snippet Viewer -->
        <div class="code-panel">
            <div class="code-panel-header">
                <span>{$file}</span>
                <span>Line {$line}</span>
            </div>
            <div class="code-viewport">
                {$codeSnippet}
            </div>
        </div>

        <!-- Bottom Grid: Stack Trace + Environment Details -->
        <div class="details-grid">

            <!-- Stack Trace -->
            <div class="panel">
                <h2 class="panel-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6"></line>
                        <line x1="8" y1="12" x2="21" y2="12"></line>
                        <line x1="8" y1="18" x2="21" y2="18"></line>
                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                    </svg>
                    Stack Trace
                </h2>
                <div>
                    {$traceHtml}
                </div>
            </div>

            <!-- Request & Environment -->
            <div class="panel">
                <h2 class="panel-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    Request & Environment
                </h2>

                <div class="info-row">
                    <span class="info-label">Request</span>
                    <span class="info-val"><span class="method-tag">{$requestMethod}</span> {$requestUri}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Host</span>
                    <span class="info-val">{$serverHost}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Framework</span>
                    <span class="info-val">Veldora v0.4.0</span>
                </div>
                <div class="info-row">
                    <span class="info-label">PHP Version</span>
                    <span class="info-val">{$phpVersion}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Memory Usage</span>
                    <span class="info-val">{$memoryUsage} MB</span>
                </div>
            </div>

        </div>

    </div>

    <script>
        function copyDebugInfo(btn) {
            const payload = `{$copyPayload}`;
            navigator.clipboard.writeText(payload).then(() => {
                const originalText = btn.innerHTML;
                btn.innerHTML = '<span style="color:#10b981;">✔ Copied to clipboard</span>';
                setTimeout(() => {
                    btn.innerHTML = originalText;
                }, 2000);
            });
        }
    </script>
</body>
</html>
HTML;
    }
}
