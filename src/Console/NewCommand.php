<?php

declare(strict_types=1);

namespace Veldora\Installer\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class NewCommand extends Command
{
    protected static ?string $defaultName = 'new';

    protected function configure(): void
    {
        $this
            ->setName('new')
            ->setDescription('Scaffold a new Veldora application')
            ->addArgument('name', InputArgument::REQUIRED, 'The directory name of the new application');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = (string) $input->getArgument('name');
        $targetDir = getcwd() . DIRECTORY_SEPARATOR . $name;

        if (file_exists($targetDir)) {
            $output->writeln("<error>Directory [{$name}] already exists.</error>");
            return Command::FAILURE;
        }

        $output->writeln("<info>Creating a new Veldora application in [{$targetDir}]...</info>");

        $directories = [
            '',
            'app',
            'app/Controllers',
            'app/Models',
            'app/Components',
            'app/Components/ui',
            'app/Middleware',
            'app/Services',
            'bootstrap',
            'config',
            'database',
            'database/migrations',
            'database/seeders',
            'database/factories',
            'public',
            'resources',
            'resources/views',
            'resources/css',
            'resources/js',
            'themes',
            'themes/default',
            'plugins',
            'routes',
            'storage',
            'tests',
        ];

        foreach ($directories as $dir) {
            $path = $targetDir . ($dir ? DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dir) : '');
            if (!is_dir($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
                $output->writeln("<error>Failed to create directory [{$path}]</error>");
                return Command::FAILURE;
            }
        }

        $this->writeStubs($targetDir, $output);

        $output->writeln("<info>Running composer install in the target directory...</info>");

        $process = new Process(['composer', 'install'], $targetDir);
        $process->setTimeout(300);
        $process->run(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });

        if (!$process->isSuccessful()) {
            $output->writeln("<error>Composer installation failed.</error>");
            return Command::FAILURE;
        }

        $output->writeln("\n<info>Application scaffolded successfully!</info>");
        $output->writeln("<comment>To start the local development server, run:</comment>");
        $output->writeln("  cd {$name}");
        $output->writeln("  php -S localhost:8000 -t public");

        return Command::SUCCESS;
    }

    protected function writeStubs(string $targetDir, OutputInterface $output): void
    {
        $corePath = realpath(__DIR__ . '/../../../veldora-core');
        if ($corePath === false) {
            $corePath = 'veldora-core'; // Fallback path representation
        }
        $corePath = str_replace('\\', '/', $corePath);

        // 1. composer.json
        $composerJson = <<<JSON
{
    "name": "veldora/app",
    "description": "Veldora Application",
    "license": "MIT",
    "type": "project",
    "require": {
        "php": "^8.2 || ^8.3",
        "veldora/framework": "*@dev"
    },
    "repositories": [
        {
            "type": "path",
            "url": "{$corePath}",
            "options": {
                "symlink": true
            }
        }
    ],
    "autoload": {
        "psr-4": {
            "App\\\\": "app/"
        }
    }
}
JSON;
        file_put_contents($targetDir . '/composer.json', $composerJson);

        // 2. bootstrap/app.php
        $bootstrapApp = <<<PHP
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Instantiate Veldora Application
\$app = new Veldora\Framework\Foundation\Application(
    dirname(__DIR__)
);

// Register HTTP Router
\$app->singleton(Veldora\Framework\Http\Router::class, function (\$app) {
    return new Veldora\Framework\Http\Router(\$app);
});

// Register View Engine
\$app->singleton(Veldora\Framework\View\Engine::class, function (\$app) {
    return new Veldora\Framework\View\Engine(\$app);
});

return \$app;
PHP;
        file_put_contents($targetDir . '/bootstrap/app.php', $bootstrapApp);

        // 3. public/index.php — clean dispatch, global handler registered by Application
        $publicIndex = <<<'PHP'
<?php

declare(strict_types=1);

/** @var Veldora\Framework\Foundation\Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->boot();

$request = Veldora\Framework\Http\Request::capture();
$router  = $app->get(Veldora\Framework\Http\Router::class);

// Load web routes
require_once $app->routesPath('web.php');

// Dispatch — exception/error handling is registered globally in the framework
$response = $router->dispatch($request);
$response->send();
PHP;
        file_put_contents($targetDir . '/public/index.php', $publicIndex);

        // 4. routes/web.php
        $routesWeb = <<<PHP
<?php

declare(strict_types=1);

/** @var Veldora\Framework\Http\Router \$router */

\$router->get('/', [App\Controllers\HomeController::class, 'index']);
PHP;
        file_put_contents($targetDir . '/routes/web.php', $routesWeb);

        // 5. app/Controllers/HomeController.php
        $homeController = <<<PHP
<?php

declare(strict_types=1);

namespace App\Controllers;

use Veldora\Framework\Http\Response;

class HomeController
{
    public function index(): Response
    {
        \$html = <<<HTML
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
        <h1>A PHP framework you actually own.</h1>
        <p>
            Welcome to your new Veldora application. This setup includes a fully wired dependency injection container, PSR-11 compliance, dynamic middleware routing, and strict modern PHP 8.2+ typing.
        </p>
        <div class="footer">
            <a href="#" class="link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                Read Docs
            </a>
            <a href="#" class="link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                GitHub
            </a>
            <a href="#" class="link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                Settings
            </a>
        </div>
    </div>
</body>
</html>
HTML;

        return new Response(\$html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
PHP;
        file_put_contents($targetDir . '/app/Controllers/HomeController.php', $homeController);

        // 6. .env
        $appKey = 'base64:' . base64_encode(random_bytes(48));
        $appName = ucwords(str_replace(['-', '_'], ' ', basename($targetDir)));
        $envContent = <<<ENV
###############################################
# Veldora Framework Environment Configuration #
###############################################

APP_NAME="{$appName}"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_KEY={$appKey}

APP_TIMEZONE=UTC
APP_LOCALE=en

###############################################
# Logging
###############################################

LOG_CHANNEL=file
LOG_LEVEL=debug

###############################################
# Database
###############################################

DB_CONNECTION=sqlite

# SQLite
DB_DATABASE=database/database.sqlite

# MySQL
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=veldora
# DB_USERNAME=root
# DB_PASSWORD=

###############################################
# Session
###############################################

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_COOKIE=veldora_session
SESSION_SECURE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

###############################################
# Cookies
###############################################

COOKIE_PREFIX=veldora_
COOKIE_SIGN=true

###############################################
# Cache
###############################################

CACHE_DRIVER=file

###############################################
# Queue
###############################################

QUEUE_CONNECTION=sync

###############################################
# Mail
###############################################

MAIL_MAILER=smtp
MAIL_HOST=mail.example.com
MAIL_PORT=587
MAIL_USERNAME=username
MAIL_PASSWORD=password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="{$appName}"

###############################################
# Authentication
###############################################

AUTH_GUARD=web
PASSWORD_TIMEOUT=10800

###############################################
# CSRF
###############################################

CSRF_TOKEN_NAME=_token
CSRF_LIFETIME=7200

###############################################
# File Storage
###############################################

FILESYSTEM_DISK=local

###############################################
# Views
###############################################

VIEW_CACHE=true

###############################################
# Development
###############################################

SHOW_DEPRECATION_WARNINGS=true
SHOW_QUERY_LOG=false

###############################################
# Custom Variables
###############################################

COMPANY_NAME="Your Company"
COMPANY_EMAIL=hello@example.com
ENV;
        file_put_contents($targetDir . '/.env', $envContent);
        file_put_contents($targetDir . '/.env.example', $envContent);
    }
}
