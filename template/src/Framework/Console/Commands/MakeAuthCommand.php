<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Veldora\Framework\Foundation\Application;

class MakeAuthCommand extends Command
{
    protected static ?string $defaultName = 'make:auth';

    protected function configure(): void
    {
        $this
            ->setName('make:auth')
            ->setDescription('Scaffold basic authentication layer (controllers, views, migrations, and routes)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Enable admin support? (Y/n) ', true);
        
        $enableAdmin = $helper->ask($input, $output, $question);
        
        $app = Application::getInstance();
        $basePath = $app->basePath();

        $output->writeln('<info>Scaffolding authentication layer...</info>');

        // 1. Create Migration
        $timestamp = date('Y_m_d_His');
        $migrationFile = "{$basePath}/database/migrations/{$timestamp}_create_users_table.php";
        
        $adminColumn = $enableAdmin 
            ? "            \$table->boolean('is_admin')->default(0);"
            : "";

        $migrationContent = <<<PHP
<?php

declare(strict_types=1);

use Veldora\Framework\Database\Schema\Blueprint;
use Veldora\Framework\Database\Schema\Migration;
use Veldora\Framework\Database\Schema\Schema;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint \$table) {
            \$table->id();
            \$table->string('name');
            \$table->string('email')->unique();
            \$table->timestamp('email_verified_at')->nullable();
            \$table->string('password');
{$adminColumn}
            \$table->rememberToken();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}
PHP;

        $migrationsDir = dirname($migrationFile);
        if (!is_dir($migrationsDir)) {
            mkdir($migrationsDir, 0755, true);
        }
        file_put_contents($migrationFile, $migrationContent);
        $output->writeln("Created migration: <comment>database/migrations/" . basename($migrationFile) . "</comment>");

        // 2. Create User Model
        $modelFile = "{$basePath}/app/Models/User.php";
        $modelDir = dirname($modelFile);
        if (!is_dir($modelDir)) {
            mkdir($modelDir, 0755, true);
        }

        $modelContent = <<<PHP
<?php

declare(strict_types=1);

namespace App\Models;

use Veldora\Framework\Database\Model;

class User extends Model
{
    protected ?string \$table = 'users';

    /**
     * Determine if the user is an administrator.
     */
    public function isAdmin(): bool
    {
        return (bool) (\$this->attributes['is_admin'] ?? false);
    }
}
PHP;
        file_put_contents($modelFile, $modelContent);
        $output->writeln("Created model: <comment>app/Models/User.php</comment>");

        // 3. Create Controllers
        $controllersDir = "{$basePath}/app/Controllers";
        if (!is_dir($controllersDir)) {
            mkdir($controllersDir, 0755, true);
        }

        // Login Controller
        $loginControllerContent = <<<PHP
<?php

declare(strict_types=1);

namespace App\Controllers;

use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use Veldora\Framework\Auth\Auth;

class LoginController
{
    public function show(): Response
    {
        return view('auth/login');
    }

    public function login(Request \$request): Response
    {
        \$credentials = \$request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        \$remember = \$request->input('remember') === 'on';

        if (Auth::attempt(\$credentials, \$remember)) {
            return redirect('/');
        }

        return back()->with('error', 'Invalid email or password.');
    }

    public function logout(): Response
    {
        Auth::logout();
        return redirect('/login');
    }
}
PHP;
        file_put_contents("{$controllersDir}/LoginController.php", $loginControllerContent);
        $output->writeln("Created controller: <comment>app/Controllers/LoginController.php</comment>");

        // Register Controller
        $registerControllerContent = <<<PHP
<?php

declare(strict_types=1);

namespace App\Controllers;

use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use App\Models\User;
use Veldora\Framework\Auth\Auth;

class RegisterController
{
    public function show(): Response
    {
        return view('auth/register');
    }

    public function register(Request \$request): Response
    {
        \$data = \$request->validate([
            'name'                  => 'required|string',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);

        \$user = new User();
        \$user->name = \$data['name'];
        \$user->email = \$data['email'];
        \$user->password = password_hash(\$data['password'], PASSWORD_BCRYPT);
        \$user->save();

        Auth::login(\$user);

        return redirect('/');
    }
}
PHP;
        file_put_contents("{$controllersDir}/RegisterController.php", $registerControllerContent);
        $output->writeln("Created controller: <comment>app/Controllers/RegisterController.php</comment>");

        // 4. Create Views
        $viewsDir = "{$basePath}/resources/views/auth";
        if (!is_dir($viewsDir)) {
            mkdir($viewsDir, 0755, true);
        }

        // Login View
        $loginViewContent = <<<'HTML'
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
</head>
<body class="bg-zinc-950 text-zinc-100 min-h-screen flex items-center justify-center font-sans antialiased">
    <div class="w-full max-w-md bg-zinc-900 border border-zinc-800 rounded-2xl p-8 shadow-xl">
        <h2 class="text-3xl font-extrabold text-white text-center mb-6 tracking-tight">Welcome Back</h2>
        
        <?php if (session()->has('error')): ?>
            <div class="mb-4 p-3 bg-red-950/50 border border-red-800 text-red-300 rounded-lg text-sm">
                {{ session()->get('error') }}
            </div>
        <?php endif; ?>

