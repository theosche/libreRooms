<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'global_admin' => \App\Http\Middleware\EnsureUserIsGlobalAdmin::class,
            'unverified' => \App\Http\Middleware\UnverifiedUserOnly::class,
            'recently_authenticated' => \App\Http\Middleware\EnsureRecentlyAuthenticated::class,
        ]);
        // Ensure setup is complete before accessing any routes
        $middleware->web(append: [
            \App\Http\Middleware\EnsureSetupComplete::class,
            \App\Http\Middleware\EnsureSystemSettingsConfigured::class,
        ]);
    })->withExceptions(function (Exceptions $exceptions): void {
        // Handle missing APP_KEY during initial setup
        // This is safe because: 1) very specific exception, 2) not exploitable, 3) clear fix
        $exceptions->render(function (\Illuminate\Encryption\MissingAppKeyException $e, \Illuminate\Http\Request $request) {
            $envPath = base_path('.env');
            $examplePath = base_path('.env.example');

            // Ensure .env exists
            if (! file_exists($envPath) && file_exists($examplePath)) {
                copy($examplePath, $envPath);
            }

            // Generate the key
            if (file_exists($envPath)) {
                \Artisan::call('key:generate', ['--force' => true]);

                // Redirect to same URL to retry with the new key
                return new \Illuminate\Http\RedirectResponse($request->fullUrl());
            }

            return null; // Let it fail normally if we can't fix it
        });
    })->create();
