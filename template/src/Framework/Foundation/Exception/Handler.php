<?php

declare(strict_types=1);

namespace Veldora\Framework\Foundation\Exception;

use Throwable;
use ErrorException;

class Handler
{
    /**
     * Register the exception and error handlers.
     */
    public static function register(): void
    {
        $handler = new self();

        set_exception_handler([$handler, 'handleException']);

        set_error_handler(function (int $level, string $message, string $file = '', int $line = 0) {
            if (error_reporting() & $level) {
                throw new ErrorException($message, 0, $level, $file, $line);
            }
        });
    }

    /**
     * Handle an uncaught exception.
     */
    public function handleException(Throwable $e): void
    {
        // Clean any output buffers
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

        $isDebug = env('APP_DEBUG') === true;

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
        fwrite(STDERR, "\n\033[41;37m ERROR \033[0m [{$e->getCode()}] " . $e->getMessage() . "\n");
        fwrite(STDERR, "In \033[33m" . $e->getFile() . "\033[0m on line \033[33m" . $e->getLine() . "\033[0m\n\n");
        fwrite(STDERR, "\033[1mStack Trace:\033[0m\n");
        fwrite(STDERR, $e->getTraceAsString() . "\n\n");
        exit(1);
    }

    /**
     * Render the premium production error page.
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #09090b; color: #a1a1aa;
            font-family: 'Inter', system-ui, sans-serif;
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; padding: 24px;
        }
        .card {
            background: #111113; border: 1px solid #27272a;
            border-radius: 14px; padding: 40px 36px;
            max-width: 520px; width: 100%; text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .badge {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 11px; font-weight: 600; letter-spacing: .1em;
            text-transform: uppercase; color: #8b5cf6;
            background: rgba(139,92,246,.1); border: 1px solid rgba(139,92,246,.25);
            padding: 4px 12px; border-radius: 9999px; margin-bottom: 20px;
        }
        h1 { font-size: 1.4rem; font-weight: 600; color: #fafafa; margin-bottom: 12px; }
        p  { font-size: .88rem; line-height: 1.6; }
        a  { color: #8b5cf6; text-decoration: none; display: inline-block; margin-top: 24px; font-size: .85rem; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="card">
    <div class="badge">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
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
     * Render the interactive premium developer debug page.
     */
    protected function renderDebugPage(Throwable $e): string
    {
        $exceptionName = get_class($e);
        $message = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        $file = htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8');
        $line = $e->getLine();

        // Build code snippet
        $codeSnippet = '';
        if (file_exists($e->getFile())) {
            $lines = file($e->getFile());
            $start = max(0, $line - 6);
            $end = min(count($lines) - 1, $line + 5);

            for ($i = $start; $i <= $end; $i++) {
                $currLine = $i + 1;
                $lineContent = htmlspecialchars($lines[$i]);
                $isErrorLine = $currLine === $line;
                $class = $isErrorLine ? 'class="line error-line"' : 'class="line"';
                $codeSnippet .= "<div {$class}><span class=\"ln\">{$currLine}</span><span class=\"code-text\">{$lineContent}</span></div>";
            }
        }

        // Build stack trace
        $traceHtml = '';
        foreach ($e->getTrace() as $index => $step) {
            $traceFile = isset($step['file']) ? htmlspecialchars($step['file'], ENT_QUOTES, 'UTF-8') : '[internal]';
            $traceLine = $step['line'] ?? '';
            $class = $step['class'] ?? '';
            $type = $step['type'] ?? '';
            $function = $step['function'] ?? '';
            
            $call = $class ? "{$class}{$type}{$function}()" : "{$function}()";

            $traceHtml .= <<<HTML
            <div class="trace-step">
                <div class="trace-meta">
                    <span class="trace-num">#{$index}</span>
                    <span class="trace-file">{$traceFile}<b>:{$traceLine}</b></span>
                </div>
                <div class="trace-call">{$call}</div>
            </div>
HTML;
        }

        $phpVersion = PHP_VERSION;
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exception: {$message}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #09090b; color: #e4e4e7;
            font-family: 'Inter', system-ui, sans-serif;
            line-height: 1.5; padding: 40px 24px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        
        /* Header */
        header {
            background: #111113; border: 1px solid #27272a;
            border-radius: 12px; padding: 28px; margin-bottom: 24px;
        }
        .exc-badge {
            display: inline-block; font-size: 11px; font-weight: 700;
            color: #ef4444; background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 4px 10px; border-radius: 6px; margin-bottom: 12px;
            font-family: 'Fira Code', monospace;
        }
        h1 { font-size: 1.6rem; font-weight: 700; color: #fafafa; letter-spacing: -.02em; margin-bottom: 8px; }
        .exc-file { font-size: 0.85rem; color: #a1a1aa; font-family: 'Fira Code', monospace; }
        .exc-file b { color: #8b5cf6; }

        /* Code View */
        .code-view {
            background: #111113; border: 1px solid #27272a;
            border-radius: 12px; overflow: hidden; margin-bottom: 24px;
        }
        .code-header {
            background: #18181b; border-bottom: 1px solid #27272a;
            padding: 12px 20px; font-size: 12px; font-family: 'Fira Code', monospace; color: #a1a1aa;
        }
        .code-body {
            padding: 16px 0; font-family: 'Fira Code', monospace; font-size: 13px;
            overflow-x: auto; background: #0c0c0e;
        }
        .line { display: flex; align-items: center; padding: 2px 20px; border-left: 3px solid transparent; }
        .ln { width: 45px; color: #52525b; user-select: none; display: inline-block; text-align: right; margin-right: 20px; }
        .code-text { color: #e4e4e7; }
        .error-line {
            background: rgba(139, 92, 246, 0.08);
            border-left-color: #8b5cf6;
        }
        .error-line .ln { color: #c4b5fd; font-weight: bold; }
        .error-line .code-text { color: #ffffff; font-weight: 500; }

        /* Two Column Layout */
        .layout-grid { display: grid; grid-template-columns: 1fr; gap: 24px; }
        @media (min-width: 900px) {
            .layout-grid { grid-template-columns: 3fr 2fr; }
        }

        /* Stack Trace */
        .panel {
            background: #111113; border: 1px solid #27272a;
            border-radius: 12px; padding: 24px;
        }
        .panel h2 { font-size: 1.1rem; font-weight: 600; color: #fafafa; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
        .panel h2 svg { color: #8b5cf6; }
        
        .trace-step {
            padding: 14px 0; border-bottom: 1px solid #27272a;
        }
        .trace-step:last-child { border-bottom: none; }
        .trace-meta { display: flex; align-items: center; gap: 10px; font-size: 12px; margin-bottom: 4px; }
        .trace-num { color: #8b5cf6; font-weight: 700; font-family: 'Fira Code', monospace; }
        .trace-file { color: #71717a; font-family: 'Fira Code', monospace; word-break: break-all; }
        .trace-file b { color: #a1a1aa; }
        .trace-call { font-family: 'Fira Code', monospace; font-size: 13.5px; color: #e4e4e7; font-weight: 500; }
        
        /* Debug Vars Panel */
        .info-item { margin-bottom: 14px; font-size: 13px; }
        .info-label { color: #71717a; margin-bottom: 4px; font-weight: 500; }
        .info-val { font-family: 'Fira Code', monospace; background: #0c0c0e; border: 1px solid #27272a; padding: 6px 12px; border-radius: 6px; word-break: break-all; }
    </style>
</head>
<body>
<div class="container">
    <header>
        <span class="exc-badge">{$exceptionName}</span>
        <h1>{$message}</h1>
        <div class="exc-file">In {$file} <b>on line {$line}</b></div>
    </header>

    <div class="code-view">
        <div class="code-header">Source File: {$file}</div>
        <div class="code-body">
            {$codeSnippet}
        </div>
    </div>

    <div class="layout-grid">
        <!-- Stack Trace -->
        <div class="panel">
            <h2>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                Stack Trace
            </h2>
            <div class="trace-list">
                {$traceHtml}
            </div>
        </div>

        <!-- System Details -->
        <div class="panel">
            <h2>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                Environment & System
            </h2>
            <div class="info-item">
                <div class="info-label">Veldora Environment</div>
                <div class="info-val">local (debug mode active)</div>
            </div>
            <div class="info-item">
                <div class="info-label">PHP Version</div>
                <div class="info-val">PHP {$phpVersion}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Request URI</div>
                <div class="info-val">{$requestUri}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Request Method</div>
                <div class="info-val">{$requestMethod}</div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
HTML;
    }
}
