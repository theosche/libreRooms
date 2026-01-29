<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRecentlyAuthenticated
{
    /**
     * Validity period for re-authentication in minutes.
     */
    protected const REAUTH_TIMEOUT_MINUTES = 10;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $reauthenticatedAt = session('reauthenticated_at');

        if ($reauthenticatedAt) {
            $threshold = now()->subMinutes(self::REAUTH_TIMEOUT_MINUTES);
            if ($reauthenticatedAt->greaterThan($threshold)) {
                return $next($request);
            }
        }

        // Store the intended URL so we can redirect back after re-authentication
        session(['reauthenticate.intended' => $request->url()]);

        return redirect()->route('reauthenticate');
    }
}
