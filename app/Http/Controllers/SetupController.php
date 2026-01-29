<?php

namespace App\Http\Controllers;

use App\Models\SystemSettings;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SetupController extends Controller
{
    /**
     * Check if database is configured via .env flag.
     */
    protected function isDatabaseConfigured(): bool
    {
        return filter_var(env('DB_CONFIGURED', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Authorize access to environment setup.
     * Allows: initial setup (DB_CONFIGURED=false) OR authenticated global admin.
     */
    protected function authorizeEnvironmentAccess(): void
    {
        if ($this->isDatabaseConfigured()) {
            // Post-setup: require global admin
            if (! auth()->check() || ! auth()->user()->is_global_admin) {
                abort(403, 'Accès réservé aux administrateurs.');
            }
        }
        // Initial setup: allow anyone
    }

    /**
     * Show the environment configuration form.
     */
    public function showEnvironmentForm(Request $request): View|RedirectResponse
    {
        $this->authorizeEnvironmentAccess();

        // Ensure .env exists (copy from .env.example if needed)
        $this->ensureEnvFileExists();

        // Read current values directly from .env file (not cached config)
        $envValues = $this->parseEnvFile();

        $config = [
            'APP_URL' => $envValues['APP_URL'] ?? 'http://localhost',
            'APP_LOCALE' => $envValues['APP_LOCALE'] ?? 'fr',
            'DB_CONNECTION' => $envValues['DB_CONNECTION'] ?? 'sqlite',
            'DB_HOST' => $envValues['DB_HOST'] ?? '127.0.0.1',
            'DB_PORT' => $envValues['DB_PORT'] ?? '3306',
            'DB_DATABASE' => $envValues['DB_DATABASE'] ?? '',
            'DB_USERNAME' => $envValues['DB_USERNAME'] ?? '',
            'DB_PASSWORD' => $envValues['DB_PASSWORD'] ?? '',
        ];

        // Check if database is currently connected (use actual running config)
        $dbConnected = $this->hasDatabaseConnection();

        // Get error from query parameter (sessions might not work)
        $error = $request->query('error');

        return view('setup.environment', [
            'config' => $config,
            'dbConnected' => $dbConnected,
            'locales' => $this->getAvailableLocales(),
            'dbDrivers' => $this->getAvailableDbDrivers(),
            'setupError' => $error,
        ]);
    }

    /**
     * Redirect to environment form with error (without using sessions).
     */
    protected function redirectWithError(string $message): RedirectResponse
    {
        return new RedirectResponse(
            route('setup.environment', ['error' => $message])
        );
    }

    /**
     * Save environment configuration.
     */
    public function saveEnvironment(Request $request): RedirectResponse
    {
        $this->authorizeEnvironmentAccess();

        // Manual validation (redirect with error in query param for better UX)
        $data = $request->only([
            'APP_URL', 'APP_LOCALE', 'DB_CONNECTION',
            'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD',
        ]);

        // Basic validation
        if (empty($data['APP_URL']) || ! filter_var($data['APP_URL'], FILTER_VALIDATE_URL)) {
            return $this->redirectWithError('URL de l\'application invalide');
        }
        if (empty($data['DB_DATABASE'])) {
            return $this->redirectWithError('Le nom de la base de données est requis');
        }

        $envValues = $this->parseEnvFile();
        // Keep password if empty in form
        $data['DB_PASSWORD'] = ! empty($data['DB_PASSWORD']) ?
            $data['DB_PASSWORD'] :
            ($envValues['DB_PASSWORD'] ?? '');

        // Test database connection before saving
        $connectionResult = $this->testDatabaseConnection(
            $data['DB_CONNECTION'] ?? 'sqlite',
            $data['DB_HOST'] ?? '127.0.0.1',
            $data['DB_PORT'] ?? 3306,
            $data['DB_DATABASE'],
            $data['DB_USERNAME'] ?? '',
            $data['DB_PASSWORD'],
        );

        if ($connectionResult !== true) {
            return $this->redirectWithError('Impossible de se connecter à la base de données : '.$connectionResult);
        }

        // Save to .env file (DB_CONFIGURED=true marks successful setup)
        $this->updateEnvFile([
            'APP_URL' => $data['APP_URL'],
            'APP_LOCALE' => $data['APP_LOCALE'] ?? 'fr',
            'APP_FALLBACK_LOCALE' => $data['APP_LOCALE'] ?? 'fr',
            'DB_CONFIGURED' => 'true',
            'DB_CONNECTION' => $data['DB_CONNECTION'] ?? 'mariadb',
            'DB_HOST' => $data['DB_HOST'] ?? '127.0.0.1',
            'DB_PORT' => $data['DB_PORT'] ?? (($data['DB_CONNECTION'] ?? '') === 'pgsql' ? 5432 : 3306),
            'DB_DATABASE' => $data['DB_DATABASE'],
            'DB_USERNAME' => $data['DB_USERNAME'] ?? '',
            'DB_PASSWORD' => $data['DB_PASSWORD'] ?? '',
        ]);

        // Clear config cache to apply new values
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        // Reconfigure database connection with new values
        $driver = $data['DB_CONNECTION'] ?? 'mariadb';
        config([
            'database.default' => $driver,
            "database.connections.{$driver}.host" => $data['DB_HOST'] ?? '127.0.0.1',
            "database.connections.{$driver}.port" => $data['DB_PORT'] ?? 3306,
            "database.connections.{$driver}.database" => $data['DB_DATABASE'],
            "database.connections.{$driver}.username" => $data['DB_USERNAME'] ?? '',
            "database.connections.{$driver}.password" => $data['DB_PASSWORD'] ?? '',
        ]);

        // Purge the connection to force reconnection with new config
        DB::purge();

        // Run migrations
        try {
            \Artisan::call('migrate', ['--force' => true]);
        } catch (\Exception $e) {
            return $this->redirectWithError('Erreur lors de l\'exécution des migrations : '.$e->getMessage());
        }

        // Redirect to admin setup (sessions should work now)
        return redirect()->route('setup.admin')
            ->with('success', 'Configuration de l\'environnement enregistrée !');
    }

    /**
     * Show the initial admin creation form.
     */
    public function showAdminForm(): View|RedirectResponse
    {
        // If a global admin already exists, redirect to home
        if ($this->hasDatabaseConnection() && User::where('is_global_admin', true)->exists()) {
            return redirect()->route('rooms.index');
        }

        return view('setup.admin');
    }

    /**
     * Create the first admin user.
     */
    public function createAdmin(Request $request): RedirectResponse
    {
        // Security: prevent creating admin if one already exists
        if (User::where('is_global_admin', true)->exists()) {
            return redirect()->route('rooms.index')
                ->with('error', 'Un administrateur existe déjà.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);

        // Create admin user with verified email
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_global_admin' => true,
            'email_verified_at' => now(),
        ]);

        // Log in the new admin
        Auth::login($user);
        $request->session()->regenerate();

        // Check if system settings need configuration
        if ($this->needsSystemSettings()) {
            return redirect()->route('system-settings.edit')
                ->with('success', 'Compte administrateur créé ! Veuillez maintenant configurer les paramètres système.');
        }

        return redirect()->route('rooms.index')
            ->with('success', 'Configuration initiale terminée !');
    }

    /**
     * Check if system settings are incomplete.
     */
    protected function needsSystemSettings(): bool
    {
        $settings = SystemSettings::first();

        if (! $settings) {
            return true;
        }

        // Check if essential settings are missing
        return empty($settings->timezone)
            || empty($settings->currency)
            || empty($settings->locale);
    }

    /**
     * Ensure .env file exists, copy from .env.example if not.
     */
    protected function ensureEnvFileExists(): void
    {
        $envPath = base_path('.env');
        $examplePath = base_path('.env.example');

        if (! file_exists($envPath) && file_exists($examplePath)) {
            copy($examplePath, $envPath);
        }
        // Generate APP_KEY if not set
        if (empty(env('APP_KEY'))) {
            \Artisan::call('key:generate', ['--force' => true]);
        }
    }

    /**
     * Parse .env file and return key-value array.
     *
     * @return array<string, string>
     */
    protected function parseEnvFile(): array
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            return [];
        }

        $values = [];
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            // Parse KEY=value
            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes if present
                if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                    $value = $matches[2];
                }

                $values[$key] = $value;
            }
        }

        return $values;
    }

    /**
     * Test database connection with given credentials.
     *
     * @return bool|string True if connected, error message otherwise
     */
    protected function testDatabaseConnection(
        string $driver,
        ?string $host,
        int|string|null $port,
        string $database,
        ?string $username,
        ?string $password
    ): bool|string {
        try {
            if ($driver === 'sqlite') {
                // For SQLite, check if file exists or can be created
                $dbPath = $database;
                if (! str_starts_with($database, '/')) {
                    $dbPath = database_path($database);
                }

                // Touch the file to create it if it doesn't exist
                if (! file_exists($dbPath)) {
                    $dir = dirname($dbPath);
                    if (! is_dir($dir)) {
                        return "Le dossier {$dir} n'existe pas";
                    }
                    touch($dbPath);
                }

                $pdo = new \PDO("sqlite:{$dbPath}");
            } else {
                $pdo_driver = $driver === 'mariadb' ? 'mysql' : $driver;
                $dsn = "{$pdo_driver}:host={$host};port={$port};dbname={$database}";
                $pdo = new \PDO($dsn, $username, $password, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_TIMEOUT => 5,
                ]);
            }

            return true;
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Check if database connection is working.
     */
    protected function hasDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update .env file with new values.
     */
    protected function updateEnvFile(array $values): void
    {
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);

        foreach ($values as $key => $value) {
            // Escape special characters in value
            $escapedValue = $this->formatEnvValue($value);

            // Check if key exists
            if (preg_match("/^{$key}=/m", $content)) {
                // Replace existing value
                $content = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$escapedValue}",
                    $content
                );
            } else {
                // Add new key
                $content .= "\n{$key}={$escapedValue}";
            }
        }

        file_put_contents($envPath, $content);
        Artisan::call('config:clear');   // recharge .env
        Artisan::call('cache:clear');   // recharge .env
        Artisan::call('config:cache');   // recache pour prod
    }

    /**
     * Format value for .env file.
     */
    protected function formatEnvValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $value = (string) $value;

        // Quote if contains spaces or special characters
        if (preg_match('/[\s#"\'\\\\]/', $value) || $value === '') {
            return '"'.addslashes($value).'"';
        }

        return $value;
    }

    /**
     * Get available locales.
     */
    protected function getAvailableLocales(): array
    {
        return [
            'fr' => 'Français',
            'en' => 'English',
            'de' => 'Deutsch',
        ];
    }

    /**
     * Get available database drivers.
     */
    protected function getAvailableDbDrivers(): array
    {
        return [
            'sqlite' => 'SQLite',
            'mysql' => 'MySQL',
            'mariadb' => 'MariaDB',
            'pgsql' => 'PostgreSQL',
        ];
    }
}
