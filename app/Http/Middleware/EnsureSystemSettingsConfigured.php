<?php

namespace App\Http\Middleware;

use App\Models\SystemSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSystemSettingsConfigured
{
    /**
     * Routes that should be accessible even without system settings configured.
     */
    protected array $except = [
        'setup.*',
        'system-settings.*',
        'logout',
        'login',
        'register',
        'password.*',
        'verification.*',
        'auth.*',
        'config.*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow excepted routes
        foreach ($this->except as $pattern) {
            if ($request->routeIs($pattern)) {
                return $next($request);
            }
        }

        // Only check for authenticated global admins
        if (! auth()->check() || ! auth()->user()->is_global_admin) {
            return $next($request);
        }

        // Check if system settings are configured
        if (! $this->isConfigured()) {
            return redirect()->route('system-settings.edit')
                ->with('error', 'Veuillez d\'abord configurer les paramètres système essentiels (mail, timezone, devise, langue).');
        }

        return $next($request);
    }

    /**
     * Check if essential system settings are configured.
     */
    protected function isConfigured(): bool
    {
        $settings = SystemSettings::first();

        if (! $settings) {
            return false;
        }

        return ! empty($settings->timezone)
            && ! empty($settings->currency)
            && ! empty($settings->locale);
    }
}
