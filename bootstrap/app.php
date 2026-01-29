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
    })->create();
