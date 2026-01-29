<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSetupComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // First, allow environment setup routes only
        if ($request->routeIs('setup.environment*')) {
            return $next($request);
        }
        // Check if database is configured (via .env flag)
        if (! $this->isDatabaseConfigured()) {
            return redirect()->route('setup.environment');
        }

        // Then, allow all setup routes
        if ($request->routeIs('setup.*')) {
            return $next($request);
        }
        // Check if any global admin exists
        if (! $this->hasGlobalAdmin()) {
            return redirect()->route('setup.admin');
        }

        return $next($request);
    }

    /**
     * Check if database has been configured via the setup process.
     */
    protected function isDatabaseConfigured(): bool
    {
        return filter_var(env('DB_CONFIGURED', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Check if at least one global admin exists.
     */
    protected function hasGlobalAdmin(): bool
    {
        return User::where('is_global_admin', true)->exists();
    }
}