        <form action="/login" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-zinc-400 mb-2">Email Address</label>
                <input type="email" name="email" required class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-4 py-3 text-zinc-100 placeholder-zinc-600 focus:outline-none focus:ring-2 focus:ring-violet-600 transition" placeholder="you@example.com">
            </div>
            <div>
                <label class="block text-sm font-semibold text-zinc-400 mb-2">Password</label>
                <input type="password" name="password" required class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-4 py-3 text-zinc-100 placeholder-zinc-600 focus:outline-none focus:ring-2 focus:ring-violet-600 transition" placeholder="••••••••">
            </div>
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center text-zinc-400 cursor-pointer">
                    <input type="checkbox" name="remember" class="sr-only peer">
                    <div class="w-4 h-4 bg-zinc-950 border border-zinc-800 rounded mr-2 peer-checked:bg-violet-600 peer-checked:border-violet-600 flex items-center justify-center transition">
                        <svg class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    Remember me
                </label>
            </div>
            <button type="submit" class="w-full bg-violet-600 hover:bg-violet-500 active:scale-[0.98] text-white font-semibold py-3.5 px-4 rounded-xl shadow-lg shadow-violet-900/30 hover:shadow-violet-800/40 transition">Sign In</button>
        </form>
        
        <p class="text-center text-sm text-zinc-500 mt-6">
            Don't have an account? <a href="/register" class="text-violet-400 hover:underline">Create one</a>
        </p>
    </div>
</body>
</html>
HTML;
        file_put_contents("{$viewsDir}/login.veldora.php", $loginViewContent);
        $output->writeln("Created view: <comment>resources/views/auth/login.veldora.php</comment>");

        // Register View
        $registerViewContent = <<<'HTML'
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
</head>
<body class="bg-zinc-950 text-zinc-100 min-h-screen flex items-center justify-center font-sans antialiased">
    <div class="w-full max-w-md bg-zinc-900 border border-zinc-800 rounded-2xl p-8 shadow-xl">
        <h2 class="text-3xl font-extrabold text-white text-center mb-6 tracking-tight">Create Account</h2>

        <form action="/register" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-zinc-400 mb-2">Name</label>
                <input type="text" name="name" required class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-4 py-3 text-zinc-100 placeholder-zinc-600 focus:outline-none focus:ring-2 focus:ring-violet-600 transition" placeholder="John Doe">
            </div>
            <div>
                <label class="block text-sm font-semibold text-zinc-400 mb-2">Email Address</label>
                <input type="email" name="email" required class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-4 py-3 text-zinc-100 placeholder-zinc-600 focus:outline-none focus:ring-2 focus:ring-violet-600 transition" placeholder="you@example.com">
            </div>
            <div>
                <label class="block text-sm font-semibold text-zinc-400 mb-2">Password</label>
                <input type="password" name="password" required class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-4 py-3 text-zinc-100 placeholder-zinc-600 focus:outline-none focus:ring-2 focus:ring-violet-600 transition" placeholder="••••••••">
            </div>
            <div>
                <label class="block text-sm font-semibold text-zinc-400 mb-2">Confirm Password</label>
                <input type="password" name="password_confirmation" required class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-4 py-3 text-zinc-100 placeholder-zinc-600 focus:outline-none focus:ring-2 focus:ring-violet-600 transition" placeholder="••••••••">
            </div>
            <button type="submit" class="w-full bg-violet-600 hover:bg-violet-500 active:scale-[0.98] text-white font-semibold py-3.5 px-4 rounded-xl shadow-lg shadow-violet-900/30 hover:shadow-violet-800/40 transition">Sign Up</button>
        </form>

        <p class="text-center text-sm text-zinc-500 mt-6">
            Already have an account? <a href="/login" class="text-violet-400 hover:underline">Log in</a>
        </p>
    </div>
</body>
</html>
HTML;
        file_put_contents("{$viewsDir}/register.veldora.php", $registerViewContent);
        $output->writeln("Created view: <comment>resources/views/auth/register.veldora.php</comment>");

        // 5. Append Routes to routes/web.php
        $routesFile = "{$basePath}/routes/web.php";
        if (file_exists($routesFile)) {
            $routesContent = file_get_contents($routesFile);
            if (!str_contains($routesContent, 'LoginController')) {
                $scaffoldRoutes = <<<PHP


// Authentication Routes Scaffolding
\$router->get('/login', [App\Controllers\LoginController::class, 'show'])->middleware(['guest']);
\$router->post('/login', [App\Controllers\LoginController::class, 'login'])->middleware(['guest']);
\$router->post('/logout', [App\Controllers\LoginController::class, 'logout'])->middleware(['auth']);

\$router->get('/register', [App\Controllers\RegisterController::class, 'show'])->middleware(['guest']);
\$router->post('/register', [App\Controllers\RegisterController::class, 'register'])->middleware(['guest']);
PHP;
                file_put_contents($routesFile, $routesContent . $scaffoldRoutes);
                $output->writeln("Appended routes to: <comment>routes/web.php</comment>");
            }
        }

        $output->writeln('<info>Authentication scaffolding completed successfully!</info>');

        return Command::SUCCESS;
    }
}
